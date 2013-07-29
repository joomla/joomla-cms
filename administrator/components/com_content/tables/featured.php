<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Table for featured articles
 *
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @since       1.0
 */
class ContentTableFeatured extends JTable
{
	/**
	 * Constructor
	 *
	 * @param   JDatabaseDriver  &$db  A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__content_frontpage', 'content_id', $db);
	}
}
