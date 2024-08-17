<?php declare(strict_types=1);

namespace Framework\Database\Create;

use Framework\Database\Create\ColumnBuilder as ColumnBuilder;
use PDO;

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

    const TYPE_BIGINT = 9223372036854776000 - 1;
    const TYPE_INT = 2147483647;
    const TYPE_MEDIUMINT = 8388607;
    const TYPE_SMALLINT = 32767;
    const TYPE_TINYINT = 127;

    const TYPE_BIGINT_UNSIGNED = 18446744073709552000 - 1;
    const TYPE_INT_UNSIGNED = 4294967295;
    const TYPE_MEDIUMINT_UNSIGNED = 16777215;
    const TYPE_SMALLINT_UNSIGNED = 65535;
    const TYPE_TINYINT_UNSIGNED = 255;

    const TYPE_LONGTEXT = 4294967295;
    const TYPE_MEDIUMTEXT = 16777215;
    const TYPE_TEXT = 65535;
    const TYPE_VARCHAR = 255;

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
        $this->table = $table;
        return $this;
    }

    /**
     * @param string $columnName
     * @param int $length
     * @return ColumnBuilder
     */
    public function addString(string $columnName, int $length = 255): ColumnBuilder
    {
        if (preg_match('/[^a-zA-Z0-9_]/', $columnName)) {
            throw new \InvalidArgumentException("Column name '{$columnName}' is invalid");
        }

        switch(true) {
            case($length <= self::TYPE_VARCHAR):
                $column = "{$columnName} VARCHAR({$length})";
                break;
            case($length <= self::TYPE_TEXT):
                $column = "{$columnName} TEXT";
                break;
                case($length <= self::TYPE_MEDIUMTEXT):
                $column = "{$columnName} MEDIUMTEXT";
                break;
            case($length <= self::TYPE_LONGTEXT):
                $column = "{$columnName} LONGTEXT";
                break;
            default:
                throw new Exception("Invalid length {$length} of string type");
                break;
        }

        $column = new ColumnBuilder($column, ColumnBuilder::TYPE_TEXT);
        $this->columns[] = $column;
        return $column;
    }

    /**
     * @param string $columnName
     * @return ColumnBuilder
     */
    public function addDateTime(string $columnName): ColumnBuilder
    {
        $column = "{$columnName} DATETIME";
        $column = new ColumnBuilder($column, ColumnBuilder::TYPE_DATETIME);
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
        $column = "{$columnName} DECIMAL({$length}, {$decimals})";
        $column = new ColumnBuilder($column, ColumnBuilder::TYPE_DECIMAL);
        $this->columns[] = $column;
        return $column;
    }

    /**
     * @param string $columnName
     * @param int $length
     * @param bool $signed
     * @return ColumnBuilder
     */
    public function addInteger(string $columnName, int $length = self::TYPE_INT_UNSIGNED, bool $signed = false): ColumnBuilder
    {
        switch(true) {
            case($signed == true && $length <= static::TYPE_TINYINT):
                $column = "{$columnName} TINYINT";
                break;
            case($signed == true && $length <= static::TYPE_SMALLINT):
                $column = "{$columnName} SMALLINT";
                break;
            case($signed == true && $length <= static::TYPE_MEDIUMINT):
                $column = "{$columnName} MEDIUMINT";
                break;
            case($signed == true && $length <= static::TYPE_INT):
                $column = "{$columnName} INT";
                break;
            case($signed == true && $length <= static::TYPE_BIGINT):
                $column = "{$columnName} BIGINT";
                break;
            case($signed == true):
                throw new Exception("Invalid length {$length} of numeric type");
                break;
            case($length <= static::TYPE_TINYINT_UNSIGNED):
                $column = "{$columnName} TINYINT UNSIGNED";
                break;
            case($length <= static::TYPE_SMALLINT_UNSIGNED):
                $column = "{$columnName} SMALLINT UNSIGNED";
                break;
            case($length <= static::TYPE_MEDIUMINT_UNSIGNED):
                $column = "{$columnName} MEDIUMINT UNSIGNED";
                break;
            case($length <= static::TYPE_INT_UNSIGNED):
                $column = "{$columnName} INT UNSIGNED";
                break;
            case($length <= static::TYPE_BIGINT_UNSIGNED):
                $column = "{$columnName} BIGINT UNSIGNED";
                break;
            default:
                throw new Exception("Invalid length {$length} of numeric type");
                break;
        }

        $column = new ColumnBuilder($column, ColumnBuilder::TYPE_INT);
        $this->columns[] = $column;
        return $column;
    }

    public function addBoolean(string $columnName): ColumnBuilder
    {
        $column = "{$columnName} BOOLEAN";
        $column = new ColumnBuilder($column, ColumnBuilder::TYPE_BOOLEAN);
        $this->columns[] = $column;
        return $column;
    }

    /**
     * @param PDO $connection
     * @param string $string
     * @return ForeignBuilder
     */
    public function addForeign(PDO $connection, string $string): ForeignBuilder
    {
        return $this->foreigns[] = new ForeignBuilder($connection, $this, $string);
    }

    /**
     * @param ...$columns
     * @return $this
     */
    public function addUnique(...$columns): static
    {
        $this->unique[] = $columns;
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