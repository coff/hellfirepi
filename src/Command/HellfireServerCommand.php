<?php

namespace Coff\Hellfire\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HellfireServerCommand extends Command
{
    public function configure()
    {
        $this->setName('hellfire:hellfire-server');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
