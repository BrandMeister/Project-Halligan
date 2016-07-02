<?php
/**
 * Copyright (c) 2016 The BrandMeister Development Team - All rights reserved
 * This file is a part of Project Halligan (the official BrandMeister Dashboard)
 * and is licenced to you under the MIT License (MIT); you may not use this file except
 * in compliance with the MIT license. You may obtain a copy of the MIT license at
 *
 *     https://opensource.org/licenses/MIT
 *
 * For more information about BrandMeister, see http://brandmeister.network
 *
 * Unless required by applicable law or agreed to in writing, software distributed
 * under the MIT License is provided "AS IS", without warranty of any kind, express
 * or implied, including but not limited to the warranties of merchantability, fitness
 * for a particular purpose and non-infringement. In no event shall the authors or
 * copyright holders be liable for any claim, damages or other liability, whether in
 * an action of contract, tort or otherwise, arising from, out of or in connection
 * with the software or the use or other dealings in the software.
 */

$time_start = microtime(true);

if (php_sapi_name() !== 'cli') {
    die("This script should be ran in the command line!");
}

if (count($argv) < 2) {
    $argv = array_merge($argv, glob("*.json"));
}

$basefile = 'en.json';
$base = json_decode(file_get_contents($basefile), true);
$serr = fopen('php://stderr', 'w+');
$total = 0;

fprintf($serr, "\nUsing %s as default language file\n\n", $basefile);

foreach (array_slice($argv, 1) as $name) {
    if (preg_match('/\.json$/', $name)) {
        list($name, $_) = explode('.', $name);
    }
    $file = sprintf("%s.json", $name);
    if (!file_exists($file)) {
        fprintf($serr, "%s: no such file\n", $file);
        continue;
    }
    if ($file == $basefile) {
        continue;
    }

    $lang = json_decode(file_get_contents($file), true);
    $errs = 0;
    foreach ($base as $section => $trans) {
        if (!isset($lang[$section])) {
            printf("%s: section %s missing\n", $name, $section);
            $errs++;
        } else {
            foreach ($trans as $key => $value) {
                if (!isset($lang[$section][$key])) {
                    printf("%s: key %s.%s missing\n", $name, $section, $key);
                    $errs++;
                }
            }
        }
    }
    if ($errs) {
        fprintf($serr, "%s: %d problem(s) need fixing\n\n", $name, $errs);
    } else {
        fprintf($serr, "%s: all good\n\n", $name);
    }
    $total += $errs;
}

$time_end = microtime(true);
$time = round($time_end-$time_start,3);
fprintf($serr, "Finished language checks in %ss with %s error(s)!\n", $time, $total);