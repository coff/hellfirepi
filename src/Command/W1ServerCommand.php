<?php

namespace Coff\Hellfire\Command;

use Coff\OneWire\Server\W1Server;
use Pimple\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * W1ServerCommand
 *
 * Launches One-Wire server service
 */
class W1ServerCommand extends Command
{
    protected $logFilename = 'onewire-server.log';

    public function configure()
    {
        $this
            ->setName('server:one-wire')
            ->setDescription('Starts One-Wire sensors server (It\'s required to run main server)')
            ->addOption('--socket-override', '-s', InputOption::VALUE_NONE, 'use to overwrite old socket handle')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Container $container */
        $container = $this->getContainer();

        $container['running_command'] = $this;
        $container['interface:input'] = $input;

        /** @var W1Server $server */
        $server = $container['server:one-wire'];

        $server->loop();
    }

}
