<?php

namespace Coff\Hellfire\Command;

use Coff\Hellfire\Buzzer;
use Coff\Hellfire\BuzzerNotes;
use Pimple\Container;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuzzerTestCommand extends Command
{
    public function configure()
    {
        $this
            ->setName('test:buzzer')
            ->setDescription('Performs tests on buzzer')
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var Container $container */
        $container = $this->getContainer();

        /** @var Buzzer $buzzer */
        $buzzer = $container['buzzer'];

        $notes = new BuzzerNotes();

        $notes->on(500000);
        $notes->off(500000);
        $notes->on(500000);
        $notes->off(500000);
        $notes->on(500000);
        $notes->off(500000);

        $buzzer->setNotes($notes);

        $output->writeln('Trying to play in foreground...');

        $buzzer->play(Buzzer::PLAY_IN_FG);

        $output->writeln('Have you heard any sound?');

        sleep(2);

        $notes = new BuzzerNotes();
        $notes->add([1,0,1,0,1,0], 50000);
        $notes->add([1,0,1,0,1,0], 100000);
        $notes->add([1,0,1,0,1,0], 200000);
        $notes->add([1,0,1,0,1,0], 300000);

        $buzzer->setNotes($notes);

        $output->writeln('Trying to play in background and exiting immediately...');

        $buzzer->play(Buzzer::PLAY_IN_BG);
    }
}
