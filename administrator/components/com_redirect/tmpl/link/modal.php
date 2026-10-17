<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var \Joomla\Component\Redirect\Administrator\View\Link\HtmlView $this */
?>
<div class="subhead noshadow mb-3">
    <?php echo $this->getDocument()->getToolbar('toolbar')->render(); ?>
</div>
<div class="container-popup">
    <?php $this->setLayout('edit'); ?>
    <?php echo $this->loadTemplate(); ?>
</div>
