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
abstract class JUcmType implements JUcm
{
	/**
	 * The UCM Type
	 *
	 * @var    JUcmType Object
	 * @since  13.1
	 */
	protected $type;

	/**
	 * The related table object
	 *
	 * @var    JTable Object
	 * @since  13.1
	 */
	protected $table;

	/**
	 * Instantiate the UcmBase.
	 *
	 * @param   JModel  	$model    The model object
	 * @param   JUcmType  	$model    The type object
	 *
	 * @since  13.1
	 */
	public function __construct(JTable $table = null)
	{
		// Setup dependencies.
		$this->table = isset($table) ? $table : null;
	}

	/**
	* Get the Content Type
	*
	* @param	JInput	$input	The input object
	*
	* @return	Object	$type	The UCM Type
	*
	* @since 13.1
	*/
	public function getType(JInput $input = null)
	{
		$db		= JFactory::getDbo();

		$query	= $db->getQuery();
		$query->select('ct.*');
		$query->from($db->qn('#__content_types') . ' AS ct');

		if ($this->table)
		{
			$query->where($db->qn('ct.table') . ' = ' . $db->q($this->tablle));
		} else 
		{
			$input = $input ? $input : JFactory::getApplication()->input;
			$alias = $input->get('option') . '.' . $input->get('view');
			$query->where($db->qn('ct.alias') . ' = ' . $db->q($alias));
		}

		$db->setQuery($query);

		$this->type = $db->loadObject();

		return $this->type;
	}
}
