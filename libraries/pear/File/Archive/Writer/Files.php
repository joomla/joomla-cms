<?php
/**
 * @version $Id: CHANGELOG.php 5243 2006-09-30 19:03:32Z davidgal $
 * Writer to files
 *
 * PHP versions 4 and 5
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330,Boston,MA 02111-1307 USA
 *
 * @category   File Formats
 * @package    File_Archive
 * @author     Vincent Lascaux <vincentlascaux@php.net>
 * 			   Modified by David Gal <david.gal@joomla.org>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL
 * @version    CVS: $Id: Files.php,v 1.21 2005/06/18 23:08:16 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 *
 * NOTE: modified for use with Joomla!
 */

//require_once "File/Archive/Writer.php";
jimport('pear.File.Archive.Writer');

/**
  * Writer to files
  */
class File_Archive_Writer_Files extends File_Archive_Writer
{
    /**
     * @var Object Handle to the file where the data are currently written
     * @access private
     */
    var $handle = null;
    var $basePath;
    var $stat = array();
    var $filename;

    function File_Archive_Writer_Files($base = '')
    {
        if ($base === null || $base == '') {
            $this->basePath = '';
        } else {
            if (substr($base, -1) == '/') {
                $this->basePath = $base;
            } else {
                $this->basePath = $base.'/';
            }
        }
    }

    function getFilename($filename)
    {
        return $this->basePath.$filename;
    }

    /**
     * Ensure that $pathname exists, or create it if it does not
     * @access private
     */
    function mkdirr($pathname)
    {
        // Check if directory already exists
        if (is_dir($pathname) || empty($pathname)) {
            return;
        }

        // Ensure a file does not already exist with the same name
        if (is_file($pathname)) {
            return PEAR::raiseError(
                "File $pathname exists, unable to create directory"
            );
        }

        // Crawl up the directory tree
        $next_pathname = substr(
                    $pathname,
                    0, strrpos($pathname, "/"));
        $error = $this->mkdirr($next_pathname);
        if (PEAR::isError($error)) {
            return $error;
        }

        //Following change made by david.gal@joomla.org
        //to allow use with Joomla! in safe mode
//      if (!@mkdir($pathname)) {
        if (!JFolder::create($pathname, 0777)) {
            return PEAR::raiseError("Unable to create directory $pathname");
        }
    }

    /**
     * Open a file for writing from a given position
     *
     * @param string $filename The name of the file to open
     * @param int $pos the initial position in the file
     * @param $stat the stats of the file
     */
    function openFile($filename, $pos = 0)
    {
        $this->close();

        $this->handle = fopen($filename, 'r+');
        $this->stat = array();
        $this->filename = $filename;

        if (!is_resource($this->handle)) {
            return PEAR::raiseError("Unable to open file $filename");
        }

        if ($pos > 0) {
            if (fseek($this->handle, $pos) == -1) {
                fread($this->handle, $pos);
            }
        }
    }

    /**
     * Open a file for appending after having removed a block of data from it
     * See File_Archive_Reader::makeWriterRemoveBlocks
     */
    function openFileRemoveBlock($filename, $pos, $blocks)
    {
        $error = $this->openFile($filename, $pos);
        if (PEAR::isError($error)) {
            return $error;
        }

        if (!empty($blocks)) {
            //This will be used to read the initial file
            //The data, with the unusefull block removed will be written to $this->handle
            $read = fopen($filename, 'r');
            if ($pos > 0) {
                if (fseek($this->handle, $pos) == -1) {
                    fread($this->handle, $pos);
                }
            }

            $keep = false;
            $data = '';
            foreach ($blocks as $length) {
                if ($keep) {
                    while ($length > 0 &&
                           ($data = fread($read, min($length, 8192))) != '') {
                        $length -= strlen($data);
                        fwrite($this->handle, $data);
                    }
                } else {
                    fseek($read, $length, SEEK_CUR);
                }
                $keep = !$keep;
            }
            if ($keep) {
                while(!feof($this->handle)) {
                    fwrite($this->handle, fread($read, 8196));
                }
            }

            fclose($read);
        }

        ftruncate($this->handle, ftell($this->handle));
    }


    /**
     * @see File_Archive_Writer::newFile()
     */
    function newFile($filename, $stat = array(), $mime = "application/octet-stream")
    {
        $this->close();
        $this->stat = $stat;
        $this->filename = $this->getFilename($filename);

        $pos = strrpos($this->filename, "/");
        if ($pos !== false) {
            $error = $this->mkdirr(substr($this->filename, 0, $pos));
            if (PEAR::isError($error)) {
                return $error;
            }
        }
        $this->handle = @fopen($this->filename, "w");
        if (!is_resource($this->handle)) {
            return PEAR::raiseError("Unable to write to file $filename");
        }
    }
    /**
     * @see File_Archive_Writer::writeData()
     */
    function writeData($data) { fwrite($this->handle, $data); }
    /**
     * @see File_Archive_Writer::newFromTempFile()
     */
    function newFromTempFile($tmpfile, $filename, $stat = array(), $mime = "application/octet-stream")
    {
        $this->filename = filename;
        $complete = $this->getFilename($filename);
        $pos = strrpos($complete, "/");
        if ($pos !== false) {
            $error = $this->mkdirr(substr($complete, 0, $pos));
            if (PEAR::isError($error)) {
                return $error;
            }
        }

        if ((file_exists($complete) && !@unlink($complete)) ||
            !@rename($tmpfile, $complete)) {
            return parent::newFromTempFile($tmpfile, $filename, $stat, $mime);
        }
    }


    /**
     * @see File_Archive_Writer::close()
     */
    function close()
    {
        if ($this->handle !== null) {
            fclose($this->handle);
            $this->handle = null;

            if (isset($this->stat[9])) {
                if (isset($this->stat[8])) {
                    touch($this->filename, $this->stat[9], $this->stat[8]);
                } else {
                    touch($this->filename, $this->stat[9]);
                }
            } else if (isset($this->stat[8])) {
                touch($this->filename, time(), $this->stat[8]);
            }

            if (isset($this->stat[2])) {
                chmod($this->filename, $this->stat[2]);
            }
            if (isset($this->stat[5])) {
                chgrp($this->filename, $this->stat[5]);
            }
            if (isset($this->stat[4])) {
                chown($this->filename, $this->stat[4]);
            }
        }
    }
}

?>