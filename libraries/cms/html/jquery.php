<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for jQuery JavaScript behaviors
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       3.0
 */
abstract class JHtmlJquery
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  3.0
	 */
	protected static $loaded = array();

	/**
	 * Method to load the jQuery JavaScript framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of jQuery is included for easier debugging.
	 *
	 * @param   boolean  $noConflict  True to load jQuery in noConflict mode [optional]
	 * @param   mixed    $debug       Is debugging mode on? [optional]
	 * @param   boolean  $migrate     True to enable the jQuery Migrate plugin
	 * @param   mixed   $useCdn   Is content delivery networks utilized [optional]
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function framework($noConflict = true, $debug = null, $migrate = true,$useCdn = null)
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__]))
		{
			return;
		}

		// If no debugging value is set, use the configuration setting
		if ($debug === null)
		{
			$config = JFactory::getConfig();
			$debug  = (boolean) $config->get('debug');
		
                        
                        //check if CDN us null now to avoid a second call to the configuration later if CDN is false
                        if($useCdn === null)
                        {
                            $useCdn = (boolean) $config->get('useCdn');
                        }
                }
//now check CDN again in case debug was not null and also if it was true - we need the CDN parameters now to process.
                if($useCdn === null || $useCdn === true)
                {
                    $config = JFactory::getConfig();
                    if($useCdn === null)
                        {
                            $useCdn = (boolean) $config->get('useCdn');
                        }
                    //here we have confirmed useCDN is true so we pull the CDN params if they are empty declare them as null
                    if($useCdn)
                    {
                        $cdnjQUri = (strlen($config->get('cdnjQUri'))>0 ? $config->get('cdnjQUri'): null);
                        $cdnjQMigrateUri = (strlen($config->get('cdnjQMigrateUri'))>0 ? $config->get('cdnjQMigrateUri'): null);
                        if($debug)
                        {
                        $cdnjQUri = (strlen($config->get('cdnjQUriD'))>0 ? $config->get('cdnjQUriD'):$cdnjQUri);
                        $cdnjQMigrateUri = (strlen($config->get('cdnjQMigrateUriD'))>0 ? $config->get('cdnjQMigrateUriD') : $cdnjQMigrateUri);
                        }
                                
                    }
                }
                if ($useCdn && $cdnjQUri != null)
                {
                  JFactory::getDocument()->addScript($cdnjQUri);                   
                }
                else
                {
                    JHtml::_('script', 'jui/jquery.min.js', false, true, false, false, $debug);
                }
		
		// Check if we are loading in noConflict not worth using a CDN
		if ($noConflict)
		{
			JHtml::_('script', 'jui/jquery-noconflict.js', false, true, false, false, false);
		}

		// Check if we are loading Migrate
		if ($migrate)
		{
                    if($useCdn && $cdnjQMigrateUri != null )
                    {
                        JFactory::getDocument()->addScript($cdnjQMigrateUri);
                    }
                    else
                    {
			JHtml::_('script', 'jui/jquery-migrate.min.js', false, true, false, false, $debug);
                    }
		}

		static::$loaded[__METHOD__] = true;

		return;
	}

	/**
	 * Method to load the jQuery UI JavaScript framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of jQuery UI is included for easier debugging.
	 *
	 * @param   array  $components  The jQuery UI components to load [optional]
	 * @param   mixed  $debug       Is debugging mode on? [optional]
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function ui(array $components = array('core'), $debug = null)
	{
		// Set an array containing the supported jQuery UI components handled by this method
		$supported = array('core', 'sortable');

		// Include jQuery
		static::framework();

		// If no debugging value is set, use the configuration setting
		if ($debug === null)
		{
			$config = JFactory::getConfig();
			$debug  = (boolean) $config->get('debug');
		}

		// Load each of the requested components
		foreach ($components as $component)
		{
			// Only attempt to load the component if it's supported in core and hasn't already been loaded
			if (in_array($component, $supported) && empty(static::$loaded[__METHOD__][$component]))
			{
				JHtml::_('script', 'jui/jquery.ui.' . $component . '.min.js', false, true, false, false, $debug);
				static::$loaded[__METHOD__][$component] = true;
			}
		}

		return;
	}
}
