<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * A reader that appears exactly as another does
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
 * @version    CVS: $Id: Relay.php,v 1.19 2005/07/09 12:54:35 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Reader.php";
jimport('pear.File.Archive.Reader');

/**
 * This reader appear exactly as $source does
 * This is usefull if you want to dynamically change $source or change
 * its behaviour
 */
class File_Archive_Reader_Relay extends File_Archive_Reader
{
    /**
     * @var    File_Archive_Reader This reader will have the same comportment as
     *         $source
     * @access protected
     */
    var $source;

    function File_Archive_Reader_Relay(&$source)
    {
        $this->source =& $source;
    }

    /**
     * @see File_Archive_Reader::next()
     */
    function next() { return $this->source->next(); }
    /**
     * @see File_Archive_Reader::getFilename()
     */
    function getFilename() { return $this->source->getFilename(); }
    /**
     * @see File_Archive_Reader::getStat()
     */
    function getStat() { return $this->source->getStat(); }
    /**
     * @see File_Archive_Reader::getMime()
     */
    function getMime() { return $this->source->getMime(); }
    /**
     * @see File_Archive_Reader::getDataFilename()
     */
    function getDataFilename() { return $this->source->getDataFilename(); }
    /**
     * @see File_Archive_Reader::getData()
     */
    function getData($length = -1) { return $this->source->getData($length); }
    /**
     * @see File_Archive_Reader::skip()
     */
    function skip($length = -1) { return $this->source->skip($length); }
    /**
     * @see File_Archive_Reader::rewind()
     */
    function rewind($length = -1) { return $this->source->rewind($length); }
    /**
     * @see File_Archive_Reader::tell()
     */
    function tell() { return $this->source->tell(); }

    /**
     * @see File_Archive_Reader::close()
     */
    function close()
    {
        if ($this->source !== null) {
            return $this->source->close();
        }
    }
    /**
     * @see File_Archive_Reader::makeAppendWriter()
     */
    function makeAppendWriter()
    {
        $writer = $this->source->makeAppendWriter();
        if (!PEAR::isError($writer)) {
            $this->close();
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
            $this->close();
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
            $this->close();
        }
        return $writer;
    }
}

?>