<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

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
?>
<label id="batch-choose-action-lbl" for="batch-choose-action"><?php echo Text::_('JLIB_HTML_BATCH_MENU_LABEL'); ?></label>
<div id="batch-choose-action" class="control-group">
	<select name="batch[category_id]" class="custom-select" id="batch-category-id">
		<option value=""><?php echo Text::_('JLIB_HTML_BATCH_NO_CATEGORY'); ?></option>
		<?php echo HTMLHelper::_('select.options', HTMLHelper::_('category.options', $extension)); ?>
	</select>
</div>
<div id="batch-copy-move" class="control-group radio">
	<?php echo Text::_('JLIB_HTML_BATCH_MOVE_QUESTION'); ?>
	<?php echo HTMLHelper::_('select.radiolist', $options, 'batch[move_copy]', '', 'value', 'text', 'm'); ?>
</div>
