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
 * None
 */

?>
<label id="batch-tag-lbl" for="batch-tag-id" class="modalTooltip" title="<?php
echo HTMLHelper::_('tooltipText', 'JLIB_HTML_BATCH_TAG_LABEL', 'JLIB_HTML_BATCH_TAG_LABEL_DESC'); ?>">
<?php echo Text::_('JLIB_HTML_BATCH_TAG_LABEL'); ?>
</label>
<select name="batch[tag]" class="custom-select" id="batch-tag-id">
	<option value=""><?php echo Text::_('JLIB_HTML_BATCH_TAG_NOCHANGE'); ?></option>
	<?php echo HTMLHelper::_('select.options', HTMLHelper::_('tag.tags', array('filter.published' => array(1))), 'value', 'text'); ?>
</select>
