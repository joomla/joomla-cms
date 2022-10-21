<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\AuthenticationHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('core')
    ->useScript('jquery')
    ->useScript('form.validate')
    ->useScript('keepalive')
    ->useScript('field.passwordview');

Text::script('JSHOWPASSWORD');
Text::script('JHIDEPASSWORD');
?>

<div class="alert alert-warning">
    <h4 class="alert-heading">
        <?php echo Text::_('COM_JOOMLAUPDATE_VIEW_UPLOAD_CAPTIVE_INTRO_HEAD'); ?>
    </h4>
    <p>
        <?php echo Text::sprintf('COM_JOOMLAUPDATE_VIEW_UPLOAD_CAPTIVE_INTRO_BODY', Factory::getApplication()->get('sitename')); ?>
    </p>
</div>

<hr>

<form action="<?php echo Route::_('index.php', true); ?>" method="post" id="form-login" class="text-center card">
    <fieldset class="loginform card-body">
        <legend class="h2 mb-3"><?php echo Text::_('COM_JOOMLAUPDATE_CAPTIVE_HEADLINE'); ?></legend>
        <div class="control-group">
            <div class="controls">
                <div class="input-group">
                    <input name="username" id="mod-login-username" type="text" class="form-control" required="required" autocomplete="username" placeholder="<?php echo Text::_('JGLOBAL_USERNAME'); ?>" size="15" autofocus="true">
                    <span class="input-group-text">
                        <span class="icon-user icon-fw" aria-hidden="true"></span>
                        <label for="mod-login-username" class="visually-hidden">
                            <?php echo Text::_('JGLOBAL_USERNAME'); ?>
                        </label>
                    </span>
                </div>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <div class="input-group">
                    <input name="passwd" id="mod-login-password" type="password" class="form-control" required="required" autocomplete="current-password" placeholder="<?php echo Text::_('JGLOBAL_PASSWORD'); ?>" size="15">
                    <button type="button" class="btn btn-secondary input-password-toggle">
                        <span class="icon-eye icon-fw" aria-hidden="true"></span>
                        <span class="visually-hidden"><?php echo Text::_('JSHOWPASSWORD'); ?></span>
                    </button>
                </div>
            </div>
        </div>
        <div class="control-group">
            <div class="controls">
                <a class="btn btn-danger" href="index.php?option=com_joomlaupdate">
                    <span class="icon-times icon-white" aria-hidden="true"></span> <?php echo Text::_('JCANCEL'); ?>
                </a>
                <button type="submit" class="btn btn-primary">
                    <span class="icon-play icon-white" aria-hidden="true"></span> <?php echo Text::_('COM_INSTALLER_INSTALL_BUTTON'); ?>
                </button>
            </div>
        </div>

        <input type="hidden" name="option" value="com_joomlaupdate">
        <input type="hidden" name="task" value="update.confirm">
        <?php echo HTMLHelper::_('form.token'); ?>
    </fieldset>
</form>
