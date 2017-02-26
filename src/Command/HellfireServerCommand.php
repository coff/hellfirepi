<?php

namespace Coff\Hellfire\Command;

use Coff\Hellfire\Server\HellfireServer;
use Pimple\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * HellfireServerCommand
 *
 *
 */
class HellfireServerCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('server:hellfire')
            ->setDescription('Starts main hellfire server without other required processes');
            ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Container $container */
        $container = $this->getContainer();

        /** @var HellfireServer $server */
        $server = $container['server:hellfire'];

        $server->loop();
    }
}
