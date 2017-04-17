<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/17/14 11:26 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Language\CBTxt;
use CBLib\Registry\Registry;
use CB\Database\Table\FieldTable;
use CB\Database\Table\TabTable;
use CB\Database\Table\UserTable;

defined('CBLIB') or die();

/**
 * cbTabs Class implementation
 * Tab Creation handler Class
 */
class cbTabs extends cbTabHandler
{
	/**
	 * Application: 1=Front End 2=Admin
	 * @var int
	 */
	protected $ui					=	0;
	/**
	 * 1=Display 2=Edit
	 * @var int
	 */
	protected $action				=	0;
	/**
	 * Adds additional validation javascript for edit tabs
	 * @var string
	 */
	protected $fieldJS				=	'';
	/**
	 * By position of tab objects for displaying
	 * @var array
	 */
	protected $tabsToDisplay		=	array();
	/**
	 * By position of positions already rendered
	 * @var array
	 */
	protected $renderedPositions	=	array();
	/**
	 * By tabid of tab contents for displaying
	 * @var array
	 */
	protected $tabsContents			=	array();
	/**
	 * To step down html output formatting
	 * @var array
	 */
	protected $_stepDownFormatting	=	array(
												'table'		=> 'tr',
												'tabletrs'	=> 'tr',
												'tr'		=> 'td',
												'td'		=> 'div',
												'divs'		=> 'div',
												'div'		=> 'span',
												'span'		=> 'span',
												'uls'		=> 'ul',
												'ul'		=> 'li',
												'ols'		=> 'ol',
												'ol'		=> 'li',
												'li'		=> 'div',
												'none'		=> 'none'
											 );

	/**
	 * Constructor
	 * Includes files needed for displaying tabs and sets cookie options
	 *
	 * @param  int      $useCookies           If set to 1 cookie will hold last used tab between page refreshes
	 * @param  int      $ui                   User interface: 1: frontend, 2: backend
	 * @param  int      $mode                 Reserved for future use, short-term workaround for to early script output (was cbCalendar object reference)
	 * @param  boolean  $outputTabpaneScript  TRUE (DEFAULT): output scripts for tabpanes, FALSE: silent, no echo output
	 */
	public function __construct( $useCookies, $ui, $mode = null, $outputTabpaneScript = true )
	{
		parent::__construct();

		$this->ui				=	$ui;

		if ( $outputTabpaneScript ) {
			$this->outputTabJS( $useCookies );
		}
	}

	/**
	 * Outputs the tab javascript once
	 *
	 * @param  int $useCookies If set to 1 cookie will hold last used tab between page refreshes
	 */
	public function outputTabJS( $useCookies = 0 )
	{
		global $_CB_framework;

		static $cache	=	false;

		if ( ! $cache ) {
			$tab		=	null;

			if ( isset( $_GET['tab'] ) ) {
				$tab	=	urldecode( stripslashes( cbGetParam( $_GET, 'tab', null ) ) );
			} elseif ( isset( $_POST['tab'] ) ) {
				$tab	=	stripslashes( cbGetParam( $_POST, 'tab', null ) );
			}

			$js			=	"$( '.cbTabs' ).cbtabs({"
						.		"useCookies: " . (int) $useCookies . ","
						.		"tabSelected: '" . addslashes( $tab ) . "'"
						.	"});";

			$_CB_framework->outputCbJQuery( $js, 'cbtabs' );

			$cache		=	true;
		}
	}

	/**
	 * Creates a tab pane
	 *
	 * @param  string  $id          The tab pane name
	 * @param  array   $classes     CSS classes to map to the tabs elements (container, nav, content, and override supported)
	 * @param  array   $attributes  HTML attributes to map to the tabs elements (container, nav, and content supported)
	 * @return string
	 */
	public function startPane( $id, $classes = array(), $attributes = array() )
	{
		$containerClass		=	( isset( $classes['container'] ) ? $classes['container'] : null );
		$navClass			=	( isset( $classes['nav'] ) ? $classes['nav'] : null );
		$contentClass		=	( isset( $classes['content'] ) ? $classes['content'] : null );
		$classOverride		=	( isset( $classes['override'] ) ? $classes['override'] : null );

		$containerAttrs		=	( isset( $attributes['container'] ) ? $attributes['container'] : null );
		$navAttrs			=	( isset( $attributes['nav'] ) ? $attributes['nav'] : null );
		$contentAttrs		=	( isset( $attributes['content'] ) ? $attributes['content'] : null );

		$return				=	'<div class="cbTabs' . ( $containerClass ? ' ' . htmlspecialchars( $containerClass ) : null ) . '" id="cbtabs' . htmlspecialchars( $id ) . '"' . ( $containerAttrs ? ' ' . $containerAttrs : null ) . '>'
							.		'<ul class="cbTabsNav ' . ( $navClass ? ( ! $classOverride ? 'nav nav-tabs ' : null ) . htmlspecialchars( $navClass ) : 'nav nav-tabs' ) . '"' . ( $navAttrs ? ' ' . $navAttrs : null ) . '></ul>'
							.		'<div class="cbTabsContent ' . ( $contentClass ? ( ! $classOverride ? 'tab-content ' : null ) . htmlspecialchars( $contentClass ) : 'tab-content' ) . '"' . ( $contentAttrs ? ' ' . $contentAttrs : null ) . '>';

		return $return;
	}

	/**
	 * Closes a tab pane
	 *
	 * @return string
	 */
	public function endPane() {
		$return		=		'</div>'
					.	'</div>';

		return $return;
	}

	/**
	 * Creates a tab
	 *
	 * @param  null    $pID         Deprecated, does nothing
	 * @param  string  $tabText     This is what is displayed on the tab
	 * @param  string  $paneId      This is the parent pane to build this tab on
	 * @param  array   $classes     CSS classes to map to the tabs elements (pane, tab, and override supported)
	 * @param  array   $attributes  HTML attributes to map to the tabs elements (pane and tab supported)
	 * @return string
	 */
	public function startTab( /** @noinspection PhpUnusedParameterInspection */ $pID, $tabText, $paneId, $classes = array(), $attributes = array() )
	{
		$paneClass		=	( isset( $classes['pane'] ) ? $classes['pane'] : null );
		$tabClass		=	( isset( $classes['tab'] ) ? $classes['tab'] : null );
		$classOverride	=	( isset( $classes['override'] ) ? $classes['override'] : null );

		$paneAttrs		=	( isset( $attributes['pane'] ) ? $attributes['pane'] : null );
		$tabAttrs		=	( isset( $attributes['tab'] ) ? $attributes['tab'] : null );

		$return			=	'<div class="cbTabPane ' . ( $paneClass ? ( ! $classOverride ? 'tab-pane ' : null ) . htmlspecialchars( $paneClass ) : 'tab-pane' ) . '" id="cbtabpane' . htmlspecialchars( $paneId ) . '"' . ( $paneAttrs ? ' ' . $paneAttrs : null ) . '>'
						.		'<h2 class="cbTabNav' . ( $tabClass ? ' ' . htmlspecialchars( $tabClass ) : null ) . '" id="cbtabnav' . htmlspecialchars( $paneId ) . '"' . ( $tabAttrs ? ' ' . $tabAttrs : null ) . '>'
						.			'<a href="#cbtabpane' . htmlspecialchars( $paneId ) . '">' . $tabText . '</a>'
						.		'</h2>';

		return $return;
	}

	/**
	 * Closes a tab
	 *
	 * @return string
	 */
	public function endTab()
	{
		return '</div>';
	}

	/**
	 * Loads tabs list from database (if not already loaded) into $this->tabsToDisplay[$position]
	 *
	 * @param  UserTable  $user      CB user object to display
	 * @param  string     $position  Name of position if only one position to display (default: null)
	 * @return void
	 */
	private function _loadTabsList( $user, $position = '' )
	{
		global $_CB_database, $_CB_framework;

		if ( ! isset( $this->tabsToDisplay[$position] ) ) {
			$_CB_database->setQuery( "SELECT * FROM #__comprofiler_tabs t"
				. "\n WHERE t.enabled=1"
				. ( $position == '' ? "" : "\n AND t.position = " . $_CB_database->Quote( $position ) )
				. ( ! ( ( $_CB_framework->getUi() == 2 ) && Application::MyUser()->isSuperAdmin() ) ?
					"\n AND t.viewaccesslevel IN " . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() )
					: '' )
				. "\n ORDER BY t.position, t.ordering" );
			$this->tabsToDisplay[$position]		=	$_CB_database->loadObjectList( 'tabid', '\CB\Database\Table\TabTable', array() );

			// THIS is VERY experimental, and not yet part of CB API !!! :
			global $_PLUGINS;
			$_PLUGINS->loadPluginGroup( 'user' );
			$_PLUGINS->trigger( 'onAfterTabsFetch', array( &$this->tabsToDisplay[$position], &$user, 'profile' ) );
		}
	}

	/**
	 * Gets html code for all cb tabs, sorted by position (default: all, no position name in db means "cb_tabmain")
	 *
	 * @param  UserTable  $user        Object to display
	 * @param  string     $position    Name of position if only one position to display (default: null)
	 * @param  int        $tabId       Only a specific tab
	 * @param  string     $output
	 * @param  null       $formatting
	 * @param  string     $reason
	 * @return void
	 */
	public function generateViewTabsContent( $user, $position = '', $tabId = null, $output = 'html', $formatting = null /* 'table' or 'divs' */, $reason = 'profile' )
	{
		global $_CB_OneTwoRowsStyleToggle, $ueConfig, $_PLUGINS;

		if ( $formatting === null ) {
			$formatting								=	( isset( $ueConfig['use_divs'] ) && ( ! $ueConfig['use_divs'] ) ? 'table' : 'divs' );
		}

		$tabOneTwoRowsStyleToggle					=	array();
		$this->action								=	1;

		$this->_loadTabsList( $user );

		static $menusPrepared						=	false;
		if ( ! $menusPrepared ) {
			$_PLUGINS->trigger( 'onPrepareMenus', array( &$user ) );
			$menusPrepared							=	true;
		}

		// optimize rendering only for position if tab rendering required (needed because of the $_CB_OneTwoRowsStyleToggle
		if ( $tabId && ! $position ) {
			if ( isset( $this->tabsToDisplay[''][$tabId] ) ) {
				$position	=	( $this->tabsToDisplay[''][$tabId]->position == '' ? 'cb_tabmain' : $this->tabsToDisplay[''][$tabId]->position );

			}
		}

		if ( isset( $this->renderedPositions[$position] ) ) {
			// all tabs are already rendered:
			return;
		}

		//Pass 1: gets all menu and status content + initializes tabsToDisplay[$position] with list of tabs if needed:
		foreach( $this->tabsToDisplay[''] AS $k => $oTab ) {
			if ( ( ! isset( $oTab->position ) ) || ( $oTab->position == '' ) ) {
				$oTab->position						=	'cb_tabmain';
			}
			if( $oTab->pluginclass != null ) {
				$this->_callTabPlugin( $oTab, $user, $oTab->pluginclass, 'getMenuAndStatus', $oTab->pluginid );
			}
			if ( ( $position == '' ) || ( $oTab->position == $position ) ) {
				$this->tabsToDisplay[$oTab->position][$k]	=	$oTab;
			}
		}

		$this->renderedPositions[$position]		=	true;

		if ( ! isset( $this->tabsToDisplay[$position] ) ) {
			return;
		}

		//Pass 2: generate content
		foreach( $this->tabsToDisplay[$position] AS $k => $oTab ) {
			$pos									=	$oTab->position;
			if ( ! isset( $tabOneTwoRowsStyleToggle[$pos] ) ) {
				$tabOneTwoRowsStyleToggle[$pos]	=	1;
			}

			$this->tabsContents[$k]				=	'';
			if( $oTab->pluginclass != null ) {
				$_CB_OneTwoRowsStyleToggle			=	$tabOneTwoRowsStyleToggle[$pos];
				$pluginTabContent					=	$this->_callTabPlugin($oTab, $user, $oTab->pluginclass, 'getDisplayTab', $oTab->pluginid);
				if ( is_array( $pluginTabContent ) ) {
					$this->tabsContents[$k]			.=	$this->_renderFields( $pluginTabContent, $user, $output, $formatting, $reason, array() );
				} else {
					$this->tabsContents[$k]			.=	$pluginTabContent;
				}
				$tabOneTwoRowsStyleToggle[$pos]	=	$_CB_OneTwoRowsStyleToggle;
			}
		}
		foreach( $this->tabsToDisplay[$position] AS $k => $oTab ) {
			$pos									=	$oTab->position;
			if ( $oTab->fields ) {
				$_CB_OneTwoRowsStyleToggle			=	$tabOneTwoRowsStyleToggle[$pos];
				$this->tabsToDisplay[$position][$k]->_fieldsCount				=	0;
				$this->tabsContents[$k]			.=	$this->_getTabContents( $oTab->tabid, $user, $this->tabsToDisplay[$position][$k]->_fieldsCount, $output, $formatting, $reason );
				$tabOneTwoRowsStyleToggle[$pos]	=	$_CB_OneTwoRowsStyleToggle;
			}
		}
		$_PLUGINS->trigger( 'onAfterPrepareViewTabs', array( &$this->tabsContents, &$this->tabsToDisplay[$position], &$user, $position, $tabId ) );
	}

	/**
	 * Returns tab content if set
	 *
	 * @param  int          $tabId
	 * @param  string|null  $default
	 * @return string|null
	 */
	public function getProfileTabHtml( $tabId, $default = null )
	{
		if ( isset( $this->tabsContents[$tabId] ) ) {
			return $this->tabsContents[$tabId];
		}
		return $default;
	}

	/**
	 * Gets html code for all cb tabs, sorted by position (default: all, no position name in db means "cb_tabmain")
	 *
	 * @param  UserTable  $user      CB user object to display
	 * @param  string     $position  Name of position if only one position to display (default: null)
	 * @return array                 Array of string with html to display at each position, key = position name, or NULL if position is empty.
	 *
	 * @throws \LogicException
	 */
	public function getViewTabs( $user, $position = '' )
	{
		global $_CB_framework, $ueConfig;

		// returns cached rendering if needed:
		static $renderedCache					=	array();

		if ( isset( $renderedCache[$user->id] ) ) {
			if ( $position == '' ) {
				return $renderedCache[$user->id];
			}
			if ( isset( $renderedCache[$user->id][$position] ) ) {
				return array( $position => $renderedCache[$user->id][$position] );
			}
		}

		// detects recursion loops (e.g. trying to render a position within a position !):
		static $callCounter						=	0;

		if ( $callCounter++ > 10 ) {
			throw new \LogicException( 'Rendering recursion for CB position: ' . $position, 500 );
		}

		// loads the tabs and generate the inside content of the tab:
		$this->generateViewTabsContent( $user, $position );

		// recursion counter decrement:
		$callCounter--;

		if ( ! isset( $this->tabsToDisplay[$position] ) ) {
			return null;
		}

		//	$output									=	'html';
		$tabsMap								=	array();
		$html									=	array();
		$results								=	array();
		$oNest									=	array();
		$oNestVert								=	array();
		$oMenu									=	array();
		$oMenuNest								=	array();
		$oVert									=	array();
		$i										=	0;

		//Pass 3: generate formatted output for each position by display type (keeping tabs together in each position)
		foreach( $this->tabsToDisplay[$position] AS $k => $oTab ) {
			if ( $oTab->pluginclass ) {
				$tabsMap[strtolower( $oTab->pluginclass )]	=	$oTab->tabid;
			}

			$pos								=	$oTab->position;

			if( ! isset($html[$pos] ) ) {
				$html[$pos]						=	'';
				$results[$pos]					=	'';
				$oNest[$pos]					=	'';
				$oNestVert[$pos]				=	'';
				$oMenu[$pos]					=	'';
				$oMenuNest[$pos]				=	'';
				$oVert[$pos]					=	'';
			}

			// handles content of tab:
			$tabContent							=	$this->tabsContents[$k];

			if ( ( $tabContent != '' ) || ( $oTab->fields && ( $oTab->_fieldsCount > 0 ) && isset( $ueConfig['showEmptyTabs'] ) && ( $ueConfig['showEmptyTabs'] == 1 ) ) ) {
				$overlaysWidth 					=	'400';			//BB later this could be one more tab parameter...
				$tabTitle						=	$this->_callTabPlugin( $oTab, $user, $oTab->pluginclass, 'getTabTitle', $oTab->pluginid );

				switch ($oTab->displaytype) {
					//	case "template":
					//		$cbTemplate	=	HTML_comprofiler::_cbTemplateLoad();
					//		$html[$pos] .=	HTML_comprofiler::_cbTemplateRender( $cbTemplate, $user, 'Profile', 'drawTab', array( &$user, $oTab, $tabTitle, $tabContent, 'cb_tabid_' . $oTab->tabid ), $output );
					//		break;
					case "html":
						$html[$pos]			.=	'<div class="cb_tab_content cb_tab_html' . ( $oTab->cssclass ? ' ' . htmlspecialchars( $oTab->cssclass ) : null ) . '" id="cb_tabid_' . (int) $oTab->tabid . '">'
											.		$tabContent
											.	'</div>';
						break;
					case "div":
						$html[$pos]			.=	'<div class="cb_tab_content cb_tab_div' . ( $oTab->cssclass ? ' ' . htmlspecialchars( $oTab->cssclass ) : null ) . '" id="cb_tabid_' . (int) $oTab->tabid . '">'
											.		'<div class="cb_tab_content_heading page-header"><h4>' . $tabTitle . '</h4></div>'
											.		$tabContent
											.	'</div>';
						break;
					case "rounddiv":
						$html[$pos]			.=	'<div class="cb_tab_container cb_tab_rounddiv panel panel-default' . ( $oTab->cssclass ? ' ' . htmlspecialchars( $oTab->cssclass ) : null ) . '">'
											.		'<div class="panel-heading">' . $tabTitle . '</div>'
											.		'<div class="cb_tab_content panel-body" id="cb_tabid_' . (int) $oTab->tabid . '">'
											.			$tabContent
											.		'</div>'
											.	'</div>';
						break;
					case "roundhtml":
						$html[$pos]			.=	'<div class="cb_tab_container cb_tab_roundhtml panel panel-default' . ( $oTab->cssclass ? ' ' . htmlspecialchars( $oTab->cssclass ) : null ) . '">'
											.		'<div class="cb_tab_content panel-body" id="cb_tabid_' . (int) $oTab->tabid . '">'
											.			$tabContent
											.		'</div>'
											.	'</div>';
						break;
					case "overlib":
						$fieldTip			=	'<div class="cb_tab_content cb_tab_overlib' . ( $oTab->cssclass ? ' ' . htmlspecialchars( $oTab->cssclass ) : null ) . '" id="cb_tabid_' . (int) $oTab->tabid . '" style="width:100%">'
											.		$tabContent
											.	'</div>';

						$html[$pos]			.=	cbTooltip( $this->ui, $fieldTip, $tabTitle, $overlaysWidth, null, $tabTitle, null, 'data-cbtooltip-position-target="mouse" data-cbtooltip-tip-hide="true" class="cb_tab_overlib_container"' );
						break;
					case "overlibfix":
						$fieldTip			=	'<div class="cb_tab_content cb_tab_overlib_fix' . ( $oTab->cssclass ? ' ' . htmlspecialchars( $oTab->cssclass ) : null ) . '" id="cb_tabid_' . (int) $oTab->tabid . '" style="width:100%">'
											.		$tabContent
											.	'</div>';

						$html[$pos]			.=	cbTooltip( $this->ui, $fieldTip, $tabTitle, $overlaysWidth, null, $tabTitle, null, 'data-cbtooltip-delay="200" class="cb_tab_overlib_fix_container"' );
						break;
					case "overlibsticky":
						$fieldTitle			=	'<button type="button" class="cb_tab_overlib_sticky_button btn btn-default">' . $tabTitle . '</button>';

						$fieldTip			=	'<div class="cb_tab_content cb_tab_overlib_sticky' . ( $oTab->cssclass ? ' ' . htmlspecialchars( $oTab->cssclass ) : null ) . '" id="cb_tabid_' . (int) $oTab->tabid . '" style="width:100%">'
											.		$tabContent
											.	'</div>';

						$html[$pos]			.=	cbTooltip( $this->ui, $fieldTip, $tabTitle, $overlaysWidth, null, $fieldTitle, null, 'data-cbtooltip-open-event="click" data-cbtooltip-close-event="click unfocus" class="cb_tab_overlib_sticky_container"' );
						break;
					case "nested":
						$oNest[$pos]		.=	$this->startTab( 'CBNested' . $pos, $tabTitle, $oTab->tabid, array( 'tab' => 'cbTabNavNested' . ( $oTab->cssclass ? ' ' . $oTab->cssclass : null ), 'pane' => 'cbTabPaneNested' . ( $oTab->cssclass ? ' ' . $oTab->cssclass : null ) ) )
											.		'<div class="cb_tab_content cb_tab_nested" id="cb_tabid_' . $oTab->tabid . '">'
											.			$tabContent
											.		'</div>'
											.	$this->endTab();
						$i++;
						break;
					case "nestedvertical":
						$oNestVert[$pos]	.=	$this->startTab( 'CBNestedVertical' . $pos, $tabTitle, $oTab->tabid, array( 'tab' => 'cbTabNavNestedVertical' . ( $oTab->cssclass ? ' ' . $oTab->cssclass : null ), 'pane' => 'cbTabPaneNestedVertical' . ( $oTab->cssclass ? ' ' . $oTab->cssclass : null ) ) )
											.		'<div class="cb_tab_content cb_tab_vertical_nested" id="cb_tabid_' . $oTab->tabid . '">'
											.			$tabContent
											.		'</div>'
											.	$this->endTab();
						$i++;
						break;
					case "menu":
						$oMenu[$pos]		.=	$this->startTab( 'CBMenu' . $pos, $tabTitle, $oTab->tabid, array( 'tab' => 'cbTabNavMenu' . ( $oTab->cssclass ? ' ' . $oTab->cssclass : null ), 'pane' => 'tab-pane cbTabPaneMenu' . ( $oTab->cssclass ? ' ' . $oTab->cssclass : null ), 'override' => true ) )
											.		'<div class="cb_tab_content cb_tab_menu" id="cb_tabid_' . $oTab->tabid . '">'
											.			$tabContent
											.		'</div>'
											.	$this->endTab();
						$i++;
						break;
					case "menunested":
						$oMenuNest[$pos]	.=	$this->startTab( 'CBMenu' . $pos, $tabTitle, $oTab->tabid, array( 'tab' => 'cbTabNavMenuNested' . ( $oTab->cssclass ? ' ' . $oTab->cssclass : null ), 'pane' => 'tab-pane cbTabPaneMenuNested' . ( $oTab->cssclass ? ' ' . $oTab->cssclass : null ), 'override' => true ) )
											.		'<div class="cb_tab_content cb_tab_menu_nested" id="cb_tabid_' . $oTab->tabid . '">'
											.			$tabContent
											.		'</div>'
											.	$this->endTab();
						$i++;
						break;
					case "vertical":
						$oVert[$pos]		.=	$this->startTab( 'CBVertical' . $pos, $tabTitle, $oTab->tabid, array( 'tab' => 'cbTabNavVertical' . ( $oTab->cssclass ? ' ' . $oTab->cssclass : null ), 'pane' => 'cbTabPaneVertical' . ( $oTab->cssclass ? ' ' . $oTab->cssclass : null ) ) )
											.		'<div class="cb_tab_content cb_tab_vertical" id="cb_tabid_' . $oTab->tabid . '">'
											.			$tabContent
											.		'</div>'
											.	$this->endTab();
						$i++;
						break;
					case "tab":
					default:
						$results[$pos]		.=	$this->startTab( $pos, $tabTitle, $oTab->tabid, array( 'tab' => $oTab->cssclass, 'pane' => $oTab->cssclass ) )
											.		'<div class="cb_tab_content cb_tab_main" id="cb_tabid_' . $oTab->tabid . '">'
											.			$tabContent
											.		'</div>'
											.	$this->endTab();
						$i++;
						break;
				}
			}
		}	//foreach tab

		// Pass 4: concat different types, generating tabs preambles/postambles:
		foreach ( $html as $pos => $val ) {
			if ( $oNest[$pos] ) {
				$results[$pos]	.=	$this->startTab( $pos, CBTxt::T( 'TABS_NESTED_MORE TABS_NESTED_HORIZONTAL_MORE', 'More' ), $pos . 0, array( 'tab' => 'cbTabNavNested', 'pane' => 'cbTabPaneNested' ) )
								.		'<div class="cb_tab_container cb_tab_nested_main" id="cb_position_' . $pos . '">'
								.			$this->startPane( 'CBNested' . $pos, array( 'container' => 'cbTabsNested', 'nav' => 'cbTabsNavNested', 'content' => 'cbTabsContentNested' ) )
								.				$oNest[$pos]
								.			$this->endPane()
								.		'</div>'
								.	$this->endTab();
			}

			if ( $oNestVert[$pos] ) {
				$oVert[$pos]	.=	$this->startTab( $pos, CBTxt::T( 'TABS_NESTED_MORE TABS_NESTED_VERTICAL_MORE', 'More' ), $pos . 0, array( 'tab' => 'cbTabNavVertical', 'pane' => 'cbTabPaneVertical' ) )
								.		'<div class="cb_tab_content cb_tab_nested_vertical" id="cb_position_' . $pos . '">'
								.			$this->startPane( 'CBNestedVertical' . $pos, array( 'container' => 'cbTabsNestedVertical', 'nav' => 'cbTabsNavNestedVertical', 'content' => 'cbTabsContentNestedVertical' ) )
								.				$oNestVert[$pos]
								.			$this->endPane()
								.		'</div>'
								.	$this->endTab();
			}

			if ( $oMenu[$pos] || $oMenuNest[$pos] ) {
				static $oMenuJS	=	0;

				if ( ! $oMenuJS++ ) {
					$js			=	"$( '.cbTabsMenuNavBar' ).on( 'click', '.navbar-toggle', function() {"
								.		"if ( ! $( this ).hasClass( 'dropdown-toggle' ) ) {"
								.			"var navbar = $( this ).closest( '.cbTabsMenuNavBar' ).find( '.navbar-collapse' );"
								.			"var toggle = $( this ).closest( '.cbTabsMenuNavBar' ).find( '.navbar-toggle' );"
								.			"if ( toggle.hasClass( 'collapsed' ) ) {"
								.				"navbar.addClass( 'in' );"
								.				"toggle.removeClass( 'collapsed' );"
								.			"} else {"
								.				"navbar.removeClass( 'in' );"
								.				"toggle.addClass( 'collapsed' );"
								.			"}"
								.		"}"
								.	"}).find( '.cbScroller' ).cbscroller({"
								.		"ignore: '.cbTabNavMenuMore,.cbTabNavMenuNested',"
								.		"height: false"
								.	"});"
								.	"$( '.cbTabsMenu' ).on( 'cbtabs.selected', function( e, event, cbtabs, tab ) {"
								.		"var dropdownNav = $( event.target ).closest( '.cbTabsMenuNavBar' );"
								.		"if ( dropdownNav.length ) {"
								.			"var toggle = dropdownNav.find( '.navbar-toggle' );"
								.			"if ( ! toggle.hasClass( 'collapsed' ) ) {"
								.				"toggle.click();"
								.			"}"
								.		"}"
								.		"var dropdownTab = $( event.target ).closest( '.cbTabNavMenuNested' );"
								.		"if ( dropdownTab.length ) {"
								.			"if ( dropdownTab.closest( '.cbTabNavMoreDropdown' ).length ) {"
								.				"dropdownTab.siblings().removeClass( 'active' );"
								.				"dropdownTab.addClass( 'active' );"
								.			"}"
								.		"}"
								.	"});"
								.	"$( window ).load( function(){"
								.		"$( '.cbTabNavMenuMore' ).on( 'cbtooltip.show', function( e, cbtooltip, event, api ) {"
								.			"if ( $( this ).siblings( '.active' ).length ) {"
								.				"api.elements.content.find( '.cbTabNav' ).removeClass( 'active' );"
								.			"}"
								.		"});"
								.	"});";

					$_CB_framework->outputCbJQuery( $js, 'cbscroller' );
				}

				if ( $oMenuNest[$pos] ) {
					$more		=	'<li class="cbTabNavMore cbTabNavMenuMore cbTooltip dropdown" data-cbtooltip-tooltip-target="#cbtabs' . htmlspecialchars( 'CBMenu' . $pos ) . 'More" data-cbtooltip-menu="true" data-cbtooltip-classes="qtip-nostyle cbTabNavMoreDropdown cbMenuDropdown" data-cbtooltip-adjust-y="0" data-cbtooltip-open-classes="open active">'
								.		'<button type="button" class="cbTabNavMenuMoreBtn dropdown-toggle navbar-toggle">'
								.			'<span class="icon-bar"></span>'
								.			'<span class="icon-bar"></span>'
								.			'<span class="icon-bar"></span>'
								.		'</button>'
								.		'<a href="javascript:void(0);" class="cbTabNavMenuMoreLink dropdown-toggle">' . CBTxt::T( 'TABS_NESTED_MORE TABS_NESTED_MENU_MORE', 'More' ) . ' <b class="fa fa-caret-down"></b></a>'
								.		'<ul id="cbtabs' . htmlspecialchars( 'CBMenu' . $pos ) . 'More" class="cbSubMenu dropdown-menu"></ul>'
								.	'</li>';

					$js			=	"$( '#cbtabs" . addslashes( 'CBMenu' . $pos ) . "' ).find( '.cbTabsNav:first' ).append( '" . addslashes( $more ) . "' );"
								.	"$( '#cbtabs" . addslashes( 'CBMenu' . $pos ) . "' ).find( '.cbTabsNav:first' ).children( '.cbTabNavMenuNested' ).appendTo( '#cbtabs" . addslashes( 'CBMenu' . $pos ) . "More' );";

					$_CB_framework->outputCbJQuery( $js );
				}

				$html[$pos]		.=	'<div class="cbTabs cbTabsMenu' . ( $oMenuNest[$pos] ? ' cbTabsMenuMore' : null ) . '" id="cbtabs' . htmlspecialchars( 'CBMenu' . $pos ) . '">'
								.		'<div class="cbTabsMenuNavBar' . ( $oMenuNest[$pos] ? ' cbTabsMenuNavBarMore' : null ) . ' navbar navbar-default">'
								.			'<div class="container-fluid">'
								.				'<div class="navbar-header">'
								.					'<button type="button" class="cbTabsMenuNavBarToggle navbar-toggle collapsed">'
								.						'<span class="icon-bar"></span>'
								.						'<span class="icon-bar"></span>'
								.						'<span class="icon-bar"></span>'
								.					'</button>'
								.				'</div>'
								.				'<div class="collapse navbar-collapse cbScroller">'
								.					'<div class="cbScrollerLeft hidden">'
								.						'<button type="button" class="btn btn-xs btn-default"><span class="fa fa-angle-left"></span></button>'
								.					'</div>'
								.					'<ul class="cbTabsNav cbTabsMenuNav' . ( $oMenuNest[$pos] ? ' cbTabsMenuNavMore' : null ) . ' nav navbar-nav cbScrollerContent"></ul>'
								.					'<div class="cbScrollerRight hidden">'
								.						'<button type="button" class="btn btn-xs btn-default"><span class="fa fa-angle-right"></span></button>'
								.					'</div>'
								.				'</div>'
								.			'</div>'
								.		'</div>'
								.		'<div class="cbTabsContent cbTabsMenuContent tab-content">'
								.			$oMenu[$pos]
								.			$oMenuNest[$pos]
								.		'</div>'
								.	'</div>';
			}

			if ( $oVert[$pos] ) {
				$html[$pos]		.=	$this->startPane( 'CBVertical' . $pos, array( 'container' => 'row cbTabsVertical', 'nav' => 'nav-stacked col-md-3 cbTabsNavVertical', 'content' => 'col-md-9 cbTabsContentVertical' ) )
								.		$oVert[$pos]
								.	$this->endPane();
			}

			if ( $results[$pos] ) {
				if ( $val ) {
					$html[$pos]	.=	'<br />';
				}

				$html[$pos]		.=	$this->startPane( $pos )
								.		$results[$pos]
								.	$this->endPane();
			}
		}

		// cache rendering if it's the complete rendering:
		if ( $position == '' ) {
			$renderedCache[$user->id]		=	$html;
		}

		// check if pluginclass has been provided as the tab selected
		$tab					=	strtolower( stripslashes( cbGetParam( $_REQUEST, 'tab', null ) ) );

		if ( $tab && isset( $tabsMap[$tab] ) ) {
			$_CB_framework->outputCbJQuery( "$( '#cbtabnav" . (int) $tabsMap[$tab] . " > a' ).click();" );
		}

		return $html;
	}

	/**
	 * Gets html code for all cb tabs, sorted by position (default: all, no position name in db means "cb_tabmain")
	 *
	 * @param  UserTable     $user        CB user object to display
	 * @param  array         $postdata    _POST data
	 * @param  string        $output      Output
	 * @param  string        $formatting  Formatting
	 * @param  string        $reason      Reason of edit
	 * @param  boolean|int   $tabbed      True|1: Tabbed, 2: Stepped, False|0: Flat
	 * @return array|string               If $tabbed == True|1|2: Array of string with html to display at each position, key = position name, or NULL if position is empty. False|0: String
	 */
	public function getEditTabs( &$user, $postdata = null, $output = 'htmledit', $formatting = 'table', $reason = 'edit', $tabbed = true )
	{
		global $_PLUGINS;

		$this->action						=	2;
		$this->fieldJS						=	'';
		$results							=	'';

		$oTabs								=	$this->_getTabsDb( $user, $reason );

		$initFieldsToDefault				=	( ( $reason == 'register' ) && ( $postdata === null ) ) || ( ( $reason == 'edit' ) && ( $user->id == null ) && ( $postdata === null ) );

		// if tab does not display CB fields by CB, and we are registering or creating a new user, we still need to init fields to default value:
		if ( $initFieldsToDefault ) {
			$fields							=	$this->_getTabFieldsDb( null, $user, $reason, null, true );

			if ( is_array( $fields ) ) {
				foreach ( $fields as $oField ) {
					$this->_initFieldToDefault( $oField, $user, $reason );
				}
			}
		}

		$oContent							=	'';

		foreach( $oTabs AS $oTab ) {
			$tabContent						=	'';

			// get Content from super-tabs:	// experimental event:
			$_PLUGINS->trigger( 'onBeforeEditATab', array( &$tabContent, &$oTab, &$user, &$postdata, $output, $formatting, $reason, $tabbed ) );

			$pluginTabContent				=	null;

			// get Content from plugin tabs:
			if ( $oTab->pluginclass != null ) {
				if ( $reason == 'register' ) {
					$userNull				=	null;
					$pluginTabContent		=	$this->_callTabPlugin( $oTab, $userNull, $oTab->pluginclass, 'getDisplayRegistration', $oTab->pluginid, $postdata );
				} else {
					$pluginTabContent		=	$this->_callTabPlugin( $oTab, $user, $oTab->pluginclass, 'getEditTab', $oTab->pluginid );
				}

				if ( is_array( $pluginTabContent ) ) {
					$tabContent				.=	$this->_renderFields( $pluginTabContent, $user, $output, $formatting, $reason, array() );
				} else {
					$tabContent				.=	$pluginTabContent;
				}

				$this->fieldJS				.=	$this->_getVarPlugin( $oTab, $oTab->pluginclass, 'fieldJS', $oTab->pluginid );
			}

			// get Content from fields:
			if ( $oTab->fields ) {
				if ( ( $oTab->pluginclass != null ) || ( $reason == 'register' ) ) {
					$oTab->description		=	null;
				}

				$tabContent					.=	$this->_getEditTabContents( $oTab, $user, $output, $formatting, $reason, true );
			}

			// get Content from super-tabs:	// experimental event:
			$_PLUGINS->trigger( 'onAfterEditATab', array( &$tabContent, &$oTab, &$user, &$postdata, $output, $formatting, $reason, $tabbed ) );

			// This is a plugin tab and the plugin is disabled; so shut off its output:
			if ( $pluginTabContent === false ) {
				$tabContent					=	'';
			}

			if ( $tabbed && ( $tabContent != '' ) ) {
				$tabTitle					=	$this->_callTabPlugin( $oTab, $user, $oTab->pluginclass, 'getTabTitle', $oTab->pluginid );

				$results					.=	$this->startTab( 'CB', $tabTitle, $oTab->tabid, array( 'tab' => $oTab->cssclass, 'pane' => $oTab->cssclass ) )
											.		'<div class="cb_tab_content cb_tab_main" id="cb_tabid_' . $oTab->tabid . '">'
											.			$tabContent
											.		'</div>';

				if ( (int) $tabbed === 2 ) {
					$results				.=		'<ul class="cb_tab_content_nav cbStepByStep pager">'
											.			'<li class="previous pull-left text-left cbStepByStepLeft">'
											.				'<a href="javascript: void(0);" class="cbTabNavPrevious cbStepByStepPrevious">' . CBTxt::T( 'STEP_BY_STEP_PREVIOUS', '[icon] Previous', array( '[icon]' => '<span class="fa fa-long-arrow-left"></span>' ) ) . '</a>'
											.			'</li>'
											.			'<li class="next pull-right text-right cbStepByStepRight">'
											.				'<a href="javascript: void(0);" class="cbTabNavNext cbStepByStepNext">' . CBTxt::T( 'STEP_BY_STEP_NEXT', 'Next [icon]', array( '[icon]' => '<span class="fa fa-long-arrow-right"></span>' ) ) . '</a>'
											.			'</li>'
											.		'</ul>';
				}

				$results					.=	$this->endTab();
			} else {
				$oContent					.=	$tabContent;
			}
		}

		if ( $tabbed ) {
			if ( (int) $tabbed === 2 ) {
				$return						=	$this->startPane( 'CB', array( 'nav' => 'nav nav-pills', 'override' => true ), array( 'container' => 'data-cbtabs-step-by-step="true"' ) );
			} else {
				$return						=	$this->startPane( 'CB' );
			}

			$return							.=		$results
											.	$this->endPane();

			return $return;
		} else {
			return $oContent;
		}
	}

	/**
	 * Gets tabs for $reason (WARNING: here we have 'editsave' as additional reason !)
	 * (needs to be public for backwards compatibility)
	 *
	 * @param  UserTable  $user
	 * @param  string              $reason ( 'profile', 'register', 'list', 'edit', 'editsave' )
	 * @return TabTable[]
	 */
	public function & _getTabsDb( $user, $reason )
	{
		global $_CB_framework, $_CB_database;

		static $tabsCache	=	null;

		if ( $tabsCache === null ) {
			$sql			=	'SELECT * FROM #__comprofiler_tabs t'
				.	"\n WHERE t.enabled = 1";
			if ( ! ( ( $_CB_framework->getUi() == 2 ) && Application::MyUser()->isSuperAdmin() ) ) {
				if ( $reason != 'register' ) {
					$sql	.=	"\n AND t.viewaccesslevel IN " . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() );
				}
			}
			$sql			.=	"\n ORDER BY ";
			if ( $reason == 'register' ) {
				$sql		.=	't.ordering_register, ';
			}
			$sql			.=	't.position, t.ordering';
			$_CB_database->setQuery( $sql );
			$tabsCache		=	$_CB_database->loadObjectList( 'tabid', '\CB\Database\Table\TabTable', array() );

			// THIS is VERY experimental, and not yet part of CB API !!! :
			global $_PLUGINS;
			$_PLUGINS->loadPluginGroup( 'user' );
			$_PLUGINS->trigger( 'onAfterTabsFetch', array( &$tabsCache, &$user, $reason ) );
		}
		return $tabsCache;
	}

	/**
	 * Gets the FieldTable's corresponding to $tabid (and $reason if not $fullAccess)
	 *
	 * @param  int         $tabId
	 * @param  UserTable   $user
	 * @param  string      $reason
	 * @param  int|string  $fieldIdOrName
	 * @param  boolean     $prefetchFields
	 * @param  boolean     $fullAccess
	 * @return FieldTable[]
	 */
	public function _getTabFieldsDb( $tabId, $user, $reason, $fieldIdOrName = null, $prefetchFields = true, $fullAccess = false )
	{
		static $preFetched		=	array();
		static $fieldsByName	=	array();

		$fields					=	array();

		$preIdx					=	$fullAccess ? 'full' : $reason;

		if ( ( ! $prefetchFields ) || ! isset( $preFetched[$preIdx] ) ) {

			global $_CB_framework, $_CB_database, $ueConfig;

			$where				=	array();
			$ordering			=	array();

			if ( $fieldIdOrName && ! $prefetchFields ) {
				if ( is_int( $fieldIdOrName ) ) {
					$where[]	=	'f.fieldid = ' . (int) $fieldIdOrName;
				} else {
					$where[]	=	'f.name = ' . $_CB_database->Quote( $fieldIdOrName );
				}
			}
			if ( ( $reason == 'list' ) && ( in_array( $ueConfig['name_format'], array( 1, 2, 4 ) ) ) ) {
				$where[]		=	"( f.published = 1 OR f.name = 'name' )";
			} elseif ( $reason != 'adminfulllist' ) {
				$where[]		=	'f.published = 1';
			}
			if ( ! $fullAccess ) {
				switch ( $reason ) {
					case 'edit':
						if ( $_CB_framework->getUi() == 1 ) {
							$where[]	=	'f.edit > 0';
						}
						break;
					case 'profile':
						$where[]	=	'f.profile > 0';
						break;
					case 'list':
						$where[]	=	"( f.profile > 0 OR f.name = 'username'" . ( in_array( $ueConfig['name_format'], array( 1, 2, 4 ) ) ? " OR f.name = 'name'" : '' ) . ')';
						break;
					case 'register':
						$where[]	=	'f.registration > 0';
						break;
					case 'adminfulllist':
					default:
						break;
				}

				if ( $tabId && ! $prefetchFields ) {
					$where[]		=	'f.tabid = ' . (int) $tabId;
				} else {
					if ( $reason != 'adminfulllist' ) {
						$where[]	=	't.enabled = 1';
					}
					if ( ( $reason != 'register' ) && ! ( ( $_CB_framework->getUi() == 2 ) && Application::MyUser()->isSuperAdmin() ) ) {
						$where[]	=	't.viewaccesslevel IN ' . $_CB_database->safeArrayOfIntegers( Application::MyUser()->getAuthorisedViewLevels() );
					}
				}
				if ( ( ( $reason == 'profile' ) || ( $reason == 'list' ) ) && ( $ueConfig['allow_email_display'] == 0 ) && ( $reason != 'adminfulllist' ) ) {
					$where[]		=	'f.type != ' . $_CB_database->Quote( 'emailaddress' );
				}
			}
			if ( ( ! $tabId ) || $prefetchFields ) {
				if ( $reason == 'register' ) {
					$ordering[]	=	't.ordering_register';
				}
				$ordering[]		=	't.position';
				$ordering[]		=	't.ordering';
			}
			$ordering[]			=	'f.ordering';

			$sql				=	'SELECT f.*';
			if ( $reason == 'register' ) {
				$sql			.=	', t.ordering_register AS tab_ordering_register, t.position AS tab_position, t.ordering AS tab_ordering';
			}
			$sql				.=	' FROM #__comprofiler_fields f';
			if ( ( ! $tabId ) || $prefetchFields ) {
				// don't get fields which are not assigned to tabs:
				$sql			.=	"\n INNER JOIN #__comprofiler_tabs AS t ON (f.tabid = t.tabid)";
			}
			$sql				.=	( $where ? "\n WHERE " . implode( ' AND ', $where ) : '' )
				.	"\n ORDER BY " . implode( ', ', $ordering );
			;
			$_CB_database->setQuery( $sql );
			$fields				=	$_CB_database->loadObjectList( null, '\CB\Database\Table\FieldTable', array() );
			if ( ! $_CB_database->getErrorNum() ) {
				for ( $i = 0, $n = count( $fields ); $i < $n; $i++ ) {
					$fields[$i]->params																=	new Registry( $fields[$i]->params );

					if ( $prefetchFields ) {
						$fieldsByName[$preIdx][strtolower($fields[$i]->name)]						=	$fields[$i];
						$preFetched[$preIdx][(int) $fields[$i]->tabid][(int) $fields[$i]->fieldid]	=	$fields[$i];
					}
				}
			}
		}
		if ( isset( $preFetched[$preIdx] ) ) {
			if ( $tabId ) {
				if (isset( $preFetched[$preIdx][(int) $tabId] ) ) {
					$fields		=	$preFetched[$preIdx][(int) $tabId];
				} else {
					$fields		=	array();
				}
			} elseif ( $fieldIdOrName ) {
				if ( is_int( $fieldIdOrName ) ) {
					$fields		=	array();
					foreach ( array_keys( $preFetched[$preIdx] ) as $k ) {
						if ( isset( $preFetched[$preIdx][$k][$fieldIdOrName] ) ) {
							$fields[]	=	$preFetched[$preIdx][$k][$fieldIdOrName];
							break;
						}
					}
				} elseif (isset( $fieldsByName[$preIdx][strtolower( $fieldIdOrName )] ) ) {
					$fields		=	array( $fieldsByName[$preIdx][strtolower( $fieldIdOrName )] );
				} else {
					$fields		=	array();
				}
			} else {
				$fields			=	array();
				foreach ( $preFetched[$preIdx] as /* $tid => */ $flds ) {
					//	$fields		=	array_merge( $fields, $flds );
					foreach ( $flds as $fl ) {
						$fields[$fl->fieldid]	=	$fl;
					}
				}
			}
		}

		// THIS is VERY experimental, and not yet part of CB API !!! :
		global $_PLUGINS;
		$_PLUGINS->loadPluginGroup( 'user' );
		$_PLUGINS->trigger( 'onAfterFieldsFetch', array( &$fields, &$user, $reason, $tabId, $fieldIdOrName, $fullAccess ) );

		return $fields;
	}

	/**
	 * Gets the fields-content of the tab $tabid
	 *
	 * @param  int        $tabid
	 * @param  UserTable  $user
	 * @param  int        $fieldsCount  [OUT] Count of fields
	 * @param  string     $output
	 * @param  string     $formatting
	 * @param  string     $reason
	 * @return string
	 */
	private function _getTabContents( $tabid, &$user, &$fieldsCount, $output = 'html', $formatting = 'table' /* 'divs' */, $reason = 'profile' )
	{
		$oFields				=	$this->_getTabFieldsDb( $tabid, $user, $reason );
		$fieldsCount			=	count( $oFields );
		return $this->_getFieldsContents( $oFields, $user, (int) $tabid, $output, $formatting, $reason );
	}

	/**
	 * Gets the fields-content of the tab $tab for editing
	 *
	 * @param  TabTable   $tab
	 * @param  UserTable  $user
	 * @param  string     $output
	 * @param  string     $formatting
	 * @param  string     $reason
	 * @param  boolean    $prefetchFields
	 * @return string
	 */
	private function _getEditTabContents( &$tab, &$user, $output = 'htmledit', $formatting = 'table', $reason = 'edit', $prefetchFields = true )
	{
		$results				=	'';

		if ( is_object( $tab ) ) {
			$tabid				=	(int) $tab->tabid;
		} else {
			$tabid				=	null;
		}
		$fields					=	$this->_getTabFieldsDb( $tabid, $user, $reason, null, $prefetchFields );
		if ( count( $fields ) > 0 ) {
			if ( $reason == 'edit' ) {
				$results		.=	$this->_writeTabDescription( $tab, $user );
			}
			$results			.=	$this->_getFieldsContents( $fields, $user, $tabid, $output, $formatting, $reason );
		}
		return $results;
	}

	/**
	 * Gets the HTML or values of the fields for search
	 *
	 * @param  FieldTable[]  $searchableFields    Fields that are searchable
	 * @param  UserTable     $userMe              User
	 * @param  StdClass      $searchVals          Values to search
	 * @param  int           $list_compare_types  IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @param  string        $output              'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string        $formatting          'tr', 'td', 'div', 'span', 'none',   'table'??
	 * @param  string        $reason              'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @return string
	 */
	public function getSearchableContents( $searchableFields, $userMe, $searchVals, $list_compare_types, $output = 'htmledit', $formatting = 'divs', $reason = 'search' )
	{
		$results				=	null;

		if ( count( $searchableFields ) > 0 ) {
			$user				=	new UserTable();
			$fields				=	$this->_getTabFieldsDb( null, $userMe, $reason, null, true );
			if ( is_array( $fields ) ) {
				foreach ( $fields as $oField ) {
					$this->_initFieldToDefault( $oField, $user, $reason );
				}
			}
			if ( is_object( $searchVals ) ) {
				foreach ( get_object_vars( $searchVals ) as $k => $v ) {
					$user->$k	=	$v;
				}
			}
			/*
						if ( $postdata !== null ) {
							$user->bindSafely( $postdata, $_CB_framework->getUi(), $reason, $user );
						}
			*/
			$results			=	$this->_getFieldsContents( $searchableFields, $user, 'listsearch', $output, $formatting, $reason, $list_compare_types );
		}
		return $results;
	}

	/**
	 * Applies (binds) the $searchVals to the $searchableFields and gets the compiled query to execute the search.
	 *
	 * @param  FieldTable[]  $searchableFields    Fields that are searchable
	 * @param  StdClass      $searchVals          Values to search
	 * @param  array         $postdata            _POST input
	 * @param  int           $list_compare_types  IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @param  string        $reason              'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @return cbSqlQueryPart
	 */
	public function applySearchableContents( &$searchableFields, &$searchVals, &$postdata, $list_compare_types, $reason = 'search' )
	{
		global $_PLUGINS;

		$searches				=	new cbSqlQueryPart();
		$searches->tag			=	'where';
		$searches->type			=	'sql:operator';
		$searches->operator		=	'AND';

		$searchVals				=	new stdClass();
		foreach ( $searchableFields as $field ) {
			$fieldSearches		=	$_PLUGINS->callField( $field->type, 'bindSearchCriteria', array( &$field, &$searchVals, &$postdata, $list_compare_types, $reason ), $field );
			if ( count( $fieldSearches ) > 0 ) {
				$searches->addChildren( $fieldSearches );
			}
		}
		return $searches;
	}

	/**
	 * Saves all fields from all the visible tabs $postdata to $user
	 *
	 * @param  UserTable  $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array      $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string     $reason    'edit' for save user edit, 'register' for save registration
	 * @return boolean               Success
	 */
	public function saveTabsContents( $user, $postdata, $reason )
	{
		global $_CB_framework, $_PLUGINS;

		$fields					=	$this->_getTabFieldsDb( null, $user, $reason, null, false );
		$result					=	true;
		foreach ( $fields as $field ) {
			if ( ( ! ( ( $field->readonly > 0 ) && $_CB_framework->getUi() == 1 ) ) || ( $reason == 'register' ) || ( $reason == 'search' ) ) {
				$_PLUGINS->callField( $field->type, 'prepareFieldDataSave', array( &$field, &$user, &$postdata, $reason ), $field );
			} else {
				$_PLUGINS->callField( $field->type, 'prepareFieldDataNotSaved', array( &$field, &$user, &$postdata, $reason ), $field );
			}
		}
		return $result;
	}

	/**
	 * Commits the tabs of the User
	 * Called only in UserTable::saveSafely()
	 *
	 * @param  UserTable  $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array      $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string     $reason    'edit' for save user edit, 'register' for save registration
	 * @return boolean               Success
	 */
	public function commitTabsContents( &$user, &$postdata, $reason )
	{
		global $_CB_framework, $_PLUGINS;

		$fields					=	$this->_getTabFieldsDb( null, $user, $reason, null, false );
		$result					=	true;
		foreach ( $fields as $field ) {
			if ( ( ! ( ( $field->readonly > 0 ) && $_CB_framework->getUi() == 1 ) ) || ( $reason == 'register' ) || ( $reason == 'search' ) ) {
				if ( ! $_PLUGINS->is_errors() ) {
					$_PLUGINS->callField( $field->type, 'commitFieldDataSave', array( &$field, &$user, &$postdata, $reason ), $field );
				}
			}
		}
		return $result;
	}

	/**
	 * Rolls back the saveTabsContents of the tabs of the User
	 * Called only in UserTable::saveSafely()
	 *
	 * @param  UserTable  $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array      $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @param  string     $reason    'edit' for save user edit, 'register' for save registration
	 * @return boolean               Success
	 */
	public function rollbackTabsContents( &$user, &$postdata, $reason )
	{
		global $_CB_framework, $_PLUGINS;

		$fields					=	$this->_getTabFieldsDb( null, $user, $reason, null, false );
		$result					=	true;
		foreach ( $fields as $field ) {
			if ( ( ! ( ( $field->readonly > 0 ) && $_CB_framework->getUi() == 1 ) ) || ( $reason == 'register' ) || ( $reason == 'search' ) ) {
				if ( $_PLUGINS->is_errors() ) {
					$_PLUGINS->callField( $field->type, 'rollbackFieldDataSave', array( &$field, &$user, &$postdata, $reason ), $field );
				}
			}
		}
		return $result;
	}

	/**
	 * Saves the plugin part of the tabs
	 *
	 * @param  UserTable  $user      RETURNED populated: touch only variables related to saving this field (also when not validating for showing re-edit)
	 * @param  array      $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @return int                   1 always
	 */
	public function savePluginTabs( &$user, &$postdata )
	{
		global $_PLUGINS;

		$oTabs					=	$this->_getTabsDb( $user, 'editsave' );

		foreach ( $oTabs AS $oTab ) {
			if ( $oTab->pluginclass != null ) {
				$this->_callTabPlugin( $oTab, $user, $oTab->pluginclass, 'saveEditTab', $oTab->pluginid, $postdata );
				if ( $_PLUGINS->is_errors() ) {
					break;
				}
			}
		}
		return 1;
	}

	/**
	 * Gets the registration plugin tabs
	 *
	 * @param  array      $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @return array
	 */
	public function getRegistrationPluginTabs( &$postdata )
	{
		$results				=	array();
		$userNull				=	null;
		$oTabs					=	$this->_getTabsDb( $userNull, 'register' );
		foreach( $oTabs AS $oTab ) {
			if ( $oTab->pluginclass != null ) {
				if ( ! isset( $results[(int) $oTab->ordering_register][$oTab->position][(int) $oTab->ordering] ) ) {
					$results[(int) $oTab->ordering_register][$oTab->position][(int) $oTab->ordering]	=	'';
				}
				$results[(int) $oTab->ordering_register][$oTab->position][(int) $oTab->ordering]		.=	$this->_callTabPlugin( $oTab, $userNull, $oTab->pluginclass, 'getDisplayRegistration', $oTab->pluginid, $postdata );
				$this->fieldJS	.=	$this->_getVarPlugin($oTab, $oTab->pluginclass, 'fieldJS', $oTab->pluginid);
			}
		}
		return $results;
	}

	/**
	 * Saves the registration plugin tabs
	 *
	 * @param  UserTable  $user      User
	 * @param  array      $postdata  Typically $_POST (but not necessarily), filtering required.
	 * @return array
	 */
	public function saveRegistrationPluginTabs( &$user, &$postdata )
	{
		$results				=	array();
		$userNull				=	null;
		$oTabs					=	$this->_getTabsDb( $userNull, 'register' );
		foreach( $oTabs AS $oTab ) {
			if( $oTab->pluginclass != null ) {
				$results[]		=	$this->_callTabPlugin($oTab, $user, $oTab->pluginclass, 'saveRegistrationTab', $oTab->pluginid, $postdata);
			}
		}
		return $results;
	}

	/**
	 * Gets the content for the fields of the tab
	 *
	 * @param  FieldTable[]  $oFields             Fields of tab
	 * @param  UserTable     $user                User
	 * @param  int           $tabid               Tab id
	 * @param  string        $output              'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string        $formatting          'tr', 'td', 'div', 'span', 'none',   'table'??
	 * @param  string        $reason              'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @param  int           $list_compare_types  IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return null|string
	 */
	private function _getFieldsContents( $oFields, $user, $tabid, $output = 'html', $formatting = 'table', $reason = 'profile', $list_compare_types = 0 )
	{
		global $_CB_OneTwoRowsStyleToggle;

		$results										=	null;
		if ( is_array( $oFields ) ) {

			if ( cbStartOfStringMatch( $output, 'html' ) ) {

				$formattingFields						=	$this->_stepDownFormatting[$formatting];
				foreach( $oFields AS $oField ) {
					$results							.=	$this->_getSingleFieldContent( $oField, $user, $output, $formattingFields, $reason, $list_compare_types );
				}

				if ( $results != null ) {
					switch ( $formatting ) {
						case 'table':
							// only displayed at Profile Edit: $return .= $this->_writeTabDescription( $tab, $user );
							return "\n\t\t\t" . '<table class="cbFieldsContentsTab cbFields table table-hover" id="cbtf_' . $tabid . '">' . $results . "\n\t\t\t</table>";
							break;

						case 'tr':
							$class 						=	'sectiontableentry' . $_CB_OneTwoRowsStyleToggle;
							$_CB_OneTwoRowsStyleToggle	=	( $_CB_OneTwoRowsStyleToggle == 1 ? 2 : 1 );
							return "\n\t\t\t\t<tr class=\"cbFieldsContentsTab " . $class . '" id="cbtf_' . $tabid . '">' . $results . "\n\t\t\t\t</tr>";

						case 'td':
							return "\n\t\t\t\t\t" . '<td class="cbFieldsContentsTab" id="cbtf_' . $tabid . '">' . $results . "\n\t\t\t\t\t</td>";

						case 'div':
						case 'divs':
							return '<div class="cbFieldsContentsTab" id="cbtf_' . $tabid . '">' . $results . '</div>';

						case 'span':
							return '<span class="cbFieldsContentsTab" id="cbtf_' . $tabid . '">' . $results . '</span>';

						case 'ul':
							return '<ul class="cbFieldsContentsList" id="cbtf_' . $tabid . '">' . $results . '</ul>';

						case 'ol':
							return '<ol class="cbFieldsContentsList" id="cbtf_' . $tabid . '">' . $results . '</ol>';

						case 'li':
							return '<li class="cbFieldsContentsList" id="cbtf_' . $tabid . '">' . $results . '</li>';

						case 'tabletrs':
						case 'none':
							return $results;

						default:
							return '*' . $results . '*';
							break;
					}
				}

			} else {

				foreach( $oFields AS $k => $oField ) {
					$results[$k]						=	$this->_getSingleFieldContent( $oField, $user, $output, $formatting, $reason );
				}

			}
		}
		return $results;
	}

	/**
	 * Initialises field value to its default value
	 *
	 * @param  FieldTable  $field
	 * @param  UserTable   $user
	 * @param  string      $reason
	 * @return void
	 */
	private function _initFieldToDefault( &$field, &$user, $reason )
	{
		global $_PLUGINS;
		$_PLUGINS->callField( $field->type, 'initFieldToDefault', array( &$field, &$user, $reason ), $field );
	}

	/**
	 * Gets the content of a single field
	 *
	 * @access protected (but left public in 2.0 for B/C with CB Progress Field plugin)
	 *
	 * @param  FieldTable  $oField              Fields of tab
	 * @param  UserTable   $user                User
	 * @param  string      $output              'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string      $formatting          'tr', 'td', 'div', 'span', 'none',   'table'??
	 * @param  string      $reason              'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @param  int         $list_compare_types  IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @return mixed
	 */
	public function _getSingleFieldContent( &$oField, &$user, $output = 'html', $formatting = 'tr', $reason = 'profile', $list_compare_types = 0 )
	{
		global $_PLUGINS;
		return $_PLUGINS->callField( $oField->type, 'getFieldRow', array( &$oField, &$user, $output, $formatting, $reason, $list_compare_types ), $oField );
	}

	/**
	 * Calls tab plugin
	 *
	 * @param  TabTable    $tab          Tab
	 * @param  UserTable   $user         User
	 * @param  string      $pluginclass  Class to call
	 * @param  string      $method       Method to call
	 * @param  int         $pluginid     Plugin id
	 * @param  array       $postdata     _POST data
	 * @return mixed|null
	 */
	protected function _callTabPlugin( &$tab, &$user, $pluginclass, $method, $pluginid = null, $postdata = null )
	{
		global $_PLUGINS;

		$results				=	null;

		if ( ! $pluginid ) {
			$pluginid			=	1;
		}

		if ( $pluginid ) {
			if ( ! $pluginclass ) {
				$pluginclass	=	'cbTabHandler';
			}

			if ( $_PLUGINS->loadPluginGroup( 'user', array( (int) $pluginid ) ) ) {
				$args			=	array( &$tab , &$user, $this->ui, &$postdata );
				$results		=	$_PLUGINS->call( $pluginid, $method, $pluginclass, $args, ( is_object( $tab ) ? $tab->params : null ) );
			}
		}

		return $results;
	}

	/**
	 * Renders fields
	 *
	 * @param  FieldTable[]  $pluginTabContent  Fields of tab
	 * @param  UserTable     $user              User
	 * @param  string        $output            'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string        $formatting        'tr', 'td', 'div', 'span', 'none',   'table'??
	 * @param  string        $reason            'profile' for user profile view, 'edit' for profile edit, 'register' for registration, 'search' for searches
	 * @param  string[]      $rowClasses        IN+OUT: row classes for fields
	 * @return string                          Rendered output
	 */
	protected function _renderFields( $pluginTabContent, $user, $output, $formatting, $reason, $rowClasses )
	{
		global $_PLUGINS, $_CB_OneTwoRowsStyleToggle;

		$rendered				=	null;
		$formattingFields		=	$this->_stepDownFormatting[$formatting];
		foreach ($pluginTabContent as $field ) {
			$saveToggle			=	$_CB_OneTwoRowsStyleToggle;
			$rendered			.=	$_PLUGINS->callField( $field->type, 'renderFieldHtml', array( &$field, &$user, $field->value, $output, $formattingFields, $reason, $rowClasses ), $field );
			if ( isset( $field->_rowNoToggle ) ) {
				$_CB_OneTwoRowsStyleToggle	=	$saveToggle;
			}
		}
		return $rendered;
	}

	/**
	 * builds a pseudo-FieldTable to return in array in getDisplayRegistration and getEditTab methods (in $output == 'htmledit' mode)
	 *
	 * @param  TabTable            $tab
	 * @param  string              $value
	 * @param  string              $title
	 * @param  string              $description
	 * @param  string              $uniqueId           Unique id system-wide ( a-z,A-Z, _ )
	 * @param  boolean             $displayOnTwoLines
	 * @param  string              $name               If the $title refers to an input then this should be the name of the input for focus and tooltips to work properly
	 * @param  boolean             $rowStyleToggle     Change row style toggle
	 * @return FieldTable
	 */
	public static function _createPseudoField( $tab, $title, $value, $description, $uniqueId, $displayOnTwoLines = false  , $name = null, $rowStyleToggle = true )
	{
		$pseudoField					=	new FieldTable();
		$pseudoField->value				=	$value;
		if ( ! $rowStyleToggle ) {
			/** @noinspection PhpUndefinedFieldInspection */
			$pseudoField->_rowNoToggle	=	true;
		}
		$pseudoField->name				=	$name;
		$pseudoField->type				=	'delimiter';
		$pseudoField->title				=	$title;
		$pseudoField->description		=	$description;
		$pseudoField->tabid				=	$tab->tabid;
		$pseudoField->fieldid			=	preg_replace( '/^W/', '_' , $uniqueId );
		$pseudoField->registration		=	( $displayOnTwoLines ? 2 : 1 );
		$pseudoField->edit				=	( $displayOnTwoLines ? 2 : 1 );
		$pseudoField->profile			=	( $displayOnTwoLines ? 2 : 1 );
		$pseudoField->params			=	new Registry();
		return $pseudoField;
	}

	/**
	 * Returns Javascript code for the plugin
	 *
	 * @param  TabTable  $tab
	 * @param  string              $pluginClass
	 * @param  string              $variable      'fieldJs'
	 * @param  int                 $pluginId
	 * @return string
	 */
	private function _getVarPlugin( /** @noinspection PhpUnusedParameterInspection */ $tab, $pluginClass, $variable, $pluginId = null )
	{
		global $_PLUGINS;
		return $_PLUGINS->getVar( $pluginId, $pluginClass, $variable );
	}

	/**
	 * Loads plugin corresponding to tab from database and calls a method of it.
	 *
	 * @param  UserTable  $user          CB User to display
	 * @param  array      $postdata      $_POST data
	 * @param  string     $pluginName    Name of class to search for and to call
	 * @param  string     $tabClassName  Name of tab class to call the $method of
	 * @param  string     $method        Name of method to call
	 * @return string|null               Returned result of call (null if call not performed)
	 */
	public function tabClassPluginTabs( $user, $postdata, $pluginName, $tabClassName, $method )
	{
		global $_CB_framework, $_CB_database, $_PLUGINS;

		$result = null;

		if ($pluginName) {
			$query	=	"SELECT * FROM #__comprofiler_plugin p"
				.	"\n WHERE p.published = 1 AND p.element = " . $_CB_database->Quote( strtolower( $pluginName ) );
			$_CB_database->setQuery( $query );
			$pluginsList				=	$_CB_database->loadObjectList( null, '\CB\Database\Table\PluginTable', array( &$_CB_database ) );
			if ( count( $pluginsList ) == 1 ) {
				$plugin					=	$pluginsList[0];
				if ( $_PLUGINS->loadPluginGroup( $plugin->type, array( (int) $plugin->id ) ) ) {
					$pluginComponentFile	=	$_CB_framework->getCfg( 'absolute_path' ) . '/' . $_PLUGINS->getPluginRelPath( $plugin ) . '/component.' . $plugin->element . '.php';
					if ( file_exists( $pluginComponentFile ) ) {
						/** @noinspection PhpIncludeInspection */
						include_once( $pluginComponentFile );
					}
					if ( class_exists( $tabClassName ) ) {
						$null			=	null;
						$result			=	$this->_callTabPlugin( $null, $user, $tabClassName, $method, $plugin->id, $postdata );
					}
				}
			}
		} else {
			$query	=	"SELECT * FROM #__comprofiler_tabs t"
				.	"\n WHERE t.enabled=1 AND t.pluginclass is not null AND LOWER(t.pluginclass) = "
				.	$_CB_database->Quote( strtolower( $tabClassName ) );
			// no ACL check here on purpose
			$_CB_database->setQuery( $query );
			$oTabs						=	$_CB_database->loadObjectList( null, '\CB\Database\Table\TabTable', array() );
			if ( count( $oTabs ) == 1 ) {
				$oTab					=	$oTabs[0];
				if( $oTab->pluginid && ( $oTab->pluginclass != null ) ) {
					if ( $_PLUGINS->loadPluginGroup( 'user', array( (int) $oTab->pluginid ) ) ) {
						// plugin exists and is published:
						$result			=	$this->_callTabPlugin( $oTab, $user, $oTab->pluginclass, $method, $oTab->pluginid, $postdata );
					}
				}
			}
		}
		return $result;
	}

	/**
	 * @param  int|string  $fieldIdOrName
	 * @param  UserTable   $user          CB User to display
	 * @param  array       $postdata      $_POST data
	 * @param  string      $reason        Reason of field call
	 * @return string|boolean             Result string or FALSE
	 */
	public function fieldCall( $fieldIdOrName, $user, $postdata, $reason )
	{
		global $_PLUGINS;

		$fields							=	$this->_getTabFieldsDb( null, $user, $reason, $fieldIdOrName, false );
		if ( is_array( $fields ) && ( count( $fields ) == 1 ) ) {
			foreach ( $fields as $field ) {
				$_PLUGINS->loadPluginGroup( 'user', array( (int) $field->pluginid ) );
				$fieldRes				=	$_PLUGINS->callField( $field->type, 'fieldClass', array( &$field, &$user, &$postdata, $reason ), $field );
				return $fieldRes;
			}
		}
		return false;
	}
}
