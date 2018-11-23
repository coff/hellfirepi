<?php


namespace Coff\Hellfire\Command;

use Coff\Hellfire\Sensor\ExhaustSensor;
use Coff\Max6675\Max6675DataSource;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Volantus\BerrySpi\RegularInterface;

class ThermocoupleTestCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('test:thermocouple')
            ->setDescription('Performs tests on thermocouple readings!')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        //RegularInterface::initialize();

        $container = $this->getContainer();
        $container['running_command'] = $this;

        /** @var ExhaustSensor $tc */
        $tc = $container['data-sources:all']['max6675:0'];

        while (true) {
            try {
                $output->write('Updating sensor reading...');
                $tc->update();

                $output->writeln('reading value ' . $tc->getValue());
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
            }

            sleep(1);

        }

        readline();
    }
}
