<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Template style table class.
 *
 * @since  1.6
 */
class TemplatesTableStyle extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  A database connector object
	 *
	 * @since   1.6
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__template_styles', 'id', $db);
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  null|string	null if operation was satisfactory, otherwise returns an error
	 *
	 * @since   1.6
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new Registry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		// Verify that the default style is not unset
		if ($array['home'] == '0' && $this->home == '1')
		{
			$this->setError(JText::_('COM_TEMPLATES_ERROR_CANNOT_UNSET_DEFAULT_STYLE'));

			return false;
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check method to ensure data integrity.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function check()
	{
		if (empty($this->title))
		{
			$this->setError(JText::_('COM_TEMPLATES_ERROR_STYLE_REQUIRES_TITLE'));

			return false;
		}

		return true;
	}

	/**
	 * Overloaded store method to ensure unicity of default style.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function store($updateNulls = false)
	{
		if ($this->home != '0')
		{
			$query = $this->_db->getQuery(true)
				->update('#__template_styles')
				->set('home=\'0\'')
				->where('client_id=' . (int) $this->client_id)
				->where('home=' . $this->_db->quote($this->home));
			$this->_db->setQuery($query);
			$this->_db->execute();
		}

		return parent::store($updateNulls);
	}

	/**
	 * Overloaded store method to unsure existence of a default style for a template.
	 *
	 * @param   mixed  $pk  An optional primary key value to delete.  If not set the instance property value is used.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.6
	 */
	public function delete($pk = null)
	{
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;

		if (!is_null($pk))
		{
			$query = $this->_db->getQuery(true)
				->from('#__template_styles')
				->select('id')
				->where('client_id=' . (int) $this->client_id)
				->where('template=' . $this->_db->quote($this->template));
			$this->_db->setQuery($query);
			$results = $this->_db->loadColumn();

			if (count($results) == 1 && $results[0] == $pk)
			{
				$this->setError(JText::_('COM_TEMPLATES_ERROR_CANNOT_DELETE_LAST_STYLE'));

				return false;
			}
		}

		return parent::delete($pk);
	}
}
