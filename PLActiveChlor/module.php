<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/PoolLabTile.php';

class PLActiveChlor extends IPSModuleStrict
{
    use PoolLabTile;

    private const ACCOUNT_GUID = '{5003D1FE-D820-150D-E709-8363BAE2CE11}';
    private const TO_ACCOUNT = '{962271EC-68EA-19B9-2732-49D5D8EEBC45}';

    //Eingaben: Ident, Bezeichnung (englisches Original), Minimum, Maximum, Schrittweite, Einheit
    private const FIELDS = [
        ['pH', 'pH', 0, 14, 0.1, ''],
        ['temperature', 'Temperature', 0, 80, 1, '°C'],
        ['chlorine', 'Chlorine', 0, 50, 0.1, 'mg/l'],
        ['cya', 'CYA', 0, 500, 1, 'mg/l']
    ];

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();

        $this->setTileType();
    }

    public function GetCompatibleParents(): string
    {
        //IPSModuleStrict kennt kein ConnectParent: die Konsole verbindet ueber diese Angabe, fehlt der Parent, wird er angelegt
        return json_encode(['type' => 'connect', 'moduleIDs' => [self::ACCOUNT_GUID]]);
    }

    public function ApplyChanges(): void
    {
        //Never delete this line!
        parent::ApplyChanges();

        $this->MaintainVariable('pH', $this->Translate('pH'), VARIABLETYPE_FLOAT, [
            'PRESENTATION' => VARIABLE_PRESENTATION_SLIDER,
            'MIN'          => 0,
            'MAX'          => 14,
            'STEP_SIZE'    => 0.1,
            'DIGITS'       => 1,
            'USAGE_TYPE'   => 0
        ], 0, true);
        $this->MaintainVariable('temperature', $this->Translate('Temperature'), VARIABLETYPE_INTEGER, [
            'PRESENTATION' => VARIABLE_PRESENTATION_SLIDER,
            'MIN'          => 0,
            'MAX'          => 80,
            'STEP_SIZE'    => 1,
            'SUFFIX'       => ' °C',
            'USAGE_TYPE'   => 0
        ], 1, true);
        $this->MaintainVariable('chlorine', $this->Translate('Chlorine'), VARIABLETYPE_FLOAT, [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_INPUT,
            'SUFFIX'       => ' mg/l'
        ], 2, true);
        $this->MaintainVariable('cya', $this->Translate('CYA'), VARIABLETYPE_FLOAT, [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_INPUT,
            'SUFFIX'       => ' mg/l'
        ], 3, true);
        $this->MaintainVariable('action', $this->Translate('Action'), VARIABLETYPE_STRING, [
            'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,
            'OPTIONS'      => json_encode([[
                'Value'      => 'calculate',
                'Caption'    => $this->Translate('Calculate'),
                'IconActive' => false,
                'IconValue'  => '',
                'Color'      => 65280
            ]])
        ], 4, true);
        $this->MaintainVariable('activeChlorine', $this->Translate('Active Chlorine'), VARIABLETYPE_FLOAT, [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'DIGITS'       => 5,
            'SUFFIX'       => ' mg/l HOCl'
        ], 5, true);

        foreach (['pH', 'temperature', 'chlorine', 'cya', 'action'] as $ident) {
            $this->EnableAction($ident);
        }
        $this->SetStatus(IS_ACTIVE);
    }

    public function GetVisualizationTile(): string
    {
        return $this->buildTile(__DIR__ . '/module.html', $this->getTileData());
    }

    public function RequestAction(string $Ident, mixed $Value): void
    {
        switch ($Ident) {
            case 'pH':
            case 'chlorine':
            case 'cya':
                $this->SetValue($Ident, floatval($Value));
                break;
            case 'temperature':
                $this->SetValue($Ident, intval($Value));
                break;
            case 'action':
                $this->SetValue($Ident, strval($Value));
                $this->calculate();
                break;
            default:
                $this->SendDebug(__FUNCTION__, 'Invalid ident: ' . $Ident, 0);
                return;
        }
        $this->updateTile($this->getTileData());
    }

    private function calculate(): void
    {
        $response = $this->SendDataToParent(json_encode([
            'DataID' => self::TO_ACCOUNT,
            'Buffer' => [
                'Command'     => 'ActiveChlorine',
                'pH'          => $this->GetValue('pH'),
                'Temperature' => $this->GetValue('temperature'),
                'Chlorine'    => $this->GetValue('chlorine'),
                'CYA'         => $this->GetValue('cya')
            ]
        ]));
        $result = json_decode($response, true);
        if (!is_array($result) || !($result['ok'] ?? false)) {
            $this->SendDebug(__FUNCTION__, 'Request failed', 0);
            return;
        }
        $this->SetValue('activeChlorine', floatval($result['data']['ActiveChlorine']['HOCl'] ?? 0));
    }

    private function getTileData(): array
    {
        $fields = [];
        foreach (self::FIELDS as [$ident, $label, $min, $max, $step, $suffix]) {
            $fields[] = [
                'ident'  => $ident,
                'label'  => $this->Translate($label),
                'value'  => $this->GetValue($ident),
                'min'    => $min,
                'max'    => $max,
                'step'   => $step,
                'suffix' => $suffix
            ];
        }
        return [
            'fields' => $fields,
            'result' => GetValueFormatted($this->GetIDForIdent('activeChlorine')),
            'texts'  => [
                'calculate' => $this->Translate('Calculate'),
                'result'    => $this->Translate('Active Chlorine')
            ]
        ];
    }
}
