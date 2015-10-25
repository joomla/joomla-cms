<?php
/**
 * CBLib, Community Builder Library(TM)
 *
 * @version       $Id: 5/13/14 5:26 PM $
 * @package       ${NAMESPACE}
 * @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
 * @license       http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */
namespace CBLib\Cms;

use CBLib\Application\ApplicationContainerInterface;
use CBLib\Input\InputInterface;


/**
 * CBLib\Cms Class implementation
 *
 */
interface CmsInterface
{
	/**
	 * @param  string   $info  Informwation to return ('release' php-style version)
	 * @return string
	 */
	public function getCmsVersion( $info = 'release' );

	/**
	 * @param  ApplicationContainerInterface  $di
	 * @param  string                         $type    'Web' or 'Cli'
	 * @param  array|InputInterface           $input
	 * @return InputInterface
	 *
	 * @throws \InvalidArgumentException
	 */
	public function getInput( ApplicationContainerInterface $di, $type, $input );

	/**
	 * Returns client id (0 = front, 1 = admin)
	 *
	 * @return int
	 */
	public function getClientId( );

	/**
	 * Returns the language name
	 *
	 * @return string
	 */
	public function getLanguageName( );

	/**
	 * Returns the language tag
	 *
	 * @return string
	 */
	public function getLanguageTag( );

	/**
	 * Returns extension name being executed (e.g. com_comprofiler or mod_cblogin)
	 *
	 * @return string
	 */
	public function getExtensionName( );

	/**
	 * Get the CBLib's interface class to the CMS User
	 *
	 * @param  int|array|null $userIdOrCriteria  [optional] default: NULL: viewing user, int: User-id (0: guest), array: Criteria, e.g. array( 'username' => 'uniqueUsername' ) or array( 'email' => 'uniqueEmail' )
	 * @return CmsUserInterface
	 */
	public function getCmsUser( $userIdOrCriteria );

	/**
	 * Gets the folder with path for the $clientId (0 = front, 1 = admin)
	 *
	 * @param $clientId
	 * @return string
	 *
	 * @throws \UnexpectedValueException
	 */
	public function getFolderPath( $clientId );

	/**
	 * Registers a handler to filter the final output
	 *
	 * @param  callable  $handler  A function( $body ) { return $bodyChanged; }
	 * @return self                To allow chaining.
	 */
	public function registerOnAfterRenderBodyFilter( $handler );

	/**
	 * Registers a handler to a particular CMS event
	 * @deprecated 2.0 (Marked as deprecated as direct uses should be avoided without a specific method)
	 *
	 * @param  string    $event    The event name:
	 * @param  callable  $handler  The handler, a function or an instance of a event object.
	 * @return self                To allow chaining.
	 */
	public function registerEvent( $event, $handler );

	/**
	 * Prepares the HTML $htmlText with triggering CMS Content Plugins
	 *
	 * @param  string   $htmlText
	 * @return string
	 */
	public function prepareHtmlContentPlugins( $htmlText );

	/**
	 * Get CMS Database object
	 * @return object|\JDatabase|\JDatabaseDriver
	 */
	public function getCmsDatabaseDriver( );
}