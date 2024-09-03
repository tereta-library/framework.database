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

    private array $innerJoin = [];

    public function innerJoin(string $table, string $condition): static
    {
        $this->innerJoin[$table] = $condition;

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
     * @return string
     */
    public function build(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->table;

        foreach ($this->innerJoin as $table => $condition) {
            $sql .= " INNER JOIN {$table} ON {$condition}";
        }

        $sql .= $this->buildWhere();
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