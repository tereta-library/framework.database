<?php declare(strict_types=1);

namespace Framework\Database\Create;

use PDO;
use Framework\Database\Facade;
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
 * @class Framework\Database\Create\ForeignBuilder
 * @package Framework\Database\Create
 * @link https://tereta.dev
 * @since 2020-2024
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 * @copyright 2020-2024 Tereta Alexander
 */
class ForeignBuilder
{
    /**
     * @var string $foreignTable
     */
    private string $foreignTable;

    /**
     * @var string $foreignColumn
     */
    private string $foreignColumn;

    /**
     * @var string $comment
     */
    private string $comment = '';


    /**
     * @param PDO $connection
     * @param Builder $parent
     * @param string $column
     */
    public function __construct(private PDO $connection, private Builder $parent, private string $column)
    {
        if (preg_match('/[^a-zA-Z0-9_]/', $column)) {
            throw new \InvalidArgumentException("Column name {$column} is invalid");
        }
    }

    /**
     * @param string $comment
     * @return $this
     */
    public function setComment(string $comment): static
    {
        $this->comment = $comment;
        return $this;
    }

    private $fieldType = null;

    /**
     * @param string $table
     * @param string $column
     * @return $this
     * @throws Exception
     */
    public function foreign(string $table, string $column): static
    {
        if (preg_match('/[^a-zA-Z0-9_]/', $table)) {
            throw new \InvalidArgumentException("Table name {$table} is invalid");
        }

        if (preg_match('/[^a-zA-Z0-9_]/', $column)) {
            throw new \InvalidArgumentException("Column name {$column} is invalid");
        }

        $foreignColumnType = null;

        if ($this->parent->getTable() != $table) {
            $foreignTable = Facade::describeTable($this->connection, $table);
        } else {
            $foreignTable = [];
            $foreignColumnType = $this->parent->getColumn($column)->getFieldType();
        }

        foreach ($foreignTable as $row) {
            if ($row['Field'] === $column) {
                $foreignColumnType = $row['Type'];
                break;
            }
        }

        switch ($foreignColumnType) {
            case 'bigint':
                $type = $this->parent->addInteger($this->column, Builder::TYPE_BIGINT, true);
                break;
            case 'bigint unsigned':
                $type = $this->parent->addInteger($this->column, Builder::TYPE_BIGINT_UNSIGNED, false);
                break;
            case 'int':
                $type = $this->parent->addInteger($this->column, Builder::TYPE_INT, true);
                break;
            case 'int unsigned':
                $type = $this->parent->addInteger($this->column, Builder::TYPE_INT_UNSIGNED, false);
                break;
            case 'mediumint':
                $type = $this->parent->addInteger($this->column, Builder::TYPE_MEDIUMINT, true);
                break;
            case 'mediumint unsigned':
                $type = $this->parent->addInteger($this->column, Builder::TYPE_MEDIUMINT_UNSIGNED, false);
                break;
            case 'smallint':
                $type = $this->parent->addInteger($this->column, Builder::TYPE_SMALLINT, true);
                break;
            case 'smallint unsigned':
                $type = $this->parent->addInteger($this->column, Builder::TYPE_SMALLINT_UNSIGNED, false);
                break;
            case 'tinyint':
                $type = $this->parent->addInteger($this->column, Builder::TYPE_TINYINT, true);
                break;
            case 'tinyint unsigned':
                $type = $this->parent->addInteger($this->column, Builder::TYPE_TINYINT_UNSIGNED, false);
                break;
            default:
                throw new Exception('Unsupported foreign key type');
        }

        $this->fieldType = $type;
        $this->foreignTable = $table;
        $this->foreignColumn = $column;
        return $this;
    }

    /**
     * @return string
     */
    public function build(): string
    {
        if ($this->comment) {
            $this->fieldType->setComment($this->comment);
        }

        return "FOREIGN KEY ({$this->column}) REFERENCES {$this->foreignTable}({$this->foreignColumn}) ON DELETE CASCADE ON UPDATE CASCADE";
    }
}