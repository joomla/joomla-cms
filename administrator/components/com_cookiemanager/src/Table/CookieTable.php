<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cookiemanager
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cookiemanager\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\String\PunycodeHelper;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

/**
 * Group Table class.
 *
 * @since   __DEPLOY_VERSION__
 */
class CookieTable extends Table
{
	/**
	 * Ensure the params in json encoded in the bind method
	 *
	 * @var    array
	 * @since   __DEPLOY_VERSION__
	 */
	protected $_jsonEncode = array('params');

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct(DatabaseDriver $db)
	{
		parent::__construct('#__cookiemanager_cookies', 'id', $db);
	}

	/**
	 * Overloaded bind function
	 *
	 * @param   mixed  $array   An associative array or object to bind to the \JTable instance.
	 * @param   mixed  $ignore  An optional array or space separated list of properties to ignore while binding.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function bind($array, $ignore = array())
	{
		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new Registry($array['params']);

			$array['params'] = (string) $registry;
		}

		return parent::bind($array, $ignore);
	}

}
