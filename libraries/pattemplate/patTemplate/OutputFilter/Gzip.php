<?PHP
/**
 * patTemplate GZip output filter
 *
 * $Id: Gzip.php 47 2005-09-15 02:55:27Z rhuk $
 *
 * Checks the accept encoding of the browser and
 * compresses the data before sending it to the client.
 *
 * @package		patTemplate
 * @subpackage	Filters
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * patTemplate GZip output filter
 *
 * $Id: Gzip.php 47 2005-09-15 02:55:27Z rhuk $
 *
 * Checks the accept encoding of the browser and
 * compresses the data before sending it to the client.
 *
 * @package		patTemplate
 * @subpackage	Filters
 * @author		Stephan Schmidt <schst@php.net>
 */
class patTemplate_OutputFilter_Gzip extends patTemplate_OutputFilter
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
	var	$_name	=	'Gzip';

   /**
	* compress the data
	*
	* @access	public
	* @param	string		data
	* @return	string		compressed data
	*/
	function apply( $data )
	{
		if (!$this->_clientSupportsGzip()) {
			return $data;
		}

		$size = strlen( $data );
		$crc  = crc32( $data );

		$data = gzcompress( $data, 9 );
		$data = substr( $data, 0, strlen( $data ) - 4 );

		$data .= $this->_gfc( $crc );
		$data .= $this->_gfc( $size );

		header( 'Content-Encoding: gzip' );
		$data = "\x1f\x8b\x08\x00\x00\x00\x00\x00" . $data;
		return $data;
	}

   /**
	* check, whether client supports compressed data
	*
	* @access	private
	* @return	boolean
	*/
	function _clientSupportsGzip()
	{
		if (!isset($_SERVER['HTTP_ACCEPT_ENCODING'])) {
			return false;
		}

		if (false !== strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
			return  true;
		}
		return  false;
	}

   /**
	* get value as hex-string
	*
	* @access      public
	* @param       integer $value  value to convert
	* @return      string  $string converted string
	*/
	function _gfc( $value )
	{
		$str = '';
		for ($i = 0; $i < 4; $i ++) {
			$str  .= chr( $value % 256 );
			$value = floor( $value / 256 );
		}
		return  $str;
	}
}
?>