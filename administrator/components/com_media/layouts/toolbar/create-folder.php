<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

Factory::getDocument()->getWebAssetManager()
	->useScript('webcomponent.toolbar-button');

$title = Text::_('COM_MEDIA_CREATE_NEW_FOLDER');
?>
<joomla-toolbar-button>
	<button class="btn btn-sm btn-info" onclick="MediaManager.Event.fire('onClickCreateFolder');">
		<span class="fas fa-folder" aria-hidden="true"></span>
		<?php echo $title; ?>
	</button>
</joomla-toolbar-button>
