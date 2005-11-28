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
 * $ID$
 *
 * @package		patTemplate
 * @subpackage	Functions
 * @author		Stephan Schmidt <schst@php.net>
 * @author 		Sebastian Mordziol <argh@php-tools.net>
 */

/**
 * Warning: could not create language folder
 */
 define( 'PATTEMPLATE_FUNCTION_TRANSLATE_WARNING_LANGFOLDER_NOT_CREATABLE', 'patTemplate:Function:Translate:01' );

/**
 * Warning: could not create language file
 */
 define( 'PATTTEMPLATE_FUNCTION_TRANSLATE_WARNING_LANGFILE_NOT_CREATABLE', 'patTemplate:Function:Translate:02' );

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
 * according to the language you set with:
 * <code>
 * $tmpl->setOption( 'lang', 'de' );
 * </code>
 *
 * You can change this behavior with some specific options:
 * <ul>
 *	 <li>
 *		 <b>translationFile</b>: If set, all strings for a
 *		 language will be collected in one file with the
 *		 specified name (without extension, that's added
 *		 automatically)
 *	 </li>
 *	 <li>
 *		 <b>translationUseFolders</b>: if set, all files
 *		 for a language will be stored in subfolders named
 *		 after the language. This option is cumulative
 *		 with the translationFile option.
 *	 </li>
 *	 <li>
 *		 <b>translationAutoCreate</b>: if set, the translation
 *		 files will automatically be created if they don't exist
 *		 so you do not have to create them manually.
 *	 </li>
 *	 <li>
 *		 <b>translationUseLocator</b>: per default, a locator
 *		 string is added to all new sentences that need to be
 *		 translated to help find them amongst the lot. You can
 *		 turn this behavior off by setting this to false.
 *	 </li>
 *	 <li>
 *		 <b>translationLocatorString</b>: per default, the
 *		 locator string is 'Translate', but you can change this
 *		 to any string you like with this option.
 *	 </li>
 * </ul>
 *
 * $ID$
 *
 * @package		patTemplate
 * @subpackage	Functions
 * @author		Stephan Schmidt <schst@php.net>
 * @author 		Sebastian Mordziol <argh@php-tools.net>
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
	* call the function
	*
	* @access	public
	* @param	array	parameters of the function (= attributes of the tag)
	* @param	string	content of the tag
	* @return	string	content to insert into the template
	* @author	Andrew Eddie
	* Function modifed for Joomla!
	*/
	function call( $params, $content )
	{
		$escape = isset( $params['escape'] ) ? $params['escape'] : '';


		// just use the Joomla translation tool
		if( count( $params ) > 0 && key_exists( 'key', $params ) ) {
			$text = JText::_( $params['key'] );
		} else {
			$text = JText::_( $content );
		}

		if ($escape == 'yes' || $escape == 'true') {
			$text = addslashes( $text );
		}
		return $text;
	}
}
?>