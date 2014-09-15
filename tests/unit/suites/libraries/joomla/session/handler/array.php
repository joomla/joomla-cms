<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Session
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Interface for managing HTTP sessions
 *
 * @package     Joomla.Platform
 * @subpackage  Session
 * @since       3.4
 */
class JSessionHandlerArray implements JSessionHandlerInterface
{
	/**
	 * The id of the handler
	 *
	 * @var  string
	 */
	protected $id = '';

	/**
	 * The name of the handler
	 *
	 * @var  string
	 */
	protected $name;

	/**
	 * Has the session heen started
	 *
	 * @var  bool
	 */
	protected $started = false;

	/**
	 * Has the session been closed
	 *
	 * @var  bool
	 */
	protected $closed = false;

	/**
	 * @var  array
	 */
	protected $data = array();

	/**
	 * Constructor.
	 *
	 * @param string      $name    Session name
	 */
	public function __construct($name = 'MOCKSESSID')
	{
		$this->name = $name;
	}

	/**
	 * Sets the session data.
	 *
	 * @param array $array
	 */
	public function setSessionData(array $array)
	{
		$this->data = $array;
	}

	/**
	 * {@inheritdoc}
	 */
	public function start()
	{
		if ($this->started && !$this->closed) {
			return true;
		}

		if (empty($this->id)) {
			$this->setId($this->generateId());
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function regenerate($destroy = false, $lifetime = null)
	{
		if (!$this->started)
		{
			$this->start();
		}

		$this->id = $this->generateId();

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setId($id)
	{
		if ($this->started) {
			throw new LogicException('Cannot set session ID after the session has started.');
		}

		// Set the PHP Session ID here too, it just works
		session_id($id);

		$this->id = $id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save()
	{
		if (!$this->started || $this->closed) {
			throw new \RuntimeException("Trying to save a session that was not started yet or was already closed");
		}
		// nothing to do since we don't persist the session data
		$this->closed = false;
		$this->started = false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear()
	{
		// clear out the session
		$this->data = array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function isStarted()
	{
		return $this->started;
	}

	/**
	 * Generates a session ID.
	 *
	 * This doesn't need to be particularly cryptographically secure since this is just
	 * a mock.
	 *
	 * @return string
	 */
	protected function generateId()
	{
		return hash('sha256', uniqid(mt_rand()));
	}
}
