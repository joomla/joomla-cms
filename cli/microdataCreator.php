<?php
/**
 * @package    Joomla.Cli
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * This is a CRON script which should be called from the command-line, not the web.
 * For example something: /usr/bin/php /path/to/site/cli/microdataCreator.php
 * The script makes more that 500 http requests so it's ok if it runs more than 5 minutes
 */

// Set flag that this is a parent file.
const _JEXEC = 1;

error_reporting(E_ALL | E_NOTICE);
ini_set('display_errors', 1);

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_LIBRARIES . '/import.legacy.php';

/**
 * A webbot that crawls the http://Schema.org website
 * to retrieve all available Types and Properties in order to create
 * a JSON (used by the JMicrodata library) file containing all that information
 *
 * @package  Joomla.CLI
 * @since    3.2
 */
class MicrodataCreator extends JApplicationCli
{
	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function doExecute()
	{
		// Import the dependencies
		jimport('joomla.http');

		// Initialize the JHttp object
		$http = new JHttp;

		$this->out('Retrieving all available Types...');

		// Retrieve the HTML containing all available Types
		$htmlTypes = $http->get('https://schema.org/docs/full.html');

		$this->out('Parsing all available Types...', true);

		// Retrieve all available Types
		$types = $this->parseTypes($htmlTypes->body);

		$this->out('Available Types: ' . count($types), true);

		// For each Type retrieve all available Properties and information
		foreach ($types as $typeName)
		{
			// Retrieve the Type HTML
			$this->out('Retrieving Type: ' . $typeName, true);
			$type = $http->get('http://schema.org/' . $typeName);
			$this->out('Parsing Type: ' . $typeName, true);
			$type = $this->parseType($type->body, $typeName);

			$types[$typeName] = $type;
		}

		// Create the JSON file
		$fileName = 'types.json';
		$this->out("Creating the $fileName file...", true);
		$handle   = fopen(JPATH_LIBRARIES . '/joomla/microdata/' . $fileName, 'w');

		$code = json_encode($types);

		// Write the class file and close the handle
		fwrite($handle, $code);
		fclose($handle);

		$this->out("Created the $fileName file", true);
	}

	/**
	 * Retrieve all available Types
	 *
	 * @param   string  $html  The retrieved HTML from the Type page
	 * 
	 * @return	array
	 */
	protected function parseTypes($html)
	{
		// Create a new DOMDocument
		$doc = new DOMDocument;
		$doc->loadHTML($html);

		// Create a new DOMXPath, to make XPath queries
		$xpath = new DOMXPath($doc);

		$nodeList = $xpath->query("//td[@class='tc']/a");

		$types = array();

		foreach ($nodeList as $node)
		{
			// Sanitize the Type
			$type = str_replace('*', '', $node->nodeValue);

			$types[$type] = $type;
		}

		return $types;
	}

	/**
	 * Retrieve all available information about that Type
	 * such as comment, inherences and properties
	 *
	 * @param   string  $html      The retrieved HTML from the Type page
	 * @param   string  $typeName  The Type name
	 *
	 * @return  array
	 */
	protected function parseType($html, $typeName)
	{
		// Create a new DOMDocument
		$doc = new DOMDocument;

		// Modify state
		$libxmlPreviousState = libxml_use_internal_errors(true);

		// Parse
		$doc->loadHTML($html);

		// Handle errors
		libxml_clear_errors();

		// Restore previous state
		libxml_use_internal_errors($libxmlPreviousState);

		// Create a new DOMXPath, to make XPath queries
		$xpath = new DOMXPath($doc);

		$type['extends']    = $this->parseTypeExtends($xpath);
		$type['properties'] = $this->parseTypeProperties($xpath, $typeName);

		return $type;
	}

	/**
	 * Retrieve the Type comment
	 *
	 * @param   string  $xpath  The Document object where to search
	 *
	 * @return	string
	 */
	protected function parseTypeComment(DOMXPath $xpath)
	{
		$nodeList = $xpath->query("//div[@property='rdfs:comment']");

		$comment = '';

		foreach ($nodeList as $node)
		{
			$comment = $node->nodeValue;
		}

		return $comment;
	}

	/**
	 * Retrieve the Type inherence if available
	 *
	 * @param   string  $xpath  The Document object where to search
	 *
	 * @return	string
	 */
	protected function parseTypeExtends(DOMXPath $xpath)
	{
		$nodeList = $xpath->query("//h1[@class='page-title']");

		foreach ($nodeList as $node)
		{
			$tmpExtends = $node->nodeValue;
		}

		// Search for the Extended Type if available
		$types = explode('>', trim($tmpExtends));

		if (count($types) > 1)
		{
			return trim($types[count($types) - 2]);
		}

		return '';
	}

	/**
	 * Retrieve all available Properties of a given Type
	 *
	 * @param   object  $xpath     The Document object where to search
	 * @param   string  $typeName  The Type name
	 *
	 * @return	array
	 */
	protected function parseTypeProperties(DOMXPath $xpath, $typeName)
	{
		// Control if properties available
		$nodeList = $xpath->query("(//thead[@class='supertype'])[last()]//a");

		foreach ($nodeList as $node)
		{
			// Return null if there is no property available
			if ($node->nodeValue != $typeName)
			{
				return array();
			}
		}

		// Retrieve all Type Properties
		$nodeList = $xpath->query("(//tbody[@class='supertype'])[last()]/tr");

		$properties = array();

		foreach ($nodeList as $node)
		{
			$values = array();
			$childNodes = $node->childNodes;

			// Retrive all available information
			foreach ($childNodes as $node)
			{
				if ($value = trim($node->nodeValue))
				{
					$values[] = $value;
				}
			}

			// Create an array with the expected Types and sanitize
			$expectedTypes = preg_replace('/\s+/', ' ', $values[1]);
			$expectedTypes = explode(' or ', trim($expectedTypes));

			// Create the final $property
			$properties[$values[0]] = array(
				'expectedTypes' => $expectedTypes
			);
		}

		if (empty($properties))
		{
			return array();
		}

		return $properties;
	}
}

JApplicationCli::getInstance('MicrodataCreator')->execute();
