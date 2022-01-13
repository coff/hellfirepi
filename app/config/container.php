<?php

namespace Hellfire;

use Casadatos\Component\Dashboard\ConsoleDashboard;
use Casadatos\Component\Dashboard\Gauge\DeltaMemoryGauge;
use Casadatos\Component\Dashboard\Gauge\ValueGauge;
use Coff\Hellfire\Buzzer;
use Coff\Hellfire\Command\Command;
use Coff\Hellfire\ComponentArray\BoilerSensorArray;
use Coff\Hellfire\ComponentArray\BufferSensorArray;
use Coff\Hellfire\ComponentArray\DataSourceArray;
use Coff\Hellfire\ComponentArray\Adapter\DatabaseStorageAdapter;
use Coff\Hellfire\ComponentArray\HeaterSensorArray;
use Coff\Hellfire\ComponentArray\RelayArray;
use Coff\Hellfire\Event\Event;
use Coff\Hellfire\EventDispatcher;
use Coff\Hellfire\Relay\Relay;
use Coff\Hellfire\Server\HellfireServer;
use Coff\Hellfire\Servo\AnalogServo;
use Coff\Hellfire\System\AdvancedAirIntakeSystem;
use Coff\Hellfire\System\AirIntakeSystem;
use Coff\Hellfire\System\BoilerSystem;
use Coff\Hellfire\System\BufferSystem;
use Coff\Hellfire\System\FailoverAirIntakeSystem;
use Coff\Hellfire\System\HeaterSystem;
use Coff\Max6675\Max6675DataSource;
use Coff\OneWire\Client\AsyncW1Client;
use Coff\OneWire\ClientTransport\XmlW1ClientTransport;
use Coff\OneWire\Sensor\DS18B20Sensor;
use Coff\OneWire\Sensor\Sensor;
use Coff\OneWire\Server\W1Server;
use Coff\OneWire\ServerTransport\XmlW1ServerTransport;
use Coff\Hellfire\Sensor\ExhaustSensor;
use Monolog\Logger;
use PiPHP\GPIO\GPIO;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\StreamOutput;
use Volantus\Pigpio\Client;
use Volantus\Pigpio\Network\Socket;
use Volantus\Pigpio\SPI\RegularSpiDevice;

/*
 * @todo extract configuration parameters into a separate readable config file.
 */

$container['logger'] = function ($c) {

    /** @var Command $command  */
    $command = $c['running_command'];

    /* each command should tell us its logfile name */
    $res = fopen('../' . $command->getLogFilename(), 'a');
    $output = new StreamOutput($res, StreamOutput::VERBOSITY_DEBUG, $isDecorated=true, new OutputFormatter());
    $logger = new ConsoleLogger($output);
    $logger->info('Logger initialized');
    return $logger;
};

$container['buzzer'] = function($c) {
    /** @var GPIO $gpio */
    $gpio = $c['gpio'];

    $pin = $gpio->getOutputPin(17);

    return new Buzzer($pin, Buzzer::EMITS_ON_LOW);
};

$container['dashboard'] = function() {
    /** @todo use NullDashboard when in daemon mode! */
    $dash = new ConsoleDashboard();

    $dash
        ->add('mem_used', new DeltaMemoryGauge(8))
        ->add('stamp', new ValueGauge(8))
        ;

    return $dash;
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
    $socketPath = '/tmp/w1server.socket';

    /** @var InputInterface $input */
    $input = $c['interface:input'];

    if ($input->hasOption('socket-override') && file_exists($socketPath)) {
        unlink($socketPath);
    }

    $server = new W1Server('unix://' . $socketPath);

    $server->setLogger($c['logger']);
    $server->setTransport(new XmlW1ServerTransport());
    $server->init();
    return $server;
};

$container['server:hellfire'] = function ($c) {

    $socketPath = '/tmp/hellfire.socket';

    /** @var InputInterface $input */
    $input = $c['interface:input'];

    if ($input->hasOption('socket-override') && file_exists($socketPath)) {
        unlink($socketPath);
    }

    $server = new HellfireServer('unix://' . $socketPath);
    $server->setLogger($c['logger']);
    $server->setContainer($c);
    $server->setEventDispatcher($c['event:dispatcher']);
    $server->init();
    return $server;
};

$container['gpio'] = function () {
    return new GPIO();
};

$container['data-sources:relays'] = function($c) {

    /** @var GPIO $gpio */
    $gpio = $c['gpio'];

    /** @var array $gpioNumbers according to BCM numeration */
    $gpioNumbers = [16, 20, 21, 26, 19, 13, 6, 5];

    $relays = new RelayArray();

    $i=0;
    foreach($gpioNumbers as $number) {
        $pin = $gpio->getOutputPin($number);
        $relays[$i++] = $relay = new Relay(Relay::NORMALLY_ON, Relay::STATE_OFF);
        $relay->setPin($pin);
        $relay->init();
    }

    $relays[7]->on(); // self-powering

    return $relays;
};

$container['data-sources:one-wire'] = function($c) {

    /** @var AsyncW1Client $w1Client */
    $w1Client = $c['client:one-wire'];

    $w1DataSources = new DataSourceArray();

    $w1Sensors = array (
        '28-0000084a49a8',
        '28-0000084b947a',
        '28-00000891595f',
        '28-0000088fc71c',
        '28-0416747d17ff',
        '28-051685dc73ff',
        '28-0316848610ff');

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

    $dataSource = new Max6675DataSource();
    $device = new RegularSpiDevice($c['client:pigpio'], 1, 32000);
    $device->open();
    $dataSource->setSpiDevice($device);

    $allDataSources['max6675:0'] = $thermocouple = new ExhaustSensor($dataSource);

    $thermocouple->init();

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

$container['data-sources:boiler'] = function ($c) {
    $boilerSensors = new BoilerSensorArray();

    /** @var Sensor $high */
    $boilerSensors[BoilerSensorArray::SENSOR_HIGH] = $high = $c['data-sources:one-wire']['28-0416747d17ff'];

    /** @var Sensor $low */
    $boilerSensors[BoilerSensorArray::SENSOR_LOW] = $low = $c['data-sources:one-wire']['28-0000084a49a8'];

    /**
     * Needs correction perhaps due to poor sensor installation.
     */
    $high->setCorrection(9);
    $low->setCorrection(1);

    /** boiler output temp. target  */
    $boilerSensors->setTargets(BoilerSensorArray::SENSOR_HIGH, 86, 2);

    /** boiler input temp. target */
    $boilerSensors->setTargets(BoilerSensorArray::SENSOR_LOW, 60, 2);

    return $boilerSensors;
};

$container['system:boiler'] = function($c) {
    $boiler = new BoilerSystem();

    $boiler->setLogger($c['logger']);
    $boiler
        ->setContainer($c)
        ->setDashboard($c['dashboard'])
        ->setEventDispatcher($c['event:dispatcher'])
        ->setPump($c['data-sources:relays'][0])
        ->setSensorArray($c['data-sources:boiler'])
        ->init();

    return $boiler;
};

$container['system:heater'] = function ($c) {
    $heaterSensors = new DataSourceArray();

    /** @var Sensor $high */
    $heaterSensors[HeaterSensorArray::SENSOR_HIGH] = $high = $c['data-sources:one-wire']['28-051685dc73ff'];

    /** @var Sensor $low */
    $heaterSensors[HeaterSensorArray::SENSOR_LOW] = $low = $c['data-sources:one-wire']['28-0316848610ff'];

    /**
     * Needs correction perhaps due to poor sensor installation.
     */
    $high->setCorrection(6);
    $low->setCorrection(6);


    $heater = new HeaterSystem();
    $heater->setLogger($c['logger']);
    $heater
        ->setContainer($c)
        ->setDashboard($c['dashboard'])
        ->setEventDispatcher($c['event:dispatcher'])
        ->setPump($c['data-sources:relays'][1])
        ->setSensorArray($heaterSensors)
        ->setRoomTempSensor($c['data-sources:one-wire']['28-00000891595f'])
        ->setTargetRoomTemp(21.3, 0.3)
        ->init()
        ;

    return $heater;
};

$container['system:buffer'] = function ($c) {
    $bufferSensors = new BufferSensorArray();
    $bufferSensors->setCapacity(1600);

    // @todo install high sensor on buffer tank
    $bufferSensors[BufferSensorArray::SENSOR_HIGH] = $c['data-sources:one-wire']['28-0000088fc71c'];
    $bufferSensors[BufferSensorArray::SENSOR_LOW] = $c['data-sources:one-wire']['28-0000084b947a'];

    $buffer = new BufferSystem();
    $buffer->setLogger($c['logger']);
    $buffer
        ->setContainer($c)
        ->setDashboard($c['dashboard'])
        ->setEventDispatcher($c['event:dispatcher'])
        ->setSensorArray($bufferSensors)
        ->init();
        ;

    return $buffer;
};

$container['client:pigpio'] = function($c) {
    return new Client(new Socket('127.0.0.1', 8888));
};

$container['system:intake'] = function($c) {

    /** @var DataSourceArray $boilerSensors */
    $boilerSensors = $c['data-sources:boiler'];

    /** @var Logger $logger */
    $logger = $c['logger'];

    $servo = new AnalogServo(800,2350);

    $logger->info('Initializing servo...');

    $servo
        ->setPigpioClient($c['client:pigpio'])
        ->setGpio(22)
        ->setStepLength(20)
        ->init();

    $logger->info('Servo initialized.');

    $intake = new FailoverAirIntakeSystem();
    $intake->setLogger($logger);
    $intake
        ->setContainer($c)
        ->setDashboard($c['dashboard'])
        ->setEventDispatcher($c['event:dispatcher'])
        ->setExhaustSensor($c['data-sources:all']['max6675:0'])
        ->setSensorArray($boilerSensors)
        ->setServo($servo)
        ->init()
        ;

    $servo->init(); // sets arm to initial position

    /** @var DataSourceArray $dataSourcesAll */
    $dataSourcesAll = $c['data-sources:all'];

    /** to register intake system's state */
    $dataSourcesAll['intake']    = $intake;

    return $intake;
};

$container['event:dispatcher'] = function($c) {
    Event::setContainer($c);
    $eventDispatcher = new EventDispatcher();
    $eventDispatcher->setLogger($c['logger']);
    return $eventDispatcher;
};


return $container;
