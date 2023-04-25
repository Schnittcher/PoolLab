<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/vendor/SymconModulHelper/VariableProfileHelper.php';

    class PLDosageRecommendation extends IPSModule
    {
        use VariableProfileHelper;

        public function Create()
        {
            //Never delete this line!
            parent::Create();
            $this->ConnectParent('{5003D1FE-D820-150D-E709-8363BAE2CE11}');

            if (!IPS_VariableProfileExists('PoolLab.m3')) {
                $this->RegisterProfileFloat('PoolLab.m3', 'Drops', '', ' m³', 0, 999, 0.5, 1);
            }
            if (!IPS_VariableProfileExists('PoolLab.ChemieGruppe')) {
                $this->RegisterProfileIntegerEx('PoolLab.ChemieGruppe', 'Information', '', '', [
                    [1, $this->Translate('Active Oxygen'), '', 0x00FF00],
                    [2, $this->Translate('Ammonia'), '', 0x00FF00],
                    [3, $this->Translate('Aluminium'), '', 0x00FF00],
                    [4, $this->Translate('Alkalinity'), '', 0x00FF00],
                    [5, $this->Translate('Boron'), '', 0x00FF00],
                    [6, $this->Translate('Bromine'), '', 0x00FF00],
                    [7, $this->Translate('Oxygen Scavengers'), '', 0x00FF00],
                    [8, $this->Translate('Chloride'), '', 0x00FF00],
                    [9, $this->Translate('Chlorine'), '', 0x00FF00],
                    [10, $this->Translate('Chlorine Dioxide'), '', 0x00FF00],
                    [11, $this->Translate('COD'), '', 0x00FF00],
                    [12, $this->Translate('Cyanuric Acid'), '', 0x00FF00],
                    [13, $this->Translate('DBNPA'), '', 0x00FF00],
                    [14, $this->Translate('Phosphonate'), '', 0x00FF00],
                    [15, $this->Translate('Iron'), '', 0x00FF00],
                    [16, $this->Translate('Fluoride'), '', 0x00FF00],
                    [17, $this->Translate('Hardness'), '', 0x00FF00],
                    [18, $this->Translate('Hydrazine'), '', 0x00FF00],
                    [19, $this->Translate('Chromium'), '', 0x00FF00],
                    [20, $this->Translate('Iodine'), '', 0x00FF00],
                    [21, $this->Translate('Potassium'), '', 0x00FF00],
                    [22, $this->Translate('Copper'), '', 0x00FF00],
                    [23, $this->Translate('Manganese'), '', 0x00FF00],
                    [24, $this->Translate('Tannic Acid'), '', 0x00FF00],
                    [25, $this->Translate('Molybdate'), '', 0x00FF00],
                    [26, $this->Translate('Sod.-Hypochlorite'), '', 0x00FF00],
                    [27, $this->Translate('Nitrate'), '', 0x00FF00],
                    [28, $this->Translate('Nitrite'), '', 0x00FF00],
                    [29, $this->Translate('Ozone'), '', 0x00FF00],
                    [30, $this->Translate('pH'), '', 0x00FF00],
                    [31, $this->Translate('PHMB'), '', 0x00FF00],
                    [32, $this->Translate('Phosphate'), '', 0x00FF00],
                    [33, $this->Translate('Magnesium'), '', 0x00FF00],
                    [34, $this->Translate('Silica'), '', 0x00FF00],
                    [35, $this->Translate('Sulphate'), '', 0x00FF00],
                    [36, $this->Translate('Sulphide'), '', 0x00FF00],
                    [37, $this->Translate('Sulphite'), '', 0x00FF00],
                    [39, $this->Translate('Turbidity'), '', 0x00FF00],
                    [40, $this->Translate('Hydrogen Peroxide'), '', 0x00FF00],
                    [41, $this->Translate('Zinc'), '', 0x00FF00],
                    [42, $this->Translate('Polyacrylate'), '', 0x00FF00],
                    [44, $this->Translate('QAC'), '', 0x00FF00],
                    [45, $this->Translate('Suspended Solids'), '', 0x00FF00],
                    [45, $this->Translate('Nickel'), '', 0x00FF00],
                    [46, $this->Translate('Chlorite'), '', 0x00FF00],
                    [47, $this->Translate('Colour'), '', 0x00FF00],
                    [48, $this->Translate('PTSA'), '', 0x00FF00],
                    [49, $this->Translate('Fluorescein'), '', 0x00FF00],
                    [50, $this->Translate('Transmission'), '', 0x00FF00],
                    [51, $this->Translate('Polyamine'), '', 0x00FF00],
                    [52, $this->Translate('Urea'), '', 0x00FF00],
                    [53, $this->Translate('manual'), '', 0x00FF00],
                    [54, $this->Translate('QC Colour Tests'), '', 0x00FF00],
                    [55, $this->Translate('Isothiazolinone'), '', 0x00FF00],
                    [56, $this->Translate('Legionella'), '', 0x00FF00],
                    [57, $this->Translate('Nitrogen'), '', 0x00FF00],
                    [58, $this->Translate('Phosphorus'), '', 0x00FF00],
                    [59, $this->Translate('Cyanide'), '', 0x00FF00],
                    [60, $this->Translate('Permanganate'), '', 0x00FF00],
                    [61, $this->Translate('Hydrocarbons'), '', 0x00FF00],
                    [62, $this->Translate('Oil'), '', 0x00FF00],
                    [64, $this->Translate('Phenol'), '', 0x00FF00],
                    [65, $this->Translate('Dissolved Oxygen'), '', 0x00FF00],
                    [66, $this->Translate('Peracetic Acid'), '', 0x00FF00],
                    [67, $this->Translate('Electronic Conductivity'), '', 0x00FF00],
                    [68, $this->Translate('ORP'), '', 0x00FF00],
                    [69, $this->Translate('Carbohydrazide'), '', 0x00FF00],
                    [70, $this->Translate('Erythorbic Acid'), '', 0x00FF00],
                    [71, $this->Translate('Methylethylketoxime'), '', 0x00FF00],
                    [72, $this->Translate('Hydroquinone'), '', 0x00FF00],
                    [73, $this->Translate('DEHA'), '', 0x00FF00],
                    [74, $this->Translate('IGG'), '', 0x00FF00],
                    [75, $this->Translate('Temperature'), '', 0x00FF00],
                    [76, $this->Translate('Cadmium'), '', 0x00FF00],
                    [77, $this->Translate('Lead'), '', 0x00FF00],
                ]);
            }

            if (!IPS_VariableProfileExists('PoolLab.Action')) {
                $this->RegisterProfileStringEx('PoolLab.Action', 'Information', '', '', [
                    ['calculate', $this->Translate('Calculate'), '', 0x00FF00]
                ]);
            }

            $this->RegisterVariableInteger('chemicGroup', $this->Translate('Chemic Group'), 'PoolLab.ChemieGruppe', 0);
            $this->EnableAction('chemicGroup');
            $this->RegisterVariableFloat('waterVolume', $this->Translate('Water volume'), 'PoolLab.m3', 1);
            $this->EnableAction('waterVolume');
            $this->RegisterVariableFloat('currentValue', $this->Translate('Current value'), '', 2);
            $this->EnableAction('currentValue');
            $this->RegisterVariableFloat('targetValue', $this->Translate('Target value'), '', 3);
            $this->EnableAction('targetValue');
            $this->RegisterVariableString('action', $this->Translate('Action'), 'PoolLab.Action', 4);
            $this->EnableAction('action');
            $this->RegisterVariableString('dosageRecommendation', $this->Translate('Dosage recommendation'), '', 5);
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
                case 'waterVolume':
                case 'chemicGroup':
                case 'currentValue':
                case 'targetValue':
                    $this->SetValue($Ident, $Value);
                    break;
                case 'action':
                    $this->SetValue($Ident, $Value);
                    $result = $this->calculate($this->GetValue('chemicGroup'), 0, $this->GetValue('waterVolume'), $this->GetValue('currentValue'), $this->GetValue('targetValue'));
                    $DoseageRecommendation = $result['data']['DosageRecommendation'][0];
                    $strDoseageRecommendation = $DoseageRecommendation['result'] . ' ' . $DoseageRecommendation['unit'] . ' ' . $this->Translate('from') . ' ' . $DoseageRecommendation['WaterConditioners'][0]['name'];
                    $this->SetValue('dosageRecommendation', $strDoseageRecommendation);

                    break;
                default:
                    # code...
                    break;
            }
        }

        private function calculate(int $groupID, int $unitID = 0, float $waterVolume, float $currentValue, float $targetValue)
        {
            $Data = [];
            $Buffer = [];

            $Data['DataID'] = '{962271EC-68EA-19B9-2732-49D5D8EEBC45}';
            $Buffer['Command'] = 'dosageRecommendation';
            $Buffer['groupID'] = $groupID;
            $Buffer['unitID'] = $unitID;
            $Buffer['waterVolume'] = $waterVolume;
            $Buffer['currentValue'] = $currentValue;
            $Buffer['targetValue'] = $targetValue;
            $Data['Buffer'] = $Buffer;
            $Data = json_encode($Data);
            $results = json_decode($this->SendDataToParent($Data), true);
            if (!$results) {
                return [];
            }
            return $results;
        }
    }