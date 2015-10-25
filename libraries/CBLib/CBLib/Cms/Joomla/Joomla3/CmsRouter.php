<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 10.06.13 15:47 $
* @package CBLib\Cms\Joomla\Joomla3
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Cms\Joomla\Joomla3;

use CBLib\Controller\RouterInterface;
use CBLib\Input\InputInterface;
use CBLib\Registry\GetterInterface;

defined('CBLIB') or die();

/**
 * CBLib\Cms\Joomla\Joomla3\CmsRouter Class implementation
 * 
 */
class CmsRouter implements RouterInterface
{
	/**
	 * Parsed routed
	 *
	 * @var array
	 */
	protected $mainRoutingArgs	=	array();

	/**
	 * This is the Joomla 3.0 (and 2.5) specific implementation of the default router.
	 *
	 * @param   InputInterface  $input  Input
	 * @return  callable|null           array( 'className', 'methodName' )
	 */
	public function parseRoute( InputInterface $input )
	{
		$this->mainRoutingArgs				=	$this->parseRoutingArgs( $input );

		list( $option, $view, $task )		=	array_values( $this->mainRoutingArgs );

		// Backwards compatibility of URLs with task but no view:
		if ( ( $view === null ) && $task ) {
			$view		=	$task;
		}

		// Default to view=default
		if ( $view === null ) {
			$view		=	'default';
		}

		// Remove 'com_' from 'com_component':
		if ( strncmp( $option, 'com_', 4 ) === 0 ) {
			$option		=	substr( $option, 4 );
		}

		$class			=	'\\CBApps\\' . $option . '\\' . ucfirst( $option ) . 'Controller';
		$method			=	$view . 'Task';

		return array( $class, $method );
	}

	/**
	 * Parses the $input for the main routing arguments
	 *
	 * @return  array                   Keyed array with the 3 main routing arguments
	 */
	public function getMainRoutingArgs( )
	{
		return $this->mainRoutingArgs;
	}

	/**
	 * Parses the $input for the main routing arguments
	 *
	 * @param   InputInterface  $input  Input
	 * @return  array                   Keyed array with the 3 main routing arguments
	 */
	protected function parseRoutingArgs( InputInterface $input )
	{
		return $input->get( array( 'option', 'view', 'task' ), null, GetterInterface::COMMAND );
	}
}
