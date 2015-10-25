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
 * Interface NamespaceInterface
 * This interface allows namespacing of GetterInterfaces or other items with other namespaces accessible
 *
 * @package CBLib
 */
interface NamespaceInterface {
	/**
	 * Sets the namespace $name of $this Registry to be $registry
	 *
	 * @param  string           $name      Namespace of the registry
	 * @param  ParamsInterface  $registry  The corresponding registry
	 * @return self                                 For chaining
	 */
	public function setNamespaceRegistry( $name, ParamsInterface $registry );

	/**
	 * Gets the namespaced Registry of $this
	 *
	 * @param  string           $name  Namespace of the registry
	 * @return ParamsInterface         The corresponding registry (if not existent, throws exception)
	 *
	 * @throws \InvalidArgumentException
	 */
	public function getNamespaceRegistry( $name );

	/**
	 * Checks if namespaced $name Parameters exist
	 *
	 * @param  string   $name  Namespace of the parameters
	 * @return boolean         True: exists
	 */
	public function hasNamespaceRegistry( $name );
}
