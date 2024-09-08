<?php declare(strict_types=1);

namespace Framework\Database\Select;

use Exception;

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
 * @class Framework\Database\Select\Builder
 * @package Framework\Database\Select
 * @link https://tereta.dev
 * @since 2020-2024
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 * @copyright 2020-2024 Tereta Alexander
 */
class Builder
{
    const DIRECTION_ASC = 0;
    const DIRECTION_DESC = 1;

    /**
     * @var string|null
     */
    private ?string $table = null;

    /**
     * @var array|string[]
     */
    private array $columns = [];

    /**
     * @var array
     */
    private array $where = [];

    /**
     * @var int
     */
    private int $valueCounter = 0;

    /**
     * @var array
     */
    private array $params = [];

    /**
     * @var string
     */
    private string $limit = '';

    /**
     * @var string $order
     */
    private string $order = '';

    /**
     * @param array $columns
     */
    public function __construct(array $columns = ['*'])
    {
        $this->columns($columns);
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function columns(array $columns = ['*']): static
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @param string $table
     * @return $this
     */
    public function from(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @var array $innerJoin
     */
    private array $innerJoin = [];

    /**
     * @param string $table
     * @param string $condition
     * @return $this
     */
    public function innerJoin(string|array $table, string $condition): static
    {
        $as = null;
        if (is_array($table) && count($table) == 1) {
            $as = array_keys($table)[0];
            $table = array_values($table)[0];
        } elseif (is_array($table)) {
            throw new Exception('Invalid table name');
        }

        $this->innerJoin[$table] = [
            'as' => $as,
            'condition' => $condition
        ];

        return $this;
    }

    const OPERATOR_AND = 0;
    const OPERATOR_OR = 1;

    /**
     * @param string $condition
     * @param ...$variables
     * @return $this
     * @throws Exception
     */
    public function whereOr(string $condition, ...$variables): static
    {
        $this->whereCondition(self::OPERATOR_OR, $condition, ...$variables);
        return $this;
    }

    /**
     * @param string $condition
     * @param ...$variables
     * @return $this
     * @throws Exception
     */
    public function where(string $condition, ...$variables): static
    {
        $this->whereCondition(self::OPERATOR_AND, $condition, ...$variables);
        return $this;
    }

    /**
     * @param int $operator
     * @param string $condition
     * @param ...$variables
     * @return $this
     */
    public function whereCondition(int $operator, string $condition, ...$variables): static
    {
        foreach ($variables as $key => $variable) {
            if (is_array($variable)) {
                throw new Exception('Parameter $variable must not be an array');
            }
            $field = $this->valueCounter ? ":field{$this->valueCounter}" : ":field";
            $this->params[$field] = $variable;
            if (!str_contains($condition, '?')) {
                $condition = $condition . ' = ?';
            }
            $condition = str_replace('?', $field, $condition);
        }

        $operatorString = 'AND';
        $operatorString = ($operator == self::OPERATOR_AND ? 'AND' : $operatorString);
        $operatorString = ($operator == self::OPERATOR_OR ? 'OR' : $operatorString);

        $this->where[] = [
            'operator' => $operatorString,
            'condition' => $condition
        ];

        $this->valueCounter++;

        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param int $limit
     * @param int|null $offset
     * @return $this
     */
    public function limit(int $limit, ?int $offset = null): static
    {
        if ($offset === null) {
            $this->limit = ' LIMIT ' . $limit;
            return $this;
        }

        $this->limit = ' LIMIT ' . $limit . ' OFFSET ' .  $offset;
        return $this;
    }

    /**
     * @param string $field
     * @param int $direction
     * @return $this
     * @throws Exception
     */
    public function order(string $field, int $direction = self::DIRECTION_ASC): static
    {
        if (preg_match('/[^a-zA-Z0-9_]/', $field)) {
            throw new Exception('Field name contains invalid characters');
        }

        switch ($direction) {
            case self::DIRECTION_ASC:
                $directionKeyWord = 'ASC';
                break;
            case self::DIRECTION_DESC:
                $directionKeyWord = 'DESC';
                break;
            default:
                throw new Exception('Invalid direction');
        }

        $this->order = " ORDER BY {$field} {$directionKeyWord}";
        return $this;
    }

    /**
     * @return string
     */
    public function build(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->table . ' AS main';

        foreach ($this->innerJoin as $table => $condition) {
            if (is_array($condition) && $condition['as']) {
                $table = "{$table} AS {$condition['as']}";
                $condition = $condition['condition'];
            } elseif (is_array($condition)) {
                $condition = $condition['condition'];
            }

            $sql .= " INNER JOIN {$table} ON {$condition}";
        }

        $sql .= $this->buildWhere();
        $sql .= $this->order;
        $sql .= $this->limit;

        return $sql;
    }

    /**
     * @return string
     */
    public function buildDelete(): string
    {
        $sql = 'DELETE FROM ' . $this->table;

        $sql .= $this->buildWhere();

        return $sql;
    }

    /**
     * @return string
     */
    private function buildWhere(): string
    {
        $sql = '';
        if (empty($this->where)) {
            return '';
        }

        $where = '';
        foreach ($this->where as $item) {
            if ($where) {
                $where .= " {$item['operator']} ";
            }
            $where .= $item['condition'];
        }

        $sql .= " WHERE {$where}";

        return $sql;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->build();
    }
}