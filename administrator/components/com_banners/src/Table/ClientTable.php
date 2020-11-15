<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Banners\Administrator\Table;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Versioning\VersionableTableInterface;
use Joomla\Database\DatabaseDriver;

/**
 * Client table
 *
 * @since  1.6
 */
class ClientTable extends Table implements VersionableTableInterface
{
	/**
	 * Indicates that columns fully support the NULL value in the database
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $_supportNullValue = true;

	/**
	 * Constructor
	 *
	 * @param   DatabaseDriver  $db  Database connector object
	 *
	 * @since   1.5
	 */
	public function __construct(DatabaseDriver $db)
	{
		$this->typeAlias        = 'com_banners.client';

		$this->setColumnAlias('published', 'state');

		parent::__construct('#__banner_clients', 'id', $db);
	}

	/**
	 * Get the type alias for the history table
	 *
	 * @return  string  The alias as described above
	 *
	 * @since   4.0.0
	 */
	public function getTypeAlias()
	{
		return 'com_banners.client';
	}

	/**
	 * Overloaded check function
	 *
	 * @return  boolean
	 *
	 * @see     Table::check
	 * @since   __DEPLOY_VERSION__
	 */
	public function check()
	{
		// Check that client is not blank
		if (trim($this->name) === '')
		{
			$this->setError(Text::_('JLIB_CMS_WARNING_PROVIDE_VALID_NAME'));

			return false;
		}

		// Check that client contact name is not blank
		if (trim($this->contact) === '')
		{
			$this->setError(Text::_('JLIB_CMS_WARNING_PROVIDE_VALID_NAME'));

			return false;
		}

		return true;
	}
}
