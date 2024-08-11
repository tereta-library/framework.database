<?php declare(strict_types=1);

namespace Framework\Database\Create;

use Framework\Database\Interface\Value;
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
 * @class Framework\Database\Create\ColumnBuilder
 * @package Framework\Database\Create
 * @link https://tereta.dev
 * @since 2020-2024
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 * @copyright 2020-2024 Tereta Alexander
 */
class ColumnBuilder
{
    const TYPE_INT = 1;
    const TYPE_DECIMAL = 2;
    const TYPE_TEXT = 3;
    const TYPE_DATETIME = 4;

    /**
     * @param string $field
     * @param int $type
     */
    public function __construct(private string $field, private int $type)
    {
    }

    /**
     * @return string
     */
    public function build(): string
    {
        return $this->field;
    }

    /**
     * @return $this
     */
    public function setAutoIncrement(): static
    {
        if ($this->type !== self::TYPE_INT) {
            throw new InvalidArgumentException('Auto increment can only be set on integer fields');
        }

        $this->field .= ' AUTO_INCREMENT';
        return $this;
    }

    /**
     * @return $this
     */
    public function setNotNull(): static
    {
        $this->field .= ' NOT NULL';
        return $this;
    }

    /**
     * @return $this
     */
    public function setPrimaryKey(): static
    {
        $this->field .= ' PRIMARY KEY';
        return $this;
    }

    /**
     * @return $this
     */
    public function setUnique(): static
    {
        $this->field .= ' UNIQUE';
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setDefault($value): static
    {
        if ($value instanceof Value) {
            $this->field .= " DEFAULT {$value->build()}";
            return $this;
        }

        $this->field .= " DEFAULT '{$value}'";
        return $this;
    }

    /**
     * @param string $comment
     * @return $this
     */
    public function setComment(string $comment): static
    {
        $this->field .= " COMMENT '{$comment}'";
        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->build();
    }
}