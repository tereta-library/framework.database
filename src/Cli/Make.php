<?php declare(strict_types=1);

namespace Framework\Database\Cli;

use Framework\Application\Manager as ApplicationManager;
use Framework\Cli\Interface\Controller;
use Framework\Cli\Symbol;
use Exception;
use Framework\Database\Abstract\Model as AbstractModel;
use Framework\Database\Abstract\Resource\Model as AbstractResourceModel;
use Framework\Database\Abstract\Resource\Collection as AbstractResourceCollection;

/**
 * @class Framework\Database\Cli\Make
 */
class Make implements Controller
{
    private string $rootDirectory;

    /**
     * @method __construct
     */
    public function __construct()
    {
        $this->rootDirectory = ApplicationManager::getRootDirectory();
    }

    /**
     * @cli make:model
     * @cliDescription Make model: samlpe "php cli make:model Vendor/Module/Model/Name"
     * @param string $modelName Full class name like "Vendor/Module/Model/Name" or "Vendor/Module/Model/Space/Name"
     * @return void
     * @throws Exception
     */
    public function make(string $modelName): void
    {
        $fullClassName = ltrim($modelName, '/');
        $fullClassName = ltrim($fullClassName, '\\');
        $fullClassName = str_replace('\\', '/', $fullClassName);
        if (!preg_match('/^([A-Z]{1}[a-z]+)\/([A-Z]{1}[a-z]+)\/Model(\/[A-Z]{1}[a-z]+)+$/', $fullClassName)) {
            throw new Exception('Invalid model name, should be in the format of "Vendor/Module/Model/Name" or "Vendor/Module/Model/Space/Name"');
        }

        $fullClassName = str_replace('/', '\\', $fullClassName);

        $modelFile = "{$this->rootDirectory}/app/module/{$fullClassName}.php";
        $modelFile = str_replace('\\', '/', $modelFile);
        if (is_file($modelFile)) {
            throw new Exception("The {$modelFile} file already exists");
        }

        $dirName = dirname($modelFile);
        if (!is_dir($dirName)) {
            mkdir($dirName, 0755, true);
        }

        $classExploded = explode('\\', $fullClassName);
        $className = array_pop($classExploded);
        $namespace = implode('\\', $classExploded);
        $dateTime = date('Y-m-d H:i:s');
        $content = "<?php declare(strict_types=1);\n\n" .
                   "namespace {$namespace};\n\n" .
                   "use " . AbstractModel::class . " as AbstractModel;\n\n" .
                   "/**\n" .
                   " * Generated by www.Tereta.dev on {$dateTime}\n" .
                   " *\n" .
                   " * @class {$fullClassName}\n" .
                   " * @package {$namespace}\n" .
                   " */\n" .
                   "class {$className} extends AbstractModel \n{\n}\n";

        file_put_contents($modelFile, $content);

        echo Symbol::COLOR_GREEN . "The \"{$fullClassName}\" model successfully created at the {$modelFile} file\n" . Symbol::COLOR_RESET;
    }

    /**
     * @cli make:model:resource
     * @cliDescription Make model: samlpe "php cli make:model:resource Vendor/Module/Model/Resource/Name"
     * @param string $resourceModelName Full class name like "Vendor/Module/Model/Resource/Name" or "Vendor/Module/Model/Resource/Space/Name"
     * @param string $tableName The table name in the database
     * @return void
     * @throws Exception
     */
    public function makeResource(string $resourceModelName, string $tableName): void
    {
        $fullClassName = ltrim($resourceModelName, '/');
        $fullClassName = ltrim($fullClassName, '\\');
        $fullClassName = str_replace('\\', '/', $fullClassName);
        if (!preg_match('/^([A-Z]{1}[a-z]+)\/([A-Z]{1}[a-z]+)\/Model\/Resource(\/[A-Z]{1}[a-z]+)+$/', $fullClassName)) {
            throw new Exception('Invalid model name, should be in the format of "Vendor/Module/Model/Resource/Name" or "Vendor/Module/Model/Resource/Space/Name"');
        }

        if (!preg_match('/^[A-Z0-9a-z_]+$/', $tableName)) {
            throw new Exception('Invalid table name, should be in the format of "a-z0-9_" for example "sampleTableName"');
        }

        $fullClassName = str_replace('/', '\\', $fullClassName);

        $modelFile = "{$this->rootDirectory}/app/module/{$fullClassName}.php";
        $modelFile = str_replace('\\', '/', $modelFile);
        if (is_file($modelFile)) {
            throw new Exception("The {$modelFile} file already exists");
        }

        $dirName = dirname($modelFile);
        if (!is_dir($dirName)) {
            mkdir($dirName, 0755, true);
        }

        $classExploded = explode('\\', $fullClassName);
        $className = array_pop($classExploded);
        $namespace = implode('\\', $classExploded);
        $dateTime = date('Y-m-d H:i:s');
        $content = "<?php declare(strict_types=1);\n\n" .
            "namespace {$namespace};\n\n" .
            "use " . AbstractResourceModel::class . " as AbstractResourceModel;\n" .
            "use Exception;\n\n" .
            "/**\n" .
            " * Generated by www.Tereta.dev on {$dateTime}\n" .
            " *\n" .
            " * @class {$fullClassName}\n" .
            " * @package {$namespace}\n" .
            " */\n" .
            "class {$className} extends AbstractResourceModel \n{\n" .
            "    /**\n" .
            "     * @throws Exception\n" .
            "     */\n" .
            "    public function __construct()\n" .
            "    {\n" .
            "        parent::__construct('{$tableName}');\n" .
            "    }\n" .
            "}\n";

        file_put_contents($modelFile, $content);

        echo Symbol::COLOR_GREEN . "The \"{$fullClassName}\" resource model successfully created at the {$modelFile} file\n" . Symbol::COLOR_RESET;
    }

    /**
     * @cli make:model:collection
     * @cliDescription Make model: samlpe "php cli make:model:collection Vendor/Module/Model/Resource/Name/Collection"
     * @param string $collectionName Full class name like "Vendor/Module/Model/Resource/Name/Collection" or "Vendor/Module/Model/Resource/Space/Name/Collection"
     * @param string|null $modelName Full class name like "Vendor/Module/Model/Resource/Name/Collection" or "Vendor/Module/Model/Resource/Space/Name/Collection"
     * @param string|null $resourceModelName Full class name like "Vendor/Module/Model/Resource/Name/Collection" or "Vendor/Module/Model/Resource/Space/Name/Collection"
     * @return void
     * @throws Exception
     */
    public function makeCollection(string $collectionName, ?string $modelName = null, ?string $resourceModelName = null): void
    {
        $fullCollectionName = ltrim($collectionName, '/');
        $fullCollectionName = ltrim($fullCollectionName, '\\');
        $fullCollectionName = str_replace('\\', '/', $fullCollectionName);
        if (!preg_match('/^([A-Z]{1}[a-z]+)\/([A-Z]{1}[a-z]+)\/Model\/Resource(\/[A-Z]{1}[a-z]+)+\/Collection$/', $fullCollectionName)) {
            throw new Exception('Invalid collection name, should be in the format of "Vendor/Module/Model/Resource/Name/Collection" or "Vendor/Module/Model/Resource/Space/Name/Collection"');
        }

        $fullCollectionName = str_replace('/', '\\', $fullCollectionName);

        $modelFile = "{$this->rootDirectory}/app/module/{$fullCollectionName}.php";
        $modelFile = str_replace('\\', '/', $modelFile);
        if (is_file($modelFile)) {
            throw new Exception("The {$modelFile} file already exists");
        }

        $dirName = dirname($modelFile);
        if (!is_dir($dirName)) {
            mkdir($dirName, 0755, true);
        }

        $classExploded = explode('\\', $fullCollectionName);
        $className = array_pop($classExploded);
        $namespace = implode('\\', $classExploded);
        $dateTime = date('Y-m-d H:i:s');

        if (!$modelName) {
            $modelSource = $classExploded;
            $modelName = array_shift($modelSource);
            $modelName .= '\\' . array_shift($modelSource);
            $modelName .= '\\' . array_shift($modelSource);
            array_shift($modelSource);
            foreach ($modelSource as $modelSourceItem) {
                $modelName .= '\\' . $modelSourceItem;
            }
        }

        if (!$resourceModelName) {
            $modelSource = $classExploded;
            $resourceModelName = '';
            foreach ($modelSource as $modelSourceItem) {
                $resourceModelName .= ($resourceModelName ? '\\' : '') . $modelSourceItem;
            }
        }

        $fullModelName = ltrim($modelName, '/');
        $fullModelName = ltrim($fullModelName, '\\');
        $fullModelName = str_replace('\\', '/', $fullModelName);
        if (!preg_match('/^([A-Z]{1}[a-z]+)\/([A-Z]{1}[a-z]+)\/Model(\/[A-Z]{1}[a-z]+)+$/', $fullModelName)) {
            throw new Exception('Invalid model name, should be in the format of "Vendor/Module/Model/Name" or "Vendor/Module/Model/Space/Name"');
        }
        $fullModelName = str_replace('/', '\\', $fullModelName);

        $fullResourceModelName = ltrim($resourceModelName, '/');
        $fullResourceModelName = ltrim($fullResourceModelName, '\\');
        $fullResourceModelName = str_replace('\\', '/', $fullResourceModelName);
        if (!preg_match('/^([A-Z]{1}[a-z]+)\/([A-Z]{1}[a-z]+)\/Model(\/[A-Z]{1}[a-z]+)+$/', $fullResourceModelName)) {
            throw new Exception('Invalid model name, should be in the format of "Vendor/Module/Model/Resource/Name" or "Vendor/Module/Model/Resource/Space/Name"');
        }
        $fullResourceModelName = str_replace('/', '\\', $fullResourceModelName);

        $content = "<?php declare(strict_types=1);\n\n" .
            "namespace {$namespace};\n\n" .
            "use " . AbstractResourceCollection::class . " as AbstractCollectionModel;\n" .
            "use " . $fullResourceModelName . " as ResourceModel;\n" .
            "use " . $fullModelName . " as Model;\n" .
            "use Exception;\n\n" .
            "/**\n" .
            " * Generated by www.Tereta.dev on {$dateTime}\n" .
            " *\n" .
            " * @class {$fullCollectionName}\n" .
            " * @package {$namespace}\n" .
            " */\n" .
            "class {$className} extends AbstractCollectionModel \n{\n" .
            "    /**\n" .
            "     * @throws Exception\n" .
            "     */\n" .
            "    public function __construct()\n" .
            "    {\n" .
            "        parent::__construct(ResourceModel::class, Model::class);\n" .
            "    }\n" .
            "}\n";

        file_put_contents($modelFile, $content);

        echo Symbol::COLOR_GREEN . "The \"{$fullCollectionName}\" resource model successfully created at the {$modelFile} file\n" . Symbol::COLOR_RESET;
    }
}