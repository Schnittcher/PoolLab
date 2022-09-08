<?php

declare(strict_types=1);
    class PLMeasurements extends IPSModule
    {
        public static $Variables = [
            ['PL_pH', 'PL pH', VARIABLETYPE_FLOAT, '', false, true],
            ['PL_pH_Comment', 'PL pH', VARIABLETYPE_STRING, '', false, true],
            ['PL_Chlorine_Total', 'PL Chlorine Total', VARIABLETYPE_FLOAT, '', false, true],
            ['PL_Chlorine_Total_Comment', 'PL Chlorine Total Comment', VARIABLETYPE_STRING, '', false, true],
            ['PL_Chlorine_Free', 'PL Chlorine Free', VARIABLETYPE_FLOAT, '', false, true],
            ['PL_Chlorine_Free_Comment', 'PL Chlorine Free Comment', VARIABLETYPE_STRING, '', false, true],
        ];

        public function Create()
        {
            //Never delete this line!
            parent::Create();

            $this->RequireParent('{5003D1FE-D820-150D-E709-8363BAE2CE11}');

            $Variables = [];
            foreach (static::$Variables as $Pos => $Variable) {
                $Variables[] = [
                    'Ident'        => str_replace(' ', '', $Variable[0]),
                    'Name'         => $this->Translate($Variable[1]),
                    'VarType'      => $Variable[2],
                    'Profile'      => $Variable[3],
                    'Action'       => $Variable[4],
                    'Pos'          => $Pos + 1,
                    'Keep'         => $Variable[5]
                ];
            }
            $this->RegisterPropertyString('Variables', json_encode($Variables));
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

            $NewRows = static::$Variables;
            $NewPos = 0;
            $Variables = json_decode($this->ReadPropertyString('Variables'), true);
            foreach ($Variables as $Variable) {
                @$this->MaintainVariable($Variable['Ident'], $Variable['Name'], $Variable['VarType'], $Variable['Profile'], $Variable['Pos'], $Variable['Keep']);
                if ($Variable['Action'] && $Variable['Keep']) {
                    $this->EnableAction($Variable['Ident']);
                }
                foreach ($NewRows as $Index => $Row) {
                    if ($Variable['Ident'] == str_replace(' ', '', $Row[0])) {
                        unset($NewRows[$Index]);
                    }
                }
                if ($NewPos < $Variable['Pos']) {
                    $NewPos = $Variable['Pos'];
                }
            }

            if (count($NewRows) != 0) {
                foreach ($NewRows as $NewVariable) {
                    $Variables[] = [
                        'Ident'        => str_replace(' ', '', $NewVariable[0]),
                        'Name'         => $this->Translate($NewVariable[1]),
                        'VarType'      => $NewVariable[2],
                        'Profile'      => $NewVariable[3],
                        'Action'       => $NewVariable[4],
                        'Pos'          => ++$NewPos,
                        'Keep'         => $NewVariable[5]
                    ];
                }
                IPS_SetProperty($this->InstanceID, 'Variables', json_encode($Variables));
                IPS_ApplyChanges($this->InstanceID);
                return;
            }
        }
        public function updateData()
        {
            $Variables = json_decode($this->ReadPropertyString('Variables'), true);
            foreach ($Variables as $key => $variable) {
                if (strpos($variable['Ident'], '_Comment') !== false) {
                    continue;
                }
                if (!$variable['Keep']) {
                    continue;
                }
                $parameterName = str_replace('_', ' ', $variable['Ident']);

                $measurements = $this->updateMeasurements(0, 0, $parameterName);
                if (empty($measurements)) {
                    $this->SendDebug('updateData :: no data', $variable['Ident'], 0);
                    continue;
                }

                print_r($measurements);
                $this->SendDebug('Update :: ' . $variable['Ident'], $measurements[0]['value'], 0);
                $this->SetValue($variable['Ident'], $measurements[0]['value']);
                $this->SetValue($variable['Ident'] . '_Comment', $measurements[0]['comment']);
            }
        }

        public function updateMeasurements(int $StartTime, int $EndTime, string $parameterName)
        {
            $Data = [];
            $Buffer = [];

            $Data['DataID'] = '{962271EC-68EA-19B9-2732-49D5D8EEBC45}';
            $Buffer['Command'] = 'GetMeasurements';
            $Buffer['StartTime'] = $StartTime;
            $Buffer['EndTime'] = $EndTime;
            $Buffer['ParameterName'] = $parameterName;
            $Data['Buffer'] = $Buffer;
            $Data = json_encode($Data);
            $results = json_decode($this->SendDataToParent($Data), true);
            if (!$results) {
                return [];
            }
            $Measurements = $results['data']['Measurements'];

            //Sortiere nach timestamp absteigend
            $timestamp = array_column($Measurements, 'timestamp');
            array_multisort($timestamp, SORT_DESC, $Measurements);

            return $Measurements;
        }
    }