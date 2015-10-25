<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Database\Table\OrderedTable;
use CBLib\Language\CBTxt;
use CB\Database\Table\PluginTable;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class HTML_cbblogsBlog
 * Template for CB Blogs Show view
 */
class HTML_cbblogsBlog
{
	/**
	 * @param  OrderedTable  $row
	 * @param  UserTable     $user
	 * @param  stdClass      $model
	 * @param  PluginTable   $plugin
	 */
	static function showBlog( $row, $user, /** @noinspection PhpUnusedParameterInspection */ $model, $plugin )
	{
		global $_CB_framework;

		$_CB_framework->setPageTitle( $row->get( 'title' ) );
		$_CB_framework->appendPathWay( htmlspecialchars( CBTxt::T( 'Blogs' ) ), $_CB_framework->userProfileUrl( $row->get( 'user', $user->get( 'id' ) ), true, 'cbblogsTab' ) );
		$_CB_framework->appendPathWay( htmlspecialchars( $row->get( 'title' ) ), $_CB_framework->pluginClassUrl( $plugin->element, true, array( 'action' => 'blogs', 'func' => 'show', 'id' => (int) $row->get( 'id' ) ) ) );

		$cbUser			=&	CBuser::getInstance( (int) $row->get( 'user' ), false );

		$return			=	'<div class="blowShow">'
						.		'<div class="blogsTitle page-header"><h3>' . $row->get( 'title' ) . ' <small>' . CBTxt::T( 'WRITTEN_BY_BLOG_AUTHOR', 'Written by [blog_author]', array( '[blog_author]' => $cbUser->getField( 'formatname', null, 'html', 'none', 'list', 0, true ) ) ) . '</small></h3></div>'
						.		'<div class="blogsHeader well well-sm">'
						.			CBTxt::T( 'CATEGORY_CATEGORY', 'Category: [category]', array( '[category]' => $row->get( 'category' ) ) )
						.			' &nbsp;/&nbsp; ' . CBTxt::T( 'CREATED_CREATED', 'Created: [created]', array( '[created]' => cbFormatDate( $row->get( 'created' ) ) ) )
						.			( $row->get( 'modified' ) && ( $row->get( 'modified' ) != '0000-00-00 00:00:00' ) ? ' &nbsp;/&nbsp; ' . CBTxt::T( 'MODIFIED_MODIFIED', 'Modified: [modified]', array( '[modified]' => cbFormatDate( $row->get( 'modified' ) ) ) ) : null )
						.		'</div>'
						.		'<div class="blogsText">' . $row->get( 'blog_intro' ) . $row->get( 'blog_full' ) . '</div>'
						.	'</div>';

		echo $return;
	}
}
