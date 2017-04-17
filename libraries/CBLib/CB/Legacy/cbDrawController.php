<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/20/14 1:46 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\AhaWow\Controller\DrawController;
use CBLib\Xml\SimpleXMLElement;

defined('CBLIB') or die();

/**
 * cbDrawController Class implementation
 * Base class for a drawing controller
 *
 * @deprecated 2.0 Use CBLib\AhaWow\Controller\DrawController (BUT actually, you should not use that class directly)
 * @see \CBLib\AhaWow\Controller\DrawController
 */
class cbDrawController extends DrawController
{
	/**
	 * Constructor
	 *
	 * @param  SimpleXMLElement  $tableBrowserModel  The model for the browser
	 * @param  SimpleXMLElement  $actions            The actions node
	 * @param  string[]          $options            The input request options
	 */
	public function __construct( $tableBrowserModel, $actions, $options )
	{
		parent::__construct( Application::Input(), $tableBrowserModel, $actions, $options );
	}
}
