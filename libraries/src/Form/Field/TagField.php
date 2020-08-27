<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * List of Tags field.
 *
 * @since  3.1
 */
class TagField extends ListField
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
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $layout = 'joomla.form.field.tag';

	/**
	 * Constructor
	 *
	 * @since  3.1
	 */
	public function __construct()
	{
		parent::__construct();

		// Load com_tags config
		$this->comParams = ComponentHelper::getParams('com_tags');
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
		$data = $this->getLayoutData();

		if (!\is_array($this->value) && !empty($this->value))
		{
			if ($this->value instanceof TagsHelper)
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
			if (\is_string($this->value))
			{
				$this->value = explode(',', $this->value);
			}

			// Integer is given
			if (\is_int($this->value))
			{
				$this->value = array($this->value);
			}

			$data['value'] = $this->value;
		}

		$data['remoteSearch']  = $this->isRemoteSearch();
		$data['options']       = $this->getOptions();
		$data['isNested']      = $this->isNested();
		$data['allowCustom']   = $this->allowCustom();
		$data['minTermLength'] = (int) $this->comParams->get('min_term_length', 3);

		return $this->getRenderer($this->layout)->render($data);
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
		$published = $this->element['published'] ?: array(0, 1);
		$app       = Factory::getApplication();
		$language  = null;

		// Return only basic options, everything else will be searched via AJAX
		if ($this->isRemoteSearch() && !$this->value)
		{
			return parent::getOptions();
		}

		$db    = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				[
					'DISTINCT ' . $db->quoteName('a.id', 'value'),
					$db->quoteName('a.path'),
					$db->quoteName('a.title', 'text'),
					$db->quoteName('a.level'),
					$db->quoteName('a.published'),
					$db->quoteName('a.lft'),
				]
			)
			->from($db->quoteName('#__tags', 'a'))
			->join(
				'LEFT',
				$db->quoteName('#__tags', 'b'),
				$db->quoteName('a.lft') . ' > ' . $db->quoteName('b.lft') . ' AND ' . $db->quoteName('a.rgt') . ' < ' . $db->quoteName('b.rgt')
			);

		// Limit Options in multilanguage
		if ($app->isClient('site') && Multilanguage::isEnabled())
		{
			if (ComponentHelper::getParams('com_tags')->get('tag_list_language_filter') === 'current_language')
			{
				$language = [$app->getLanguage()->getTag(), '*'];
			}
		}
		// Filter language
		elseif (!empty($this->element['language']))
		{
			if (strpos($this->element['language'], ',') !== false)
			{
				$language = explode(',', $this->element['language']);
			}
			else
			{
				$language = [$this->element['language']];
			}
		}

		if ($language)
		{
			$query->whereIn($db->quoteName('a.language'), $language, ParameterType::STRING);
		}

		$query->where($db->quoteName('a.lft') . ' > 0');

		// Preload only active values, everything else will be searched via AJAX
		if ($this->isRemoteSearch() && $this->value)
		{
			$query->whereIn($db->quoteName('a.id'), $this->value);
		}

		// Filter on the published state
		if (is_numeric($published))
		{
			$published = (int) $published;
			$query->where($db->quoteName('a.published') . ' = :published')
				->bind(':published', $published, ParameterType::INTEGER);
		}
		elseif (\is_array($published))
		{
			$published = ArrayHelper::toInteger($published);
			$query->whereIn($db->quoteName('a.published'), $published);
		}

		$query->order($db->quoteName('a.lft') . ' ASC');

		// Get the options.
		$db->setQuery($query);

		try
		{
			$options = $db->loadObjectList();
		}
		catch (\RuntimeException $e)
		{
			return array();
		}

		// Block the possibility to set a tag as it own parent
		if ($this->form->getName() === 'com_tags.tag')
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
			$options = TagsHelper::convertPathsToNames($options);
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
		if ($this->isNested === null)
		{
			// If mode="nested" || ( mode not set & config = nested )
			if (isset($this->element['mode']) && (string) $this->element['mode'] === 'nested'
				|| !isset($this->element['mode']) && $this->comParams->get('tag_field_ajax_mode', 1) == 0)
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
		if ($this->element['custom'] && \in_array((string) $this->element['custom'], array('0', 'false', 'deny')))
		{
			return false;
		}

		return Factory::getUser()->authorise('core.create', 'com_tags');
	}

	/**
	 * Check whether need to enable AJAX search
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public function isRemoteSearch()
	{
		if ($this->element['remote-search'])
		{
			return !\in_array((string) $this->element['remote-search'], array('0', 'false', ''));
		}

		return $this->comParams->get('tag_field_ajax_mode', 1) == 1;
	}
}
