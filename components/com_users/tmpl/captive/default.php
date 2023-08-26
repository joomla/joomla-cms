<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Users\Site\Model\CaptiveModel;
use Joomla\Component\Users\Site\View\Captive\HtmlView;
use Joomla\Utilities\ArrayHelper;

/**
 * @var HtmlView     $this  View object
 * @var CaptiveModel $model The model
 */
$model = $this->getModel();

$this->document->getWebAssetManager()
    ->useScript('com_users.two-factor-focus');

?>
<div class="users-mfa-captive card card-body">
    <h2 id="users-mfa-title">
        <?php if (!empty($this->renderOptions['help_url'])) : ?>
            <span class="float-end">
        <a href="<?php echo $this->renderOptions['help_url'] ?>"
                class="btn btn-sm btn-secondary"
                target="_blank"
        >
            <span class="icon icon-question-sign" aria-hidden="true"></span>
            <span class="visually-hidden"><?php echo Text::_('JHELP') ?></span>
        </a>
        </span>
        <?php endif;?>
        <?php if (!empty($this->title)) : ?>
            <?php echo $this->title ?> <small> &ndash;
        <?php endif; ?>
        <?php if (!$this->allowEntryBatching) : ?>
            <?php echo $this->escape($this->record->title) ?>
        <?php else : ?>
            <?php echo $this->escape($this->getModel()->translateMethodName($this->record->method)) ?>
        <?php endif; ?>
        <?php if (!empty($this->title)) : ?>
        </small>
        <?php endif; ?>
    </h2>

    <?php if ($this->renderOptions['pre_message']) : ?>
        <div class="users-mfa-captive-pre-message text-muted mb-3">
            <?php echo $this->renderOptions['pre_message'] ?>
        </div>
    <?php endif; ?>

    <form action="<?php echo Route::_('index.php?option=com_users&task=captive.validate&record_id=' . ((int) $this->record->id)) ?>"
            id="users-mfa-captive-form"
            method="post"
            class="form-horizontal"
    >
        <?php echo HTMLHelper::_('form.token') ?>

        <div id="users-mfa-captive-form-method-fields">
            <?php if ($this->renderOptions['field_type'] == 'custom') : ?>
                <?php echo $this->renderOptions['html']; ?>
            <?php endif; ?>
            <div class="row mb-3">
                <?php if ($this->renderOptions['label']) : ?>
                <label for="users-mfa-code" class="col-sm-3 col-form-label">
                    <?php echo $this->renderOptions['label'] ?>
                </label>
                <?php endif; ?>
                <div class="col-sm-9 <?php echo $this->renderOptions['label'] ? '' : 'offset-sm-3' ?>">
                    <?php
                    $attributes = array_merge(
                        [
                            'type'        => $this->renderOptions['input_type'],
                            'name'        => 'code',
                            'value'       => '',
                            'placeholder' => $this->renderOptions['placeholder'] ?? null,
                            'id'          => 'users-mfa-code',
                            'class'       => 'form-control'
                        ],
                        $this->renderOptions['input_attributes']
                    );

                    if (strpos($attributes['class'], 'form-control') === false) {
                        $attributes['class'] .= ' form-control';
                    }
                    ?>
                    <input <?php echo ArrayHelper::toString($attributes) ?>>
                </div>
            </div>
        </div>

        <div id="users-mfa-captive-form-standard-buttons" class="row my-3">
            <div class="col-sm-9 offset-sm-3">
                <button class="btn btn-primary me-3 <?php echo $this->renderOptions['submit_class'] ?>"
                        id="users-mfa-captive-button-submit"
                        style="<?php echo $this->renderOptions['hide_submit'] ? 'display: none' : '' ?>"
                        type="submit">
                    <span class="<?php echo $this->renderOptions['submit_icon'] ?>" aria-hidden="true"></span>
                    <?php echo Text::_($this->renderOptions['submit_text']); ?>
                </button>

                <a href="<?php echo Route::_('index.php?option=com_users&task=user.logout&' . Factory::getApplication()->getFormToken() . '=1') ?>"
                   class="btn btn-danger btn-sm" id="users-mfa-captive-button-logout">
                    <span class="icon icon-lock" aria-hidden="true"></span>
                    <?php echo Text::_('COM_USERS_MFA_LOGOUT'); ?>
                </a>

                <?php if (count($this->records) > 1) : ?>
                    <div id="users-mfa-captive-form-choose-another" class="my-3">
                        <a href="<?php echo Route::_('index.php?option=com_users&view=captive&task=select') ?>">
                            <?php echo Text::_('COM_USERS_MFA_USE_DIFFERENT_METHOD'); ?>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </form>

    <?php if ($this->renderOptions['post_message']) : ?>
        <div class="users-mfa-captive-post-message">
            <?php echo $this->renderOptions['post_message'] ?>
        </div>
    <?php endif; ?>

</div>
