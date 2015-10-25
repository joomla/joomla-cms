<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 10/29/13 8:52 PM $
* @package CBLib\Controller
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Controller;

/**
 * Interface CBLib\Controller\ControllerInterface
 */
interface ControllerInterface
{
	/**
	 * Dispatches the execution (and sets Output in Container $di)
	 *
	 * @param   string  $method
	 * @return  void
	 */
	public function dispatch( $method );
}
