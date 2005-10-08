<?PHP
/**
 * patTemplate function that enables you to insert any
 * template, that has been loaded previously into the
 * current template.
 *
 * You may pass any variables to the template.
 *
 * $Id$
 *
 * @package		patTemplate
 * @subpackage	Functions
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * template does not exist
 */
define( 'PATTEMPLATE_FUNCTION_CALL_ERROR_NO_TEMPLATE', 'patTemplate::Function::Call::NT' );

/**
 * patTemplate function that enables you to insert any
 * template, that has been loaded previously into the
 * current template.
 *
 * You may pass any variables to the template.
 *
 * $Id$
 *
 * @package		patTemplate
 * @subpackage	Functions
 * @author		Stephan Schmidt <schst@php.net>
 */
class patTemplate_Function_Call extends patTemplate_Function
{
   /**
	* name of the function
	* @access	private
	* @var		string
	*/
	var $_name	=	'Call';

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
		$this->_tmpl = &$tmpl;
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
		// get the name of the template to use
		if (isset($params['template'])) {
			$tmpl = $params['template'];
			unset( $params['template'] );
		} elseif (isset($params['_originalTag'])) {
			$tmpl = $params['_originalTag'];
			unset( $params['_originalTag'] );
		} else {
			return patErrorManager::raiseError( PATTEMPLATE_FUNCTION_CALL_ERROR_NO_TEMPLATE, 'No template for Call function specified.' );
		}

		if (!$this->_tmpl->exists( $tmpl )) {

			$tmpl = strtolower($tmpl);

			// try some autoloading magic
			$componentLocation  = $this->_tmpl->getOption('componentFolder');
			$componentExtension = $this->_tmpl->getOption('componentExtension');
			$filename = $componentLocation . '/' . $tmpl . '.' . $componentExtension;
			$this->_tmpl->readTemplatesFromInput($filename);

			// still does not exist
			if( !$this->_tmpl->exists( $tmpl ) ) {
				return patErrorManager::raiseError( PATTEMPLATE_FUNCTION_CALL_ERROR_NO_TEMPLATE, 'Template '.$tmpl.' does not exist' );
			}
		}

		/**
		 * clear template and all of its dependencies
		 */
		$this->_tmpl->clearTemplate( $tmpl, true );

		/**
		 * add variables
		 */
		$this->_tmpl->addVars( $tmpl, $params );
		$this->_tmpl->addVar( $tmpl, 'CONTENT', $content );

		/**
		 * get content
		 */
		return $this->_tmpl->getParsedTemplate( $tmpl );
	}
}
?>