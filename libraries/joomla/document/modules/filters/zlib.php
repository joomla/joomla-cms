<?PHP
/**
* @version $Id: zlib.php 1548 2005-12-23 09:07:11Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * JTemplate Zlib output filter
 *
 * Checks the accept encoding of the browser and
 * compresses the data before sending it to the client.
 *
 * @author		Johan Janssens <johan@joomla.be>
 * @subpackage	JDocument
 * @since 1.1
 */
class patTemplate_OutputFilter_Zlib extends patTemplate_OutputFilter
{
   /**
	* filter name
	*
	* This has to be set in the final
	* filter classes.
	*
	* @access	protected
	* @abstract
	* @var	string
	*/
	var	$_name	=	'zlib';

   /**
	* compress the data
	*
	* @access	public
	* @param	string		data
	* @return	string		compressed data
	*/
	function apply( $data )
	{
		$encoding = $this->_clientEncoding();

        if (!$encoding)
			return $data;

		if (!extension_loaded('zlib') || ini_get('zlib.output_compression')) {
			return $data;
        }

		if (headers_sent())
			return $data;

        if (connection_status() !== 0)
			return $data;


        $level = 4; //ideal level

		$size = strlen($data);
        $crc  = crc32($data);

        $gzdata = "\x1f\x8b\x08\x00\x00\x00\x00\x00";
		$gzdata .= gzcompress($data, $level);

		$gzdata  = substr($gzdata, 0, strlen($gzdata) - 4);
        $gzdata .= pack("V",$crc) . pack("V", $size);


		Header('Content-Encoding: ' . $encoding);
        Header('Content-Length: ' . strlen($gzdata));
        Header('X-Content-Encoded-By: patTemplate');

		return $gzdata;
	}

   /**
	* check, whether client supports compressed data
	*
	* @access	private
	* @return	boolean
	*/
	function _clientEncoding()
	{
		if (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
			return false;
		}

		$encoding = false;

		if (false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
			$encoding = 'gzip';
		}

		if (false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip')) {
			$encoding = 'x-gzip';
        }

		return $encoding;
	}
}
?>