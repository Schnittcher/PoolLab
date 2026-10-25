<?php

declare(strict_types=1);

class PLAccount extends IPSModuleStrict
{
    private const CLOUD_GUID = '{229DD642-B66F-C8F0-A2F3-55CD927F9D8C}';
    private const TO_CLOUD = '{76339EB6-91E7-B97F-159C-71F769BA285A}';

    private const STATUS_ACCOUNT_MISSING = 201;

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();

        $this->ConnectParent(self::CLOUD_GUID);
        $this->RegisterPropertyInteger('AccountID', 0);
    }

    public function ApplyChanges(): void
    {
        //Never delete this line!
        parent::ApplyChanges();

        $this->MaintainVariable('PoolVolume', $this->Translate('Pool volume'), VARIABLETYPE_FLOAT, [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION,
            'DIGITS'       => 1,
            'SUFFIX'       => ' m³'
        ], 1, true);

        if ($this->ReadPropertyInteger('AccountID') <= 0) {
            $this->SetStatus(self::STATUS_ACCOUNT_MISSING);
            return;
        }
        $this->SetStatus(IS_ACTIVE);

        if (IPS_GetKernelRunlevel() === KR_READY && $this->HasActiveParent()) {
            $this->RegisterOnceTimer('UpdateAccount', 'PL_UpdateAccount($_IPS[\'TARGET\']);');
        }
    }

    public function ForwardData(string $JSONString): string
    {
        $data = json_decode($JSONString, true);
        $buffer = $data['Buffer'] ?? [];
        //Alle Anfragen der Kind-Instanzen laufen im Kontext dieses Pools (Accounts)
        $buffer['AccountID'] = $this->ReadPropertyInteger('AccountID');
        $this->SendDebug(__FUNCTION__, 'Command: ' . ($buffer['Command'] ?? ''), 0);

        return $this->sendToCloud($buffer);
    }

    public function UpdateAccount(): void
    {
        $result = json_decode($this->sendToCloud([
            'Command'   => 'GetAccount',
            'AccountID' => $this->ReadPropertyInteger('AccountID')
        ]), true);
        if (!($result['ok'] ?? false)) {
            $this->SendDebug(__FUNCTION__, 'Request failed: ' . ($result['error'] ?? 'no response'), 0);
            return;
        }
        $account = $result['data']['Accounts'][0] ?? null;
        if ($account === null) {
            $this->SendDebug(__FUNCTION__, 'Account not found', 0);
            return;
        }
        $this->SetValue('PoolVolume', (float) $account['volume']);
    }

    private function sendToCloud(array $Buffer): string
    {
        if (!$this->HasActiveParent()) {
            return json_encode(['ok' => false, 'error' => 'Cloud instance not active']);
        }
        $response = $this->SendDataToParent(json_encode(['DataID' => self::TO_CLOUD, 'Buffer' => $Buffer]));
        if (!is_string($response) || $response === '') {
            return json_encode(['ok' => false, 'error' => 'No response from the cloud instance']);
        }
        return $response;
    }
}
