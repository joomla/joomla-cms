<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

?>
<button id="applyBtn" type="button" class="visually-hidden" onclick="Joomla.submitbutton('item.apply'); jEditMenuModal();"></button>
<button id="saveBtn" type="button" class="visually-hidden" onclick="Joomla.submitbutton('item.save'); jEditMenuModal();"></button>
<button id="closeBtn" type="button" class="visually-hidden" onclick="Joomla.submitbutton('item.cancel');"></button>

<div class="container-popup">
    <?php $this->setLayout('edit'); ?>
    <?php echo $this->loadTemplate(); ?>
</div>
