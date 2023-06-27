<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;


/** @var \Joomla\Component\Privacy\Administrator\View\Capabilities\HtmlView $this */

?>
<div id="j-main-container" class="main-card p-4">
    <div class="alert alert-info">
        <h2 class="alert-heading"><?php echo $this->_('COM_PRIVACY_MSG_CAPABILITIES_ABOUT_THIS_INFORMATION'); ?></h2>
        <?php echo $this->_('COM_PRIVACY_MSG_CAPABILITIES_INTRODUCTION'); ?>
    </div>
    <?php if (empty($this->capabilities)) : ?>
        <div class="alert alert-info">
            <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo $this->_('INFO'); ?></span>
            <?php echo $this->_('COM_PRIVACY_MSG_CAPABILITIES_NO_CAPABILITIES'); ?>
        </div>
    <?php else : ?>
        <?php foreach ($this->capabilities as $extension => $capabilities) : ?>
            <details>
            <summary><?php echo $extension; ?></summary>
                <?php if (empty($capabilities)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo $this->_('INFO'); ?></span>
                        <?php echo $this->_('COM_PRIVACY_MSG_EXTENSION_NO_CAPABILITIES'); ?>
                    </div>
                <?php else : ?>
                    <ul>
                        <?php foreach ($capabilities as $capability) : ?>
                            <li><?php echo $capability; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </details>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
