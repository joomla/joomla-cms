#!/usr/bin/php
<?php
/**
 * An example command line application built on the Joomla Platform.
 *
 * To run this example, adjust the executable path above to suite your operating system,
 * make this file executable and run the file.
 *
 * Alternatively, run the file using:
 *
 * php -f run.php
 *
 * @package    Joomla.Examples
 * @copyright  Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Setup the path related constants.
define('JPATH_BASE', dirname(__FILE__));

// Bootstrap the application.
require realpath('../libraries/import.php');

/**
 * Joomla Platform Changelog builder.
 *
 * This application builds the HTML version of the Joomla Platform change log from the Github API
 * that is used in news annoucements.
 *
 * @package     Joomla.Examples
 * @subpackage  Changlog
 * @since       12.1
 */
class Changelog extends JApplicationCli
{
	/**
	 * The github API object.
	 *
	 * @var    JGithub
	 * @since  12.1
	 */
	protected $api;

	/**
	 * Debug mode switch.
	 *
	 * @var    boolean
	 * @since  12.1
	 */
	protected $debug = false;

	/**
	 * An array of output buffers.
	 *
	 * @var    array
	 * @since  12.1
	 */
	protected $buffers = array();

	/**
	 * Overrides JGithub constructor to initialise the api property.
	 *
	 * @param   mixed  $input       An optional argument to provide dependency injection for the application's
	 *                              input object.  If the argument is a JInputCli object that object will become
	 *                              the application's input object, otherwise a default input object is created.
	 * @param   mixed  $config      An optional argument to provide dependency injection for the application's
	 *                              config object.  If the argument is a JRegistry object that object will become
	 *                              the application's config object, otherwise a default config object is created.
	 * @param   mixed  $dispatcher  An optional argument to provide dependency injection for the application's
	 *                              event dispatcher.  If the argument is a JDispatcher object that object will become
	 *                              the application's event dispatcher, if it is null then the default event dispatcher
	 *                              will be created based on the application's loadDispatcher() method.
	 *
	 * @see     loadDispatcher()
	 * @since   11.1
	 */
	public function __construct(JInputCli $input = null, JRegistry $config = null, JDispatcher $dispatcher = null)
	{
		parent::__construct($input, $config, $dispatcher);

		$options = new JRegistry;
		$options->set('headers.Accept', 'application/vnd.github.html+json');
		$this->api = new JGithub($options);
	}

	/**
	 * Execute the application.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function execute()
	{
		// Check if we just want the help page.
		if ($this->input->get('h'))
		{
			$this->help();

			return;
		}

		// Check for debug mode.
		$this->debug = $this->input->getBool('d');

		// Set the maximum number of pages (and runaway failsafe).
		$cutoff = 100;
		$page = 1;

		// Check if we only want to get the latest version information.
		$latestOnly = $this->input->get('l');

		// Initialise the version cutoffs.
		$versions = array(
			0 => '12.1',
			653 => '11.4',
			310 => '11.3',
			140 => '11.2',
			72 => '11.1',
		);

		// Initialise arrays and metrics.
		$log = array();
		$userCount = array();
		$pullCount = 0;
		$mergedBy = array();
		$labelled = array();

		// Set the current version.
		$version = $versions[0];

		while ($cutoff--)
		{
			// Get a page of the closed issues.
			$issues = $this->getIssues($page++);

			// Check if we've gone past the last page.
			if (empty($issues))
			{
				break;
			}

			// Loop through each pull.
			foreach ($issues as $i => $issue)
			{
				$this->out(sprintf(' %03d ', $i + 1), false);

				// Check if the issue is a pull request.
				if (empty($issue->pull_request->html_url))
				{
					$this->out('Skipped; no pull request url.');
					continue;
				}

				// Change the version
				if (isset($versions[$issue->number]) || ($this->debug && $pullCount > 0))
				{
					// Populate buffers.
					$this->setBuffer("$version.userCount", $userCount);
					$this->setBuffer("$version.pullCount", $pullCount, false);
					$this->setBuffer("$version.mergedBy", $mergedBy);
					$this->setBuffer("$version.labelled", $labelled);

					// Reset counters.
					$pullCount = 0;
					$userCount = array();
					$mergedBy = array();
					$labelled = array();

					// Break if we only want the latest version.
					if ($latestOnly || $this->debug)
					{
						break 2;
					}

					// Increment version.
					$version = $versions[$issue->number];
				}

				// Get specific information about the pull request.
				$data = $this->getPull($issue->number);

				// Check if merged.
				if (!$data->merged || $data->commits == 0)
				{
					$this->out(' - not merged.');
					continue;
				}

				// Check if the issue is labelled.
				foreach ($issue->labels as $label)
				{
					if (!isset($labelled[$label->name]))
					{
						$labelled[$label->name] = array();
					}
					$labelled[$label->name][] = '<a href="' . $issue->html_url . '">' . $issue->title . '</a>';
				}

				// Prepare the link to the pull.
				$html = '[<a href="' . $issue->html_url . '" title="Closed ' . $issue->closed_at . '">';
				$html .= '#' . $issue->number;
				$html .= '</a>] <strong>' . $issue->title . '</strong>';
				$html .= ' (<a href="https://github.com/' . $issue->user->login . '">' . $issue->user->login . '</a>)';

				if (trim($data->body_html))
				{
					$html .= $data->body_html;
				}

				$this->setBuffer("$version.log", $html);

				if (!isset($userCount[$issue->user->login]))
				{
					$userCount[$issue->user->login] = 0;
				}
				$userCount[$issue->user->login]++;
				$pullCount++;

				if (!isset($mergedBy[$data->merged_by->login]))
				{
					$mergedBy[$data->merged_by->login] = 0;
				}
				$mergedBy[$data->merged_by->login]++;

				$this->out(' - ok');
			}
		}

		// Check if the output folder exists.
		if (!is_dir('./docs'))
		{
			mkdir('./docs');
		}

		// Write the file.
		file_put_contents('./docs/changelog.html', $this->render($latestOnly ? array($versions[0]) : $versions));

		// Close normally.
		$this->close();
	}

	/**
	 * Display the help text for the app.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	public function help()
	{
		$this->out();
		$this->out('Joomla Platform Changelog');
		$this->out('-------------------------');
		$this->out();
		$this->out('Usage: ./changelog [-l]');
		$this->out();
		$this->out('  -l  Build the changelog for the latest version only.');
		$this->out('  -d  Debug mode. Test one issue.');
		$this->out();
		$this->out('Output is sent to docs/changelog.html');
		$this->out();
	}

	/**
	 * Gets a named buffer.
	 *
	 * @param   string  $name  the name of the buffer.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	protected function getBuffer($name)
	{
		if (isset($this->buffers[$name]))
		{
			return $this->buffers[$name];
		}
		else
		{
			return '';
		}
	}

	/**
	 * Get a page of issue data.
	 *
	 * @param   integer  $page  The page number.
	 *
	 * @return  array
	 *
	 * @since   12.1
	 */
	protected function getIssues($page)
	{
		$this->out(sprintf('Getting issues page #%02d.', $page));
		$this->out(str_pad('', 40, '-'));

		$issues = $this->api->issues
			->getListByRepository('joomla', 'joomla-platform', null, 'closed', null, null, null, 'updated', 'desc', null, $page, 100);

		$this->out(sprintf('Got %s issues.', count($issues)));

		return $issues;
	}

	/**
	 * Get information about a specific pull request.
	 *
	 * @param   integer  $id  The GitHub pull request number.
	 *
	 * @return  object
	 *
	 * @since   12.1
	 */
	protected function getPull($id)
	{
		$this->out(sprintf('Getting info for pull %6d', $id), false);

		return $this->api->pulls->get('joomla', 'joomla-platform', $id);
	}

	/**
	 * Renders the output.
	 *
	 * @param   array  $versions  An array of the versions to render.
	 *
	 * @return  string
	 *
	 * @since   12.1
	 */
	protected function render(array $versions)
	{
		$html = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
		<html>
			<head>
				<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
				<title>Joomla Platform pull request log</title>
				</head>
			<body>';

		foreach ($versions as $version)
		{
			// Print the version number.
			$html .= PHP_EOL . '	<h1>' . $version . '</h1>';

			// Print out the labelled version of the changelog first.
			$labelled = $this->getBuffer("$version.labelled");

			if ($labelled)
			{
				foreach ($labelled as $label => $links)
				{
					$html .= PHP_EOL . "<h2>$label</h2>";
					$html .= PHP_EOL . '<ul>';

					foreach ($links as $link)
					{
						$html .= PHP_EOL . "<li>$link</li>";
					}

					$html .= PHP_EOL . '</ul>';
				}
			}

			// Print out the detailed version of the changelog.
			$log = $this->getBuffer("$version.log");
			$html .= PHP_EOL . '<h2>The following pull requests made by community contributors were merged:</h2>';
			$html .= PHP_EOL . '<ol>';

			foreach ($log as $issue)
			{
				$html .= PHP_EOL . "<li>$issue</li>";
			}
			$html .= PHP_EOL . '</ol>';

			// Print out the user-pull statistics.
			$userCount = $this->getBuffer("$version.userCount");
			arsort($userCount);
			$pullCount = $this->getBuffer("$version.pullCount");

			$html .= PHP_EOL . sprintf('<h4>%d pull requests.</h4>', $pullCount);
			$html .= PHP_EOL . '<ol>';

			foreach ($userCount as $user => $count)
			{
				$html .= PHP_EOL . sprintf('<li><a href="https://github.com/%1$s">%1$s</a>: %2$d</li>', $user, $count);
			}
			$html .= PHP_EOL . '	</ol>';

			// Print out the admin-merge statistics.
			$mergedBy = $this->getBuffer("$version.mergedBy");
			arsort($mergedBy);

			$html .= PHP_EOL . '<h4>Merged by:</h4>';
			$html .= PHP_EOL . '<ol>';

			foreach ($mergedBy as $user => $count)
			{
				$html .= PHP_EOL . sprintf('<li><a href="https://github.com/%1$s">%1$s</a>: %2$d</li>', $user, $count);
			}
			$html .= PHP_EOL . '</ol>';
		}

		$html .= PHP_EOL . '</body>';
		$html .= PHP_EOL . '</html>';

		return $html;
	}

	/**
	 * Sets a named buffer.
	 *
	 * @param   string   $name    The name of the buffer.
	 * @param   mixed    $text    The text to put into/append to the buffer.
	 * @param   boolean  $append  Append to the array buffer.
	 *
	 * @return  void
	 *
	 * @since   12.1
	 */
	protected function setBuffer($name, $text, $append = true)
	{
		if (!isset($this->buffers[$name]))
		{
			$this->buffers[$name] = array();
		}

		if (is_array($text) || !$append)
		{
			$this->buffers[$name] = $text;
		}
		else
		{
			$this->buffers[$name][] = $text;
		}
	}
}

// Catch any exceptions thrown.
try
{
	JApplicationCli::getInstance('Changelog')->execute();
}
catch (Exception $e)
{
	// An exception has been caught, just echo the message.
	fwrite(STDOUT, $e->getMessage() . "\n");
	exit($e->getCode());
}
