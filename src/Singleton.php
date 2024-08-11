<?php declare(strict_types=1);

namespace Framework\Database;

use PDO;
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
 * @class Framework\Database\Singleton
 * @package Framework\Database
 * @link https://tereta.dev
 * @since 2020-2024
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 * @copyright 2020-2024 Tereta Alexander
 */
class Singleton
{
    /**
     * @var array $connectionInstances
     */
    private static array $connectionInstances = [];

    /**
     * @var array $connectionConfigs
     */
    private static array $connectionConfigs = [];

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $name
     * @return void
     */
    public static function createConnection(string $host, string $user, string $password, string $database, string $name = 'default'): void
    {
        static::$connectionConfigs[$name] = [
            'host'     => $host,
            'user'     => $user,
            'password' => $password,
            'database' => $database
        ];
    }

    /**
     * @param string $name
     * @return void
     * @throws Exception
     */
    private static function initConnection(string $name): void
    {
        if (!isset(static::$connectionConfigs[$name])) {
            throw new Exception("Connection {$name} not found");
        }

        $config = static::$connectionConfigs[$name];

        static::$connectionInstances[$name] = new PDO(
            "mysql:dbname={$config['database']};host={$config['host']}",
            $config['user'],
            $config['password']
        );
    }

    /**
     * @param string $name
     * @return PDO
     * @throws Exception
     */
    public static function getConnection(string $name = 'default'): PDO
    {
        if (!isset(static::$connectionInstances[$name])) {
            static::initConnection($name);
        }

        return static::$connectionInstances[$name];
    }
}