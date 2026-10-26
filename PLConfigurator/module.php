<?php

declare(strict_types=1);

class PLConfigurator extends IPSModuleStrict
{
    private const CLOUD_GUID = '{229DD642-B66F-C8F0-A2F3-55CD927F9D8C}';
    private const TO_CLOUD = '{76339EB6-91E7-B97F-159C-71F769BA285A}';

    private const ACCOUNT_MODULE = '{5003D1FE-D820-150D-E709-8363BAE2CE11}';
    private const MEASUREMENTS_MODULE = '{9875F7F8-30F8-0978-4856-54EFA62B821E}';
    private const DOSAGE_MODULE = '{C142014F-282A-4AC4-8D6F-AA35648FB773}';
    private const ACTIVE_CHLOR_MODULE = '{741F0852-47FA-4865-B90D-CE87A386F29C}';

    public function GetCompatibleParents(): string
    {
        //IPSModuleStrict kennt kein RequireParent: die Konsole verbindet ueber diese Angabe, fehlt der Parent, wird er angelegt
        return json_encode(['type' => 'connect', 'moduleIDs' => [self::CLOUD_GUID]]);
    }

    public function GetConfigurationForm(): string
    {
        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $form['actions'][0]['values'] = $this->getValues();
        return json_encode($form);
    }

    private function getValues(): array
    {
        if (!$this->HasActiveParent()) {
            return [];
        }
        $response = $this->SendDataToParent(json_encode(['DataID' => self::TO_CLOUD, 'Buffer' => ['Command' => 'GetAccounts']]));
        $result = json_decode($response, true);
        if (!is_array($result) || !($result['ok'] ?? false)) {
            $this->SendDebug(__FUNCTION__, 'Accounts could not be read', 0);
            return [];
        }

        $values = [];
        $childID = 9000;
        foreach ($result['data']['Accounts'] ?? [] as $account) {
            $accountID = intval($account['id']);
            $values[] = [
                'id'          => $accountID,
                'parent'      => 0,
                'DisplayName' => trim(strval($account['forename']) . ' ' . strval($account['surname'])),
                'City'        => strval($account['city'] ?? ''),
                'Volume'      => strval($account['volume'] ?? '')
            ];
            foreach ([
                ['Measurements', self::MEASUREMENTS_MODULE],
                ['Dosage Recommendation', self::DOSAGE_MODULE],
                ['Active Chlor', self::ACTIVE_CHLOR_MODULE]
            ] as [$caption, $moduleID]) {
                $values[] = [
                    'id'          => $childID++,
                    'parent'      => $accountID,
                    'DisplayName' => $this->Translate($caption),
                    'City'        => '',
                    'Volume'      => '',
                    'instanceID'  => $this->getInstanceID($accountID, $moduleID),
                    'create'      => [
                        [
                            'name'          => $this->Translate($caption),
                            'moduleID'      => $moduleID,
                            'configuration' => new stdClass()
                        ],
                        [
                            'moduleID'      => self::ACCOUNT_MODULE,
                            'configuration' => ['AccountID' => $accountID]
                        ],
                        //Ohne ConnectParent muss die Kette bis zum Cloud-Gateway reichen, ein vorhandenes wird wiederverwendet
                        [
                            'moduleID'      => self::CLOUD_GUID,
                            'configuration' => new stdClass()
                        ]
                    ]
                ];
            }
        }
        return $values;
    }

    private function getInstanceID(int $AccountID, string $ModuleID): int
    {
        foreach (IPS_GetInstanceListByModuleID($ModuleID) as $instanceID) {
            $connectionID = IPS_GetInstance($instanceID)['ConnectionID'];
            if ($connectionID > 0 && IPS_GetProperty($connectionID, 'AccountID') === $AccountID) {
                return $instanceID;
            }
        }
        return 0;
    }
}
