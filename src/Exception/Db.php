<?php declare(strict_types=1);

namespace Framework\Database\Exception;

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
 * @class Framework\Database\Exception\Db
 * @package Framework\Database\Exception
 * @link https://tereta.dev
 * @since 2020-2024
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 * @copyright 2020-2024 Tereta Alexander
 */
class Db extends Exception
{
    private ?string $sqlQuery = null;

    private array $parameters = [];

    public function setQuery(string $query): self
    {
        $this->sqlQuery = $query;
        return $this;
    }

    public function getQuery(): ?string
    {
        return $this->sqlQuery;
    }

    public function setParameters(array $parameters = []): self
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function getParameters(): ?array
    {
        return $this->parameters;
    }
}