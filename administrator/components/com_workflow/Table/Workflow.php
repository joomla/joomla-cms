<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Workflow\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;

/**
 * Category table
 *
 * @since  1.6
 */
class Workflow extends Table
{

	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver  $db  Database connector object
	 *
	 * @since   1.0
	 */
	public function __construct(\JDatabaseDriver $db)
	{
		$this->typeAlias = 'com_workflow.name';
		parent::__construct('#__workflows', 'id', $db);
	}
}
