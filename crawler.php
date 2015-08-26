<?php
// 資料來自 http://data.taipei/opendata/datalist/datasetMeta?oid=9b7d78d2-0d73-4b42-9b29-c1640efed0eb
// 臺北市自動化3D近似建物模型
// 把這邊完整的 kmz, kml 爬到 kmzs/ 資料夾下
class Crawler
{
    public function main($url)
    {
        $f = "kmzs/{$url}";
        error_log($f);
        if (!file_exists(dirname($f))) {
            mkdir(dirname($f));
        }
        if (!file_exists($f)) {
            file_put_contents($f, file_get_contents('http://adm3d.taipei.gov.tw/tcg/kml/Taipei3DBuilding/' . $url));
        }

        $c = file_get_contents($f);
        preg_match_all('#<href>([^<]*)</href>#', $c, $matches);
        foreach ($matches[1] as $new_url) {
            if (strpos($new_url, 'http') === 0) {
                continue;
            }
            if (strpos($new_url, 'kmz')) {
            }
            $this->main(dirname($url) . '/' . str_replace('\\', '/', $new_url));
        }
    }
}

$c = new Crawler;
$c->main('Taipei3DBuilding_nl.kml');
