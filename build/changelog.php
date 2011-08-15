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
				$pulls = $this->getPulls($page++);

				// Check if we've gone past the last page.
				if (empty($pulls))
				{
					// No more data.
					break;
				}

				// Loop through each pull.
				foreach ($pulls as $pull)
				{
					// Check if the pull has been merged.
					if (empty($pull->pull_request->html_url))
					{
						// This pull has not been merged yet.
						continue;
					}

					$doc->startElement('itemizedlist');
					$doc->startElement('listitem');

					$doc->startElement('para');

					// Prepare the link to the pull.
					$doc->text('[');
					$doc->startElement('ulink');
					$doc->writeAttribute('url', $pull->url);
					$doc->writeAttribute('title', 'Closed '.$pull->closed_at);
					$doc->text('#'.$pull->number);
					$doc->endElement(); // ulink
					$doc->text('] '.$pull->title.' (');

					// Prepare the link to the author.
					$doc->startElement('ulink');
					$doc->writeAttribute('url', $pull->user->url);
					$doc->text($pull->user->login);
					$doc->endElement(); // ulink
					$doc->text(')');

					$doc->endElement(); // para

					if (trim($pull->body))
					{
						$doc->startElement('para');
						$doc->text($pull->body);
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
	 * Get a page of pull data.
	 *
	 * @param   integer  The page number.
	 *
	 * @return  array
	 *
	 * @since   11.3
	 */
	protected function getPulls($page)
	{
		$http = new JHttp;
		$r = $http->get(
			'https://api.github.com/repos/joomla/joomla-platform/issues?state=closed&sort=updated&direction=desc&page='.$page.'&per_page=100'
		);

		return json_decode($r->body);
	}
}

JCli::getInstance('Changelog')->execute();
