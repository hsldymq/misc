<?php

(new class {
    /** @var string[] */
    private $filepaths;

    public function __construct()
    {
        global $argv;
        array_shift($argv);

        $this->filepaths = $argv;
    }

    public function run()
    {
        $fps = [];
        foreach ($this->filepaths as $each) {
            $fp = fopen($each, 'r');
            if (!$fp) {
                throw new Exception("文件打不开: {$each}");
            }
            $fps[] = $fp;
        }
        foreach ($fps as $each) {
            $casesFp = fopen(__DIR__.'/test_cases.txt', 'w');
            $expectFp = fopen(__DIR__.'/expect.txt', 'w');
            $rowIndex = 0;
            while ($line = fgets($each)) {
                $data = json_decode($line, true);
                if (!$data) {
                    throw new Exception("error parse: {$line}\n");
                }

                $t = trim($data['time']);
                fwrite($casesFp, "{$rowIndex}: {Time: \"{$t}\", RoundInfo: RoundInfo{Escaping: component.Escaping({$data['escaping']['value']}), RoundPalaceIndex: component.PalaceIndex({$data['round']['value']}), Yuan: component.Yuan({$data['yuan']['value']}), SexagenaryHour: sexagenary.NewSexagenaryTermFromIndex({$data['sexagenaryHour']['value']})}},\n");
                fwrite($expectFp, "{$rowIndex}: {DutyStar: component.Star({$data['dutyStar']['value']}), DutyDoor: component.Door({$data['dutyDoor']['value']}), QiYiTerrestrialPlate: component.NewPalaces([9]component.PalaceValue{{$data['roundPalaces'][0]['terrestrialQiYi']['value']}, {$data['roundPalaces'][1]['terrestrialQiYi']['value']}, {$data['roundPalaces'][2]['terrestrialQiYi']['value']}, {$data['roundPalaces'][3]['terrestrialQiYi']['value']}, {$data['roundPalaces'][4]['terrestrialQiYi']['value']}, {$data['roundPalaces'][5]['terrestrialQiYi']['value']}, {$data['roundPalaces'][6]['terrestrialQiYi']['value']}, {$data['roundPalaces']['7']['terrestrialQiYi']['value']}, {$data['roundPalaces'][8]['terrestrialQiYi']['value']}}), QiYiCelestialPlate: component.NewPalaces([9]component.PalaceValue{{$data['roundPalaces'][0]['celestialQiYi']['value']}, {$data['roundPalaces'][1]['celestialQiYi']['value']}, {$data['roundPalaces'][2]['celestialQiYi']['value']}, {$data['roundPalaces'][3]['celestialQiYi']['value']}, {$data['roundPalaces'][4]['celestialQiYi']['value']}, {$data['roundPalaces'][5]['celestialQiYi']['value']}, {$data['roundPalaces'][6]['celestialQiYi']['value']}, {$data['roundPalaces']['7']['celestialQiYi']['value']}, {$data['roundPalaces'][8]['celestialQiYi']['value']}}), StarCelestialPlate: component.NewPalaces([9]component.PalaceValue{{$data['roundPalaces'][0]['star']['value']}, {$data['roundPalaces'][1]['star']['value']}, {$data['roundPalaces'][2]['star']['value']}, {$data['roundPalaces'][3]['star']['value']}, {$data['roundPalaces'][4]['star']['value']}, {$data['roundPalaces'][5]['star']['value']}, {$data['roundPalaces'][6]['star']['value']}, {$data['roundPalaces']['7']['star']['value']}, {$data['roundPalaces'][8]['star']['value']}}), HumanPlate: component.NewPalaces([9]component.PalaceValue{{$data['roundPalaces'][0]['door']['value']}, {$data['roundPalaces'][1]['door']['value']}, {$data['roundPalaces'][2]['door']['value']}, {$data['roundPalaces'][3]['door']['value']}, {$data['roundPalaces'][4]['door']['value']}, {$data['roundPalaces'][5]['door']['value']}, {$data['roundPalaces'][6]['door']['value']}, {$data['roundPalaces']['7']['door']['value']}, {$data['roundPalaces'][8]['door']['value']}}), GodPlate: component.NewPalaces([9]component.PalaceValue{{$data['roundPalaces'][0]['god']['value']}, {$data['roundPalaces'][1]['god']['value']}, {$data['roundPalaces'][2]['god']['value']}, {$data['roundPalaces'][3]['god']['value']}, {$data['roundPalaces'][4]['god']['value']}, {$data['roundPalaces'][5]['god']['value']}, {$data['roundPalaces'][6]['god']['value']}, {$data['roundPalaces']['7']['god']['value']}, {$data['roundPalaces'][8]['god']['value']}})},\n");
                $rowIndex++;
            }
        }
    }
})->run();