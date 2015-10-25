<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 10/20/13 11:21 PM $
* @package CBLib\Registry
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Registry;

use CBLib\Database\Table\TableInterface;

interface RegistryInterface extends ParamsInterface
{
	/**
	 * Sets the storage object of $this
	 *
	 * @param   RegistryInterface|TableInterface  $storage  The parent of this object
	 * @return  void
	 */
	public function setStorage( $storage );

	/**
	 * Gets the storage object of $this
	 *
	 * @return  RegistryInterface|TableInterface  The parent of this object
	 */
	public function getStorage( );
}
