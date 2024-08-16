<?php declare(strict_types=1);

namespace Framework\Database\Abstract\Resource;

use Framework\Database\Abstract\Model as ItemModel;
use Framework\Database\Factory;
use Framework\Database\Singleton as SingletonDatabase;
use PDO;
use Exception;
use Framework\Database\Facade;
use Framework\Database\Select\Builder as SelectBuilder;

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
     * @var Select|null $select
     */
    private ?SelectBuilder $select = null;

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
     * @return bool
     * @throws Exception
     */
    public function load(ItemModel $model, string|int|float|null $value = null, ?string $field = null): bool
    {
        $params = func_get_args();
        if (!$field) {
            $this->prepareModel();
            $field = $this->idField;
        }
        if ($value === null && count($params) < 2) $value = $model->get($field);

        $select = $this->getSelect();
        if ($value && $field) {
            $select->where($field . ' = ?', $value);
        }

        $pdo = SingletonDatabase::getConnection();
        $pdoStatement = $pdo->prepare($select->build());
        $pdoStatement->execute($select->getParams());

        $itemData = $pdoStatement->fetch(PDO::FETCH_ASSOC);
        if (!$itemData) return false;
        $model->setData($itemData);
        $this->select = null;
        return true;
    }

    /**
     * @return SelectBuilder
     */
    public function getSelect(bool $newSelect = false): SelectBuilder
    {
        if ($this->select) return $this->select;

        return $this->select = Factory::createSelect('*')->from($this->table);
    }

    /**
     * @param string $where
     * @param mixed ...$params
     * @return $this
     */
    public function where(string $where, mixed ...$params): static
    {
        $this->getSelect()->where($where, ...$params);
        return $this;
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
        $result = $pdoStat->execute($query->getParams());

        if ($result && $this->idField && !$model->get($this->idField)) {
            $id = $this->connection->lastInsertId();
            $model->set($this->idField, $id);
        }

        return $this;
    }
}