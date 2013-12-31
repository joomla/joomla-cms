<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$title = JText::_('JTOOLBAR_DELETE');
?>
<button onclick="MediaManager.submit('folder.delete')" class="btn btn-small">
	<i class="icon-remove" title="<?php echo $title; ?>"></i> <?php echo $title; ?>
</button>
