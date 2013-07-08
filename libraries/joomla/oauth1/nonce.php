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
 * @since       12.3
 */
class JOAuth1Nonce
{
	/**
	 * @var    JDatabaseDriver  Database driver object.
	 * @since  12.3
	 */
	private $_db;

	/**
	 * @var    JRegistry  Options object.
	 * @since  12.3
	 */
	protected $options;

	/**
	 * Object constructor.
	 *
	 * @param   JRegistry        $options  Options object.
	 * @param   JDatabaseDriver  $db       The database driver object.
	 *
	 * @codeCoverageIgnore
	 * @since   12.3
	 */
	public function __construct(JRegistry $options = null, JDatabaseDriver $db = null)
	{
		// Setup the table object.
		$this->_db = $db ? $db : JFactory::getDbo();

		$this->options = $options ? $options : new JRegistry;
	}

	/**
	 * Validate the nonce.
	 *
	 * @param   string   $nonce        The nonce.
	 * @param   string   $consumerKey  The consumer key.
	 * @param   integer  $timestamp    The timestamp.
	 * @param   string   $token        The token.
	 *
	 * @return  boolean  Whether the nonce is valid or not.
	 */
	public function validate($nonce, $consumerKey, $timestamp, $token)
	{
		if (abs($timestamp - time()) > $this->options->get('drift', 15))
		{
			return false;
		}

		$query = $this->_db->getQuery(true);

		$query->select('count(nonce_id)')
			->from('#__oauth_nonce')
			->where('consumer_key = ' . $this->_db->quote($consumerKey))
			->where('timestamp = ' . (int) $timestamp)
			->where('nonce = ' . $this->_db->quote($nonce));

		$this->_db->setQuery($query);

		$count = $this->_db->loadResult();

		if ($count > 0)
		{
			return false;
		}

		$nonceObject = new stdClass;
		$nonceObject->consumer_key = $consumerKey;
		$nonceObject->timestamp = $timestamp;
		$nonceObject->nonce = $nonce;

		$this->_db->insertObject('#__oauth_nonce', $nonceObject, 'nonce_id');

		$query = $this->_db->getQuery(true);
		$query->delete('#__oauth_nonce')
			->where('timestamp < ' . (time() - $this->options->get('drift', 15)));
		$this->_db->setQuery($query);
		$this->_db->execute();

		return true;
	}
}
