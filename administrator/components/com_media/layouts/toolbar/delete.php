<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$title = JText::_('JTOOLBAR_DELETE');
?>
<button class="btn btn-sm btn-outline-danger" onclick="MediaManager.Event.fire('onClickDelete');">
    <span class="icon-delete" title="<?php echo $title; ?>"></span> <?php echo $title; ?>
</button>