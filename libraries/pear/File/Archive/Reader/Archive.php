<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Base class for all the archive readers (that read from a single file)
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
 * @version    CVS: $Id: Archive.php,v 1.12 2005/05/23 19:25:24 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Reader.php";
jimport('pear.File.Archive.Reader');

/**
 * Base class for all the archive readers (that read from a single file)
 */
class File_Archive_Reader_Archive extends File_Archive_Reader
{
    /**
     * @var    File_Archive_Reader Single file source that contains the archive
     *         to uncompress
     * @access protected
     */
    var $source = null;

    /**
     * @var    bool Indicate whether the $source is currently opened
     * @access private
     */
    var $sourceOpened = false;

    /**
     * The source was let in this state at the end
     *
     * @var    bool Indicate whether the $source was given opened
     * @access private
     */
    var $sourceInitiallyOpened;

//ABSTRACT
    /**
     * @see File_Archive_Reader::next()
     *
     * Open the source if necessary
     */
    function next()
    {
        if (!$this->sourceOpened && ($error = $this->source->next()) !== true) {
            return $error;
        }

        $this->sourceOpened = true;
        return true;
    }

//PUBLIC
    function File_Archive_Reader_Archive(&$source, $sourceOpened = false)
    {
        $this->source =& $source;
        $this->sourceOpened = $this->sourceInitiallyOpened = $sourceOpened;
    }
    /**
     * Close the source if it was given closed in the constructor
     *
     * @see File_Archive_Reader::close()
     */
    function close()
    {
        if (!$this->sourceInitiallyOpened && $this->sourceOpened) {
            $this->sourceOpened = false;
            if ($this->source !== null) {
                return $this->source->close();
            }
        }
    }
}

?>