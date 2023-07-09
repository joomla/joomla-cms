<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Filesystem;

use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * A Unified Diff Format Patcher class
 *
 * @link   http://sourceforge.net/projects/phppatcher/ This has been derived from the PhpPatcher version 0.1.1 written by Giuseppe Mazzotta
 * @since  3.0.0
 * @deprecated  4.4 will be removed in 6.0
 *              Use Joomla\Filesystem\Patcher instead.
 */
class Patcher
{
    /**
     * Regular expression for searching source files
     */
    public const SRC_FILE = '/^---\\s+(\\S+)\s+\\d{1,4}-\\d{1,2}-\\d{1,2}\\s+\\d{1,2}:\\d{1,2}:\\d{1,2}(\\.\\d+)?\\s+(\+|-)\\d{4}/A';

    /**
     * Regular expression for searching destination files
     */
    public const DST_FILE = '/^\\+\\+\\+\\s+(\\S+)\s+\\d{1,4}-\\d{1,2}-\\d{1,2}\\s+\\d{1,2}:\\d{1,2}:\\d{1,2}(\\.\\d+)?\\s+(\+|-)\\d{4}/A';

    /**
     * Regular expression for searching hunks of differences
     */
    public const HUNK = '/@@ -(\\d+)(,(\\d+))?\\s+\\+(\\d+)(,(\\d+))?\\s+@@($)/A';

    /**
     * Regular expression for splitting lines
     */
    public const SPLIT = '/(\r\n)|(\r)|(\n)/';

    /**
     * @var    array  sources files
     * @since  3.0.0
     */
    protected $sources = [];

    /**
     * @var    array  destination files
     * @since  3.0.0
     */
    protected $destinations = [];

    /**
     * @var    array  removal files
     * @since  3.0.0
     */
    protected $removals = [];

    /**
     * @var    array  patches
     * @since  3.0.0
     */
    protected $patches = [];

    /**
     * @var    array  instance of this class
     * @since  3.0.0
     */
    protected static $instance;

    /**
     * Constructor
     *
     * The constructor is protected to force the use of FilesystemPatcher::getInstance()
     *
     * @since   3.0.0
     * @deprecated  4.4 will be removed in 6.0
     *              Use Joomla\Filesystem\Patcher::__construct() instead.
     */
    protected function __construct()
    {
    }

    /**
     * Method to get a patcher
     *
     * @return  Patcher  an instance of the patcher
     *
     * @since   3.0.0
     * @deprecated  4.4 will be removed in 6.0
     *              Use Joomla\Filesystem\Patcher::getInstance() instead.
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Reset the patcher
     *
     * @return  Patcher  This object for chaining
     *
     * @since   3.0.0
     * @deprecated  4.4 will be removed in 6.0
     *              Use Joomla\Filesystem\Patcher::reset() instead.
     */
    public function reset()
    {
        $this->sources      = [];
        $this->destinations = [];
        $this->removals     = [];
        $this->patches      = [];

        return $this;
    }

    /**
     * Apply the patches
     *
     * @return  integer  The number of files patched
     *
     * @since   3.0.0
     * @throws  \RuntimeException
     * @deprecated  4.4 will be removed in 6.0
     *              Use Joomla\Filesystem\Patcher::apply() instead.
     */
    public function apply()
    {
        foreach ($this->patches as $patch) {
            // Separate the input into lines
            $lines = self::splitLines($patch['udiff']);

            // Loop for each header
            while (self::findHeader($lines, $src, $dst)) {
                $done = false;

                $regex = '#^([^/]*/)*#';

                if ($patch['strip'] !== null) {
                    $regex = '#^([^/]*/){' . (int) $patch['strip'] . '}#';
                }

                $src = $patch['root'] . preg_replace($regex, '', $src);
                $dst = $patch['root'] . preg_replace($regex, '', $dst);

                // Loop for each hunk of differences
                while (self::findHunk($lines, $src_line, $src_size, $dst_line, $dst_size)) {
                    $done = true;

                    // Apply the hunk of differences
                    $this->applyHunk($lines, $src, $dst, $src_line, $src_size, $dst_line, $dst_size);
                }

                // If no modifications were found, throw an exception
                if (!$done) {
                    throw new \RuntimeException('Invalid Diff');
                }
            }
        }

        // Initialize the counter
        $done = 0;

        // Patch each destination file
        foreach ($this->destinations as $file => $content) {
            $buffer = implode("\n", $content);

            if (File::write($file, $buffer)) {
                if (isset($this->sources[$file])) {
                    $this->sources[$file] = $content;
                }

                $done++;
            }
        }

        // Remove each removed file
        foreach ($this->removals as $file) {
            if (File::delete($file)) {
                if (isset($this->sources[$file])) {
                    unset($this->sources[$file]);
                }

                $done++;
            }
        }

        // Clear the destinations cache
        $this->destinations = [];

        // Clear the removals
        $this->removals = [];

        // Clear the patches
        $this->patches = [];

        return $done;
    }

    /**
     * Add a unified diff file to the patcher
     *
     * @param   string   $filename  Path to the unified diff file
     * @param   string   $root      The files root path
     * @param   integer  $strip     The number of '/' to strip
     *
     * @return  Patcher  $this for chaining
     *
     * @since   3.0.0
     * @deprecated  4.4 will be removed in 6.0
     *              Use Joomla\Filesystem\Patcher::addFile() instead.
     */
    public function addFile($filename, $root = JPATH_BASE, $strip = 0)
    {
        return $this->add(file_get_contents($filename), $root, $strip);
    }

    /**
     * Add a unified diff string to the patcher
     *
     * @param   string   $udiff  Unified diff input string
     * @param   string   $root   The files root path
     * @param   integer  $strip  The number of '/' to strip
     *
     * @return  Patcher  $this for chaining
     *
     * @since   3.0.0
     * @deprecated  4.4 will be removed in 6.0
     *              Use Joomla\Filesystem\Patcher::add() instead.
     */
    public function add($udiff, $root = JPATH_BASE, $strip = 0)
    {
        $this->patches[] = [
            'udiff' => $udiff,
            'root'  => isset($root) ? rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR : '',
            'strip' => $strip,
        ];

        return $this;
    }

    /**
     * Separate CR or CRLF lines
     *
     * @param   string  $data  Input string
     *
     * @return  array  The lines of the inputdestination file
     *
     * @since   3.0.0
     * @deprecated  4.4 will be removed in 6.0
     *              Use Joomla\Filesystem\Patcher::splitLines() instead.
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
     * @param   array   $lines  The udiff array of lines
     * @param   string  $src    The source file
     * @param   string  $dst    The destination file
     *
     * @return  boolean  TRUE in case of success, FALSE in case of failure
     *
     * @since   3.0.0
     * @throws  \RuntimeException
     * @deprecated  4.4 will be removed in 6.0
     *              Use Joomla\Filesystem\Patcher::findHeader() instead.
     */
    protected static function findHeader(&$lines, &$src, &$dst)
    {
        // Get the current line
        $line = current($lines);

        // Search for the header
        while ($line !== false && !preg_match(self::SRC_FILE, $line, $m)) {
            $line = next($lines);
        }

        if ($line === false) {
            // No header found, return false
            return false;
        }

        // Set the source file
        $src = $m[1];

        // Advance to the next line
        $line = next($lines);

        if ($line === false) {
            throw new \RuntimeException('Unexpected EOF');
        }

        // Search the destination file
        if (!preg_match(self::DST_FILE, $line, $m)) {
            throw new \RuntimeException('Invalid Diff file');
        }

        // Set the destination file
        $dst = $m[1];

        // Advance to the next line
        if (next($lines) === false) {
            throw new \RuntimeException('Unexpected EOF');
        }

        return true;
    }

    /**
     * Find the next hunk of difference
     *
     * The internal array pointer of $lines is on the next line after the finding
     *
     * @param   array   $lines    The udiff array of lines
     * @param   string  $srcLine  The beginning of the patch for the source file
     * @param   string  $srcSize  The size of the patch for the source file
     * @param   string  $dstLine  The beginning of the patch for the destination file
     * @param   string  $dstSize  The size of the patch for the destination file
     *
     * @return  boolean  TRUE in case of success, false in case of failure
     *
     * @since   3.0.0
     * @throws  \RuntimeException
     * @deprecated  4.4 will be removed in 6.0
     *              Use Joomla\Filesystem\Patcher::findHunk() instead.
     */
    protected static function findHunk(&$lines, &$srcLine, &$srcSize, &$dstLine, &$dstSize)
    {
        $line = current($lines);

        if (preg_match(self::HUNK, $line, $m)) {
            $srcLine = (int) $m[1];

            $srcSize = 1;

            if ($m[3] !== '') {
                $srcSize = (int) $m[3];
            }

            $dstLine = (int) $m[4];

            $dstSize = 1;

            if ($m[6] !== '') {
                $dstSize = (int) $m[6];
            }

            if (next($lines) === false) {
                throw new \RuntimeException('Unexpected EOF');
            }

            return true;
        }

        return false;
    }

    /**
     * Apply the patch
     *
     * @param   array   $lines    The udiff array of lines
     * @param   string  $src      The source file
     * @param   string  $dst      The destination file
     * @param   string  $srcLine  The beginning of the patch for the source file
     * @param   string  $srcSize  The size of the patch for the source file
     * @param   string  $dstLine  The beginning of the patch for the destination file
     * @param   string  $dstSize  The size of the patch for the destination file
     *
     * @return  void
     *
     * @since   3.0.0
     * @throws  \RuntimeException
     * @deprecated  4.4 will be removed in 6.0
     *              Use Joomla\Filesystem\Patcher::applyHunk() instead.
     */
    protected function applyHunk(&$lines, $src, $dst, $srcLine, $srcSize, $dstLine, $dstSize)
    {
        $srcLine--;
        $dstLine--;
        $line = current($lines);

        // Source lines (old file)
        $source = [];

        // New lines (new file)
        $destin   = [];
        $src_left = $srcSize;
        $dst_left = $dstSize;

        do {
            if (!isset($line[0])) {
                $source[] = '';
                $destin[] = '';
                $src_left--;
                $dst_left--;
            } elseif ($line[0] == '-') {
                if ($src_left == 0) {
                    throw new \RuntimeException(Text::sprintf('JLIB_FILESYSTEM_PATCHER_UNEXPECTED_REMOVE_LINE', key($lines)));
                }

                $source[] = substr($line, 1);
                $src_left--;
            } elseif ($line[0] == '+') {
                if ($dst_left == 0) {
                    throw new \RuntimeException(Text::sprintf('JLIB_FILESYSTEM_PATCHER_UNEXPECTED_ADD_LINE', key($lines)));
                }

                $destin[] = substr($line, 1);
                $dst_left--;
            } elseif ($line != '\\ No newline at end of file') {
                $line     = substr($line, 1);
                $source[] = $line;
                $destin[] = $line;
                $src_left--;
                $dst_left--;
            }

            if ($src_left == 0 && $dst_left == 0) {
                // Now apply the patch, finally!
                if ($srcSize > 0) {
                    $src_lines = & $this->getSource($src);

                    if (!isset($src_lines)) {
                        throw new \RuntimeException(
                            Text::sprintf(
                                'JLIB_FILESYSTEM_PATCHER_UNEXISTING_SOURCE',
                                Path::removeRoot($src)
                            )
                        );
                    }
                }

                if ($dstSize > 0) {
                    if ($srcSize > 0) {
                        $dst_lines  = & $this->getDestination($dst, $src);
                        $src_bottom = $srcLine + \count($source);

                        for ($l = $srcLine; $l < $src_bottom; $l++) {
                            if ($src_lines[$l] != $source[$l - $srcLine]) {
                                throw new \RuntimeException(
                                    Text::sprintf(
                                        'JLIB_FILESYSTEM_PATCHER_FAILED_VERIFY',
                                        Path::removeRoot($src),
                                        $l
                                    )
                                );
                            }
                        }

                        array_splice($dst_lines, $dstLine, \count($source), $destin);
                    } else {
                        $this->destinations[$dst] = $destin;
                    }
                } else {
                    $this->removals[] = $src;
                }

                next($lines);

                return;
            }

            $line = next($lines);
        } while ($line !== false);
        throw new \RuntimeException('Unexpected EOF');
    }

    /**
     * Get the lines of a source file
     *
     * @param   string  $src  The path of a file
     *
     * @return  array  The lines of the source file
     *
     * @since   3.0.0
     * @deprecated  4.4 will be removed in 6.0
     *              Use Joomla\Filesystem\Patcher::getSource() instead.
     */
    protected function &getSource($src)
    {
        if (!isset($this->sources[$src])) {
            $this->sources[$src] = null;

            if (is_readable($src)) {
                $this->sources[$src] = self::splitLines(file_get_contents($src));
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
     * @since   3.0.0
     * @deprecated  4.4 will be removed in 6.0
     *              Use Joomla\Filesystem\Patcher::getDestination() instead.
     */
    protected function &getDestination($dst, $src)
    {
        if (!isset($this->destinations[$dst])) {
            $this->destinations[$dst] = $this->getSource($src);
        }

        return $this->destinations[$dst];
    }
}
