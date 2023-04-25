<?php

declare(strict_types=1);
    class PLCloud extends IPSModule
    {
        private $Endpoint = 'https://backend.labcom.cloud/graphql';
        public function Create()
        {
            //Never delete this line!
            parent::Create();

            $this->RegisterPropertyString('Token', '');
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
                case 'getAccounts':
                    $Query = '{Accounts{id,forename,surname,street,zipcode,city,phone1,phone2,fax,email,country,canton,notes,volume,pooltext,gps}}';
                    $result = $this->sendRequest($Query);
                    break;
                case 'GetAccount':
                    $Query = '{Accounts(id: ' . $data['Buffer']['AccountID'] . ' ){id,forename,surname,street,zipcode,city,phone1,phone2,fax,email,country,canton,notes,volume,pooltext,gps}}';
                    $result = $this->sendRequest($Query);
                    break;
                case 'dosageRecommendation':
                    $Query = '{
                        DosageRecommendation(groupId: ' . $data['Buffer']['groupID'] . ', unitId: ' . $data['Buffer']['unitID'] . ', waterVolume: ' . $data['Buffer']['waterVolume'] . ', currentValue: ' . $data['Buffer']['currentValue'] . ', targetValue: ' . $data['Buffer']['targetValue'] . ') {
                          result
                          unit
                          WaterConditioners {
                            id
                            name
                            effect
                            phrase
                          }
                        }
                      }';
                    $result = $this->sendRequest($Query);
                    break;
                case 'GetMeasurements':
                $Query = '{
                    Measurements(accountId: ' . $data['Buffer']['AccountID'] . ',from: 0, parameterName: "' . $data['Buffer']['ParameterName'] . '") {
                    account
                    accountId
                    id
                    scenario
                    parameter
                    unit
                    comment
                    value
                    ideal_low
                    ideal_high
                    timestamp
                    }
                }';
                    $result = $this->sendRequest($Query);
                    break;
                default:
                $this->SendDebug(__FUNCTION__, 'Invalid Command: ' . $data->Buffer->Command, 0);
                break;
            }
            $this->SendDebug(__FUNCTION__, json_encode($result), 0);
            return json_encode($result);
        }

        private function sendRequest(string $Query)
        {
            $headers[] = 'Content-Type: application/json';
            $headers[] = 'Authorization: ' . $this->ReadPropertyString('Token');
            $Query = ['query'=>$Query];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->Endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($Query));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            $Response = curl_exec($ch);
            $HttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($HttpCode != 200) {
                $this->LogMessage('PoolLab - API Error: ' . $HttpCode, KL_ERROR);
                return false;
            }

            return json_decode($Response, true);
        }
    }