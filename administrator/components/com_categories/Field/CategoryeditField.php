<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Categories\Administrator\Field;

defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormHelper;
use Joomla\Utilities\ArrayHelper;

FormHelper::loadFieldClass('list');

/**
 * Category Edit field..
 *
 * @since  1.6
 */
class CategoryeditField extends \JFormFieldList
{
	/**
	 * To allow creation of new categories.
	 *
	 * @var    integer
	 * @since  3.6
	 */
	protected $allowAdd;

	/**
	 * A flexible category list that respects access controls
	 *
	 * @var    string
	 * @since  1.6
	 */
	public $type = 'CategoryEdit';

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     FormField::setup()
	 * @since   3.2
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->allowAdd = $this->element['allowAdd'] ?? '';
		}

		return $return;
	}

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.6
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'allowAdd':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.6
	 */
	public function __set($name, $value)
	{
		$value = (string) $value;

		switch ($name)
		{
			case 'allowAdd':
				$value = (string) $value;
				$this->$name = ($value === 'true' || $value === $name || $value === '1');
				break;
			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to get a list of categories that respects access controls and can be used for
	 * either category assignment or parent category assignment in edit screens.
	 * Use the parent element to indicate that the field will be used for assigning parent categories.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6
	 */
	protected function getOptions()
	{
		$options = array();
		$published = $this->element['published'] ?: array(0, 1);
		$name = (string) $this->element['name'];

		// Let's get the id for the current item, either category or content item.
		$jinput = \JFactory::getApplication()->input;

		// Load the category options for a given extension.

		// For categories the old category is the category id or 0 for new category.
		if ($this->element['parent'] || $jinput->get('option') == 'com_categories')
		{
			$oldCat = $jinput->get('id', 0);
			$oldParent = $this->form->getValue($name, 0);
			$extension = $this->element['extension'] ? (string) $this->element['extension'] : (string) $jinput->get('extension', 'com_content');
		}
		else
			// For items the old category is the category they are in when opened or 0 if new.
		{
			$oldCat = $this->form->getValue($name, 0);
			$extension = $this->element['extension'] ? (string) $this->element['extension'] : (string) $jinput->get('option', 'com_content');
		}

		// Account for case that a submitted form has a multi-value category id field (e.g. a filtering form), just use the first category
		$oldCat = is_array($oldCat)
			? (int) reset($oldCat)
			: (int) $oldCat;

		$db   = \JFactory::getDbo();
		$user = \JFactory::getUser();

		$query = $db->getQuery(true)
			->select('a.id AS value, a.title AS text, a.level, a.published, a.lft, a.language')
			->from('#__categories AS a');

		// Filter by the extension type
		if ($this->element['parent'] == true || $jinput->get('option') == 'com_categories')
		{
			$query->where('(a.extension = ' . $db->quote($extension) . ' OR a.parent_id = 0)');
		}
		else
		{
			$query->where('(a.extension = ' . $db->quote($extension) . ')');
		}

		// Filter language
		if (!empty($this->element['language']))
		{
			if (strpos($this->element['language'], ',') !== false)
			{
				$language = implode(',', $db->quote(explode(',', $this->element['language'])));
			}
			else
			{
				$language = $db->quote($this->element['language']);
			}
			$query->where($db->quoteName('a.language') . ' IN (' . $language . ')');
		}

		// Filter on the published state
		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif (is_array($published))
		{
			$query->where('a.published IN (' . implode(',', ArrayHelper::toInteger($published)) . ')');
		}

		// Filter categories on User Access Level
		// Filter by access level on categories.
		if (!$user->authorise('core.admin'))
		{
			$groups = implode(',', $user->getAuthorisedViewLevels());
			$query->where('a.access IN (' . $groups . ')');
		}

		$query->order('a.lft ASC');

		// If parent isn't explicitly stated but we are in com_categories assume we want parents
		if ($oldCat != 0 && ($this->element['parent'] == true || $jinput->get('option') == 'com_categories'))
		{
			// Prevent parenting to children of this item.
			// To rearrange parents and children move the children up, not the parents down.
			$query->join('LEFT', $db->quoteName('#__categories') . ' AS p ON p.id = ' . (int) $oldCat)
				->where('NOT(a.lft >= p.lft AND a.rgt <= p.rgt)');

			$rowQuery = $db->getQuery(true);
			$rowQuery->select('a.id AS value, a.title AS text, a.level, a.parent_id')
				->from('#__categories AS a')
				->where('a.id = ' . (int) $oldCat);
			$db->setQuery($rowQuery);
			$row = $db->loadObject();
		}

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			\JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
		}

		// Pad the option text with spaces using depth level as a multiplier.
		for ($i = 0, $n = count($options); $i < $n; $i++)
		{
			// Translate ROOT
			if ($this->element['parent'] == true || $jinput->get('option') == 'com_categories')
			{
				if ($options[$i]->level == 0)
				{
					$options[$i]->text = \JText::_('JGLOBAL_ROOT_PARENT');
				}
			}

			if ($options[$i]->published == 1)
			{
				$options[$i]->text = str_repeat('- ', !$options[$i]->level ? 0 : $options[$i]->level - 1) . $options[$i]->text;
			}
			else
			{
				$options[$i]->text = str_repeat('- ', !$options[$i]->level ? 0 : $options[$i]->level - 1) . '[' . $options[$i]->text . ']';
			}

			// Displays language code if not set to All
			if ($options[$i]->language !== '*')
			{
				$options[$i]->text = $options[$i]->text . ' (' . $options[$i]->language . ')';
			}
		}

		// For new items we want a list of categories you are allowed to create in.
		if ($oldCat == 0)
		{
			foreach ($options as $i => $option)
			{
				/*
				 * To take save or create in a category you need to have create rights for that category unless the item is already in that category.
				 * Unset the option if the user isn't authorised for it. In this field assets are always categories.
				 */
				if ($option->level != 0 && !$user->authorise('core.create', $extension . '.category.' . $option->value))
				{
					unset($options[$i]);
				}
			}
		}
		// If you have an existing category id things are more complex.
		else
		{
			/*
			 * If you are only allowed to edit in this category but not edit.state, you should not get any
			 * option to change the category parent for a category or the category for a content item,
			 * but you should be able to save in that category.
			 */
			foreach ($options as $i => $option)
			{
				$assetKey = $extension . '.category.' . $oldCat;

				if ($option->level != 0 && !isset($oldParent) && $option->value != $oldCat && !$user->authorise('core.edit.state', $assetKey))
				{
					unset($options[$i]);
					continue;
				}

				if ($option->level != 0	&& isset($oldParent) && $option->value != $oldParent && !$user->authorise('core.edit.state', $assetKey))
				{
					unset($options[$i]);
					continue;
				}

				/*
				 * However, if you can edit.state you can also move this to another category for which you have
				 * create permission and you should also still be able to save in the current category.
				 */
				$assetKey = $extension . '.category.' . $option->value;

				if ($option->level != 0 && !isset($oldParent) && $option->value != $oldCat && !$user->authorise('core.create', $assetKey))
				{
					unset($options[$i]);
					continue;
				}

				if ($option->level != 0	&& isset($oldParent) && $option->value != $oldParent && !$user->authorise('core.create', $assetKey))
				{
					unset($options[$i]);
					continue;
				}
			}
		}

		if (($this->element['parent'] == true || $jinput->get('option') == 'com_categories')
			&& (isset($row) && !isset($options[0]))
			&& isset($this->element['show_root']))
		{
			if ($row->parent_id == '1')
			{
				$parent = new \stdClass;
				$parent->text = \JText::_('JGLOBAL_ROOT_PARENT');
				array_unshift($options, $parent);
			}

			array_unshift($options, \JHtml::_('select.option', '0', \JText::_('JGLOBAL_ROOT')));
		}

		// Merge any additional options in the XML definition.
		return array_merge(parent::getOptions(), $options);
	}

	/**
	 * Method to get the field input markup for a generic list.
	 * Use the multiple attribute to enable multiselect.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.6
	 */
	protected function getInput()
	{
		$html = array();
		$class = array();
		$attr = '';

		// Initialize some field attributes.
		$class[] = !empty($this->class) ? $this->class : '';

		if ($this->allowAdd)
		{
			$customGroupText = \JText::_('JGLOBAL_CUSTOM_CATEGORY');

			$class[] = 'chzn-custom-value';
			$attr .= ' data-custom_group_text="' . $customGroupText . '" '
					. 'data-no_results_text="' . \JText::_('JGLOBAL_ADD_CUSTOM_CATEGORY') . '" '
					. 'data-placeholder="' . \JText::_('JGLOBAL_TYPE_OR_SELECT_CATEGORY') . '" ';
		}

		if ($class)
		{
			$attr .= 'class="' . implode(' ', $class) . '"';
		}

		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$attr .= $this->multiple ? ' multiple' : '';
		$attr .= $this->required ? ' required aria-required="true"' : '';
		$attr .= $this->autofocus ? ' autofocus' : '';

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ((string) $this->readonly == '1'
			|| (string) $this->readonly == 'true'
			|| (string) $this->disabled == '1'
			|| (string) $this->disabled == 'true')
		{
			$attr .= ' disabled="disabled"';
		}

		// Initialize JavaScript field attributes.
		$attr .= $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

		// Get the field options.
		$options = (array) $this->getOptions();

		// Create a read-only list (no name) with hidden input(s) to store the value(s).
		if ((string) $this->readonly == '1' || (string) $this->readonly == 'true')
		{
			$html[] = \JHtml::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);

			// E.g. form field type tag sends $this->value as array
			if ($this->multiple && is_array($this->value))
			{
				if (!count($this->value))
				{
					$this->value[] = '';
				}

				foreach ($this->value as $value)
				{
					$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($value, ENT_COMPAT, 'UTF-8') . '">';
				}
			}
			else
			{
				$html[] = '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '">';
			}
		}
		else
		{
			// Create a regular list.
			if (count($options) === 0)
			{
				// All Categories have been deleted, so we need a new category (This will create on save if selected).
				$options[0]            = new \stdClass;
				$options[0]->value     = 'Uncategorised';
				$options[0]->text      = 'Uncategorised';
				$options[0]->level     = '1';
				$options[0]->published = '1';
				$options[0]->lft       = '1';
			}

			$html[] = \JHtml::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
		}

		return implode($html);
	}
}
