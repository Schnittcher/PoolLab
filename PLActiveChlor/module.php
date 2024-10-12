<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php';

    class PLActiveChlor extends IPSModule
    {
        use VariableProfileHelper;

        public function Create()
        {
            //Never delete this line!
            parent::Create();
            $this->ConnectParent('{5003D1FE-D820-150D-E709-8363BAE2CE11}');

            if (!IPS_VariableProfileExists('PoolLab.Action')) {
                $this->RegisterProfileStringEx('PoolLab.Action', 'Information', '', '', [
                    ['calculate', $this->Translate('Calculate'), '', 0x00FF00]
                ]);
            }

            if (!IPS_VariableProfileExists('PoolLab.ActiveChlor')) {
                $this->RegisterProfileFloat('PoolLab.ActiveChlor', 'Information', '', ' mg/l HOCl', 0, 0, 0.00001, 5);
            }

            if (!IPS_VariableProfileExists('PoolLab.Temperature')) {
                $this->RegisterProfileInteger('PoolLab.Temperature', 'Temperature', '', ' °C', 0, 80, 1);
            }

            $this->RegisterVariableFloat('pH', $this->Translate('pH'), '', 0);
            $this->EnableAction('pH');
            $this->RegisterVariableInteger('temperature', $this->Translate('Temperature'), 'PoolLab.Temperature', 1);
            $this->EnableAction('temperature');
            $this->RegisterVariableFloat('chlorine', $this->Translate('Chlorine'), '', 2);
            $this->EnableAction('chlorine');
            $this->RegisterVariableFloat('cya', $this->Translate('CYA'), '', 3);
            $this->EnableAction('cya');
            $this->RegisterVariableString('action', $this->Translate('Action'), 'PoolLab.Action', 4);
            $this->EnableAction('action');
            $this->RegisterVariableFloat('activeChlorine', $this->Translate('Active Chlorine'), 'PoolLab.ActiveChlor', 5);
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

        public function RequestAction($Ident, $Value)
        {
            switch ($Ident) {
                case 'pH':
                case 'temperature':
                case 'chlorine':
                case 'cya':
                    $this->SetValue($Ident, $Value);
                    break;
                case 'action':
                    $this->SetValue($Ident, $Value);
                    $result = $this->calculateActiveChlor($this->GetValue('pH'), $this->GetValue('temperature'), $this->GetValue('chlorine'), $this->GetValue('cya'));
                    $this->SetValue('activeChlorine', $result['data']['ActiveChlorine']['HOCl']);

                    break;
                default:
                    # code...
                    break;
            }
        }

        public function calculateActiveChlor(float $pH, int $temperature, float $chlorine, float $cya)
        {
            $Data = [];
            $Buffer = [];

            $Data['DataID'] = '{962271EC-68EA-19B9-2732-49D5D8EEBC45}';
            $Buffer['Command'] = 'activeChlor';
            $Buffer['pH'] = $pH;
            $Buffer['temperature'] = $temperature;
            $Buffer['chlorine'] = $chlorine;
            $Buffer['cya'] = $cya;
            $Data['Buffer'] = $Buffer;
            $Data = json_encode($Data);
            $results = json_decode($this->SendDataToParent($Data), true);
            if (!$results) {
                return [];
            }
            return $results;
        }
    }