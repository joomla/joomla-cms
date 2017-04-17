<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Database\Table\OrderedTable;
use CBLib\Language\CBTxt;
use CB\Database\Table\PluginTable;
use CB\Database\Table\TabTable;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class HTML_cbblogsTab
 * Template for CB Blogs Tab view
 */
class HTML_cbblogsTab
{
	/**
	 * Renders the Blogs tab
	 *
	 * @param  OrderedTable[]  $rows       Blogs to render
	 * @param  cbPageNav       $pageNav    Pagination
	 * @param  boolean         $searching  Currently searching
	 * @param  string[]        $input      HTML of input elements
	 * @param  UserTable       $viewer     Viewing user
	 * @param  UserTable       $user       Viewed user
	 * @param  stdClass        $model      The model reference
	 * @param  TabTable        $tab        Current Tab
	 * @param  PluginTable     $plugin     Current Plugin
	 * @return string                      HTML
	 */
	static function showBlogTab( $rows, $pageNav, $searching, $input, $viewer, $user, /** @noinspection PhpUnusedParameterInspection */ $model, $tab, $plugin )
	{
		global $_CB_framework;

		$blogLimit					=	(int) $plugin->params->get( 'blog_limit', null );
		$tabPaging					=	$tab->params->get( 'tab_paging', 1 );
		$canSearch					=	( $tab->params->get( 'tab_search', 1 ) && ( $searching || $pageNav->total ) );
		$canCreate					=	false;
		$profileOwner				=	( $viewer->get( 'id' ) == $user->get( 'id' ) );
		$cbModerator				=	Application::User( (int) $viewer->get( 'id' ) )->isGlobalModerator();
		$canPublish					=	( $cbModerator || ( $profileOwner && ( ! $plugin->params->get( 'blog_approval', 0 ) ) ) );

		if ( $profileOwner ) {
			if ( $cbModerator ) {
				$canCreate			=	true;
			} elseif ( $user->get( 'id' ) && Application::User( (int) $viewer->get( 'id' ) )->canViewAccessLevel( (int) $plugin->params->get( 'blog_create_access', 2 ) ) ) {
				if ( ( ! $blogLimit ) || ( $blogLimit && ( $pageNav->total < $blogLimit ) ) ) {
					$canCreate		=	true;
				}
			}
		}

		$return						=	'<div class="blogsTab">'
									.		'<form action="' . $_CB_framework->userProfileUrl( $user->get( 'id' ), true, $tab->tabid ) . '" method="post" name="blogForm" id="blogForm" class="blogForm">';

		if ( $canCreate || $canSearch ) {
			$return					.=			'<div class="blogsHeader row" style="margin-bottom: 10px;">';

			if ( $canCreate ) {
				$return				.=				'<div class="' . ( ! $canSearch ? 'col-sm-12' : 'col-sm-8' ) . ' text-left">'
									.					'<button type="button" onclick="location.href=\'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'blogs', 'func' => 'new' ) ) . '\';" class="blogsButton blogsButtonNew btn btn-success"><span class="fa fa-plus-circle"></span> ' . CBTxt::T( 'New Blog' ) . '</button>'
									.				'</div>';
			}

			if ( $canSearch ) {
				$return				.=				'<div class="' . ( ! $canCreate ? 'col-sm-offset-8 ' : null ) . 'col-sm-4 text-right">'
									.					'<div class="input-group">'
									.						'<span class="input-group-addon"><span class="fa fa-search"></span></span>'
									.						$input['search']
									.					'</div>'
									.				'</div>';
			}

			$return					.=			'</div>';
		}

		$menuAccess					=	( $cbModerator || $profileOwner || $canPublish );

		$return						.=			'<table class="blogsContainer table table-hover table-responsive">'
									.				'<thead>'
									.					'<tr>'
									.						'<th style="width: 50%;" class="text-left">' . CBTxt::T( 'Title' ) . '</th>'
									.						'<th style="width: 25%;" class="text-left hidden-xs">' . CBTxt::T( 'Category' ) . '</th>'
									.						'<th style="width: 24%;" class="text-left hidden-xs">' . CBTxt::T( 'Created' ) . '</th>'
									.						( $menuAccess ? '<th style="width: 1%;" class="text-right">&nbsp;</th>' : null )
									.					'</tr>'
									.				'</thead>'
									.				'<tbody>';

		if ( $rows ) foreach ( $rows as $row ) {
			$return					.=					'<tr>'
									.						'<td style="width: 50%;" class="text-left">' . ( $row->get( 'published' ) ? '<a href="' . cbblogsModel::getUrl( $row, true, 'article' ) . '">' . $row->get( 'title' ) . '</a>' : $row->get( 'title' ) ) . '</td>'
									.						'<td style="width: 25%;" class="text-left hidden-xs">' . ( $row->get( 'category_published' ) ? '<a href="' . cbblogsModel::getUrl( $row, true, 'category' ) . '">' . $row->get( 'category' ) . '</a>' : $row->get( 'category' ) ) . '</td>'
									.						'<td style="width: 24%;" class="text-left hidden-xs">' . cbFormatDate( $row->get( 'created' ) ) . '</td>';

			if ( $menuAccess ) {
				$menuItems			=	'<ul class="blogsMenuItems dropdown-menu" style="display: block; position: relative; margin: 0;">';

				if ( $cbModerator || $profileOwner ) {
					$menuItems		.=		'<li class="blogsMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'blogs', 'func' => 'edit', 'id' => (int) $row->get( 'id' ) ) ) . '"><span class="fa fa-edit"></span> ' . CBTxt::T( 'Edit' ) . '</a></li>';
				}

				if ( $canPublish ) {
					if ( $row->get( 'published' ) ) {
						$menuItems	.=		'<li class="blogsMenuItem"><a href="javascript: void(0);" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to unpublish this Blog?' ) ) . '\' ) ) { location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'blogs', 'func' => 'unpublish', 'id' => (int) $row->get( 'id' ) ) ) . '\'; }"><span class="fa fa-times-circle"></span> ' . CBTxt::T( 'Unpublish' ) . '</a></li>';
					} else {
						$menuItems	.=		'<li class="blogsMenuItem"><a href="' . $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'blogs', 'func' => 'publish', 'id' => (int) $row->get( 'id' ) ) ) . '"><span class="fa fa-check"></span> ' . CBTxt::T( 'Publish' ) . '</a></li>';
					}
				}

				if ( $cbModerator || $profileOwner ) {
					$menuItems		.=		'<li class="blogsMenuItem"><a href="javascript: void(0);" onclick="if ( confirm( \'' . addslashes( CBTxt::T( 'Are you sure you want to delete this Blog?' ) ) . '\' ) ) { location.href = \'' . $_CB_framework->pluginClassUrl( $plugin->element, false, array( 'action' => 'blogs', 'func' => 'delete', 'id' => (int) $row->get( 'id' ) ) ) . '\'; }"><span class="fa fa-trash-o"></span> ' . CBTxt::T( 'Delete' ) . '</a></li>';
				}

				$menuItems			.=	'</ul>';

				$menuAttr			=	cbTooltip( 1, $menuItems, null, 'auto', null, null, null, 'class="btn btn-default btn-xs" data-cbtooltip-menu="true" data-cbtooltip-classes="qtip-nostyle"' );

				$return				.=						'<td style="width: 1%;" class="text-right">'
									.							'<div class="blogsMenu btn-group">'
									.								'<button type="button"' . $menuAttr . '><span class="fa fa-cog"></span> <span class="fa fa-caret-down"></span></button>'
									.							'</div>'
									.						'</td>';
			}

			$return					.=					'</tr>';
		} else {
			$return					.=					'<tr>'
									.						'<td colspan="' . ( $menuAccess ? 4 : 3 ) . '" class="text-left">';

			if ( $searching ) {
				$return				.=							CBTxt::T( 'No blog search results found.' );
			} else {
				if ( $viewer->id == $user->id ) {
					$return			.=							CBTxt::T( 'You have no blogs.' );
				} else {
					$return			.=							CBTxt::T( 'This user has no blogs.' );
				}
			}

			$return					.=						'</td>'
									.					'</tr>';
		}

		$return						.=				'</tbody>';

		if ( $tabPaging && ( $pageNav->total > $pageNav->limit ) ) {
			$return					.=				'<tfoot>'
									.					'<tr>'
									.						'<td colspan="' . ( $menuAccess ? 4 : 3 ) . '" class="text-center">'
									.							$pageNav->getListLinks()
									.						'</td>'
									.					'</tr>'
									.				'</tfoot>';
		}

		$return						.=			'</table>'
									.			$pageNav->getLimitBox( false )
									.		'</form>'
									.	'</div>';

		return $return;
	}
}
