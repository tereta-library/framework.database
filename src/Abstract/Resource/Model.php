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
    const DIRECTION_ASC = SelectBuilder::DIRECTION_ASC;
    const DIRECTION_DESC = SelectBuilder::DIRECTION_DESC;

    /**
     * @var array $instance
     */
    protected static array $instance = [];

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
     * The method should be extended by child classes with static table predefinition in the $table property
     * The field ID can be set in the $idField property, but if it is not set, the model will try to find it in the table definition
     *
     * @param string $table - table to process by the model
     * @param string|null $idField - it is key definition, but if it is not set, the model will try to find it in the table definition
     * @param string $connectionName - connection name from the config
     * @throws Exception
     */
    public function __construct(private string $table, private ?string $idField = null, string $connectionName = 'default')
    {
        $this->connection = SingletonDatabase::getConnection($connectionName);
    }

    /**
     * @return static
     */
    public static function getInstance(): static
    {
        $key = static::class;
        if (isset(static::$instance[$key])) {
            return static::$instance[$key];
        }

        return static::$instance[$key] = new static;
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
     * @return array
     * @throws Exception
     */
    public function getDescription(): array
    {
        $this->prepareModel();
        return $this->description;
    }

    /**
     * @param ItemModel $model
     * @param string|int|float|null $value
     * @param string|null $field
     * @return bool
     * @throws Exception
     */
    public function load(ItemModel $model, string|int|float|null|array $value = null, ?string $field = null): bool
    {
        $params = func_get_args();
        if (!$field && !is_array($value)) {
            $this->prepareModel();
        }
        if (!$field && !is_array($value) && $model->has($this->idField)) {
            $value = $model->get($this->idField);
        }
        if (!$field && !is_array($value) && $value !== null) {
            $field = $this->idField;
        }
        if ($field && $value === null && count($params) < 2) $value = $model->get($field);

        $select = $this->getSelect();
        if (is_array($value)) {
            $valueSearch = $value;
        } elseif ($field) {
            $valueSearch = [$field => $value];
        } else  {
            $valueSearch = [];
        }

        foreach ($valueSearch as $key => $val) {
            $select->where($key . ' = ?', $val);
        }

        $pdo = SingletonDatabase::getConnection();
        $pdoStatement = $pdo->prepare($select->build());
        try {
            $pdoStatement->execute($select->getParams());
        } catch (Exception $e) {
            throw $e;
        }

        $itemData = $pdoStatement->fetch(PDO::FETCH_ASSOC);
        $this->select = null;
        if (!$itemData) return false;
        $model->setData($itemData);
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
     * @param string $order
     * @return $this
     * @throws Exception
     */
    public function order(string $order, int $direction = self::DIRECTION_ASC): static
    {
        $this->getSelect()->order($order, $direction);
        return $this;
    }

    /**
     * @param ItemModel $model
     * @param string|null $idField
     * @return $this
     * @throws Exception
     */
    public function save(ItemModel $model, string $idField = null): static
    {
        $this->prepareModel();

        if (!$idField) {
            $idField = $this->idField;
        }

        $data = array_intersect_key($model->getData(), $this->description);

        $this->prepareModel();
        $query = Factory::createInsert($this->table)->values($data);

        if ($model->get($idField)) {
            $query->updateOnDupilicate(...$this->uniqueFields);
        }

        $pdoStat = $this->connection->prepare($query->build());

        $result = $pdoStat->execute($query->getParams());

        $lastInsertId = null;
        if ($result && $idField && !$model->get($idField) && $lastInsertId = $this->connection->lastInsertId()) {
            $model->set($idField, $lastInsertId);
        }

        return $this;
    }

    /**
     * @param ItemModel|string|int|float|array|null $value
     * @param string|null $field
     * @return int
     * @throws Exception
     */
    public function delete(ItemModel|string|int|float|null|array $value = null, ?string $field = null): int
    {
        $params = func_get_args();
        if (!$field) {
            $this->prepareModel();
            $field = $this->idField;
        }
        if ($value instanceof ItemModel) {
            $value = $value->get($field) ? $value->get($field) : throw new Exception("The value {$field} is not set");
        }

        $select = $this->getSelect();
        if (is_array($value) && $field) {
            foreach ($value as $val) {
                $select->whereOr($field . ' = ?', $val);
            }

            $valueSearch = [];
        } elseif (is_array($value)) {
            $valueSearch = $value;
        } elseif ($value && $field) {
            $valueSearch = [$field => $value];
        } else  {
            $valueSearch = [];
        }

        foreach ($valueSearch as $key => $val) {
            $select->where($key . ' = ?', $val);
        }

        $pdo = SingletonDatabase::getConnection();
        $pdoStatement = $pdo->prepare($select->buildDelete());
        $executed = $pdoStatement->execute($select->getParams());
        $this->select = null;
        return $pdoStatement->rowCount();
    }
}