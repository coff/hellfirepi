<?php

namespace Coff\Hellfire\Command;

use Coff\Hellfire\ComponentArray\DataSourceArray;
use Coff\Hellfire\ComponentArray\RelayArray;
use Coff\Hellfire\Relay\Relay;
use Pimple\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * RelaysTestCommand
 *
 *
 */
class RelaysTestCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('test:relays')
            ->setDescription('Performs tests (turns them off and on) over all configured relays')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Container $container */
        $container = $this->getContainer();

        /** @var RelayArray $relays */
        $relays = $container['data-sources:relays'];

        $output->writeln('Relays configured on pins (BCM):');

        /** @var Relay $relay */
        foreach ($relays as $relay) {
            $output->write($relay->getPin()->getNumber() . ' ');
        }
        $output->writeln('');

        $relays->off();

        $relays->on();

        foreach ($relays as $key => $relay) {
            usleep(500000);
            $output->writeln('Relay ' . $key . ' on pin ' . $relay->getPin()->getNumber() . ' off');
            $relay->off();
        }

        foreach ($relays as $key => $relay) {
            usleep(500000);
            $output->writeln('Relay ' . $key . ' on pin ' . $relay->getPin()->getNumber() . ' on');
            $relay->on();
        }

        for ($i=0;$i<3;$i++) {
            usleep(500000);
            $relays->off();

            usleep(500000);
            $relays->on();
        }

    }

}
