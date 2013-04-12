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
	 * @var		JUcmType Object
	 * @since	13.1
	 */
	public $type;

	/**
	* The Database object
	*
	* @var		JDatabase Object
	* @since	13.1
	*/
	protected $db;

	/**
	* The alias for the content type
	*
	* @var	String name for content type
	* @since	13.1
	*/
	protected $alias;


	public function __construct($alias = null, JDatabase $database = null, JApplication $application = null)
	{
		$this->db 		= $database ? $database : JFactory::getDbo();
		$application = $application ? $application : JFactory::getApplication();

		// Make the best guess we can in the absence of information.
		$this->alias = $alias ? $alias : $app->input->get('option') . '.' . $app->input->get('view');
		$this->type  = $this->getType();
	}

	/**
	* Get the Content Type
	*
	* @param   Integer  $pk  The primary key of the alias type
	*
	* @return  JUcmType  The UCM Type
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
	*
	* @param   string  $alias  The string of the type alias
	*
	* @return  integer  The ID of the requested type
	*
	* @since 3.1
	*/
	public function getTypeId($alias = null)
	{
		if (!$alias)
		{
			$alias = $this->alias;
		}

		$query = $this->db->getQuery(true);
		$query->select('ct.type_id');
		$query->from($this->db->quoteName('#__content_types','ct'));
		$query->where($this->db->quoteName('ct.type_alias') . ' = ' . $this->db->q($alias));

		$this->db->setQuery($query);

		$id = $this->db->loadResult();

		return $id;

	}
}
