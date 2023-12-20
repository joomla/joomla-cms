<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

$hadErrors    = $this->state->get('update_finished_with_error');
$errors       = $this->state->get('update_errors');
$logFile      = $this->state->get('log_file');
$installerMsg = $this->state->get('installer_message');
$forumLink    = '<a href="https://forum.joomla.org/" target="_blank" rel="noopener noreferrer">https://forum.joomla.org/</a>';

?>
<div class="card">
    <h2 class="card-header"><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_COMPLETE_HEADING'); ?></h2>
    <div class="card-body">
        <?php if (!$hadErrors) : ?>
            <div class="alert alert-success">
                <span class="icon-check-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('NOTICE'); ?></span>
                <?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_COMPLETE_MESSAGE', '&#x200E;' . JVERSION); ?>
            </div>
        <?php else : ?>
            <div class="alert alert-error">
                <span class="icon-check-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('NOTICE'); ?></span>
                <?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_COMPLETE_WITH_ERROR_MESSAGE', $logFile, $forumLink); ?>
            </div>
            <p>
                <a href="<?php echo Uri::base(true); ?>/" class="btn btn-primary"><?php echo Text::_('JGLOBAL_TPL_CPANEL_LINK_TEXT') ?></a>
            </p>
            <?php if ($errors) : ?>
                <h3><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_COMPLETE_UPDATE_ERRORS'); ?></h3>
                <?php foreach ($errors as $error) : ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($installerMsg) : ?>
        <div>
            <h3><?php echo Text::_('COM_JOOMLAUPDATE_VIEW_COMPLETE_INSTALLER_MESSAGE'); ?></h3>
            <div class="alert alert-warning"><?php echo $installerMsg ?></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<form action="<?php echo Route::_('index.php?option=com_joomlaupdate'); ?>" method="post" id="adminForm">
    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
