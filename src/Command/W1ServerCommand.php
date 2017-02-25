<?php

namespace Coff\Hellfire\Command;

use Symfony\Component\Console\Command\Command;
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
        $this->setName('hellfire:w1-server');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
