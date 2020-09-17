<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die();

use Joomla\CMS\Form\FormField;

if (class_exists('JFormFieldBackupprofiles'))
{
	return;
}

/**
 * Our main element class, creating a multi-select list out of an SQL statement
 */
class JFormFieldBackupprofiles extends FormField
{
	/**
	 * Element name
	 *
	 * @var        string
	 */
	protected $name = 'Backupprofiles';

	function getInput()
	{
		$db = \Joomla\CMS\Factory::getDBO();

		$query = $db->getQuery(true)
			->select([
				$db->qn('id'),
				$db->qn('description'),
			])->from($db->qn('#__ak_profiles'));
		$db->setQuery($query);
		$key = 'id';
		$val = 'description';

		$objectList = $db->loadObjectList();

		if (!is_array($objectList))
		{
			$objectList = [];
		}

		foreach ($objectList as $o)
		{
			$o->description = "#{$o->id}: {$o->description}";
		}

		$showNone = $this->element['show_none'] ? (string) $this->element['show_none'] : '';
		$showNone = in_array(strtolower($showNone), ['yes', '1', 'true', 'on']);

		if ($showNone)
		{
			$defaultItem = (object) [
				'id'          => '0',
				'description' => \Joomla\CMS\Language\Text::_('COM_AKEEBA_FORMFIELD_BACKUPPROFILES_NONE'),
			];

			array_unshift($objectList, $defaultItem);
		}

		return \Joomla\CMS\HTML\HTMLHelper::_('select.genericlist', $objectList, $this->name, 'class="inputbox"', $key, $val, $this->value, $this->id);
	}
}
