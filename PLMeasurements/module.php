<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php';

    class PLMeasurements extends IPSModule
    {
        use VariableProfileHelper;

        public static $Variables = [
            ['PL_pH', 'pH', VARIABLETYPE_FLOAT, 'PoolLab.pH', false, true],
            ['PL_pH_State', 'pH State', VARIABLETYPE_STRING, 'PoolLab.State', false, true],
            ['PL_pH_IdealLow', 'pH ideal low', VARIABLETYPE_FLOAT, 'PoolLab.pH', false, true],
            ['PL_pH_IdealHigh', 'pH ideal high', VARIABLETYPE_FLOAT, 'PoolLab.pH', false, true],
            ['PL_pH_Comment', 'pH Comment', VARIABLETYPE_STRING, '', false, true],
            ['PL_pH_Timestamp', 'pH last measurement', VARIABLETYPE_INTEGER, '~UnixTimestamp', false, true],           
            ['PL_Chlorine_Total', 'Chlorine Total', VARIABLETYPE_FLOAT, 'PoolLab.mgl', false, true],
            ['PL_Chlorine_Total_State', 'Chlorine Total State', VARIABLETYPE_STRING, 'PoolLab.State', false, true],
            ['PL_Chlorine_Total_IdealLow', 'Chlorine Total ideal low', VARIABLETYPE_FLOAT, 'PoolLab.mgl', false, true],
            ['PL_Chlorine_Total_IdealHigh', 'Chlorine Total ideal high', VARIABLETYPE_FLOAT, 'PoolLab.mgl', false, true],
            ['PL_Chlorine_Total_Comment', 'Chlorine Total Comment', VARIABLETYPE_STRING, '', false, true],
            ['PL_Chlorine_Total_Timestamp', 'Chlorine Total last measurement', VARIABLETYPE_INTEGER, '~UnixTimestamp', false, true],
            ['PL_Chlorine_Free', 'Chlorine Free', VARIABLETYPE_FLOAT, 'PoolLab.mgl', false, true],
            ['PL_Chlorine_Free_State', 'Chlorine Free State', VARIABLETYPE_STRING, 'PoolLab.State', false, true],
            ['PL_Chlorine_Free_IdealLow', 'Chlorine Free ideal low', VARIABLETYPE_FLOAT, 'PoolLab.mgl', false, true],
            ['PL_Chlorine_Free_IdealHigh', 'Chlorine Free ideal high', VARIABLETYPE_FLOAT, 'PoolLab.mgl', false, true],
            ['PL_Chlorine_Free_Comment', 'Chlorine Free Comment', VARIABLETYPE_STRING, '', false, true],
            ['PL_Chlorine_Free_Timestamp', 'Chlorine Free last measurement', VARIABLETYPE_INTEGER, '~UnixTimestamp', false, true],
            ['PL_TAlka', 'Alkalinity', VARIABLETYPE_FLOAT, '', false, true],
            ['PL_TAlka_State', 'Alkalinity State', VARIABLETYPE_STRING, 'PoolLab.State', false, true],
            ['PL_TAlka_IdealLow', 'Alkalinity ideal low', VARIABLETYPE_FLOAT, '', false, true],
            ['PL_TAlka_IdealHigh', 'Alkalinity ideal high', VARIABLETYPE_FLOAT, '', false, true],
            ['PL_TAlka_Comment', 'Alkalinity Comment', VARIABLETYPE_STRING, '', false, true],
            ['PL_TAlka_Timestamp', 'Alkalinity last measurement', VARIABLETYPE_INTEGER, '~UnixTimestamp', false, true],
            ['PL_Cyanuric_Acid', 'Cyanuric Acid', VARIABLETYPE_FLOAT, '', false, true],
            ['PL_Cyanuric_Acid_State', 'Cyanuric Acid State', VARIABLETYPE_STRING, 'PoolLab.State', false, true],
            ['PL_Cyanuric_Acid_IdealLow', 'Cyanuric Acid ideal low', VARIABLETYPE_FLOAT, '', false, true],
            ['PL_Cyanuric_Acid_IdealHigh', 'Cyanuric Acid ideal high', VARIABLETYPE_FLOAT, '', false, true],
            ['PL_Cyanuric_Acid_Comment', 'Cyanuric Acid Comment', VARIABLETYPE_STRING, '', false, true],
            ['PL_Cyanuric_Acid_Timestamp', 'Cyanuric Acid last measurement', VARIABLETYPE_INTEGER, '~UnixTimestamp', false, true],
        ];

        public function Create()
        {
            //Never delete this line!
            parent::Create();

            $this->ConnectParent('{5003D1FE-D820-150D-E709-8363BAE2CE11}');

            $this->RegisterPropertyBoolean('Active', true);

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

            if (!IPS_VariableProfileExists('PoolLab.pH')) {
                $this->RegisterProfileFloat('PoolLab.pH', 'Information', '', ' pH', 0, 0, 0.01, 2);
            }
            if (!IPS_VariableProfileExists('PoolLab.mgl')) {
                $this->RegisterProfileFloat('PoolLab.mgl', 'Information', '', ' mg/l', 0, 0, 0.01, 2);
            }
            if (!IPS_VariableProfileExists('PoolLab.State')) {
                $this->RegisterProfileStringEx('PoolLab.State', 'Information', '', '', [
                    ['toLow', $this->Translate('to low'), '', 0xFF0000],
                    ['okay', $this->Translate('okay'), '', 0x008800],
                    ['toHigh', $this->Translate('to high'), '', 0xFF0000],
                ]);
            }

            if (!IPS_VariableProfileExists('PoolLab.Update')) {
                $this->RegisterProfileStringEx('PoolLab.Update', 'Information', '', '', [
                    ['update_data', $this->Translate('Update data'), '', 0x00FF00]
                ]);
            }

            $this->RegisterVariableString('action', $this->Translate('Action'), 'PoolLab.Update', 4);
            $this->EnableAction('action');

            $this->RegisterPropertyInteger('UpdateInterval', 3600);
            $this->RegisterTimer('PL_updateMeasurements', 0, 'PL_updateData($_IPS[\'TARGET\'],false);');
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

            if ($this->ReadPropertyBoolean('Active')) {
                $this->SetTimerInterval('PL_updateMeasurements', $this->ReadPropertyInteger('UpdateInterval') * 1000);
                $this->SetStatus(102);
            } else {
                $this->SetTimerInterval('PL_updateMeasurements', 0);
                $this->SetStatus(104);
            }
        }

        public function RequestAction($Ident, $Value)
        {
            switch ($Ident) {
                case 'action':
                    $this->updateData();
                    break;
                default:
                    break;
                }
        }

        public function updateData(bool $archive = false)
        {
            $Variables = json_decode($this->ReadPropertyString('Variables'), true);
            foreach ($Variables as $key => $variable) {
                if (!$variable['Keep']) {
                    continue;
                }
                if (strpos($variable['Ident'], '_Comment') !== false) {
                    continue;
                }
                $parameterName = str_replace('_', ' ', $variable['Ident']);
                //Sonderfall Minus im ParameterName
                $parameterName = str_replace('PL TAlka', 'PL T-Alka', $parameterName);

                $measurements = $this->updateMeasurements(0, 0, $parameterName);
                if (empty($measurements)) {
                    $this->SendDebug('updateData :: no data', $variable['Ident'], 0);
                    continue;
                }

                if ($archive) {
                    $archiveID = IPS_GetInstanceListByModuleID('{43192F0B-135B-4CE7-A0A7-1475603F3060}')[0];
                    $Values = [];
                    $ValuesComments = [];
                    foreach ($measurements as $key => $measurement) {
                        switch ($variable['VarType']) {
                            case VARIABLETYPE_FLOAT:
                                $Value = floatval($measurement['value']);
                                break;
                            case VARIABLETYPE_INTEGER:
                                $Value = intval($measurement['value']);
                                break;
                        }

                        $Values[] = [
                            'TimeStamp' => $measurement['timestamp'],
                            'Value'     => $Value
                        ];
                        if (!empty($measurement['comment'])) {
                            $ValuesComments[] = [
                                'TimeStamp' => $measurement['timestamp'],
                                'Value'     => strval($measurement['comment'])
                            ];
                        }
                    }

                    //Löscht alle Daten für die Value Variable aus dem Archiv
                    AC_DeleteVariableData($archiveID, $this->GetIDForIdent($variable['Ident']), 0, 0);
                    AC_SetLoggingStatus($archiveID, $this->GetIDForIdent($variable['Ident']), true);

                    //Löscht alle Daten für die Comment Variable aus dem Archiv
                    AC_DeleteVariableData($archiveID, $this->GetIDForIdent($variable['Ident'] . '_Comment'), 0, 0);
                    AC_SetLoggingStatus($archiveID, $this->GetIDForIdent($variable['Ident'] . '_Comment'), true);

                    //Values
                    AC_AddLoggedValues($archiveID, $this->GetIDForIdent($variable['Ident']), $Values);
                    AC_ReAggregateVariable($archiveID, $this->GetIDForIdent($variable['Ident']));

                    //Comments to Values
                    AC_AddLoggedValues($archiveID, $this->GetIDForIdent($variable['Ident'] . '_Comment'), $ValuesComments);
                    AC_ReAggregateVariable($archiveID, $this->GetIDForIdent($variable['Ident'] . '_Comment'));

                    //Logging deaktivieren
                    //AC_SetLoggingStatus($archiveID, $this->GetIDForIdent($variable['Ident']), false);
                    //AC_SetLoggingStatus($archiveID, $this->GetIDForIdent($variable['Ident'] . '_Comment'), false);
                }

                $this->SendDebug('Update :: ' . $variable['Ident'], $measurements[0]['value'], 0);
                $this->SetValue($variable['Ident'], $measurements[0]['value']);
                $this->SetValue($variable['Ident'] . '_Comment', $measurements[0]['comment']);
                $this->SetValue($variable['Ident'] . '_Timestamp', $measurements[0]['timestamp']);
                $this->SetValue($variable['Ident'] . '_IdealLow', $measurements[0]['ideal_low']);
                $this->SetValue($variable['Ident'] . '_IdealHigh', $measurements[0]['ideal_high']);

                if ($measurements[0]['value'] < $measurements[0]['ideal_low']) {
                    $this->SetValue($variable['Ident'] . '_State', 'toLow');
                }
                if ($measurements[0]['value'] > $measurements[0]['ideal_high']) {
                    $this->SetValue($variable['Ident'] . '_State', 'toHigh');
                }
                if (($measurements[0]['value'] >= $measurements[0]['ideal_low']) && ($measurements[0]['value'] <= $measurements[0]['ideal_high'])) {
                    $this->SetValue($variable['Ident'] . '_State', 'okay');
                }
            }
        }

        public function updateMeasurements(int $StartTime, int $EndTime, string $parameterName)
        {
            $this->SendDebug(__FUNCTION__ . ' :: ' . $parameterName, $parameterName, 0);

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