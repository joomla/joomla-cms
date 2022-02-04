<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Pathway;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\ApplicationHelper;

/**
 * Class to maintain a pathway.
 *
 * The user's navigated path within the application.
 *
 * @since  1.5
 */
class Pathway
{
	/**
	 * @var    array  Array to hold the pathway item objects
	 * @since  1.5
	 * @deprecated  4.0  Will convert to $pathway
	 */
	protected $_pathway = array();

	/**
	 * @var    integer  Integer number of items in the pathway
	 * @since  1.5
	 * @deprecated  4.0  Will convert to $count
	 */
	protected $_count = 0;

	/**
	 * JPathway instances container.
	 *
	 * @var    Pathway[]
	 * @since  1.7
	 */
	protected static $instances = array();

	/**
	 * Class constructor
	 *
	 * @param   array  $options  The class options.
	 *
	 * @since   1.5
	 */
	public function __construct($options = array())
	{
	}

	/**
	 * Returns a Pathway object
	 *
	 * @param   string  $client   The name of the client
	 * @param   array   $options  An associative array of options
	 *
	 * @return  Pathway  A Pathway object.
	 *
	 * @since   1.5
	 * @throws  \RuntimeException
	 */
	public static function getInstance($client, $options = array())
	{
		if (empty(self::$instances[$client]))
		{
			// Create a Pathway object
			$classname = 'JPathway' . ucfirst($client);

			if (!class_exists($classname))
			{
				// @deprecated 4.0 Everything in this block is deprecated but the warning is only logged after the file_exists
				// Load the pathway object
				$info = ApplicationHelper::getClientInfo($client, true);

				if (is_object($info))
				{
					$path = $info->path . '/includes/pathway.php';

					\JLoader::register($classname, $path);

					if (class_exists($classname))
					{
						\JLog::add('Non-autoloadable Pathway subclasses are deprecated, support will be removed in 4.0.', \JLog::WARNING, 'deprecated');
					}
				}
			}

			if (class_exists($classname))
			{
				self::$instances[$client] = new $classname($options);
			}
			else
			{
				throw new \RuntimeException(\JText::sprintf('JLIB_APPLICATION_ERROR_PATHWAY_LOAD', $client), 500);
			}
		}

		return self::$instances[$client];
	}

	/**
	 * Return the Pathway items array
	 *
	 * @return  array  Array of pathway items
	 *
	 * @since   1.5
	 */
	public function getPathway()
	{
		$pw = $this->_pathway;

		// Use array_values to reset the array keys numerically
		return array_values($pw);
	}

	/**
	 * Set the Pathway items array.
	 *
	 * @param   array  $pathway  An array of pathway objects.
	 *
	 * @return  array  The previous pathway data.
	 *
	 * @since   1.5
	 */
	public function setPathway($pathway)
	{
		$oldPathway = $this->_pathway;

		// Set the new pathway.
		$this->_pathway = array_values((array) $pathway);

		return array_values($oldPathway);
	}

	/**
	 * Create and return an array of the pathway names.
	 *
	 * @return  array  Array of names of pathway items
	 *
	 * @since   1.5
	 */
	public function getPathwayNames()
	{
		$names = array();

		// Build the names array using just the names of each pathway item
		foreach ($this->_pathway as $item)
		{
			$names[] = $item->name;
		}

		// Use array_values to reset the array keys numerically
		return array_values($names);
	}

	/**
	 * Create and add an item to the pathway.
	 *
	 * @param   string  $name  The name of the item.
	 * @param   string  $link  The link to the item.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.5
	 */
	public function addItem($name, $link = '')
	{
		$ret = false;

		if ($this->_pathway[] = $this->makeItem($name, $link))
		{
			$ret = true;
			$this->_count++;
		}

		return $ret;
	}

	/**
	 * Set item name.
	 *
	 * @param   integer  $id    The id of the item on which to set the name.
	 * @param   string   $name  The name to set.
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.5
	 */
	public function setItemName($id, $name)
	{
		$ret = false;

		if (isset($this->_pathway[$id]))
		{
			$this->_pathway[$id]->name = $name;
			$ret = true;
		}

		return $ret;
	}

	/**
	 * Create and return a new pathway object.
	 *
	 * @param   string  $name  Name of the item
	 * @param   string  $link  Link to the item
	 *
	 * @return  Pathway  Pathway item object
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use makeItem() instead
	 * @codeCoverageIgnore
	 */
	protected function _makeItem($name, $link)
	{
		return $this->makeItem($name, $link);
	}

	/**
	 * Create and return a new pathway object.
	 *
	 * @param   string  $name  Name of the item
	 * @param   string  $link  Link to the item
	 *
	 * @return  Pathway  Pathway item object
	 *
	 * @since   3.1
	 */
	protected function makeItem($name, $link)
	{
		$item = new \stdClass;
		$item->name = html_entity_decode($name, ENT_COMPAT, 'UTF-8');
		$item->link = $link;

		return $item;
	}
}
