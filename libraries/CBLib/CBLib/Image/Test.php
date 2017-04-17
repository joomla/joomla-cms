<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 07.06.13 23:17 $
* @package CBLib\Image
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Image;

use Imagine;
use Exception;

defined('CBLIB') or die();

/**
 * CBLib\Image\Test Class implementation
 */
class Test {

	public function __construct() {}

	/**
	 * Checks if Gd image processing is available
	 *
	 * @return bool
	 */
	public function Gd() {
		try {
			new Imagine\Gd\Imagine();

			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Checks if Imagick image processing is available
	 *
	 * @return bool
	 */
	public function Imagick() {
		try {
			new Imagine\Imagick\Imagine();

			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}

	/**
	 * Checks if Gmagick image processing is available
	 *
	 * @return bool
	 */
	public function Gmagick() {
		try {
			new Imagine\Gmagick\Imagine();

			return true;
		} catch ( Exception $e ) {
			return false;
		}
	}
}
