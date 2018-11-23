<?php

namespace Coff\Hellfire\Command;

use Coff\Hellfire\System\AirIntakeSystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Volantus\BerrySpi\RegularInterface;

class AirIntakeCalibrateCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('calibrate:intake')
            ->setDescription('Performs calibration on intake shutter. Do it after running tests!')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        RegularInterface::initialize();

        $container = $this->getContainer();
        $container['running_command'] = $this;

        /** @var AirIntakeSystem $intake */
        $intake = $container['system:intake'];

        $servo = $intake->getServo();

        $output->writeln('Make sure you\'re not running hellfire server when calibrating. Press <ENTER> to move arm to the lowest position.');

        readline();

        $servo->setRelative(0);

        $output->writeln('Now adjust shutter cable length so it is neither loose or pulling the shutter up from it\'s resting position');
        $output->writeln('Press <ENTER> when done. Shutter will move up to it\'s highest position.');

        $servo->setRelative(1);

        readline();
    }
}
