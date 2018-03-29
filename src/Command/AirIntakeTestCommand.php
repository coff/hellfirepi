<?php

namespace Coff\Hellfire\Command;

use Coff\Hellfire\System\AirIntakeSystem;
use Pimple\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AirIntakeTestCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('test:intake')
            ->setDescription('Performs tests on air-intake shutter. Be careful with this!')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        /** @var Container $container */
        $container = $this->getContainer();
        $container['running_command'] = $this;

        $output->writeln('Air Intake System init'); $this->sleep();

        /** @var AirIntakeSystem $airIntake */
        $airIntake = $container['system:intake'];

        $output->writeln('Full open'); $this->sleep();
        $airIntake->open();

        $output->writeln('Full close'); $this->sleep();
        $airIntake->close();

        $output->writeln('Step by step up:'); $this->sleep();
        while (!$airIntake->getServo()->isMax()) {
            $airIntake->stepUp();
            $output->writeln($airIntake->getServo()->getSignalLength());
            $this->sleep(0.5);
        }
        $output->writeln('');

        $output->writeln('Step by step down:'); $this->sleep();
        while (!$airIntake->getServo()->isMin()) {
            $airIntake->stepDown();
            $output->writeln($airIntake->getServo()->getSignalLength());
            $this->sleep(0.5);
        }
        $output->writeln('');

        $output->writeln('Back to initial position'); $this->sleep();
        $airIntake->init();

        $output->writeln('Thank you!');
    }

    private function sleep($rel=1) {
        usleep($rel * 500000);
    }
}
