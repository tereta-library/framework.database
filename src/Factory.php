<?php declare(strict_types=1);

namespace Framework\Database;

use Framework\Database\Select\Builder as SelectBuilder;
use Framework\Database\Select\Factory as SelectFactory;
use Framework\Database\Create\Builder as CreateBuilder;
use Framework\Database\Create\Factory as CreateFactory;
use Framework\Database\Insert\Builder as InsertBuilder;
use Framework\Database\Insert\Factory as InsertFactory;

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
 * @class Framework\Database\Factory
 * @package Framework\Database
 * @link https://tereta.dev
 * @since 2020-2024
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 * @copyright 2020-2024 Tereta Alexander
 */
class Factory
{
    /**
     * @param array ...$columns
     * @return SelectBuilder
     */
    public static function createSelect(...$columns): SelectBuilder
    {
        return SelectFactory::create($columns);
    }

    /**
     * @param string $table
     * @return CreateBuilder
     */
    public static function createTable(string $table): CreateBuilder
    {
        return CreateFactory::create($table);
    }

    /**
     * @param string $table
     * @return InsertBuilder
     */
    public static function createInsert(string $table): InsertBuilder
    {
        return InsertFactory::create($table);
    }
}