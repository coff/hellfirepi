<?php

namespace Coff\Hellfire\Command;

use Coff\Hellfire\Application\HellfireApplication;
use Coff\Hellfire\Server\HellfireServer;
use Pimple\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HellfireServerCommand extends HellfireCommand
{
    public function configure()
    {
        $this->setName('hellfire-server');
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
