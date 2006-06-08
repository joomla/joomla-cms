<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Contact
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


/**
 * Contact Component HTML View Class
 *
 * @static
 * @package Joomla
 * @subpackage Contact
 * @since 1.5
 */
class JContactView
{
	/**
	 * Method to output an email sending error view
	 *
	 * @static
	 * @since 1.0
	 */
	function emailError()
	{
		global $Itemid;
		$option = JRequest::getVar('option');
		?>
		<script>
		alert( "<?php echo JText::_('CONTACT_FORM_NC', true); ?>" );
		document.location.href='<?php echo sefRelToAbs( 'index.php?option='. $option .'&Itemid='. $Itemid ); ?>';
		</script>
		<?php
	}

}
?>