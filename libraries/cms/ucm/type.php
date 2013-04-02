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
	 * @param	String $alias	The table alias for the content type
	 *
	 * @var		JUcmType Object
	 * @since	13.1
	 */
	public $type;

	public function __construct($alias = null)
	{
		$this->type = $this->getType($alias);
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
	public function getType($alias = null, JInput $input = null)
	{
		$db		= JFactory::getDbo();

		$query	= $db->getQuery(TRUE);
		$query->select('ct.*');
		$query->from($db->qn('#__content_types') . ' AS ct');

		if (!$alias) 
		{
			$input = $input ? $input : JFactory::getApplication()->input;
			$alias = $input->get('option') . '.' . $input->get('view');
		}

		$query->where($db->qn('ct.type_alias') . ' = ' . $db->q($alias));			
		$db->setQuery($query);

		$type = $db->loadObject();

		return $type;
	}
}
