<?php

/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$this->getDocument()->getWebAssetManager()->useScript('form.validate');

$direction = Factory::getLanguage()->isRtl() ? 'left' : 'right';

/** @var \Joomla\CMS\Installation\View\Setup\HtmlView $this */

$wa = $this->getDocument()->getWebAssetManager();

$wa->useScript('joomla.dialog-autocreate');
?>

<div id="installer-view" data-page-name="setup">
    <form action="index.php" method="post" id="adminForm" class="form-validate">
        <fieldset id="installStep1" class="j-install-step active">
            <legend class="j-install-step-header">
                <span class="icon-cog" aria-hidden="true"></span> <?php echo Text::_('INSTL_SETUP_SITE_NAME'); ?>
            </legend>
            <div class="j-install-step-form">
                <div class="mb-3">
                    <?php echo $this->form->renderField('site_name'); ?>
                </div>
                <div class="mt-4 mb-3">
                    <button id="step1" class="btn btn-primary w-100"><?php echo Text::_('INSTL_SETUP_LOGIN_DATA'); ?> <span class="icon-chevron-<?php echo $direction; ?>" aria-hidden="true"></span></button>
                </div>
            </div>
        </fieldset>
        <fieldset id="installStep2" class="j-install-step">
            <legend class="j-install-step-header">
                <span class="icon-lock" aria-hidden="true"></span> <?php echo Text::_('INSTL_LOGIN_DATA'); ?>
            </legend>
            <div class="j-install-step-form">
                <div class="mb-3">
                    <?php echo $this->form->renderField('admin_user'); ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->form->renderField('admin_username'); ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->form->renderField('admin_password'); ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->form->renderField('admin_email'); ?>
                </div>
                <div class="mt-4 mb-3">
                    <button id="step2" class="btn btn-primary w-100"><?php echo Text::_('INSTL_CONNECT_DB'); ?> <span class="icon-chevron-<?php echo $direction; ?>" aria-hidden="true"></span></button>
                </div>
            </div>
        </fieldset>
        <fieldset id="installStep3" class="j-install-step" >
            <legend class="j-install-step-header">
                <span class="icon-database" aria-hidden="true"></span> <?php echo Text::_('INSTL_DATABASE'); ?>
            </legend>
            <div class="j-install-step-form">
                <div class="mb-3">
                    <?php echo $this->form->renderField('db_type'); ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->form->renderField('db_host'); ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->form->renderField('db_user'); ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->form->renderField('db_pass'); ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->form->renderField('db_name'); ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->form->renderField('db_prefix'); ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->form->renderField('db_encryption'); ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->form->renderField('db_sslkey'); ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->form->renderField('db_sslcert'); ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->form->renderField('db_sslverifyservercert'); ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->form->renderField('db_sslca'); ?>
                </div>
                <div class="mb-3">
                    <?php echo $this->form->renderField('db_sslcipher'); ?>
                </div>
                <div class="mb-3">
                    <?php //echo $this->form->getLabel('db_old'); ?>
                    <?php echo $this->form->getInput('db_old'); ?>
                </div>
                <div class="mt-4 mb-3">
                    <button id="setupButton" class="btn btn-primary w-100"><?php echo Text::_('INSTL_INSTALL_JOOMLA'); ?> <span class="icon-chevron-<?php echo $direction; ?>" aria-hidden="true"></span></button>
                </div>
            </div>
        </fieldset>
        <fieldset id="installStep4" class="j-install-step" >
            <legend class="j-install-step-header">
                <span class="icon-cogs" aria-hidden="true"></span> <?php echo Text::_('INSTL_PROGRESS'); ?>
            </legend>
            <div class="j-install-step-form" aria-live="polite" >
                <label class="progresslabel text-center">
                    <progress class="progressbar" id="progressbar" value="0" max="8"></progress>
                    <span id="progress-text" role="status"><?php echo Text::_('INSTL'); ?></span>
                </label>
            </div>
        </fieldset>
        <input type="hidden" name="admin_password2" id="jform_admin_password2">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>
    <form action="index.php" method="post" id="languageForm" class="lang-select">
        <div class="d-flex align-items-center">
            <span class="fas fa-globe me-1" aria-hidden="true"></span>
            <?php
                $dataAttribs = [
                    'textHeader'      => Text::_('INSTL_SELECT_INSTALL_LANG'),
                    'iconHeader'      => 'icon-language',
                    'src'             => '#languageSelect',
                ];

                $text = '<span class="ms-1 fw-bold" id="languageForm-current"></span>';

                $text .= '<button type="button" data-joomla-dialog="' . htmlspecialchars(json_encode($dataAttribs)) . '" aria-label="' . Text::_('INSTL_CHANGE_INSTALL_LANG') . '" class="btn btn-primary btn-sm ms-2"><span class="fas fa-repeat fa-fw me-2" aria-hidden="true"></span>' . Text::_('INSTL_CHANGE_INSTALL_LANG_SHORT') . '</button>';

                echo Text::sprintf('INSTL_SELECTED_INSTALL_LANGUAGE', $text);
                ?>
        </div>
        <template id="languageSelect">
            <div class="mb-3">
                <?php echo $this->form->renderField('language'); ?>
            </div>
        </template>
        <input type="hidden" name="task" value="language.set">
        <input type="hidden" name="format" value="json">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>

</div>
