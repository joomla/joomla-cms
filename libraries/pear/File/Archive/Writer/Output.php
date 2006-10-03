<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Writer to the standard output
 * It will concatenate the files that it receive
 * It may send some headers, but will do so only for the first file
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
 * @version    CVS: $Id: Output.php,v 1.8 2005/05/30 15:25:14 vincentlascaux Exp $
 * @link       http://pear.php.net/package/File_Archive
 */

//require_once "File/Archive/Writer.php";
jimport('pear.File.Archive.Writer');

/**
 * Writer to the standard output
 * It will concatenate the files that it receive
 * It may send some headers, but will do so only for the first file
 */
class File_Archive_Writer_Output extends File_Archive_Writer
{
    /**
     * @var    bool If true, the Content-type and Content-disposition headers
     *         will be sent. The file will be considered as an attachment and
     *         the MIME will be deduced from its extension
     * @access private
     */
    var $sendHeaders;

    /**
     * @param $sendHeaders see the variable
     */
    function File_Archive_Writer_Output($sendHeaders = true)
    {
        $this->sendHeaders = $sendHeaders;
    }
    /**
     * @see File_Archive_Writer::newFile()
     */
    function newFile($filename, $stat = array(), $mime = "application/octet-stream")
    {
        if ($this->sendHeaders) {
            if(headers_sent()) {
                return PEAR::raiseError(
                    'The headers have already been sent. '.
                    'Use File_Archive::toOutput(false) to write '.
                    'to output without sending headers');
            }

            header("Content-type: $mime");
            header("Content-disposition: attachment; filename=$filename");
            $this->sendHeaders = false;
        }
    }
    /**
     * @see File_Archive_Writer::newFileNeedsMIME
     */
    function newFileNeedsMIME()
    {
        return $this->sendHeaders;
    }
    /**
     * @see File_Archive_Writer::writeData()
     */
    function writeData($data) { echo $data; }
    /**
     * @see File_Archive_Writer::writeFile()
     */
    function writeFile($filename) { readfile($filename); }
}

?>