<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Featured Table class.
 *
 * @since  1.6
 */
class ContentTableFeatured extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  $db  Database connector object
	 *
	 * @since   1.6
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__content_frontpage', 'content_id', $db);
	}
}
