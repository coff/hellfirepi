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

    protected $query = <<< SQL
      INSERT INTO 
        readings (`source`, `value`)
      VALUES 
        (:source, :value)
SQL;


    /**
     * @var \PDO
     */
    protected $pdo;

    public function __construct(DataSourceArray $array)
    {
        $this->array;
    }

    public function setPdo(\PDO $pdo) {
        $this->pdo = $pdo;

        return $this;
    }

    public function store() {

        $statement = $this->pdo->prepare($this->query);

        /**
         * @var string $sourceId
         * @var DataSource $dataSource
         */
        foreach ($this->array as $sourceId => $dataSource) {
            $statement->execute(array(
                ':source'   => $sourceId,
                ':value'    => $dataSource->getValue()
            ));
        }
    }
}
