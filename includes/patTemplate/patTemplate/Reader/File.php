<?PHP
/**
 * patTemplate Reader that reads from a file
 *
 * $Id$
 *
 * @package		patTemplate
 * @subpackage	Readers
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * patTemplate Reader that reads from a file
 *
 * $Id$
 *
 * @package		patTemplate
 * @subpackage	Readers
 * @author		Stephan Schmidt <schst@php.net>
 */
class patTemplate_Reader_File extends patTemplate_Reader
{
   /**
	* reader name
	* @access	private
	* @var		string
	*/
	var	$_name	=	'File';

   /**
	* flag to indicate, that current file is remote
	*
	* @access	private
	* @var		boolean
	*/
	var $_isRemote = false;

   /**
	* all files, that have been opened
	*
	* @access	private
	* @var		array
	*/
	var $_files = array();

   /**
	* read templates from any input
	*
	* @final
	* @access	public
	* @param	string	file to parse
	* @return	array	templates
	*/
	function readTemplates( $input )
	{
		if (isset($this->_rootAtts['relative'])) {
			$relative = $this->_rootAtts['relative'];
		} else {
			$relative = false;
		}
		if ($relative === false) {
	   		$this->_currentInput = $input;
		} else {
			$this->_currentInput = dirname($relative) . DIRECTORY_SEPARATOR . $input;
		}

		$fullPath = $this->_resolveFullPath($input, $relative);
		if (patErrorManager::isError($fullPath)) {
			return $fullPath;
		}
		$content = $this->_getFileContents($fullPath);
		if (patErrorManager::isError($content)) {
			return $content;
		}

		$templates = $this->parseString($content);

		return	$templates;
	}

   /**
	* load template from any input
	*
	* If the a template is loaded, the content will not get
	* analyzed but the whole content is returned as a string.
	*
	* @abstract	must be implemented in the template readers
	* @param	mixed	input to load from.
	*					This can be a string, a filename, a resource or whatever the derived class needs to read from
	* @return	string  template content
	*/
	function loadTemplate( $input )
	{
		if (isset($this->_rootAtts['relative'])) {
			$relative = $this->_rootAtts['relative'];
		} else {
			$relative = false;
		}
		$fullPath	=	$this->_resolveFullPath( $input, $relative );
		if( patErrorManager::isError( $fullPath ) )
			return $fullPath;
		return $this->_getFileContents( $fullPath );
	}

   /**
	* resolve path for a template
	*
	* @access	private
	* @param	string			filename
	* @param	boolean|string  filename for relative path calculation
	* @return	string			full path
	*/
	function _resolveFullPath( $filename, $relativeTo = false )
	{
		if (preg_match( '/^[a-z]+:\/\//', $filename )) {
			$this->_isRemote = true;
			return $filename;
		} else {
			$rootFolders = $this->getTemplateRoot();
			if (!is_array($rootFolders)) {
				$rootFolders = array($rootFolders);
			}
			foreach ($rootFolders as $root) {
				if ($relativeTo === false) {
					$baseDir = $root;
				} else {
					$baseDir = $root . DIRECTORY_SEPARATOR . dirname($relativeTo);
				}
				$fullPath = $baseDir . DIRECTORY_SEPARATOR . $filename;
				if (file_exists($fullPath)) {
					return $fullPath;
				}
			}
		}
		return patErrorManager::raiseError(
									PATTEMPLATE_READER_ERROR_NO_INPUT,
									"Could not load templates from $filename."
									);
	}

   /**
	* get the contents of a file
	*
	* @access	private
	* @param	string		filename
	* @return	string		file contents
	*/
	function _getFileContents( $file )
	{
		if (!$this->_isRemote && (!file_exists($file) || !is_readable($file))) {
			return patErrorManager::raiseError(
										PATTEMPLATE_READER_ERROR_NO_INPUT,
										"Could not load templates from $file."
										);
		}

		if (function_exists('file_get_contents')) {
			$content = @file_get_contents( $file );
		} else {
			$content = implode('', file($file));
		}

		/**
		 * store the file name
		 */
		array_push($this->_files, $file);

		return $content;
	}
}
?>