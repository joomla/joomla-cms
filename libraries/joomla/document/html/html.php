<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Document
 */

defined('JPATH_PLATFORM') or die;

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
	 */
	public $_links = array();

	/**
	 * Array of custom tags
	 *
	 * @var		array
	 */
	public $_custom = array();

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
	 * Integer with caching setting
	 */
	protected $_caching = null;

	/**
	 * Class constructor
	 *
	 * @param	array	$options Associative array of options
	 */
	public function __construct($options = array())
	{
		parent::__construct($options);

		//set document type
		$this->_type = 'html';

		//set default mime type and document metadata (meta data syncs with mime type by default)
		$this->setMetaData('Content-Type', 'text/html', true);
		$this->setMetaData('robots', 'index, follow');
	}

	/**
	 * Get the html document head data
	 *
	 * @return	array	The document head data in array form
	 */
	public function getHeadData()
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
	 * @param	array	$data	The document head data in array form
	 */
	public function setHeadData($data)
	{
		if (empty($data) || !is_array($data)) {
			return;
		}

		$this->title		= (isset($data['title']) && !empty($data['title'])) ? $data['title'] : $this->title;
		$this->description	= (isset($data['description']) && !empty($data['description'])) ? $data['description'] : $this->description;
		$this->link			= (isset($data['link']) && !empty($data['link'])) ? $data['link'] : $this->link;
		$this->_metaTags	= (isset($data['metaTags']) && !empty($data['metaTags'])) ? $data['metaTags'] : $this->_metaTags;
		$this->_links		= (isset($data['links']) && !empty($data['links'])) ? $data['links'] : $this->_links;
		$this->_styleSheets	= (isset($data['styleSheets']) && !empty($data['styleSheets'])) ? $data['styleSheets'] : $this->_styleSheets;
		$this->_style		= (isset($data['style']) && !empty($data['style'])) ? $data['style'] : $this->_style;
		$this->_scripts		= (isset($data['scripts']) && !empty($data['scripts'])) ? $data['scripts'] : $this->_scripts;
		$this->_script		= (isset($data['script']) && !empty($data['script'])) ? $data['script'] : $this->_script;
		$this->_custom		= (isset($data['custom']) && !empty($data['custom'])) ? $data['custom'] : $this->_custom;
	}

	/**
	 * Merge the html document head data
	 *
	 * @param	array	$data	The document head data in array form
	 */
	public function mergeHeadData($data)
	{

  		if (empty($data) || !is_array($data)) {
			return;
		}

		$this->title		= (isset($data['title']) && !empty($data['title']) && !stristr($this->title, $data['title'])) ? $this->title.$data['title'] : $this->title;
		$this->description	= (isset($data['description']) && !empty($data['description']) && !stristr($this->description, $data['description'])) ? $this->description. $data['description'] : $this->description;
		$this->link			= (isset($data['link'])) ? $data['link'] : $this->link;

		if (isset($data['metaTags'])) {
			foreach($data['metaTags'] AS $type1=>$data1)
			{
				$booldog = $type1 == 'http-equiv' ? true : false;
				foreach($data1 AS $name2=>$data2)
				{
					$this->setMetaData($name2, $data2, $booldog);
				}
			}
		}

		$this->_links		= (isset($data['links']) && !empty($data['links']) && is_array($data['links'])) ? array_unique(array_merge($this->_links, $data['links'])) : $this->_links;
		$this->_styleSheets	= (isset($data['styleSheets']) && !empty($data['styleSheets']) && is_array($data['styleSheets'])) ? array_merge($this->_styleSheets, $data['styleSheets']) : $this->_styleSheets;

		if (isset($data['style'])) {
			foreach($data['style'] AS $type=>$stdata)
			{
				if (!isset($this->_style[strtolower($type)]) || !stristr($this->_style[strtolower($type)],$stdata)) {
					$this->addStyleDeclaration($stdata, $type);
 				}
			}
		}

		$this->_scripts		= (isset($data['scripts']) && !empty($data['scripts']) && is_array($data['scripts'])) ? array_merge($this->_scripts, $data['scripts']) : $this->_scripts;


		if (isset($data['script'])) {
			foreach($data['script'] AS $type=>$sdata)
			{
				if (!isset($this->_script[strtolower($type)]) || !stristr($this->_script[strtolower($type)],$sdata)) {
					$this->addScriptDeclaration($sdata, $type);
				}
			}
		}

		$this->_custom = (isset($data['custom']) && !empty($data['custom'])&& is_array($data['custom'])) ? array_unique(array_merge($this->_custom, $data['custom'])) : $this->_custom;
	}

	/**
	 * Adds <link> tags to the head of the document
	 *
	 * <p>$relType defaults to 'rel' as it is the most common relation type used.
	 * ('rev' refers to reverse relation, 'rel' indicates normal, forward relation.)
	 * Typical tag: <link href="index.php" rel="Start"></p>
	 *
	 * @param	string  $href		The link that is being related.
	 * @param	string  $relation	Relation of link.
	 * @param	string  $relType	Relation type attribute.  Either rel or rev (default: 'rel').
	 * @param	array	$attributes Associative array of remaining attributes.
	 * @return	void
	 */
	public function addHeadLink($href, $relation, $relType = 'rel', $attribs = array())
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
	 */
	public function addFavicon($href, $type = 'image/vnd.microsoft.icon', $relation = 'shortcut icon')
	{
		$href = str_replace('\\', '/', $href);
		$this->_links[] = '<link href="'.$href.'" rel="'.$relation.'" type="'.$type.'"';
	}

	/**
	 * Adds a custom html string to the head block
	 *
	 * @param string The html to add to the head
	 * @return	void
	 */

	public function addCustomTag($html)
	{
		$this->_custom[] = trim($html);
	}

	/**
	 * Get the contents of a document include
	 *
	 * @param string	$type	The type of renderer
	 * @param string	$name	The name of the element to render
	 * @param array		$attribs Associative array of remaining attributes.
	 * @return	The output of the renderer
	 */
	public function getBuffer($type = null, $name = null, $attribs = array())
	{
		// If no type is specified, return the whole buffer
		if ($type === null) {
			return parent::$_buffer;
		}

		$result = null;
		if (isset(parent::$_buffer[$type][$name])) {
			return parent::$_buffer[$type][$name];
		}

		// If the buffer has been explicitly turned off don't display or attempt to render
		if ($result === false) {
			return null;
		}

			$renderer = $this->loadRenderer($type);
			if ($this->_caching == true && $type == 'modules') {
				$cache = JFactory::getCache('com_modules','');
				$hash = md5(serialize(array($name, $attribs, $result, $renderer)));
				$cbuffer = $cache->get('cbuffer_'.$type);

				if (isset($cbuffer[$hash])) {
					return JCache::getWorkarounds($cbuffer[$hash], array('mergehead' => 1));
				} else {

					$options = array();
					$options['nopathway'] = 1;
					$options['nomodules'] = 1;
					$options['modulemode'] = 1;

					$this->setBuffer($renderer->render($name, $attribs, $result), $type, $name);
					$data = parent::$_buffer[$type][$name];

					$tmpdata = JCache::setWorkarounds($data, $options);


					$cbuffer[$hash] = $tmpdata;

					$cache->store($cbuffer, 'cbuffer_'.$type);
				}

			} else {
				$this->setBuffer($renderer->render($name, $attribs, $result), $type, $name);
			}

		return parent::$_buffer[$type][$name];
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

		parent::$_buffer[$options['type']][$options['name']] = $content;
	}

	/**
	 * Parses the template and populates the buffer
	 *
	 * @param array parameters for fetching the template
	 */
	public function parse($params = array()) {
		$this->_fetchTemplate($params);
		$this->_parseTemplate();
	}

	/**
	 * Outputs the template to the browser.
	 *
	 * @param boolean	$cache		If true, cache the output
	 * @param array		$params		Associative array of attributes
	 * @return	The rendered data
	 */
	public function render($caching = false, $params = array())
	{
		$this->_caching = $caching;

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
	 * @param  string	$condition	The condition to use
	 * @return integer  Number of modules found
	 */
	public function countModules($condition)
	{
		$result = '';

		$operators = '(\+|\-|\*|\/|==|\!=|\<\>|\<|\>|\<=|\>=|and|or|xor)';
		$words = preg_split('# '.$operators.' #', $condition, null, PREG_SPLIT_DELIM_CAPTURE);
		for ($i = 0, $n = count($words); $i < $n; $i+=2)
		{
			// odd parts (modules)
			$name		= strtolower($words[$i]);
			$words[$i]	= ((isset(parent::$_buffer['modules'][$name])) && (parent::$_buffer['modules'][$name] === false)) ? 0 : count(JModuleHelper::getModules($name));
		}

		$str = 'return '.implode(' ', $words).';';

		return eval($str);
	}

	/**
	 * Count the number of child menu items
	 *
	 * @return integer Number of child menu items
	 */
	public function countMenuChildren()
	{
		static $children;

		if (!isset($children)) {
			$dbo	= JFactory::getDbo();
			$app	= JFactory::getApplication();
			$menu	= $app->getMenu();
			$where	= Array();
			$active	= $menu->getActive();
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
	private function _loadTemplate($directory, $filename)
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
			require $directory.DS.$filename;
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

		// Load the language file for the template
		$lang = JFactory::getLanguage();
		// 1.5 or core then
		// 1.6
			$lang->load('tpl_'.$template, JPATH_BASE, null, false, false)
		||	$lang->load('tpl_'.$template, $directory.DS.$template, null, false, false)
		||	$lang->load('tpl_'.$template, JPATH_BASE, $lang->getDefault(), false, false)
		||	$lang->load('tpl_'.$template, $directory.DS.$template, $lang->getDefault(), false, false);

		// Assign the variables
		$this->template = $template;
		$this->baseurl  = JURI::base(true);
		$this->params	= isset($params['params']) ? $params['params'] : new JRegistry;

		// load
		$this->_template = $this->_loadTemplate($directory.DS.$template, $file);
	}

	/**
	 * Parse a document template
	 *
	 * @return	The parsed contents of the template
	 */
	private function _parseTemplate()
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
	private function _renderTemplate() {
		$replace = array();
		$with = array();

		foreach($this->_template_tags AS $jdoc => $args) {
			$replace[] = $jdoc;
			$with[] = $this->getBuffer($args['type'], $args['name'], $args['attribs']);
		}

		return str_replace($replace, $with, $this->_template);
	}
}
