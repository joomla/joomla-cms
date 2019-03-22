<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$title = Text::_('JTOOLBAR_UPLOAD');
?>
<button class="btn btn-sm btn-success" onclick="MediaManager.Event.fire('onClickUpload');">
	<span class="icon-upload"></span> <?php echo $title; ?>
</button>
