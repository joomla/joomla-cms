<?php
/**
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

/**
 * @package		Joomla.Administrator
 * @subpackage	com_templates
 */
class TemplatesTableStyle extends JTable
{
	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__template_styles', 'id', $db);
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param	array		Named array
	 * @return	null|string	null is operation was satisfactory, otherwise returns an error
	 * @see JTable:bind
	 * @since 1.5
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string)$registry;
		}

		// Verify that the default style is not unset
		if ($array['home']=='0' && $this->home=='1') {
			$this->setError(JText::_('COM_TEMPLATES_ERROR_CANNOT_UNSET_DEFAULT_STYLE'));
			return false;
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check method to ensure data integrity.
	 *
	 * @return	boolean	True on success.
	 */
	function check()
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
	 * @param	boolean True to update fields even if they are null.
	 * @return	boolean	True on success.
	 * @since	1.0
	 * @link	http://docs.joomla.org/JTable/store
	 */
	public function store($updateNulls = false)
	{
		if ($this->home!='0') {
			$query = $this->_db->getQuery(true);
			$query->update('#__template_styles');
			$query->set('home=\'0\'');
			$query->where('client_id='.(int)$this->client_id);
			$query->where('home='.$this->_db->quote($this->home));
			$this->_db->setQuery($query);
			$this->_db->query();
		}
		return parent::store($updateNulls);
	}

	/**
	 * Overloaded store method to unsure existence of a default style for a template.
	 *
	 * @param	mixed	An optional primary key value to delete.  If not set the
	 *					instance property value is used.
	 * @return	boolean	True on success.
	 * @since	1.0
	 * @link	http://docs.joomla.org/JTable/delete
	 */
	public function delete($pk = null)
	{
		$k = $this->_tbl_key;
		$pk = (is_null($pk)) ? $this->$k : $pk;
		if (!is_null($pk)) {
			$query = $this->_db->getQuery(true);
			$query->from('#__template_styles');
			$query->select('id');
			$query->where('client_id='.(int)$this->client_id);
			$query->where('template='.$this->_db->quote($this->template));
			$this->_db->setQuery($query);
			$results = $this->_db->loadColumn();
			if (count($results)==1 && $results[0]==$pk) {
				$this->setError(JText::_('COM_TEMPLATES_ERROR_CANNOT_DELETE_LAST_STYLE'));
				return false;
			}
		}
		return parent::delete($pk);
	}
}
