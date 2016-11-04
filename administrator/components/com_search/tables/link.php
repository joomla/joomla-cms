<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_search
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * Link table class for the Search package.
 *
 * @since  2.5
 */
class SearchTableLink extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  JDatabaseDriver connector object.
	 *
	 * @since   2.5
	 */
	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__finder_links', 'link_id', $db);
	}
}
