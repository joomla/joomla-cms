<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  UCM
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * UCM Class for handling content types
 *
 * @package     Joomla.Libraries
 * @subpackage  UCM
 * @since       3.1
 */
class JUcmType implements JUcm
{
	/**
	 * The UCM Type
	 *
	 * @var    JUcmType
	 * @since  3.1
	 */
	public $type;

	/**
	* The Database object
	*
	* @var    JDatabaseDriver
	* @since  3.1
	*/
	protected $db;

	/**
	* The alias for the content type
	*
	* @var	  string
	* @since  3.1
	*/
	protected $alias;

	/**
	 * Class constructor
	 *
	 * @param   string            $alias        The alias for the item
	 * @param   JDatabaseDriver   $database     The database object
	 * @param   JApplicationBase  $application  The application object
	 *
	 * @since   3.1
	 */
	public function __construct($alias = null, JDatabaseDriver $database = null, JApplicationBase $application = null)
	{
		$this->db = $database ? $database : JFactory::getDbo();
		$app      = $application ? $application : JFactory::getApplication();

		// Make the best guess we can in the absence of information.
		$this->alias = $alias ? $alias : $app->input->get('option') . '.' . $app->input->get('view');
		$this->type  = $this->getType();
	}

	/**
	* Get the Content Type
	*
	* @param   integer  $pk  The primary key of the alias type
	*
	* @return  object  The UCM Type data
	*
	* @since   3.1
	*/
	public function getType($pk = null)
	{
		if (!$pk)
		{
			$pk = $this->getTypeId();
		}

		$query	= $this->db->getQuery(true);
		$query->select('ct.*');
		$query->from($this->db->quoteName('#__content_types', 'ct'));

		$query->where($this->db->quoteName('ct.type_id') . ' = ' . (int) $pk);
		$this->db->setQuery($query);

		$type = $this->db->loadObject();

		return $type;
	}

	/**
	 * Get the Content Type from the alias
	 *
	 * @param   string  $typeAlias  The alias for the type
	 *
	 * @return  object  The UCM Type data
	 *
	 * @since   3.2
	 */
	public function getTypeByAlias($typeAlias = null)
	{

		$query	= $this->db->getQuery(true);
		$query->select('ct.*');
		$query->from($this->db->quoteName('#__content_types', 'ct'));

		$query->where($this->db->quoteName('ct.type_alias') . ' = ' . (int) $typeAlias);
		$this->db->setQuery($query);

		$type = $this->db->loadObject();

		return $type;
	}

	/**
	 * Retrieves the UCM type ID
	 *
	 * @param   string  $alias  The string of the type alias
	 *
	 * @return  mixed  The ID of the requested type or false if type is not found
	 *
	 * @since   3.1
	 */
	public function getTypeId($alias = null)
	{
		if (!$alias)
		{
			$alias = $this->alias;
		}

		$query = $this->db->getQuery(true);
		$query->select('ct.type_id');
		$query->from($this->db->quoteName('#__content_types', 'ct'));
		$query->where($this->db->quoteName('ct.type_alias') . ' = ' . $this->db->q($alias));

		$this->db->setQuery($query);

		$id = $this->db->loadResult();

		if (!$id)
		{
			return false;
		}

		return $id;

	}

	/**
	 * Method to expand the field mapping
	 *
	 * @param   boolean  $assoc  True to return an associative array.
	 *
	 * @return  mixed  Array or object with field mappings. Defaults to object.
	 *
	 * @since   3.2
	 */
	public function fieldmapExpand($assoc = false)
	{

		if (!empty($this->type->field_mappings))
		{
			return $this->fieldmap = json_decode($this->type->field_mappings, $assoc);
		}
		else
		{
			return false;
		}
	}

	/**
	 * Magic method to get the name of the field mapped to a ucm field (core_something).
	 *
	 * @param   string  $ucmField  The name of the field in JTableCorecontent
	 *
	 * @return  string  The name mapped to the $ucmField for a given content type
	 *
	 * @since   3.2
	 */
	public function __get($ucmField)
	{
		if (!isset($this->fieldmap))
		{
			$this->fieldmapExpand(false);
		}

		return isset($this->fieldmap->common->$ucmField) ? $this->fieldmap->common->$ucmField : null;
	}
}
