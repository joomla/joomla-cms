<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var Joomla\Component\Users\Administrator\View\User\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate');

$input = Factory::getApplication()->input;

// Get the form fieldsets.
$fieldsets = $this->form->getFieldsets();
$settings  = [];

$this->useCoreUI = true;
?>
<form action="<?php echo Route::_('index.php?option=com_users&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="user-form" enctype="multipart/form-data" aria-label="<?php echo Text::_('COM_USERS_USER_FORM_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>" class="form-validate">

    <h2><?php echo $this->escape($this->form->getValue('name', null, Text::_('COM_USERS_USER_NEW_USER_TITLE'))); ?></h2>

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'details', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'details', Text::_('COM_USERS_USER_ACCOUNT_DETAILS')); ?>
            <fieldset class="options-form">
                <legend><?php echo Text::_('COM_USERS_USER_ACCOUNT_DETAILS'); ?></legend>
                <div class="form-grid">
                    <?php echo $this->form->renderFieldset('user_details'); ?>
                </div>
            </fieldset>

        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php if ($this->grouplist) : ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'groups', Text::_('COM_USERS_ASSIGNED_GROUPS')); ?>
                <fieldset id="fieldset-groups" class="options-form">
                    <legend><?php echo Text::_('COM_USERS_ASSIGNED_GROUPS'); ?></legend>
                    <div>
                    <?php echo $this->loadTemplate('groups'); ?>
                    </div>
                </fieldset>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>

        <?php
        $this->ignore_fieldsets = ['user_details'];
        echo LayoutHelper::render('joomla.edit.params', $this);
        ?>

        <?php if (!empty($this->mfaConfigurationUI)) : ?>
            <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'multifactorauth', Text::_('COM_USERS_USER_MULTIFACTOR_AUTH')); ?>
            <fieldset class="options-form">
                <legend><?php echo Text::_('COM_USERS_USER_MULTIFACTOR_AUTH'); ?></legend>
                <?php echo $this->mfaConfigurationUI ?>
            </fieldset>
            <?php echo HTMLHelper::_('uitab.endTab'); ?>
        <?php endif; ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="">
    <input type="hidden" name="return" value="<?php echo $input->getBase64('return'); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
