<?php declare(strict_types=1);

namespace Framework\Database\Abstract\Resource;

use Framework\Database\Abstract\Model as ItemModel;
use Framework\Database\Factory;
use Framework\Database\Singleton as SingletonDatabase;
use PDO;
use Exception;
use Framework\Database\Facade;

/**
 * ···························WWW.TERETA.DEV······························
 * ·······································································
 * : _____                        _                     _                :
 * :|_   _|   ___   _ __    ___  | |_    __ _        __| |   ___  __   __:
 * :  | |    / _ \ | '__|  / _ \ | __|  / _` |      / _` |  / _ \ \ \ / /:
 * :  | |   |  __/ | |    |  __/ | |_  | (_| |  _  | (_| | |  __/  \ V / :
 * :  |_|    \___| |_|     \___|  \__|  \__,_| (_)  \__,_|  \___|   \_/  :
 * ·······································································
 * ·······································································
 *
 * @class Framework\Database\Abstract\Resource\Model
 * @package Framework\Database\Abstract\Resource
 * @link https://tereta.dev
 * @since 2020-2024
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 * @copyright 2020-2024 Tereta Alexander
 */
abstract class Model
{
    /**
     * @var array|null
     */
    private ?array $description = null;

    /**
     * @var PDO $connection
     */
    private PDO $connection;

    /**
     * @var array $uniqueFields
     */
    private array $uniqueFields = [];

    /**
     * @param string $table
     * @param string|null $idField
     * @param string $connectionName
     * @throws Exception
     */
    public function __construct(private string $table, private ?string $idField = null, string $connectionName = 'default')
    {
        $this->connection = SingletonDatabase::getConnection($connectionName);
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return void
     * @throws Exception
     */
    private function prepareModel(): void
    {
        if ($this->description) return;

        $this->description = [];
        foreach (Facade::describeTable($this->connection, $this->table) as $column) {
            $this->description[$column['Field']] = $column;
            if (isset($column['Key']) && $column['Key'] === 'PRI') {
                $this->idField = $column['Field'];
                $this->uniqueFields[] = $column['Field'];
            }

            if (isset($column['Key']) && $column['Key'] === 'UNI') {
                $this->uniqueFields[] = $column['Field'];
            }
        }
    }

    /**
     * @param ItemModel $model
     * @param string|int|float|null $value
     * @param string|null $field
     * @return void
     * @throws Exception
     */
    public function load(ItemModel $model, string|int|float|null $value = null, ?string $field = null): void
    {
        if (!$field) {
            $this->prepareModel();
            $field = $this->idField;
        }
        if ($value === null) $value = $model->get($field);

        $select = Factory::createSelect('*');
        $select->from($this->table);
        $select->where($field . ' = ?', $value);

        $pdo = SingletonDatabase::getConnection();
        $pdoStatement = $pdo->prepare($select->build());
        foreach ($select->getParams() as $key => $param) {
            $pdoStatement->bindParam($key, $param);
        }
        $pdoStatement->execute();

        $itemData = $pdoStatement->fetch(PDO::FETCH_ASSOC);
        $model->setData($itemData);
    }

    /**
     * @param ItemModel $model
     * @return Model
     * @throws Exception
     */
    public function save(ItemModel $model): static
    {
        $this->prepareModel();
        $query = Factory::createInsert($this->table)->values($model->getData());
        $query->updateOnDupilicate(...$this->uniqueFields);

        $pdoStat = $this->connection->prepare($query->build());
        $pdoStat->execute($query->getParams());

        return $this;
    }
}