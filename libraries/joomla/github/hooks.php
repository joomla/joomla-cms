<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API Hooks class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  GitHub
 * @since       12.3
 */
class JGithubHooks extends JGithubObject
{
	/**
	 * Array containing the allowed hook events
	 *
	 * @var    array
	 * @since  12.3
	 */
	protected $events = array(
						'push', 'issues', 'issue_comment', 'commit_comment', 'pull_request', 'gollum', 'watch', 'download', 'fork', 'fork_apply',
						'member', 'public', 'status'
	);

	/**
	 * Method to create a hook on a repository.
	 *
	 * @param   string   $user    The name of the owner of the GitHub repository.
	 * @param   string   $repo    The name of the GitHub repository.
	 * @param   string   $name    The name of the service being called.
	 * @param   array    $config  Array containing the config for the service.
	 * @param   array    $events  The events the hook will be triggered for.
	 * @param   boolean  $active  Flag to determine if the hook is active
	 *
	 * @return  object
	 *
	 * @since   12.3
	 * @throws  DomainException
	 * @throws  RuntimeException
	 */
	public function create($user, $repo, $name, array $config, array $events = array('push'), $active = true)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/hooks';

		// Check to ensure all events are in the allowed list
		foreach ($events as $event)
		{
			if (!in_array($event, $this->events))
			{
				throw new RuntimeException('Your events array contains an unauthorized event.');
			}
		}

		$data = json_encode(
			array('name' => $name, 'config' => $config, 'events' => $events, 'active' => $active)
		);

		return $this->processResponse(
			$this->client->post($this->fetchUrl($path), $data),
			201
		);
	}

	/**
	 * Method to delete a hook
	 *
	 * @param   string   $user  The name of the owner of the GitHub repository.
	 * @param   string   $repo  The name of the GitHub repository.
	 * @param   integer  $id    ID of the hook to delete.
	 *
	 * @return  object
	 *
	 * @since   12.3
	 * @throws  DomainException
	 */
	public function delete($user, $repo, $id)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/hooks/' . $id;

		return $this->processResponse(
			$this->client->delete($this->fetchUrl($path)),
			204
		);
	}

	/**
	 * Method to edit a hook.
	 *
	 * @param   string   $user          The name of the owner of the GitHub repository.
	 * @param   string   $repo          The name of the GitHub repository.
	 * @param   integer  $id            ID of the hook to edit.
	 * @param   string   $name          The name of the service being called.
	 * @param   array    $config        Array containing the config for the service.
	 * @param   array    $events        The events the hook will be triggered for.  This resets the currently set list
	 * @param   array    $addEvents     Events to add to the hook.
	 * @param   array    $removeEvents  Events to remove from the hook.
	 * @param   boolean  $active        Flag to determine if the hook is active
	 *
	 * @return  object
	 *
	 * @since   12.3
	 * @throws  DomainException
	 * @throws  RuntimeException
	 */
	public function edit($user, $repo, $id, $name, array $config, array $events = array('push'), array $addEvents = array(),
		array $removeEvents = array(), $active = true)
	{
		// Check to ensure all events are in the allowed list
		foreach ($events as $event)
		{
			if (!in_array($event, $this->events))
			{
				throw new RuntimeException('Your events array contains an unauthorized event.');
			}
		}

		foreach ($addEvents as $event)
		{
			if (!in_array($event, $this->events))
			{
				throw new RuntimeException('Your active_events array contains an unauthorized event.');
			}
		}

		foreach ($removeEvents as $event)
		{
			if (!in_array($event, $this->events))
			{
				throw new RuntimeException('Your remove_events array contains an unauthorized event.');
			}
		}
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/hooks/' . $id;

		$data = json_encode(
			array(
				'name' => $name,
				'config' => $config,
				'events' => $events,
				'add_events' => $addEvents,
				'remove_events' => $removeEvents,
				'active' => $active)
		);

		return $this->processResponse(
			$this->client->patch($this->fetchUrl($path), $data)
		);
	}

	/**
	 * Method to get details about a single hook for the repository.
	 *
	 * @param   string   $user  The name of the owner of the GitHub repository.
	 * @param   string   $repo  The name of the GitHub repository.
	 * @param   integer  $id    ID of the hook to retrieve
	 *
	 * @return  object
	 *
	 * @since   12.3
	 * @throws  DomainException
	 */
	public function get($user, $repo, $id)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/hooks/' . $id;

		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Method to list hooks for a repository.
	 *
	 * @param   string   $user   The name of the owner of the GitHub repository.
	 * @param   string   $repo   The name of the GitHub repository.
	 * @param   integer  $page   Page to request
	 * @param   integer  $limit  Number of results to return per page
	 *
	 * @return  object
	 *
	 * @since   12.3
	 * @throws  DomainException
	 */
	public function getList($user, $repo, $page = 0, $limit = 0)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/hooks';

		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Method to test a hook against the latest repository commit
	 *
	 * @param   string   $user  The name of the owner of the GitHub repository.
	 * @param   string   $repo  The name of the GitHub repository.
	 * @param   integer  $id    ID of the hook to delete
	 *
	 * @return  object
	 *
	 * @since   12.3
	 * @throws  DomainException
	 */
	public function test($user, $repo, $id)
	{
		// Build the request path.
		$path = '/repos/' . $user . '/' . $repo . '/hooks/' . $id . '/test';

		return $this->processResponse(
			$this->client->post($this->fetchUrl($path), json_encode('')),
			204
		);
	}
}
