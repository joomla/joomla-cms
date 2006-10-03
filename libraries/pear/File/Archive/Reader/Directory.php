<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Recursively reads a directory
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
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL
 * @version    CVS: $Id: Directory.php,v 1.21 2005/07/07 12:24:58 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Reader/Relay.php";
jimport('pear.File.Archive.Reader.Relay');
//require_once "File/Archive/Reader/File.php";
jimport('pear.File.Archive.Reader.File');

/**
 * Recursively reads a directory
 */
class File_Archive_Reader_Directory extends File_Archive_Reader_Relay
{
    /**
     * @var String URL of the directory that must be read
     * @access private
     */
    var $directory;
    /**
     * @var Int The subdirectories will be read up to a depth of maxRecurs
     *          If maxRecurs == 0, the subdirectories will not be read
     *          If maxRecurs == -1, the depth is considered infinite
     * @access private
     */
    var $maxRecurs;
    /**
     * @var Object Handle returned by the openedDirectory function
     * @access private
     */
    var $directoryHandle = null;

    /**
     * $directory is the path of the directory that must be read
     * If $maxRecurs is specified, the subdirectories will be read up to a depth
     * of $maxRecurs. In particular, if $maxRecurs == 0, the subdirectories
     * won't be read.
     */
    function File_Archive_Reader_Directory($directory, $symbolic='',
                                           $maxRecurs=-1)
    {
        parent::File_Archive_Reader_Relay($tmp = null);
        $this->directory = empty($directory) ? '.' : $directory;
        $this->symbolic = $this->getStandardURL($symbolic);
        $this->maxRecurs = $maxRecurs;
    }

    /**
     * @see File_Archive_Reader::close()
     */
    function close()
    {
        $error = parent::close();

        if ($this->directoryHandle !== null) {
            closedir($this->directoryHandle);
            $this->directoryHandle = null;
        }

        return $error;
    }

    /**
     * @see File_Archive_Reader::next()
     *
     * The files are returned in the same order as readdir
     */
    function next()
    {
        if ($this->directoryHandle === null) {
            $this->directoryHandle = opendir($this->directory);
            if (!is_resource($this->directoryHandle)) {
                return PEAR::raiseError(
                    "Directory {$this->directory} not found"
                );
            }
        }

        while ($this->source === null ||
              ($error = $this->source->next()) !== true) {

            if ($this->source !== null) {
                $this->source->close();
            }

            $file = readdir($this->directoryHandle);
            if ($file == '.' || $file == '..') {
                continue;
            }
            if ($file === false) {
                return false;
            }

            $current = $this->directory.'/'.$file;
            if (is_dir($current)) {
                if ($this->maxRecurs != 0) {
                    $this->source = new File_Archive_Reader_Directory(
                        $current, $file.'/', $this->maxRecurs-1
                    );
                }
            } else {
                $this->source = new File_Archive_Reader_File($current, $file);
            }
        }

        return $error;
    }

    /**
     * @see File_Archive_Reader::getFilename()
     */
    function getFilename() { return $this->symbolic . parent::getFilename(); }

    /**
     * @see File_Archive_Reader::makeWriterRemoveFiles()
     */
    function makeWriterRemoveFiles($pred)
    {
        if ($source !== null && $pred->isTrue($this)) {
            $toUnlink = $this->getDataFilename();
        } else {
            $toUnlink = null;
        }

        while ($this->next()) {
            if ($toUnlink !== null &&
                !@unlink($toUnlink)) {
                return PEAR::raiseError("Unable to unlink $toUnlink");
            }
            $toUnlink = ($pred->isTrue($this) ? $this->getDataFilename() : null);
        }
        if ($toUnlink !== null &&
            !@unlink("Unable to unlink $toUnlink")) {
            return PEAR::raiseError($pred);
        }

//        require_once "File/Archive/Writer/Files.php";
        jimport('pear.File.Archive.Writer.Files');

        $writer = new File_Archive_Writer_Files($this->directory);
        $this->close();
        return $writer;
    }

    function &getLastSource()
    {
        if ($this->source === null ||
            is_a($this->source, 'File_Archive_Reader_File')) {
            return $this->source;
        } else {
            return $this->source->getLastSource();
        }
    }

    /**
     * @see File_Archive_Reader::makeWriterRemoveBlocks()
     */
    function makeWriterRemoveBlocks($blocks, $seek = 0)
    {
        $lastSource = &$this->getLastSource();
        if ($lastSource === null) {
            return PEAR::raiseError('No file selected');
        }

//        require_once "File/Archive/Writer/Files.php";
        jimport('pear.File.Archive.Writer.Files');

        $writer = $lastSource->makeWriterRemoveBlocks($blocks, $seek);
        if (!PEAR::isError($writer)) {
            $writer->basePath = $this->directory;
            $this->close();
        }

        return $writer;
    }

    /**
     * @see File_Archive_Reader::makeAppendWriter
     */
    function makeAppendWriter()
    {
//        require_once "File/Archive/Writer/Files.php";
        jimport('pear.File.Archive.Writer.Files');

        if ($this->source === null ||
            is_a($this->source, 'File_Archive_Reader_File') ) {
            $writer = new File_Archive_Writer_Files($this->directory);
        } else {
            $writer = $this->source->makeAppendWriter($seek);
        }

        $this->close();

        return $writer;
    }
}

?>