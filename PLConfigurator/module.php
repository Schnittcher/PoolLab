<?php

declare(strict_types=1);
define('PL_ACCOUNT', '{5003D1FE-D820-150D-E709-8363BAE2CE11}');
define('PL_MEASUREMENTS', '{9875F7F8-30F8-0978-4856-54EFA62B821E}');
define('PL_DOSAGE_RECOMMENDATION', '{C142014F-282A-4AC4-8D6F-AA35648FB773}');
define('PL_ACTIVE_CHLOR', '{741F0852-47FA-4865-B90D-CE87A386F29C}');

    class Configurator extends IPSModule
    {
        public function Create()
        {
            //Never delete this line!
            parent::Create();
            $this->RequireParent('{229DD642-B66F-C8F0-A2F3-55CD927F9D8C}');
        }

        public function Destroy()
        {
            //Never delete this line!
            parent::Destroy();
        }

        public function ApplyChanges()
        {
            //Never delete this line!
            parent::ApplyChanges();
        }
        private function isNull(&$value)
        {
            $value = $value ?? '';
        }

        public function GetConfigurationForm()
        {
            $Form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);

            $Accounts = $this->getAccounts();

            if (!array_key_exists('data', $Accounts)) {
                $Accounts = [];
            } else {
                $Accounts = $Accounts['data'];
            }

            $Values = [];
            $id = 9000;
            foreach ($Accounts['Accounts'] as $key => &$Account) {

                array_walk($Account, [$this, 'isNull']);
                $Values[] = [
                    'id'                                 => intval($Account['id']),
                    'parent'                             => 0,
                    'DisplayName'                        => $Account['forename'] . ' ' . $Account['surname'],
                    'Forename'                           => $Account['forename'],
                    'Surname'                            => $Account['surname'],
                    'Street'                             => $Account['street'],
                    'Zipcode'                            => $Account['zipcode'],
                    'City'                               => $Account['city'],
                    'Phone1'                             => $Account['phone1'],
                    'Phone2'                             => $Account['phone2'],
                    'Fax'                                => $Account['fax'],
                    'EMail'                              => $Account['email'],
                    'Country'                            => $Account['country'],
                    'Canton'                             => $Account['canton'],
                    'Notes'                              => $Account['notes'],
                    'Volume'                             => $Account['volume'],
                    'Pooltext'                           => $Account['pooltext'],
                    'GPS'                                => $Account['gps']
                ];
                $Values[] = [
                    'id'                             => $id,
                    'parent'                         => intval($Account['id']),
                    'DisplayName'                    => $this->Translate('Measurements'),
                    'Forename'                       => '',
                    'Surname'                        => '',
                    'Street'                         => '',
                    'Zipcode'                        => '',
                    'City'                           => '',
                    'Phone1'                         => '',
                    'Phone2'                         => '',
                    'Fax'                            => '',
                    'EMail'                          => '',
                    'Country'                        => '',
                    'Canton'                         => '',
                    'Notes'                          => '',
                    'Volume'                         => '',
                    'Pooltext'                       => '',
                    'GPS'                            => '',
                    'instanceID'                     => $this->getInstanceID($Account['id'], PL_MEASUREMENTS),
                    'create'                         => [
                        [
                            'name'          => $this->Translate('Measurements'),
                            'moduleID'      => PL_MEASUREMENTS,
                            'configuration' => new stdClass()
                        ],
                        [
                            'moduleID'      => PL_ACCOUNT, //Splitter PLAccount
                            'configuration' => [
                                'AccountID' => $Account['id']
                            ]
                        ]
                    ]
                ];
                $id++;
                $Values[] = [
                    'id'                             => $id,
                    'parent'                         => intval($Account['id']),
                    'DisplayName'                    => $this->Translate('Dosage Recommendation'),
                    'Forename'                       => '',
                    'Surname'                        => '',
                    'Street'                         => '',
                    'Zipcode'                        => '',
                    'City'                           => '',
                    'Phone1'                         => '',
                    'Phone2'                         => '',
                    'Fax'                            => '',
                    'EMail'                          => '',
                    'Country'                        => '',
                    'Canton'                         => '',
                    'Notes'                          => '',
                    'Volume'                         => '',
                    'Pooltext'                       => '',
                    'GPS'                            => '',
                    'instanceID'                     => $this->getInstanceID($Account['id'], PL_DOSAGE_RECOMMENDATION),
                    'create'                         => [
                        [
                            'name'          => $this->Translate('Dosage Recommendation'),
                            'moduleID'      => PL_DOSAGE_RECOMMENDATION,
                            'configuration' => new stdClass()
                        ],
                        [
                            'moduleID'      => PL_ACCOUNT, //Splitter PLAccount
                            'configuration' => [
                                'AccountID' => $Account['id']
                            ]
                        ]
                    ]
                ];
                $id++;
                $Values[] = [
                    'id'                             => $id,
                    'parent'                         => intval($Account['id']),
                    'DisplayName'                    => $this->Translate('Active Chlor'),
                    'Forename'                       => '',
                    'Surname'                        => '',
                    'Street'                         => '',
                    'Zipcode'                        => '',
                    'City'                           => '',
                    'Phone1'                         => '',
                    'Phone2'                         => '',
                    'Fax'                            => '',
                    'EMail'                          => '',
                    'Country'                        => '',
                    'Canton'                         => '',
                    'Notes'                          => '',
                    'Volume'                         => '',
                    'Pooltext'                       => '',
                    'GPS'                            => '',
                    'instanceID'                     => $this->getInstanceID($Account['id'], PL_ACTIVE_CHLOR),
                    'create'                         => [
                        [
                            'name'          => $this->Translate('Dosage Recommendation'),
                            'moduleID'      => PL_ACTIVE_CHLOR,
                            'configuration' => new stdClass()
                        ],
                        [
                            'moduleID'      => PL_ACCOUNT, //Splitter PLAccount
                            'configuration' => [
                                'AccountID' => $Account['id']
                            ]
                        ]
                    ]
                ];
                $id++;
            }
            $Form['actions'][0]['values'] = $Values;
            IPS_LogMessage('test',json_encode($Form));

            return json_encode($Form);
        }

        public function getAccounts()
        {
            $Data = [];
            $Buffer = [];

            $Data['DataID'] = '{76339EB6-91E7-B97F-159C-71F769BA285A}';
            $Buffer['Command'] = 'getAccounts';
            $Buffer['Params'] = '';
            $Data['Buffer'] = $Buffer;
            $Data = json_encode($Data);
            $result = json_decode($this->SendDataToParent($Data), true);
            if (!$result) {
                return [];
            }
            return $result;
        }

        private function getInstanceID($accountID, $moduleID)
        {
            $InstanceIDs = IPS_GetInstanceListByModuleID($moduleID);
            foreach ($InstanceIDs as $ID) {
                $ConnectionID = IPS_GetInstance($ID)['ConnectionID'];
                if (IPS_GetProperty($ConnectionID, 'AccountID') == $accountID) {
                    return $ID;
                }
            }
            return 0;
        }
    }