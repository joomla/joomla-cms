<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = $displayData;

// Load the form list fields
$list = $data['view']->filterForm->getGroup('list');

// Currently, only added in articles, so control if component is com_content
$component =  JFactory::getApplication()->input->get('option', '');

// Get Current URL
$thisURL = JURI::getInstance()->toString();
?>
<?php if ($list) : ?>
	<div class="ordering-select hidden-phone">
		<?php foreach ($list as $fieldName => $field) : ?>
			<div class="js-stools-field-list">
				<?php echo $field->input; ?>
				<?php if ($fieldName == 'list_fullordering' AND $component == 'com_content') : ?>
					<a class="hasTooltip" href="<?php echo JRoute::_($thisURL.'&setdefault=list_defaultordering') ?>" data-original-title="Clear" data-toggle="tooltip" title="<?php echo JText::_('JGLOBAL_DEFAULT_LIST_FULLORDERING_DESC') ?>"
					>
						<span class="icon-pin"></span>
					</a>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
