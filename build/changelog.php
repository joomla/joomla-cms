#!/usr/bin/php
<?php
/**
 * @package     Joomla.Build
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

// We are a valid Joomla entry point.
define('_JEXEC', 1);

// Setup the path related constants.
define('JPATH_BASE', dirname(__FILE__));

// Bootstrap the application.
require '../libraries/import.php';

jimport('joomla.application.cli');

/**
 * The command line application.
 *
 * @package		NewLifeInIT
 * @subpackage	cron
 */
class Changelog extends JCli
{
	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   11.3
	 */
	public function execute()
	{
		// Import dependancies.
		jimport('joomla.client.http');

		try
		{
			// Get a list of the merged pull requests.
			$merged = $this->getMergedPulls();

			$doc = new XMLWriter;
			$doc->openMemory();
			$doc->setIndent(true);
			$doc->setIndentString("\t");
			$doc->startDocument('1.0', 'utf-8');
			$doc->startElement('appendix');
			$doc->startElement('section');

			$cutoff = 10;
			$page = 1;

			while ($cutoff--)
			{
				// Get a page of issues.
				$issues = $this->getIssues($page++);

				// Check if we've gone past the last page.
				if (empty($issues))
				{
					break;
				}

				// Loop through each pull.
				foreach ($issues as $issue)
				{
					// Check if the issue has been merged.
					if (empty($issue->pull_request->html_url))
					{
						continue;
					}

					// Check if the pull has been merged.
					if (!in_array($issue->number, $merged))
					{
						continue;
					}

					$doc->startElement('itemizedlist');
					$doc->startElement('listitem');

					$doc->startElement('para');

					// Prepare the link to the pull.
					$doc->text('[');
					$doc->startElement('ulink');
					$doc->writeAttribute('url', $issue->url);
					$doc->writeAttribute('title', 'Closed '.$issue->closed_at);
					$doc->text('#'.$issue->number);
					$doc->endElement(); // ulink
					$doc->text('] '.$issue->title.' (');

					// Prepare the link to the author.
					$doc->startElement('ulink');
					$doc->writeAttribute('url', $issue->user->url);
					$doc->text($issue->user->login);
					$doc->endElement(); // ulink
					$doc->text(')');

					$doc->endElement(); // para

					if (trim($issue->body))
					{
						$doc->startElement('para');
						$doc->text($issue->body);
						$doc->endElement(); // para
					}

					$doc->endElement(); // listitem
					$doc->endElement(); // itemizedlist
				}
			}

			$doc->endElement(); // section
			$doc->endElement(); // appendix

			// Write the file.
			if (!is_dir('./docs'))
			{
				mkdir('./docs');
			}

			file_put_contents('./docs/changelog.xml', $doc->outputMemory());
		}
		catch (Exception $e)
		{
			$this->out($e->getMessage());
			$this->close($e->getCode());
		}

		// Close normally.
		$this->close();
	}

	/**
	 * Get a page of issue data.
	 *
	 * @param   integer  The page number.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	protected function getIssues($page)
	{
		$http = new JHttp;
		$r = $http->get(
			'https://api.github.com/repos/joomla/joomla-platform/issues?state=closed&sort=updated&direction=desc&page='.$page.'&per_page=100'
		);

		return json_decode($r->body);
	}

	/**
	 * Gets a list of the merged pull numbers.
	 *
	 * @param   integer  The pull/issue number.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	protected function getMergedPulls()
	{
		$cutoff = 10;
		$page = 1;
		$merged = array();

		while ($cutoff--)
		{
			$http = new JHttp;

			$r = $http->get(
				'https://api.github.com/repos/joomla/joomla-platform/pulls?state=closed&page='.$page++.'&per_page=100'
			);

			$pulls = json_decode($r->body);

			// Check if we've gone past the last page.
			if (empty($pulls))
			{
				break;
			}

			// Loop through each of the pull requests.
			foreach ($pulls as $pull)
			{
				// If merged, add to the white list.
				if ($pull->merged_at)
				{
					$merged[] = $pull->number;
				}
			}
		}

		return $merged;
	}
}

JCli::getInstance('Changelog')->execute();
