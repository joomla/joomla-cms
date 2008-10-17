<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Table
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// No direct access
defined('JPATH_BASE') or die();

/**
 * Plugin table
 *
 * @package 	Joomla.Framework
 * @subpackage		Table
 * @since	1.0
 */
class JTablePlugin extends JTable
{
	/**
	 * Primary Key
	 *
	 *  @var int
	 */
	protected $id = null;

	/**
	 *
	 *
	 * @var varchar
	 */
	protected $name = null;

	/**
	 *
	 *
	 * @var varchar
	 */
	protected $element = null;

	/**
	 *
	 *
	 * @var varchar
	 */
	protected $folder = null;

	/**
	 *
	 *
	 * @var tinyint unsigned
	 */
	protected $access = null;

	/**
	 *
	 *
	 * @var int
	 */
	protected $ordering = null;

	/**
	 *
	 *
	 * @var tinyint
	 */
	protected $published = null;

	/**
	 *
	 *
	 * @var tinyint
	 */
	protected $iscore = null;

	/**
	 *
	 *
	 * @var tinyint
	 */
	protected $client_id = null;

	/**
	 *
	 *
	 * @var int unsigned
	 */
	protected $checked_out = 0;

	/**
	 *
	 *
	 * @var datetime
	 */
	protected $checked_out_time = 0;

	/**
	 *
	 *
	 * @var text
	 */
	protected $params = null;

	protected function __construct(& $db) {
		parent::__construct('#__plugins', 'id', $db);
	}

	/**
	* Overloaded bind function
	*
	* @access public
	* @param array $hash named array
	* @return null|string	null is operation was satisfactory, otherwise returns an error
	* @see JTable:bind
	* @since 1.5
	*/
	public function bind($array, $ignore = '')
	{
		if (isset( $array['params'] ) && is_array($array['params']))
		{
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = $registry->toString();
		}

		return parent::bind($array, $ignore);
	}
}
