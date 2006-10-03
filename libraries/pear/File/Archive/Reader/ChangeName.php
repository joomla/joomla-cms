<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Add a directory to the public name of all the files of a reader
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
 * @version    CVS: $Id: ChangeName.php,v 1.19 2005/07/09 12:54:35 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Reader/Relay.php";
jimport('pear.File.Archive.Reader.Relay');

/**
 * Add a directory to the public name of all the files of a reader
 *
 * Example:
 *  If archive.tar is a file archive containing files a.txt and foo/b.txt
 *  new File_Archive_Reader_AddBaseName('bar',
 *     new File_Archive_Reader_Tar(
 *         new File_Archive_Reader_File('archive.tar')
 *     )
 *  ) is a reader containing files bar/a.txt and bar/foo/b.txt
 */
class File_Archive_Reader_AddBaseName extends File_Archive_Reader_Relay
{
    var $baseName;
    function File_Archive_Reader_AddBaseName($baseName, &$source)
    {
        parent::File_Archive_Reader_Relay($source);
        $this->baseName = $this->getStandardURL($baseName);
    }

    /**
     * Modify the name by adding baseName to it
     */
    function modifyName($name)
    {
        return $this->baseName.
               (empty($this->baseName) || empty($name) ? '': '/').
               $name;
    }

    /**
     * Remove baseName from the name
     * Return false if the name doesn't start with baseName
     */
    function unmodifyName($name)
    {
        if (strncmp($name, $this->baseName.'/', strlen($this->baseName)+1) == 0) {
            $res = substr($name, strlen($this->baseName)+1);
            if ($res === false) {
                return '';
            } else {
                return $res;
            }
        } else if (empty($this->baseName)) {
            return $name;
        } else if ($name == $this->baseName) {
            return '';
        } else {
            return false;
        }
    }

    /**
     * @see File_Archive_Reader::getFilename()
     */
    function getFilename()
    {
        return $this->modifyName(parent::getFilename());
    }
    /**
     * @see File_Archive_Reader::getFileList()
     */
    function getFileList()
    {
        $list = parent::getFileList();
        $result = array();
        foreach ($list as $name) {
            $result[] = $this->modifyName($name);
        }
        return $result;
    }
    /**
     * @see File_Archive_Reader::select()
     */
    function select($filename, $close = true)
    {
        $name = $this->unmodifyName($filename);
        if ($name === false) {
            return false;
        } else {
            return $this->source->select($name, $close);
        }
    }
}

/**
 * Change a directory name to another
 *
 * Example:
 *  If archive.tar is a file archive containing files a.txt and foo/b.txt
 *  new File_Archive_Reader_ChangeBaseName('foo', 'bar'
 *     new File_Archive_Reader_Tar(
 *         new File_Archive_Reader_File('archive.tar')
 *     )
 *  ) is a reader containing files a.txt and bar/b.txt
 */
class File_Archive_Reader_ChangeBaseName extends File_Archive_Reader_Relay
{
    var $oldBaseName;
    var $newBaseName;

    function File_Archive_Reader_ChangeBaseName
                        ($oldBaseName, $newBaseName, &$source)
    {
        parent::File_Archive_Reader_Relay($source);
        $this->oldBaseName = $this->getStandardURL($oldBaseName);
        if (substr($this->oldBaseName, -1) == '/') {
            $this->oldBaseName = substr($this->oldBaseName, 0, -1);
        }

        $this->newBaseName = $this->getStandardURL($newBaseName);
        if (substr($this->newBaseName, -1) == '/') {
            $this->newBaseName = substr($this->newBaseName, 0, -1);
        }
    }

    function modifyName($name)
    {
        if (empty($this->oldBaseName) ||
          !strncmp($name, $this->oldBaseName.'/', strlen($this->oldBaseName)+1) ||
           strcmp($name, $this->oldBaseName) == 0) {
            return $this->newBaseName.
                   (
                    empty($this->newBaseName) ||
                    strlen($name)<=strlen($this->oldBaseName)+1 ?
                    '' : '/'
                   ).
                   substr($name, strlen($this->oldBaseName)+1);
        } else {
            return $name;
        }
    }
    function unmodifyName($name)
    {
        if (empty($this->newBaseName) ||
          !strncmp($name, $this->newBaseName.'/', strlen($this->newBaseName)+1) ||
           strcmp($name, $this->newBaseName) == 0) {
            return $this->oldBaseName.
                   (
                    empty($this->oldBaseName) ||
                    strlen($name)<=strlen($this->newBaseName)+1 ?
                    '' : '/'
                   ).
                   substr($name, strlen($this->newBaseName)+1);
        } else {
            return $name;
        }
    }

    /**
     * @see File_Archive_Reader::getFilename()
     */
    function getFilename()
    {
        return $this->modifyName(parent::getFilename());
    }
    /**
     * @see File_Archive_Reader::getFileList()
     */
    function getFileList()
    {
        $list = parent::getFileList();
        $result = array();
        foreach ($list as $name) {
            $result[] = $this->modifyName($name);
        }
        return $result;
    }
    /**
     * @see File_Archive_Reader::select()
     */
    function select($filename, $close = true)
    {
        return $this->source->select($this->unmodifyName($filename));
    }

}

?>