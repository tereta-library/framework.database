<?php declare(strict_types=1);

namespace Framework\Database\Abstract\Resource;

use Framework\Database\Abstract\Resource\Model as ResourceModel;
use Iterator;
use Framework\Database\Select\Factory as SelectFactory;
use Framework\Database\Singleton as SingletonConnection;
use Framework\Database\Abstract\Model;
use Exception;
use PDO;
use PDOStatement;
use Framework\Database\Select\Builder as SelectBuilder;
use Framework\Database\Value\Query as ValueQuery;

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
 * @class Framework\Database\Abstract\Resource\Collection
 * @package Framework\Database\Abstract\Resource
 * @link https://tereta.dev
 * @since 2020-2024
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 * @copyright 2020-2024 Tereta Alexander
 */
abstract class Collection implements Iterator
{
    /**
     * @var int
     */
    private int $position = 0;

    /**
     * @var int
     */
    private int $count = 0;

    /**
     * @var ResourceModel
     */
    private ResourceModel $resourceModel;

    /**
     * @var
     */
    private $select;

    /**
     * @var null
     */
    private $loadStatement = null;

    /**
     * @var PDO
     */
    private PDO $connection;

    /**
     * @param string $resourceModel
     * @param string $model
     * @param string $connectionName
     * @throws Exception
     */
    public function __construct(string $resourceModel, private string $model, string $connectionName = 'default')
    {
        $this->connection = SingletonConnection::getConnection($connectionName);
        $this->resourceModel = new $resourceModel;
    }

    /**
     * @param string $value
     * @param string $key
     * @return static
     */
    public function load(string|int|null|bool $value, string $key): static
    {
        $this->getSelect()->where("{$key} = ?", $value);
        $this->loadCollection(true);
        return $this;
    }

    /**
     * @param bool $reset
     * @return SelectBuilder
     */
    public function getSelect(bool $reset = false): SelectBuilder
    {
        if ($this->select && !$reset) {
            return $this->select;
        }
        $this->select = SelectFactory::create()->from($this->resourceModel->getTable());
        return $this->select;
    }

    /**
     * @param string $condition
     * @param ...$params
     * @return $this
     */
    public function where(string $condition, ...$params): static
    {
        $this->getSelect()->where($condition, ...$params);
        return $this;
    }

    private ?int $limit = null;
    private ?int $limitPage = null;

    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @param int $page
     * @return $this
     */
    public function setPage(int $page): static
    {
        $this->limitPage = $page;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        $query = clone $this->getSelect();
        $query->columns([new ValueQuery('COUNT(*) as count')]);
        $pdoState = $this->connection->prepare($query->build());
        $pdoState->execute($query->getParams());
        $count = $pdoState->fetchColumn();
        return $count;
    }

    /**
     * @param bool $reset
     * @return PDOStatement
     */
    private function loadCollection(bool $reset = false): PDOStatement
    {
        if ($this->loadStatement && !$reset) {
            return $this->loadStatement;
        }

        if (!is_null($this->limit) && !is_null($this->limitPage)) {
            $this->getSelect()->limit($this->limit, $this->limit * ($this->limitPage - 1));
        } elseif (!is_null($this->limit)) {
            $this->getSelect()->limit($this->limit);
        }

        $this->position = 0;
        $query = $this->getSelect();
        $pdoState = $this->connection->prepare($query->build());
        $pdoState->execute($query->getParams());

        $this->count = $pdoState->rowCount();

        return $this->loadStatement = $pdoState;
    }

    /**
     * @return void
     */
    public function rewind(): void {
        $this->loadCollection(true);
    }

    /**
     * @return Model
     */
    public function current(): Model {
        $this->position++;
        $data = $this->loadStatement->fetch(PDO::FETCH_ASSOC);
        $model = $this->model;
        return new $model($data);
    }

    /**
     * @return int
     */
    public function key(): int {
        return $this->position;
    }

    /**
     * @return void
     */
    public function next(): void {
    }

    /**
     * @return bool
     */
    public function valid(): bool {
        $this->loadCollection();

        return $this->position < $this->count;
    }
}