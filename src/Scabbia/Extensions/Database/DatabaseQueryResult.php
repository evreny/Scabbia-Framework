<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Database;

use Scabbia\Extensions\Database\Database;
use Scabbia\Extensions\Datasources\Datasources;

/**
 * Database Extension: DatabaseQueryResult Class
 *
 * @package Scabbia
 * @subpackage Database
 * @version 1.1.0
 */
class DatabaseQueryResult implements \ArrayAccess, \Countable, \Iterator
{
    /**
     * @ignore
     */
    public $_query;
    /**
     * @ignore
     */
    public $_parameters;
    /**
     * @ignore
     */
    public $_object = null;
    /**
     * @ignore
     */
    public $_database = null;
    /**
     * @ignore
     */
    public $_caching = null;
    /**
     * @ignore
     */
    public $_sequence = null;
    /**
     * @ignore
     */
    public $_filename = null;
    /**
     * @ignore
     */
    public $_rows = array();
    /**
     * @ignore
     */
    public $_count = -1;
    /**
     * @ignore
     */
    public $_cursor = 0;


    /**
     * @ignore
     */
    public function __construct($uQuery, $uParameters, $uDatabase, $uCaching, $uSequence)
    {
        $this->_query = $uQuery;
        $this->_parameters = $uParameters;
        $this->_database = $uDatabase;
        $this->_caching = $uCaching;
        $this->_sequence = $uSequence;
    }

    /**
     * @ignore
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * @ignore
     */
    public function __isset($uKey)
    {
        return isset($this->_rows[$this->_cursor][$uKey]);
    }

    /**
     * @ignore
     */
    public function __get($uKey)
    {
        return $this->_rows[$this->_cursor][$uKey];
    }

    /**
     * @ignore
     */
    public function __set($uKey, $uValue)
    {
        return $this->_rows[$this->_cursor][$uKey] = $uValue;
    }

    /**
     * @ignore
     */
    public function __unset($uKey)
    {
        unset($this->_rows[$this->_cursor][$uKey]);
    }

    /**
     * @ignore
     */
    public function offsetExists($uOffset)
    {
        return isset($this->_rows[$this->_cursor][$uOffset]);
    }

    /**
     * @ignore
     */
    public function offsetGet($uOffset)
    {
        return $this->_rows[$this->_cursor][$uOffset];
    }

    /**
     * @ignore
     */
    public function offsetSet($uOffset, $uValue)
    {
        return $this->_rows[$this->_cursor][$uOffset] = $uValue;
    }

    /**
     * @ignore
     */
    public function offsetUnset($uOffset)
    {
        unset($this->_rows[$this->_cursor][$uOffset]);
    }

    /**
     * @ignore
     */
    public function count()
    {
        return $this->_count;
    }

    /**
     * @ignore
     */
    public function current()
    {
        return $this->_rows[$this->_cursor];
    }

    /**
     * @ignore
     */
    public function key()
    {
        return $this->_cursor;
    }

    /**
     * @ignore
     */
    public function next()
    {
        ++$this->_cursor;
    }

    /**
     * @ignore
     */
    public function execute($uReturnId = false)
    {
        try {
            $this->_object = $this->_database->queryDirect($this->_query, $this->_parameters);
            $this->_count = $this->_database->itCount($this->_object);
        } catch (\Exception $ex) {
            $tErrorHandling = $this->_database->errorHandling;
            $this->close();

            if ($tErrorHandling === Database::ERROR_EXCEPTION) {
                throw $ex;
            }

            return false;
        }

        if ($uReturnId) {
            if ($this->_sequence !== null && strlen($this->_sequence) > 0) {
                $tReturn = $this->_database->lastInsertId($this->_sequence);
            } else {
                $tReturn = $this->_database->lastInsertId();
            }
        } else {
            $tReturn = $this->_count;
        }

        $this->close();

        return $tReturn;
    }

    /**
     * @ignore
     */
    public function all()
    {
        // $this->_cursor = 0;
        while ($this->valid()) {
            ++$this->_cursor;
        }

        $this->close();

        return $this->_rows;
    }

    /**
     * @ignore
     */
    public function allWithKey($uKey)
    {
        $tItems = array();

        $this->_cursor = 0;
        while ($this->valid()) {
            $tCurrent = $this->current();
            $tItems[$tCurrent[$uKey]] = $tCurrent;
            ++$this->_cursor;
        }

        $this->close();

        return $tItems;
    }

    /**
     * @ignore
     */
    public function column($uKey)
    {
        $tItems = array();

        $this->_cursor = 0;
        while ($this->valid()) {
            $tCurrent = $this->current();
            $tItems[] = $tCurrent[$uKey];
            ++$this->_cursor;
        }

        $this->close();

        return $tItems;
    }

    /**
     * @ignore
     */
    public function row()
    {
        if (!$this->valid()) {
            $this->close();

            return false;
        }

        $tRow = $this->current();
        $this->close();

        return $tRow;
    }

    /**
     * @ignore
     */
    public function scalar($uColumn = 0, $uDefault = false)
    {
        if (!$this->valid()) {
            $this->close();

            return $uDefault;
        }

        $tRow = $this->current();
        $this->close();

        for ($i = 0; $i < $uColumn; $i++) {
            next($tRow);
        }

        return current($tRow);
    }

    /**
     * @ignore
     */
    public function rewind()
    {
        $this->_cursor = 0;
    }

    /**
     * @ignore
     */
    public function valid()
    {
        if (count($this->_rows) > $this->_cursor) {
            return true;
        }

        if ($this->_object === null) {
            try {
                $this->_object = $this->_database->queryDirect($this->_query, $this->_parameters);
                $this->_count = $this->_database->itCount($this->_object);

                if ($this->_count <= $this->_cursor) {
                    return false;
                }

                $this->_rows[$this->_cursor] = $this->_database->itSeek($this->_object, $this->_cursor);
            } catch (\Exception $ex) {
                if ($this->_database->errorHandling === Database::ERROR_EXCEPTION) {
                    throw $ex;
                }

                return false;
            }

            return true;
        }

        if ($this->_count <= $this->_cursor) {
            return false;
        }

        $this->_rows[$this->_cursor] = $this->_database->itNext($this->_object);

        return true;
    }

    /**
     * @ignore
     */
    public function close()
    {
        if ($this->_object !== null) {
            $this->_database->itClose($this->_object);
            $this->_object = null;
        }

        $this->_database = null;
        $this->_cursor = 0;

        if ($this->_caching !== null) {
            Datasources::get($this->_caching[0])->cacheSet($this->_caching[1], $this, $this->_caching[2]);
        }
    }

    /**
     * @ignore
     */
    public function resume($uDatabase)
    {
        $this->_database = $uDatabase;

        return $this;
    }
}
