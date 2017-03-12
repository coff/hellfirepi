<?php

namespace Coff\Hellfire\Server;

use Casadatos\Component\Dashboard\Dashboard;
use Coff\Hellfire\ComponentArray\Adapter\DatabaseStorageAdapter;
use Coff\Hellfire\Event\CyclicEvent;
use Coff\OneWire\Client\AsyncW1Client;

class HellfireServer extends Server
{
    /**
     * @var AsyncW1Client
     */
    protected $w1Client;

    /**
     * PDO storage for keeping sensors' readings and control devices' states
     * @var
     */
    protected $storage;

    /**
     * @var \PDO
     */
    protected $pdo;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub

        $this->addShortCycleCallback('3s', [$this, 'every3s']);
        $this->addShortCycleCallback('10s', [$this, 'every10s']);
        $this->addShortCycleCallback('30s', [$this, 'every30s']);
        $this->addShortCycleCallback('1m', [$this, 'every1m']);
        $this->addShortCycleCallback('2m', [$this, 'every2m']);
        $this->addShortCycleCallback('10m', [$this, 'every10m']);

        $this->logger->info('Hellfire server initialized properly');
    }

    public function loop()
    {
        $this->logger->info('Starting server loop');
        return parent::loop(); // TODO: Change the autogenerated stub
    }

    public function each()
    {

        /** @todo Server<->Client communication here? */
    }

    public function every3s() {
        $this->getEventDispatcher()->dispatch(CyclicEvent::EVERY_3_SECOND, new CyclicEvent());

        $container = $this->getContainer();

        $container['client:one-wire']->update();

        /** @var Dashboard $dashboard */
        $dashboard = $this->getContainer()['dashboard'];
        $dashboard->refresh();
    }

    public function every10s() {
        $this->getEventDispatcher()->dispatch(CyclicEvent::EVERY_10_SECOND, new CyclicEvent());
    }

    public function every30s() {
        $this->getEventDispatcher()->dispatch(CyclicEvent::EVERY_30_SECOND, new CyclicEvent());

        /** @var Dashboard $dashboard */
        $dashboard = $this->getContainer()['dashboard'];
        $dashboard->snap();
    }

    public function every1m() {

        $this->getEventDispatcher()->dispatch(CyclicEvent::EVERY_MINUTE, new CyclicEvent());

        /**
         * Store sensors readings
         */
        /** @var DatabaseStorageAdapter $storageAdapter */
        $storageAdapter = $this->container['data-sources-storage'];
        $storageAdapter->store();
        $this->logger->debug('Stored sensor readings.');
    }

    public function every2m() {
        $this->getEventDispatcher()->dispatch(CyclicEvent::EVERY_2_MINUTE, new CyclicEvent());
    }

    public function every10m() {
        $this->getEventDispatcher()->dispatch(CyclicEvent::EVERY_10_MINUTE, new CyclicEvent());

        /** @var Dashboard $dashboard */
        $dashboard = $this->getContainer()['dashboard'];
        $dashboard->printHeaders();
    }


    public function everyNight()
    {
        /**
         * Clean old readings
         */
        /** @var DatabaseStorageAdapter $storageAdapter */
        $storageAdapter = $this->container['data-sources-storage'];
        $storageAdapter->clean();
    }
}
