<?php

namespace Hellfire\Server;

use Hellfire\Exception\ServerException;
use Psr\Log\LogLevel;

/**
 * W1Server - one wire protocol sensors server
 *
 * Reading from 1-wire sensors usually takes some time so this server takes care
 * of doing the dirty stuff for you.
 */
class W1Server extends Server
{
    const
        READING_OUTDATED_MIN = 3, // seconds
        DISCOVERY_TIMEOUT = 60; // seconds


    /**
     * @var \DirectoryIterator[] $sensors
     */
    protected $sensors;

    /**
     * @var resource[] $sensorStreams
     */
    protected $sensorStreams;

    /**
     * @var array $sensorReadings
     */
    protected $sensorReadings;

    /**
     * @var int
     */
    protected $lastQueryTime;

    /**
     * @var int
     */
    protected $lastDiscoveryTime;

    /**
     * @var bool
     */
    protected $allCollected=false;

    /**
     * @var int $peerTimeout server peer timeout in seconds
     */
    protected $peerTimeout=1;

    public function init() {
        parent::init();

        $dir = new \DirectoryIterator('/sys/devices/w1_bus_master1');

        $this->sensors = array();
        foreach($dir as $fileInfo) {
            try {
                if ($fileInfo->isDot()) {
                    continue;
                }

                if (false === file_exists($sensorPath = $fileInfo->getPathname() . '/w1_slave')) {
                    continue;
                }


                $this->sensors[$fileInfo->getFilename()] = $sensorPath;
            } catch (ServerException $e) {
                $this->logger->log(LogLevel::WARNING, 'Sensor discovery failed for ' . $fileInfo->getPathname() );
            }
        }
        $this->lastDiscoveryTime = time();
    }

    protected function querySensors() {
        $this->sensorStreams = array();

        foreach ($this->sensors as $key => $sensorPath) {
            try {

                $stream = popen('cat ' . $sensorPath, 'r');

                if (false === $stream) {
                    throw new ServerException("Couldn't open process stream ", $sensorPath);
                }

                $res = stream_set_blocking($stream, false);
                if (false === $res) {
                    throw new ServerException("Couldn't set non-blocking mode for stream", $sensorPath);
                }
                $this->sensorStreams[$key] = $stream;
            } catch (ServerException $e) {
                $this->logger->log(LogLevel::ALERT, $e->getMessage(), $e->getCode());
            }

        }

        $this->lastQueryTime = time();
    }

    protected function parseReading($sensorId, $reading) {
        $lines = explode("\n",$reading);
        $crcLine = trim($lines[0]);
        $valueLine = trim($lines[1]);
        if (substr($crcLine,-1) == 'S') { // YE(S)
            $this->sensorReadings[$sensorId] = $rd = [
                'value' => (double)explode('=', substr($valueLine,-8))[1] / 1000,
                'stamp' => time(),
                ];
        }
    }

    protected function readReadings($streams) {
        foreach ($streams as $key => $stream) {
            $s = fread($stream, 200);
            if ($s) {
                $this->parseReading($key, $s);
                if (feof($stream)) {
                    pclose($stream);
                    unset($this->sensorStreams[$key]);

                    if (!$this->sensorStreams) {
                        $this->allCollected = true;
                    }
                }
            }

        }
    }

    public function getReadingsResponse ($sensors, \SimpleXMLElement $response) {

        foreach ($sensors as $sensor) {
            $sensorId = (string) $sensor;
            $sensorResp = $response->addChild('Sensor');
            $sensorResp->addAttribute('sensorId', $sensorId);

            if (false == isset($this->sensors[$sensorId])) {
                $errorResp = $sensorResp->addChild('error', 'Sensor not found');
                $errorResp->addAttribute('code', 1);
                $errorResp->addAttribute('sensorId', $sensorId);
                continue;
            }

            if (false == isset($this->sensorReadings[$sensorId])) {
                $errorResp = $sensorResp->addChild('error', 'Sensor reading not yet available or unavailable');
                $errorResp->addAttribute('code', 2);
                $errorResp->addAttribute('sensorId', $sensorId);
                continue;
            }

            $readingResp = $sensorResp->addChild('Reading', $this->sensorReadings[$sensorId]['value']);
            $readingResp->addAttribute('stamp', $this->sensorReadings[$sensorId]['stamp']);
        }
    }

    public function each() {

        /**
         * Perform sensor discovery each at self::DISCOVERY_TIMEOUT
         */
        if ($this->lastDiscoveryTime < time()-self::DISCOVERY_TIMEOUT && true === $this->allCollected) {
            $this->init(); // performs discovery
        }
        echo '.';
        /**
         * Query sensors' readings each self::READING_OUTDATED_MIN
         */
        if ($this->lastQueryTime < time()-self::READING_OUTDATED_MIN) {
            $this->querySensors();
        }

        /**
         * Got any reply from opened processes?
         */
        if ($this->sensorStreams && 0 < stream_select($streams = $this->sensorStreams, $w=null, $o=null, 0, $this->sleepTime)) {
            $this->readReadings($streams);
        }

        /**
         * Or any incoming client connection?
         */
        if (0 < stream_select($sockets = array($this->socket), $w=null, $o=null, 0, $this->sleepTime)) {
            try {

                $connection = stream_socket_accept($this->socket, $this->peerTimeout, $peerName = '');
                $this->logger->log(LogLevel::INFO, 'Incoming connection from peer ' . $peerName);

                $dataQueryString = fread($connection, 2048);

                echo $dataQueryString;
                $dataQuery = simplexml_load_string($dataQueryString);
                $response = new \SimpleXMLElement('<Response/>');

                if (count($dataQuery->Sensors) > 0) {
                    $this->getReadingsResponse($dataQuery->Sensors, $response);
                } else {
                    $this->getReadingsResponse(array_keys($this->sensors), $response);
                }

                fwrite($connection, $response->asXML());
                fclose($connection);
            } catch (\Exception $e) {
                if (isset($connection) && is_resource($connection)) {
                    fwrite($connection, "<?xml version=\"1.0\"?>\n<Response><error code=\"0\">" . $e->getMessage() . "</error></Response>");
                }
                $this->logger->log(LogLevel::ERROR, 'Peer error: ' . $e->getMessage());
            }
        }

    }
}
