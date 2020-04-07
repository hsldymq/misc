<?php

require __DIR__.'/vendor/autoload.php';

use Embed\Embed;

(new class() {
    private $uri = 'http://www.zhycw.com/pp/qm.aspx';

    private $fromDateTime;

    private $toDateTime;

    public function __construct()
    {
        global $argv;
        $this->fromDateTime = new DateTime($argv[1]);
        $this->toDateTime = new DateTime($argv[2]);
    }

    public function run()
    {
        $dt = $this->fromDateTime;
        while ($dt->getTimestamp() <= $this->toDateTime->getTimestamp()) {
            $roundStr = $this->fetchRound($dt);
            $roundData = $this->parseRound($roundStr);

            $dt = $dt->add(new DateInterval('PT1M'));
        }
    }

    private function parseRound(string $round): array
    {
        return [];
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
})->run();