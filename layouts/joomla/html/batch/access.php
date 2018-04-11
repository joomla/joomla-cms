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
 * none
 */

?>
<label id="batch-access-lbl" for="batch-access" class="modalTooltip" title="<?php echo HTMLHelper::_('tooltipText', 'JLIB_HTML_BATCH_ACCESS_LABEL', 'JLIB_HTML_BATCH_ACCESS_LABEL_DESC'); ?>">
	<?php echo Text::_('JLIB_HTML_BATCH_ACCESS_LABEL'); ?></label>
	<?php echo HTMLHelper::_(
		'access.assetgrouplist',
		'batch[assetgroup_id]', '',
		'class="custom-select"',
		array(
			'title' => Text::_('JLIB_HTML_BATCH_NOCHANGE'),
			'id'    => 'batch-access'
		)
	); ?>
