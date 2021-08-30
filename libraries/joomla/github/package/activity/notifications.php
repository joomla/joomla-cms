<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API Activity Events class for the Joomla Platform.
 *
 * @documentation https://developer.github.com/v3/activity/notifications/
 *
 * @since       3.3 (CMS)
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
class JGithubPackageActivityNotifications extends JGithubPackage
{
	/**
	 * List your notifications.
	 *
	 * List all notifications for the current user, grouped by repository.
	 *
	 * @param   boolean  $all            True to show notifications marked as read.
	 * @param   boolean  $participating  True to show only notifications in which the user is directly participating or
	 *                                   mentioned.
	 * @param   JDate    $since          filters out any notifications updated before the given time. The time should be passed in
	 *                                   as UTC in the ISO 8601 format.
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function getList($all = true, $participating = true, JDate $since = null)
	{
		// Build the request path.
		$path = '/notifications?';

		$path .= ($all) ? '&all=1' : '';
		$path .= ($participating) ? '&participating=1' : '';
		$path .= ($since) ? '&since=' . $since->toISO8601() : '';

		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * List your notifications in a repository.
	 *
	 * List all notifications for the current user.
	 *
	 * @param   string   $owner          Repository owner.
	 * @param   string   $repo           Repository name.
	 * @param   boolean  $all            True to show notifications marked as read.
	 * @param   boolean  $participating  True to show only notifications in which the user is directly participating or
	 *                                   mentioned.
	 * @param   JDate    $since          filters out any notifications updated before the given time. The time should be passed in
	 *                                   as UTC in the ISO 8601 format.
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function getListRepository($owner, $repo, $all = true, $participating = true, JDate $since = null)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/notifications?';

		$path .= ($all) ? '&all=1' : '';
		$path .= ($participating) ? '&participating=1' : '';
		$path .= ($since) ? '&since=' . $since->toISO8601() : '';

		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Mark as read.
	 *
	 * Marking a notification as “read” removes it from the default view on GitHub.com.
	 *
	 * @param   boolean  $unread      Changes the unread status of the threads.
	 * @param   boolean  $read        Inverse of “unread”.
	 * @param   JDate    $lastReadAt  Describes the last point that notifications were checked.
	 *                                Anything updated since this time will not be updated. Default: Now. Expected in ISO 8601 format.
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function markRead($unread = true, $read = true, JDate $lastReadAt = null)
	{
		// Build the request path.
		$path = '/notifications';

		$data = array(
			'unread' => $unread,
			'read'   => $read,
		);

		if ($lastReadAt)
		{
			$data['last_read_at'] = $lastReadAt->toISO8601();
		}

		return $this->processResponse(
			$this->client->put($this->fetchUrl($path), json_encode($data)),
			205
		);
	}

	/**
	 * Mark notifications as read in a repository.
	 *
	 * Marking all notifications in a repository as “read” removes them from the default view on GitHub.com.
	 *
	 * @param   string   $owner       Repository owner.
	 * @param   string   $repo        Repository name.
	 * @param   boolean  $unread      Changes the unread status of the threads.
	 * @param   boolean  $read        Inverse of “unread”.
	 * @param   JDate    $lastReadAt  Describes the last point that notifications were checked.
	 *                                Anything updated since this time will not be updated. Default: Now. Expected in ISO 8601 format.
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function markReadRepository($owner, $repo, $unread, $read, JDate $lastReadAt = null)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/notifications';

		$data = array(
			'unread' => $unread,
			'read'   => $read,
		);

		if ($lastReadAt)
		{
			$data['last_read_at'] = $lastReadAt->toISO8601();
		}

		return $this->processResponse(
			$this->client->put($this->fetchUrl($path), json_encode($data)),
			205
		);
	}

	/**
	 * View a single thread.
	 *
	 * @param   integer  $id  The thread id.
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function viewThread($id)
	{
		// Build the request path.
		$path = '/notifications/threads/' . $id;

		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Mark a thread as read.
	 *
	 * @param   integer  $id      The thread id.
	 * @param   boolean  $unread  Changes the unread status of the threads.
	 * @param   boolean  $read    Inverse of “unread”.
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function markReadThread($id, $unread = true, $read = true)
	{
		// Build the request path.
		$path = '/notifications/threads/' . $id;

		$data = array(
			'unread' => $unread,
			'read'   => $read,
		);

		return $this->processResponse(
			$this->client->patch($this->fetchUrl($path), json_encode($data)),
			205
		);
	}

	/**
	 * Get a Thread Subscription.
	 *
	 * This checks to see if the current user is subscribed to a thread.
	 * You can also get a Repository subscription.
	 *
	 * @param   integer  $id  The thread id.
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function getThreadSubscription($id)
	{
		// Build the request path.
		$path = '/notifications/threads/' . $id . '/subscription';

		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Set a Thread Subscription.
	 *
	 * This lets you subscribe to a thread, or ignore it. Subscribing to a thread is unnecessary
	 * if the user is already subscribed to the repository. Ignoring a thread will mute all
	 * future notifications (until you comment or get @mentioned).
	 *
	 * @param   integer  $id          The thread id.
	 * @param   boolean  $subscribed  Determines if notifications should be received from this thread.
	 * @param   boolean  $ignored     Determines if all notifications should be blocked from this thread.
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function setThreadSubscription($id, $subscribed, $ignored)
	{
		// Build the request path.
		$path = '/notifications/threads/' . $id . '/subscription';

		$data = array(
			'subscribed' => $subscribed,
			'ignored'    => $ignored,
		);

		return $this->processResponse(
			$this->client->put($this->fetchUrl($path), json_encode($data))
		);
	}

	/**
	 * Delete a Thread Subscription.
	 *
	 * @param   integer  $id  The thread id.
	 *
	 * @since 3.3 (CMS)
	 *
	 * @return object
	 */
	public function deleteThreadSubscription($id)
	{
		// Build the request path.
		$path = '/notifications/threads/' . $id . '/subscription';

		return $this->processResponse(
			$this->client->delete($this->fetchUrl($path)),
			204
		);
	}
}
