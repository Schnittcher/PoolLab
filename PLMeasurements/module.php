<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/PoolLabTile.php';

/**
 * Legt die Messwerte eines Pools (Accounts der LabCOM-Cloud) an, unabhaengig vom messenden Geraet.
 * Die Variablen werden ueber die Parameter-ID der Cloud einer Messgroesse zugeordnet (parameters.json).
 *
 * Annahmen, die mit echten Daten zu pruefen sind:
 * - Ueberschreitung/Unterschreitung des Messbereichs liefert das Geraet als +/-1000000.0 (WSAPI-Referenz
 *   des PoolLab 2.0). Ob die Cloud diesen Wert unveraendert in Measurement.value ausliefert, ist offen.
 * - ideal_status kennt "TOO LOW", "OKAY" und "TOO HIGH" ("TOO HIGH" wurde noch nicht beobachtet).
 * - Parameter-IDs des PoolLab 2.0 stammen aus der offiziellen Parameterliste des Herstellers.
 */
class PLMeasurements extends IPSModuleStrict
{
    use PoolLabTile;

    private const ACCOUNT_GUID = '{5003D1FE-D820-150D-E709-8363BAE2CE11}';
    private const TO_ACCOUNT = '{962271EC-68EA-19B9-2732-49D5D8EEBC45}';
    private const ARCHIVE_GUID = '{43192F0B-135B-4CE7-A0A7-1475603F3060}';

    private const RANGE_LIMIT = 1000000.0;
    //Nachtraeglich vom Geraet synchronisierte Messungen haben aeltere Zeitstempel: Abfrage mit Ueberlappung, Filter ueber die ID
    private const OVERLAP = 2592000;

    private const STATUS_INTERVAL_INVALID = 201;

    //Zustand der Messung: Beschriftung (englisches Original) und Farbe der Variablendarstellung
    private const STATES = [
        'tooLow'     => ['too low', 0xFF0000],
        'okay'       => ['okay', 0x008800],
        'tooHigh'    => ['too high', 0xFF0000],
        'overrange'  => ['above measuring range', 0xFF8800],
        'underrange' => ['below measuring range', 0xFF8800]
    ];

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();

        $this->ConnectParent(self::ACCOUNT_GUID);

        $this->RegisterPropertyBoolean('Active', true);
        $this->RegisterPropertyInteger('UpdateInterval', 600);
        $this->RegisterPropertyString('DeviceSerial', '');

        $this->RegisterAttributeInteger('LastID', 0);
        $this->RegisterAttributeInteger('LastTimestamp', 0);
        $this->RegisterAttributeString('AppliedSerial', '');
        $this->RegisterAttributeString('UnknownParameters', '[]');

        $this->RegisterTimer('UpdateData', 0, 'PL_UpdateData($_IPS[\'TARGET\']);');

        $this->setTileType();
    }

    public function GetVisualizationTile(): string
    {
        return $this->buildTile(__DIR__ . '/module.html', $this->getTileData());
    }

    public function ApplyChanges(): void
    {
        //Never delete this line!
        parent::ApplyChanges();

        $this->MaintainVariable('LastDevice', $this->Translate('Last device'), VARIABLETYPE_STRING, [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION
        ], 1, true);
        $this->MaintainVariable('DeviceType', $this->Translate('Device type'), VARIABLETYPE_STRING, [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION
        ], 2, true);

        //Wechselt der Geraetefilter, werden die Messungen neu eingelesen
        $serial = trim($this->ReadPropertyString('DeviceSerial'));
        if ($serial !== $this->ReadAttributeString('AppliedSerial')) {
            $this->WriteAttributeInteger('LastID', 0);
            $this->WriteAttributeInteger('LastTimestamp', 0);
            $this->WriteAttributeString('AppliedSerial', $serial);
        }

        if ($this->ReadPropertyInteger('UpdateInterval') < 60) {
            $this->SetTimerInterval('UpdateData', 0);
            $this->SetStatus(self::STATUS_INTERVAL_INVALID);
            return;
        }

        if (!$this->ReadPropertyBoolean('Active')) {
            $this->SetTimerInterval('UpdateData', 0);
            $this->SetStatus(IS_INACTIVE);
            return;
        }

        $this->SetTimerInterval('UpdateData', $this->ReadPropertyInteger('UpdateInterval') * 1000);
        $this->SetStatus(IS_ACTIVE);

        if (IPS_GetKernelRunlevel() === KR_READY && $this->HasActiveParent()) {
            $this->RegisterOnceTimer('InitialUpdate', 'PL_UpdateData($_IPS[\'TARGET\']);');
        }
    }

    public function UpdateData(): void
    {
        $this->runLocked(function (): void
        {
            $this->doUpdate();
        });
    }

    /**
     * Ergaenzt das Archiv um alle Messungen, die dort noch fehlen. Vorhandene Werte bleiben unveraendert.
     */
    public function FillArchive(): void
    {
        $this->runLocked(function (): void
        {
            $this->doFillArchive();
        });
    }

    private function runLocked(callable $Function): void
    {
        //Timer, Schaltflaeche und Erstabruf duerfen nicht gleichzeitig Variablen anlegen
        $lock = 'PLMeasurements' . $this->InstanceID;
        if (!IPS_SemaphoreEnter($lock, 20000)) {
            $this->SendDebug(__FUNCTION__, 'Another update is still running', 0);
            return;
        }
        try {
            $Function();
        } finally {
            IPS_SemaphoreLeave($lock);
        }
    }

    private function doUpdate(): void
    {
        if (!$this->ReadPropertyBoolean('Active') || !$this->HasActiveParent()) {
            return;
        }

        $lastID = $this->ReadAttributeInteger('LastID');
        $from = ($lastID === 0) ? 0 : max(0, $this->ReadAttributeInteger('LastTimestamp') - self::OVERLAP);

        $measurements = $this->fetchMeasurements($from);
        if ($measurements === null) {
            return;
        }

        $new = array_filter($measurements, function (array $Measurement) use ($lastID): bool
        {
            return intval($Measurement['id']) > $lastID;
        });
        $this->SendDebug(__FUNCTION__, sprintf('Fetched %d measurements since %s, %d are new (last ID %d)', count($measurements), date('Y-m-d H:i:s', $from), count($new), $lastID), 0);
        if (count($new) === 0) {
            return;
        }

        $this->processMeasurements($new);
        $this->WriteAttributeInteger('LastID', max(array_map(fn (array $M): int => intval($M['id']), $new)));
        $this->WriteAttributeInteger('LastTimestamp', max($this->ReadAttributeInteger('LastTimestamp'), ...array_map(fn (array $M): int => intval($M['timestamp']), $new)));
    }

    private function doFillArchive(): void
    {
        $archiveIDs = IPS_GetInstanceListByModuleID(self::ARCHIVE_GUID);
        if (count($archiveIDs) === 0 || !$this->HasActiveParent()) {
            return;
        }
        $archiveID = $archiveIDs[0];

        $measurements = $this->fetchMeasurements(0);
        if ($measurements === null) {
            return;
        }

        $map = $this->getParameterMap();
        $byKey = [];
        foreach ($measurements as $measurement) {
            $key = $map['parameters'][strval($measurement['parameter_id'])] ?? null;
            if ($key !== null) {
                $byKey[$key][] = $measurement;
            } else {
                $this->reportUnknownParameter($measurement);
            }
        }

        $tolerance = $this->ReadPropertyInteger('UpdateInterval') + 300;
        foreach ($byKey as $key => $list) {
            $valueID = @IPS_GetObjectIDByIdent($key, $this->InstanceID);
            if ($valueID === false) {
                $this->ensureVariables($key, $list[0]);
                $valueID = $this->GetIDForIdent($key);
            }
            $commentID = $this->GetIDForIdent($key . '_Comment');

            //Ohne aktive Protokollierung liefert das Archiv keine Werte
            AC_SetLoggingStatus($archiveID, $valueID, true);
            AC_SetLoggingStatus($archiveID, $commentID, true);
            IPS_ApplyChanges($archiveID);

            $logged = AC_GetLoggedValues($archiveID, $valueID, 0, 0, 0) ?: [];
            $loggedComments = AC_GetLoggedValues($archiveID, $commentID, 0, 0, 0) ?: [];

            $values = [];
            $comments = [];
            foreach ($list as $measurement) {
                $value = $this->parseValue($measurement['value']);
                if ($value === null || abs($value) >= self::RANGE_LIMIT) {
                    continue;
                }
                $timestamp = intval($measurement['timestamp']);
                if (!$this->isLogged($logged, $timestamp, $value, $tolerance)) {
                    $values[] = ['TimeStamp' => $timestamp, 'Value' => $value];
                }
                $comment = strval($measurement['comment'] ?? '');
                if ($comment !== '' && !$this->isLogged($loggedComments, $timestamp, $comment, $tolerance)) {
                    $comments[] = ['TimeStamp' => $timestamp, 'Value' => $comment];
                }
            }

            if (count($values) > 0) {
                AC_AddLoggedValues($archiveID, $valueID, $values);
                AC_ReAggregateVariable($archiveID, $valueID);
            }
            if (count($comments) > 0) {
                AC_AddLoggedValues($archiveID, $commentID, $comments);
                AC_ReAggregateVariable($archiveID, $commentID);
            }
            $this->SendDebug(__FUNCTION__, $key . ': ' . count($values) . ' values, ' . count($comments) . ' comments added', 0);
        }
    }

    private function fetchMeasurements(int $From): ?array
    {
        $response = $this->SendDataToParent(json_encode([
            'DataID' => self::TO_ACCOUNT,
            'Buffer' => ['Command' => 'GetMeasurements', 'From' => $From]
        ]));
        $result = json_decode($response, true);

        if (!is_array($result) || !($result['ok'] ?? false)) {
            $error = is_array($result) ? strval($result['error'] ?? 'unknown error') : 'no response';
            //Eine Meldung pro Fehler, damit das Log bei einer Stoerung nicht volllaeuft
            if ($this->GetBuffer('LastError') !== $error) {
                $this->SetBuffer('LastError', $error);
                $this->LogMessage('Messwerte konnten nicht abgerufen werden: ' . $error, KL_WARNING);
            }
            return null;
        }
        $this->SetBuffer('LastError', '');

        $measurements = $result['data']['Measurements'] ?? [];
        $serial = trim($this->ReadPropertyString('DeviceSerial'));
        if ($serial !== '') {
            $measurements = array_filter($measurements, fn (array $M): bool => strval($M['device_serial']) === $serial);
        }
        return array_values($measurements);
    }

    private function processMeasurements(array $Measurements): void
    {
        $map = $this->getParameterMap();
        usort($Measurements, fn (array $A, array $B): int => intval($A['timestamp']) <=> intval($B['timestamp']));

        //Je Messgroesse zaehlt die neueste Messung
        $latest = [];
        $unknown = 0;
        foreach ($Measurements as $measurement) {
            $key = $map['parameters'][strval($measurement['parameter_id'])] ?? null;
            if ($key === null) {
                $unknown++;
                $this->reportUnknownParameter($measurement);
                continue;
            }
            $latest[$key] = $measurement;
        }

        $newest = null;
        $applied = 0;
        foreach ($latest as $key => $measurement) {
            if ($this->applyMeasurement($key, $measurement)) {
                $applied++;
                if ($newest === null || intval($measurement['timestamp']) >= intval($newest['timestamp'])) {
                    $newest = $measurement;
                }
            }
        }
        $this->SendDebug(__FUNCTION__, sprintf('%d new measurements: %d measurands updated, %d with unknown parameter ignored', count($Measurements), $applied, $unknown), 0);

        if ($newest !== null) {
            $this->SetValue('LastDevice', strval($newest['device_serial']));
            $this->SetValue('DeviceType', $this->getDeviceType(intval($newest['scenario_id'])));
        }
        if ($applied > 0) {
            $this->updateTile($this->getTileData());
        }
    }

    private function applyMeasurement(string $Key, array $Measurement): bool
    {
        $this->ensureVariables($Key, $Measurement);

        $timestamp = intval($Measurement['timestamp']);
        if ($timestamp < $this->GetValue($Key . '_Timestamp')) {
            //Aeltere Messung (nachtraeglich synchronisiert): aktuelle Anzeige bleibt
            $this->SendDebug(__FUNCTION__, $Key . ': older measurement ignored (' . date('Y-m-d H:i:s', $timestamp) . ')', 0);
            return false;
        }

        $value = $this->parseValue($Measurement['value']);
        if ($value === null) {
            $this->SendDebug(__FUNCTION__, $Key . ': value is not numeric: ' . strval($Measurement['value']), 0);
            return false;
        }

        $idealLow = $this->parseValue($Measurement['ideal_low'] ?? null);
        $idealHigh = $this->parseValue($Measurement['ideal_high'] ?? null);

        if ($value >= self::RANGE_LIMIT) {
            $state = 'overrange';
            $this->SendDebug(__FUNCTION__, $Key . ': measuring range exceeded (value ' . strval($Measurement['value']) . ')', 0);
        } elseif ($value <= -self::RANGE_LIMIT) {
            $state = 'underrange';
            $this->SendDebug(__FUNCTION__, $Key . ': below measuring range (value ' . strval($Measurement['value']) . ')', 0);
        } else {
            $this->SetValue($Key, $value);
            $state = $this->getState(strval($Measurement['ideal_status'] ?? ''), $value, $idealLow, $idealHigh);
        }

        $this->SetValue($Key . '_State', $state);
        if ($idealLow !== null) {
            $this->SetValue($Key . '_IdealLow', $idealLow);
        }
        if ($idealHigh !== null) {
            $this->SetValue($Key . '_IdealHigh', $idealHigh);
        }
        $this->SetValue($Key . '_Comment', strval($Measurement['comment'] ?? ''));
        $this->SetValue($Key . '_Timestamp', $timestamp);
        return true;
    }

    private function getState(string $IdealStatus, float $Value, ?float $IdealLow, ?float $IdealHigh): string
    {
        switch (strtoupper(trim($IdealStatus))) {
            case 'TOO LOW':
                return 'tooLow';
            case 'OKAY':
                return 'okay';
            case 'TOO HIGH':
                return 'tooHigh';
        }
        if ($IdealLow !== null && $Value < $IdealLow) {
            return 'tooLow';
        }
        if ($IdealHigh !== null && $Value > $IdealHigh) {
            return 'tooHigh';
        }
        return ($IdealLow !== null && $IdealHigh !== null) ? 'okay' : '';
    }

    private function parseValue(mixed $Value): ?float
    {
        if (is_string($Value)) {
            $Value = trim($Value);
        }
        return is_numeric($Value) ? floatval($Value) : null;
    }

    private function ensureVariables(string $Key, array $Measurement): void
    {
        if (@IPS_GetObjectIDByIdent($Key, $this->InstanceID) !== false) {
            return;
        }

        $map = $this->getParameterMap();
        $name = $map['names'][$Key] ?? $Key;
        $position = (array_search($Key, array_keys($map['names']), true) + 1) * 10;

        //Einheit und Nachkommastellen der Cloud: "mg/l (fCl₂)" bzw. "(pH)", formatted_value "0.06"
        $unit = strval($Measurement['unit'] ?? '');
        if (preg_match('/^\((.+)\)$/', $unit, $match) === 1) {
            $unit = $match[1];
        }
        $digits = 2;
        if (preg_match('/\.(\d+)$/', strval($Measurement['formatted_value'] ?? ''), $match) === 1) {
            $digits = strlen($match[1]);
        }
        $number = [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'DIGITS'       => $digits,
            'SUFFIX'       => $unit !== '' ? ' ' . $unit : ''
        ];

        $this->MaintainVariable($Key, $this->Translate($name), VARIABLETYPE_FLOAT, $number, $position, true);
        $this->MaintainVariable($Key . '_State', sprintf($this->Translate('%s state'), $this->Translate($name)), VARIABLETYPE_STRING, $this->statePresentation(), $position + 1, true);
        $this->MaintainVariable($Key . '_IdealLow', sprintf($this->Translate('%s ideal low'), $this->Translate($name)), VARIABLETYPE_FLOAT, $number, $position + 2, true);
        $this->MaintainVariable($Key . '_IdealHigh', sprintf($this->Translate('%s ideal high'), $this->Translate($name)), VARIABLETYPE_FLOAT, $number, $position + 3, true);
        $this->MaintainVariable($Key . '_Comment', sprintf($this->Translate('%s comment'), $this->Translate($name)), VARIABLETYPE_STRING, [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION
        ], $position + 4, true);
        $this->MaintainVariable($Key . '_Timestamp', sprintf($this->Translate('%s last measurement'), $this->Translate($name)), VARIABLETYPE_INTEGER, [
            'PRESENTATION' => VARIABLE_PRESENTATION_DATE_TIME,
            'DATE'         => 1,
            'TIME'         => 2
        ], $position + 5, true);
    }

    private function statePresentation(): array
    {
        $options = [];
        foreach (self::STATES as $value => [$caption, $color]) {
            $options[] = [
                'Value'              => $value,
                'Caption'            => $this->Translate($caption),
                'IconActive'         => false,
                'IconValue'          => '',
                'ColorActive'        => true,
                'ColorValue'         => $color,
                'ContentColorActive' => false,
                'ContentColorValue'  => -1
            ];
        }
        return [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'OPTIONS'      => json_encode($options)
        ];
    }

    /**
     * Daten der Kachel: je Messgroesse ein fertig formatierter Wert, die Rohwerte fuer die Skala und der Zustand.
     */
    private function getTileData(): array
    {
        $rows = [];
        foreach ($this->getParameterMap()['names'] as $key => $name) {
            $valueID = @IPS_GetObjectIDByIdent($key, $this->InstanceID);
            if ($valueID === false) {
                continue;
            }
            $state = strval($this->GetValue($key . '_State'));
            $rows[] = [
                'name'      => $this->Translate($name),
                'value'     => GetValueFormatted($valueID),
                'raw'       => $this->GetValue($key),
                'low'       => $this->GetValue($key . '_IdealLow'),
                'high'      => $this->GetValue($key . '_IdealHigh'),
                'range'     => $this->formatRange(GetValueFormatted($this->GetIDForIdent($key . '_IdealLow')), GetValueFormatted($this->GetIDForIdent($key . '_IdealHigh'))),
                'state'     => $state,
                'stateText' => isset(self::STATES[$state]) ? $this->Translate(self::STATES[$state][0]) : '',
                'time'      => $this->GetValue($key . '_Timestamp'),
                'comment'   => strval($this->GetValue($key . '_Comment'))
            ];
        }
        return [
            'rows'   => $rows,
            'device' => strval($this->GetValue('LastDevice')),
            'type'   => strval($this->GetValue('DeviceType')),
            'texts'  => [
                'device'      => $this->Translate('Last device'),
                'range'       => $this->Translate('Target range'),
                'empty'       => $this->Translate('No measurements yet')
            ]
        ];
    }

    /**
     * "0.30 mg/l" und "1.00 mg/l" werden zu "0.30 – 1.00 mg/l": die Einheit steht nur einmal.
     */
    private function formatRange(string $Low, string $High): string
    {
        $pattern = '/^(-?[\d.,]+)(.*)$/u';
        if (preg_match($pattern, $Low, $low) === 1 && preg_match($pattern, $High, $high) === 1 && $low[2] === $high[2]) {
            return $low[1] . ' – ' . $high[1] . $high[2];
        }
        return $Low . ' – ' . $High;
    }

    private function getDeviceType(int $ScenarioID): string
    {
        foreach ($this->getParameterMap()['deviceTypes'] as $type => $ranges) {
            foreach ($ranges as $range) {
                if ($ScenarioID >= $range[0] && $ScenarioID <= $range[1]) {
                    return $type;
                }
            }
        }
        return $this->Translate('Unknown');
    }

    private function reportUnknownParameter(array $Measurement): void
    {
        $id = intval($Measurement['parameter_id']);
        //Im Debug-Fenster erscheint jede ignorierte Messung mit allen Angaben, die zum Ergaenzen der parameters.json noetig sind
        $this->SendDebug('UNKNOWN PARAMETER', sprintf(
            'parameter_id=%d parameter="%s" scenario_id=%d scenario="%s" unit="%s" value=%s device=%s time=%s (add the ID to parameters.json)',
            $id,
            strval($Measurement['parameter'] ?? ''),
            intval($Measurement['scenario_id'] ?? 0),
            strval($Measurement['scenario'] ?? ''),
            strval($Measurement['unit'] ?? ''),
            strval($Measurement['value'] ?? ''),
            strval($Measurement['device_serial'] ?? ''),
            date('Y-m-d H:i:s', intval($Measurement['timestamp'] ?? 0))
        ), 0);
        $known = json_decode($this->ReadAttributeString('UnknownParameters'), true);
        if (!is_array($known) || in_array($id, $known, true)) {
            return;
        }
        $known[] = $id;
        $this->WriteAttributeString('UnknownParameters', json_encode($known));
        $this->LogMessage(sprintf('Unbekannter Parameter %d (%s, Methode %s): Messung wird ignoriert. Die ID fehlt in parameters.json.', $id, strval($Measurement['parameter']), strval($Measurement['scenario'])), KL_WARNING);
    }

    private function isLogged(array $Logged, int $Timestamp, mixed $Value, int $Tolerance): bool
    {
        foreach ($Logged as $entry) {
            if ($entry['TimeStamp'] < $Timestamp - 60 || $entry['TimeStamp'] > $Timestamp + $Tolerance) {
                continue;
            }
            if (is_float($Value) ? abs(floatval($entry['Value']) - $Value) < 0.000001 : strval($entry['Value']) === $Value) {
                return true;
            }
        }
        return false;
    }

    private function getParameterMap(): array
    {
        static $map = null;
        if ($map === null) {
            $map = json_decode(strval(file_get_contents(__DIR__ . '/parameters.json')), true);
        }
        return $map;
    }
}
