<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$title = Text::_('JTOOLBAR_DELETE');
?>
<button class="btn btn-sm btn-danger" onclick="MediaManager.Event.fire('onClickDelete');">
    <span class="icon-delete" title="<?php echo $title; ?>"></span> <?php echo $title; ?>
</button>