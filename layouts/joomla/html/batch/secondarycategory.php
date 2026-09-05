<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var string $extension The extension name
 */

// Create the add/remove secondary category options.
$options = [
    HTMLHelper::_('select.option', 'a', Text::_('JLIB_HTML_BATCH_ADDITIONAL_CATEGORY_ADD')),
    HTMLHelper::_('select.option', 'r', Text::_('JLIB_HTML_BATCH_ADDITIONAL_CATEGORY_REMOVE')),
];

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('joomla.batch-secondary-category-addremove');

// Get category options.
$categoryOptions = HTMLHelper::_('category.options', $extension, ['filter.published' => [0, 1, 2]]);

?>
<label id="batch-secondary-category-choose-action-lbl" for="batch-secondary-category-id">
    <?php echo Text::_('JLIB_HTML_BATCH_ADDITIONAL_CATEGORY_LABEL'); ?>
</label>
<div id="batch-secondary-category-choose-action" class="control-group">
    <select name="batch[secondary_category]" class="form-select" id="batch-secondary-category-id">
        <option value=""><?php echo Text::_('JLIB_HTML_BATCH_ADDITIONAL_CATEGORY_NOCHANGE'); ?></option>
        <?php echo HTMLHelper::_('select.options', $categoryOptions); ?>
    </select>
</div>
<div id="batch-secondary-category-addremove" class="control-group radio">
    <fieldset id="batch-secondary-category-addremove-id">
        <legend>
            <?php echo Text::_('JLIB_HTML_BATCH_ADDITIONAL_CATEGORY_ADDREMOVE_QUESTION'); ?>
        </legend>
        <?php echo HTMLHelper::_('select.radiolist', $options, 'batch[secondary_category_addremove]', '', 'value', 'text', 'a'); ?>
    </fieldset>
</div>
