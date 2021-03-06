<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Datasources;

use Scabbia\Extensions\Datasources\IDataInterface;

/**
 * Datasources Extension: IServerConnection interface
 *
 * @package Scabbia
 * @subpackage Datasources
 * @version 1.1.0
 */
interface IServerConnection extends IDataInterface
{
    public function connectionOpen();
    public function connectionClose();
    public function serverInfo();
}
