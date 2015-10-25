<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Database\Table\Table;
use CBLib\Language\CBTxt;
use CB\Database\Table\PluginTable;
use CB\Database\Table\TabTable;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class HTML_cbarticlesTab
 * Template for CB Articles
 */
class HTML_cbarticlesTab
{
	/**
	 * Renders the Articles tab
	 *
	 * @param  Table[]      $rows       Articles to render
	 * @param  cbPageNav    $pageNav    Pagination
	 * @param  boolean      $searching  Currently searching
	 * @param  string[]     $input      HTML of input elements
	 * @param  UserTable    $viewer     Viewing user
	 * @param  UserTable    $user       Viewed user
	 * @param  stdClass     $model      The model reference
	 * @param  TabTable     $tab        Current Tab
	 * @param  PluginTable  $plugin     Current Plugin
	 * @return string                   HTML
	 */
	static public function showArticleTab( $rows, $pageNav, $searching, $input, $viewer, $user, /** @noinspection PhpUnusedParameterInspection */ $model, $tab, /** @noinspection PhpUnusedParameterInspection */ $plugin )
	{
		global $_CB_framework;

		$tabPaging				=	$tab->params->get( 'tab_paging', 1 );
		$canSearch				=	( $tab->params->get( 'tab_search', 1 ) && ( $searching || $pageNav->total ) );

		$return					=	'<div class="articlesTab">'
								.		'<form action="' . $_CB_framework->userProfileUrl( $user->id, true, $tab->tabid ) . '" method="post" name="articleForm" id="articleForm" class="articleForm">';

		if ( $canSearch ) {
			$return				.=			'<div class="articlesHeader row" style="margin-bottom: 10px;">'
								.				'<div class="col-sm-offset-8 col-sm-4 text-right">'
								.					'<div class="input-group">'
								.						'<span class="input-group-addon"><span class="fa fa-search"></span></span>'
								.						$input['search']
								.					'</div>'
								.				'</div>'
								.			'</div>';
		}

		$return					.=			'<table class="articlesContainer table table-hover table-responsive">'
								.				'<thead>'
								.					'<tr>'
								.						'<th style="width: 50%;" class="text-left">' . CBTxt::T( 'Article' ) . '</th>'
								.						'<th style="width: 25%;" class="text-left hidden-xs">' . CBTxt::T( 'Category' ) . '</th>'
								.						'<th style="width: 25%;" class="text-left hidden-xs">' . CBTxt::T( 'Created' ) . '</th>'
								.					'</tr>'
								.				'</thead>'
								.				'<tbody>';

		if ( $rows ) foreach ( $rows as $row ) {
			$return				.=					'<tr>'
								.						'<td style="width: 50%;" class="text-left"><a href="' . cbarticlesModel::getUrl( $row, true, 'article' ) . '">' . $row->get( 'title' ) . '</a></td>'
								.						'<td style="width: 25%;" class="text-left hidden-xs">' . ( $row->get( 'category' ) ? '<a href="' . cbarticlesModel::getUrl( $row, true, 'category' ) . '">' . $row->get( 'category_title' ) . '</a>' : CBTxt::T( 'None' ) ) . '</td>'
								.						'<td style="width: 25%;" class="text-left hidden-xs">' . cbFormatDate( $row->get( 'created' ) ) . '</td>'
								.					'</tr>';
		} else {
			$return				.=					'<tr>'
								.						'<td colspan="3" class="text-left">';

			if ( $searching ) {
				$return			.=							CBTxt::T( 'No article search results found.' );
			} else {
				if ( $viewer->id == $user->id ) {
					$return		.=							CBTxt::T( 'You have no articles.' );
				} else {
					$return		.=							CBTxt::T( 'This user has no articles.' );
				}
			}

			$return				.=						'</td>'
								.					'</tr>';
		}

		$return					.=				'</tbody>';

		if ( $tabPaging && ( $pageNav->total > $pageNav->limit ) ) {
			$return				.=				'<tfoot>'
								.					'<tr>'
								.						'<td colspan="3" class="text-center">'
								.							$pageNav->getListLinks()
								.						'</td>'
								.					'</tr>'
								.				'</tfoot>';
		}

		$return					.=			'</table>'
								.			$pageNav->getLimitBox( false )
								.		'</form>'
								.	'</div>';

		return $return;
	}
}
