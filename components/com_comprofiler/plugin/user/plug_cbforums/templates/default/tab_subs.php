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
 * Class HTML_cbforumsTabSubs
 * CB Forum Subscriptions Tab Template
 */
class HTML_cbforumsTabSubs
{
	/**
	 * Shows Forum Subscriptions
	 *
	 * @param  stdClass[]   $rows       Rows to show
	 * @param  cbPageNav    $pageNav    Page Navigation
	 * @param  boolean      $searching  Are we searching currently ?
	 * @param  string[]     $input      Inputs to show
	 * @param  UserTable    $viewer     Viewing User
	 * @param  UserTable    $user       Viewed at User
	 * @param  TabTable     $tab        Current Tab
	 * @param  PluginTable  $plugin     Current Plugin
	 * @return string
	 */
	static public function showSubscriptions( $rows, $pageNav, $searching, $input, $viewer, $user, $tab, /** @noinspection PhpUnusedParameterInspection */ $plugin )
	{
		global $_CB_framework;

		$tabPaging			=	$tab->params->get( 'tab_subs_paging', 1 );
		$canSearch			=	( $tab->params->get( 'tab_subs_search', 1 ) && ( $searching || $pageNav->total ) );
		$unsuballUrl		=	"javascript: if ( confirm( '" . addslashes( CBTxt::T( 'Are you sure you want to delete all Subscriptions?' ) ) . "' ) ) { location.href = '" . addslashes( $_CB_framework->userProfileUrl( $user->id, false, $tab->tabid ) . '&forums_unsub=all' ) . "'; }";

		$return				=	'<div class="forumsSubsTab">'
							.		'<form action="' . $_CB_framework->userProfileUrl( $user->id, true, $tab->tabid ) . '" method="post" name="forumSubsForm" id="forumSubsForm" class="forumSubsForm">';

		if ( $canSearch ) {
			$return			.=			'<div class="forumsHeader row" style="margin-bottom: 10px;">'
							.				'<div class="col-sm-offset-8 col-sm-4 text-right">'
							.					'<div class="input-group">'
							.						'<span class="input-group-addon"><span class="fa fa-search"></span></span>'
							.						$input['search']
							.					'</div>'
							.				'</div>'
							.			'</div>';
		}

		$return				.=			'<table class="forumsContainer table table-hover table-responsive">'
							.				'<thead>'
							.					'<tr>'
							.						'<th style="width: 50%;" class="text-left">' . CBTxt::T( 'Subject' ) . '</th>'
							.						'<th style="width: 25%;" class="text-left hidden-xs">' . CBTxt::T( 'Category' ) . '</th>'
							.						'<th style="width: 24%;" class="text-left hidden-xs">' . CBTxt::T( 'Date' ) . '</th>'
							.						'<th style="width: 1%;" class="text-right">' . ( $rows ? '<a href="javascript: void(0);" onclick="' . $unsuballUrl . '" title="' . htmlspecialchars( CBTxt::T( 'Delete All' ) ) . '"><span class="fa fa-trash-o"></span></a>' : '&nbsp;' ) . '</th>'
							.					'</tr>'
							.				'</thead>'
							.				'<tbody>';

		if ( $rows ) foreach ( $rows as $row ) {
			$unsubUrl		=	"javascript: if ( confirm( '" . addslashes( CBTxt::T( 'Are you sure you want to delete this Subscription?' ) ) . "' ) ) { location.href = '" . addslashes( $_CB_framework->userProfileUrl( $user->id, false, $tab->tabid ) . '&forums_unsub=' . $row->id ) . "'; }";

			$return			.=					'<tr>'
							.						'<td style="width: 50%;" class="text-left"><a href="' . ( isset( $row->url ) ? $row->url : cbforumsModel::getForumURL( $row->category_id, $row->id ) ) . '">' . cbforumsClass::cleanPost( $row->subject ) . '</a></td>'
							.						'<td style="width: 25%;" class="text-left hidden-xs"><a href="' . ( isset( $row->category_url ) ? $row->category_url : cbforumsModel::getForumURL( $row->category_id ) ) . '">' . cbforumsClass::cleanPost( $row->category_name ) . '</a></td>'
							.						'<td style="width: 24%;" class="text-left hidden-xs">' . cbFormatDate( date( 'Y-m-d H:i:s', $row->date ) ) . '</td>'
							.						'<td style="width: 1%;" class="text-right"><a href="javascript: void(0);" onclick="' . $unsubUrl . '" title="' . htmlspecialchars( CBTxt::T( 'Delete' ) ) . '"><span class="fa fa-trash-o"></span></a></td>'
							.					'</tr>';
		} else {
			$return			.=					'<tr>'
							.						'<td colspan="4" class="text-left">';

			if ( $searching ) {
				$return		.=							CBTxt::T( 'No subscription search results found.' );
			} else {
				if ( $viewer->id == $user->id ) {
					$return	.=							CBTxt::T( 'You have no subscriptions.' );
				} else {
					$return	.=							CBTxt::T( 'This user has no subscriptions.' );
				}
			}

			$return			.=						'</td>'
							.					'</tr>';
		}

		$return				.=				'</tbody>';

		if ( $tabPaging && ( $pageNav->total > $pageNav->limit ) ) {
			$return			.=				'<tfoot>'
							.					'<tr>'
							.						'<td colspan="4" class="text-center">'
							.							$pageNav->getListLinks()
							.						'</td>'
							.					'</tr>'
							.				'</tfoot>';
		}

		$return				.=			'</table>'
							.			$pageNav->getLimitBox( false )
							.		'</form>'
							.	'</div>';

		return $return;
	}
}
