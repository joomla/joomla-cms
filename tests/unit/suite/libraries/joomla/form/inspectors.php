<?php
/**
 * Inspector classes for the forms library.
 */
require_once JPATH_BASE.'/libraries/joomla/form/form.php';

class JFormInspector extends JForm
{
	public function &getName()
	{
		return $this->_name;
	}

	public function &getFieldsets()
	{
		return $this->_fieldsets;
	}

	public function &getGroups()
	{
		return $this->_groups;
	}

	public function &getData()
	{
		return $this->_data;
	}

	public function &getOptions()
	{
		return $this->_options;
	}
}