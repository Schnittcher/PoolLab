<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php';

    class PLAccount extends IPSModule
    {
        use VariableProfileHelper;

        public function Create()
        {
            //Never delete this line!
            parent::Create();
            $this->ConnectParent('{229DD642-B66F-C8F0-A2F3-55CD927F9D8C}');
            $this->RegisterPropertyInteger('AccountID', 0);

            if (!IPS_VariableProfileExists('PoolLab.m3')) {
                $this->RegisterProfileInteger('PoolLab.m3', 'Drops', '', ' m³', 0, 0, 1);
            }

            $this->RegisterVariableInteger('PoolVolume', $this->Translate('Pool Volume'), 'PoolLab.m3');
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

            if ($this->HasActiveParent()) {
                $this->updateAccountVariables();
            }
        }

        public function ForwardData($JSONString)
        {
            $this->SendDebug(__FUNCTION__, $JSONString, 0);
            $data = json_decode($JSONString, true);

            switch ($data['Buffer']['Command']) {
                case 'GetMeasurements':
                    $result = $this->GetMeasurements($data['Buffer']['StartTime'], $data['Buffer']['EndTime'], $data['Buffer']['ParameterName']);
                    break;
                default:
                $this->SendDebug(__FUNCTION__, 'Invalid Command: ' . $data->Buffer->Command, 0);
                break;
            }
            $this->SendDebug(__FUNCTION__, json_encode($result), 0);
            return json_encode($result);
        }

        public function GetAccount()
        {
            $Data = [];
            $Buffer = [];

            $Data['DataID'] = '{76339EB6-91E7-B97F-159C-71F769BA285A}';
            $Buffer['Command'] = 'GetAccount';
            $Buffer['AccountID'] = $this->ReadPropertyInteger('AccountID');
            $Data['Buffer'] = $Buffer;
            $Data = json_encode($Data);
            $result = $this->SendDataToParent($Data);
            $result = json_decode($this->SendDataToParent($Data), true);
            if (!$result) {
                return [];
            }
            return $result;
        }

        public function GetMeasurements(int $StartTime, int $EndTime, $parameterName)
        {
            $Data = [];
            $Buffer = [];

            $Data['DataID'] = '{76339EB6-91E7-B97F-159C-71F769BA285A}';
            $Buffer['Command'] = 'GetMeasurements';
            $Buffer['AccountID'] = $this->ReadPropertyInteger('AccountID');
            $Buffer['StartTime'] = $StartTime;
            $Buffer['EndTime'] = $EndTime;
            $Buffer['ParameterName'] = $parameterName;
            $Data['Buffer'] = $Buffer;
            $Data = json_encode($Data);
            $result = json_decode($this->SendDataToParent($Data), true);
            if (!$result) {
                return [];
            }
            return $result;
        }

        public function updateAccountVariables()
        {
            $result = $this->GetAccount()['data']['Accounts'];
            IPS_LogMessage('test', print_r($result, true));
            if (!empty($result)) {
                $this->SetValue('PoolVolume', $result[0]['volume']);
            }
        }
    }