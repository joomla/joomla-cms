<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
	 * @var int Primary key
	 */
	public $id = null;

	/**
	 * @var string
	 */
	public $template = null;

	/**
	 * @var int
	 */
	public $client_id = null;

	/**
	 * @var int
	 */
	public $home = null;

	/**
	 * @var string
	 */
	public $title = null;

	/**
	 * @var string
	 */
	public $params = null;

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
			$this->setError('Templates_Error_Style_requires_title');
			return false;
		}

		return true;
	}
}
