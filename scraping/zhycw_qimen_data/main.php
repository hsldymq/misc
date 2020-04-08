<?php

require __DIR__.'/vendor/autoload.php';

(new class() {
    private $uri = 'http://www.zhycw.com/pp/qm.aspx';

    private $fromDateTime;

    private $toDateTime;

    public function __construct()
    {
        global $argv;
        $this->fromDateTime = new DateTime($argv[1]);
        $this->toDateTime = new DateTime($argv[2]);

//        $this->fromDateTime = new DateTime('1984-02-02 00:00:00');
//        $this->toDateTime = new DateTime('1984-02-02 00:00:00');
    }

    public function run()
    {
        $dt = $this->fromDateTime;
        while ($dt->getTimestamp() <= $this->toDateTime->getTimestamp()) {
            $roundStr = $this->fetchRound($dt);
            $roundData = $this->parseRound($roundStr);
            echo json_encode($roundData).PHP_EOL;
            $dt = $dt->add(new DateInterval('PT1M'));
        }
    }

    private function parseRound(string $round): array
    {
        $round = trim($round);
        $fp = fopen("php://memory", 'rw');
        fwrite($fp, $round);
        rewind($fp);

        fgets($fp);
        fgets($fp);

        // 时间
        $line = fgets($fp);
        $time = $line;

        // 农历
        $line = fgets($fp);
        // 干支
        $line = fgets($fp);
        // 旬空
        $line = fgets($fp);
        // 节气1
        $line = fgets($fp);
        // 节气2
        $line = fgets($fp);
        // 节气首日
        $line = fgets($fp);

        // 节气
        $line = fgets($fp);
        $solarTermStr = mb_substr($line, 0, 2, 'UTF-8');
        $solarTerm = [
                "春分" => 0,
                "清明" => 1,
                "谷雨" => 2,
                "立夏" => 3,
                "小满" => 4,
                "芒种" => 5,
                "夏至" => 6,
                "小暑" => 7,
                "大暑" => 8,
                "立秋" => 9,
                "处暑" => 10,
                "白露" => 11,
                "秋分" => 12,
                "寒露" => 13,
                "霜降" => 14,
                "立冬" => 15,
                "小雪" => 16,
                "大雪" => 17,
                "冬至" => 18,
                "小寒" => 19,
                "大寒" => 20,
                "立春" => 21,
                "雨水" => 22,
                "惊蛰" => 23,
            ][$solarTermStr] ?? null;
        if ($solarTerm === null) {
            throw new Exception("节气解析失败: {$solarTermStr}");
        }

        $yuan = mb_substr($line, 3, 2, 'UTF-8');
        if (!in_array($yuan, ['上元', '中元', '下元'])) {
            throw new Exception("元解析失败: {$yuan}");
        }
        $yuan = ['上元' => 0, '中元' => 1, '下元' => 2];

        // 信息
        $line = fgets($fp);
        $escaping = mb_substr($line, 0, 2, 'UTF-8');
        if ($escaping === '阳遁') {
            $escaping = 0;
        } else if ($escaping === '阴遁') {
            $escaping = 1;
        } else {
            throw new Exception("阴阳遁解析失败: {$escaping}");
        }

        $roundStr = mb_substr($line, 2, 1, 'UTF-8');
        $round = [
            '一' => 0,
            '二' => 1,
            '三' => 2,
            '四' => 3,
            '五' => 4,
            '六' => 5,
            '七' => 6,
            '八' => 7,
            '九' => 8,
        ][$roundStr] ?? null;
        if ($round === null) {
            throw new Exception("局数解析失败: {$roundStr}");
        }

        // 分隔符
        $line = fgets($fp);

        // 解析4 9 2宫
        $p = $this->parsePalaces($fp, false);
        $palaces[3] = $p[0];
        $palaces[8] = $p[1];
        $palaces[1] = $p[2];

        // 分隔符
        $line = fgets($fp);

        // 解析3 5 7宫
        $p = $this->parsePalaces($fp, true);
        $palaces[2] = $p[0];
        $palaces[4] = $p[1];
        $palaces[6] = $p[2];

        // 分隔符
        $line = fgets($fp);

        // 解析8 1 6宫
        $p = $this->parsePalaces($fp, false);
        $palaces[7] = $p[0];
        $palaces[0] = $p[1];
        $palaces[5] = $p[2];

        fclose($fp);

        return [
            'time' => $time,
            'escaping' => $escaping,
            'solarTerm' => $solarTerm,
            'yuan' => $yuan,
            'round' => $round,
            'roundPalaces' => $palaces,
        ];
    }

    private function fetchRound(DateTime $dt): string
    {
        $request = new \GuzzleHttp\Psr7\Request('POST', $this->uri);
        $request = $request->withHeader('Host', 'www.zhycw.com')
            ->withHeader('Connection', 'keep-alive')
            ->withHeader('Cache-Control', 'max-age=0')
            ->withHeader('Origin', 'http://www.zhycw.com')
            ->withHeader('Upgrade-Insecure-Requests', '1')
            ->withHeader('Content-Type', 'application/x-www-form-urlencoded')
            ->withHeader('User-Agent', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/80.0.3987.149 Safari/537.36')
            ->withHeader('Accept', 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9')
            ->withHeader('Referer', 'http://www.zhycw.com/pp/qimen.aspx')
            ->withHeader('Accept-Encoding', 'gzip, deflate')
            ->withHeader('Accept-Language', 'zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7')
            ->withHeader('Cookie', 'UserLanguage=zh-cn; __gads=ID=1ad176c930960ca4:T=1586246543:S=ALNI_MbQnXF5T3VDKMuXvmC0c7AWtUxN-A')
            ->withBody(\GuzzleHttp\Psr7\stream_for(\GuzzleHttp\Psr7\build_query([
                'y' => $dt->format('Y'),
                'm' => intval($dt->format('m')),
                'd' => intval($dt->format('d')),
                'h' => intval($dt->format('H')),
                'min' => intval($dt->format('i')),
                'Nian' => 59,
                'Yue' => 11,
                'Ri' => 59,
                'Shi' => 11,
                'Ju' => 6,
                'mod' => 1,
                'pai' => 1,
                'run1' => 1,
            ])));

        $response = (new \GuzzleHttp\Client())->send($request);
        $d = new \Embed\Document(new \Embed\Extractor(new \GuzzleHttp\Psr7\Uri($this->uri), $request, $response, new \Embed\Http\Crawler()));

        return $d->select('//div[@class="container2"]')->get();
    }

    private function parsePalaces($fp, bool $isMiddle): array
    {
        $palaces = [];

        $line = fgets($fp);
        $gods = explode('│', $line);
        $palaces[0]['god'] = $this->parseGodIndex($gods[1]);
        $palaces[1]['god'] = $this->parseGodIndex($gods[2]);
        $palaces[2]['god'] = $this->parseGodIndex($gods[3]);

        $line = fgets($fp);
        $doorCelestialQiYi = explode('│', $line);
        $doorCelestialQiYi[1] = mb_substr($doorCelestialQiYi[1], 1, 1000, 'UTF-8');
        $doorCelestialQiYi[2] = mb_substr($doorCelestialQiYi[2], 1, 1000, 'UTF-8');
        $doorCelestialQiYi[3] = mb_substr($doorCelestialQiYi[3], 1, 1000, 'UTF-8');
        $palaces[0]['door'] = $this->parseDoorIndex(mb_substr($this->trim($doorCelestialQiYi[1]), 0, 2, 'UTF-8'));
        $palaces[0]['celestialQiYi'] = $this->parseQiYi(mb_substr($this->trim($doorCelestialQiYi[1]), 3, 1, 'UTF-8'));
        $palaces[1]['door'] = $this->parseDoorIndex(mb_substr($this->trim($doorCelestialQiYi[2]), 0, 2, 'UTF-8'));
        $palaces[1]['celestialQiYi'] = $this->parseQiYi(mb_substr($this->trim($doorCelestialQiYi[2]), 3, 1, 'UTF-8'));
        $palaces[2]['door'] = $this->parseDoorIndex(mb_substr($this->trim($doorCelestialQiYi[3]), 0, 2, 'UTF-8'));
        $palaces[2]['celestialQiYi'] = $this->parseQiYi(mb_substr($this->trim($doorCelestialQiYi[3]), 3, 1, 'UTF-8'));


        $line = fgets($fp);
        $starTerrestrialQiYi = explode('│', $line);
        $starTerrestrialQiYi[1] = mb_substr($starTerrestrialQiYi[1], 1, 1000, 'UTF-8');
        $starTerrestrialQiYi[2] = mb_substr($starTerrestrialQiYi[2], 1, 1000, 'UTF-8');
        $starTerrestrialQiYi[3] = mb_substr($starTerrestrialQiYi[3], 1, 1000, 'UTF-8');
        $palaces[0]['star'] = $this->parseStarIndex(mb_substr($this->trim($starTerrestrialQiYi[1]), 0, 2, 'UTF-8'));
        $palaces[0]['terrestrialQiYi'] = $this->parseQiYi(mb_substr($this->trim($starTerrestrialQiYi[1]), 3, 1, 'UTF-8'));
        if ($isMiddle) {
            $palaces[1]['star'] = -1;
            $palaces[1]['terrestrialQiYi'] = $this->parseQiYi($this->trim($starTerrestrialQiYi[2]));
        } else {
            $palaces[1]['star'] = $this->parseStarIndex(mb_substr($this->trim($starTerrestrialQiYi[2]), 0, 2, 'UTF-8'));
            $palaces[1]['terrestrialQiYi'] = $this->parseQiYi(mb_substr($this->trim($starTerrestrialQiYi[2]), 3, 1, 'UTF-8'));
        }
        $palaces[2]['star'] = $this->parseStarIndex(mb_substr($this->trim($starTerrestrialQiYi[3]), 0, 2, 'UTF-8'));
        $palaces[2]['terrestrialQiYi'] = $this->parseQiYi(mb_substr($this->trim($starTerrestrialQiYi[3]), 3, 1, 'UTF-8'));


        return $palaces;
    }

    private function parseGodIndex(string $godStr): array
    {
        $godsMap = [
            "值符" => 0,
            "直符" => 0,
            "朱雀" => 1,
            "玄武" => 2,
            "太阴" => 3,
            "六合" => 4,
            "九天" => 5,
            "九地" => 6,
            "螣蛇" => 7,
            "勾陈" => 8,
            "白虎" => 9,
            "" => -1,
        ];

        $god = $godsMap[$this->trim($godStr)] ?? null;
        if ($god === null) {
            throw new Exception("神解析错误: {$godStr}");
        }

        return ["text" => $this->trim($godStr), "value" => $god];
    }

    private function parseDoorIndex(string $doorStr): array
    {
        $doorsMap = [
            "休门" => 0,
            "死门" => 1,
            "伤门" => 2,
            "杜门" => 3,
            "开门" => 4,
            "惊门" => 5,
            "生门" => 6,
            "景门" => 7,
            "" => -1,
        ];

        $door = $doorsMap[$this->trim($doorStr)] ?? null;
        if ($door === null) {
            throw new Exception("门解析错误: {$doorStr}");
        }

        return ["text" => $this->trim($doorStr), "value" => $door];
    }

    private function parseQiYi(string $cString): array
    {
        $sMap = [
            "乙" => 1,
            "丙" => 2,
            "丁" => 3,
            "戊" => 4,
            "己" => 5,
            "庚" => 6,
            "辛" => 7,
            "壬" => 8,
            "癸" => 9,
            "" => -1,
        ];

        $cs = $sMap[$this->trim($cString)] ?? null;
        if ($cs === null) {
            throw new Exception("三奇六仪解析错误: {$cString}");
        }

        return ["text" => $this->trim($cString), "value" => $cs];
    }

    private function parseStarIndex(string $starStr): array
    {
        $sMap = [
            "天蓬" => 0,
            "天芮" => 1,
            "天冲" => 2,
            "天辅" => 3,
            "天禽" => 4,
            "天心" => 5,
            "天柱" => 6,
            "天任" => 7,
            "天英" => 8,
            "" => -1,
        ];

        $cs = $sMap[$this->trim($starStr)] ?? null;
        if ($cs === null) {
            throw new Exception("星解析错误: {$starStr}");
        }

        return ["text" => $this->trim($starStr), "value" => $cs];
    }

    private function trim(string $s): string
    {
        do {
            $pos = strpos($s, "\xe3\x80\x80");
            if ($pos === false || $pos !== 0) {
                break;
            }
            $s = substr($s, 3);
        } while (true);

        while (substr($s, -3) === "\xe3\x80\x80") {
            $s = substr($s, 0, -3);
        }

        return $s;
    }
})->run();