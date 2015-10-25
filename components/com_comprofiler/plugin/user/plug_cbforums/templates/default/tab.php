<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;
use CB\Database\Table\TabTable;
use CB\Database\Table\PluginTable;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class HTML_cbforumsTab
 * CB Forum Tab Template
 */
class HTML_cbforumsTab
{
	/**
	 * Shows Forum Tab
	 *
	 * @param  UserTable    $viewer     Viewing User
	 * @param  UserTable    $user       Viewed at User
	 * @param  TabTable     $tab        Current Tab
	 * @param  PluginTable  $plugin     Current Plugin
	 * @return string
	 */
	static public function showTab( $viewer, $user, $tab, $plugin )
	{
		global $_CB_framework;

		$tabs					=	new cbTabs( 1, 1 );

		$favorites				=	$tab->params->get( 'tab_favs_display', 1 );
		$subscriptions			=	$tab->params->get( 'tab_subs_display', 1 );
		$tabbed					=	( ( $user->id == $_CB_framework->myId() ) && ( $favorites || $subscriptions ) ? true : false );

		$return					=	null;

		if ( $tabbed ) {
			$return				.=			$tabs->startPane( 'cbForumsTabs' )
								.				$tabs->startTab( null, htmlspecialchars( CBTxt::T( 'Posts' ) ), 'cbForumsTabsPosts' );
		}

		$return					.=					cbforumsModel::getPosts( $viewer, $user, $tab, $plugin );

		if ( $tabbed ) {
			$return				.=				$tabs->endTab();

			if ( $favorites ) {
				$return			.=				$tabs->startTab( null, htmlspecialchars( CBTxt::T( 'Favorites' ) ), 'cbForumsTabsFavorites' )
								.					cbforumsModel::getFavorites( $viewer, $user, $tab, $plugin )
								.				$tabs->endTab();
			}

			if ( $subscriptions ) {
				$return			.=				$tabs->startTab( null, htmlspecialchars( CBTxt::T( 'Subscriptions' ) ), 'cbForumsTabsSubscriptions' )
								.					'<div class="tab-content">'
								.						cbforumsModel::getCategorySubscriptions( $viewer, $user, $tab, $plugin )
								.						cbforumsModel::getSubscriptions( $viewer, $user, $tab, $plugin )
								.					'</div>'
								.				$tabs->endTab();
			}

			$return				.=			$tabs->endPane();
		}

		return $return;
	}
}
