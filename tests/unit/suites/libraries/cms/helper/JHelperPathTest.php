<?php
/**
 * @package        Joomla.UnitTest
 * @subpackage     Helper
 * @copyright      Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE
 */

/**
 * Test class for JHelperPath.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Helper
 * @since       3.3
 */
class JHelperPathTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Path provider of component.
	 *
	 * @return array
	 *
	 * @since  3.3
	 */
	public function pathProviderComponent()
	{
		return array(
			// Component in base client
			array('com_content', null, true, JPATH_BASE . '/components/com_content'),

			// Component in site client
			array('com_content', 'site', true, JPATH_SITE . '/components/com_content'),

			// Component in admin client
			array('com_content', 'admin', true, JPATH_ADMINISTRATOR . '/components/com_content'),

			// Component in administrator client
			array('com_content', 'administrator', true, JPATH_ADMINISTRATOR . '/components/com_content'),

			// Component in administrator client with relative path
			array('com_content', 'administrator', false, 'components/com_content'),
		);
	}

	/**
	 * Path provider of module.
	 *
	 * @return array
	 *
	 * @since  3.3
	 */
	public function pathProviderModule()
	{
		return array(
			// Module in base client
			array('mod_latest_news', null, true, JPATH_BASE . '/modules/mod_latest_news'),

			// Module in site client
			array('mod_latest_news', 'site', true, JPATH_SITE . '/modules/mod_latest_news'),

			// Module in admin client
			array('mod_quickicon', 'admin', true, JPATH_ADMINISTRATOR . '/modules/mod_quickicon'),

			// Module in administrator client
			array('mod_quickicon', 'administrator', true, JPATH_ADMINISTRATOR . '/modules/mod_quickicon'),

			// Module in administrator client with relative path
			array('mod_quickicon', 'administrator', false, 'modules/mod_quickicon'),
		);
	}

	/**
	 * Path provider of plugin.
	 *
	 * @return array
	 *
	 * @since  3.3
	 */
	public function pathProviderPlugin()
	{
		return array(
			// Plugin system in base client
			array('plg_system_cache', null, true, JPATH_SITE . '/plugins/system/cache'),

			// Plugin system in site client
			array('plg_system_cache', 'site', true, JPATH_SITE . '/plugins/system/cache'),

			// Plugin system in site client
			array('plg_system_cache', 'admin', true, JPATH_SITE . '/plugins/system/cache'),

			// Plugin system in site client
			array('plg_system_cache', 'administrator', true, JPATH_SITE . '/plugins/system/cache'),

			// Plugin system in site client with relative path
			array('plg_system_cache', 'administrator', false, 'plugins/system/cache'),
		);
	}

	/**
	 * Path provider of library.
	 *
	 * @return array
	 *
	 * @since  3.3
	 */
	public function pathProviderLibrary()
	{
		return array(
			// Library in base client
			array('lib_fof', null, true, JPATH_SITE . '/libraries/fof'),

			// Library in site client
			array('lib_fof', 'site', true, JPATH_SITE . '/libraries/fof'),

			// Library in site client
			array('lib_fof', 'admin', true, JPATH_SITE . '/libraries/fof'),

			// Library in site client
			array('lib_fof', 'administrator', true, JPATH_SITE . '/libraries/fof'),

			// Library in site client with relative path
			array('lib_fof', 'administrator', false, 'libraries/fof'),
		);
	}

	/**
	 * Path provider of template.
	 *
	 * @return array
	 *
	 * @since  3.3
	 */
	public function pathProviderTemplate()
	{
		return array(
			// Template in base client
			array('tpl_protostar', null, true, JPATH_BASE . '/templates/protostar'),

			// Template in site client
			array('tpl_protostar', 'site', true, JPATH_SITE . '/templates/protostar'),

			// Template in admin client
			array('tpl_isis', 'admin', true, JPATH_ADMINISTRATOR . '/templates/isis'),

			// Template in administrator client
			array('tpl_isis', 'administrator', true, JPATH_ADMINISTRATOR . '/templates/isis'),

			// Template in administrator client with relative path
			array('tpl_isis', 'administrator', false, 'templates/isis'),
		);
	}

	/**
	 * Test get path of component
	 *
	 * @param   string  $element   Extension element name.
	 * @param   string  $client    Client, 'site', 'admin' or 'administrator'.
	 * @param   string  $absolute  True to get whole path.
	 * @param   string  $result    The result to compare.
	 *
	 * @dataProvider  pathProviderComponent
	 *
	 * @return  void
	 *
	 * @since  3.3
	 */
	public function testGetComponent($element, $client, $absolute, $result)
	{
		$path = JHelperPath::get($element, $client, $absolute);
		$this->assertEquals($path, $result);
	}

	/**
	 * Test get path of module
	 *
	 * @param   string  $element   Extension element name.
	 * @param   string  $client    Client, 'site', 'admin' or 'administrator'.
	 * @param   string  $absolute  True to get whole path.
	 * @param   string  $result    The result to compare.
	 *
	 * @dataProvider  pathProviderModule
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	public function testGetModule($element, $client, $absolute, $result)
	{
		$path = JHelperPath::get($element, $client, $absolute);
		$this->assertEquals($path, $result);
	}

	/**
	 * Test get path of plugin
	 *
	 * @param   string  $element   Extension element name.
	 * @param   string  $client    Client, 'site', 'admin' or 'administrator'.
	 * @param   string  $absolute  True to get whole path.
	 * @param   string  $result    The result to compare.
	 *
	 * @dataProvider  pathProviderPlugin
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	public function testGetPlugin($element, $client, $absolute, $result)
	{
		$path = JHelperPath::get($element, $client, $absolute);
		$this->assertEquals($path, $result);
	}

	/**
	 * Test get path of library
	 *
	 * @param   string  $element   Extension element name.
	 * @param   string  $client    Client, 'site', 'admin' or 'administrator'.
	 * @param   string  $absolute  True to get whole path.
	 * @param   string  $result    The result to compare.
	 *
	 * @dataProvider  pathProviderLibrary
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	public function testGetLibrary($element, $client, $absolute, $result)
	{
		$path = JHelperPath::get($element, $client, $absolute);
		$this->assertEquals($path, $result);
	}

	/**
	 * Test get path of template
	 *
	 * @param   string  $element   Extension element name.
	 * @param   string  $client    Client, 'site', 'admin' or 'administrator'.
	 * @param   string  $absolute  True to get whole path.
	 * @param   string  $result    The result to compare.
	 *
	 * @dataProvider  pathProviderTemplate
	 *
	 * @return  void
	 *
	 * @since   3.3
	 */
	public function testGetTemplate($element, $client, $absolute, $result)
	{
		$path = JHelperPath::get($element, $client, $absolute);
		$this->assertEquals($path, $result);
	}
}
