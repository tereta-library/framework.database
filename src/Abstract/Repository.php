<?php declare(strict_types=1);

namespace Framework\Database\Abstract;

use Builder\Site\Model\Site as EntityModel;
use Exception;
use Framework\Database\Abstract\Model;
use Framework\Database\Abstract\Model as ModelAbstract;
use Framework\Database\Abstract\Repository as RepositoryAbstract;
use Framework\Database\Exception\Db\Repository as RepositoryException;
use Framework\Pattern\Traits\Singleton as SingletonTrait;
use Framework\Helper\Strings as StringsHelper;
use Framework\Database\Abstract\Resource\Model as AbstractResourceModel;
use Framework\Database\Abstract\Resource\Collection as AbstractResourceCollection;
use Framework\Pattern\Traits\Cache as CacheTrait;

/**
 * @class Framework\Database\Abstract\Repository
 */
abstract class Repository {
    use SingletonTrait;
    use CacheTrait;

    /**
     * @var ModelAbstract
     */
    protected ModelAbstract $model;

    /**
     * @var ResourceModelAbstract
     */
    protected AbstractResourceModel $resourceModel;

    /**
     * @var ResourceModelCollectionAbstract
     */
    protected ?AbstractResourceCollection $collection;

    /**
     * @throws RepositoryException
     */
    protected function __construct()
    {
        /**
        $this->model = new ModelAbstract;
        $this->resourceModel = new ResourceModelAbstract;
        $this->collection = new ResourceModelCollectionAbstract;
        */
    }

    /**
     * @param int $id
     * @return ModelAbstract
     * @throws Exception
     */
    public function getById(int $id): ModelAbstract
    {
        if ($cached = $this->getCache($id)) {
            return $cached;
        }

        $this->resourceModel->load($model = new ($this->entityModel::class), ['id' => $id]);

        if (!$model->get('id')) {
            throw new Exception('Model not found');
        }

        return $this->setCache($model, $id);
    }
}