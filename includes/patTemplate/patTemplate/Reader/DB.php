<?PHP
/**
 * patTemplate Reader that reads from a database using PEAR::DB
 *
 * $Id$
 *
 * @package		patTemplate
 * @subpackage	Readers
 * @author		Stephan Schmidt <schst@php.net>
 */

/**
 * PEAR::DB is not installed
 */
define('PATTEMPLATE_READER_DB_ERROR_CLASS_NOT_FOUND', 'patTemplate::Reader::DB::001');

/**
 * Connection could not be established
 */
define('PATTEMPLATE_READER_DB_ERROR_NO_CONNECTION', 'patTemplate::Reader::DB::002');

/**
 * Could not find input
 */
define('PATTEMPLATE_READER_DB_ERROR_NO_INPUT', 'patTemplate::Reader::DB::003');

/**
 * Unknown input syntax
 */
define('PATTEMPLATE_READER_DB_ERROR_UNKNOWN_INPUT', 'patTemplate::Reader::DB::004');

/**
 * patTemplate Reader that reads from a database using PEAR::DB
 *
 * $Id$
 *
 * @package		patTemplate
 * @subpackage	Readers
 * @author		Stephan Schmidt <schst@php.net>
 */
class patTemplate_Reader_DB extends patTemplate_Reader
{
   /**
	* reader name
	* @access	private
	* @var		string
	*/
	var	$_name = 'DB';

   /**
	* read templates from the database
	*
	* Input may either be an SQL query or a string defining the location
	* of the template using the format:
	* <code>
	* table[@key=value]/@templateField
	* </code>
	*
	* @final
	* @access	public
	* @param	string	file to parse
	* @return	array	templates
	*/
	function readTemplates($input)
	{
		$content = $this->getDataFromDb($input);
		if (patErrorManager::isError($content)) {
			return $content;
		}
		$templates = $this->parseString($content);
		return $templates;
	}

   /**
	* fetch the template data from the database
	*
	* @access   protected
	* @param    string      input to read from
	*/
	function getDataFromDb($input)
	{
		// check for PEAR DB
		if (!class_exists('DB')) {
			@include_once 'DB.php';
			if (!class_exists('DB')) {
				return patErrorManager::raiseError(PATTEMPLATE_READER_DB_ERROR_CLASS_NOT_FOUND, 'This reader requires PEAR::DB which could not be found on your system.');
			}
		}

		// establish connection
		$db = &DB::connect($this->getTemplateRoot());
		if (PEAR::isError($db)) {
			return patErrorManager::raiseError(PATTEMPLATE_READER_DB_ERROR_NO_CONNECTION, 'Could not establish database connection: ' . $db->getMessage());
		}

		$input = $this->parseInputStringToQuery($input, $db);
		if (patErrorManager::isError($input)) {
			return $input;
		}

		$content = $db->getOne($input);
		if (PEAR::isError($content)) {
			return patErrorManager::raiseError(PATTEMPLATE_READER_DB_ERROR_NO_INPUT, 'Could not fetch template: ' . $content->getMessage());
		}
		return $content;
	}

   /**
	* Parse the template location syntax to a query
	*
	* @access  private
	* @param   string
	* @param   DB_common
	*/
	function parseInputStringToQuery($input, $db)
	{
		// Input is no query
		if (strstr($input, 'SELECT') !== false) {
			return $input;
		}

		$matches = array();
		if (!preg_match('/^([a-z]+)\[([^]]+)\]\/@([a-z]+)$/i', $input, $matches)) {
			return patErrorManager::raiseError(PATTEMPLATE_READER_DB_ERROR_UNKNOWN_INPUT, 'Could not parse input string.');
		}

		$table		 = $matches[1];
		$templateField = $matches[3];
		$where		 = array();
		$tmp = explode(',', $matches[2]);
		foreach ($tmp as $clause) {
			list($field, $value) = explode('=', trim($clause));
			if ($field{0} !== '@') {
				return patErrorManager::raiseError(PATTEMPLATE_READER_DB_ERROR_UNKNOWN_INPUT, 'Could not parse input string.');
			}
			$field = substr($field, 1);
			array_push($where, $field . '=' . $db->quoteSmart($value));
		}

		$query = sprintf('SELECT %s FROM %s WHERE %s', $templateField, $table, implode(' AND ', $where));
		return $query;
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
	function loadTemplate($input)
	{
		$content = $this->getDataFromDb($input);
		return $content;
	}
}
?>