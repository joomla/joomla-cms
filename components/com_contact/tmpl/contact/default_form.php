<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2006 Open Source Matters, Inc.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Fields\Administrator\Helper\FieldsHelper;

/** @var \Joomla\Component\Contact\Site\View\Contact\HtmlView $this */
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */

$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
   ->useScript('form.validate');

$fieldsAfterTitle = [];
$fieldsBeforeContent = [];
$fieldsAfterContent = [];

if (!empty($this->item->jcfields)) {
    foreach ($this->item->jcfields as $field) {
        $display = $field->params->get('display');

        switch ($display) {
            case 'after_title':
                $fieldsAfterTitle[] = $field;
                break;

            case 'before_display_content':
                $fieldsBeforeContent[] = $field;
                break;

            case 'after_display_content':
                $fieldsAfterContent[] = $field;
                break;
        }
    }
}
?>

<div class="com-contact__form contact-form">
    <form id="contact-form"
        action="<?php echo Route::_('index.php'); ?>"
        method="post"
        class="form-validate form-horizontal well">

        <?php if ($fieldsAfterTitle) : ?>
            <?php echo FieldsHelper::render(
                'com_contact.contact',
                'fields.render',
                ['fields' => $fieldsAfterTitle]
            ); ?>
        <?php endif; ?>

        <?php foreach ($this->form->getFieldsets() as $fieldset) : ?>
            <?php if ($fieldset->name === 'captcha' && $this->captchaEnabled) : ?>
                <?php continue; ?>
            <?php endif; ?>

            <?php $fields = $this->form->getFieldset($fieldset->name); ?>

            <?php if (count($fields)) : ?>
                <fieldset class="m-0">
                    <?php if (!empty($fieldset->label)) : ?>
                        <legend><?php echo Text::_($fieldset->label); ?></legend>
                    <?php endif; ?>

                    <?php foreach ($fields as $field) : ?>
                        <?php echo $field->renderField(); ?>
                    <?php endforeach; ?>
                </fieldset>
            <?php endif; ?>
        <?php endforeach; ?>

        <?php if ($fieldsBeforeContent) : ?>
            <?php echo FieldsHelper::render(
                'com_contact.contact',
                'fields.render',
                ['fields' => $fieldsBeforeContent]
            ); ?>
        <?php endif; ?>

        <?php if ($this->captchaEnabled) : ?>
            <?php echo $this->form->renderFieldset('captcha'); ?>
        <?php endif; ?>

        <?php if ($fieldsAfterContent) : ?>
            <?php echo FieldsHelper::render(
                'com_contact.contact',
                'fields.render',
                ['fields' => $fieldsAfterContent]
            ); ?>
        <?php endif; ?>

        <div class="control-group">
            <div class="controls">
                <button class="btn btn-primary validate" type="submit">
                    <?php echo Text::_('COM_CONTACT_CONTACT_SEND'); ?>
                </button>

                <input type="hidden" name="option" value="com_contact">
                <input type="hidden" name="task" value="contact.submit">
                <input type="hidden" name="return" value="<?php echo $this->return_page; ?>">
                <input type="hidden" name="id" value="<?php echo $this->item->slug; ?>">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </form>
</div>
