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

    const OPERATOR_AND = 0;
    const OPERATOR_OR = 1;

    const JOIN_TYPE_INNER = 0;
    const JOIN_TYPE_LEFT = 1;
    const JOIN_TYPE_RIGHT = 2;

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
     * @var array $join
     */
    private array $join = [];

    /**
     * @var string $group
     */
    private string $group = '';

    /**
     * @param array $columns
     */
    public function __construct(array $columns = ['*'])
    {
        $this->columns(...$columns);
    }

    /**
     * @param array $columns
     * @return $this
     */
    public function columns(...$columns): static
    {
        if (!$columns) {
            $columns = ['*'];
        }

        $columns = array_map(function($column) {
            if (is_array($column)) {
                return array_keys($column)[0] . ' AS `' . array_values($column)[0] . '`';
            }
            return $column;
        }, $columns);

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
     * @param string $table
     * @param string $condition
     * @return $this
     */
    public function innerJoin(string|array $table, string $condition): static
    {
        return $this->join(self::JOIN_TYPE_INNER, $table, $condition);
    }

    /**
     * @param string|array $table
     * @param string $condition
     * @return $this
     * @throws Exception
     */
    public function rightJoin(string|array $table, string $condition): static
    {
        return $this->join(self::JOIN_TYPE_RIGHT, $table, $condition);
    }

    /**
     * @param string|array $table
     * @param string $condition
     * @return $this
     * @throws Exception
     */
    public function leftJoin(string|array $table, string $condition): static
    {
        return $this->join(self::JOIN_TYPE_LEFT, $table, $condition);
    }

    /**
     * @param int $type
     * @param string|array $table
     * @param string $condition
     * @return $this
     */
    private function join(int $type, string|array $table, string $condition): static
    {
        $as = null;
        if (is_array($table) && count($table) == 1) {
            $as = array_keys($table)[0];
            $table = array_values($table)[0];
        } elseif (is_array($table)) {
            throw new Exception('Invalid table name');
        }

        $this->join[$table] = [
            'type'      => $type,
            'as'        => $as,
            'condition' => $condition
        ];

        return $this;
    }

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
            $this->valueCounter++;
            $this->params[$field] = $variable;
            if (!str_contains($condition, '?')) {
                $condition = $condition . ' = ?';
            }
            $strPos = strpos($condition, '?');
            $condition = substr_replace($condition, $field, $strPos, 1);
            $e=0;
        }

        $operatorString = 'AND';
        $operatorString = ($operator == self::OPERATOR_AND ? 'AND' : $operatorString);
        $operatorString = ($operator == self::OPERATOR_OR ? 'OR' : $operatorString);

        $this->where[] = [
            'operator' => $operatorString,
            'condition' => $condition
        ];

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
     * @param string ...$field
     * @return $this
     */
    public function group(string ...$field): static
    {
        $insert = implode(", ", $field);
        $this->group = " GROUP BY {$insert}";
        return $this;
    }

    /**
     * @return string
     */
    public function build(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->table . ' AS main';

        foreach ($this->join as $table => $condition) {
            $type = $condition['type'];
            if (is_array($condition) && $condition['as']) {
                $table = "{$table} AS {$condition['as']}";
                $condition = $condition['condition'];
            } elseif (is_array($condition)) {
                $condition = $condition['condition'];
            }

            $joinType = 'INNER';
            switch ($type) {
                case self::JOIN_TYPE_INNER:
                    $joinType = 'INNER';
                    break;
                case self::JOIN_TYPE_LEFT:
                    $joinType = 'LEFT';
                    break;
                case self::JOIN_TYPE_RIGHT:
                    $joinType = 'RIGHT';
                    break;
            }

            $sql .= " {$joinType} JOIN {$table} ON {$condition}";
        }

        $sql .= $this->buildWhere();
        $sql .= $this->group;
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