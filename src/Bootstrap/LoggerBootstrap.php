<?php


namespace Coff\Hellfire\Bootstrap;


class LoggerBootstrap extends Bootstrap
{
    public function init()
    {
        $this->container['logger'] = function ($c) {

            /** @var Command $command  */
            $command = $c['running_command'];

            /* each command should tell us its logfile name */
            $res = fopen('../' . $command->getLogFilename(), 'a');
            $output = new StreamOutput($res, StreamOutput::VERBOSITY_DEBUG, $isDecorated=true, new OutputFormatter());
            $logger = new ConsoleLogger($output);
            $logger->info('Logger initialized');
            return $logger;
        };
    }
}