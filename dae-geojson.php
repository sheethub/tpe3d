<?php

/**
 *   將原來台北 3d 建築的 kml 全部抓到 kmzs/ 下面之後，把裡面的 .dae 解出來並產生 geojson 到 geojsons/ 下面
 */

$print_polygons = function($points, $height, $location, &$first, $fp) {
    if (count($points) < 4) {
        return;
    }
    if ($first) {
        $first = false;
    } else {
        fputs($fp, ",\n");
    }

    $inch = 0.02539999969303608;
    $json = new StdClass;
    $json->type = 'Feature';
    $json->properties = array('height' => $inch * $height, "color" => "rgb(0,0,255)");
    $json->geometry = new STdClass;
    $json->geometry->type = 'Polygon';
    $json->geometry->coordinates = array(array());

    $earth_radius = 6378137;

    $x_meter = 2 * pi() * $earth_radius / 360;
    $y_meter = 2 * pi() * $earth_radius * cos(pi() * $location['latitude'] / 180) / 360;

    foreach ($points as $point) {
        $json->geometry->coordinates[0][] = array(
            $location['longitude'] + $point[0] * $inch / $x_meter,
            $location['latitude'] + $point[1] * $inch / $y_meter,
        );
    }

    if ($json->geometry->coordinates[0][count($json->geometry->coordinates[0]) - 1] != $json->geometry->coordinates[0][0]) {
        $json->geometry->coordinates[0][] = $json->geometry->coordinates[0][0];
    }

    fputs($fp, json_encode($json) . "\n");
};

foreach (glob(__DIR__ . '/kmzs/*/') as $kmz_dir) {
    if (!preg_match('#kmzs/([0-9]*)/#', $kmz_dir, $matches)) {
        continue;
    }
    $id = $matches[1];
    $fp = fopen(__DIR__ . '/geojsons/' . $id . '.json', 'w');
    $first = true;
    fputs($fp, "{\n");;
    fputs($fp, '"type": "FeatureCollection","features": [' . "\n");

    foreach (glob(__DIR__ . "/kmzs/{$id}/*.kmz") as $kmz_file) {
        error_log($kmz_file);
        $doc = new DOMDocument;
        if (!preg_match('#([^/]*)\.kmz#', $kmz_file, $matches)) {
            continue;
        }
        $f = $matches[1];
        $zip_file = "zip://{$kmz_file}#{$f}.kml";
        $doc->loadXML(file_get_contents($zip_file));

        foreach ($doc->getElementsByTagName('Model') as $model_dom) {
            $location_dom = $model_dom->getElementsByTagName('Location')->item(0);
            $location = array();
            foreach ($location_dom->childNodes as $n) {
                if ($n->nodeType != 1) {
                    continue;
                }
                $location[$n->nodeName] = floatval($n->nodeValue);
            }
            $href = $model_dom->getElementsByTagName('href')->item(0)->nodeValue;
            $dae_content = file_get_contents("zip://{$kmz_file}#{$href}");
            $dae_doc = new DOMDocument;
            $dae_doc->loadXML($dae_content);

            foreach ($dae_doc->getElementsByTagName('instance_geometry') as $instance_geometry_dom) {
                $target = $instance_geometry_dom->getAttribute('url');
                foreach ($dae_doc->getElementsByTagName('geometry') as $geometry_dom) {
                    if ($geometry_dom->getAttribute('id') == ltrim($target, '#')) {
                        break;
                    }
                }
                $points = array_chunk(array_map('floatval', preg_split('#\s+#', trim($geometry_dom->getElementsByTagName('float_array')->item(0)->nodeValue))), 3);


                $prev_height = null;
                $polygons = array();
                foreach ($points as $point) {
                    if (is_null($prev_height) or $point[2] != $prev_height) {
                        if (!is_null($prev_height)) {
                            $print_polygons($polygons, $prev_height, $location, $first, $fp);
                        }
                        $prev_height = null;
                        $polygons = array();
                    }

                    $prev_height = $point[2];
                    $polygons[] = array($point[0], $point[1]);
                    if (count($polygons) > 1 and $point[0] == $polygons[0][0] and $point[1] == $polygons[0][1]) {
                    }
                }
                $print_polygons($polygons, $prev_height, $location, $first, $fp);
            }
        }
    }
    fputs($fp, "]}\n");
    fclose($fp);
}
