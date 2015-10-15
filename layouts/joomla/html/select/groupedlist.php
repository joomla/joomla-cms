<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * ---------------------
 *
 * @var  string  $baseIndent  The base indentation
 * @var  string  $id          The tag id
 * @var  string  $name        The tag name
 * @var  string  $options     The tag options
 * @var  array   $data        The tag data
 * @var  string  $attribs     The tag attribute
 */

extract($displayData);

$baseIndent = str_repeat($options['format.indent'], $options['format.depth']++);
?>
<?php echo $baseIndent; ?><select<?php echo ($id !== '' ? ' id="' . $id . '"' : '');?> name="<?php echo $name; ?>"<?php echo $attribs; ?>><?php echo $options['format.eol']; ?>
	<?php $groupIndent = str_repeat($options['format.indent'], $options['format.depth']++); ?>

<?php
	foreach ($data as $dataKey => $group)
	{
		$label = $dataKey;
		$id = '';
		$noGroup = is_int($dataKey);

		if ($options['group.items'] == null)
		{
			// Sub-list is an associative array
			$subList = $group;
		}
		elseif (is_array($group))
		{
			// Sub-list is in an element of an array.
			$subList = $group[$options['group.items']];

			if (isset($group[$options['group.label']]))
			{
				$label = $group[$options['group.label']];
				$noGroup = false;
			}

			if (isset($options['group.id']) && isset($group[$options['group.id']]))
			{
				$id = $group[$options['group.id']];
				$noGroup = false;
			}
		}
		elseif (is_object($group))
		{
			// Sub-list is in a property of an object
			$subList = $group->$options['group.items'];

			if (isset($group->$options['group.label']))
			{
				$label = $group->$options['group.label'];
				$noGroup = false;
			}

			if (isset($options['group.id']) && isset($group->$options['group.id']))
			{
				$id = $group->$options['group.id'];
				$noGroup = false;
			}
		}
		else
		{
			throw new RuntimeException('Invalid group contents.', 1);
		}

		if ($noGroup)
		{
			echo JHtml::_('select.options', $subList, $options);
		}
		else
		{
			echo $groupIndent
				. '<optgroup'
				. (empty($id) ? '' : ' id="' . $id . '"')
				. ' label="'
				. ($options['group.label.toHtml'] ? htmlspecialchars($label, ENT_COMPAT, 'UTF-8') : $label)
				. '">'
				. $options['format.eol']
				. JHtml::_('select.options', $subList, $options)
				. $groupIndent
				. '</optgroup>'
				. $options['format.eol'];
		}
	}
?>
<?php echo $baseIndent; ?></select><?php echo $options['format.eol']; ?>
