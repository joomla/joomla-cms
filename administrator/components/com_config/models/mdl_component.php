<?php
/**
 * @version $Id: admin.config.php 3566 2006-05-20 14:57:33Z stingrey $
 * @package Joomla
 * @subpackage Config
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.application.model' );

/**
 * @package Joomla
 * @subpackage Config
 */
class JConfigComponentModel extends JModel 
{
	/** @var object JTable object */
	var $_table = null;

	/**
	 * Returns the internal table object
	 * @return JTable
	 */
	function &getTable()
	{
		if ($this->_table == null)
		{
			jimport( 'joomla.database.table.component' );

			$db = &$this->getDBO();
			$this->_table = new JTableComponent( $db );
		}
		return $this->_table;
	}

	/**
	 * Get the params for the configuration variables
	 */
	function &getParams()
	{
		static $instance;

		if ($instance == null)
		{
			$table = &$this->getTable();
			$option = preg_replace( '#\W#', '', $table->option );

			// work out file path
			$path = JPATH_ADMINISTRATOR . '/components/' . $option . '/config.xml';
			if (file_exists( $path ))
			{
				$instance = new JParameter( $table->params, $path );
			}
			else
			{
				$instance = new JParameter( $table->params );
			}
		}
		return $instance;
	}
}
?>