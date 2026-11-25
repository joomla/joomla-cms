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

$title = Text::_('JTOOLBAR_UPLOAD');
?>
<joomla-toolbar-button>
    <button class="btn btn-success" onclick="MediaManager.Event.fire('onClickUpload');">
        <span class="icon-upload" aria-hidden="true"></span>
        <?php echo $title; ?>
    </button>
</joomla-toolbar-button>
