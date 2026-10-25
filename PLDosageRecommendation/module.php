<?php

declare(strict_types=1);

require_once __DIR__ . '/../libs/PoolLabTile.php';

class PLDosageRecommendation extends IPSModuleStrict
{
    use PoolLabTile;

    private const ACCOUNT_GUID = '{5003D1FE-D820-150D-E709-8363BAE2CE11}';
    private const TO_ACCOUNT = '{962271EC-68EA-19B9-2732-49D5D8EEBC45}';

    //Gruppen-IDs der LabCOM-Cloud (Query Groups)
    private const GROUPS = [
        1  => 'Active Oxygen', 2 => 'Ammonia', 3 => 'Aluminium', 4 => 'Alkalinity', 5 => 'Boron', 6 => 'Bromine',
        7  => 'Oxygen Scavengers', 8 => 'Chloride', 9 => 'Chlorine', 10 => 'Chlorine Dioxide', 11 => 'COD',
        12 => 'Cyanuric Acid', 13 => 'DBNPA', 14 => 'Phosphonate', 15 => 'Iron', 16 => 'Fluoride', 17 => 'Hardness',
        18 => 'Hydrazine', 19 => 'Chromium', 20 => 'Iodine', 21 => 'Potassium', 22 => 'Copper', 23 => 'Manganese',
        24 => 'Tannic Acid', 25 => 'Molybdate', 26 => 'Sod.-Hypochlorite', 27 => 'Nitrate', 28 => 'Nitrite',
        29 => 'Ozone', 30 => 'pH', 31 => 'PHMB', 32 => 'Phosphate', 33 => 'Magnesium', 34 => 'Silica',
        35 => 'Sulphate', 36 => 'Sulphide', 37 => 'Sulphite', 39 => 'Turbidity', 40 => 'Hydrogen Peroxide',
        41 => 'Zinc', 42 => 'Polyacrylate', 43 => 'QAC', 44 => 'Suspended Solids', 45 => 'Nickel', 46 => 'Chlorite',
        47 => 'Colour', 48 => 'PTSA', 49 => 'Fluorescein', 50 => 'Transmission', 51 => 'Polyamine', 52 => 'Urea',
        53 => 'manual', 54 => 'QC Colour Tests', 55 => 'Isothiazolinone', 56 => 'Legionella', 57 => 'Nitrogen',
        58 => 'Phosphorus', 59 => 'Cyanide', 60 => 'Permanganate', 61 => 'Hydrocarbons', 62 => 'Oil',
        64 => 'Phenol', 65 => 'Dissolved Oxygen', 66 => 'Peracetic Acid', 67 => 'Electronic Conductivity',
        68 => 'ORP', 69 => 'Carbohydrazide', 70 => 'Erythorbic Acid', 71 => 'Methylethylketoxime',
        72 => 'Hydroquinone', 73 => 'DEHA', 74 => 'IGG', 75 => 'Temperature', 76 => 'Cadmium', 77 => 'Lead'
    ];

    public function Create(): void
    {
        //Never delete this line!
        parent::Create();

        $this->ConnectParent(self::ACCOUNT_GUID);

        //Ergebniszeilen der letzten Berechnung fuer die Kachel
        $this->RegisterAttributeString('Lines', '[]');
        $this->setTileType();
    }

    public function GetVisualizationTile(): string
    {
        return $this->buildTile(__DIR__ . '/module.html', $this->getTileData());
    }

    public function ApplyChanges(): void
    {
        //Never delete this line!
        parent::ApplyChanges();

        $groupOptions = [];
        foreach (self::GROUPS as $id => $name) {
            $groupOptions[] = [
                'Value'      => $id,
                'Caption'    => $this->Translate($name),
                'IconActive' => false,
                'IconValue'  => '',
                'Color'      => -1
            ];
        }

        $this->MaintainVariable('chemicGroup', $this->Translate('Chemic Group'), VARIABLETYPE_INTEGER, [
            'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,
            'OPTIONS'      => json_encode($groupOptions)
        ], 0, true);
        $this->MaintainVariable('waterVolume', $this->Translate('Water volume'), VARIABLETYPE_FLOAT, [
            'PRESENTATION' => VARIABLE_PRESENTATION_SLIDER,
            'MIN'          => 0,
            'MAX'          => 999,
            'STEP_SIZE'    => 0.5,
            'DIGITS'       => 1,
            'SUFFIX'       => ' m³',
            'USAGE_TYPE'   => 0
        ], 1, true);
        $this->MaintainVariable('currentValue', $this->Translate('Current value'), VARIABLETYPE_FLOAT, [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_INPUT
        ], 2, true);
        $this->MaintainVariable('targetValue', $this->Translate('Target value'), VARIABLETYPE_FLOAT, [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_INPUT
        ], 3, true);
        $this->MaintainVariable('action', $this->Translate('Action'), VARIABLETYPE_STRING, [
            'PRESENTATION' => VARIABLE_PRESENTATION_ENUMERATION,
            'OPTIONS'      => json_encode([[
                'Value'      => 'calculate',
                'Caption'    => $this->Translate('Calculate'),
                'IconActive' => false,
                'IconValue'  => '',
                'Color'      => 65280
            ]])
        ], 4, true);
        $this->MaintainVariable('dosageRecommendation', $this->Translate('Dosage recommendation'), VARIABLETYPE_STRING, [
            'PRESENTATION' => VARIABLE_PRESENTATION_WEB_CONTENT
        ], 5, true);

        foreach (['chemicGroup', 'waterVolume', 'currentValue', 'targetValue', 'action'] as $ident) {
            $this->EnableAction($ident);
        }
        if ($this->GetValue('chemicGroup') === 0) {
            $this->SetValue('chemicGroup', 30);
        }
        $this->SetStatus(IS_ACTIVE);
    }

    public function RequestAction(string $Ident, mixed $Value): void
    {
        switch ($Ident) {
            case 'chemicGroup':
                if (!array_key_exists(intval($Value), self::GROUPS)) {
                    $this->SendDebug(__FUNCTION__, 'Invalid group: ' . strval($Value), 0);
                    return;
                }
                $this->SetValue($Ident, intval($Value));
                break;
            case 'waterVolume':
            case 'currentValue':
            case 'targetValue':
                $this->SetValue($Ident, floatval($Value));
                break;
            case 'action':
                $this->SetValue($Ident, strval($Value));
                $this->calculate();
                break;
            default:
                $this->SendDebug(__FUNCTION__, 'Invalid ident: ' . $Ident, 0);
                return;
        }
        $this->updateTile($this->getTileData());
    }

    private function calculate(): void
    {
        $response = $this->SendDataToParent(json_encode([
            'DataID' => self::TO_ACCOUNT,
            'Buffer' => [
                'Command'      => 'DosageRecommendation',
                'GroupID'      => $this->GetValue('chemicGroup'),
                'UnitID'       => 1,
                'WaterVolume'  => $this->GetValue('waterVolume'),
                'CurrentValue' => $this->GetValue('currentValue'),
                'TargetValue'  => $this->GetValue('targetValue')
            ]
        ]));
        $result = json_decode($response, true);
        if (!is_array($result) || !($result['ok'] ?? false)) {
            $this->SendDebug(__FUNCTION__, 'Request failed', 0);
            $this->SetValue('dosageRecommendation', '');
            $this->WriteAttributeString('Lines', '[]');
            return;
        }

        $lines = [];
        foreach ($result['data']['DosageRecommendation'] ?? [] as $recommendation) {
            $conditioner = $recommendation['WaterConditioners'][0]['name'] ?? '';
            $lines[] = $recommendation['result'] . ' ' . $this->Translate(strval($recommendation['unit'])) . ' ' . $this->Translate('from') . ' ' . $conditioner;
        }
        $this->WriteAttributeString('Lines', json_encode($lines));
        $this->SetValue('dosageRecommendation', implode('', array_map(fn (string $Line): string => htmlspecialchars($Line) . '<hr>', $lines)));
    }

    private function getTileData(): array
    {
        $groups = [];
        foreach (self::GROUPS as $id => $name) {
            $groups[] = ['id' => $id, 'name' => $this->Translate($name)];
        }
        return [
            'groups' => $groups,
            'group'  => $this->GetValue('chemicGroup'),
            'fields' => [
                ['ident' => 'waterVolume', 'label' => $this->Translate('Water volume'), 'value' => $this->GetValue('waterVolume'), 'step' => 0.5, 'suffix' => 'm³'],
                ['ident' => 'currentValue', 'label' => $this->Translate('Current value'), 'value' => $this->GetValue('currentValue'), 'step' => 0.01, 'suffix' => ''],
                ['ident' => 'targetValue', 'label' => $this->Translate('Target value'), 'value' => $this->GetValue('targetValue'), 'step' => 0.01, 'suffix' => '']
            ],
            'lines'  => json_decode($this->ReadAttributeString('Lines'), true) ?: [],
            'texts'  => [
                'group'     => $this->Translate('Chemic Group'),                'calculate' => $this->Translate('Calculate'),
                'result'    => $this->Translate('Dosage recommendation')
            ]
        ];
    }
}
