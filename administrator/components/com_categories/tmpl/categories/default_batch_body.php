<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

/** @var \Joomla\Component\Categories\Administrator\View\Categories\HtmlView $this */

$published = (int) $this->state->get('filter.published');
$extension = $this->escape($this->state->get('filter.extension'));

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
        <?php if ($published >= 0) : ?>
            <div class="form-group col-md-6">
                <div class="controls">
                    <?php echo LayoutHelper::render('joomla.html.batch.item', ['extension' => $extension, 'addRoot' => true]); ?>
                </div>
            </div>
        <?php endif; ?>
        <div class="form-group col-md-6">
            <div class="controls">
                <?php echo LayoutHelper::render('joomla.html.batch.tag', []); ?>
            </div>
        </div>
    </div>
    <?php if ($extension === 'com_content') : ?>
    <div class="row">
        <div class="form-group col-md-6">
            <div class="controls">
                <label id="flip-ordering-id-lbl" for="flip-ordering-id" class="control-label">
                    <?php echo Text::_('JLIB_HTML_BATCH_FLIPORDERING_LABEL'); ?>
                </label>
                <fieldset id="flip-ordering-id">
                    <?php echo HTMLHelper::_('select.booleanlist', 'batch[flip_ordering]', [], 0, 'JYES', 'JNO', 'flip-ordering-id'); ?>
                </fieldset>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<div class="btn-toolbar p-3">
    <joomla-toolbar-button task="category.batch" class="ms-auto">
        <button type="button" class="btn btn-success"><?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?></button>
    </joomla-toolbar-button>
</div>
