<?php

/**
 * @package     Joomla.Installation
 * @subpackage  View
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.formvalidator');

/** @var \Joomla\CMS\Installation\View\Setup\HtmlView $this */

$steps = 6;

if (is_file(JPATH_INSTALLATION . '/sql/mysql/localise.sql')) :
    $steps++;
endif

if (is_file(JPATH_INSTALLATION . '/sql/mysql/custom.sql')) :
    $steps++;
endif
?>

<div id="installer-view" data-page-name="setup">
    <form action="index.php" method="post" id="languageForm" class="lang-select">
        <fieldset class="j-install-step active">
            <legend class="j-install-step-header">
                <span class="icon-language" aria-hidden="true"></span> <?php echo Text::_('INSTL_SELECT_INSTALL_LANG'); ?>
            </legend>
            <div class="j-install-step-form">
                <div class="mb-3">
                    <?php echo $this->form->renderField('language'); ?>
                </div>
                <input type="hidden" name="task" value="language.set">
                <input type="hidden" name="format" value="json">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </fieldset>
    </form>
    <form action="index.php" method="post" id="adminForm" class="form-validate">
        <fieldset id="installStep1" class="j-install-step active">
            <legend class="j-install-step-header">
                <span class="icon-cog" aria-hidden="true"></span> <?php echo Text::_('INSTL_SETUP_SITE_NAME'); ?>
            </legend>
            <div class="j-install-step-form">
                <div class="mb-3">
                    <?php echo $this->form->renderField('site_name'); ?>
                </div>
                <div class="mb-3 mt-4">
                    <button class="btn btn-primary w-100" id="step1"><?php echo Text::_('INSTL_SETUP_LOGIN_DATA'); ?> <span class="icon-chevron-right" aria-hidden="true"></span></button>
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
                <div class="mb-3 mt-4">
                    <button class="btn btn-primary w-100" id="step2"><?php echo Text::_('INSTL_CONNECT_DB'); ?> <span class="icon-chevron-right" aria-hidden="true"></span></button>
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
                <div class="mb-3 mt-4">
                    <button class="btn btn-primary w-100" id="setupButton"><?php echo Text::_('INSTL_INSTALL_JOOMLA'); ?> <span class="icon-chevron-right" aria-hidden="true"></span></button>
                </div>
            </div>
        </fieldset>
        <fieldset id="installStep4" class="j-install-step" >
            <legend class="j-install-step-header">
                <span class="icon-cogs" aria-hidden="true"></span> <?php echo Text::_('INSTL_PROGRESS'); ?>
            </legend>
            <div class="j-install-step-form">
                <div class="progress mb-4">
                    <div class="progress-bar" role="progressbar" id="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="<?php echo $steps; ?>"></div>
                </div>
                <ul class="list-unstyled">
                    <li id="progressdbcheck" aria-hidden="true">
                        <span class="fa fa-spinner fa-spin text-white"></span>
                        <?php echo Text::_('INSTL_PROGRESS_STEP_DBCHECK'); ?>
                    </li>
                    <li id="progresscreate" aria-hidden="true">
                        <span class="fa fa-spinner fa-spin text-white"></span>
                        <?php echo Text::_('INSTL_PROGRESS_STEP_CREATE'); ?>
                    </li>
                    <li id="progresspopulate1" aria-hidden="true">
                        <span class="fa fa-spinner fa-spin text-white"></span>
                        <?php echo Text::_('INSTL_PROGRESS_STEP_POPULATE1'); ?>
                    </li>
                    <li id="progresspopulate2" aria-hidden="true">
                        <span class="fa fa-spinner fa-spin text-white"></span>
                        <?php echo Text::_('INSTL_PROGRESS_STEP_POPULATE2'); ?>
                    </li>
                    <li id="progresspopulate3" aria-hidden="true">
                        <span class="fa fa-spinner fa-spin text-white"></span>
                        <?php echo Text::_('INSTL_PROGRESS_STEP_POPULATE3'); ?>
                    </li>
                    <?php if (is_file('sql/mysql/localise.sql')) : ?>
                        <li id="progresscustom1" aria-hidden="true">
                            <span class="fa fa-spinner fa-spin text-white"></span>
                            <?php echo Text::_('INSTL_PROGRESS_STEP_CUSTOM1'); ?>
                        </li>
                    <?php endif; ?>
                    <?php if (is_file('sql/mysql/custom.sql')) : ?>
                        <li id="progresscustom2" aria-hidden="true">
                            <span class="fa fa-spinner fa-spin text-white"></span>
                            <?php echo Text::_('INSTL_PROGRESS_STEP_CUSTOM2'); ?>
                        </li>
                    <?php endif; ?>
                    <li id="progressconfig" aria-hidden="true">
                        <span class="fa fa-spinner fa-spin text-white"></span>
                        <?php echo Text::_('INSTL_PROGRESS_STEP_CONFIG'); ?>
                    </li>
                </ul>
            </div>
        </fieldset>
        <input type="hidden" name="admin_password2" id="jform_admin_password2">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>

</div>
