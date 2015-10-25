<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Input\Get;
use CBLib\Registry\GetterInterface;
use CBLib\Language\CBTxt;
use CBLib\Application\Application;
use CB\Database\Table\PluginTable;
use CB\Database\Table\TabTable;
use CB\Database\Table\UserTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

/**
 * Class CBplug_cbblogs
 * CB Components-type class for CB Blogs
 */
class CBplug_cbblogs extends cbPluginHandler
{
	/**
	 * @param  TabTable   $tab       Current tab
	 * @param  UserTable  $user      Current user
	 * @param  int        $ui        1 front, 2 admin UI
	 * @param  array      $postdata  Raw unfiltred POST data
	 * @return string                HTML
	 */
	public function getCBpluginComponent( /** @noinspection PhpUnusedParameterInspection */ $tab, $user, $ui, $postdata )
	{
		global $_CB_framework;

		outputCbJs( 1 );
		outputCbTemplate( 1 );

		$plugin					=	cbblogsClass::getPlugin();
		$model					=	cbblogsClass::getModel();
		$action					=	$this->input( 'action', null, GetterInterface::STRING );
		$function				=	$this->input( 'func', null, GetterInterface::STRING );
		$id						=	$this->input( 'id', null, GetterInterface::INT );
		$user					=	CBuser::getUserDataInstance( $_CB_framework->myId() );

		$tab					=	new TabTable();

		$tab->load( array( 'pluginid' => (int) $plugin->id ) );

		$profileUrl				=	$_CB_framework->userProfileUrl( $user->get( 'id' ), false, 'cbblogsTab' );

		if ( ! ( $tab->enabled && Application::MyUser()->canViewAccessLevel( $tab->viewaccesslevel ) ) ) {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}

		ob_start();
		switch ( $action ) {
			case 'blogs':
				switch ( $function ) {
					case 'new':
						$this->showBlogEdit( null, $user, $model, $plugin );
						break;
					case 'edit':
						$this->showBlogEdit( $id, $user, $model, $plugin );
						break;
					case 'save':
						cbSpoofCheck( 'plugin' );
						$this->saveBlogEdit( $id, $user, $model, $plugin );
						break;
					case 'publish':
						$this->stateBlog( 1, $id, $user, $model, $plugin );
						break;
					case 'unpublish':
						$this->stateBlog( 0, $id, $user, $model, $plugin );
						break;
					case 'delete':
						$this->deleteBlog( $id, $user, $model, $plugin );
						break;
					case 'show':
					default:
						if ( $model->type != 2 ) {
							cbRedirect( cbblogsModel::getUrl( (int) $id, false ) );
						} else {
							$this->showBlog( $id, $user, $model, $plugin );
						}
						break;
				}
				break;
			default:
				cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
				break;
		}
		$html		=	ob_get_contents();
		ob_end_clean();

		$class		=	$plugin->params->get( 'general_class', null );

		$return		=	'<div id="cbBlogs" class="cbBlogs' . ( $class ? ' ' . htmlspecialchars( $class ) : null ) . '">'
					.		'<div id="cbBlogsInner" class="cbBlogsInner">'
					.			$html
					.		'</div>'
					.	'</div>';

		echo $return;
	}

	/**
	 * @param  int          $id
	 * @param  UserTable    $user
	 * @param  stdClass     $model
	 * @param  PluginTable  $plugin
	 */
	public function showBlog( $id, $user, $model, $plugin )
	{
		global $_CB_framework;

		$row					=	new cbblogsBlogTable();

		$row->load( (int) $id );

		$profileUrl				=	$_CB_framework->userProfileUrl( $row->get( 'user', $user->get( 'id' ) ), false, 'cbblogsTab' );

		if ( ! $row->get( 'id' ) ) {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}

		if ( ! ( ( $row->get( 'user' ) == $user->get( 'id' ) )
				|| ( Application::MyUser()->canViewAccessLevel( $row->get( 'access' ) ) && $row->get( 'published' ) )
			    || Application::User( (int) $user->get( 'id' ) )->isGlobalModerator()
			   )
		)
		{
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}

		cbblogsClass::getTemplate( 'blog_show' );

		HTML_cbblogsBlog::showBlog( $row, $user, $model, $plugin );
	}

	/**
	 * @param  null|int     $id
	 * @param  UserTable    $user
	 * @param  stdClass     $model
	 * @param  PluginTable  $plugin
	 * @param  null|string  $message
	 * @param  null|string  $messageType
	 */
	public function showBlogEdit( $id, $user, $model, $plugin, $message = null, $messageType = 'error' )
	{
		global $_CB_framework;

		$blogLimit						=	(int) $plugin->params->get( 'blog_limit', null );
		$blogMode						=	$plugin->params->get( 'blog_mode', 1 );
		$cbModerator					=	Application::User( (int) $user->get( 'id' ) )->isGlobalModerator();

		$row							=	new cbblogsBlogTable();

		$row->load( (int) $id );

		$canAccess						=	false;

		if ( ! $row->get( 'id' ) ) {
			if ( $cbModerator ) {
				$canAccess				=	true;
			} elseif ( $user->get( 'id' ) && Application::MyUser()->canViewAccessLevel( $plugin->params->get( 'blog_create_access', 2 ) ) ) {
				if ( ( ! $blogLimit ) || ( $blogLimit && ( cbblogsModel::getBlogsTotal( null, $user, $user, $plugin ) < $blogLimit ) ) ) {
					$canAccess			=	true;
				}
			}
		} elseif ( $cbModerator || ( $row->get( 'user' ) == $user->get( 'id' ) ) ) {
			$canAccess					=	true;
		}

		$profileUrl						=	$_CB_framework->userProfileUrl( $row->get( 'user', $user->get( 'id' ) ), false, 'cbblogsTab' );

		if ( $canAccess ) {
			cbblogsClass::getTemplate( 'blog_edit' );

			$input						=	array();

			$publishedTooltip			=	cbTooltip( $_CB_framework->getUi(), CBTxt::T( 'Select publish status of the blog. Unpublished blogs will not be visible to the public.' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

			$input['published']			=	moscomprofilerHTML::yesnoSelectList( 'published', 'class="form-control"' . ( $publishedTooltip ? ' ' . $publishedTooltip : null ), (int) $this->input( 'post/published', $row->get( 'published', ( $cbModerator || ( ! $plugin->params->get( 'blog_approval', 0 ) ) ? 1 : 0 ) ), GetterInterface::INT ) );

			$categoryTooltip			=	cbTooltip( $_CB_framework->getUi(), CBTxt::T( 'Select blog category. Select the category that best describes your blog.' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

			switch ( (int) $plugin->params->get( 'blog_model', 2 ) ) {
				case 2;
					$categoryDefault	=	$plugin->params->get( 'blog_int_category_default', 'General' );
					break;
				case 7;
					$categoryDefault	=	$plugin->params->get( 'blog_j_category_default', null );
					break;
				case 6;
					$categoryDefault	=	$plugin->params->get( 'blog_k2_category_default', null );
					break;
				default;
					$categoryDefault	=	null;
					break;
			}

			$listCategory				=	cbblogsModel::getCategoriesList();
			$input['category']			=	moscomprofilerHTML::selectList( $listCategory, 'category', 'class="form-control"' . ( $categoryTooltip ? ' ' . $categoryTooltip : null ), 'value', 'text', $this->input( 'post/category', $row->get( 'category', $categoryDefault ), GetterInterface::STRING ), 1, false, false );

			$accessTooltip				=	cbTooltip( $_CB_framework->getUi(), CBTxt::T( 'Select access to blog; all groups above that level will also have access to the blog.' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

			$listAccess					=	Application::CmsPermissions()->getAllViewAccessLevels( true, Application::MyUser() );
			$input['access']			=	moscomprofilerHTML::selectList( $listAccess, 'access', 'class="form-control"' . ( $accessTooltip ? ' ' . $accessTooltip : null ), 'value', 'text', (int) $this->input( 'post/access', $row->get( 'access', $plugin->params->get( 'blog_access_default', 1 ) ), GetterInterface::INT ), 1, false, false );

			$titleTooltip				=	cbTooltip( $_CB_framework->getUi(), CBTxt::T( 'Input blog title. This is the title that will distinguish this blog from others. Suggested to input something unique and intuitive.' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

			$input['title']				=	'<input type="text" id="title" name="title" value="' . htmlspecialchars( $this->input( 'post/title', $row->get( 'title' ), GetterInterface::STRING ) ) . '" class="required form-control" size="30"' . ( $titleTooltip ? ' ' . $titleTooltip : null ) . ' />';

			if ( in_array( $blogMode, array( 1, 2 ) ) ) {
				$blogIntro				=	$_CB_framework->displayCmsEditor( 'blog_intro', $this->input( 'post/blog_intro', $row->get( 'blog_intro' ), GetterInterface::HTML ), 400, 200, 40, 7 );

				$input['blog_intro']	=	cbTooltip( $_CB_framework->getUi(), CBTxt::T( 'Input HTML supported blog intro contents. Suggested to use minimal but well formatting for easy readability.' ), null, null, null, $blogIntro, null, 'style="display:block;"' );
			}

			if ( in_array( $blogMode, array( 1, 3 ) ) ) {
				$blogFull				=	$_CB_framework->displayCmsEditor( 'blog_full', $this->input( 'post/blog_full', $row->get( 'blog_full' ), GetterInterface::HTML ), 400, 200, 40, 7 );

				$input['blog_full']		=	cbTooltip( $_CB_framework->getUi(), CBTxt::T( 'Input HTML supported blog contents. Suggested to use minimal but well formatting for easy readability.' ), null, null, null, $blogFull, null, 'style="display:block;"' );
			}

			$userTooltip				=	cbTooltip( $_CB_framework->getUi(), CBTxt::T( 'Input owner of blog as single integer user_id.' ), null, null, null, null, null, 'data-hascbtooltip="true"' );

			$input['user']				=	'<input type="text" id="user" name="user" value="' . (int) ( $cbModerator ? $this->input( 'post/user', $row->get( 'user', $user->get( 'id' ) ), GetterInterface::INT ) : $user->get( 'id' ) ) . '" class="digits required form-control" size="4"' . ( $userTooltip ? ' ' . $userTooltip : null ) . ' />';

			if ( $message ) {
				$_CB_framework->enqueueMessage( $message, $messageType );
			}

			HTML_cbblogsBlogEdit::showBlogEdit( $row, $input, $user, $model, $plugin );
		} else {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}
	}

	/**
	 * @param  null|int     $id
	 * @param  UserTable    $user
	 * @param  stdClass     $model
	 * @param  PluginTable  $plugin
	 */
	private function saveBlogEdit( $id, $user, $model, $plugin )
	{
		global $_CB_framework, $_PLUGINS;

		$blogLimit					=	(int) $plugin->params->get( 'blog_limit', null );
		$cbModerator				=	Application::User( (int) $user->get( 'id' ) )->isGlobalModerator();

		$row						=	new cbblogsBlogTable();

		$row->load( (int) $id );

		$canAccess					=	false;

		if ( ! $row->get( 'id' ) ) {
			if ( $cbModerator ) {
				$canAccess			=	true;
			} elseif ( $user->get( 'id' ) && Application::MyUser()->canViewAccessLevel( $plugin->params->get( 'blog_create_access', 2 ) ) ) {
				if ( ( ! $blogLimit ) || ( $blogLimit && ( cbblogsModel::getBlogsTotal( null, $user, $user, $plugin ) < $blogLimit ) ) ) {
					$canAccess		=	true;
				}
			}
		} elseif ( $cbModerator || ( $row->get( 'user' ) == $user->get( 'id' ) ) ) {
			$canAccess				=	true;
		}

		$profileUrl					=	$_CB_framework->userProfileUrl( $row->get( 'user', $user->get( 'id' ) ), false, 'cbblogsTab' );

		if ( $canAccess ) {
			if ( $plugin->params->get( 'blog_captcha', 0 ) && ( ! $row->get( 'id' ) ) && ( ! $cbModerator ) ) {
				$_PLUGINS->loadPluginGroup( 'user' );

				$_PLUGINS->trigger( 'onCheckCaptchaHtmlElements', array() );

				if ( $_PLUGINS->is_errors() ) {
					$row->setError( CBTxt::T( $_PLUGINS->getErrorMSG() ) );
				}
			}

			$new					=	( $row->get( 'id' ) ? false : true );

			if ( ! $row->bind( $_POST ) ) {
				$this->showBlogEdit( $id, $user, $model, $plugin, CBTxt::T( 'BLOG_FAILED_TO_BIND_ERROR_ERROR', 'Blog failed to bind! Error: [error]', array( '[error]' => $row->getError() ) ) ); return;
			}

			if ( ! $row->check() ) {
				$this->showBlogEdit( $id, $user, $model, $plugin, CBTxt::T( 'BLOG_FAILED_TO_VALIDATE_ERROR_ERROR', 'Blog failed to validate! Error: [error]', array( '[error]' => $row->getError() ) ) ); return;
			}

			if ( $row->getError() || ( ! $row->store() ) ) {
				$this->showBlogEdit( $id, $user, $model, $plugin, CBTxt::T( 'BLOG_FAILED_TO_SAVE_ERROR_ERROR', 'Blog failed to save! Error: [error]', array( '[error]' => $row->getError() ) ) ); return;
			}

			if ( $new && ( ! $row->get( 'published' ) ) && $plugin->params->get( 'approval_notify', 1 ) && ( ! $cbModerator ) ) {
				$cbUser				=	CBuser::getInstance( (int) $row->get( 'user' ), false );

				$extraStrings		=	array(	'site_name' => $_CB_framework->getCfg( 'sitename' ),
												'site' => '<a href="' . $_CB_framework->getCfg( 'live_site' ) . '">' . $_CB_framework->getCfg( 'sitename' ) . '</a>',
												'blog_id' => (int) $row->get( 'id' ),
												'blog_title' => $row->get( 'title' ),
												'blog_intro' => $row->get( 'blog_intro' ),
												'blog_full' => $row->get( 'blog_full' ),
												'blog_created' => $row->get( 'blog_created' ),
												'blog_user' => (int) $row->get( 'user' ),
												'blog_url' => cbblogsModel::getUrl( $row ),
												'blog_tab_url' => $_CB_framework->viewUrl( 'userprofile', false, array( 'user' => (int) $row->get( 'user' ), 'tab' => 'cbblogsTab' ) ),
												'user_name' => $cbUser->getField( 'formatname', null, 'html', 'none', 'profile' ),
												'user' => '<a href="' . $_CB_framework->viewUrl( 'userprofile', true, array( 'user' => (int) $row->get( 'user' ) ) ) . '">' . $cbUser->getField( 'formatname', null, 'html', 'none', 'profile' ) . '</a>'
											);

				$subject			=	$cbUser->replaceUserVars( CBTxt::T( 'Blogs - New Blog Created!' ), false, true, $extraStrings, false );
				$message			=	$cbUser->replaceUserVars( CBTxt::T( '[user] created [blog_title] and requires <a href="[blog_tab_url]">approval</a>!' ), false, true, $extraStrings, false );

				$notifications		=	new cbNotification();

				$notifications->sendToModerators( $subject, $message, false, 1 );

				cbRedirect( $profileUrl, CBTxt::T( 'Blog saved successfully and awaiting approval!' ) );
			} else {
				cbRedirect( $profileUrl, CBTxt::T( 'Blog saved successfully!' ) );
			}
		} else {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}
	}

	/**
	 * @param  int          $state
	 * @param  int          $id
	 * @param  UserTable    $user
	 * @param  stdClass     $model
	 * @param  PluginTable  $plugin
	 */
	private function stateBlog( $state, $id, $user, /** @noinspection PhpUnusedParameterInspection */ $model, $plugin )
	{
		global $_CB_framework;

		$row						=	new cbblogsBlogTable();

		$row->load( (int) $id );

		$canAccess					=	false;

		if ( $row->get( 'id' ) && ( Application::User( (int) $user->get( 'id' ) )->isGlobalModerator() || ( ( $row->get( 'user' ) == $user->get( 'id' ) ) && ( ! $plugin->params->get( 'blog_approval', 0 ) ) ) ) ) {
			$canAccess				=	true;
		}

		$profileUrl					=	$_CB_framework->userProfileUrl( $row->get( 'user', $user->get( 'id' ) ), false, 'cbblogsTab' );

		if ( $canAccess ) {
			$_POST['published']		=	(int) $state;

			if ( ! $row->bind( $_POST ) ) {
				cbRedirect( $profileUrl, CBTxt::T( 'BLOG_STATE_FAILED_TO_BIND_ERROR_ERROR', 'Blog state failed to bind! Error: [error]', array( '[error]' => $row->getError() ) ), 'error' );
			}

			if ( ! $row->check() ) {
				cbRedirect( $profileUrl, CBTxt::T( 'BLOG_STATE_FAILED_TO_VALIDATE_ERROR_ERROR', 'Blog state failed to validate! Error: [error]', array( '[error]' => $row->getError() ) ), 'error' );
			}

			if ( $row->getError() || ( ! $row->store() ) ) {
				cbRedirect( $profileUrl, CBTxt::T( 'BLOG_STATE_FAILED_TO_SAVE_ERROR_ERROR', 'Blog state failed to save! Error: [error]', array( '[error]' => $row->getError() ) ), 'error' );
			}

			cbRedirect( $profileUrl, CBTxt::T( 'Blog state saved successfully!' ) );
		} else {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}
	}

	/**
	 * @param  int          $id
	 * @param  UserTable    $user
	 * @param  stdClass     $model
	 * @param  PluginTable  $plugin
	 */
	private function deleteBlog( $id, $user, /** @noinspection PhpUnusedParameterInspection */ $model, /** @noinspection PhpUnusedParameterInspection */ $plugin )
	{
		global $_CB_framework;

		$row				=	new cbblogsBlogTable();

		$row->load( (int) $id );

		$canAccess			=	false;

		if ( $row->get( 'id' ) && ( ( $row->get( 'user' ) == $user->get( 'id' ) ) || Application::User( (int) $user->get( 'id' ) )->isGlobalModerator() ) ) {
			$canAccess		=	true;
		}

		$profileUrl			=	$_CB_framework->userProfileUrl( $row->get( 'user', $user->get( 'id' ) ), false, 'cbblogsTab' );

		if ( $canAccess ) {
			if ( ! $row->canDelete() ) {
				cbRedirect( $profileUrl, CBTxt::T( 'BLOG_FAILED_TO_DELETE_ERROR_ERROR', 'Blog failed to delete! Error: [error]', array( '[error]' => $row->getError() ) ), 'error' );
			}

			if ( ! $row->delete( (int) $id ) ) {
				cbRedirect( $profileUrl, CBTxt::T( 'BLOG_FAILED_TO_DELETE_ERROR_ERROR', 'Blog failed to delete! Error: [error]', array( '[error]' => $row->getError() ) ), 'error' );
			}

			cbRedirect( $profileUrl, CBTxt::T( 'Blog deleted successfully!' ) );
		} else {
			cbRedirect( $profileUrl, CBTxt::T( 'Not authorized.' ), 'error' );
		}
	}
}
