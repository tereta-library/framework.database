<?php declare(strict_types=1);

namespace Framework\Database\Abstract;

use Builder\Site\Model\Entity as EntityModel;
use Exception;
use Framework\Database\Abstract\Model;


/**
 * @class Framework\Database\Abstract\Repository
 */
abstract class Repository {
    /**
     * @var \Builder\Site\Model\Repository|null $instance
     */
    private static ?self $instance = null;

    /**
     * @var array $registered
     */
    private array $registered = [];

    /**
     * @var array
     */
    protected array $registeredKeys = ['id', ['siteId', 'identifier']];

    /**
     * @return static
     */
    public static function getInstance(): static
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @param array $valuesSource
     * @return Model|null
     */
    protected function getRegisterModel(array $valuesSource): ?Model
    {
        $key = '';
        $values = [];
        foreach ($valuesSource as $keyItem => $valueItem) {
            $key .= ($key ? ':' : '') . $keyItem;
            $values[] = $valueItem;
        }

        $valuesHash = $this->getKey(...$values);
        if (!isset($this->registered[$key][$valuesHash])) {
            return null;
        }

        return $this->registered[$key][$valuesHash];
    }

    /**
     * @param Model $entityModel
     * @return Model
     * @throws Exception
     */
    protected function setRegisterModel(Model $entityModel): Model
    {
        $keys = $this->registeredKeys;

        if (!$entityModel->get('id')) {
            throw new Exception('Site not found');
        }

        foreach ($keys as $key) {
            if (!is_array($key)) {
                $this->registered[$key][$entityModel->get($key)] = $entityModel;
                continue;
            }

            $itemKey = '';
            $itemKeyValue = [];
            foreach($key as $item) {
                $itemKey .= ($itemKey ? ':' : '') . $item;
                $itemKeyValue[] = $entityModel->get($item);
            }

            $this->registered[$itemKey][$this->getKey(...$itemKeyValue)] = $entityModel;
        }

        return $entityModel;
    }

    /**
     * @param ...$params
     * @return int
     */
    protected function getKey(...$params): int
    {
        return crc32(implode(':', $params));
    }
}