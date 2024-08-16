<?php declare(strict_types=1);

namespace Framework\Database\Select;

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

    /**
     * @param string $condition
     * @param ...$variables
     * @return $this
     */
    public function where(string $condition, ...$variables): static
    {
        foreach ($variables as $key => $variable) {
            $field = $this->valueCounter ? ":field{$this->valueCounter}" : ":field";
            $this->params[$field] = $variable;
            $condition = str_replace('?', $field, $condition);
        }

        $this->where[] = [
            'operator' => 'AND',
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
     * @return string
     */
    public function build(): string
    {
        $sql = 'SELECT ' . implode(', ', $this->columns) . ' FROM ' . $this->table;

        foreach ($this->innerJoin as $table => $condition) {
            $sql .= " INNER JOIN {$table} ON {$condition}";
        }

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