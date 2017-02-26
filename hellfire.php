#!/usr/bin/php
<?php

namespace Coff\Hellfire;

use Coff\Hellfire\Application\HellfireApplication;
use Coff\Hellfire\Command\CreateStorageCommand;
use Coff\Hellfire\Command\HellfireServerCommand;
use Coff\Hellfire\Command\W1ServerCommand;

require (__DIR__ . '/app/bootstrap.php');

$app = new HellfireApplication('HellfirePi', '0.0.1');
$app->setContainer($container);

$app->add(new CreateStorageCommand());
$app->add(new HellfireServerCommand());
$app->add(new W1ServerCommand());


$app->run();

