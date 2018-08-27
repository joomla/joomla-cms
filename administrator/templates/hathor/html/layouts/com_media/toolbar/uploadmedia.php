<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$title = JText::_('JTOOLBAR_UPLOAD');
?>
<button data-toggle="collapse" data-target="#collapseUpload" class="toolbar">
	<span class="icon-32-upload" title="<?php echo $title; ?>"></span> <?php echo $title; ?>
</button>
