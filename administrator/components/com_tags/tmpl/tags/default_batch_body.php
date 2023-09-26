<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_tags
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$options = [
    HTMLHelper::_('select.option', 'c', Text::_('JLIB_HTML_BATCH_COPY')),
    HTMLHelper::_('select.option', 'm', Text::_('JLIB_HTML_BATCH_MOVE'))
];

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('joomla.batch-copymove');
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
            <div id="batch-choose-action" class="control-group">
                <label id="batch-choose-action-lbl" for="batch-tag-copy-move-id">
                    <?php echo Text::_('COM_TAGS_BATCH_TAG_LABEL'); ?>
                </label>
                <select class="form-select" name="batch[tag_id]" id="batch-tag-copy-move-id">
                    <option value=""><?php echo Text::_('JLIB_HTML_BATCH_TAG_NOCHANGE'); ?></option>
                    <?php echo HTMLHelper::_('select.options', HTMLHelper::_('tag.tags')); ?>
                </select>
            </div>
            <div id="batch-copy-move">
                <?php echo Text::_('JLIB_HTML_BATCH_MOVE_QUESTION'); ?>
                <?php echo HTMLHelper::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'); ?>
            </div>
        </div>
    </div>
</div>