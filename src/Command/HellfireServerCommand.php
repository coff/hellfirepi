<?php

namespace Coff\Hellfire\Command;

use Casadatos\Component\Dashboard\Dashboard;
use Coff\Hellfire\Server\HellfireServer;
use Pimple\Container;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * HellfireServerCommand
 *
 *
 */
class HellfireServerCommand extends Command
{
    protected $logFilename = 'hellfire-server.log';

    public function configure()
    {
        $this
            ->setName('server:hellfire')
            ->setDescription('Starts main hellfire server without other required processes')
            ->addOption('--socket-override', '-s', InputOption::VALUE_NONE, 'use to overwrite old socket handle')
            ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Container $container */
        $container = $this->getContainer();

        $container['running_command'] = $this;
        $container['interface:input'] = $input;


        /** @var HellfireServer $server */
        $server = $container['server:hellfire'];

        $container['logger']->info('Preparing subsystems...');

        /** force init sub-systems */
        $container['system:intake'];
        $container['system:boiler'];
        $container['system:buffer'];
        $container['system:heater'];

        $container['logger']->info('Sub-systems initialized');

        /** @var Dashboard $dashboard */
        $dashboard = $container['dashboard'];

        $dashboard->printHeaders();

        $server->loop();
    }

}
