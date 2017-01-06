<?php

function loop() {

    $descriptionsLeft = [
        0 => "Other",
        1 => "Radiator system IN (hot)",
        2 => "Radiator system OUT (cold)",
        3 => "Heat source OUT (hot)",
        4 => "Heat source IN (cold)",
        5 => "Buffer tank 20% (counting from bottom to top)",
        7 => "Buffer tank 40% (counting from bottom to top)",
        8 => "Buffer tank 60% (counting from bottom to top)",
        9 => "Buffer tank 80% (counting from bottom to top)",
        10=> "Buffer tank 100% (counting from bottom to top)",
    ];

    $alreadyDiscovered = [];
    $noNewDiscoveries = 0;
    while(true) {
        $noNewDiscoveries++;

        foreach (new DirectoryIterator('/sys/bus/w1/devices/') as $fileInfo) {


            # not interested
            if($fileInfo->isDot()) {
                continue;
            }

            # already discovered
            if (isset($alreadyDiscovered[$fileInfo->getPathname()])) {
                continue;
            }

            $noNewDiscoveries = 0;

            foreach ($descriptionsLeft as $key => $descr) {
                echo $key.'. '.$descr.PHP_EOL;
            }

            $descrId = readline('Choose sensor type for ' . $fileInfo->getFilename() . ':');

            $alreadyDiscovered[$fileInfo->getFilename()] = array($descrId, $descriptionsLeft[$descrId]);
            if ($descrId !== 0) {
                unset($descriptionsLeft[$descrId]);
            }
        }


        if ($noNewDiscoveries > 0) {
            sleep(1);
        }

        if ($noNewDiscoveries > 15) {
            $readline = readline("No sensors, continue? [Y/N]");
            if ($readline == 'Y') {
                break;
            }
        }
    }



}
