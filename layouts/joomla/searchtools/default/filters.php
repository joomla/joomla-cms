<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$data = $displayData;

// Check for show on fields.
$filters = $data['view']->filterForm->getGroup('filter');
foreach ($filters as $field)
{
	if ($showonstring = $field->getAttribute('showon'))
	{
		$showonarr = array();
		foreach (preg_split('%\[AND\]|\[OR\]%', $showonstring) as $showonfield)
		{
			$showon   = explode(':', $showonfield, 2);
			$showonarr[] = array(
				'field'  => $showon[0],
				'values' => explode(',', $showon[1]),
				'op'     => preg_match('%\[(AND|OR)\]' . $showonfield . '%', $showonstring, $matches) ? $matches[1] : ''
			);
		}
		$data['view']->filterForm->setFieldAttribute($field->fieldname, 'dataShowOn', json_encode($showonarr), $field->group);
	}
}

// Load the form filters
$filters = $data['view']->filterForm->getGroup('filter');
?>
<?php if ($filters) : ?>
	<?php foreach ($filters as $fieldName => $field) : ?>
		<?php if ($fieldName !== 'filter_search') : ?>
			<?php
			$showOn = '';
			if ($showOnData = $field->getAttribute('dataShowOn'))
			{
				JHtml::_('jquery.framework');
				JHtml::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));
				$showOn = " data-showon='" . $showOnData . "'";
			}
			?>
			<div class="js-stools-field-filter"<?php echo $showOn; ?>>
				<?php echo $field->input; ?>
			</div>
		<?php endif; ?>
	<?php endforeach; ?>
<?php endif; ?>
