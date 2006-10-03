<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Recursively uncompress every file it finds
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
 * @version    CVS: $Id: Uncompress.php,v 1.32 2005/07/09 12:54:35 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Reader.php";
jimport('pear.File.Archive.Reader');
//require_once "File/Archive/Reader/ChangeName.php";
jimport('pear.File.Archive.Reader.ChangeName');

/**
 * Recursively uncompress every file it finds
 */
class File_Archive_Reader_Uncompress extends File_Archive_Reader_Relay
{
    /**
     * @var Array Stack of readers
     * @access private
     */
    var $readers = array();

    /**
     * @var array of readers to close when closing $this
     * @access private
     */
    var $toClose = array();

    /**
     * @var File_Archive_Reader Reader from which all started (usefull to be
     *      able to close)
     * @access private
     */
    var $startReader;

    /**
     * @var Int Maximum depth of uncompression after the basicDir
     *          (that may contain some uncompression also)
     *          -1 means no limit
     * @access private
     */
    var $uncompressionLevel;

    /**
     * @var array Only files starting with $baseDir will be reported
     *      This array contains explode('/', $directoryName)
     * @access private
     */
    var $baseDir = '';

    /**
     * @var int Compression level required to go to reach the baseDir
     *          or null if it is currently being computed
     * @access private
     */
    var $baseDirCompressionLevel = null;

    /**
     * @var int We are selecting substr($baseDir, 0, $baseDirProgression)
     */
    var $baseDirProgression = 0;

    /**
     * @var boolean Flag set to indicate that the current file has not been
     *              displayed
     */
    var $currentFileNotDisplayed = false;

    function File_Archive_Reader_Uncompress(
                        &$innerReader, $uncompressionLevel = -1)
    {
        parent::File_Archive_Reader_Relay($innerReader);
        $this->startReader =& $innerReader;
        $this->uncompressionLevel = $uncompressionLevel;
    }

    /**
     * Attempt to change the current source (if the current file is an archive)
     * If this is the case, push the current source onto the stack and make the
     * good archive reader the current source. A file is considered as an
     * archive if its extension is one of tar, gz, zip, tgz
     *
     * @return bool whether the source has been pushed or not
     * @access private
     */
    function push()
    {
        if ($this->uncompressionLevel >= 0 &&
            $this->baseDirCompressionLevel !== null &&
            count($this->readers) >= $this->uncompressionLevel
           ) {
           return false;
        }

        // Check the extension of the file (maybe we need to uncompress it?)
        $filename  = $this->source->getFilename();

        $extensions = explode('.', strtolower($filename));

        $reader =& $this->source;
        $nbUncompressions = 0;

        while (($extension = array_pop($extensions)) !== null) {
            $nbUncompressions++;
            unset($next);
            $next = File_Archive::readArchive($extension, $reader, $nbUncompressions == 1);
            if ($next === false) {
                $extensions = array();
            } else {
                unset($reader);
                $reader =& $next;
            }
        }
        if ($nbUncompressions == 1) {
            return false;
        } else {
            $this->readers[count($this->readers)] =& $this->source;
            unset($this->source);
            $this->source = new File_Archive_Reader_AddBaseName(
                $filename, $reader
            );
            return true;
        }
    }
    /**
     * @see File_Archive_Reader::close()
     */
    function next()
    {
        if ($this->currentFileNotDisplayed) {
            $this->currentFileNotDisplayed = false;
            return true;
        }

        do {
            do {
                $selection = substr($this->baseDir, 0, $this->baseDirProgression);
                if ($selection === false) {
                    $selection = '';
                }

                $error = $this->source->select($selection, false);
                if (PEAR::isError($error)) {
                    return $error;
                }
                if (!$error) {
                    if (empty($this->readers)) {
                        return false;
                    }
                    $this->source->close();
                    unset($this->source);
                    $this->source =& $this->readers[count($this->readers)-1];
                    unset($this->readers[count($this->readers)-1]);
                }
            } while (!$error);

            $filename = $this->source->getFilename();
            if (strlen($filename) < strlen($this->baseDir)) {
                $goodFile = (strncmp($filename, $this->baseDir, strlen($filename)) == 0 &&
                             $this->baseDir{strlen($filename)} == '/');
                if ($goodFile) {
                    if (strlen($filename) + 2 < strlen($this->baseDirProgression)) {
                        $this->baseDirProgression = strpos($this->baseDir, '/', strlen($filename)+2);
                        if ($this->baseDirProgression === false) {
                            $this->baseDirProgression = strlen($this->baseDir);
                        }
                    } else {
                        $this->baseDirProgression = strlen($this->baseDir);
                    }
                }
            } else {
                $goodFile = (strncmp($filename, $this->baseDir, strlen($this->baseDir)) == 0);
                if ($goodFile) {
                    $this->baseDirProgression = strlen($this->baseDir);
                }
            }
        } while ($goodFile && $this->push());

        return true;
    }

    /**
     * Efficiently filter out the files which URL does not start with $baseDir
     * Throws an error if the $baseDir can't be found
     * @return bool Whether baseDir was a directory or a file
     */
    function setBaseDir($baseDir)
    {
        $this->baseDir = $baseDir;
        $this->baseDirProgression = strpos($baseDir, '/');
        if ($this->baseDirProgression === false) {
            $this->baseDirProgression = strlen($baseDir);
        }

        $error = $this->next();
        if ($error === false) {
            return PEAR::raiseError("No directory $baseDir in inner reader");
        } else if (PEAR::isError($error)) {
            return $error;
        }

        $this->currentFileNotDisplayed = true;
        return strlen($this->getFilename())>strlen($baseDir);
    }
    /**
     * @see File_Archive_Reader::select()
     */
    function select($filename, $close = true)
    {
        if ($close) {
            $error = $this->close();
            if (PEAR::isError($close)) {
                return $error;
            }
        }

        $oldBaseDir = $this->baseDir;
        $oldProgression = $this->baseDirProgression;

        $this->baseDir = $filename;
        $this->baseDirProgression = 0;

        $res = $this->next();

        $this->baseDir = $oldBaseDir;
        $this->baseDirProgression = $oldProgression;

        return $res;
    }

    /**
     * @see File_Archive_Reader::close()
     */
    function close()
    {
        for ($i=0; $i<count($this->readers); ++$i) {
            $this->readers[$i]->close();
        }
        //var_dump($this->toClose);
        for ($i=0; $i<count($this->toClose); ++$i) {
            if ($this->toClose[$i] !== null) {
                $this->toClose[$i]->close();
            }
        }

        $this->readers = array();
        $this->toClose = array();
        $error = parent::close();
        $this->baseDirCompressionLevel = null;
        $this->baseDirProgression = 0;

        unset($this->source);
        $this->source =& $this->startReader;
        $this->source->close();
        $this->currentFileNotDisplayed = false;

        return $error;
    }

    /**
     * @see File_Archive_Reader::makeAppendWriter()
     */
    function makeAppendWriter()
    {
        //The reader needs to be open so that the base dir is found
        $error = $this->next();
        if (PEAR::isError($error)) {
            return $error;
        }

        return parent::makeAppendWriter();
    }

    /**
     * @see File_Archive_Reader::makeWriterRemoveFiles()
     */
    function makeWriterRemoveFiles($pred)
    {
        //The reader needs to be open so that the base dir is found
        $error = $this->next();
        if (PEAR::isError($error)) {
            return $error;
        }

        return parent::makeWriterRemoveFiles($pred);
    }
}

?>