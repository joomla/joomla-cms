<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_languages
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

$expired = ($this->state->get('cache_expired') == 1 ) ? '1' : '';

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('form.validate')
    ->usePreset('com_languages.overrider')
    ->useScript('com_languages.admin-override-edit-refresh-searchstring');

?>

<form action="<?php echo Route::_('index.php?option=com_languages&id=' . $this->item->key); ?>" method="post" name="adminForm" id="override-form" aria-label="<?php echo Text::_('COM_LANGUAGES_VIEW_OVERRIDE_FORM_' . ((int) $this->item->key === 0 ? 'NEW' : 'EDIT'), true); ?>" class="main-card form-validate p-4 mt-4">
    <div class="row">
        <div class="col-md-6">
            <fieldset id="fieldset-override" class="options-form">
                <legend><?php echo empty($this->item->key) ? Text::_('COM_LANGUAGES_VIEW_OVERRIDE_EDIT_NEW_OVERRIDE_LEGEND') : Text::_('COM_LANGUAGES_VIEW_OVERRIDE_EDIT_EDIT_OVERRIDE_LEGEND'); ?></legend>
                <div>
                <?php echo $this->form->renderField('language'); ?>
                <?php echo $this->form->renderField('client'); ?>
                <?php echo $this->form->renderField('key'); ?>
                <?php echo $this->form->renderField('override'); ?>

                <?php if ($this->state->get('filter.client') == 'administrator') : ?>
                    <?php echo $this->form->renderField('both'); ?>
                <?php endif; ?>

                <?php echo $this->form->renderField('file'); ?>
                </div>
            </fieldset>
        </div>

        <div class="col-md-6">
            <fieldset id="fieldset-override-search" class="options-form">
                <legend><?php echo Text::_('COM_LANGUAGES_VIEW_OVERRIDE_SEARCH_LEGEND'); ?></legend>
                <div>
                <div class="alert alert-info">
                    <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                    <?php echo Text::_('COM_LANGUAGES_VIEW_OVERRIDE_SEARCH_TIP'); ?>
                </div>
                <?php echo $this->form->renderField('searchtype'); ?>
                <div class="control-group">
                    <div class="control-label">
                        <?php echo $this->form->getLabel('searchstring'); ?>
                    </div>
                    <div class="controls">
                        <div class="input-group">
                            <?php echo $this->form->getInput('searchstring'); ?>
                            <button type="submit" class="btn btn-primary" onclick="Joomla.overrider.searchStrings();return false;" formnovalidate>
                                <?php echo Text::_('COM_LANGUAGES_VIEW_OVERRIDE_SEARCH_BUTTON'); ?>
                            </button>
                            <span id="refresh-status" class="form-text">
                                <span class="icon-sync icon-spin" aria-hidden="true"></span>
                                <?php echo Text::_('COM_LANGUAGES_VIEW_OVERRIDE_REFRESHING'); ?>
                            </span>
                        </div>
                    </div>
                </div>
                </div>
            </fieldset>

            <fieldset id="results-container" class="adminform">
                <legend><?php echo Text::_('COM_LANGUAGES_VIEW_OVERRIDE_RESULTS_LEGEND'); ?></legend>
                <div id="overrider-spinner" class="overrider-spinner text-center" data-search-string-expired="<?php echo $expired; ?>"><span class="icon-spinner icon-spin" aria-hidden="true"></span></div>
                <span id="more-results" class="mt-2">
                    <button type="button" id="more-results-button" class="btn btn-secondary" disabled>
                        <span id="overrider-spinner-btn" class="overrider-spinner-btn icon-spinner icon-spin" aria-hidden="true"></span>
                        <?php echo Text::_('COM_LANGUAGES_VIEW_OVERRIDE_MORE_RESULTS'); ?>
                    </button>
                </span>
            </fieldset>

            <input type="hidden" name="task" value="">
            <input type="hidden" name="id" value="<?php echo $this->item->key; ?>">

            <?php echo HTMLHelper::_('form.token'); ?>
        </div>
    </div>
</form>
