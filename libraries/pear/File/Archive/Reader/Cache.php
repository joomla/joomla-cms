<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This reader caches the files of another reader
 * It allow fast access to files. This is usefull if the access to the reader
 * is slow (HTTP, FTP...), but will need more IO if the file is only extracted
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
 * @version    CVS: $Id: Cache.php,v 1.1 2005/07/07 12:24:58 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Reader.php";
jimport('pear.File.Archive.Reader');

/**
 * This reader caches the files of another reader
 * It allow fast access to files. This is usefull if the access to the reader
 * is slow (HTTP, FTP...), but will need more IO if the file is only extracted
 */
class File_Archive_Reader_Cache extends File_Archive_Reader
{
    var $tmpFile;
    var $files = array();
    var $pos = 0;
    var $fromSource = true;
    var $endOfSource = false;
    var $source;

    /**
     * $source is the reader to filter
     */
    function File_Archive_Reader_Cache(&$source)
    {
        $this->source =& $source;
        $this->tmpFile = tmpfile();
    }

    function _writeEndOfFile()
    {
        $bufferSize = File_Archive::getOption('blockSize');
        while (($data = $this->source->getData($bufferSize))!=null) {
            fwrite($this->tmpFile, $data);
        }
    }
    /**
     * @see File_Archive_Reader::next()
     */
    function next()
    {
        //Write the end of the current file to the temp file
        if ($this->fromSource && !empty($this->files)) {
            $this->_writeEndOfFile();
        }

        if ($this->pos+1 < count($this->files) && !$this->fromSource) {
            $this->pos++;
            fseek($this->tmpFile, $this->files[$this->pos]['pos'], SEEK_SET);
            return true;
        } else {
            $this->fromSource = true;
            if ($this->endOfSource) {
                return false;
            }

            $ret = $this->source->next();
            if ($ret !== true) {
                $this->endOfSource = true;
                $this->source->close();
                return $ret;
            }

            $this->endOfSource = false;
            fseek($this->tmpFile, 0, SEEK_END);
            $this->files[] = array(
                'name' => $this->source->getFilename(),
                'stat' => $this->source->getStat(),
                'mime' => $this->source->getMime(),
                'pos'  => ftell($this->tmpFile)
            );
            $this->pos = count($this->files)-1;

            return true;
        }
    }

    /**
     * @see File_Archive_Reader::getFilename()
     */
    function getFilename() { return $this->files[$this->pos]['name']; }
    /**
     * @see File_Archive_Reader::getStat()
     */
    function getStat() { return $this->files[$this->pos]['stat']; }
    /**
     * @see File_Archive_Reader::getMime()
     */
    function getMime() { return $this->files[$this->pos]['mime']; }
    /**
     * @see File_Archive_Reader::getDataFilename()
     */
    function getDataFilename() { return null; }
    /**
     * @see File_Archive_Reader::getData()
     */
    function getData($length = -1)
    {
        if ($this->fromSource) {
            $data = $this->source->getData($length);
            if (PEAR::isError($data)) {
                return $data;
            }

            fwrite($this->tmpFile, $data);
            return $data;
        } else {
            if ($length == 0) {
                return '';
            }

            if ($length > 0 && $this->pos+1 < count($this->files)) {
                $maxSize = $this->files[$this->pos+1]['pos'] - ftell($this->tmpFile);
                if ($maxSize == 0) {
                    return null;
                }
                if ($length > $maxSize) {
                    $length = $maxSize;
                }
                return fread($this->tmpFile, $length);
            } else {
                $contents = '';
                $blockSize = File_Archive::getOption('blockSize');
                while (!feof($this->tmpFile)) {
                    $contents .= fread($this->tmpFile, $blockSize);
                }
                return $contents == '' ? null : $contents;
            }
        }
    }
    /**
     * @see File_Archive_Reader::skip()
     */
    function skip($length = -1)
    {
        if ($this->fromSource) {
            return strlen($this->getData($length));
        } else {
            if ($length >= 0 && $this->pos+1 < count($this->files)) {
                $maxSize = $this->files[$this->pos+1]['pos'] - ftell($this->tmpFile);
                if ($maxSize == 0) {
                    return null;
                }
                if ($length > $maxSize) {
                    $length = $maxSize;
                }
                fseek($this->tmpFile, $length, SEEK_CUR);
                return $length;
            } else {
                $before = ftell($this->tmpFile);
                fseek($this->tmpFile, 0, SEEK_SET);
                $after = fteel($this->tmpFile);
                return $after - $before;
            }
        }
    }
    /**
     * @see File_Archive_Reader::rewind()
     */
    function rewind($length = -1)
    {
        if ($this->fromSource) {
            $this->_writeEndOfFile();
            $this->fromSource = false;
        }
        $before = ftell($this->tmpFile);
        $pos = $this->files[$this->pos]['pos'];
        fseek($this->tmpFile, $pos, SEEK_SET);
        return $pos - $before;
    }
    /**
     * @see File_Archive_Reader::tell()
     */
    function tell()
    {
        return ftell($this->tmpFile) - $this->files[$this->pos]['pos'];
    }
    /**
     * @see File_Archive_Reader::close()
     */
    function close()
    {
        $this->fromSource = false;
        $this->pos = 0;
        fseek($this->tmpFile, 0, SEEK_SET);
    }
    function _closeAndReset()
    {
        $this->close();

        fclose($this->tmpFile);
        $this->tmpFile = tmpfile();
        $this->endOfSource = false;
        $this->files = array();
        $this->source->close();
    }
    /**
     * @see File_Archive_Reader::makeAppendWriter()
     */
    function makeAppendWriter()
    {
        $writer = $this->source->makeAppendWriter();
        if (!PEAR::isError($writer)) {
            $this->_closeAndReset();
        }

        return $writer;
    }
    /**
     * @see File_Archive_Reader::makeWriterRemoveFiles()
     */
    function makeWriterRemoveFiles($pred)
    {
        $writer = $this->source->makeWriterRemoveFiles($pred);
        if (!PEAR::isError($writer)) {
            $this->_closeAndReset();
        }
        return $writer;
    }
    /**
     * @see File_Archive_Reader::makeWriterRemoveBlocks()
     */
    function makeWriterRemoveBlocks($blocks, $seek = 0)
    {
        $writer = $this->source->makeWriterRemoveBlocks($blocks, $seek);
        if (!PEAR::isError($writer)) {
            $this->_closeAndReset();
        }
        return $writer;
    }
}

?>