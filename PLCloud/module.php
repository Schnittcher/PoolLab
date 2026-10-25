<?php

declare(strict_types=1);

class PLCloud extends IPSModuleStrict
{
    private const ENDPOINT = 'https://backend.labcom.cloud/graphql';

    private const STATUS_TOKEN_MISSING = 201;
    private const STATUS_TOKEN_INVALID = 202;
    private const STATUS_UNREACHABLE = 203;

    private const ACCOUNT_FIELDS = 'id forename surname street zipcode city country notes volume volume_unit pooltext gps';
    private const MEASUREMENT_FIELDS = 'id account account_id scenario scenario_id parameter parameter_id unit comment device_serial value formatted_value ideal_low ideal_high ideal_status timestamp';

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();

        $this->RegisterPropertyString('Token', '');
    }

    public function ApplyChanges(): void
    {
        //Never delete this line!
        parent::ApplyChanges();

        if (trim($this->ReadPropertyString('Token')) === '') {
            $this->SetStatus(self::STATUS_TOKEN_MISSING);
            return;
        }
        $this->SetStatus(IS_ACTIVE);
    }

    public function ForwardData(string $JSONString): string
    {
        $data = json_decode($JSONString, true);
        $buffer = $data['Buffer'] ?? [];
        $command = (string) ($buffer['Command'] ?? '');
        $this->SendDebug(__FUNCTION__, 'Command: ' . $command, 0);

        switch ($command) {
            case 'GetAccounts':
                $query = '{ Accounts { ' . self::ACCOUNT_FIELDS . ' } }';
                break;
            case 'GetAccount':
                $query = '{ Accounts(id: ' . intval($buffer['AccountID'] ?? 0) . ') { ' . self::ACCOUNT_FIELDS . ' } }';
                break;
            case 'GetMeasurements':
                $query = '{ Measurements(accountId: [' . intval($buffer['AccountID'] ?? 0) . '], from: ' . max(0, intval($buffer['From'] ?? 0)) . ') { ' . self::MEASUREMENT_FIELDS . ' } }';
                break;
            case 'DosageRecommendation':
                $query = '{ DosageRecommendation(groupId: ' . intval($buffer['GroupID'] ?? 0)
                    . ', unitId: ' . intval($buffer['UnitID'] ?? 1)
                    . ', waterVolume: ' . $this->number($buffer['WaterVolume'] ?? 0)
                    . ', currentValue: ' . $this->number($buffer['CurrentValue'] ?? 0)
                    . ', targetValue: ' . $this->number($buffer['TargetValue'] ?? 0)
                    . ') { result unit WaterConditioners { id name effect phrase } } }';
                break;
            case 'ActiveChlorine':
                $query = '{ ActiveChlorine(pH: ' . $this->number($buffer['pH'] ?? 0)
                    . ', temperature: ' . $this->number($buffer['Temperature'] ?? 0)
                    . ', chlorine: ' . $this->number($buffer['Chlorine'] ?? 0)
                    . ', cya: ' . $this->number($buffer['CYA'] ?? 0)
                    . ') { HOCl } }';
                break;
            default:
                $this->SendDebug(__FUNCTION__, 'Invalid command: ' . $command, 0);
                return json_encode(['ok' => false, 'error' => 'Invalid command']);
        }

        $result = $this->sendRequest($query);

        //Die Cloud liefert trotz accountId-Argument die Messungen aller Konten: nach dem Konto filtern
        if ($command === 'GetMeasurements' && $result['ok']) {
            $accountID = intval($buffer['AccountID'] ?? 0);
            $result['data']['Measurements'] = array_values(array_filter(
                $result['data']['Measurements'] ?? [],
                fn (array $Measurement): bool => intval($Measurement['account_id']) === $accountID
            ));
        }

        return json_encode($result);
    }

    public function TestConnection(): string
    {
        if (trim($this->ReadPropertyString('Token')) === '') {
            return $this->Translate('The API token is missing.');
        }
        $result = $this->sendRequest('{ Accounts { id } }');
        if (!$result['ok']) {
            return $this->Translate('Connection failed:') . ' ' . $result['error'];
        }
        $count = count($result['data']['Accounts'] ?? []);
        return sprintf($this->Translate('Connection successful, %d account(s) found.'), $count);
    }

    private function sendRequest(string $Query): array
    {
        $this->SendDebug('Query', $Query, 0);

        $ch = curl_init(self::ENDPOINT);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(['query' => $Query]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json', 'Authorization: ' . $this->ReadPropertyString('Token')],
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 30
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            $this->SetStatus(self::STATUS_UNREACHABLE);
            $this->SendDebug('Error', $curlError, 0);
            return ['ok' => false, 'error' => 'Cloud not reachable: ' . $curlError];
        }
        if ($httpCode === 401 || $httpCode === 403) {
            $this->SetStatus(self::STATUS_TOKEN_INVALID);
            return ['ok' => false, 'error' => 'API token rejected (HTTP ' . $httpCode . ')'];
        }
        if ($httpCode !== 200) {
            $this->SetStatus(self::STATUS_UNREACHABLE);
            return ['ok' => false, 'error' => 'HTTP ' . $httpCode];
        }

        $this->SendDebug('Response', substr($response, 0, 2000), 0);
        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return ['ok' => false, 'error' => 'Invalid response'];
        }
        if (!empty($decoded['errors'])) {
            $message = (string) ($decoded['errors'][0]['message'] ?? 'Unknown error');
            $this->SendDebug('GraphQL error', $message, 0);
            return ['ok' => false, 'error' => $message];
        }

        $this->SetStatus(IS_ACTIVE);
        return ['ok' => true, 'data' => $decoded['data'] ?? []];
    }

    private function number(mixed $Value): string
    {
        return number_format((float) $Value, 2, '.', '');
    }
}
