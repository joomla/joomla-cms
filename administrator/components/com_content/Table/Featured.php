<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Table;

defined('_JEXEC') or die;

use Joomla\Cms\Table\Table;

/**
 * Featured Table class.
 *
 * @since  1.6
 */
class Featured extends Table
{
	/**
	 * Constructor
	 *
	 * @param   \JDatabaseDriver  &$db  Database connector object
	 *
	 * @since   1.6
	 */
	public function __construct(JDatabaseDriver $db)
	{
		parent::__construct('#__content_frontpage', 'content_id', $db);
	}
}
