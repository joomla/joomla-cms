<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Base
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Adapter Instance Class
 *
 * @package		Joomla.Framework
 * @subpackage	Base
 * @since		1.6
 */
class JAdapterInstance extends JObject {

	/** Parent
	 * @var object */
	protected $parent = null;

	/** Database
	 * @var object */
	protected $db = null;


	/**
	 * Constructor
	 *
	 * @access	public
	 * @param	object	$parent	Parent object [JAdapter instance]
	 * @return	void
	 * @since	1.6
	 */
	public function __construct(&$parent, &$db)
	{
		$this->parent =& $parent;
		$this->db =& $db ? $db : JFactory::getDBO(); // pull in the global dbo in case
	}

	/**
	 * Retrieves the parent object
	 * @access  public
	 * @return 	object parent
	 * @since 	1.6
	 */
	public function getParent()
	{
		return $this->parent;
	}
}