<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Framework.
 *
 * @since  3.1
 */
class JFormFieldTag extends JFormFieldList
{
	/**
	 * A flexible tag list that respects access controls
	 *
	 * @var    string
	 * @since  3.1
	 */
	public $type = 'Tag';

	/**
	 * Flag to work with nested tag field
	 *
	 * @var    boolean
	 * @since  3.1
	 */
	public $isNested = null;

	/**
	 * com_tags parameters
	 *
	 * @var    \Joomla\Registry\Registry
	 * @since  3.1
	 */
	protected $comParams = null;

	/**
	 * Constructor
	 *
	 * @since  3.1
	 */
	public function __construct()
	{
		parent::__construct();

		// Load com_tags config
		$this->comParams = JComponentHelper::getParams('com_tags');
	}

	/**
	 * Method to get the field input for a tag field.
	 *
	 * @return  string  The field input.
	 *
	 * @since   3.1
	 */
	protected function getInput()
	{
		// AJAX mode requires ajax-chosen
		if (!$this->isNested())
		{
			// Get the field id
			$id    = isset($this->element['id']) ? $this->element['id'] : null;
			$cssId = '#' . $this->getId($id, $this->element['name']);

			// Load the ajax-chosen customised field
			JHtml::_('tag.ajaxfield', $cssId, $this->allowCustom());
		}

		if (!is_array($this->value) && !empty($this->value))
		{
			if ($this->value instanceof JHelperTags)
			{
				if (empty($this->value->tags))
				{
					$this->value = array();
				}
				else
				{
					$this->value = $this->value->tags;
				}
			}

			// String in format 2,5,4
			if (is_string($this->value))
			{
				$this->value = explode(',', $this->value);
			}
		}

		$input = parent::getInput();

		return $input;
	}

	/**
	 * Method to get a list of tags
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.1
	 */
	protected function getOptions()
	{
		$published = $this->element['published']? $this->element['published'] : array(0,1);

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('DISTINCT a.id AS value, a.path, a.title AS text, a.level, a.published, a.lft')
			->from('#__tags AS a')
			->join('LEFT', $db->qn('#__tags') . ' AS b ON a.lft > b.lft AND a.rgt < b.rgt');

		// Filter language
		if (!empty($this->element['language']))
		{
			$query->where('a.language = ' . $db->q($this->element['language']));
		}

		$query->where($db->qn('a.lft') . ' > 0');

		// Filter on the published state
		if (is_numeric($published))
		{
			$query->where('a.published = ' . (int) $published);
		}
		elseif (is_array($published))
		{
			JArrayHelper::toInteger($published);
			$query->where('a.published IN (' . implode(',', $published) . ')');
		}

		$query->order('a.lft ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			return false;
		}

		// Block the possibility to set a tag as it own parent
		if ($this->form->getName() == 'com_tags.tag')
		{
			$id   = (int) $this->form->getValue('id', 0);

			foreach ($options as $option)
			{
				if ($option->value == $id)
				{
					$option->disable = true;
				}
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		// Prepare nested data
		if ($this->isNested())
		{
			$this->prepareOptionsNested($options);
		}
		else
		{
			$options = JHelperTags::convertPathsToNames($options);
		}

		return $options;
	}

	/**
	 * Add "-" before nested tags, depending on level
	 *
	 * @param   array  &$options  Array of tags
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.1
	 */
	protected function prepareOptionsNested(&$options)
	{
		if ($options)
		{
			foreach ($options as &$option)
			{
				$repeat = (isset($option->level) && $option->level - 1 >= 0) ? $option->level - 1 : 0;
				$option->text = str_repeat('- ', $repeat) . $option->text;
			}
		}

		return $options;
	}

	/**
	 * Determine if the field has to be tagnested
	 *
	 * @return  boolean
	 *
	 * @since   3.1
	 */
	public function isNested()
	{
		if (is_null($this->isNested))
		{
			// If mode="nested" || ( mode not set & config = nested )
			if ((isset($this->element['mode']) && $this->element['mode'] == 'nested')
				|| (!isset($this->element['mode']) && $this->comParams->get('tag_field_ajax_mode', 1) == 0))
			{
				$this->isNested = true;
			}
		}

		return $this->isNested;
	}

	/**
	 * Determines if the field allows or denies custom values
	 *
	 * @return  boolean
	 */
	public function allowCustom()
	{
		if (isset($this->element['custom']) && $this->element['custom'] == 'deny')
		{
			return false;
		}

		return true;
	}
}
