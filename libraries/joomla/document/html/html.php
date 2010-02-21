<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Document
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.application.module.helper');

/**
 * DocumentHTML class, provides an easy interface to parse and display an html document
 *
 * @package		Joomla.Framework
 * @subpackage	Document
 * @since		1.5
 */

jimport('joomla.document.document');

class JDocumentHTML extends JDocument
{
	/**
	 * Array of Header <link> tags
	 *
	 * @var		array
	 * @access  private
	 */
	var $_links = array();

	/**
	 * Array of custom tags
	 *
	 * @var		string
	 * @access  private
	 */
	var $_custom = array();

	public $template = null;
	public $baseurl = null;
	public $params = null;
	public $_file = null;

	/**
	 * String holding parsed template
	 */
	protected $_template = '';

	/**
	 * Array of parsed template JDoc tags
	 */
	protected $_template_tags = array();

	/**
	 * Class constructor
	 *
	 * @access	public
	 * @param	array	$options Associative array of options
	 */
	function __construct($options = array())
	{
		parent::__construct($options);

		//set document type
		$this->_type = 'html';

		//set mime type
		$this->_mime = 'text/html';

		//set default document metadata
		$this->setMetaData('Content-Type', $this->_mime . '; charset=' . $this->_charset , true);
		$this->setMetaData('robots', 'index, follow');
	}

	/**
	 * Get the html document head data
	 *
	 * @access	public
	 * @return	array	The document head data in array form
	 */
	function getHeadData()
	{
		$data = array();
		$data['title']		= $this->title;
		$data['description']= $this->description;
		$data['link']		= $this->link;
		$data['metaTags']	= $this->_metaTags;
		$data['links']		= $this->_links;
		$data['styleSheets']= $this->_styleSheets;
		$data['style']		= $this->_style;
		$data['scripts']	= $this->_scripts;
		$data['script']		= $this->_script;
		$data['custom']		= $this->_custom;
		return $data;
	}

	/**
	 * Set the html document head data
	 *
	 * @access	public
	 * @param	array	$data	The document head data in array form
	 */
	function setHeadData($data)
	{
		$this->title		= (isset($data['title'])) ? $data['title'] : $this->title;
		$this->description	= (isset($data['description'])) ? $data['description'] : $this->description;
		$this->link			= (isset($data['link'])) ? $data['link'] : $this->link;
		$this->_metaTags	= (isset($data['metaTags'])) ? $data['metaTags'] : $this->_metaTags;
		$this->_links		= (isset($data['links'])) ? $data['links'] : $this->_links;
		$this->_styleSheets	= (isset($data['styleSheets'])) ? $data['styleSheets'] : $this->_styleSheets;
		$this->_style		= (isset($data['style'])) ? $data['style'] : $this->_style;
		$this->_scripts		= (isset($data['scripts'])) ? $data['scripts'] : $this->_scripts;
		$this->_script		= (isset($data['script'])) ? $data['script'] : $this->_script;
		$this->_custom		= (isset($data['custom'])) ? $data['custom'] : $this->_custom;
	}

	/**
	 * Adds <link> tags to the head of the document
	 *
	 * <p>$relType defaults to 'rel' as it is the most common relation type used.
	 * ('rev' refers to reverse relation, 'rel' indicates normal, forward relation.)
	 * Typical tag: <link href="index.php" rel="Start"></p>
	 *
	 * @access	public
	 * @param	string  $href		The link that is being related.
	 * @param	string  $relation	Relation of link.
	 * @param	string  $relType	Relation type attribute.  Either rel or rev (default: 'rel').
	 * @param	array	$attributes Associative array of remaining attributes.
	 * @return	void
	 */
	function addHeadLink($href, $relation, $relType = 'rel', $attribs = array())
	{
		$attribs = JArrayHelper::toString($attribs);
		$generatedTag = '<link href="'.$href.'" '.$relType.'="'.$relation.'" '.$attribs;
		$this->_links[] = $generatedTag;
	}

	/**
	 * Adds a shortcut icon (favicon)
	 *
	 * <p>This adds a link to the icon shown in the favorites list or on
	 * the left of the url in the address bar. Some browsers display
	 * it on the tab, as well.</p>
	 *
	 * @param	string  $href		The link that is being related.
	 * @param	string  $type		File type
	 * @param	string  $relation	Relation of link
	 * @access	public
	 */
	function addFavicon($href, $type = 'image/x-icon', $relation = 'shortcut icon')
	{
		$href = str_replace('\\', '/', $href);
		$this->_links[] = '<link href="'.$href.'" rel="'.$relation.'" type="'.$type.'"';
	}

	/**
	 * Adds a custom html string to the head block
	 *
	 * @param string The html to add to the head
	 * @access	public
	 * @return	void
	 */

	function addCustomTag($html)
	{
		$this->_custom[] = trim($html);
	}

	/**
	 * Get the contents of a document include
	 *
	 * @access public
	 * @param string	$type	The type of renderer
	 * @param string	$name	The name of the element to render
	 * @param array		$attribs Associative array of remaining attributes.
	 * @return	The output of the renderer
	 */
	function getBuffer($type = null, $name = null, $attribs = array())
	{
		// If no type is specified, return the whole buffer
		if ($type === null) {
			return $this->_buffer;
		}

		$result = null;
		if (isset($this->_buffer[$type][$name])) {
			return $this->_buffer[$type][$name];
		}

		// If the buffer has been explicitly turned off don't display or attempt to render
		if ($result === false) {
			return null;
		}

		$renderer = &$this->loadRenderer($type);
		$this->setBuffer($renderer->render($name, $attribs, $result), $type, $name);

		return $this->_buffer[$type][$name];
	}

	/**
	 * Set the contents a document include
	 *
	 * @param	string	$content	The content to be set in the buffer.
	 * @param	array	$options	Array of optional elements.
	 */
	public function setBuffer($content, $options = array())
	{
		// The following code is just for backward compatibility.
		if (func_num_args() > 1 && !is_array($options)) {
			$args = func_get_args(); $options = array();
			$options['type'] = $args[1];
			$options['name'] = (isset($args[2])) ? $args[2] : null;
		}

		$this->_buffer[$options['type']][$options['name']] = $content;
	}

	/**
	 * Parses the template and populates the buffer
	 *
	 * @access public
	 * @param array parameters for fetching the template
	 */
	function parse($params = array()) {
		$this->_fetchTemplate($params);
		$this->_parseTemplate();
	}

	/**
	 * Outputs the template to the browser.
	 *
	 * @access public
	 * @param boolean	$cache		If true, cache the output
	 * @param array		$params		Associative array of attributes
	 * @return	The rendered data
	 */
	function render($caching = false, $params = array())
	{
		if (!empty($this->_template)) {
			$data = $this->_renderTemplate();
		} else {
			$this->parse($params);
			$data = $this->_renderTemplate();
		}

		parent::render();
		return $data;
	}

	/**
	 * Count the modules based on the given condition
	 *
	 * @access public
	 * @param  string	$condition	The condition to use
	 * @return integer  Number of modules found
	 */
	function countModules($condition)
	{
		$result = '';

		$operators = '(\+|\-|\*|\/|==|\!=|\<\>|\<|\>|\<\=|\>\=|and|or|xor) ';
		$words = preg_split('# '.$operators.' #', $condition, null, PREG_SPLIT_DELIM_CAPTURE);
		for ($i = 0, $n = count($words); $i < $n; $i+=2)
		{
			// odd parts (modules)
			$name		= strtolower($words[$i]);
			$words[$i]	= ((isset($this->_buffer['modules'][$name])) && ($this->_buffer['modules'][$name] === false)) ? 0 : count(JModuleHelper::getModules($name));
		}

		$str = 'return '.implode(' ', $words).';';

		return eval($str);
	}

	/**
	 * Count the number of child menu items
	 *
	 * @access public
	 * @return integer Number of child menu items
	 */
	function countMenuChildren() {
		static $children;
		if (!isset($children)) {
			$dbo = &JFactory::getDbo();
			$menu = &JSite::getMenu();
			$where = Array();
			$active = $menu->getActive();
			if ($active) {
				$where[] = 'parent = ' . $active->id;
				$where[] = 'published = 1';
				$dbo->setQuery('SELECT COUNT(*) FROM #__menu WHERE '. implode(' AND ', $where));
				$children = $dbo->loadResult();
			} else {
				$children = 0;
			}
		}

		return $children;
	}

	/**
	 * Load a template file
	 *
	 * @param string	$template	The name of the template
	 * @param string	$filename	The actual filename
	 * @return string The contents of the template
	 */
	function _loadTemplate($directory, $filename)
	{
//		$component	= JApplicationHelper::getComponentName();

		$contents = '';

		//Check to see if we have a valid template file
		if (file_exists($directory.DS.$filename))
		{
			//store the file path
			$this->_file = $directory.DS.$filename;

			//get the file content
			ob_start();
			require_once $directory.DS.$filename;
			$contents = ob_get_contents();
			ob_end_clean();
		}

		// Try to find a favicon by checking the template and root folder
		$path = $directory . DS;
		$dirs = array($path, JPATH_BASE.DS);
		foreach ($dirs as $dir)
		{
			$icon = $dir.'favicon.ico';
			if (file_exists($icon))
			{
				$path = str_replace(JPATH_BASE . DS, '', $dir);
				$path = str_replace('\\', '/', $path);
				$this->addFavicon(JURI::base(true).'/'.$path.'favicon.ico');
				break;
			}
		}

		return $contents;
	}

	/**
	 * Fetch the template, and initialise the params
	 *
	 * @param array parameters to determine the template
	 */
	protected function _fetchTemplate($params = array())
	{
		// check
		$directory	= isset($params['directory']) ? $params['directory'] : 'templates';
		$filter		= JFilterInput::getInstance();
		$template	= $filter->clean($params['template'], 'cmd');
		$file		= $filter->clean($params['file'], 'cmd');

		if (!file_exists($directory.DS.$template.DS.$file)) {
			$template = 'system';
		}

		// Assign the variables
		$this->template = $template;
		$this->baseurl  = JURI::base(true);
		$this->params	= isset($params['params']) ? $params['params'] : new JParameter;

		// load
		$this->_template = $this->_loadTemplate($directory.DS.$template, $file);
	}

	/**
	 * Parse a document template
	 *
	 * @access	public
	 * @return	The parsed contents of the template
	 */
	function _parseTemplate()
	{
		$replace = array();
		$matches = array();
		if (preg_match_all('#<jdoc:include\ type="([^"]+)" (.*)\/>#iU', $this->_template, $matches))
		{
			$matches[0] = array_reverse($matches[0]);
			$matches[1] = array_reverse($matches[1]);
			$matches[2] = array_reverse($matches[2]);

			$count = count($matches[1]);

			for ($i = 0; $i < $count; $i++)
			{
				$attribs = JUtility::parseAttributes($matches[2][$i]);
				$type  = $matches[1][$i];

				$name  = isset($attribs['name']) ? $attribs['name'] : null;
				$this->_template_tags[$matches[0][$i]] = array('type'=>$type, 'name' => $name, 'attribs' => $attribs);
			}
		}
	}

	/**
	 * Render pre-parsed template
	 *
	 * @return string rendered template
	 */
	protected function _renderTemplate() {
		$replace = array();
		$with = array();
		foreach($this->_template_tags AS $jdoc => $args) {
			$replace[] = $jdoc;
			$with[] = $this->getBuffer($args['type'], $args['name'], $args['attribs']);
		}
		return str_replace($replace, $with, $this->_template);
	}
}
