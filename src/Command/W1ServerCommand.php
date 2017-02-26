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
class W1ServerCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('server:one-wire')
            ->setDescription('Starts One-Wire sensors server (It\'s required to run main server)')
            ;
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
