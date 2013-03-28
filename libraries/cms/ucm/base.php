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
 * Base class for implementing UCM
 *
 * @package     Joomla.Libraries
 * @subpackage  UCM
 * @since       3.1
 */
abstract class JUcmBase implements JUcm
{
	/**
	 * The related table object
	 *
	 * @var    JTable Object
	 * @since  13.1
	 */
	protected $table;

	/**
	 * The UCM type object
	 *
	 * @var    JUcmType Object
	 * @since  13.1
	 */
	protected $type;

	/**
	 * Instantiate the UcmBase.
	 *
	 * @param   JTable  	$table    The table object
	 * @param   JUcmType  	$model    The type object
	 *
	 * @since  13.1
	 */
	public function __construct(JTable $table = null, JUcmType $type = null)
	{
		// Setup dependencies.
		$this->table = isset($table) ? $table : null;
		$this->type = isset($type) ? $type : $this->getType();
	}

	/**
	*
	* @param	Array	$data	The content to be saved
	* @param	String	$type	The UCM Type string
	*
	* @return	boolean	true
	*
	* @since	13.1
	**/
	public function save($data = null, $type = null)
	{

	}

	/**
	* Get the UCM Content type.
	*
	* @return	Object	The UCM content type
	*
	* @since	13.1
	**/
	public function getType()
	{
		$type = new JUcmType($this->table);

		return $type;
	}
}
