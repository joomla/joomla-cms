<?php
/**
 * @version $Id: contact.php 3690 2006-05-27 04:59:14Z eddieajau $
 * @package Joomla
 * @subpackage Contact
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

global $Itemid;
?>
<script>
	alert( "<?php echo JText::_('CONTACT_FORM_NC', true); ?>" );
	document.location.href='<?php echo sefRelToAbs( 'index.php?option=com_contact&Itemid='. $Itemid ); ?>';
</script>