<?php declare(strict_types=1);

namespace Framework\Database\Abstract;

use Builder\Site\Model\Site as EntityModel;
use Exception;
use Framework\Database\Abstract\Model;
use Framework\Database\Abstract\Repository as RepositoryAbstract;
use Framework\Database\Exception\Db\Repository as RepositoryException;
use Framework\Pattern\Traits\Singleton as SingletonTrait;
use Framework\Helper\Strings as StringsHelper;

/**
 * @class Framework\Database\Abstract\Repository
 */
abstract class Repository {
    use SingletonTrait;

    /**
     * @var array $registered
     */
    private array $registered = [];

    /**
     * @var array
     */
    protected array $registeredKeys = [];

    /**
     * @var string $registeredId
     */
    protected string $registeredId = 'id';

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

        $valuesHash = StringsHelper::generateKey(...$values);
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
    protected function setRegisterModel(Model $entityModel): ?Model
    {
        $keys = $this->registeredKeys;

        if (!$entityModel->has($this->registeredId)) {
            return null;
        }

        foreach ($keys as $key) {
            if (!is_array($key)) {
                $this->registered[$key][StringsHelper::generateKey($entityModel->get($key))] = $entityModel;
                continue;
            }

            $itemKey = '';
            $itemKeyValue = [];
            foreach($key as $item) {
                $itemKey .= ($itemKey ? ':' : '') . $item;
                $itemKeyValue[] = $entityModel->get($item);
            }

            $this->registered[$itemKey][StringsHelper::generateKey(...$itemKeyValue)] = $entityModel;
        }

        return $entityModel;
    }
}