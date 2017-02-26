<?php

namespace Coff\Hellfire\Command;

use Coff\OneWire\Server\W1Server;
use Pimple\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * W1ServerCommand
 *
 * Launches One-Wire server service
 */
class W1ServerCommand extends HellfireCommand
{
    public function configure()
    {
        $this->setName('w1-server');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Container $container */
        $container = $this->getContainer();

        /** @var W1Server $server */
        $server = $container['server:one-wire'];

        $server->loop();
    }
}
