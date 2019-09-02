<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('webcomponent', 'system/joomla-toolbar-button.min.js', ['version' => 'auto', 'relative' => true]);

$title = Text::_('COM_MEDIA_CREATE_NEW_FOLDER');
?>
<joomla-toolbar-button class="btn btn-sm btn-info" onclick="MediaManager.Event.fire('onClickCreateFolder');">
	<span class="icon-folder-close"></span> <?php echo $title; ?>
</joomla-toolbar-button>
