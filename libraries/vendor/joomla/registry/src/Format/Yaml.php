<?php
/**
 * Part of the Joomla Framework Registry Package
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Registry\Format;

use Joomla\Registry\AbstractRegistryFormat;
use Symfony\Component\Yaml\Parser as SymfonyYamlParser;
use Symfony\Component\Yaml\Dumper as SymfonyYamlDumper;

/**
 * YAML format handler for Registry.
 *
 * @since  1.0
 */
class Yaml extends AbstractRegistryFormat
{
	/**
	 * The YAML parser class.
	 *
	 * @var    \Symfony\Component\Yaml\Parser;
	 *
	 * @since  1.0
	 */
	private $parser;

	/**
	 * The YAML dumper class.
	 *
	 * @var    \Symfony\Component\Yaml\Dumper;
	 *
	 * @since  1.0
	 */
	private $dumper;

	/**
	 * Construct to set up the parser and dumper
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		$this->parser = new SymfonyYamlParser;
		$this->dumper = new SymfonyYamlDumper;
	}

	/**
	 * Converts an object into a YAML formatted string.
	 * We use json_* to convert the passed object to an array.
	 *
	 * @param   object  $object   Data source object.
	 * @param   array   $options  Options used by the formatter.
	 *
	 * @return  string  YAML formatted string.
	 *
	 * @since   1.0
	 */
	public function objectToString($object, $options = array())
	{
		$array = json_decode(json_encode($object), true);

		return $this->dumper->dump($array, 2, 0);
	}

	/**
	 * Parse a YAML formatted string and convert it into an object.
	 * We use the json_* methods to convert the parsed YAML array to an object.
	 *
	 * @param   string  $data     YAML formatted string to convert.
	 * @param   array   $options  Options used by the formatter.
	 *
	 * @return  object  Data object.
	 *
	 * @since   1.0
	 */
	public function stringToObject($data, array $options = array())
	{
		$array = $this->parser->parse(trim($data));

		return json_decode(json_encode($array));
	}
}
