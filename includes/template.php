<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_BASE . '/includes/template.html.php' );

/**
 * Get the number of modules loaded for a particular template position
 *
 * @param 	string 	The mdoule position
 * @return 	integer The number of modules loaded for that position
 */
function mosCountModules( $position='left' ) {
	return count(JModuleHelper::getModules($position));
}

/**
 * Insert a component placeholdere
 */
function mosMainBody() {
	?><jdoc:include type="component" /><?php
}

/**
 * Insert a modules placholder
 *
 * @param string 	The position of the modules
 * @param integer 	The style.  0=normal, 1=horiz, -1=no wrapper
 */
function mosLoadModules( $position='left', $style=0 ) {
	?><jdoc:include type="modules" name="<?php echo $position ?>" style="<?php echo $style ?>"/><?php
}

/**
 * Insert a module placholder
 *
 * @param string 	The name of the module
 * @param integer 	The style.  0=normal, 1=horiz, -1=no wrapper
 */
function mosLoadModule( $name, $style=-1 ) {
	?><jdoc:include type="module" name="<?php echo $name ?>" style="<?php echo $style ?>" /><?php
}

/**
* Insert a head placeholder
*/
function mosShowHead() {
	?><jdoc:include type="head" /><?php
}
?>