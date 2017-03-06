<?php

namespace Coff\Hellfire\ComponentArray\Adapter;

use Coff\DataSource\DataSource;
use Coff\Hellfire\ComponentArray\DataSourceArray;

class DatabaseStorageAdapter
{
    /**
     * @var DataSourceArray
     */
    protected $array;

    protected $feedQuery = <<< SQL
      INSERT INTO 
        readings (`source`, `value`)
      VALUES 
SQL;

    protected $cleanupQuery = <<< SQL
      DELETE FROM 
        readings
      WHERE
        stamp < now() - interval 1 MONTH 
SQL;



    /**
     * @var \PDO
     */
    protected $pdo;

    public function __construct(DataSourceArray $array)
    {
        $this->array = $array;
    }

    public function setPdo(\PDO $pdo) {
        $this->pdo = $pdo;

        return $this;
    }

    public function store() {

        $values = [];
        /**
         * @var string $sourceId
         * @var DataSource $dataSource
         */
        foreach ($this->array as $sourceId => $dataSource) {
            $values[] = "('$sourceId',".$dataSource->getValue().")";
        }

        $this->pdo->exec($this->feedQuery . implode(', ', $values));
    }

    public function clean() {
        $this->pdo->exec($this->cleanupQuery);
    }
}
