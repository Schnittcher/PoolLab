<?php

declare(strict_types=1);
    class PLAccount extends IPSModule
    {
        public function Create()
        {
            //Never delete this line!
            parent::Create();
            $this->ConnectParent('{229DD642-B66F-C8F0-A2F3-55CD927F9D8C}');
            $this->RegisterPropertyInteger('AccountID', 0);
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

        public function ForwardData($JSONString)
        {
            $this->SendDebug(__FUNCTION__, $JSONString, 0);
            $data = json_decode($JSONString, true);

            switch ($data['Buffer']['Command']) {
                case 'GetMeasurements':
                    $result = $this->GetMeasurements($data['Buffer']['StartTime'], $data['Buffer']['EndTime'],$data['Buffer']['ParameterName']);
                    break;
                default:
                $this->SendDebug(__FUNCTION__, 'Invalid Command: ' . $data->Buffer->Command, 0);
                break;
            }
            $this->SendDebug(__FUNCTION__, json_encode($result), 0);
            return json_encode($result);
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
    }