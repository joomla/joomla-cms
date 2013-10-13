<?php
/**
 * @package     Joomla.Platform
 * @subpackage  OAuth1
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * OAuth Client class for the Joomla Platform
 *
 * @package     Joomla.Platform
 * @subpackage  OAuth1
 * @since       12.1
 */
class JOAuth1Serverclient
{
	/**
	 * @var    JDatabaseDriver  Driver for persisting the client object.
	 * @since  12.1
	 */
	private $_db;

	/**
	 * @var    array  Client property array.
	 * @since  12.1
	 */
	private $_properties = array(
		'client_id' => '',
		'alias' => '',
		'key' => '',
		'secret' => '',
		'title' => '',
		'callback' => '',
		'resource_owner_id' => ''
	);

	/**
	 * Object constructor.
	 *
	 * @param   JDatabaseDriver  $db          The database driver to use when persisting the object.
	 * @param   array            $properties  A set of properties with which to prime the object.
	 *
	 * @codeCoverageIgnore
	 * @since   12.1
	 */
	public function __construct(JDatabaseDriver $db = null, array $properties = null)
	{
		// Setup the database object.
		$this->_db = $db ? $db : JFactory::getDbo();

		// Iterate over any input properties and bind them to the object.
		if ($properties)
		{
			foreach ($properties as $k => $v)
			{
				$this->_properties[$k] = $v;
			}
		}
	}

	/**
	 * Method to get a property value.
	 *
	 * @param   string  $p  The name of the property for which to return the value.
	 *
	 * @return  mixed  The property value for the given property name.
	 *
	 * @since   12.1
	 */
	public function __get($p)
	{
		if (isset($this->_properties[$p]))
		{
			return $this->_properties[$p];
		}
	}

	/**
	 * Method to set a value for a property.
	 *
	 * @param   string  $p  The name of the property for which to set the value.
	 * @param   mixed   $v  The property value to set.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function __set($p, $v)
	{
		if (isset($this->_properties[$p]))
		{
			$this->_properties[$p] = $v;
		}
	}

	/**
	 * Method to create the client in the database.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   12.1
	 */
	public function create()
	{
		// Setup the object to be inserted.
		$object = (object) $this->_properties;

		// Can't insert something that already has an ID.
		if ($object->client_id)
		{
			return false;
		}

		// Ensure we don't have an id to insert... use the auto-incrementor instead.
		unset($object->client_id);

		// Insert the object into the database.
		$success = $this->_db->insertObject('#__oauth_clients', $object, 'client_id');

		if ($success)
		{
			$this->_properties['client_id'] = (int) $object->client_id;
		}

		return $success;
	}

	/**
	 * Method to delete the client from the database.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function delete()
	{
		// Build the query to delete the row from the database.
		$query = $this->_db->getQuery(true);
		$query->delete('#__oauth_clients')
			->where('client_id = ' . (int) $this->_properties['client_id']);

		// Set and execute the query.
		$this->_db->setQuery($query);
		$this->_db->execute();
	}

	/**
	 * Method to load a client by id.
	 *
	 * @param   integer  $clientId  The id of the client to load.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function load($clientId)
	{
		// Build the query to load the row from the database.
		$query = $this->_db->getQuery(true);
		$query->select('*')
			->from('#__oauth_clients')
			->where('client_id = ' . (int) $clientId);

		// Set and execute the query.
		$this->_db->setQuery($query);
		$properties = $this->_db->loadAssoc();

		// Iterate over any the loaded properties and bind them to the object.
		if ($properties)
		{
			foreach ($properties as $k => $v)
			{
				$this->_properties[$k] = $v;
			}
		}
	}

	/**
	 * Method to load a client by key.
	 *
	 * @param   string  $key  The key of the client to load.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function loadByKey($key)
	{
		// Build the query to load the row from the database.
		$query = $this->_db->getQuery(true);
		$query->select('*')
			->from('#__oauth_clients')
			->where($this->_db->quoteName('key') . ' = ' . $this->_db->quote($key));

		// Set and execute the query.
		$this->_db->setQuery($query);
		$properties = $this->_db->loadAssoc();

		// Iterate over any the loaded properties and bind them to the object.
		if ($properties)
		{
			foreach ($properties as $k => $v)
			{
				$this->_properties[$k] = $v;
			}
		}
	}

	/**
	 * Method to update the client in the database.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   12.1
	 */
	public function update()
	{
		// Setup the object to be inserted.
		$object = (object) $this->_properties;

		if (!$object->client_id)
		{
			return false;
		}
		else
		{
			$object->client_id = (int) $object->client_id;
		}

		// Update the object into the database.
		return $this->_db->updateObject('#__oauth_clients', $object, 'client_id');
	}
}
