<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load the mootools framework.
JHtml::_('behavior.mootools');
?>

<script type="text/javascript">
	window.addEvent('domready', function(){ window.parent.document.getElementById('sbox-window').close(); });
</script>