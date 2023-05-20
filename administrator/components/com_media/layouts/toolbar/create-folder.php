<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
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
    <button class="btn btn-info" onclick="MediaManager.Event.fire('onClickCreateFolder');">
        <span class="icon-folder icon-fw" aria-hidden="true"></span>
        <?php echo $title; ?>
    </button>
</joomla-toolbar-button>
