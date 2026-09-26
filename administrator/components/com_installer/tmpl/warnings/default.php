<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var \Joomla\Component\Installer\Administrator\View\Warnings\HtmlView $this */
?>
<div id="installer-warnings" class="clearfix">
    <form action="<?php echo Route::_('index.php?option=com_installer&view=warnings'); ?>" method="post" name="adminForm" id="adminForm">
        <div class="row">
            <div class="col-md-12">
                <div id="j-main-container" class="j-main-container">
                    <?php if (count($this->messages)) : ?>
                        <?php foreach ($this->messages as $message) : ?>
                            <div class="alert alert-warning">
                                <h4 class="alert-heading">
                                    <span class="icon-exclamation-triangle" aria-hidden="true"></span>
                                    <span class="visually-hidden"><?php echo Text::_('WARNING'); ?></span>
                                    <?php echo $message['message']; ?>
                                </h4>
                                <p class="mb-0"><?php echo $message['description']; ?></p>
                            </div>
                        <?php endforeach; ?>
                        <div class="alert alert-info">
                            <h4 class="alert-heading">
                                <span class="icon-info-circle" aria-hidden="true"></span>
                                <span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                                <?php echo Text::_('COM_INSTALLER_MSG_WARNINGFURTHERINFO'); ?>
                            </h4>
                            <p class="mb-0"><?php echo Text::_('COM_INSTALLER_MSG_WARNINGFURTHERINFODESC'); ?></p>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-info">
                            <span class="icon-info-circle" aria-hidden="true"></span>
                            <span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                            <?php echo Text::_('COM_INSTALLER_MSG_WARNINGS_NONE'); ?>
                        </div>
                    <?php endif; ?>
                    <div>
                        <input type="hidden" name="boxchecked" value="0">
                        <?php echo HTMLHelper::_('form.token'); ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
