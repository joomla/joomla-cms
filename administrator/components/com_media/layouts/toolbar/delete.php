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
use Joomla\CMS\Layout\LayoutHelper;

Factory::getDocument()->getWebAssetManager()
	->useScript('webcomponent.toolbar-button');

$title = Text::_('JTOOLBAR_DELETE');
?>
<joomla-toolbar-button>
	<button id="mediaDelete" class="btn btn-danger" onclick="MediaManager.Event.fire('onClickDelete');">
		<?php echo LayoutHelper::render('joomla.icon.iconclass', ['icon' => 'times']); ?>
		<?php echo $title; ?>
	</button>
</joomla-toolbar-button>
