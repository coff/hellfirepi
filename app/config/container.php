<?php

namespace Hellfire;

use Coff\Hellfire\ComponentArray\DataSourceArray;
use Coff\Hellfire\ComponentArray\Adapter\DatabaseStorageAdapter;
use Coff\Hellfire\Relay\Relay;
use Coff\Hellfire\Server\HellfireServer;
use Coff\Max6675\Max6675DataSource;
use Coff\OneWire\DataSource\W1ServerDataSource;
use Coff\OneWire\Sensor\DS18B20Sensor;
use Coff\OneWire\Server\W1Server;
use Pimple\Container;
use PiPHP\GPIO\GPIO;

$container = new Container();

$container['pdo'] = function () {
    return new \PDO('localhost', 'hellfire', '666fire');
};

$container['server:one-wire'] = function() {
    $server = new W1Server('/tmp/w1server.socket');
    $server->init();

    return $server;
};

$container['server:hellfire'] = function () {
    $server = new HellfireServer('/tmp/hellfire.socket');
    $server->init();

    return $server;
};

$container['gpio'] = function () {
    return new GPIO();
};

$container['data-sources:relays'] = function($c) {

    /** @var GPIO $gpio */
    $gpio = $c['gpio'];

    $gpioNumbers = [27, 28, 29, 25, 24, 23, 22, 21];

    $relays = new DataSourceArray();

    foreach($gpioNumbers as $number) {
        $pin = $gpio->getOutputPin($number);
        $relays[$number] = $relay = new Relay(Relay::NORMALLY_ON, Relay::STATE_OFF);
        $relay->setPin($pin);
        $relay->init();
    }

    return $relays;
};

$container['data-sources:one-wire'] = function() {
    $w1DataSources = new DataSourceArray();

    $w1Sensors = array ('28-0000084a49a8', '28-0000084b947a',
        '28-00000891595f', '28-0000088fc71c', '28-0416747d17ff');

    foreach ($w1Sensors as $sensorId) {
        $ds = new W1ServerDataSource($sensorId);
        $sensor = new DS18B20Sensor($ds);
        $w1DataSources[$sensorId] = $sensor;
    }

    return $w1DataSources;
};

$container['data-sources:all'] = function ($c) {
    $allDataSources = new DataSourceArray();

    foreach ($c['data-sources:one-wire'] as $key => $sensor) {
        $allDataSources[$key] = $sensor;
    }

    $allDataSources['max6675:0'] = new Max6675DataSource($busNumber = 0, $cableSelect = 1, $speedHz = 4300000);

    /** @var DataSourceArray $relays */
    $relays = $c['data-sources:relays'];

    $i=0;
    foreach ($relays as $relay) {
        $allDataSources['relay:' . $i++] = $relay;
    }
    return $allDataSources;
};

$container['data-sources-storage'] = function ($c) {
    $storage = new DatabaseStorageAdapter($c['data-sources:all']);
    $storage->setPdo($c['pdo']);
};


