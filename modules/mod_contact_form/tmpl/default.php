<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_contact_form
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

if (empty($render)) {
    return;
}

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();

$wa->registerAndUseScript(
    'mod_contact_form',
    'mod_contact_form/mod-contact-form.min.js',
    [],
    ['defer' => true],
    ['core', 'form.validate']
);
$wa->registerAndUseStyle(
    'mod_contact_form',
    'mod_contact_form/mod-contact-form.css'
);

Text::script('MOD_CONTACT_FORM_SEND');
Text::script('MOD_CONTACT_FORM_SENDING');
Text::script('MOD_CONTACT_FORM_MESSAGE_NETWORK_ERROR');
Text::script('MOD_CONTACT_FORM_MESSAGE_SERVER_ERROR');
Text::script('MOD_CONTACT_FORM_MESSAGE_VALIDATION_FAILED');
Text::script('JINVALID_TOKEN');

$formId      = 'contact-form-mod-' . $module->id;
$resultId    = 'contact-form-result-' . $module->id;
$ajaxUrl     = Route::_('index.php?option=com_ajax&module=contact_form&method=submit&format=json');
$redirectUrl = htmlspecialchars((string) $params->get('redirect_on_success', ''), ENT_QUOTES, 'UTF-8');
$successMsg  = htmlspecialchars(trim((string) $params->get('success_message', '')), ENT_QUOTES, 'UTF-8');
$sendLabel   = htmlspecialchars(Text::_('MOD_CONTACT_FORM_SEND'), ENT_QUOTES, 'UTF-8');

if ($successMsg === '') {
    $successMsg = htmlspecialchars(Text::_('MOD_CONTACT_FORM_MESSAGE_SUCCESS_DEFAULT'), ENT_QUOTES, 'UTF-8');
}
?>
<div class="mod-contact-form<?php echo htmlspecialchars((string) $params->get('moduleclass_sfx', ''), ENT_QUOTES, 'UTF-8'); ?>">
    <?php if ((int) $params->get('show_contact_info', 0)) : ?>
        <div class="mod-contact-form__contact-info" aria-label="<?php echo htmlspecialchars(Text::_('MOD_CONTACT_FORM_CONTACT_INFO_HEADING'), ENT_QUOTES, 'UTF-8'); ?>">
            <h3 class="mod-contact-form__contact-name">
                <?php echo htmlspecialchars($contact->name, ENT_QUOTES, 'UTF-8'); ?>
            </h3>
            <?php if ($contact->params->get('show_email', 0) && $contact->email_to) : ?>
                <p class="text-body-secondary">
                    <?php echo htmlspecialchars($contact->email_to, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form
        id="<?php echo $formId; ?>"
        action="<?php echo $ajaxUrl; ?>"
        method="post"
        class="mod-contact-form__form form-validate"
        data-mod-contact-form="1"
        data-instance-id="<?php echo $module->id; ?>"
        data-result-target="<?php echo $resultId; ?>"
        data-success-message="<?php echo $successMsg; ?>"
        data-redirect="<?php echo $redirectUrl; ?>"
        data-ajax-url="<?php echo $ajaxUrl; ?>"
        data-contact-id="<?php echo (int) $contact->id; ?>"
    >
        <?php
        foreach ($form->getFieldsets() as $fieldset) : ?>
            <?php if ($fieldset->name === 'captcha') : ?>
                <?php continue; ?>
            <?php endif; ?>
            <?php $fields = $form->getFieldset($fieldset->name); ?>
            <?php if (\count($fields)) : ?>
                <fieldset class="mod-contact-form__fieldset">
                    <?php if (isset($fieldset->label) && ($legend = trim(Text::_($fieldset->label))) !== '') : ?>
                        <legend><?php echo htmlspecialchars($legend, ENT_QUOTES, 'UTF-8'); ?></legend>
                    <?php endif; ?>
                    <?php foreach ($fields as $field) : ?>
                        <?php echo $field->renderField(); ?>
                    <?php endforeach; ?>
                </fieldset>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($captchaEnabled) : ?>
            <?php echo $form->renderFieldset('captcha'); ?>
        <?php endif; ?>

        <div class="mod-contact-form__submit">
            <button
                type="submit"
                class="btn btn-primary mod-contact-form__btn-submit"
                data-submit-label="<?php echo $sendLabel; ?>"
            >
                <?php echo $sendLabel; ?>
            </button>
        </div>

        <input type="hidden" name="option" value="com_ajax">
        <input type="hidden" name="module" value="contact_form">
        <input type="hidden" name="method" value="submit">
        <input type="hidden" name="format" value="json">
        <input type="hidden" name="module_id" value="<?php echo $module->id; ?>">
        <input type="hidden" name="contact_id" value="<?php echo (int) $contact->id; ?>">
        <?php echo HTMLHelper::_('form.token'); ?>
    </form>

    <div
        id="<?php echo $resultId; ?>"
        class="mod-contact-form__result"
        role="alert"
        aria-live="polite"
        aria-atomic="true"
    ></div>
</div>
