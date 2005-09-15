<?PHP
/**
 * patTemplate function that emulates gettext's behaviour
 *
 * This can be used to create multi-lingual websites.
 * When the template is read, all texts inside the
 * Translation tags are extracted and written to a file
 * called '$tmplname-default.ini'.
 *
 * You should copy this file and translate all sentences.
 * When the template is used the next time, the sentences
 * will be replaced with their respective translations,
 * according to the langanuge you set with:
 * <code>
 * $tmpl->setOption( 'lang', 'de' );
 * </code>
 *
 * $Id: Translate.php 137 2005-09-12 10:21:17Z eddieajau $
 *
 * @package		patTemplate
 * @subpackage	Functions
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * patTemplate function that emulates gettext's behaviour
 *
 * This can be used to create multi-lingual websites.
 * When the template is read, all texts inside the
 * Translation tags are extracted and written to a file
 * called '$tmplname-default.ini'.
 *
 * You should copy this file and translate all sentences.
 * When the template is used the next time, the sentences
 * will be replaced with their respective translations,
 * according to the langanuge you set with:
 * <code>
 * $tmpl->setOption( 'lang', 'de' );
 * </code>
 *
 * $Id: Translate.php 137 2005-09-12 10:21:17Z eddieajau $
 *
 * @package		patTemplate
 * @subpackage	Functions
 * @author		Stephan Schmidt <schst@php.net>
 * @todo		add error management
 */
class patTemplate_Function_Translate extends patTemplate_Function
{
   /**
	* name of the function
	* @access	private
	* @var		string
	*/
	var $_name	=	'Translate';

   /**
	* configuration of all files
	*
	* @access	private
	* @var		array
	*/
	var $_config	=	array();

   /**
	* global config
	*
	* @access	private
	* @var		array
	*/
	var $_globalconfig	=	array();

   /**
    * reference to the patTemplate object that instantiated the module
	*
	* @access	protected
	* @var	object
	*/
	var	$_tmpl;

   /**
    * set a reference to the patTemplate object that instantiated the reader
	*
	* @access	public
	* @param	object		patTemplate object
	*/
	function setTemplateReference( &$tmpl )
	{
		$this->_tmpl		=	&$tmpl;
	}

   /**
	* call the function
	*
	* @access	public
	* @param	array	parameters of the function (= attributes of the tag)
	* @param	string	content of the tag
	* @return	string	content to insert into the template
	*/
	function call( $params, $content )
	{
		global $_LANG;
		$escape = isset( $params['escape'] ) ? $params['escape'] : '';

		// just use the Joomla! translation tool
		if( count( $params ) > 0 && key_exists( 'key', $params ) ) {
			$text = $_LANG->_( $params['key'] );
		} else {
			$text = $_LANG->_( $content );
		}
		if ($escape == 'yes' || $escape == 'true') {
			$text = addslashes( $text );
		}
		return $text;
	}
}
?>