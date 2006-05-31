<?php
/**
* @version $Id: template.php 2879 2006-03-23 14:35:57Z Jinx $
* @package JamboWorks
* @copyright Copyright (C) 2006 JamboWorks LLC. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
*/

jimport('joomla.template.template');

/**
 * Template class, provides an easy interface to parse and display a template file
 *
 * @author 		Andrew Eddie
 * @package 	Joomla.Framework
 * @subpackage 	Template
 * @since		1.5
 * @see			patTemplate
 */

class JTemplateHelper
{
	/**
	 * @param array An array of support template files to load
	 */
	function &getInstance($files = array())
	{
		global $mainframe;

		$tmpl = new JTemplate;

		// patTemplate
		if ($mainframe->get( 'caching' ))
		{
	   		 $tmpl->enableTemplateCache( 'File', $mainframe->getCfg('cachepath'));
		}

		$tmpl->setNamespace( 'jtmpl' );

		// load the wrapper and common templates
		$tmpl->readTemplatesFromFile( 'page.html' );
		$tmpl->applyInputFilter('ShortModifiers');

		// load the stock templates
		if (is_array( $files ))
		{
			foreach ($files as $file)
			{
				$tmpl->readTemplatesFromInput( $file );
			}
		}
		if ($mainframe->isAdmin())
		{
			$baseURL = $mainframe->getSiteURL();
		}
		else
		{
			$baseURL = $mainframe->getBaseURL();
		}

		$tmpl->addGlobalVar( 'option', 				$GLOBALS['option'] );
		$tmpl->addGlobalVar( 'self', 				$_SERVER['PHP_SELF'] );
		$tmpl->addGlobalVar( 'uri_query', 			$_SERVER['QUERY_STRING'] );
		$tmpl->addGlobalVar( 'itemid', 				$GLOBALS['Itemid'] );
		$tmpl->addGlobalVar( 'siteurl',				$baseURL );
		$tmpl->addGlobalVar( 'adminurl',			$baseURL . '/administrator' );
		$tmpl->addGlobalVar( 'templateurl', 		$baseURL . '/templates/' . $mainframe->getTemplate() );
		$tmpl->addGlobalVar( 'admintemplateurl', 	$baseURL . '/administrator/templates/' . $mainframe->getTemplate() );
		$tmpl->addGlobalVar( 'sitename', 			$mainframe->getCfg( 'sitename' ) );
		$tmpl->addGlobalVar( 'REQUEST_URL',			JRequest::getUrl() );

		// TODO: Do the protocol better
		$tmpl->addVar( 'form', 'formAction', basename($_SERVER['PHP_SELF']) );
		$tmpl->addVar( 'form', 'formName', 'adminForm' );

		return $tmpl;
	}


	/**
	 * Converts a named array to an array or named rows suitable to option lists
	 * @param array The source array[key] = value
	 * @param mixed A value or array of selected values
	 * @param string The name for the value field
	 * @param string The name for selected attribute (use 'checked' for radio of box lists)
	 */
	function selectArray( &$source, $selected=null, $valueName='value', $selectedAttr='selected' ) {
		if (!is_array( $selected )) {
			$selected = array( $selected );
		}
		foreach ($source as $i => $row) {
			if (is_object( $row )) {
				$source[$i]->selected = in_array( $row->$valueName, $selected ) ? $selectedAttr . '="true"' : '';
			} else {
				$source[$i]['selected'] = in_array( $row[$valueName], $selected ) ? $selectedAttr . '="true"' : '';
			}
		}
	}

	/**
	 * Converts a named array to an array or named rows suitable to checkbox or radio lists
	 * @param array The source array[key] = value
	 * @param mixed A value or array of selected values
	 * @param string The name for the value field
	 */
	function checkArray( &$source, $selected=null, $valueName='value' ) {
		jwTemplateHelper::selectArray( $source, $selected, $valueName, 'checked' );
	}

	/**
	 * @param mixed The value for the option
	 * @param string The text for the option
	 * @param string The name of the value parameter (default is value)
	 * @param string The name of the text parameter (default is text)
	 */
	function makeOption( $value, $text='', $valueName='value', $textName='text' ) {
		return array(
			$valueName => $value,
			$textName => $text ? $text : $value
		);
	}

	/**
	 * @param mixed The value for the option
	 * @param string The text for the option
	 * @param string The name of the value parameter (default is value)
	 * @param string The name of the text parameter (default is text)
	 */
	function makeObjectOption( $value, $text='', $valueName='value', $textName='text' ) {
		$result = new stdClass;
		$result->$valueName = $value;
		$result->$textName = $text ? $text : $value;

		return $result;
	}

	/**
	 * Writes a radio pair
	 * @param object Template object
	 * @param string The template name
	 * @param string The field name
	 * @param int The value of the field
	 * @param array Array of options
	 * @param string Optional template variable name
	 */
	function radioSet( &$tmpl, $template, $name, $value, $a, $varname=null ) {
		jwTemplateHelper::checkArray( $a, $value );

		$tmpl->addVar( 'radio-set', 'name', $name );
		$tmpl->addRows( 'radio-set', $a );
		$tmpl->parseIntoVar( 'radio-set', $template, is_null( $varname ) ? $name : $varname );
	}

	/**
	 * Writes a radio pair
	 * @param object Template object
	 * @param string The template name
	 * @param string The field name
	 * @param int The value of the field
	 * @param string Optional template variable name
	 */
	function yesNoRadio( &$tmpl, $template, $name, $value, $varname=null ) {
		$a = array(
			jwTemplateHelper::makeOption( 0, 'No' ),
			jwTemplateHelper::makeOption( 1, 'Yes' )
		);
		jwTemplateHelper::radioSet( $tmpl, $template, $name, $value, $a, $varname );
	}

	/**
	 * Converts a named array to an array or named rows suitable to option lists
	 * @param array The source array[key] = value
	 * @param mixed A value or array of selected values
	 * @param string The name for the value field
	 * @param string The name for the text field
	 * @param string The name for the selected field
	 */
	function namedArrayToList( $source, $selected=null, $valueName='value', $textName='text', $selName='selected' ) {
		if (!is_array( $selected )) {
			$selected = array( $selected );
		}
		$array = array();
		foreach ($source as $k => $v) {
			$array[] = array(
				'value' => $k,
				'text' => $v,
				'selected' => in_array( $k, $selected ) ? 'selected="true"' : ''
			);
		}
		return $array;
	}

}
?>