<?php
/**
 * @package    tags
 *
 * @author     Kevin <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

defined('_JEXEC') or die;

/**
 * Tags plugin.
 *
 * @package  tags
 * @since    1.0
 */
class plgTagsTags extends JPlugin
{
	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 * @since  1.0
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  1.0
	 */
	protected $db;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * onAfterInitialise.
	 *
	 * @return  void.
	 *
	 * @since   1.0
	 */
	public function onAfterInitialise()
	{
		
	}

	/**
	 * onAfterRoute.
	 *
	 * @return  void.
	 *
	 * @since   1.0
	 */
	public function onAfterRoute()
	{
	
	}

	/**
	 * onAfterDispatch.
	 *
	 * @return  void.
	 *
	 * @since   1.0
	 */
	public function onAfterDispatch()
	{
	
	}

	/**
	 * onAfterRender.
	 *
	 * @return  void.
	 *
	 * @since   1.0
	 */
	public function onAfterRender()
	{
		// Access to plugin parameters
		$sample = $this->params->get('sample', '42');
	}

	/**
	 * onAfterCompileHead.
	 *
	 * @return  void.
	 *
	 * @since   1.0
	 */
	public function onAfterCompileHead()
	{
	
	}

	/**
	 * OnAfterCompress.
	 *
	 * @return  void.
	 *
	 * @since   1.0
	 */
	public function onAfterCompress()
	{
	
	}

	/**
	 * onAfterRespond.
	 *
	 * @return  void.
	 *
	 * @since   1.0
	 */
	public function onAfterRespond()
	{
	
	}

	public function onTagListQuery($query)
    {
        //TODO: Replace with functioning code
        return $query;
    }
}
