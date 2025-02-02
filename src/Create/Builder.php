<?php declare(strict_types=1);

namespace Framework\Database\Create;

use Framework\Database\Create\ColumnBuilder as ColumnBuilder;
use PDO;
use InvalidArgumentException;

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
 * @class Framework\Database\Create\Builder
 * @package Framework\Database\Create
 * @link https://tereta.dev
 * @since 2020-2024
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 * @copyright 2020-2024 Tereta Alexander
 */
class Builder
{
    const int TYPE_BOOLEAN = 1;

    const int TYPE_TINYINT = 2;
    const int TYPE_SMALLINT = 3;
    const int TYPE_MEDIUMINT = 4;
    const int TYPE_INT = 5;
    const int TYPE_BIGINT = 6;

    const int TYPE_TINYINT_UNSIGNED = 7;
    const int TYPE_BIGINT_UNSIGNED = 8;
    const int TYPE_INT_UNSIGNED = 9;
    const int TYPE_MEDIUMINT_UNSIGNED = 10;
    const int TYPE_SMALLINT_UNSIGNED = 11;

    const int TYPE_CHAR = 12;
    const int TYPE_VARCHAR = 13;

    const int TYPE_TINYTEXT = 14;
    const int TYPE_TEXT = 15;
    const int TYPE_MEDIUMTEXT = 16;
    const int TYPE_LONGTEXT = 17;

    const array TYPE_DEFAULT_LENGTHS = [
        self::TYPE_VARCHAR => 255,
        self::TYPE_CHAR => 16
    ];

    /**
     * @var array $columns
     */
    private array $columns = [];

    /**
     * @var array $foreign
     */
    private array $foreigns = [];

    /**
     * @var array $unique
     */
    private array $unique = [];

    /**
     * @var array $index
     */
    private array $index = [];

    /**
     * @param string|null $table
     */
    public function __construct(private ?string $table = null)
    {
    }

    /**
     * @param string $table
     * @return $this
     */
    public function setTable(string $table): static
    {
        if (preg_match('/[^a-zA-Z0-9_]/', $table)) {
            throw new InvalidArgumentException("Column name \"{$table}\" is invalid");
        }

        $this->table = $table;
        return $this;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $columnName
     * @param int $type
     * @param int|null $length
     * @return ColumnBuilder
     */
    public function addString(string $columnName, int $type = self::TYPE_VARCHAR, ?int $length = null): ColumnBuilder
    {
        if (preg_match('/[^a-zA-Z0-9_]/', $columnName)) {
            throw new InvalidArgumentException("Column name '{$columnName}' is invalid");
        }

        switch(true) {
            case($type == self::TYPE_CHAR):
                $columnLength = $length ?? self::TYPE_DEFAULT_LENGTHS[self::TYPE_VARCHAR];
                $columnType = "char({$length})";
                break;
            case($type == self::TYPE_VARCHAR):
                $columnLength = $length ?? self::TYPE_DEFAULT_LENGTHS[self::TYPE_VARCHAR];
                $columnType = "varchar({$length})";
                break;
            case($type == self::TYPE_TINYTEXT):
                $columnType = "tinytext";
                break;
            case($type == self::TYPE_TEXT):
                $columnType = "text";
                break;
            case($type == self::TYPE_MEDIUMTEXT):
                $columnType = "mediumtext";
                break;
            case($type == self::TYPE_LONGTEXT):
                $columnType = "longtext";
                break;
            default:
                throw new Exception("Invalid string type");
                break;
        }

        $column = new ColumnBuilder($columnName, $columnType, ColumnBuilder::TYPE_TEXT);
        $this->columns[] = $column;
        return $column;
    }

    /**
     * @param string $columnName
     * @return ColumnBuilder
     */
    public function addDateTime(string $columnName): ColumnBuilder
    {
        if (preg_match('/[^a-zA-Z0-9_]/', $columnName)) {
            throw new InvalidArgumentException("Column name \"{$columnName}\" is invalid");
        }

        $column = new ColumnBuilder($columnName, "datetime", ColumnBuilder::TYPE_DATETIME);
        $this->columns[] = $column;
        return $column;
    }

    /**
     * @param string $columnName
     * @param int $length
     * @param int $decimals
     * @return ColumnBuilder
     */
    public function addDecimal(string $columnName, int $length = 10, int $decimals = 2): ColumnBuilder
    {
        if (preg_match('/[^a-zA-Z0-9_]/', $columnName)) {
            throw new InvalidArgumentException("Column name \"{$columnName}\" is invalid");
        }

        $column = new ColumnBuilder($column, "decimal({$length}, {$decimals})", ColumnBuilder::TYPE_DECIMAL);
        $this->columns[] = $column;
        return $column;
    }

    /**
     * @param string $columnName
     * @param int $length
     * @param bool $signed
     * @return ColumnBuilder
     */
    public function addInteger(string $columnName, int $type = self::TYPE_INT_UNSIGNED, bool $signed = true): ColumnBuilder
    {
        if (preg_match('/[^a-zA-Z0-9_]/', $columnName)) {
            throw new InvalidArgumentException("Column name \"{$columnName}\" is invalid");
        }

        switch(true) {
            case($type == static::TYPE_BOOLEAN):
                $columnType = 'boolean';
                break;
            case($signed === false && $type == static::TYPE_TINYINT):
                $columnType = 'tinyint unsigned';
                break;
            case($signed === false && $type <= static::TYPE_SMALLINT):
                $columnType = 'smallint unsigned';
                break;
            case($signed === false && $type <= static::TYPE_MEDIUMINT):
                $columnType = 'mediumint unsigned';
                break;
            case($signed === false && $type <= static::TYPE_INT):
                $columnType = 'int unsigned';
                break;
            case($signed === false && $type <= static::TYPE_BIGINT):
                $columnType = 'bigint unsigned';
                break;
            case($type == static::TYPE_TINYINT):
                $columnType = 'tinyint';
                break;
            case($type == static::TYPE_SMALLINT):
                $columnType = 'smallint';
                break;
            case($type == static::TYPE_MEDIUMINT):
                $columnType = 'mediumint';
                break;
            case($type == static::TYPE_INT):
                $columnType = 'int';
                break;
            case($type == static::TYPE_BIGINT):
                $columnType = 'bigint';
                break;
            case($type == static::TYPE_TINYINT_UNSIGNED):
                $columnType = 'tinyint unsigned';
                break;
            case($type == static::TYPE_SMALLINT_UNSIGNED):
                $columnType = 'smallint unsigned';
                break;
            case($type == static::TYPE_MEDIUMINT_UNSIGNED):
                $columnType = 'mediumint unsigned';
                break;
            case($type == static::TYPE_INT_UNSIGNED):
                $columnType = 'int unsigned';
                break;
            case($type == static::TYPE_BIGINT_UNSIGNED):
                $columnType = 'bigint unsigned';
                break;
            default:
                throw new Exception("Invalid numeric type");
                break;
        }

        $column = new ColumnBuilder($columnName, $columnType, ColumnBuilder::TYPE_INT);
        $this->columns[] = $column;
        return $column;
    }

    /**
     * @param string $columnName
     * @return ColumnBuilder
     */
    public function getColumn(string $columnName): ColumnBuilder
    {
        foreach ($this->columns as $column) {
            if ($column->getFieldName() == $columnName) {
                return $column;
            }
        }

        throw new InvalidArgumentException("Column \"{$columnName}\" not found");
    }

    /**
     * @param string $columnName
     * @return ColumnBuilder
     */
    public function addBoolean(string $columnName): ColumnBuilder
    {
        $columnType = "BOOLEAN";
        $column = new ColumnBuilder($columnName, $columnType, ColumnBuilder::TYPE_BOOLEAN);
        $this->columns[] = $column;
        return $column;
    }

    /**
     * @param PDO $connection
     * @param string $string
     * @return ForeignBuilder
     */
    public function addForeign(PDO $connection, string $column): ForeignBuilder
    {
        return $this->foreigns[] = new ForeignBuilder($connection, $this, $column);
    }

    /**
     * @param ...$columns
     * @return $this
     */
    public function addUnique(...$columns): static
    {
        if (preg_match('/[^a-zA-Z0-9_]/', implode($columns))) {
            throw new InvalidArgumentException("Column names \"" . implode("\", \"", $columns) . "\" is invalid");
        }

        $this->unique[] = $columns;
        return $this;
    }

    /**
     * @param ...$columns
     * @return $this
     */
    public function addIndex(string $column): static
    {
        if (preg_match('/[^a-zA-Z0-9_]/', $column)) {
            throw new InvalidArgumentException("Column name '{$column}' is invalid");
        }

        $this->index[] = $column;
        return $this;
    }

    /**
     * @return string $this
     */
    public function build(): string
    {
        $foreigns = [];
        foreach($this->foreigns as $foreign) {
            $foreigns[] = $foreign->build();
        }

        $columns = [];
        foreach ($this->columns as $column) {
            $columns[] = $column->build();
        }

        $columns = array_merge($columns, $foreigns);

        foreach ($this->unique as $unique) {
            $columns[] = "UNIQUE KEY unique_" . implode("_", $unique) . " (" . implode(", ", $unique) . ")";
        }

        foreach ($this->index as $index) {
            $columns[] = "INDEX index_{$index} ({$index})";
        }

        $string = "CREATE TABLE {$this->table} (\n  " . implode(",\n  ", $columns) . "\n)";

        return $string;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->build();
    }
}