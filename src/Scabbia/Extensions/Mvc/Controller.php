<?php
/**
 * Scabbia Framework Version 1.1
 * http://larukedi.github.com/Scabbia-Framework/
 * Eser Ozvataf, eser@sent.com
 */

namespace Scabbia\Extensions\Mvc;

use Scabbia\Extensions\Helpers\FileSystem;
use Scabbia\Extensions\Mvc\ControllerBase;
use Scabbia\Io;

/**
 * Mvc Extension: Controller Class
 *
 * @package Scabbia
 * @subpackage Mvc
 * @version 1.1.0
 */
abstract class Controller extends ControllerBase
{
    /**
     * @ignore
     */
    public function mapDirectory($uDirectory, $uExtension, $uAction, array $uArgs)
    {
        $tMap = FileSystem::mapFlatten(Io::translatePath($uDirectory), '*' . $uExtension, true, true);

        array_unshift($uArgs, $uAction);
        $tPath = implode('/', $uArgs);

        if (in_array($tPath, $tMap, true)) {
            $this->view($uDirectory . $tPath . $uExtension);

            return true;
        }

        return false;
    }
}
