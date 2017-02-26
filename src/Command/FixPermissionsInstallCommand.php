<?php

namespace Coff\Hellfire\Command;

use Coff\Hellfire\ComponentArray\RelayArray;
use Coff\Hellfire\Relay\Relay;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * FixPermissionsInstallCommand
 *
 *
 */
class FixPermissionsInstallCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('install:fix-permissions')
            ->setDescription('Fixes permissions over RaspberryPi pins so that we can setup them by ourselves. Run through sudo')
            ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** set permissions over RaspberryPi pins */

        $container = $this->getContainer();

        /** pins configured for relays */

        /** @var RelayArray $relays */
        $relays = $container['data-sources:relays'];

        /** @todo perhaps there's some more elegant solution? Our own user? */
        $permissions = '777';

        /** @var Relay $relay */
        foreach($relays as $relay) {
            $pin = $relay->getPin()->getNumber();
            $output->writeln('Sets ' . $permissions . ' permissions for PIN ' . $pin);
            exec('chmod ' . $permissions . ' /sys/class/gpio/gpio' . $pin . '/direction');
        }
    }
}
