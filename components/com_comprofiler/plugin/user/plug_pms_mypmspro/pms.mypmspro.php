<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Language\CBTxt;
use CBLib\Registry\ParamsInterface;
use CBLib\Registry\Registry;
use CBLib\Registry\GetterInterface;
use CBLib\Database\Table\Table;
use CB\Database\Table\UserTable;
use CB\Database\Table\TabTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_PLUGINS;
$_PLUGINS->registerFunction( 'onAfterDeleteUser', 'userDeleted', 'getmypmsproTab' );

class cbmypmsproTable extends Table
{
	var $id						=	null;
	var $replyid				=	null;
	var $fromid					=	null;
	var $toid					=	null;
	var $message				=	null;
	var $datum					=	null;
	var $toread					=	null;
	var $totrash				=	null;
	var $totrashdate			=	null;
	var $totrashoutbox			=	null;
	var $totrashdateoutbox		=	null;
	var $expires				=	null;
	var $disablereply			=	null;
	var $systemflag				=	null;
	var $delayed				=	null;
	var $systemmessage			=	null;
	var $archived				=	null;
	var $cryptmode				=	null;
	var $flagged				=	null;
	var $crypthash				=	null;
	var $publicname				=	null;
	var $publicemail			=	null;

	/**
	 * Table name in database
	 * @var string
	 */
	protected $_tbl				=	'#__uddeim';

	/**
	 * Primary key(s) of table
	 * @var string
	 */
	protected $_tbl_key			=	'id';
}

class CBplug_pmsmypmspro extends cbPluginHandler
{

	/**
	 * @param null      $tab
	 * @param UserTable $user
	 * @param int       $ui
	 * @param array     $postdata
	 */
	public function getCBpluginComponent( /** @noinspection PhpUnusedParameterInspection */ $tab, /** @noinspection PhpUnusedParameterInspection */ $user, /** @noinspection PhpUnusedParameterInspection */ $ui, /** @noinspection PhpUnusedParameterInspection */ $postdata )
	{
		global $_CB_framework, $_PLUGINS, $_CB_PMS;

		cbSpoofCheck( 'plugin' );

		$id				=	$this->input( 'id', null, GetterInterface::INT );
		$user			=	CBuser::getMyUserDataInstance();

		if ( ! $id ) {
			cbRedirect( $_CB_framework->userProfileUrl( $user->get( 'id' ), false, 'getmypmsproTab' ), CBTxt::T( 'SEND_PMS_MISSING_TO_USER', 'Private message failed to send! Error: Missing to user' ), 'error' );
		}

		$profileUrl		=	$_CB_framework->userProfileUrl( $id, false, 'getmypmsproTab' );

		if ( ! $user->get( 'id' ) ) {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}

		if ( $id == $user->get( 'id' ) ) {
			cbRedirect( $profileUrl, CBTxt::T( 'SEND_PMS_ERROR_SELF', 'Private message failed to send! Error: You can not send a private message to your self' ), 'error' );
		}

		$tab			=	new TabTable();

		$tab->load( array( 'pluginclass' => 'getmypmsproTab' ) );

		if ( ! ( $tab->enabled && Application::MyUser()->canViewAccessLevel( $tab->viewaccesslevel ) ) ) {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}

		$subject		=	$this->input( 'subject', null, GetterInterface::STRING );
		$message		=	$this->input( 'message', null, GetterInterface::STRING );
		$send			=	$_CB_PMS->sendPMSMSG( $id, $user->get( 'id' ), $subject, $message, false );

		if ( is_array( $send ) && ( count( $send ) > 0 ) ) {
			$result		=	$send[0];
		} else {
			$result		=	false;
		}

		if ( $result ) {
			cbRedirect( $profileUrl, CBTxt::T( 'SEND_PMS_SUCCESS', 'Private message sent successfully!' ) );
		} else{
			cbRedirect( $profileUrl, $_PLUGINS->getErrorMSG(), 'error' );
		}
	}
}

class getmypmsproTab extends cbPMSHandler
{
	/** @var uddeimconfigclass */
	private $uddeIMConfigRAW;
	/** @var Registry */
	private $uddeIMConfig;

	/**
	* Constructor
	*/
	public function __construct()
	{
		$this->uddeIMConfig		=	new Registry();

		parent::__construct();
	}

	/**
	 * Checks if UddeIM is installed; if it is then load its API
	 *
	 * @return bool
	 */
	private function isInstalled()
	{
		global $_CB_framework, $_PLUGINS;

		$absPath						=	$_CB_framework->getCfg( 'absolute_path' );

		if ( ! file_exists( $absPath . '/components/com_uddeim/uddeim.php' ) ) {
			$_PLUGINS->_setErrorMSG( CBTxt::T( 'UDDEIM_NOT_INSTALLED', 'The UddeIM private message system is not installed.' ) );

			return false;
		}

		static $loaded					=	0;

		if ( ! $loaded++ ) {
			$this->loadLib();

			/** @noinspection PhpIncludeInspection */
			require_once( $absPath . '/administrator/components/com_uddeim/admin.shared.php' );
			/** @noinspection PhpIncludeInspection */
			require_once( $absPath . '/components/com_uddeim/bbparser.php' );
			/** @noinspection PhpIncludeInspection */
			require_once( $absPath . '/components/com_uddeim/includes.php' );
			/** @noinspection PhpIncludeInspection */
			require_once( $absPath . '/components/com_uddeim/includes.db.php' );
			/** @noinspection PhpIncludeInspection */
			require_once( $absPath . '/components/com_uddeim/crypt.class.php' );
			/** @noinspection PhpIncludeInspection */
			require_once( $absPath . '/administrator/components/com_uddeim/config.class.php' );

			$this->uddeIMConfigRAW		=	new uddeimconfigclass();

			uddeIMloadLanguage( $absPath . '/administrator/components/com_uddeim', $this->uddeIMConfigRAW );

			$this->uddeIMConfig->load( $this->uddeIMConfigRAW );
		}

		return true;
	}

	/**
	 * Loads the appropriate UddeIM API lib
	 *
	 * @param string $jVersion
	 */
	private function loadLib( $jVersion = 'auto' )
	{
		global $_CB_framework;

		$absPath	=	$_CB_framework->getCfg( 'absolute_path' );

		if ( file_exists( $absPath . '/components/com_uddeim/uddeimlib.php' ) ) {
			/** @noinspection PhpIncludeInspection */
			require_once( $absPath . '/components/com_uddeim/uddeimlib.php' );
		} elseif ( ( checkJversion( '3.3+' ) && ( $jVersion == 'auto' ) ) || ( $jVersion == '3.3' ) ) {
			if ( file_exists( $absPath . '/components/com_uddeim/uddeimlib33.php' ) ) {
				/** @noinspection PhpIncludeInspection */
				require_once( $absPath . '/components/com_uddeim/uddeimlib33.php' );
			} else {
				$this->loadLib( '3.2' );
			}
		} elseif ( ( checkJversion( '3.2+' ) && ( $jVersion == 'auto' ) ) || ( $jVersion == '3.2' ) ) {
			if ( file_exists( $absPath . '/components/com_uddeim/uddeimlib32.php' ) ) {
				/** @noinspection PhpIncludeInspection */
				require_once( $absPath . '/components/com_uddeim/uddeimlib32.php' );
			} else {
				$this->loadLib( '3.1' );
			}
		} elseif ( ( checkJversion( '3.1+' ) && ( $jVersion == 'auto' ) ) || ( $jVersion == '3.1' ) ) {
			if ( file_exists( $absPath . '/components/com_uddeim/uddeimlib31.php' ) ) {
				/** @noinspection PhpIncludeInspection */
				require_once( $absPath . '/components/com_uddeim/uddeimlib31.php' );
			} else {
				$this->loadLib( '3.0' );
			}
		} elseif ( ( checkJversion( '3.0+' ) && ( $jVersion == 'auto' ) ) || ( $jVersion == '3.0' ) ) {
			if ( file_exists( $absPath . '/components/com_uddeim/uddeimlib30.php' ) ) {
				/** @noinspection PhpIncludeInspection */
				require_once( $absPath . '/components/com_uddeim/uddeimlib30.php' );
			} else {
				$this->loadLib( '2.5' );
			}
		} elseif ( ( checkJversion( '2.5+' ) && ( $jVersion == 'auto' ) ) || ( $jVersion == '2.5' ) ) {
			if ( file_exists( $absPath . '/components/com_uddeim/uddeimlib25.php' ) ) {
				/** @noinspection PhpIncludeInspection */
				require_once( $absPath . '/components/com_uddeim/uddeimlib25.php' );
			}
		}
	}

	/**
	 * Sends a PMS message
	 *
	 *  @param  int      $toUserId         UserId of receiver
	 *  @param  int      $fromUserId       UserId of sender
	 *  @param  string   $subject          Subject of PMS message
	 *  @param  string   $message          Body of PMS message
	 *  @param  boolean  $systemGenerated  False: real user-to-user message; True: system-Generated by an action from user $fromid (if non-null)
	 *  @return boolean                    True: PM sent successfully; False: PM failed to send
	 */
	public function sendUserPMS( $toUserId, $fromUserId, $subject, $message, $systemGenerated = false )
	{
		global $_PLUGINS;

		if ( ! $this->isInstalled() ) {
			return false;
		}

		$toUserId				=	(int) $toUserId;
		$fromUserId				=	(int) $fromUserId;

		if ( ! $toUserId ) {
			$_PLUGINS->_setErrorMSG( CBTxt::T( 'SEND_PMS_MISSING_TO_USER', 'Private message failed to send! Error: Missing to user' ) );

			return false;
		}

		if ( $subject ) {
			$message			=	"[b]" . $subject . "[/b]\n\n" . $message;
		}

		$message				=	$this->htmlToBBCode( $message );

		if ( ! $message ) {
			$_PLUGINS->_setErrorMSG( CBTxt::T( 'SEND_PMS_MISSING_MESSAGE', 'Private message failed to send! Error: Missing message' ) );

			return false;
		}

		$cryptMode				=	$this->uddeIMConfig->get( 'cryptmode', 0, GetterInterface::INT );
		$cryptKey				=	$this->uddeIMConfig->get( 'cryptkey', 'uddeIMcryptkey', GetterInterface::STRING );

		$pm						=	new cbmypmsproTable();

		if ( $systemGenerated || ( ! $fromUserId ) ) {
			$fromSystem			=	$this->uddeIMConfig->get( 'sysm_username', 'System', GetterInterface::STRING );

			if ( $fromUserId ) {
				$fromSystem		=	uddeIMgetNameFromID( $fromUserId, $this->uddeIMConfigRAW );
			}

			$pm->set( 'disablereply', 1 );
			$pm->set( 'systemflag', 1 );
			$pm->set( 'systemmessage', $fromSystem );
		}

		$pm->set( 'fromid', (int) $fromUserId );
		$pm->set( 'toid', (int) $toUserId );
		$pm->set( 'datum', uddetime( $this->uddeIMConfig->get( 'timezone', 0, GetterInterface::INT ) ) );

		if ( in_array( $cryptMode, array( 1, 2, 4 ) ) ) {
			$pm->set( 'message', uddeIMencrypt( $message, $cryptKey, CRYPT_MODE_BASE64 ) );
			$pm->set( 'cryptmode', 1 );
			$pm->set( 'crypthash', md5( $cryptKey ) );
		} elseif ( $cryptMode == 3 ) {
			$pm->set( 'message', uddeIMencrypt( $message, '', CRYPT_MODE_STOREBASE64 ) );
			$pm->set( 'cryptmode', 1 );
			$pm->set( 'crypthash', md5( $cryptKey ) );
		} else {
			$pm->set( 'message', $message );
		}

		if ( uddeIMgetEMNmoderated( $pm->get( 'fromid' ) ) ) {
			$pm->set( 'delayed', 1 );
		}

		if ( ! $pm->store() ) {
			$_PLUGINS->_setErrorMSG( CBTxt::T( 'SEND_PMS_FAILED_ERROR', 'Private message failed to send! Error: [error]', array( '[error]' => $pm->getError() ) ) );

			return false;
		}

		$this->sendNotification( $pm, $message );

		return true;
	}

	/**
	 * Sends a PM notification
	 *
	 * @param cbmypmsproTable $pm
	 * @param null|string     $message
	 */
	private function sendNotification( $pm, $message = null )
	{
		if ( ! $pm->get( 'id' ) ) {
			return;
		}

		$itemId				=	uddeIMgetItemid( $this->uddeIMConfigRAW );

		if ( ! uddeIMexistsEMN( $pm->get( 'toid' ) ) ) {
			uddeIMinsertEMNdefaults( $pm->get( 'toid' ), $this->uddeIMConfigRAW );
		}

		$emailNotify		=	$this->uddeIMConfig->get( 'allowemailnotify', 0 );
		$isModerated		=	uddeIMgetEMNmoderated( $pm->get( 'fromid' ) );
		$isReply			=	stristr( $pm->get( 'message' ), $this->uddeIMConfig->get( 'quotedivider' ), '__________' );
		$isOnline			=	uddeIMisOnline( $pm->get( 'toid' ) );

		// Strip the html and bbcode as uddeim supports neither in its notification:
		$message			=	strip_tags( uddeIMbbcode_strip( ( $message ? $message : $pm->get( 'message' ) ), $this->uddeIMConfigRAW ) );

		if ( ! $isModerated ) {
			if ( ( $emailNotify == 1 ) || ( ( $emailNotify == 2 ) && Application::User( $pm->get( 'toid' ) )->isSuperAdmin() ) ) {
				$status		=	uddeIMgetEMNstatus( $pm->get( 'toid' ) );

				if ( ( $status == 1 ) || ( ( $status == 2 ) && ( ! $isOnline ) ) || ( ( $status == 10 ) && ( ! $isReply ) ) || ( ( $status == 20 ) && ( ! $isOnline ) && ( ! $isReply ) ) )  {
					uddeIMdispatchEMN( $pm->get( 'id' ), $itemId, 0, $pm->get( 'fromid' ), $pm->get( 'toid' ), $message, 0, $this->uddeIMConfigRAW );
				}
			}
		}
	}

	/**
	 * Converts string containing HTML to BBCode
	 *
	 * @param string $string
	 * @return string
	 */
	private function htmlToBBCode( $string )
	{
		// Bold:
		$string		=	preg_replace( '%<strong[^>]*>(.*?)</strong>%i', '[b]$1[/b]', $string );
		$string		=	preg_replace( '%<b[^>]*>(.*?)</b>%i', '[b]$1[/b]', $string );
		$string		=	preg_replace( '%<span style="font-weight: bold">(.*?)</span>%i', '[b]$1[/b]', $string );

		// Underline:
		$string		=	preg_replace( '%<u[^>]*>(.*?)</u>%i', '[u]$1[/u]', $string );
		$string		=	preg_replace( '%<span style="text-decoration: underline">(.*?)</span>%i', '[u]$1[/u]', $string );

		// Italic:
		$string		=	preg_replace( '%<i[^>]*>(.*?)</i>%i', '[i]$1[/i]', $string );
		$string		=	preg_replace( '%<span style="font-style: italic">(.*?)</span>%i', '[i]$1[/i]', $string );

		// Size:
		$string		=	preg_replace( '%<font size="([1-7])">(.*?)</font>%i', '[size=$1]$2[/size]', $string );

		// Color:
		$string		=	preg_replace( '%<span style="color: #(.{1,6}?)">(.*?)</span>%i', '[color=#$1]$2[/color]', $string );

		// Links:
		$string		=	preg_replace( '%<a[^>]*href="(.*?)"[^>]*>(.*?)</a>%i', '[url=$1]$2[/url]', $string );

		// Images:
		$string		=	preg_replace( '%<img[^>]*src="(.*?)"[^>]*width="([0-9]*?)"[^>]*/>%i', '[img size=$2]$1[/img]', $string );
		$string		=	preg_replace( '%<img[^>]*src="(.*?)"[^>]*/>%i', '[img]$1[/img]', $string );

		// Lists:
		$string		=	preg_replace( '%<ul[^>]*>(.*?)</ul>%i', '[ul]$1[/ul]', $string );
		$string		=	preg_replace( '%<ol[^>]*>(.*?)</ol>%i', '[ol]$1[/ol]', $string );
		$string		=	preg_replace( '%<li[^>]*>(.*?)</li>%i', '[li]$1[/li]', $string );

		// Linebreaks:
		$string		=	preg_replace( '%<br\s*/?>%i', "\n", $string );

		// Remove any remaining unsupported HTML:
		$string		=	strip_tags( $string );

		return $string;
	}

	/**
	 * Converts string containing BBCode to HTML
	 *
	 * @param string $string
	 * @return string
	 */
	private function bbCodeToHTML( $string )
	{
		// Parse BBCode:
		$string		=	uddeIMbbcode_replace( $string, $this->uddeIMConfigRAW );

		// Remove remaining BBCode:
		$string		=	uddeIMbbcode_strip( $string, $this->uddeIMConfigRAW );

		return $string;
	}

	/**
	 * returns all the parameters needed for a hyperlink or a menu entry to do a pms action
	 *
	 * @param  int     $toUserId     UserId of receiver
	 * @param  int     $fromUserId   UserId of sender
	 * @param  string  $subject      Subject of PMS message
	 * @param  string  $message      Body of PMS message
	 * @param  int     $kind         kind of link: 1: link to compose new PMS message for $toid user. 2: link to inbox of $fromid user; 3: outbox, 4: trashbox, 5: link to edit pms options, 6: archive
	 * @return array|boolean         Array of string {"caption" => menu-text ,"url" => NON-cbSef relative url-link, "tooltip" => description} or false and errorMSG
	 */
	public function getPMSlink( $toUserId, $fromUserId, $subject, $message, $kind )
	{
		if ( ! $this->isInstalled() ) {
			return false;
		}

		static $itemId		=	null;

		if ( $itemId === null ) {
			$itemId			=	uddeIMgetItemid( $this->uddeIMConfigRAW );
		}

		$urlBase			=	'index.php?option=com_uddeim';
		$urlItemId			=	( $itemId ? '&amp;Itemid=' . (int) $itemId : null );

		switch( $kind ) {
			case 1: // Send PM
				return array(	'caption'	=>	CBTxt::T( 'PM_USER', 'Send Private Message' ),
								'url'		=>	$urlBase . '&amp;task=new&amp;recip=' . (int) $toUserId . $urlItemId,
								'tooltip'	=>	CBTxt::T( 'PM_USER_DESC', 'Send a Private Message to this user' )
							);
				break;
			case 2: // Inbox
				return array(	'caption'	=>	CBTxt::T( 'PM_INBOX', 'Show Private Inbox' ),
								'url'		=>	$urlBase . '&amp;task=inbox' . $urlItemId,
								'tooltip'	=>	CBTxt::T( 'PM_INBOX_DESC', 'Show Received Private Messages' )
							);
				break;
			case 3: // Outbox
				return array(	'caption'	=>	CBTxt::T( 'PM_OUTBOX', 'Show Private Outbox' ),
								'url'		=>	$urlBase . '&amp;task=outbox' . $urlItemId,
								'tooltip'	=>	CBTxt::T( 'PM_OUTBOX_DESC', 'Show Sent/Pending Private Messages' )
							);
				break;
			case 4: // Trashcan
				return array(	'caption'	=>	CBTxt::T( 'PM_TRASHBOX', 'Show Private Trashbox' ),
								'url'		=>	$urlBase . '&amp;task=trashcan' . $urlItemId,
								'tooltip'	=>	CBTxt::T( 'PM_TRASHBOX_DESC', 'Show Trashed Private Messages' )
							);
				break;
			case 5: // Options
				return array(	'caption'	=>	CBTxt::T( 'PM_OPTIONS', 'Edit PMS Options' ),
								'url'		=>	$urlBase . '&amp;task=settings' . $urlItemId,
								'tooltip'	=>	CBTxt::T( 'PM_OPTIONS_DESC', 'Edit Private Messaging System Options' )
							);
				break;
			case 6: // Archive
				return array(	'caption'	=>	CBTxt::T( 'PM_ARCHIVE', 'Show Private Archive' ),
								'url'		=>	$urlBase . '&amp;task=archive' . $urlItemId,
								'tooltip'	=>	CBTxt::T( 'PM_ARCHIVE_DESC', 'Show Archived Private Messages' )
							);
				break;
		}

		return false;
	}

	/**
	 * Returs array of PMS capabilities or false if no compatible PMS is installed
	 *
	 * @return array|bool false: no compatible PMS installed; array: { 'subject' => boolean, 'body' => boolean }
	 */
	public function getPMScapabilites()
	{
		if ( ! $this->isInstalled() ) {
			return false;
		}

		return array( 'subject' => false, 'body' => true );
	}

	/**
	 * Counts number of unread uddeim messages (trashed and archived also excluded) for a user
	 *
	 * @param int $userId
	 * @return int
	 */
	public function getPMSunreadCount( $userId )
	{
		if ( ! $this->isInstalled() ) {
			return 0;
		}

		return uddeIMgetInboxCount( $userId, 0, 1 );
	}

	/**
	 * Generates the HTML to display the user profile tab
	 *
	 * @param  TabTable  $tab  the tab database entry
	 * @param  UserTable $user the user being displayed
	 * @param  int       $ui   1 for front-end, 2 for back-end
	 * @return string|boolean  Either string HTML for tab content, or false if ErrorMSG generated
	 */
	public function getDisplayTab( $tab, $user, $ui )
	{
		global $_CB_framework;

		$viewer				=	CBuser::getMyUserDataInstance();

		if ( ( ! $this->isInstalled() ) || ( ! $viewer->get( 'id' ) ) || ( ! $user->get( 'id' ) ) || ( $viewer->get( 'id' ) == $user->get( 'id' ) ) ) {
			return null;
		}

		if ( ! ( $tab->params instanceof ParamsInterface ) ) {
			$tab->params	=	new Registry( $tab->params );
		}

		$showTitle			=	(int) $tab->params->get( 'pmsShowTitle', 1 );
		$showSubject		=	(int) $tab->params->get( 'pmsShowSubject', 0 );
		$description		=	$this->_writeTabDescription( $tab, $user );

		cbValidator::loadValidation();

		$return				=	'<form action="' . $_CB_framework->pluginClassUrl( $this->element, true, array( 'id' => (int) $user->get( 'id' ) ) ) . '" method="post" name="quickMsgForm" id="quickMsgForm" class="cb_form quickMsgForm cbValidation">'
							.		'<div class="panel panel-default">'
							.			( $showTitle ? '<div class="panel-heading">' . CBTxt::T( $tab->title ) . '</div>' : null )
							.			'<div class="panel-body">';

		if ( $description ) {
			$return			.=				'<div class="cbft_delimiter form-group cb_form_line clearfix">'
							.					'<div class="cb_field col-sm-12">'
							.						$description
							.					'</div>'
							.				'</div>';
		}

		if ( $showSubject ) {
			$return			.=				'<div class="cbft_text cbtt_input form-group cb_form_line clearfix">'
							.					'<label for="subject" class="col-sm-3 control-label">' . CBTxt::T( 'PM_SUBJECT', 'Subject' ) . '</label>'
							.					'<div class="cb_field col-sm-9">'
							.						'<input type="text" name="subject" value="" class="form-control" />'
							.					'</div>'
							.				'</div>';
		}

		$return				.=				'<div class="cbft_textarea cbtt_textarea cb_form_line clearfix">'
							.					( $showSubject ? '<label for="subject" class="col-sm-3 control-label">' . CBTxt::T( 'PM_MESSAGE', 'Message' ) . '</label>' : null )
							.					'<div class="cb_field col-sm-' . ( $showSubject ? 9 : 12 ) . '">'
							.						'<textarea name="message" class="form-control required" rows="5"></textarea>'
							.					'</div>'
							.				'</div>'
							.			'</div>'
							.			'<div class="panel-footer">'
							.				'<div class="cb_form_line clearfix">'
							.					'<div class="' . ( $showSubject ? 'col-sm-offset-3 col-sm-9' : 'col-sm-12' ) . '">'
							.						'<input type="submit" value="' . htmlspecialchars( CBTxt::T( 'PM_SEND_MESSAGE', 'Send Message' ) ) . '" class="quickMsgButton quickMsgButtonSubmit btn btn-primary" ' . cbValidator::getSubmitBtnHtmlAttributes() . ' />&nbsp;'
							.					'</div>'
							.				'</div>'
							.			'</div>'
							.		'</div>'
							.		cbGetSpoofInputTag( 'plugin' )
							.	'</form>';

		return $return;
	}

	/**
	 * Called when a user is deleted to clean up their private messages
	 *
	 * @param UserTable $user
	 * @param bool      $success
	 * @return bool
	 */
	public function userDeleted( $user, /** @noinspection PhpUnusedParameterInspection */ $success )
	{
		global $_CB_database;

		if ( $this->isInstalled() && $this->params->get( 'pmsDelete', 0 ) ) {
			$sent				=	$this->params->get( 'pmsDeleteSent', 0 );
			$received			=	$this->params->get( 'pmsDeleteRecieved', 1 );

			if ( $sent || $received ) {
				// Private Messages:
				$query			=	'DELETE'
								.	"\n FROM " . $_CB_database->NameQuote( '#__uddeim' );
				if ( $sent && $received ) {
					$query		.=	"\n WHERE ( " . $_CB_database->NameQuote( 'fromid' ) . " = " . (int) $user->get( 'id' )
								.	' OR ' . $_CB_database->NameQuote( 'toid' ) . ' = ' . (int) $user->get( 'id' ) . ' )';
				} elseif ( $sent ) {
					$query		.=	"\n WHERE " . $_CB_database->NameQuote( 'fromid' ) . " = " . (int) $user->get( 'id' );
				} elseif ( $received ) {
					$query		.=	"\n WHERE " . $_CB_database->NameQuote( 'toid' ) . " = " . (int) $user->get( 'id' );
				}
				$_CB_database->setQuery( $query );
				$_CB_database->query();
			}

			// Notifications:
			$query				=	'DELETE'
								.	"\n FROM " . $_CB_database->NameQuote( '#__uddeim_emn' )
								.	"\n WHERE " . $_CB_database->NameQuote( 'userid' ) . " = " . (int) $user->get( 'id' );
			$_CB_database->setQuery( $query );
			$_CB_database->query();

			// Blocks:
			$query				=	'DELETE'
								.	"\n FROM " . $_CB_database->NameQuote( '#__uddeim_blocks' )
								.	"\n WHERE ( " . $_CB_database->NameQuote( 'blocker' ) . " = " . (int) $user->get( 'id' )
								.	' OR ' . $_CB_database->NameQuote( 'blocked' ) . ' = ' . (int) $user->get( 'id' ) . ' )';
			$_CB_database->setQuery( $query );
			$_CB_database->query();

			// Userlists:
			$query				=	'DELETE'
								.	"\n FROM " . $_CB_database->NameQuote( '#__uddeim_userlists' )
								.	"\n WHERE " . $_CB_database->NameQuote( 'userid' ) . " = " . (int) $user->get( 'id' );
			$_CB_database->setQuery( $query );
			$_CB_database->query();

			// Spam:
			$query				=	'DELETE'
								.	"\n FROM " . $_CB_database->NameQuote( '#__uddeim_spam' )
								.	"\n WHERE ( " . $_CB_database->NameQuote( 'fromid' ) . " = " . (int) $user->get( 'id' )
								.	' OR ' . $_CB_database->NameQuote( 'toid' ) . ' = ' . (int) $user->get( 'id' ) . ' )';
			$_CB_database->setQuery( $query );
			$_CB_database->query();
		}
	}
}
