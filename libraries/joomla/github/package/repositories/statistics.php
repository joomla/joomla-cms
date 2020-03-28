<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API class for the Joomla Platform.
 *
 * The Repository Statistics API allows you to fetch the data that GitHub uses for
 * visualizing different types of repository activity.
 *
 * @documentation https://developer.github.com/v3/repos/statistics
 *
 * @since       3.3 (CMS)
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
class JGithubPackageRepositoriesStatistics  extends JGithubPackage
{
	/**
	 * Get contributors list with additions, deletions, and commit counts.
	 *
	 * Response include:
	 * total - The Total number of commits authored by the contributor.
	 *
	 * Weekly Hash
	 *
	 * w - Start of the week
	 * a - Number of additions
	 * d - Number of deletions
	 * c - Number of commits
	 *
	 * @param   string  $owner  The owner of the repository.
	 * @param   string  $repo   The repository name.
	 *
	 * @since   1.0
	 *
	 * @return  object
	 */
	public function getListContributors($owner, $repo)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/stats/contributors';

		// Send the request.
		return $this->processResponse($this->client->get($this->fetchUrl($path)));
	}

	/**
	 * Get the last year of commit activity data.
	 *
	 * Returns the last year of commit activity grouped by week.
	 * The days array is a group of commits per day, starting on Sunday.
	 *
	 * @param   string  $owner  The owner of the repository.
	 * @param   string  $repo   The repository name.
	 *
	 * @since   1.0
	 *
	 * @return  object
	 */
	public function getActivityData($owner, $repo)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/stats/commit_activity';

		// Send the request.
		return $this->processResponse($this->client->get($this->fetchUrl($path)));
	}

	/**
	 * Get the number of additions and deletions per week.
	 *
	 * Response returns a weekly aggregate of the number of additions and deletions pushed to a repository.
	 *
	 * @param   string  $owner  The owner of the repository.
	 * @param   string  $repo   The repository name.
	 *
	 * @since   1.0
	 *
	 * @return  object
	 */
	public function getCodeFrequency($owner, $repo)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/stats/code_frequency';

		// Send the request.
		return $this->processResponse($this->client->get($this->fetchUrl($path)));
	}

	/**
	 * Get the weekly commit count for the repo owner and everyone else.
	 *
	 * Returns the total commit counts for the "owner" and total commit counts in "all". "all" is everyone combined,
	 * including the owner in the last 52 weeks.
	 * If you’d like to get the commit counts for non-owners, you can subtract all from owner.
	 *
	 * The array order is oldest week (index 0) to most recent week.
	 *
	 * @param   string  $owner  The owner of the repository.
	 * @param   string  $repo   The repository name.
	 *
	 * @since   1.0
	 *
	 * @return  object
	 */
	public function getParticipation($owner, $repo)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/stats/participation';

		// Send the request.
		return $this->processResponse($this->client->get($this->fetchUrl($path)));
	}

	/**
	 * Get the number of commits per hour in each day.
	 *
	 * Response
	 * Each array contains the day number, hour number, and number of commits:
	 *
	 * 0-6: Sunday - Saturday
	 * 0-23: Hour of day
	 * Number of commits
	 *
	 * For example, [2, 14, 25] indicates that there were 25 total commits, during the 2:00pm hour on Tuesdays.
	 * All times are based on the time zone of individual commits.
	 *
	 * @param   string  $owner  The owner of the repository.
	 * @param   string  $repo   The repository name.
	 *
	 * @since   1.0
	 *
	 * @return  object
	 */
	public function getPunchCard($owner, $repo)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/stats/punch_card';

		// Send the request.
		return $this->processResponse($this->client->get($this->fetchUrl($path)));
	}

	/**
	 * Process the response and decode it.
	 *
	 * @param   JHttpResponse  $response      The response.
	 * @param   integer        $expectedCode  The expected "good" code.
	 * @param   boolean        $decode        If the should be response be JSON decoded.
	 *
	 * @return  mixed
	 *
	 * @since   1.0
	 * @throws  \DomainException
	 */
	protected function processResponse(JHttpResponse $response, $expectedCode = 200, $decode = true)
	{
		if (202 == $response->code)
		{
			throw new \DomainException(
				'GitHub is building the statistics data. Please try again in a few moments.',
				$response->code
			);
		}

		return parent::processResponse($response, $expectedCode, $decode);
	}
}
