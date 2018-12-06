<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Filter;

defined('JPATH_PLATFORM') or die;

use Joomla\Filter\InputFilter as BaseInputFilter;
use Joomla\String\StringHelper;

/**
 * InputFilter is a class for filtering input from any data source
 *
 * Forked from the php input filter library by: Daniel Morris <dan@rootcube.com>
 * Original Contributors: Gianpaolo Racca, Ghislain Picard, Marco Wandschneider, Chris Tobin and Andrew Eddie.
 *
 * @since  1.7.0
 */
class InputFilter extends BaseInputFilter
{
	/**
	 * A flag for Unicode Supplementary Characters (4-byte Unicode character) stripping.
	 *
	 * @var    integer
	 *
	 * @since  3.5
	 */
	public $stripUSC = 0;

	/**
	 * Constructor for inputFilter class. Only first parameter is required.
	 *
	 * @param   array    $tagsArray   List of user-defined tags
	 * @param   array    $attrArray   List of user-defined attributes
	 * @param   integer  $tagsMethod  WhiteList method = 0, BlackList method = 1
	 * @param   integer  $attrMethod  WhiteList method = 0, BlackList method = 1
	 * @param   integer  $xssAuto     Only auto clean essentials = 0, Allow clean blacklisted tags/attr = 1
	 * @param   integer  $stripUSC    Strip 4-byte unicode characters = 1, no strip = 0, ask the database driver = -1
	 *
	 * @since   1.7.0
	 */
	public function __construct($tagsArray = array(), $attrArray = array(), $tagsMethod = 0, $attrMethod = 0, $xssAuto = 1, $stripUSC = -1)
	{
		// Make sure user defined arrays are in lowercase
		$tagsArray = array_map('strtolower', (array) $tagsArray);
		$attrArray = array_map('strtolower', (array) $attrArray);

		// Assign member variables
		$this->tagsArray = $tagsArray;
		$this->attrArray = $attrArray;
		$this->tagsMethod = $tagsMethod;
		$this->attrMethod = $attrMethod;
		$this->xssAuto = $xssAuto;
		$this->stripUSC = $stripUSC;
		/**
		 * If Unicode Supplementary Characters stripping is not set we have to check with the database driver. If the
		 * driver does not support USCs (i.e. there is no utf8mb4 support) we will enable USC stripping.
		 */
		if ($this->stripUSC === -1)
		{
			try
			{
				// Get the database driver
				$db = \JFactory::getDbo();

				// This trick is required to let the driver determine the utf-8 multibyte support
				$db->connect();

				// And now we can decide if we should strip USCs
				$this->stripUSC = $db->hasUTF8mb4Support() ? 0 : 1;
			}
			catch (\RuntimeException $e)
			{
				// Could not connect to MySQL. Strip USC to be on the safe side.
				$this->stripUSC = 1;
			}
		}
	}

	/**
	 * Returns an input filter object, only creating it if it doesn't already exist.
	 *
	 * @param   array    $tagsArray   List of user-defined tags
	 * @param   array    $attrArray   List of user-defined attributes
	 * @param   integer  $tagsMethod  WhiteList method = 0, BlackList method = 1
	 * @param   integer  $attrMethod  WhiteList method = 0, BlackList method = 1
	 * @param   integer  $xssAuto     Only auto clean essentials = 0, Allow clean blacklisted tags/attr = 1
	 * @param   integer  $stripUSC    Strip 4-byte unicode characters = 1, no strip = 0, ask the database driver = -1
	 *
	 * @return  InputFilter  The InputFilter object.
	 *
	 * @since   1.7.0
	 */
	public static function &getInstance($tagsArray = array(), $attrArray = array(), $tagsMethod = 0, $attrMethod = 0, $xssAuto = 1, $stripUSC = -1)
	{
		$sig = md5(serialize(array($tagsArray, $attrArray, $tagsMethod, $attrMethod, $xssAuto)));

		if (empty(self::$instances[$sig]))
		{
			self::$instances[$sig] = new InputFilter($tagsArray, $attrArray, $tagsMethod, $attrMethod, $xssAuto, $stripUSC);
		}

		return self::$instances[$sig];
	}

	/**
	 * Method to be called by another php script. Processes for XSS and
	 * specified bad code.
	 *
	 * @param   mixed   $source  Input string/array-of-string to be 'cleaned'
	 * @param   string  $type    The return type for the variable:
	 *                           INT:       An integer, or an array of integers,
	 *                           UINT:      An unsigned integer, or an array of unsigned integers,
	 *                           FLOAT:     A floating point number, or an array of floating point numbers,
	 *                           BOOLEAN:   A boolean value,
	 *                           WORD:      A string containing A-Z or underscores only (not case sensitive),
	 *                           ALNUM:     A string containing A-Z or 0-9 only (not case sensitive),
	 *                           CMD:       A string containing A-Z, 0-9, underscores, periods or hyphens (not case sensitive),
	 *                           BASE64:    A string containing A-Z, 0-9, forward slashes, plus or equals (not case sensitive),
	 *                           STRING:    A fully decoded and sanitised string (default),
	 *                           HTML:      A sanitised string,
	 *                           ARRAY:     An array,
	 *                           PATH:      A sanitised file path, or an array of sanitised file paths,
	 *                           TRIM:      A string trimmed from normal, non-breaking and multibyte spaces
	 *                           USERNAME:  Do not use (use an application specific filter),
	 *                           RAW:       The raw string is returned with no filtering,
	 *                           unknown:   An unknown filter will act like STRING. If the input is an array it will return an
	 *                                      array of fully decoded and sanitised strings.
	 *
	 * @return  mixed  'Cleaned' version of input parameter
	 *
	 * @since   1.7.0
	 */
	public function clean($source, $type = 'string')
	{
		// Strip Unicode Supplementary Characters when requested to do so
		if ($this->stripUSC)
		{
			// Alternatively: preg_replace('/[\x{10000}-\x{10FFFF}]/u', "\xE2\xAF\x91", $source) but it'd be slower.
			$source = $this->stripUSC($source);
		}

		// Handle the type constraint cases
		switch (strtoupper($type))
		{
			case 'INT':
			case 'INTEGER':
				$pattern = '/[-+]?[0-9]+/';

				if (is_array($source))
				{
					$result = array();

					// Iterate through the array
					foreach ($source as $eachString)
					{
						preg_match($pattern, (string) $eachString, $matches);
						$result[] = isset($matches[0]) ? (int) $matches[0] : 0;
					}
				}
				else
				{
					preg_match($pattern, (string) $source, $matches);
					$result = isset($matches[0]) ? (int) $matches[0] : 0;
				}

				break;
			case 'UINT':
				$pattern = '/[-+]?[0-9]+/';

				if (is_array($source))
				{
					$result = array();

					// Iterate through the array
					foreach ($source as $eachString)
					{
						preg_match($pattern, (string) $eachString, $matches);
						$result[] = isset($matches[0]) ? abs((int) $matches[0]) : 0;
					}
				}
				else
				{
					preg_match($pattern, (string) $source, $matches);
					$result = isset($matches[0]) ? abs((int) $matches[0]) : 0;
				}

				break;
			case 'FLOAT':
			case 'DOUBLE':
				$pattern = '/[-+]?[0-9]+(\.[0-9]+)?([eE][-+]?[0-9]+)?/';

				if (is_array($source))
				{
					$result = array();

					// Iterate through the array
					foreach ($source as $eachString)
					{
						preg_match($pattern, (string) $eachString, $matches);
						$result[] = isset($matches[0]) ? (float) $matches[0] : 0;
					}
				}
				else
				{
					preg_match($pattern, (string) $source, $matches);
					$result = isset($matches[0]) ? (float) $matches[0] : 0;
				}

				break;
			case 'BOOL':
			case 'BOOLEAN':

				if (is_array($source))
				{
					$result = array();

					// Iterate through the array
					foreach ($source as $eachString)
					{
						$result[] = (bool) $eachString;
					}
				}
				else
				{
					$result = (bool) $source;
				}

				break;
			case 'WORD':
				$pattern = '/[^A-Z_]/i';

				if (is_array($source))
				{
					$result = array();

					// Iterate through the array
					foreach ($source as $eachString)
					{
						$result[] = (string) preg_replace($pattern, '', $eachString);
					}
				}
				else
				{
					$result = (string) preg_replace($pattern, '', $source);
				}

				break;
			case 'ALNUM':
				$pattern = '/[^A-Z0-9]/i';

				if (is_array($source))
				{
					$result = array();

					// Iterate through the array
					foreach ($source as $eachString)
					{
						$result[] = (string) preg_replace($pattern, '', $eachString);
					}
				}
				else
				{
					$result = (string) preg_replace($pattern, '', $source);
				}

				break;
			case 'CMD':
				$pattern = '/[^A-Z0-9_\.-]/i';

				if (is_array($source))
				{
					$result = array();

					// Iterate through the array
					foreach ($source as $eachString)
					{
						$cleaned  = (string) preg_replace($pattern, '', $eachString);
						$result[] = ltrim($cleaned, '.');
					}
				}
				else
				{
					$result = (string) preg_replace($pattern, '', $source);
					$result = ltrim($result, '.');
				}

				break;
			case 'BASE64':
				$pattern = '/[^A-Z0-9\/+=]/i';

				if (is_array($source))
				{
					$result = array();

					// Iterate through the array
					foreach ($source as $eachString)
					{
						$result[] = (string) preg_replace($pattern, '', $eachString);
					}
				}
				else
				{
					$result = (string) preg_replace($pattern, '', $source);
				}

				break;
			case 'STRING':

				if (is_array($source))
				{
					$result = array();

					// Iterate through the array
					foreach ($source as $eachString)
					{
						$result[] = (string) $this->remove($this->decode((string) $eachString));
					}
				}
				else
				{
					$result = (string) $this->remove($this->decode((string) $source));
				}

				break;
			case 'HTML':

				if (is_array($source))
				{
					$result = array();

					// Iterate through the array
					foreach ($source as $eachString)
					{
						$result[] = (string) $this->remove((string) $eachString);
					}
				}
				else
				{
					$result = (string) $this->remove((string) $source);
				}

				break;
			case 'ARRAY':
				$result = (array) $source;

				break;
			case 'PATH':
				$pattern = '/^[A-Za-z0-9_\/-]+[A-Za-z0-9_\.-]*([\\\\\/][A-Za-z0-9_-]+[A-Za-z0-9_\.-]*)*$/';

				if (is_array($source))
				{
					$result = array();

					// Iterate through the array
					foreach ($source as $eachString)
					{
						preg_match($pattern, (string) $eachString, $matches);
						$result[] = isset($matches[0]) ? (string) $matches[0] : '';
					}
				}
				else
				{
					preg_match($pattern, $source, $matches);
					$result = isset($matches[0]) ? (string) $matches[0] : '';
				}

				break;
			case 'TRIM':

				if (is_array($source))
				{
					$result = array();

					// Iterate through the array
					foreach ($source as $eachString)
					{
						$cleaned  = (string) trim($eachString);
						$cleaned  = StringHelper::trim($cleaned, chr(0xE3) . chr(0x80) . chr(0x80));
						$result[] = StringHelper::trim($cleaned, chr(0xC2) . chr(0xA0));
					}
				}
				else
				{
					$result = (string) trim($source);
					$result = StringHelper::trim($result, chr(0xE3) . chr(0x80) . chr(0x80));
					$result = StringHelper::trim($result, chr(0xC2) . chr(0xA0));
				}

				break;
			case 'USERNAME':
				$pattern = '/[\x00-\x1F\x7F<>"\'%&]/';

				if (is_array($source))
				{
					$result = array();

					// Iterate through the array
					foreach ($source as $eachString)
					{
						$result[] = (string) preg_replace($pattern, '', $eachString);
					}
				}
				else
				{
					$result = (string) preg_replace($pattern, '', $source);
				}

				break;
			case 'RAW':
				$result = $source;

				break;
			default:

				// Are we dealing with an array?
				if (is_array($source))
				{
					foreach ($source as $key => $value)
					{
						// Filter element for XSS and other 'bad' code etc.
						if (is_string($value))
						{
							$source[$key] = $this->_remove($this->_decode($value));
						}
					}

					$result = $source;
				}
				else
				{
					// Or a string?
					if (is_string($source) && !empty($source))
					{
						// Filter source for XSS and other 'bad' code etc.
						$result = $this->_remove($this->_decode($source));
					}
					else
					{
						// Not an array or string... return the passed parameter
						$result = $source;
					}
				}

				break;
		}

		return $result;
	}

	/**
	 * Function to punyencode utf8 mail when saving content
	 *
	 * @param   string  $text  The strings to encode
	 *
	 * @return  string  The punyencoded mail
	 *
	 * @since   3.5
	 */
	public function emailToPunycode($text)
	{
		$pattern = '/(("mailto:)+[\w\.\-\+]+\@[^"?]+\.+[^."?]+("|\?))/';

		if (preg_match_all($pattern, $text, $matches))
		{
			foreach ($matches[0] as $match)
			{
				$match  = (string) str_replace(array('?', '"'), '', $match);
				$text   = (string) str_replace($match, \JStringPunycode::emailToPunycode($match), $text);
			}
		}

		return $text;
	}

	/**
	 * Checks an uploaded for suspicious naming and potential PHP contents which could indicate a hacking attempt.
	 *
	 * The options you can define are:
	 * null_byte                   Prevent files with a null byte in their name (buffer overflow attack)
	 * forbidden_extensions        Do not allow these strings anywhere in the file's extension
	 * php_tag_in_content          Do not allow `<?php` tag in content
	 * phar_stub_in_content        Do not allow the `__HALT_COMPILER()` phar stub in content
	 * shorttag_in_content         Do not allow short tag `<?` in content
	 * shorttag_extensions         Which file extensions to scan for short tags in content
	 * fobidden_ext_in_content     Do not allow forbidden_extensions anywhere in content
	 * php_ext_content_extensions  Which file extensions to scan for .php in content
	 *
	 * This code is an adaptation and improvement of Admin Tools' UploadShield feature,
	 * relicensed and contributed by its author.
	 *
	 * @param   array  $file     An uploaded file descriptor
	 * @param   array  $options  The scanner options (see the code for details)
	 *
	 * @return  boolean  True of the file is safe
	 *
	 * @since   3.4
	 */
	public static function isSafeFile($file, $options = array())
	{
		$defaultOptions = array(

			// Null byte in file name
			'null_byte'                  => true,

			// Forbidden string in extension (e.g. php matched .php, .xxx.php, .php.xxx and so on)
			'forbidden_extensions'       => array(
				'php', 'phps', 'pht', 'phtml', 'php3', 'php4', 'php5', 'php6', 'php7', 'phar', 'inc', 'pl', 'cgi', 'fcgi', 'java', 'jar', 'py',
			),

			// <?php tag in file contents
			'php_tag_in_content'         => true,

			// <? tag in file contents
			'shorttag_in_content'        => true,

			// __HALT_COMPILER()
			'phar_stub_in_content'        => true,

			// Which file extensions to scan for short tags
			'shorttag_extensions'        => array(
				'inc', 'phps', 'class', 'php3', 'php4', 'php5', 'txt', 'dat', 'tpl', 'tmpl',
			),

			// Forbidden extensions anywhere in the content
			'fobidden_ext_in_content'    => true,

			// Which file extensions to scan for .php in the content
			'php_ext_content_extensions' => array('zip', 'rar', 'tar', 'gz', 'tgz', 'bz2', 'tbz', 'jpa'),
		);

		$options = array_merge($defaultOptions, $options);

		// Make sure we can scan nested file descriptors
		$descriptors = $file;

		if (isset($file['name']) && isset($file['tmp_name']))
		{
			$descriptors = self::decodeFileData(
				array(
					$file['name'],
					$file['type'],
					$file['tmp_name'],
					$file['error'],
					$file['size'],
				)
			);
		}

		// Handle non-nested descriptors (single files)
		if (isset($descriptors['name']))
		{
			$descriptors = array($descriptors);
		}

		// Scan all descriptors detected
		foreach ($descriptors as $fileDescriptor)
		{
			if (!isset($fileDescriptor['name']))
			{
				// This is a nested descriptor. We have to recurse.
				if (!self::isSafeFile($fileDescriptor, $options))
				{
					return false;
				}

				continue;
			}

			$tempNames     = $fileDescriptor['tmp_name'];
			$intendedNames = $fileDescriptor['name'];

			if (!is_array($tempNames))
			{
				$tempNames = array($tempNames);
			}

			if (!is_array($intendedNames))
			{
				$intendedNames = array($intendedNames);
			}

			$len = count($tempNames);

			for ($i = 0; $i < $len; $i++)
			{
				$tempName     = array_shift($tempNames);
				$intendedName = array_shift($intendedNames);

				// 1. Null byte check
				if ($options['null_byte'])
				{
					if (strstr($intendedName, "\x00"))
					{
						return false;
					}
				}

				// 2. PHP-in-extension check (.php, .php.xxx[.yyy[.zzz[...]]], .xxx[.yyy[.zzz[...]]].php)
				if (!empty($options['forbidden_extensions']))
				{
					$explodedName = explode('.', $intendedName);
					$explodedName =	array_reverse($explodedName);
					array_pop($explodedName);
					$explodedName = array_map('strtolower', $explodedName);

					/*
					 * DO NOT USE array_intersect HERE! array_intersect expects the two arrays to
					 * be set, i.e. they should have unique values.
					 */
					foreach ($options['forbidden_extensions'] as $ext)
					{
						if (in_array($ext, $explodedName))
						{
							return false;
						}
					}
				}

				// 3. File contents scanner (PHP tag in file contents)
				if ($options['php_tag_in_content']
					|| $options['shorttag_in_content'] || $options['phar_stub_in_content']
					|| ($options['fobidden_ext_in_content'] && !empty($options['forbidden_extensions'])))
				{
					$fp = @fopen($tempName, 'r');

					if ($fp !== false)
					{
						$data = '';

						while (!feof($fp))
						{
							$data .= @fread($fp, 131072);

							if ($options['php_tag_in_content'] && stripos($data, '<?php') !== false)
							{
								return false;
							}

							if ($options['phar_stub_in_content'] && stripos($data, '__HALT_COMPILER()') !== false)
							{
								return false;
							}

							if ($options['shorttag_in_content'])
							{
								$suspiciousExtensions = $options['shorttag_extensions'];

								if (empty($suspiciousExtensions))
								{
									$suspiciousExtensions = array(
										'inc', 'phps', 'class', 'php3', 'php4', 'txt', 'dat', 'tpl', 'tmpl',
									);
								}

								/*
								 * DO NOT USE array_intersect HERE! array_intersect expects the two arrays to
								 * be set, i.e. they should have unique values.
								 */
								$collide = false;

								foreach ($suspiciousExtensions as $ext)
								{
									if (in_array($ext, $explodedName))
									{
										$collide = true;

										break;
									}
								}

								if ($collide)
								{
									// These are suspicious text files which may have the short tag (<?) in them
									if (strstr($data, '<?'))
									{
										return false;
									}
								}
							}

							if ($options['fobidden_ext_in_content'] && !empty($options['forbidden_extensions']))
							{
								$suspiciousExtensions = $options['php_ext_content_extensions'];

								if (empty($suspiciousExtensions))
								{
									$suspiciousExtensions = array(
										'zip', 'rar', 'tar', 'gz', 'tgz', 'bz2', 'tbz', 'jpa',
									);
								}

								/*
								 * DO NOT USE array_intersect HERE! array_intersect expects the two arrays to
								 * be set, i.e. they should have unique values.
								 */
								$collide = false;

								foreach ($suspiciousExtensions as $ext)
								{
									if (in_array($ext, $explodedName))
									{
										$collide = true;

										break;
									}
								}

								if ($collide)
								{
									/*
									 * These are suspicious text files which may have an executable
									 * file extension in them
									 */
									foreach ($options['forbidden_extensions'] as $ext)
									{
										if (strstr($data, '.' . $ext))
										{
											return false;
										}
									}
								}
							}

							/*
							 * This makes sure that we don't accidentally skip a <?php tag if it's across
							 * a read boundary, even on multibyte strings
							 */
							$data = substr($data, -10);
						}

						fclose($fp);
					}
				}
			}
		}

		return true;
	}

	/**
	 * Method to decode a file data array.
	 *
	 * @param   array  $data  The data array to decode.
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	protected static function decodeFileData(array $data)
	{
		$result = array();

		if (is_array($data[0]))
		{
			foreach ($data[0] as $k => $v)
			{
				$result[$k] = self::decodeFileData(array($data[0][$k], $data[1][$k], $data[2][$k], $data[3][$k], $data[4][$k]));
			}

			return $result;
		}

		return array('name' => $data[0], 'type' => $data[1], 'tmp_name' => $data[2], 'error' => $data[3], 'size' => $data[4]);
	}

	/**
	 * Internal method to iteratively remove all unwanted tags and attributes
	 *
	 * @param   string  $source  Input string to be 'cleaned'
	 *
	 * @return  string  'Cleaned' version of input parameter
	 *
	 * @since       1.7.0
	 * @deprecated  4.0 Use InputFilter::remove() instead
	 */
	protected function _remove($source)
	{
		return $this->remove($source);
	}

	/**
	 * Internal method to iteratively remove all unwanted tags and attributes
	 *
	 * @param   string  $source  Input string to be 'cleaned'
	 *
	 * @return  string  'Cleaned' version of input parameter
	 *
	 * @since   3.5
	 */
	protected function remove($source)
	{
		// Check for invalid UTF-8 byte sequence
		if (!preg_match('//u', $source))
		{
			// String contains invalid byte sequence, remove it
			$source = htmlspecialchars_decode(htmlspecialchars($source, ENT_IGNORE, 'UTF-8'));
		}

		// Iteration provides nested tag protection
		do
		{
			$temp = $source;
			$source = $this->_cleanTags($source);
		}
		while ($temp !== $source);

		return $source;
	}

	/**
	 * Internal method to strip a string of certain tags
	 *
	 * @param   string  $source  Input string to be 'cleaned'
	 *
	 * @return  string  'Cleaned' version of input parameter
	 *
	 * @since       1.7.0
	 * @deprecated  4.0 Use InputFilter::cleanTags() instead
	 */
	protected function _cleanTags($source)
	{
		return $this->cleanTags($source);
	}

	/**
	 * Internal method to strip a string of certain tags
	 *
	 * @param   string  $source  Input string to be 'cleaned'
	 *
	 * @return  string  'Cleaned' version of input parameter
	 *
	 * @since   3.5
	 */
	protected function cleanTags($source)
	{
		// First, pre-process this for illegal characters inside attribute values
		$source = $this->_escapeAttributeValues($source);

		// In the beginning we don't really have a tag, so result is empty
		$result = '';
		$offset = 0;
		$length = strlen($source);

		// Is there a tag? If so it will certainly start with a '<'.
		$tagOpenStartOffset = strpos($source, '<');

		// Is there any close tag
		$tagOpenEndOffset = strpos($source, '>');

		while ($offset < $length)
		{
			// Preserve '>' character which exists before related '<'
			if ($tagOpenEndOffset !== false && ($tagOpenStartOffset === false || $tagOpenEndOffset < $tagOpenStartOffset))
			{
				$result .= substr($source, $offset, $tagOpenEndOffset - $offset) . '>';
				$offset  = $tagOpenEndOffset + 1;

				// Search for a new closing indicator
				$tagOpenEndOffset = strpos($source, '>', $offset);

				continue;
			}

			// Add safe text appearing before the '<'
			if ($tagOpenStartOffset > $offset)
			{
				$result .= substr($source, $offset, $tagOpenStartOffset - $offset);
				$offset  = $tagOpenStartOffset;
			}

			// There is no more tags
			if ($tagOpenStartOffset === false && $tagOpenEndOffset === false)
			{
				$result .= substr($source, $offset, $length - $offset);
				$offset  = $length;

				break;
			}

			// Remove every '<' character if '>' does not exists or we have '<>'
			if ($tagOpenStartOffset !== false && $tagOpenEndOffset === false || $tagOpenStartOffset + 1 == $tagOpenEndOffset)
			{
				$offset++;

				// Search for a new opening indicator
				$tagOpenStartOffset = strpos($source, '<', $offset);

				continue;
			}

			// Check for mal-formed tag where we have a second '<' before the '>'
			$nextOpenStartOffset = strpos($source, '<', $tagOpenStartOffset + 1);

			if ($nextOpenStartOffset !== false && $nextOpenStartOffset < $tagOpenEndOffset)
			{
				// At this point we have a mal-formed tag, skip previous '<'
				$offset++;

				// Set a new opening indicator position
				$tagOpenStartOffset = $nextOpenStartOffset;

				continue;
			}

			// Let's get some information about our tag and setup attribute pairs
			// Now we have something like 'span class="" style=""', '/span', 'br/', 'br /' or 'hr disabled /'
			$tagContent = substr($source, $offset + 1, $tagOpenEndOffset - 1 - $offset);

			// All ASCII whitespaces replace by 0x20
			$tagNormalized = preg_replace('/\s/', ' ', $tagContent);
			$tagLength     = strlen($tagContent);
			$spaceOffset   = strpos($tagNormalized, ' ');

			// Are we an open tag or a close tag?
			$isClosingTag     = $tagContent[0] === '/' ? 1 : 0;
			$isSelfClosingTag = substr($tagContent, -1) === '/' ? 1 : 0;

			if ($spaceOffset !== false)
			{
				$tagName = substr($tagContent, $isClosingTag, $spaceOffset - $isClosingTag);
			}
			else
			{
				$tagName = substr($tagContent, $isClosingTag, $tagLength - $isClosingTag - $isSelfClosingTag);
			}

			/*
			 * Exclude all "non-regular" tagnames
			 * OR no tagname
			 * OR remove if xssauto is on and tag is blacklisted
			 */
			if (!$tagName
				|| !preg_match("/^[a-z][a-z0-9]*$/i", $tagName)
				|| ($this->xssAuto && in_array(strtolower($tagName), $this->tagBlacklist)))
			{
				$offset += $tagLength + 2;

				$tagOpenStartOffset = strpos($source, '<', $offset);
				$tagOpenEndOffset   = strpos($source, '>', $offset);

				// Strip tag
				continue;
			}

			$attrSet = array();

			/*
			 * Time to grab any attributes from the tag... need this section in
			 * case attributes have spaces in the values.
			 */
			while ($spaceOffset !== false && $spaceOffset + 1 < $tagLength)
			{
				$attrStartOffset = $spaceOffset + 1;

				// Find position of equal and open quote
				if (preg_match('#= *(")[^"]*(")#', $tagNormalized, $matches, PREG_OFFSET_CAPTURE, $attrStartOffset))
				{
					$equalOffset     = $matches[0][1];
					$quote1Offset    = $matches[1][1];
					$quote2Offset    = $matches[2][1];
					$nextSpaceOffset = strpos($tagNormalized, ' ', $quote2Offset);
				}
				else
				{
					$equalOffset     = strpos($tagNormalized, '=', $attrStartOffset);
					$quote1Offset    = strpos($tagNormalized, '"', $attrStartOffset);
					$nextSpaceOffset = strpos($tagNormalized, ' ', $attrStartOffset);

					if ($quote1Offset !== false)
					{
						$quote2Offset = strpos($tagNormalized, '"', $quote1Offset + 1);
					}
					else
					{
						$quote2Offset = false;
					}
				}

				// Do we have an attribute to process? [check for equal sign]
				if ($tagContent[$attrStartOffset] !== '/'
					&& ($equalOffset && $nextSpaceOffset && $nextSpaceOffset < $equalOffset || !$equalOffset))
				{
					// Search for attribute without value, ex: 'checked/' or 'checked '
					if ($nextSpaceOffset)
					{
						$attrEndOffset = $nextSpaceOffset;
					}
					else
					{
						$attrEndOffset = strpos($tagContent, '/', $attrStartOffset);

						if ($attrEndOffset === false)
						{
							$attrEndOffset = $tagLength;
						}
					}

					// If there is an ending, use this, if not, do not worry.
					if ($attrEndOffset > $attrStartOffset)
					{
						$attrSet[] = substr($tagContent, $attrStartOffset, $attrEndOffset - $attrStartOffset);
					}
				}
				elseif ($equalOffset !== false)
				{
					/*
					 * If the attribute value is wrapped in quotes we need to grab the substring from
					 * the closing quote, otherwise grab until the next space.
					 */
					if ($quote1Offset !== false && $quote2Offset !== false)
					{
						// Add attribute, ex: 'class="body abc"'
						$attrSet[] = substr($tagContent, $attrStartOffset, $quote2Offset + 1 - $attrStartOffset);
					}
					else
					{
						if ($nextSpaceOffset)
						{
							$attrEndOffset = $nextSpaceOffset;
						}
						else
						{
							$attrEndOffset = $tagLength;
						}

						// Add attribute, ex: 'class=body'
						$attrSet[] = substr($tagContent, $attrStartOffset, $attrEndOffset - $attrStartOffset);
					}
				}

				$spaceOffset = $nextSpaceOffset;
			}

			// Is our tag in the user input array?
			$tagFound = in_array(strtolower($tagName), $this->tagsArray);

			// If the tag is allowed let's append it to the output string.
			if ((!$tagFound && $this->tagsMethod) || ($tagFound && !$this->tagsMethod))
			{
				// Reconstruct tag with allowed attributes
				if ($isClosingTag)
				{
					$result .= "</$tagName>";
				}
				else
				{
					$attrSet = $this->_cleanAttributes($attrSet);

					// Open or single tag
					$result .= '<' . $tagName;

					if ($attrSet)
					{
						$result .= ' ' . implode(' ', $attrSet);
					}

					// Reformat single tags to XHTML
					if (strpos($source, "</$tagName>", $tagOpenStartOffset) !== false)
					{
						$result .= '>';
					}
					else
					{
						$result .= ' />';
					}
				}
			}

			$offset += $tagLength + 2;

			if ($offset < $length)
			{
				// Find next tag's start and continue iteration
				$tagOpenStartOffset = strpos($source, '<', $offset);
				$tagOpenEndOffset   = strpos($source, '>', $offset);
			}
		}

		return $result;
	}

	/**
	 * Internal method to strip a tag of certain attributes
	 *
	 * @param   array  $attrSet  Array of attribute pairs to filter
	 *
	 * @return  array  Filtered array of attribute pairs
	 *
	 * @since       1.7.0
	 * @deprecated  4.0 Use InputFilter::cleanAttributes() instead
	 */
	protected function _cleanAttributes($attrSet)
	{
		return $this->cleanAttributes($attrSet);
	}

	/**
	 * Escape < > and " inside attribute values
	 *
	 * @param   string  $source  The source string.
	 *
	 * @return  string  Filtered string
	 *
	 * @since    3.5
	 */
	protected function escapeAttributeValues($source)
	{
		$alreadyFiltered = '';
		$remainder = $source;
		$badChars = array('<', '"', '>');
		$escapedChars = array('&lt;', '&quot;', '&gt;');

		/*
		 * Process each portion based on presence of =" and "<space>, "/>, or ">
		 * See if there are any more attributes to process
		 */
		while (preg_match('#<[^>]*?=\s*?(\"|\')#s', $remainder, $matches, PREG_OFFSET_CAPTURE))
		{
			// Get the portion before the attribute value
			$quotePosition = $matches[0][1];
			$nextBefore = $quotePosition + strlen($matches[0][0]);

			/*
			 * Figure out if we have a single or double quote and look for the matching closing quote
			 * Closing quote should be "/>, ">, "<space>, or " at the end of the string
			 */
			$quote = substr($matches[0][0], -1);
			$pregMatch = ($quote == '"') ? '#(\"\s*/\s*>|\"\s*>|\"\s+|\"$)#' : "#(\'\s*/\s*>|\'\s*>|\'\s+|\'$)#";

			// Get the portion after attribute value
			if (preg_match($pregMatch, substr($remainder, $nextBefore), $matches, PREG_OFFSET_CAPTURE))
			{
				// We have a closing quote
				$nextAfter = $nextBefore + $matches[0][1];
			}
			else
			{
				// No closing quote
				$nextAfter = strlen($remainder);
			}

			// Get the actual attribute value
			$attributeValue = substr($remainder, $nextBefore, $nextAfter - $nextBefore);

			// Escape bad chars
			$attributeValue = str_replace($badChars, $escapedChars, $attributeValue);
			$attributeValue = $this->_stripCSSExpressions($attributeValue);
			$alreadyFiltered .= substr($remainder, 0, $nextBefore) . $attributeValue . $quote;
			$remainder = substr($remainder, $nextAfter + 1);
		}

		// At this point, we just have to return the $alreadyFiltered and the $remainder
		return $alreadyFiltered . $remainder;
	}

	/**
	 * Try to convert to plaintext
	 *
	 * @param   string  $source  The source string.
	 *
	 * @return  string  Plaintext string
	 *
	 * @since       1.7.0
	 * @deprecated  4.0 Use InputFilter::decode() instead
	 */
	protected function _decode($source)
	{
		return $this->decode($source);
	}

	/**
	 * Try to convert to plaintext
	 *
	 * @param   string  $source  The source string.
	 *
	 * @return  string  Plaintext string
	 *
	 * @since   3.5
	 */
	protected function decode($source)
	{
		static $ttr;

		if (!is_array($ttr))
		{
			// Entity decode
			$trans_tbl = get_html_translation_table(HTML_ENTITIES, ENT_COMPAT, 'ISO-8859-1');

			foreach ($trans_tbl as $k => $v)
			{
				$ttr[$v] = utf8_encode($k);
			}
		}

		$source = strtr($source, $ttr);

		// Convert decimal
		$source = preg_replace_callback('/&#(\d+);/m', function($m)
		{
			return utf8_encode(chr($m[1]));
		}, $source
		);

		// Convert hex
		$source = preg_replace_callback('/&#x([a-f0-9]+);/mi', function($m)
		{
			return utf8_encode(chr(hexdec($m[1])));
		}, $source
		);

		return $source;
	}

	/**
	 * Escape < > and " inside attribute values
	 *
	 * @param   string  $source  The source string.
	 *
	 * @return  string  Filtered string
	 *
	 * @since       1.7.0
	 * @deprecated  4.0 Use InputFilter::escapeAttributeValues() instead
	 */
	protected function _escapeAttributeValues($source)
	{
		return $this->escapeAttributeValues($source);
	}

	/**
	 * Remove CSS Expressions in the form of `<property>:expression(...)`
	 *
	 * @param   string  $source  The source string.
	 *
	 * @return  string  Filtered string
	 *
	 * @since       1.7.0
	 * @deprecated  4.0 Use InputFilter::stripCSSExpressions() instead
	 */
	protected function _stripCSSExpressions($source)
	{
		return $this->stripCSSExpressions($source);
	}

	/**
	 * Recursively strip Unicode Supplementary Characters from the source. Not: objects cannot be filtered.
	 *
	 * @param   mixed  $source  The data to filter
	 *
	 * @return  mixed  The filtered result
	 *
	 * @since  3.5
	 */
	protected function stripUSC($source)
	{
		if (is_object($source))
		{
			return $source;
		}

		if (is_array($source))
		{
			$filteredArray = array();

			foreach ($source as $k => $v)
			{
				$filteredArray[$k] = $this->stripUSC($v);
			}

			return $filteredArray;
		}

		return preg_replace('/[\xF0-\xF7].../s', "\xE2\xAF\x91", $source);
	}
}
