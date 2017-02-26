#!/usr/bin/php
<?php

namespace Coff\Hellfire;

use Coff\Hellfire\Application\HellfireApplication;
use Coff\Hellfire\Command\FixPermissionsInstallCommand;
use Coff\Hellfire\Command\HellfireServerCommand;
use Coff\Hellfire\Command\RelaysTestCommand;
use Coff\Hellfire\Command\StorageInstallCommand;
use Coff\Hellfire\Command\W1ServerCommand;
use Pimple\Container;

require __DIR__ . '/vendor/autoload.php';

$container = new Container();

require (__DIR__ . '/app/bootstrap.php');

$app = new HellfireApplication('HellfirePi', '0.0.1');
$app->setContainer($container);

$app->add(new StorageInstallCommand());
$app->add(new HellfireServerCommand());
$app->add(new W1ServerCommand());
$app->add(new RelaysTestCommand());
$app->add(new FixPermissionsInstallCommand());


$app->run();

