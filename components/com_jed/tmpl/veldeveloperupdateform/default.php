<?php

/**
 * @package       JED
 *
 * @subpackage    VEL
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Jed\Component\Jed\Site\Helper\JedHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$wa = $this->document->getWebAssetManager();
$wa->getRegistry()->addExtensionRegistryFile('com_jed');
$wa->useScript('keepalive')
    ->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');

// Load admin language file
$lang = Factory::getLanguage();
$lang->load('com_jed', JPATH_SITE);
// Import CSS
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useStyle('com_jed.form');

$user    = JedHelper::getUser();
$canEdit = JedHelper::canUserEdit($this->item);

$isLoggedIn  = JedHelper::IsLoggedIn();
$redirectURL = JedHelper::getLoginlink();

if (!$isLoggedIn) {
    try {
        $app = JFactory::getApplication();
    } catch (Exception $e) {
    }

    $app->enqueueMessage(Text::_('COM_JED_VEL_DEVELOPERUPDATES_NO_ACCESS'), 'success');
    $app->redirect($redirectURL);
} else {
    ?>

    <div class="veldeveloperupdate-edit front-end-edit">
        <?php if (!$canEdit) : ?>
            <h3>
                <?php throw new Exception(Text::_('COM_JED_GENERAL_ERROR_MESSAGE_NOT_AUTHORISED'), 403); ?>
            </h3>
        <?php else : ?>
            <form id="form-veldeveloperupdate"
                  action="<?php echo Route::_('index.php?option=com_jed&task=veldeveloperupdateform.save'); ?>"
                  method="post" class="form-validate form-horizontal" enctype="multipart/form-data">
                <?php
                $fieldsets['overview']['title']       = Text::_('COM_JED_VEL_DEVELOPERUPDATES_FORM_OVERVIEW_TITLE');
                $fieldsets['overview']['description'] = Text::_('COM_JED_VEL_DEVELOPERUPDATES_FORM_OVERVIEW_DESCR');
                $fieldsets['overview']['fields'] = array();

                $fieldsets['aboutyou']['title']       = Text::_('COM_JED_VEL_GENERAL_FIELD_ABOUT_YOU_LABEL');
                $fieldsets['aboutyou']['description'] = "";
                $fieldsets['aboutyou']['fields']      = array(
                    'contact_fullname',
                    'contact_organisation',
                    'contact_email');

                $fieldsets['vulnerabilitydetails']['title']       = Text::_('COM_JED_VEL_DEVELOPERUPDATES_FORM_VULNERABILITY_DETAILS_TITLE');
                $fieldsets['vulnerabilitydetails']['description'] = "";
                $fieldsets['vulnerabilitydetails']['fields']      = array(
                    'vulnerable_item_name',
                    'vulnerable_item_version',
                    'extension_update',
                    'new_version_number');

                $fieldsets['vulnerabilitydetails2']['title']       = "";
                $fieldsets['vulnerabilitydetails2']['description'] = Text::_('COM_JED_VEL_DEVELOPERUPDATES_FORM_VULNERABILITY_DETAILS_2_DESCR');
                $fieldsets['vulnerabilitydetails2']['fields']      = array(
                    'update_notice_url',
                    'changelog_url');

                $fieldsets['vulnerabilitydetails3']['title']       = "";
                $fieldsets['vulnerabilitydetails3']['description'] = Text::_('COM_JED_VEL_DEVELOPERUPDATES_FORM_VULNERABILITY_DETAILS_3_DESCR');
                $fieldsets['vulnerabilitydetails3']['fields']      = array(
                    'download_url',
                    'consent_to_process');


                $fieldsets['final']['title']       = "";
                $fieldsets['final']['description'] = Text::_('COM_JED_VEL_DEVELOPERUPDATES_FINAL_DESCRIPTION');

                $fieldsets['final']['fields'] = array('captcha');
                $fscount                      = 0;


                foreach ($fieldsets as $fs) {
                    $fscount = $fscount + 1;
                    if ($fs['title'] <> '') {
                        if ($fscount > 1) {
                            echo '</fieldset>';
                        }

                        echo '<fieldset class="veldeveloperupdateform"><legend>' . $fs['title'] . '</legend>';
                    }
                    if ($fs['description'] <> '') {
                        echo $fs['description'];
                    }

                    $fields = $fs['fields'];



                    foreach ($fields as $field) {
                        echo $this->form->renderField($field, null, null, array('class' => 'control-wrapper-' . $field));
                    }
                }

                $hiddenfields = array('vel_item_id',
                    'update_data_source',
                    'update_date_submitted',
                    'data_source',
                    'update_user_ip');


                foreach ($hiddenfields as $field) {
                    $this->form->setFieldAttribute($field, 'type', 'hidden');
                }
                ?>

                <div class="control-group">
                    <div class="controls">

                        <?php if ($this->canSave) : ?>
                            <button type="submit" class="validate btn btn-primary">
                                <span class="fas fa-check" aria-hidden="true"></span>
                                <?php echo Text::_('JSUBMIT'); ?>
                            </button>
                        <?php endif; ?>
                        <a class="btn btn-danger"
                           href="<?php echo Route::_('index.php?option=com_jed&task=veldeveloperupdateform.cancel'); ?>"
                           title="<?php echo Text::_('JCANCEL'); ?>">
                            <span class="fas fa-times" aria-hidden="true"></span>
                            <?php echo Text::_('JCANCEL'); ?>
                        </a>
                    </div>
                </div>

                <input type="hidden" name="option" value="com_jed"/>
                <input type="hidden" name="task"
                       value="veldeveloperupdateform.save"/>
                <?php echo HTMLHelper::_('form.token'); ?>
            </form>
        <?php endif; ?>
    </div>
    <?php
}
?>
