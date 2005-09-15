<?PHP
/**
 * patTemplate input filter to allow the short modifier syntax
 * that is used by Smarty
 *
 * $Id: ShortModifiers.php 138 2005-09-12 10:37:53Z eddieajau $
 *
 * @package		patTemplate
 * @subpackage	Filters
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * patTemplate input filter to allow the short modifier syntax
 * that is used by Smarty
 *
 * $Id: ShortModifiers.php 138 2005-09-12 10:37:53Z eddieajau $
 *
 * This will replace the variables with patTemplate:var/> tags that
 * have the name and the modifier attribute set.
 *
 * @package		patTemplate
 * @subpackage	Filters
 * @author		Stephan Schmidt <schst@php.net>
 */
class patTemplate_InputFilter_ShortModifiers extends patTemplate_InputFilter
{
   /**
	* filter name
	*
	* @access	private
	* @var	    string
	*/
	var	$_name = 'ShortModifiers';

   /**
	* parameters of the filter
	*
	* @access  private
	* @var     array
	*/
	var $_params = array(
							'copyVars' => true
						);

   /**
	* namespace
	*
	* @access	private
	* @var	    string
	*/
	var	$_ns = null;

   /**
	* reference to the patTemplate object
	*
	* @var	   object patTemplate
	* @access  private
	*/
	var $_tmpl = null;

   /**
	* set the template reference
	*
	* @access	public
	* @param    object patTemplate
	*/
	function setTemplateReference(&$tmpl)
	{
		$this->_tmpl = &$tmpl;
	}

   /**
	* generate the <patTemplate:var/> tag
	*
	* @access	public
	* @param	array       matches from preg_replace
	* @return	string		tag
	*/
	function _generateReplace($matches)
	{
		if ($this->getParam('copyVars') === true) {
			$newName = $matches[2] . '_' . $matches[3];
			$replace = $matches[1] . '<' . $this->_ns . ':var copyFrom="' . $matches[2] . '" name="' . $newName . '" modifier="' . $matches[3] . '"';
		} else {
			$replace = $matches[1] . '<' . $this->_ns . ':var name="' . $matches[2] . '" modifier="' . $matches[3] . '"';
		}

		for ($i = 4; $i < count($matches) - 1; $i++ ) {
			$replace .= ' ' . $matches[++$i] . '="' . $matches[++$i] . '"';
		}
		$replace .= '/>';
		return $replace;
	}

   /**
	* replace the variables
	*
	* @access	public
	* @param	string		data
	* @return	string		data with variables replaced
	*/
	function apply($data)
	{
		$startTag = $this->_tmpl->getStartTag();
		$endTag   = $this->_tmpl->getEndTag();

		$this->_ns = $this->_tmpl->getNamespace();
		if (is_array($this->_ns)) {
			$this->_ns = array_shift($this->_ns);
		}
		$regex = chr( 1 ) . "([^\\\])" . $startTag . "([^a-z]+)\|(.+[^\\\])(\|(.+):(.+[^\\\]))*" . $endTag . chr( 1 ) . "U";
		$data = preg_replace_callback($regex, array( $this, '_generateReplace' ), $data);
		return $data;
	}
}
?>