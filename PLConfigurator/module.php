<?php

declare(strict_types=1);
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
            $AddValue = [];
            foreach ($Accounts['Accounts'] as $key => $Account) {
                $Values[] = [
                    'id'                             => $Account['id'],
                    'DisplayName'                    => $Account['forename'] . ' ' . $Account['surname'],
                    'Forename'                       => $Account['forename'],
                    'Surname'                        => $Account['surname'],
                    'Street'                         => $Account['street'],
                    'Zipcode'                        => $Account['zipcode'],
                    'City'                           => $Account['city'],
                    'Phone1'                         => $Account['phone1'],
                    'Phone2'                         => $Account['phone2'],
                    'Fax'                            => $Account['fax'],
                    'Email'                          => $Account['email'],
                    'Country'                        => $Account['country'],
                    'Canton'                         => $Account['canton'],
                    'Notes'                          => $Account['notes'],
                    'Volume'                         => $Account['volume'],
                    'Pooltext'                       => $Account['pooltext'],
                    'GPS'                            => $Account['gps'],
                    'create'                         => [
                        [
                            'moduleID'      => '{9875F7F8-30F8-0978-4856-54EFA62B821E}',
                            'configuration' => new stdClass()
                        ],
                        [
                            'moduleID'      => '{5003D1FE-D820-150D-E709-8363BAE2CE11}', //Splitter PLAccount
                            'configuration' => [
                                'AccountID' => $Account['id']
                            ]
                        ]
                    ]

                ];
            }
            $Form['actions'][0]['values'] = $Values;
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
    }