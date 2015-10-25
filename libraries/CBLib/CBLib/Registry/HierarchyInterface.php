<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 10/31/13 11:29 PM $
* @package CBLib
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/


namespace CBLib\Registry;


/**
 * Interface HierarchyInterface
 * This interface allows a hierarchy of GetterInterfaces or other items with parents stored in children but not the reverse
 *
 * @package CBLib
 */
interface HierarchyInterface {
	/**
	 * Sets the parent of $this
	 *
	 * @param   HierarchyInterface  $parent  The parent of this object
	 * @return  void
	 */
	public function setParent( HierarchyInterface $parent );

	/**
	 * Gets the parent of $this
	 *
	 * @return  HierarchyInterface  The parent of this object
	 */
	public function getParent( );
}
