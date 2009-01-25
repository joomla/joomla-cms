<?php
/**
 * Document Description
 *
 * Document Long Description
 *
 * PHP4/5
 *
 * Created on Oct 30, 2008
 *
 * @package package_name
 * @author Your Name <author@toowoombarc.qld.gov.au>
 * @author Toowoomba Regional Council Information Management Branch
 * @license GNU/GPL http://www.gnu.org/licenses/gpl.html
 * @copyright 2008 Toowoomba Regional Council/Developer Name
 * @version SVN: $Id$
 * @see http://joomlacode.org/gf/project/   JoomlaCode Project:
 */

// No direct access
defined('JPATH_BASE') or die();

abstract class JDataLoad extends JClass {

	abstract public function load();

	public static function &getInstance($options = array())
	{
		static $instances;

		if (!isset( $instances )) {
			$instances = array();
		}

		$signature = serialize( $options );

		if (empty($instances[$signature]))
		{
			$driver		= array_key_exists('driver', $options) 		? $options['driver']	: 'sql';
			$filename	= array_key_exists('filename', $options)	? $options['filename']	: null;

			$driver = preg_replace('/[^A-Z0-9_\.-]/i', '', $driver);
			$path	= dirname(__FILE__).DS.'loader'.DS.$driver.'.php';

			if (file_exists($path)) {
				require_once $path;
			} else {
				JError::setErrorHandling(E_ERROR, 'die'); //force error type to die
				$error = JError::raiseError( 500, JTEXT::_('Unable to load Data Load Driver:') .$driver);
				return $error;
			}
			$adapter	= 'JLoader'.$driver;
			$instance	= new $adapter($options);

			if ( $error = $instance->getError() )
			{
				JError::setErrorHandling(E_ERROR, 'ignore'); //force error type to die
				$error = JError::raiseError( 500, JTEXT::_('Unable to instantiate data load driver:') .$error);
				return $error;
			}


			$instances[$signature] = & $instance;
		}

		return $instances[$signature];
	}
}

