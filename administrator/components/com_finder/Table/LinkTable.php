<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Finder\Administrator\Table;

use Joomla\CMS\Table\Table;

defined('_JEXEC') or die;

/**
 * Link table class for the Finder package.
 *
 * @since  2.5
 */
class LinkTable extends Table
{
	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver  $db  \JDatabaseDriver connector object.
	 *
	 * @since   2.5
	 */
	public function __construct(\JDatabaseDriver $db)
	{
		parent::__construct('#__finder_links', 'link_id', $db);
	}
}
