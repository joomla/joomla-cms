<?php
/**
 * @package    PatchTester
 *
 * @copyright  Copyright (C) 2011 - 2012 Ian MacLennan, Copyright (C) 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later
 */

defined('_JEXEC') or die;

/**
 * Methods supporting pull requests.
 *
 * @package  PatchTester
 * @since    1.0
 */
class PatchtesterModelPull extends JModelLegacy
{
	/**
	 * @var    JHttp
	 * @since  2.0
	 */
	protected $transport;

	/**
	 * Github object
	 *
	 * @var    PTGithub
	 * @since  2.0
	 */
	protected $github;

	/**
	 * Array containing top level non-production folders
	 *
	 * @var    array
	 * @since  2.0
	 */
	protected $nonProductionFolders = array('build', 'docs', 'installation', 'tests');

	/**
	 * Object containing the rate limit data
	 *
	 * @var    object
	 * @since  2.0
	 */
	protected $rate;

	/**
	 * Constructor
	 *
	 * @param   array  $config  An array of configuration options (name, state, dbo, table_path, ignore_request).
	 *
	 * @since   2.0
	 * @throws  RuntimeException
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		// Set up the JHttp object
		$options = new JRegistry;
		$options->set('userAgent', 'JPatchTester/2.0');
		$options->set('timeout', 120);

		// Make sure we can use the cURL driver
		$driver = JHttpFactory::getAvailableDriver($options, 'curl');

		if (!($driver instanceof JHttpTransportCurl))
		{
			throw new RuntimeException('Cannot use the PHP cURL adapter in this environment, cannot use patchtester', 500);
		}

		$this->transport = new JHttp($options, $driver);

		// Set up the Github object
		$params = JComponentHelper::getParams('com_patchtester');

		$options = new JRegistry;

		// Set the username and password if set in the params
		if ($params->get('gh_user', '') && $params->get('gh_password'))
		{
			$options->set('api.username', $params->get('gh_user', ''));
			$options->set('api.password', $params->get('gh_password', ''));
		}

		$this->github = new PTGithub($options);

		// Store the rate data for reuse during this request cycle
		$this->rate = $this->github->account->getRateLimit()->rate;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @note    Calling getState() in this method will result in recursion.
	 * @since   1.0
	 */
	protected function populateState()
	{
		// Load the parameters.
		$params = JComponentHelper::getParams('com_patchtester');
		$this->setState('params', $params);
		$this->setState('github_user', $params->get('org', 'joomla'));
		$this->setState('github_repo', $params->get('repo', 'joomla-cms'));

		parent::populateState();
	}

	/**
	 * Method to parse a patch and extract the affected files
	 *
	 * @param   string  $patch  Patch file to parse
	 *
	 * @return  array  Array of files within a patch
	 *
	 * @since   1.0
	 */
	protected function parsePatch($patch)
	{
		$state = 0;
		$files = array();

		$lines = explode("\n", $patch);

		foreach ($lines AS $line)
		{
			switch ($state)
			{
				case 0:
					if (strpos($line, 'diff --git') === 0)
					{
						$state = 1;
					}

					$file         = new stdClass;
					$file->action = 'modified';

					break;

				case 1:
					if (strpos($line, 'index') === 0)
					{
						$file->index = substr($line, 6);
					}

					if (strpos($line, '---') === 0)
					{
						$file->old = substr($line, 6);
					}

					if (strpos($line, '+++') === 0)
					{
						$file->new = substr($line, 6);
					}

					if (strpos($line, 'new file mode') === 0)
					{
						$file->action = 'added';
					}

					if (strpos($line, 'deleted file mode') === 0)
					{
						$file->action = 'deleted';
					}

					if (strpos($line, '@@') === 0)
					{
						$state   = 0;

						/*
						 * Check if the patch tester is running in a production environment
						 * If so, do not patch certain files as errors will be thrown
						 */
						if (!file_exists(JPATH_ROOT . '/installation/CHANGELOG'))
						{
							$filePath = explode('/', $file->new);

							if (in_array($filePath[0], $this->nonProductionFolders))
							{
								continue;
							}
						}

						$files[] = $file;
					}

					break;
			}
		}

		return $files;
	}

	/**
	 * Patches the code with the supplied pull request
	 *
	 * @param   integer  $id  ID of the pull request to apply
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 * @throws  Exception
	 */
	public function apply($id)
	{
		// Only act if there are API hits remaining
		if ($this->rate->remaining > 0)
		{
			$pull = $this->github->pulls->get($this->getState('github_user'), $this->getState('github_repo'), $id);

			if (is_null($pull->head->repo))
			{
				throw new Exception(JText::_('COM_PATCHTESTER_REPO_IS_GONE'));
			}

			$patch = $this->transport->get($pull->diff_url)->body;

			$files = $this->parsePatch($patch);

			if (!$files)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('COM_PATCHTESTER_NO_FILES_TO_PATCH', 'message'));

				return true;
			}

			foreach ($files as $file)
			{
				if ($file->action == 'deleted' && !file_exists(JPATH_ROOT . '/' . $file->old))
				{
					throw new Exception(sprintf(JText::_('COM_PATCHTESTER_FILE_DELETED_DOES_NOT_EXIST_S'), $file->old));
				}

				if ($file->action == 'added' || $file->action == 'modified')
				{
					// If the backup file already exists, we can't apply the patch
					if (file_exists(JPATH_COMPONENT . '/backups/' . md5($file->new) . '.txt'))
					{
						throw new Exception(sprintf(JText::_('COM_PATCHTESTER_CONFLICT_S'), $file->new));
					}

					if ($file->action == 'modified' && !file_exists(JPATH_ROOT . '/' . $file->old))
					{
						throw new Exception(sprintf(JText::_('COM_PATCHTESTER_FILE_MODIFIED_DOES_NOT_EXIST_S'), $file->old));
					}

					$url = 'https://raw.github.com/' . $pull->head->user->login . '/' . $pull->head->repo->name . '/' . $pull->head->ref . '/' . $file->new;

					$file->body = $this->transport->get($url)->body;
				}
			}

			jimport('joomla.filesystem.file');

			// At this point, we have ensured that we have all the new files and there are no conflicts
			foreach ($files as $file)
			{
				// We only create a backup if the file already exists
				if ($file->action == 'deleted' || (file_exists(JPATH_ROOT . '/' . $file->new) && $file->action == 'modified'))
				{
					if (!JFile::copy(JPath::clean(JPATH_ROOT . '/' . $file->old), JPATH_COMPONENT . '/backups/' . md5($file->old) . '.txt'))
					{
						throw new Exception(
							sprintf('Can not copy file %s to %s', JPATH_ROOT . '/' . $file->old, JPATH_COMPONENT . '/backups/' . md5($file->old) . '.txt')
						);
					}
				}

				switch ($file->action)
				{
					case 'modified':
					case 'added':
						if (!JFile::write(JPath::clean(JPATH_ROOT . '/' . $file->new), $file->body))
						{
							throw new Exception(sprintf('Can not write the file: %s', JPATH_ROOT . '/' . $file->new));
						}

						break;

					case 'deleted':
						if (!JFile::delete(JPATH::clean(JPATH_ROOT . '/' . $file->old)))
						{
							throw new Exception(sprintf('Can not delete the file: %s', JPATH_ROOT . '/' . $file->old));
						}

						break;
				}
			}

			$table                  = JTable::getInstance('tests', 'PatchTesterTable');
			$table->pull_id         = $pull->number;
			$table->data            = json_encode($files);
			$table->patched_by      = JFactory::getUser()->id;
			$table->applied         = 1;
			$table->applied_version = JVERSION;

			if (!$table->store())
			{
				throw new Exception($table->getError());
			}
		}
		else
		{
			throw new Exception(JText::sprintf('COM_PATCHTESTER_API_LIMIT_ACTION', JFactory::getDate($this->rate->reset)));
		}

		return true;
	}

	/**
	 * Reverts the specified pull request
	 *
	 * @param   integer  $id  ID of the pull request to Reverts
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 * @throws  Exception
	 */
	public function revert($id)
	{
		$table = JTable::getInstance('tests', 'PatchTesterTable');
		$table->load($id);

		// We don't want to restore files from an older version
		if ($table->applied_version != JVERSION)
		{
			$table->delete();

			return $this;
		}

		$files = json_decode($table->data);

		if (!$files)
		{
			throw new Exception(sprintf(JText::_('%s - Error retrieving table data (%s)'), __METHOD__, htmlentities($table->data)));
		}

		jimport('joomla.filesystem.file');

		foreach ($files as $file)
		{
			switch ($file->action)
			{
				case 'deleted':
				case 'modified':
					if (!JFile::copy(JPATH_COMPONENT . '/backups/' . md5($file->old) . '.txt', JPATH_ROOT . '/' . $file->old))
					{
						throw new Exception(
							sprintf(
								JText::_('Can not copy file %s to %s'),
								JPATH_COMPONENT . '/backups/' . md5($file->old) . '.txt',
								JPATH_ROOT . '/' . $file->old
							)
						);
					}

					if (!JFile::delete(JPATH_COMPONENT . '/backups/' . md5($file->old) . '.txt'))
					{
						throw new Exception(sprintf(JText::_('Can not delete the file: %s'), JPATH_COMPONENT . '/backups/' . md5($file->old) . '.txt'));
					}

					break;

				case 'added':
					if (!JFile::delete(JPath::clean(JPATH_ROOT . '/' . $file->new)))
					{
						throw new Exception(sprintf(JText::_('Can not delete the file: %s'), JPATH_ROOT . '/' . $file->new));
					}

					break;
			}
		}

		$table->delete();

		return true;
	}
}
