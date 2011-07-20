<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Class to maintain a pathway.
 *
 * The user's navigated path within the application.
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       11.1
 */
class JPathway extends JObject
{
	/**
	 * @var    array  Array to hold the pathway item objects
	 * @since  11.1
	 */
	protected $_pathway = null;

	/**
	 * @var    integer  Integer number of items in the pathway
	 * @since  11.1
	 */
	protected $_count = 0;

	/**
	 * Class constructor
	 */
	function __construct($options = array())
	{
		//Initialise the array
		$this->_pathway = array();
	}

	/**
	 * Returns a JPathway object
	 *
	 * @param   string   $client   The name of the client
	 * @param   array    $options  An associative array of options
	 *
	 * @return  JPathway  A JPathway object.
	 *
	 * @since   11.1
	 */
	public static function getInstance($client, $options = array())
	{
		static $instances;

		if (!isset($instances)) {
			$instances = array();
		}

		if (empty($instances[$client]))
		{
			//Load the router object
			$info = JApplicationHelper::getClientInfo($client, true);

			$path = $info->path . '/includes/pathway.php';
			if (file_exists($path))
			{
				require_once $path;

				// Create a JPathway object
				$classname = 'JPathway'.ucfirst($client);
				$instance = new $classname($options);
			}
			else
			{
				$error = JError::raiseError(500, JText::sprintf('JLIB_APPLICATION_ERROR_PATHWAY_LOAD', $client));
				return $error;
			}

			$instances[$client] = & $instance;
		}

		return $instances[$client];
	}

	/**
	 * Return the JPathWay items array
	 *
	 * @return  array  Array of pathway items
	 *
	 * @since   11.1
	 */
	public function getPathway()
	{
		$pw = $this->_pathway;

		// Use array_values to reset the array keys numerically
		return array_values($pw);
	}

	/**
	 * Set the JPathway items array.
	 *
	 * @param   array  $pathway	An array of pathway objects.
	 *
	 * @return  array  The previous pathway data.
	 *
	 * @since   11.1
	 */
	public function setPathway($pathway)
	{
		$oldPathway	= $this->_pathway;
		$pathway	= (array) $pathway;

		// Set the new pathway.
		$this->_pathway = array_values($pathway);

		return array_values($oldPathway);
	}

	/**
	 * Create and return an array of the pathway names.
	 *
	 * @return  array  Array of names of pathway items
	 *
	 * @since   11.1
	 */
	public function getPathwayNames()
	{
		// Initialise variables.
		$names = array (null);

		// Build the names array using just the names of each pathway item
		foreach ($this->_pathway as $item) {
			$names[] = $item->name;
		}

		//Use array_values to reset the array keys numerically
		return array_values($names);
	}

	/**
	 * Create and add an item to the pathway.
	 *
	 * @param   string  $name
	 * @param   string  $link
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function addItem($name, $link='')
	{
		// Initalize variables
		$ret = false;

		if ($this->_pathway[] = $this->_makeItem($name, $link)) {
			$ret = true;
			$this->_count++;
		}

		return $ret;
	}

	/**
	 * Set item name.
	 *
	 * @param   integer  $id
	 * @param   string   $name
	 *
	 * @return  boolean  True on success
	 *
	 * @since   11.1
	 */
	public function setItemName($id, $name)
	{
		// Initalize variables
		$ret = false;

		if (isset($this->_pathway[$id])) {
			$this->_pathway[$id]->name = $name;
			$ret = true;
		}

		return $ret;
	}

	/**
	 * Create and return a new pathway object.
	 *
	 * @param   string   $name  Name of the item
	 * @param   string   $link  Link to the item
	 *
	 * @return  JPathway  Pathway item object
	 *
	 * @since   11.1
	 */
	protected function _makeItem($name, $link)
	{
		$item = new stdClass;
		$item->name = html_entity_decode($name, ENT_COMPAT, 'UTF-8');
		$item->link = $link;

		return $item;
	}
}
