<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

/** @var \Joomla\Component\Fields\Administrator\View\Fields\HtmlView $this */

/** @var \Joomla\Component\Fields\Administrator\Model\FieldsModel $model */
$model = $this->getModel();

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('com_fields.admin-fields-batch');
$wa->useScript('joomla.batch-copymove');

$context = $this->escape($this->state->get('filter.context'));
?>

<div class="p-3">
    <div class="row">
        <?php if (Multilanguage::isEnabled()) : ?>
            <div class="form-group col-md-6">
                <div class="controls">
                    <?php echo LayoutHelper::render('joomla.html.batch.language', []); ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="form-group col-md-6">
            <div class="controls">
                <?php echo LayoutHelper::render('joomla.html.batch.access', []); ?>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-6">
            <div class="controls">
                <?php $options = [
                    HTMLHelper::_('select.option', 'c', Text::_('JLIB_HTML_BATCH_COPY')),
                    HTMLHelper::_('select.option', 'm', Text::_('JLIB_HTML_BATCH_MOVE'))
                ];
?>
                <label id="batch-choose-action-lbl" for="batch-group-id">
                    <?php echo Text::_('COM_FIELDS_BATCH_GROUP_LABEL'); ?>
                </label>
                <div id="batch-choose-action" class="control-group">
                    <select name="batch[group_id]" class="form-select" id="batch-group-id">
                        <option value=""><?php echo Text::_('JLIB_HTML_BATCH_NO_CATEGORY'); ?></option>
                        <option value="nogroup"><?php echo Text::_('COM_FIELDS_BATCH_GROUP_OPTION_NONE'); ?></option>
                        <?php echo HTMLHelper::_('select.options', $model->getGroups(), 'value', 'text'); ?>
                    </select>
                </div>
                <div id="batch-copy-move">
                    <?php echo Text::_('JLIB_HTML_BATCH_MOVE_QUESTION'); ?>
                    <?php echo HTMLHelper::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="btn-toolbar p-3">
    <joomla-toolbar-button task="field.batch" class="ms-auto">
        <button type="button" class="btn btn-success"><?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?></button>
    </joomla-toolbar-button>
</div>
