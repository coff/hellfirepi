#!/usr/bin/php
<?php

namespace Coff\Hellfire;

use Coff\Hellfire\Application\HellfireApplication;
use Coff\Hellfire\Command\AirIntakeCalibrateCommand;
use Coff\Hellfire\Command\AirIntakeTestCommand;
use Coff\Hellfire\Command\BuzzerTestCommand;
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

/** Install commands */
$app->add(new StorageInstallCommand());
$app->add(new FixPermissionsInstallCommand());

/** Server commands */
$app->add(new W1ServerCommand());
$app->add(new HellfireServerCommand());

/** Test commands */
$app->add(new AirIntakeTestCommand());
$app->add(new RelaysTestCommand());
$app->add(new BuzzerTestCommand());

/** Calibration commands */
$app->add(new AirIntakeCalibrateCommand());


$app->run();

