<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Layout variables
 * ---------------------
 *
 * @var  string   $extension The extension name
 */

extract($displayData);

// Create the copy/move options.
$options = array(
	HTMLHelper::_('select.option', 'c', Text::_('JLIB_HTML_BATCH_COPY')),
	HTMLHelper::_('select.option', 'm', Text::_('JLIB_HTML_BATCH_MOVE'))
);

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('joomla.batch-language');

?>
<label id="batch-choose-action-lbl" for="batch-category-id">
	<?php echo Text::_('JLIB_HTML_BATCH_MENU_LABEL'); ?>
</label>
<div id="batch-choose-action" class="control-group">
	<select name="batch[category_id]" class="custom-select" id="batch-category-id">
		<option value=""><?php echo Text::_('JLIB_HTML_BATCH_NO_CATEGORY'); ?></option>
		<?php if (isset($addRoot) && $addRoot) : ?>
			<?php echo HTMLHelper::_('select.options', HTMLHelper::_('category.categories', $extension)); ?>
		<?php else : ?>
			<?php echo HTMLHelper::_('select.options', HTMLHelper::_('category.options', $extension)); ?>
		<?php endif; ?>
	</select>
</div>
<div id="batch-copy-move" class="control-group radio">
	<label id="batch-copy-move-lbl" for="batch-copy-move-id" class="control-label">
		<?php echo Text::_('JLIB_HTML_BATCH_MOVE_QUESTION'); ?>
	</label>
	<fieldset id="batch-copy-move-id">
		<?php echo HTMLHelper::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'); ?>
	</fieldset>
</div>
