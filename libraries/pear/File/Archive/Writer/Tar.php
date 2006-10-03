<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Write the files as a TAR archive
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
 * @version    CVS: $Id: Tar.php,v 1.18 2005/06/02 12:24:43 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Writer/Archive.php";
jimport('pear.File.Archive.Writer.Archive');

/**
 * Write the files as a TAR archive
 */
class File_Archive_Writer_Tar extends File_Archive_Writer_Archive
{
    var $buffer;
    var $useBuffer;

    var $filename = null;
    var $stats = null;


    /**
     * Creates the TAR header for a file
     *
     * @param string $filename name of the file
     * @param array $stat statistics of the file
     * @return string A 512 byte header for the file
     * @access private
     */
    function tarHeader($filename, $stat)
    {
        $mode = isset($stat[2]) ? $stat[2] : 0x8000;
        $uid  = isset($stat[4]) ? $stat[4] : 0;
        $gid  = isset($stat[5]) ? $stat[5] : 0;
        $size = $stat[7];
        $time = isset($stat[9]) ? $stat[9] : time();
        $link = "";

        if ($mode & 0x4000) {
            $type = 5;        // Directory
        } else if ($mode & 0x8000) {
            $type = 0;        // Regular
        } else if ($mode & 0xA000) {
            $type = 1;        // Link
            $link = @readlink($current);
        } else {
            $type = 9;        // Unknown
        }

        $filePrefix = '';
        if (strlen($filename) > 255) {
            return PEAR::raiseError(
                "$filename is too long to be put in a tar archive"
            );
        } else if (strlen($filename) > 100) {
            $filePrefix = substr($filename, 0, strlen($filename)-100);
            $filename = substr($filename, -100);
        }

        $blockbeg = pack("a100a8a8a8a12a12",
            $filename,
            decoct($mode),
            sprintf("%6s ",decoct($uid)),
            sprintf("%6s ",decoct($gid)),
            sprintf("%11s ",decoct($size)),
            sprintf("%11s ",decoct($time))
            );

        $blockend = pack("a1a100a6a2a32a32a8a8a155a12",
            $type,
            $link,
            "ustar",
            "00",
            "Unknown",
            "Unknown",
            "",
            "",
            $filePrefix,
            "");

        $checksum = 8*ord(" ");
        for ($i = 0; $i < 148; $i++) {
            $checksum += ord($blockbeg{$i});
        }
        for ($i = 0; $i < 356; $i++) {
            $checksum += ord($blockend{$i});
        }

        $checksum = pack("a8",sprintf("%6s ",decoct($checksum)));

        return $blockbeg . $checksum . $blockend;
    }
    /**
     * Creates the TAR footer for a file
     *
     * @param  int $size the size of the data that has been written to the TAR
     * @return string A string made of less than 512 characteres to fill the
     *         last 512 byte long block
     * @access private
     */
    function tarFooter($size)
    {
        if ($size % 512 > 0) {
            return pack("a".(512 - $size%512), "");
        } else {
            return "";
        }
    }

    function flush()
    {
        if ($this->filename !== null) {
            if ($this->useBuffer) {
                $this->stats[7] = strlen($this->buffer);

                $this->innerWriter->writeData(
                    $this->tarHeader($this->filename, $this->stats)
                );
                $this->innerWriter->writeData(
                    $this->buffer
                );
            }
            $this->innerWriter->writeData(
                $this->tarFooter($this->stats[7])
            );
        }
        $this->buffer = "";
    }

    function newFile($filename, $stats = array(),
                     $mime = "application/octet-stream")
    {
        $this->flush();

        $this->useBuffer = !isset($stats[7]);
        $this->filename = $filename;
        $this->stats = $stats;

        if (!$this->useBuffer) {
            return $this->innerWriter->writeData(
                $this->tarHeader($filename, $stats)
            );
        }
    }

    /**
     * @see File_Archive_Writer::close()
     */
    function close()
    {
        $this->flush();
        $this->innerWriter->writeData(pack("a1024", ""));
        parent::close();
    }
    /**
     * @see File_Archive_Writer::writeData()
     */
    function writeData($data)
    {
        if ($this->useBuffer) {
            $this->buffer .= $data;
        } else {
            $this->innerWriter->writeData($data);
        }

    }
    /**
     * @see File_Archive_Writer::writeFile()
     */
    function writeFile($filename)
    {
        if ($this->useBuffer) {
            $this->buffer .= file_get_contents($filename);
        } else {
            $this->innerWriter->writeFile($filename);
        }
    }
    /**
     * @see File_Archive_Writer::getMime()
     */
    function getMime() { return "application/x-tar"; }
}


/**
 * A tar archive cannot contain files with name of folders longer than 255 chars
 * This filter removes them
 *
 * @see File_Archive_Predicate, File_Archive_Reader_Filter
 */
//require_once "File/Archive/Predicate.php";
jimport('pear.File.Archive.Predicate');
class File_Archive_Predicate_TARCompatible extends File_Archive_Predicate
{
    function isTrue($source)
    {
        return strlen($source->getFilename()) <= 255;
    }
}

?>