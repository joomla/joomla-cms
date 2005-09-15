<?PHP
/**
 * patTemplate BBCode output filter
 *
 * $Id: BBCode.php 138 2005-09-12 10:37:53Z eddieajau $
 *
 * Uses patBBCode.
 *
 * @package		patTemplate
 * @subpackage	Filters
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * patTemplate BBCode output filter
 *
 * $Id: BBCode.php 138 2005-09-12 10:37:53Z eddieajau $
 *
 * Uses patBBCode. Note that patBBCode's syntax is not
 * entirely the same than the 'official' BBCode. See the
 * patBBCode projet page for details.
 *
 * The following parameters are available:
 *
 * - skinDir (required)
 *   The folder where BBCode templates are stored
 *
 * - reader (required)
 *   The type of reader to use
 *
 * - BBCode (optional)
 *   A fully configured BBCode objet to use. The other
 *   two options are not required if you set this.
 *
 * @package		patTemplate
 * @subpackage	Filters
 * @author		Stephan Schmidt <schst@php.net>
 * @author 		Sebastian Mordziol <argh@php-tools.net>
 * @link 		http://www.php-tools.net/site.php?file=patBBCode/Overview.xml
 */
class patTemplate_OutputFilter_BBCode extends patTemplate_OutputFilter
{
   /**
	* filter name
	*
	* @access	protected
	* @abstract
	* @var	string
	*/
	var	$_name	=	'BBCode';

   /**
	* BBCode parser
	*
	* @access	private
	* @var		object patBBCode
	*/
	var $BBCode = null;

   /**
	* remove all whitespace from the output
	*
	* @access	public
	* @param	string		data
	* @return	string		data without whitespace
	*/
	function apply( $data )
	{
		if( !$this->_prepare() )
			return $data;

		$data = $this->BBCode->parseString( $data );

		return $data;
	}

   /**
	* prepare BBCode object
	*
	* @access	private
	*/
	function _prepare()
	{
		// there already is a BBCode object
		if( is_object( $this->BBCode ) ) {
			return true;
		}

		// maybe a fully configured BBCode object was provided?
		if( isset( $this->_params['BBCode'] ) ) {
			$this->BBCode =& $this->_params['BBCode'];
			return true;
		}

		// include the patBBCode class
		if( !class_exists( 'patBBCode' ) )
		{
			if( !@include_once 'pat/patBBCode.php' )
				return false;
		}

		$this->BBCode = &new patBBCode();

		if( isset( $this->_params['skinDir'] ) )
			$this->BBCode->setSkinDir( $this->_params['skinDir'] );

		$reader =& $this->BBCode->createConfigReader( $this->_params['reader'] );

		// give patBBCode the reader we just created
		$this->BBCode->setConfigReader( $reader );

		return true;
	}
}
?>