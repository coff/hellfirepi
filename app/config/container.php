<?php

namespace Hellfire;

use Coff\Hellfire\ComponentArray\DataSourceArray;
use Coff\Hellfire\ComponentArray\Adapter\DatabaseStorageAdapter;
use Coff\Hellfire\Relay\Relay;
use Coff\Hellfire\Server\HellfireServer;
use Coff\Hellfire\System\AirIntakeSystem;
use Coff\Hellfire\System\BoilerSystem;
use Coff\Hellfire\System\BufferSystem;
use Coff\Hellfire\System\HeaterSystem;
use Coff\Max6675\Max6675DataSource;
use Coff\OneWire\Client\AsyncW1Client;
use Coff\OneWire\ClientTransport\XmlW1ClientTransport;
use Coff\OneWire\Sensor\DS18B20Sensor;
use Coff\OneWire\Server\W1Server;
use Coff\OneWire\ServerTransport\XmlW1ServerTransport;
use PiPHP\GPIO\GPIO;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\ConsoleOutput;


$container['logger'] = function () {
    $logger = new ConsoleLogger(new ConsoleOutput(ConsoleOutput::VERBOSITY_DEBUG, $isDecorated=true, new OutputFormatter()));
    return $logger;
};

$container['pdo'] = function () {
    return new \PDO('mysql:dbname=hellfirepi;unix_socket=/var/run/mysqld/mysqld.sock', 'hellfire', '666fire');
};

$container['client:one-wire'] = function($c) {
    $client = new AsyncW1Client('unix:///tmp/w1server.socket');
    $client->setLogger($c['logger']);
    $client->setTransport(new XmlW1ClientTransport());
    $client->init();


    return $client;
};

$container['server:one-wire'] = function($c) {
    $server = new W1Server('unix:///tmp/w1server.socket');
    $server->setLogger($c['logger']);
    $server->setTransport(new XmlW1ServerTransport());
    $server->init();
    return $server;
};

$container['server:hellfire'] = function ($c) {
    $server = new HellfireServer('unix:///tmp/hellfire.socket');
    $server->setLogger($c['logger']);
    $server->setContainer($c);
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

    $i=0;
    foreach($gpioNumbers as $number) {
        $pin = $gpio->getOutputPin($number);
        $relays[$i++] = $relay = new Relay(Relay::NORMALLY_ON, Relay::STATE_OFF);
        $relay->setPin($pin);
        $relay->init();
    }

    return $relays;
};

$container['data-sources:one-wire'] = function($c) {

    /** @var AsyncW1Client $w1Client */
    $w1Client = $c['client:one-wire'];

    $w1DataSources = new DataSourceArray();

    $w1Sensors = array ('28-0000084a49a8', '28-0000084b947a',
        '28-00000891595f', '28-0000088fc71c', '28-0416747d17ff');

    foreach ($w1Sensors as $sensorId) {
        $ds = $w1Client->createDataSourceById($sensorId);
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

    /** to register intake system's state */
    $allDataSources['intake']    = $c['system:intake'];

    /**
     * Relays state's are to be register too
     * @var DataSourceArray $relays
     */
    $relays = $c['data-sources:relays'];

    foreach ($relays as $key => $relay) {
        $allDataSources['relay:' . $key] = $relay;
    }
    return $allDataSources;
};

$container['data-sources-storage'] = function ($c) {
    $storage = new DatabaseStorageAdapter($c['data-sources:all']);
    $storage->setPdo($c['pdo']);

    return $storage;
};

$container['system:boiler'] = function($c) {
    $boilerSensors = new DataSourceArray();
    $boilerSensors[BoilerSystem::SENSOR_HIGH] = $c['data-sources:one-wire']['28-0416747d17ff'];
    $boilerSensors[BoilerSystem::SENSOR_LOW] = $c['data-sources:one-wire']['28-0000084a49a8'];

    $boiler = new BoilerSystem();
    $boiler
        ->setPump($c['data-sources:relays'][0])
        ->setExhaustSensor($c['data-sources:all']['max6675:0'])
        ->setAirIntake($c['system:intake'])
        ->setSensorArray($boilerSensors);

    return $boiler;
};

$container['system:heater'] = function ($c) {
    $heaterSensors = new DataSourceArray();
    $heaterSensors[HeaterSystem::SENSOR_HIGH] = $c['data-sources:one-wire']['28-00000891595f'];
    $heaterSensors[HeaterSystem::SENSOR_LOW] = $c['data-sources:one-wire']['28-0000088fc71c'];

    $heater = new HeaterSystem();
    $heater
        ->setPump($c['data-sources:relays'][1])
        ->setSensorArray($heaterSensors);

    return $heater;
};

$container['system:buffer'] = function ($c) {
    $bufferSensors = new DataSourceArray();
    // @todo install high sensor on buffer tank
    //$bufferSensors[BufferSystem::SENSOR_HIGH] = $c['data-sources:one-wire'][''];
    $bufferSensors[BufferSystem::SENSOR_LOW] = $c['data-sources:one-wire']['28-0000084b947a'];

    $buffer = new BufferSystem();
    $buffer
        ->setSensorArray($bufferSensors);

    return $buffer;
};

$container['system:intake'] = function() {
    $intake = new AirIntakeSystem();
    $intake->init();

    return $intake;
};

return $container;
