<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait DatabaseCompatibility
{
    /**
     * Get the appropriate date format function based on the database driver
     */
    protected function getDateFormatFunction()
    {
        $driver = DB::getDriverName();
        
        return match($driver) {
            'mysql' => 'DATE_FORMAT',
            'sqlite' => 'strftime',
            'pgsql' => 'to_char',
            default => 'DATE_FORMAT'
        };
    }

    /**
     * Get the appropriate date format string based on the database driver
     */
    protected function getDateFormatString($format = '%Y-%m')
    {
        $driver = DB::getDriverName();
        
        return match($driver) {
            'mysql' => $format, // MySQL DATE_FORMAT nécessite les %
            'sqlite' => $format,
            'pgsql' => str_replace(['%Y', '%m', '%d', '%u'], ['YYYY', 'MM', 'DD', 'WW'], $format),
            default => $format
        };
    }

    /**
     * Build a date format select raw query that works across different databases
     */
    protected function buildDateFormatSelect($column, $format = '%Y-%m', $alias = 'month')
    {
        $driver = DB::getDriverName();
        
        return match($driver) {
            'mysql' => "DATE_FORMAT({$column}, '{$this->getDateFormatString($format)}') as {$alias}",
            'sqlite' => "strftime('{$format}', {$column}) as {$alias}",
            'pgsql' => "to_char({$column}, '{$this->getDateFormatString($format)}') as {$alias}",
            default => "DATE_FORMAT({$column}, '{$this->getDateFormatString($format)}') as {$alias}"
        };
    }

    /**
     * Check if the current database is SQLite
     */
    protected function isSqlite()
    {
        return DB::getDriverName() === 'sqlite';
    }

    /**
     * Check if the current database is MySQL
     */
    protected function isMysql()
    {
        return DB::getDriverName() === 'mysql';
    }

    /**
     * Check if the current database is PostgreSQL
     */
    protected function isPostgresql()
    {
        return DB::getDriverName() === 'pgsql';
    }
}
