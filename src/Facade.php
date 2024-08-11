<?php declare(strict_types=1);

namespace Framework\Database;

use PDO;

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
 * @class Framework\Database\Facade
 * @package Framework\Database
 * @link https://tereta.dev
 * @since 2020-2024
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 * @author Tereta Alexander <tereta.alexander@gmail.com>
 * @copyright 2020-2024 Tereta Alexander
 */
class Facade
{
    /**
     * @param PDO $connection
     * @param string $table
     * @return array
     */
    public static function describeTable(PDO $connection, string $table): array
    {
        $smt = $connection->prepare("DESCRIBE {$table}");
        $smt->execute();

        return $smt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @return string
     */
    public static function dropTable(): string
    {
        return 'DROP TABLE ?';
    }

    /**
     * @param PDO $connection
     * @param string|null $like
     * @return array
     */
    public static function showTables(PDO $connection, ?string $like = null): array
    {
        if ($like) {
            $smt = $connection->prepare('SHOW TABLES LIKE :like');
            $smt->bindParam(':like', $like);
        } else {
            $smt = $connection->prepare('SHOW TABLES');
        }
        $smt->execute();

        return $smt->fetchAll(PDO::FETCH_COLUMN);
    }
}