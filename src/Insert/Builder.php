<?php declare(strict_types=1);

namespace Framework\Database\Insert;

use Exception;
use Framework\Database\Interface\Value;

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
 * @class Framework\Database\Insert\Builder
 * @package Framework\Database\Insert
 * @link https://tereta.dev
 * @since 2020-2024
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 * @copyright 2020-2024 Tereta Alexander
 */
class Builder
{
    /**
     *
     */
    private ?array $fields = null;

    /**
     * @var array
     */
    private array $insertArray = [];

    /**
     * @var array|null
     */
    private ?array $onDuplicate = null;

    private array $values = [];

    /**
     * @param string|null $table
     */
    public function __construct(private ?string $table = null)
    {
    }

    /**
     * @param array $data
     * @return $this
     * @throws Exception
     */
    public function values(array $data): static
    {
        if ($this->fields === null) {
            $this->fields = array_keys($data);
        } else if ($this->fields != array_keys($data)){
            throw new Exception('Keys on insert values should be same for all insert records');
        }

        $preparedData = [];
        foreach ($this->fields as $key) {
            $preparedData[$key] = $data[$key];
        }

        $this->insertArray[] = $preparedData;

        return $this;
    }

    /**
     * @param ...$fields
     * @return $this
     */
    public function updateOnDupilicate(...$fields): static
    {
        $this->onDuplicate = $fields;
        return $this;
    }

    /**
     * @return string
     */
    public function build(): string
    {
        $this->values = [];
        foreach ($this->insertArray as $itemNumber => $itemData) {
            $preparedItem = [];
            foreach ($itemData as $itemKey => $itemValue) {
                if ($itemValue instanceof Value) {
                    $preparedItem[] = $itemValue->build();
                    continue;
                }

                if (!is_scalar($itemValue) && !is_null($itemValue)) {
                    throw new Exception("The \"{$itemKey}\" value is not scalar");
                }
                
                $insertKey = ":{$itemNumber}_{$itemKey}";
                $this->values[$insertKey] = $itemValue;
                $preparedItem[] = $insertKey;
            }
            $values[] = implode(", ", $preparedItem);
        }
        $query = "INSERT INTO {$this->table} (" . implode(", ", $this->fields) . ") VALUES (" . implode('), (', $values) . ")";

        if ($this->onDuplicate && $this->fields) {
            $query .= " ON DUPLICATE KEY UPDATE ";
            $updateArray = [];

            foreach ($this->fields as $field) {
                if (in_array($field, $this->onDuplicate)) continue;
                $updateArray[] = "{$field} = VALUES({$field})";
            }
            $query .= implode(", ", $updateArray);
        }

        return $query;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->values;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->build();
    }
}