<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_mails
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Component\Mails\Administrator\Helper\MailsHelper;

/** @var \Joomla\Component\Mails\Administrator\View\Template\HtmlView $this */

$app = Factory::getApplication();

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->useScript('com_mails.admin-email-template-edit');

$this->useCoreUI = true;

$input = $app->getInput();
list($component, $sub_id) = explode('.', $this->master->template_id, 2);
$sub_id = str_replace('.', '_', $sub_id);

$this->getDocument()->addScriptOptions('com_mails', ['templateData' => $this->templateData]);

?>

<form action="<?php echo Route::_('index.php?option=com_mails&layout=edit&template_id=' . $this->item->template_id . '&language=' . $this->item->language); ?>" method="post" name="adminForm" id="item-form" aria-label="<?php echo Text::_('COM_MAILS_FORM_EDIT'); ?>" class="form-validate">
    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', Text::_('COM_MAILS_MAIL_CONTENT')); ?>
        <div class="row">
            <div class="col-md-12">
                <h1><?php echo Text::_($component . '_MAIL_' . $sub_id . '_TITLE'); ?> - <?php echo $this->escape($this->item->language); ?>
                </h1>
                <div class="small mb-1">
                    <span class="badge bg-secondary"><?php echo $this->escape($this->master->template_id); ?></span>
                </div>
                <p><?php echo Text::_($component . '_MAIL_' . $sub_id . '_DESC'); ?></p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-9">
                <?php echo $this->form->renderField('subject'); ?>
            </div>
            <div class="col-md-3">
                <button type="button" id="btnResetSubject" class="btn btn-secondary">
                    <?php echo Text::_('COM_MAILS_RESET_TO_DEFAULT_SUBJECT'); ?>
                </button>
            </div>
        </div>
        <?php if ($fieldBody = $this->form->getField('body')) : ?>
        <div class="row">
            <div class="col-md-9">
                <?php echo $this->form->renderField('body'); ?>
            </div>
            <div class="col-md-3">
                <button type="button" id="btnResetBody" class="btn btn-secondary">
                    <?php echo Text::_('COM_MAILS_RESET_TO_DEFAULT_BODY'); ?>
                </button>
                <div class="tags-container-body mt-3 <?php echo $fieldBody->disabled ? 'hidden' : ''; ?>">
                    <h2><?php echo Text::_('COM_MAILS_FIELDSET_TAGS_LABEL'); ?></h2>
                    <?php echo MailsHelper::mailtags($this->master, 'body'); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($fieldHtmlBody = $this->form->getField('htmlbody')) : ?>
        <div class="row">
            <div class="col-md-9">
                <?php echo $this->form->renderField('htmlbody'); ?>
            </div>
            <div class="col-md-3">
                <button type="button" id="btnResetHtmlBody" class="btn btn-secondary">
                    <?php echo Text::_('COM_MAILS_RESET_TO_DEFAULT_HTML_BODY'); ?>
                </button>
                <div class="tags-container-htmlbody mt-3 <?php echo $fieldHtmlBody->disabled ? 'hidden' : ''; ?>">
                    <h2><?php echo Text::_('COM_MAILS_FIELDSET_TAGS_LABEL'); ?></h2>
                    <?php echo MailsHelper::mailtags($this->master, 'htmlbody'); ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($this->form->getField('attachments')) : ?>
        <div class="row">
            <div class="col-md-9">
                <?php echo $this->form->renderField('attachments'); ?>
            </div>
        </div>
        <?php endif; ?>

        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php if (count($this->form->getFieldset('basic'))) : ?>
            <?php echo LayoutHelper::render('joomla.edit.params', $this); ?>
        <?php endif; ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>
    <?php echo $this->form->renderField('template_id'); ?>
    <?php echo $this->form->renderField('language'); ?>
    <input type="hidden" name="task" value="">
    <input type="hidden" name="return" value="<?php echo $input->get('return', null, 'BASE64'); ?>">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
