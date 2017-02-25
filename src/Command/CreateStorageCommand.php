<?php

namespace Coff\Hellfire\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * CreateStorageCommand
 *
 * A command for (re-)creating database storage for HellfirePi
 */
class CreateStorageCommand extends Command
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $dropQuery = "DROP TABLE readings";

    /**
     * @var string
     */
    protected $createQuery = <<<SQL
CREATE TABLE `readings` (
  `source` CHAR(16) NOT NULL,
  `stamp` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `value` DECIMAL(10,2) NOT NULL,
PRIMARY KEY (`source`, `stamp`))
SQL;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function configure()
    {
        $this->setName('hellfire:create-storage');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->pdo->beginTransaction();

        $this->pdo->exec($this->dropQuery);
        $this->logger->info('Storage table dropped (if existed).');

        $this->pdo->exec($this->createQuery);
        $this->logger->info('Storage table created.');

        $this->pdo->commit();
    }
}
