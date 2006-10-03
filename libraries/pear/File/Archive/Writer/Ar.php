<?php
/**
 * Write data to a file and save as an ar
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
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.gnu.org/copyleft/lesser.html  LGPL
 * @version    CVS: $Id:
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Writer/Archive.php";
jimport('pear.File.Archive.Writer.Archive');

/**
 * Write the files as an AR archive
 */
class File_Archive_Writer_Ar extends File_Archive_Writer_Archive
{

    /**
     * @var    string   Current data of the file.
     * @access private
     */
    var $_buffer = "";

    /**
     * @var    string   Filename of the current filename
     * @access private
     */
    var $_currentFilename = null;

    /**
     * @var    boolean  Flag: use buffer or not.
     * @access private
     */
    var $_useBuffer;

    /**
     * @var    array    Stats of the current filename
     * @access private
     */
    var $_currentStat = array ();

    /**
     * @var    boolean  Flag: beginning of the archive or not
     * @access private
     */
    var $_atStart = true;

    /**
     * Returns the header of the current file.
     *
     * More Info:
     *  http://publibn.boulder.ibm.com/doc_link/en_US/a_doc_lib/files/aixfiles/ar_IA64.htm
     *
     * @access  private
     * @param   string  $filename  Name of the current file
     * @param   array   $stat      Stat array of the current file
     * @return  string  The built header struct
     */
    function arHeader ($filename, $stat)
    {
        $mode = isset($stat[2]) ? $stat[2] : 0x8000;
        $uid  = isset($stat[4]) ? $stat[4] : 0;
        $gid  = isset($stat[5]) ? $stat[5] : 0;
        $size = $stat[7];
        $time = isset($stat[9]) ? $stat[9] : time();

        $struct = "";
        $currentSize = $size;
        //if file length is > than 16..
        if (strlen($filename) > 16) {
            $currentSize += strlen($filename);
            $struct .= sprintf("#1/%-13d", strlen($filename));
            $struct .= sprintf("%-12d%-6d%-6d%-8s%-10d",
                               $time, $uid, $gid, $mode, $currentSize);
            $struct .=  "`\n".$filename;
        } else {
            $struct .= sprintf("%-16s", $filename);
            $struct .= sprintf("%-12d%-6d%-6d%-8s%-10d`\n",
                               $time, $uid, $gid, $mode, $size);
        }
        return $struct;
    }

    /**
     * Returns the footer of the current file, the footer depends
     * of the size of the file
     *
     * @access  private
     * @param   string   $filename Name of the file, the footer depends on its length
     * @param   int      $size     Size of the current file, here the size does matters!
     * @return  string   The footer struct
     */
    function arFooter($filename, $size)
    {
        $size = (strlen ($filename) > 16) ? $size + strlen($filename) : $size;

        return ($size % 2 == 1) ? "\n" : "";
    }


    /**
     * Flush the memory we have in the ar.
     *
     * Build the buffer if its called at the end or initialize
     * it if we are just creating it from the start.
     */
    function flush()
    {
        if ($this->_atStart) {
            $this->innerWriter->writeData("!<arch>\n");
            $this->_atStart = false;
        }
        if ($this->_currentFilename !== null) {
            $this->_currentStat[7] = strlen($this->_buffer);
            if ($this->_useBuffer) {
                $this->innerWriter->writeData(
                                              $this->arHeader($this->_currentFilename, $this->_currentStat)
                                              );
                $this->innerWriter->writeData($this->_buffer);
            }
            $this->innerWriter->writeData($this->arFooter($this->_currentFilename, $this->_currentStat[7]));
        }
        $this->_buffer = "";
    }

    /**
     * @see File_Archive_Writer::newFile()
     *
     */
    function newFile($filename, $stat = array (),
                     $mime = "application/octet-stream")
    {
        $this->flush();
        /*
         * If the file is empty, there's no reason to have a buffer
         * or use memory
         */
        $this->_useBuffer = !isset($stats[7]);
        /*
         * Becaue ar fileformats doesn't support files in directories,
         * then we need to just save with the filename an ommit the
         * directory
         */
        $this->_currentFilename = basename($filename);
        $this->_currentStat = $stat;

        if(!$this->_useBuffer) {
            return $this->innerWriter->writeData($this->arHeader($filename, $stat));
        }
    }

    /**
     * @see File_Archive_Writer::close()
     */
    function close()
    {
        $this->flush();
        parent::close();
    }

    /**
     * @see File_Archive_Writer::writeData()
     */
    function writeData($data)
    {
        if ($this->_useBuffer) {
            $this->_buffer .= $data;
        } else {
            $this->innerWriter->writeData($data);
        }

    }
    /**
     * @see File_Archive_Writer::writeFile()
     */
    function writeFile($filename)
    {
        if ($this->_useBuffer) {
            $this->_buffer .= file_get_contents($filename);
        } else {
            $this->innerWriter->writeFile($filename);
        }
    }
}