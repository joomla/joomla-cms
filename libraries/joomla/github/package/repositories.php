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
 * GitHub API Activity class for the Joomla Platform.
 *
 * @since       3.3 (CMS)
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 *
 * @documentation  https://developer.github.com/v3/repos
 *
 * @property-read  JGithubPackageRepositoriesCollaborators  $collaborators  GitHub API object for collaborators.
 * @property-read  JGithubPackageRepositoriesComments       $comments       GitHub API object for comments.
 * @property-read  JGithubPackageRepositoriesCommits        $commits        GitHub API object for commits.
 * @property-read  JGithubPackageRepositoriesContents       $contents       GitHub API object for contents.
 * @property-read  JGithubPackageRepositoriesDownloads      $downloads      GitHub API object for downloads.
 * @property-read  JGithubPackageRepositoriesForks          $forks          GitHub API object for forks.
 * @property-read  JGithubPackageRepositoriesHooks          $hooks          GitHub API object for hooks.
 * @property-read  JGithubPackageRepositoriesKeys           $keys           GitHub API object for keys.
 * @property-read  JGithubPackageRepositoriesMerging        $merging        GitHub API object for merging.
 * @property-read  JGithubPackageRepositoriesStatuses       $statuses       GitHub API object for statuses.
 */
class JGithubPackageRepositories extends JGithubPackage
{
	protected $name = 'Repositories';

	protected $packages = array('collaborators', 'comments', 'commits', 'contents', 'downloads', 'forks', 'hooks', 'keys', 'merging', 'statuses');

	/**
	 * List your repositories.
	 *
	 * List repositories for the authenticated user.
	 *
	 * @param   string  $type       Sort type. all, owner, public, private, member. Default: all.
	 * @param   string  $sort       Sort field. created, updated, pushed, full_name, default: full_name.
	 * @param   string  $direction  Sort direction. asc or desc, default: when using full_name: asc, otherwise desc.
	 *
	 * @throws RuntimeException
	 *
	 * @return object
	 */
	public function getListOwn($type = 'all', $sort = 'full_name', $direction = '')
	{
		if (false == in_array($type, array('all', 'owner', 'public', 'private', 'member')))
		{
			throw new RuntimeException('Invalid type');
		}

		if (false == in_array($sort, array('created', 'updated', 'pushed', 'full_name')))
		{
			throw new RuntimeException('Invalid sort field');
		}

		// Sort direction default: when using full_name: asc, otherwise desc.
		$direction = ($direction) ? : (('full_name' == $sort) ? 'asc' : 'desc');

		if (false == in_array($direction, array('asc', 'desc')))
		{
			throw new RuntimeException('Invalid sort order');
		}

		// Build the request path.
		$path = '/user/repos'
			. '?type=' . $type
			. '&sort=' . $sort
			. '&direction=' . $direction;

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * List user repositories.
	 *
	 * List public repositories for the specified user.
	 *
	 * @param   string  $user       The user name.
	 * @param   string  $type       Sort type. all, owner, member. Default: all.
	 * @param   string  $sort       Sort field. created, updated, pushed, full_name, default: full_name.
	 * @param   string  $direction  Sort direction. asc or desc, default: when using full_name: asc, otherwise desc.
	 *
	 * @throws RuntimeException
	 *
	 * @return object
	 */
	public function getListUser($user, $type = 'all', $sort = 'full_name', $direction = '')
	{
		if (false == in_array($type, array('all', 'owner', 'member')))
		{
			throw new RuntimeException('Invalid type');
		}

		if (false == in_array($sort, array('created', 'updated', 'pushed', 'full_name')))
		{
			throw new RuntimeException('Invalid sort field');
		}

		// Sort direction default: when using full_name: asc, otherwise desc.
		$direction = ($direction) ? : (('full_name' == $sort) ? 'asc' : 'desc');

		if (false == in_array($direction, array('asc', 'desc')))
		{
			throw new RuntimeException('Invalid sort order');
		}

		// Build the request path.
		$path = '/users/' . $user . '/repos'
			. '?type=' . $type
			. '&sort=' . $sort
			. '&direction=' . $direction;

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * List organization repositories.
	 *
	 * List repositories for the specified org.
	 *
	 * @param   string  $org   The name of the organization.
	 * @param   string  $type  Sort type. all, public, private, forks, sources, member. Default: all.
	 *
	 * @throws RuntimeException
	 *
	 * @return object
	 */
	public function getListOrg($org, $type = 'all')
	{
		if (false == in_array($type, array('all', 'public', 'private', 'forks', 'sources', 'member')))
		{
			throw new RuntimeException('Invalid type');
		}

		// Build the request path.
		$path = '/orgs/' . $org . '/repos'
			. '?type=' . $type;

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * List all repositories.
	 *
	 * This provides a dump of every repository, in the order that they were created.
	 *
	 * @param   integer  $id  The integer ID of the last Repository that you’ve seen.
	 *
	 * @throws RuntimeException
	 *
	 * @return object
	 */
	public function getList($id = 0)
	{
		// Build the request path.
		$path = '/repositories';
		$path .= ($id) ? '?since=' . (int) $id : '';

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Create a new repository for the authenticated user or an organization.
	 * OAuth users must supply repo scope.
	 *
	 * @param   string   $name               The repository name.
	 * @param   string   $org                The organization name (if needed).
	 * @param   string   $description        The repository description.
	 * @param   string   $homepage           The repository homepage.
	 * @param   boolean  $private            Set true to create a private repository, false to create a public one.
	 *                                       Creating private repositories requires a paid GitHub account.
	 * @param   boolean  $hasIssues          Set true to enable issues for this repository, false to disable them.
	 * @param   boolean  $hasWiki            Set true to enable the wiki for this repository, false to disable it.
	 * @param   boolean  $hasDownloads       Set true to enable downloads for this repository, false to disable them.
	 * @param   integer  $teamId             The id of the team that will be granted access to this repository.
	 *                                       This is only valid when creating a repo in an organization.
	 * @param   boolean  $autoInit           true to create an initial commit with empty README.
	 * @param   string   $gitignoreTemplate  Desired language or platform .gitignore template to apply.
	 *                                       Use the name of the template without the extension. For example,
	 *                                       “Haskell” Ignored if auto_init parameter is not provided.
	 *
	 * @return object
	 */
	public function create($name, $org = '', $description = '', $homepage = '', $private = false, $hasIssues = false,
		$hasWiki = false, $hasDownloads = false, $teamId = 0, $autoInit = false, $gitignoreTemplate = '')
	{
		$path = ($org)
			// Create a repository for an organization
			? '/orgs/' . $org . '/repos'
			// Create a repository for a user
			: '/user/repos';

		$data = array(
			'name'               => $name,
			'description'        => $description,
			'homepage'           => $homepage,
			'private'            => $private,
			'has_issues'         => $hasIssues,
			'has_wiki'           => $hasWiki,
			'has_downloads'      => $hasDownloads,
			'team_id'            => $teamId,
			'auto_init'          => $autoInit,
			'gitignore_template' => $gitignoreTemplate,
		);

		// Send the request.
		return $this->processResponse(
			$this->client->post($this->fetchUrl($path), json_encode($data)),
			201
		);
	}

	/**
	 * Get a repository.
	 *
	 * @param   string  $owner  Repository owner.
	 * @param   string  $repo   Repository name.
	 *
	 * @return object
	 */
	public function get($owner, $repo)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo;

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Edit a repository.
	 *
	 * @param   string   $owner          Repository owner.
	 * @param   string   $repo           Repository name.
	 * @param   string   $name           The repository name.
	 * @param   string   $description    The repository description.
	 * @param   string   $homepage       The repository homepage.
	 * @param   boolean  $private        Set true to create a private repository, false to create a public one.
	 *                                   Creating private repositories requires a paid GitHub account.
	 * @param   boolean  $hasIssues      Set true to enable issues for this repository, false to disable them.
	 * @param   boolean  $hasWiki        Set true to enable the wiki for this repository, false to disable it.
	 * @param   boolean  $hasDownloads   Set true to enable downloads for this repository, false to disable them.
	 * @param   string   $defaultBranch  Update the default branch for this repository
	 *
	 * @return object
	 */
	public function edit($owner, $repo, $name, $description = '', $homepage = '', $private = false, $hasIssues = false,
		$hasWiki = false, $hasDownloads = false, $defaultBranch = '')
	{
		$path = '/repos/' . $owner . '/' . $repo;

		$data = array(
			'name'           => $name,
			'description'    => $description,
			'homepage'       => $homepage,
			'private'        => $private,
			'has_issues'     => $hasIssues,
			'has_wiki'       => $hasWiki,
			'has_downloads'  => $hasDownloads,
			'default_branch' => $defaultBranch,
		);

		// Send the request.
		return $this->processResponse(
			$this->client->patch($this->fetchUrl($path), json_encode($data))
		);
	}

	/**
	 * List contributors.
	 *
	 * @param   string   $owner  Repository owner.
	 * @param   string   $repo   Repository name.
	 * @param   boolean  $anon   Set to 1 or true to include anonymous contributors in results.
	 *
	 * @return object
	 */
	public function getListContributors($owner, $repo, $anon = false)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/contributors';

		$path .= ($anon) ? '?anon=true' : '';

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * List languages.
	 *
	 * List languages for the specified repository. The value on the right of a language is the number of bytes of code
	 * written in that language.
	 *
	 * @param   string  $owner  Repository owner.
	 * @param   string  $repo   Repository name.
	 *
	 * @return object
	 */
	public function getListLanguages($owner, $repo)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/languages';

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * List Teams
	 *
	 * @param   string  $owner  Repository owner.
	 * @param   string  $repo   Repository name.
	 *
	 * @return object
	 */
	public function getListTeams($owner, $repo)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/teams';

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * List Tags.
	 *
	 * @param   string  $owner  Repository owner.
	 * @param   string  $repo   Repository name.
	 *
	 * @return object
	 */
	public function getListTags($owner, $repo)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/tags';

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * List Branches.
	 *
	 * @param   string  $owner  Repository owner.
	 * @param   string  $repo   Repository name.
	 *
	 * @return object
	 */
	public function getListBranches($owner, $repo)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/branches';

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Get a Branch.
	 *
	 * @param   string  $owner   Repository owner.
	 * @param   string  $repo    Repository name.
	 * @param   string  $branch  Branch name.
	 *
	 * @return object
	 */
	public function getBranch($owner, $repo, $branch)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo . '/branches/' . $branch;

		// Send the request.
		return $this->processResponse(
			$this->client->get($this->fetchUrl($path))
		);
	}

	/**
	 * Delete a Repository.
	 *
	 * Deleting a repository requires admin access. If OAuth is used, the delete_repo scope is required.
	 *
	 * @param   string  $owner  Repository owner.
	 * @param   string  $repo   Repository name.
	 *
	 * @return object
	 */
	public function delete($owner, $repo)
	{
		// Build the request path.
		$path = '/repos/' . $owner . '/' . $repo;

		// Send the request.
		return $this->processResponse(
			$this->client->delete($this->fetchUrl($path))
		);
	}
}
