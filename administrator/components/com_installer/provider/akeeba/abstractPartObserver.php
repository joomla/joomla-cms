<?php

/**
 * Akeeba Restore
 * A JSON-powered JPA, JPS and ZIP archive extraction library
 *
 * @copyright   2010-2014 Nicholas K. Dionysopoulos / Akeeba Ltd.
 * @license     GNU GPL v2 or - at your option - any later version
 * @package     akeebabackup
 * @subpackage  kickstart
 */

/**
 * Descendants of this class can be used in the unarchiver's observer methods (attach, detach and notify)
 * @author Nicholas
 *
 */
abstract class AKAbstractPartObserver
{
  abstract public function update($object, $message);
}
