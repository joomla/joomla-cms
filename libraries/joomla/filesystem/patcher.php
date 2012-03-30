<?php

/**
 * @package     Joomla.Platform
 * @subpackage  FileSystem
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.file');

/**
 * A Unified Diff Format Patcher class
 *
 * @package     Joomla.Platform
 * @subpackage  FileSystem
 *
 * @link        http://sourceforge.net/projects/phppatcher/ This has been derived from the PhpPatcher version 0.1.1 written by Giuseppe Mazzotta
 * @since       12.1
 */
class JFilesystemPatcher
{
	/**
	 * Regular expression for searching source files
	 */
	const SRC_FILE = '/^---\\s+(\\S+)\s+\\d{1,4}-\\d{1,2}-\\d{1,2}\\s+\\d{1,2}:\\d{1,2}:\\d{1,2}(\\.\\d+)?\\s+(\+|-)\\d{4}/A';

	/**
	 * Regular expression for searching destination files
	 */
	const DST_FILE = '/^\\+\\+\\+\\s+(\\S+)\s+\\d{1,4}-\\d{1,2}-\\d{1,2}\\s+\\d{1,2}:\\d{1,2}:\\d{1,2}(\\.\\d+)?\\s+(\+|-)\\d{4}/A';

	/**
	 * Regular expression for searching hunks of differences
	 */
	const HUNK = '/@@ -(\\d+)(,(\\d+))?\\s+\\+(\\d+)(,(\\d+))?\\s+@@($)/A';

	/**
	 * Regular expression for splitting lines
	 */
	const SPLIT = '/(\r\n)|(\r)|(\n)/';

	/**
	 * @var  array  sources files
	 *
	 * @since   12.1
	 */
	protected $sources = array();

	/**
	 * @var  array  destination files
	 *
	 * @since   12.1
	 */
	protected $destinations = array();

	/**
	 * @var  array  removal files
	 *
	 * @since   12.1
	 */
	protected $removals = array();

	/**
	 * @var  array  patches
	 *
	 * @since   12.1
	 */
	protected $patches = array();

	/**
	 * @var  array  instance of this class
	 *
	 * @since   12.1
	 */
	protected static $instance;

	/**
	 * Constructor
	 *
	 * The constructor is protected to force the use of JFilesystemPatcher::getInstance()
	 *
	 * @since   12.1
	 */
	protected function __construct()
	{
	}

	/**
	 * Method to get a patcher
	 *
	 * @return  JFilesystemPatcher  an instance of the patcher
	 *
	 * @since   12.1
	 */
	public static function getInstance()
	{
		if (!isset(static::$instance))
		{
			static::$instance = new static;
		}
		return static::$instance;
	}

	/**
	 * Reset the pacher
	 *
	 * @return  JFilesystemPatcher  This object for chaining
	 */
	public function reset()
	{
		$this->sources = array();
		$this->destinations = array();
		$this->removals = array();
		$this->patches = array();
		return $this;
	}

	/**
	 * Apply the patches
	 *
	 * @throw  RuntimeException
	 *
	 * @return integer the number of files patched
	 */
	public function apply()
	{
		foreach ($this->patches as $patch)
		{
			// Separate the input into lines
			$lines = self::splitLines($patch['udiff']);

			// Loop for each header
			while (self::findHeader($lines, $src, $dst))
			{
				$done = false;

				if ($patch['strip'] === null)
				{
					$src = $patch['root'] . preg_replace('#^([^/]*/)*#', '', $src);
					$dst = $patch['root'] . preg_replace('#^([^/]*/)*#', '', $dst);
				}
				else
				{
					$src = $patch['root'] . preg_replace('#^([^/]*/){' . (int) $patch['strip'] . '}#', '', $src);
					$dst = $patch['root'] . preg_replace('#^([^/]*/){' . (int) $patch['strip'] . '}#', '', $dst);
				}

				// Loop for each hunk of differences
				while (self::findHunk($lines, $src_line, $src_size, $dst_line, $dst_size))
				{
					$done = true;

					// Apply the hunk of differences
					$this->applyHunk($lines, $src, $dst, $src_line, $src_size, $dst_line, $dst_size);
				}

				// If no modifications were found, throw an exception
				if (!$done)
				{
					throw new RuntimeException('Invalid Diff');
				}
			}
		}

		// Initialize the counter
		$done = 0;

		// Patch each destination file
		foreach ($this->destinations as $file => $content)
		{
			if (JFile::write($file, implode("\n", $content)))
			{
				if (isset($this->sources[$file]))
				{
					$this->sources[$file] = $content;
				}
				$done++;
			}
		}

		// Remove each removed file
		foreach ($this->removals as $file)
		{
			if (JFile::delete($file))
			{
				if (isset($this->sources[$file]))
				{
					unset($this->sources[$file]);
				}
				$done++;
			}
		}

		// Clear the destinations cache
		$this->destinations = array();

		// Clear the removals
		$this->removals = array();

		// Clear the patches
		$this->patches = array();
		return $done;
	}

	/**
	 * Add a unified diff file to the patcher
	 *
	 * @param   string  $filename  Path to the unified diff file
	 * @param   string  $root      The files root path
	 * @param   string  $strip     The number of '/' to strip
	 *
	 * @return	JFilesystemPatch $this for chaining
	 *
	 * @since   12.1
	 */
	public function addFile($filename, $root = JPATH_BASE, $strip = 0)
	{
		return $this->add(file_get_contents($filename), $root, $strip);
	}

	/**
	 * Add a unified diff string to the patcher
	 *
	 * @param   string  $udiff  Unified diff input string
	 * @param   string  $root   The files root path
	 * @param   string  $strip  The number of '/' to strip
	 *
	 * @return	JFilesystemPatch $this for chaining
	 *
	 * @since   12.1
	 */
	public function add($udiff, $root = JPATH_BASE, $strip = 0)
	{
		$this->patches[] = array(
			'udiff' => $udiff,
			'root' => isset($root) ? rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR : '',
			'strip' => $strip
		);
		return $this;
	}

	/**
	 * Separate CR or CRLF lines
	 *
	 * @param   string  $data  Input string
	 *
	 * @return  array  The lines of the inputdestination file
	 *
	 * @since   12.1
	 */
	protected static function splitLines($data)
	{
		return preg_split(self::SPLIT, $data);
	}

	/**
	 * Find the diff header
	 *
	 * The internal array pointer of $lines is on the next line after the finding
	 *
	 * @param   array   &$lines  The udiff array of lines
	 * @param   string  &$src    The source file
	 * @param   string  &$dst    The destination file
	 *
	 * @return  boolean  TRUE in case of success, FALSE in case of failure
	 *
	 * @throw  RuntimeException
	 */
	protected static function findHeader(&$lines, &$src, &$dst)
	{
		// Get the current line
		$line = current($lines);

		// Search for the header
		while ($line !== false && !preg_match(self::SRC_FILE, $line, $m))
		{
			$line = next($lines);
		}
		if ($line === false)
		{
			// No header found, return false
			return false;
		}
		else
		{
			// Set the source file
			$src = $m[1];

			// Advance to the next line
			$line = next($lines);
			if ($line === false)
			{
				throw new RuntimeException('Unexpected EOF');
			}

			// Search the destination file
			if (!preg_match(self::DST_FILE, $line, $m))
			{
				throw new RuntimeException('Invalid Diff file');
			}

			// Set the destination file
			$dst = $m[1];

			// Advance to the next line
			if (next($lines) === false)
			{
				throw new RuntimeException('Unexpected EOF');
			}
			return true;
		}
	}

	/**
	 * Find the next hunk of difference
	 *
	 * The internal array pointer of $lines is on the next line after the finding
	 *
	 * @param   array   &$lines     The udiff array of lines
	 * @param   string  &$src_line  The beginning of the patch for the source file
	 * @param   string  &$src_size  The size of the patch for the source file
	 * @param   string  &$dst_line  The beginning of the patch for the destination file
	 * @param   string  &$dst_size  The size of the patch for the destination file
	 *
	 * @return  boolean  TRUE in case of success, false in case of failure
	 *
	 * @throw  RuntimeException
	 */
	protected static function findHunk(&$lines, &$src_line, &$src_size, &$dst_line, &$dst_size)
	{
		$line = current($lines);
		if (preg_match(self::HUNK, $line, $m))
		{
			$src_line = (int) $m[1];
			if ($m[3] === '')
			{
				$src_size = 1;
			}
			else
			{
				$src_size = (int) $m[3];
			}

			$dst_line = (int) $m[4];
			if ($m[6] === '')
			{
				$dst_size = 1;
			}
			else
			{
				$dst_size = (int) $m[6];
			}

			if (next($lines) === false)
			{
				throw new RuntimeException('Unexpected EOF');
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Apply the patch
	 *
	 * @param   array   &$lines    The udiff array of lines
	 * @param   string  $src       The source file
	 * @param   string  $dst       The destination file
	 * @param   string  $src_line  The beginning of the patch for the source file
	 * @param   string  $src_size  The size of the patch for the source file
	 * @param   string  $dst_line  The beginning of the patch for the destination file
	 * @param   string  $dst_size  The size of the patch for the destination file
	 *
	 * @return  void
	 *
	 * @throw  RuntimeException
	 */
	protected function applyHunk(&$lines, $src, $dst, $src_line, $src_size, $dst_line, $dst_size)
	{
		$src_line--;
		$dst_line--;
		$line = current($lines);

		// Source lines (old file)
		$source = array();

		// New lines (new file)
		$destin = array();
		$src_left = $src_size;
		$dst_left = $dst_size;
		do
		{
			if (!isset($line[0]))
			{
				$source[] = '';
				$destin[] = '';
				$src_left--;
				$dst_left--;
			}
			elseif ($line[0] == '-')
			{
				if ($src_left == 0)
				{
					throw new RuntimeException(JText::sprintf('JLIB_FILESYSTEM_PATCHER_REMOVE_LINE', key($lines)));
				}
				$source[] = substr($line, 1);
				$src_left--;
			}
			elseif ($line[0] == '+')
			{
				if ($dst_left == 0)
				{
					throw new RuntimeException(JText::sprintf('JLIB_FILESYSTEM_PATCHER_ADD_LINE', key($lines)));
				}
				$destin[] = substr($line, 1);
				$dst_left--;
			}
			elseif ($line != '\\ No newline at end of file')
			{
				$line = substr($line, 1);
				$source[] = $line;
				$destin[] = $line;
				$src_left--;
				$dst_left--;
			}
			if ($src_left == 0 && $dst_left == 0)
			{

				// Now apply the patch, finally!
				if ($src_size > 0)
				{
					$src_lines = & $this->getSource($src);
					if (!isset($src_lines))
					{
						throw new RuntimeException(JText::sprintf('JLIB_FILESYSTEM_PATCHER_UNEXISING_SOURCE', $src));
					}
				}
				if ($dst_size > 0)
				{
					if ($src_size > 0)
					{
						$dst_lines = & $this->getDestination($dst, $src);
						$src_bottom = $src_line + count($source);
						$dst_bottom = $dst_line + count($destin);
						for ($l = $src_line;$l < $src_bottom;$l++)
						{
							if ($src_lines[$l] != $source[$l - $src_line])
							{
								throw new RuntimeException(JText::sprintf('JLIB_FILESYSTEM_PATCHER_FAILED_VERIFY', $src, $l));
							}
						}
						array_splice($dst_lines, $dst_line, count($source), $destin);
					}
					else
					{
						$this->destinations[$dst] = $destin;
					}
				}
				else
				{
					$this->removals[] = $src;
				}
				next($lines);
				return;
			}
			$line = next($lines);
		}
		while ($line !== false);
		throw new RuntimeException('Unexpected EOF');
	}

	/**
	 * Get the lines of a source file
	 *
	 * @param   string  $src  The path of a file
	 *
	 * @return  array  The lines of the source file
	 *
	 * @since   12.1
	 */
	protected function &getSource($src)
	{
		if (!isset($this->sources[$src]))
		{
			if (is_readable($src))
			{
				$this->sources[$src] = self::splitLines(file_get_contents($src));
			}
			else
			{
				$this->sources[$src] = null;
			}
		}
		return $this->sources[$src];
	}

	/**
	 * Get the lines of a destination file
	 *
	 * @param   string  $dst  The path of a destination file
	 * @param   string  $src  The path of a source file
	 *
	 * @return  array  The lines of the destination file
	 *
	 * @since   12.1
	 */
	protected function &getDestination($dst, $src)
	{
		if (!isset($this->destinations[$dst]))
		{
			$this->destinations[$dst] = $this->getSource($src);
		}
		return $this->destinations[$dst];
	}
}
