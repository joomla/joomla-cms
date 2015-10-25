<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

jimport( 'joomla.plugin.plugin' );

class plgSystemCommunityBuilder extends JPlugin {

	/**
	 * @param  JForm         $form  Joomla XML form
	 * @param  array|object  $data  Form data (j2.5 is array, j3.0 is object, converted to array for easy usage between both)
	 */
	public function onContentPrepareForm( $form, $data ) {
		if ( ( $form instanceof JForm ) && ( $form->getName() == 'com_menus.item' ) ) {
			$data				=	(array) $data;

			if ( isset( $data['request']['option'] ) && ( $data['request']['option'] == 'com_comprofiler' ) && isset( $data['request']['view'] ) && ( $data['request']['view'] == 'pluginclass' ) ) {
				$element		=	( isset( $data['request']['plugin'] ) ? $data['request']['plugin'] : 'cb.core' );

				if ( $element ) {
					$db			=	JFactory::getDBO();

					$query		=	'SELECT ' . $db->quoteName( 'type' )
								.	', ' . $db->quoteName( 'folder' )
								.	"\n FROM " . $db->quoteName( '#__comprofiler_plugin' )
								.	"\n WHERE " . $db->quoteName( 'element' ) . " = " . $db->quote( $element );
					$db->setQuery( $query );
					$plugin		=	$db->loadAssoc();

					if ( $plugin ) {
						$path	=	JPATH_ROOT . '/components/com_comprofiler/plugin/' . $plugin['type'] . '/' . $plugin['folder'] . '/xml';

						if ( file_exists( $path ) ) {
							JForm::addFormPath( $path );

							$form->loadFile( 'metadata', false );
						}
					}
				}
			}
		}
	}

	public function onAfterRoute() {
		// Joomla doesn't populate GET so we'll do it below based off the menu item and routing variables:
		$app							=	JFactory::getApplication();

		if ( $app->isSite() && ( $app->input->get( 'option' ) == 'com_comprofiler' ) ) {
			// Map the current menu item variables to GET:
			$menu						=	$app->getMenu()->getActive();

			if ( $menu && isset( $menu->query ) && ( isset( $menu->query['option'] ) ) && ( $menu->query['option'] == 'com_comprofiler' ) ) {
				foreach( $menu->query as $k => $v ) {
					if ( ! isset( $_GET[$k] ) ) {
						$_GET[$k]		=	$v;
					}

					if ( ! isset( $_REQUEST[$k] ) ) {
						$_REQUEST[$k]	=	$v;
					}
				}
			}

			// Map the current route variables to GET:
			$route						=	$app->getRouter()->getVars();

			if ( $route && ( isset( $route['option'] ) ) && ( $route['option'] == 'com_comprofiler' ) ) {
				foreach( $route as $k => $v ) {
					if ( ! isset( $_GET[$k] ) ) {
						$_GET[$k]		=	$v;
					}

					if ( ! isset( $_REQUEST[$k] ) ) {
						$_REQUEST[$k]	=	$v;
					}
				}
			}
		}
	}

	public function onAfterInitialise() {
		$app								=	JFactory::getApplication();

		if ( $app->isSite() ) {
			if ( $app->input->get( 'option' ) == 'com_comprofiler' ) {
				// CB is dynamic and can't be page cached; so remove the cache:
				if ( JFactory::getConfig()->get( 'caching' ) ) {
					JFactory::getCache( 'page' )->remove( JUri::getInstance()->toString() );
				}
			}

			if ( $this->isRerouteSafe() ) {
				$view						=	$app->input->get( 'task' );

				if ( ! $view ) {
					$view					=	$app->input->get( 'view' );
				}

				if ( $this->params->get( 'redirect_urls', 1 ) && ( $app->input->get( 'option' ) == 'com_users' ) ) {
					switch ( $view ) {
						case 'profile':
							if ( $app->input->get( 'layout' ) == 'edit' ) {
								$userId		=	(int) $app->input->get( 'user_id' );
								$task		=	'userdetails';

								if ( $userId ) {
									$task	.=	'&user=' . $userId;
								}
							} else {
								$task		=	'userprofile';
							}
							break;
						case 'registration':
							$task			=	'registers';
							break;
						case 'reset':
						case 'remind':
							$task			=	'lostpassword';
							break;
						case 'user.logout':
						case 'logout':
							$task			=	'logout';
							break;
						case 'user.login':
						case 'login':
						default:
							$task			=	'login';
							break;
					}

					$Itemid					=	( $this->params->get( 'itemids', 1 ) ? $this->getItemid( $task ) : null );
					$url					=	'index.php?option=com_comprofiler' . ( $task ? '&view=' . $task : null ) . ( $Itemid ? '&Itemid=' . $Itemid : null );

					if ( in_array( $task, array( 'login', 'logout' ) ) ) {
						$return				=	$app->input->get( 'return', '', 'BASE64' );

						if ( $return ) {
							$url			.=	'&return=' . $return;
						}
					}

					$app->redirect( JRoute::_( $url, false ), null, null, true, true );
				}

				$this->setReturnURL( $app );

				if ( $this->params->get( 'rewrite_urls', 1 ) ) {
					$router					=	$app->getRouter();

					$router->attachBuildRule( array( $this, 'buildRule' ) );
				}
			}
		}
	}

	/**
	 * @param plgSystemCommunityBuilder $router
	 * @param JUri $uri
	 */
	public function buildRule( &$router, &$uri ) {
		$app							=	JFactory::getApplication();

		if ( $app->isSite() && $this->isRerouteSafe() ) {
			if ( $uri->getVar( 'option' ) == 'com_users' ) {
				$uri->setVar( 'option', 'com_comprofiler' );

				$view					=	$uri->getVar( 'task' );

				if ( ! $view ) {
					$view				=	$uri->getVar( 'view' );
				}

				switch ( $view ) {
					case 'profile':
						if ( $uri->getVar( 'layout' ) == 'edit' ) {
							$userId		=	(int) $uri->getVar( 'user_id' );
							$task		=	'userdetails';

							if ( $userId ) {
								$task	.=	'&user=' . $userId;
							}
						} else {
							$task		=	'userprofile';
						}
						break;
					case 'registration':
						$task			=	'registers';
						break;
					case 'reset':
					case 'remind':
						$task			=	'lostpassword';
						break;
					case 'logout':
						$task			=	'logout';
						break;
					case 'login':
					default:
						$task			=	'login';
						break;
				}

				$uri->delVar( 'task' );
				$uri->delVar( 'view' );
				$uri->delVar( 'layout' );

				if ( $task ) {
					$uri->setVar( 'view', $task );
				}

				$Itemid					=	$uri->getVar( 'Itemid' );

				if ( ! $Itemid ) {
					$Itemid				=	( $this->params->get( 'itemids', 1 ) ? $this->getItemid( $task ) : null );
				}

				$uri->delVar( 'Itemid' );

				if ( $Itemid ) {
					$uri->setVar( 'Itemid', $Itemid );
				}
			}
		}
	}

	/**
	 * Returns the task specific Itemid from Joomla CB menu items
	 *
	 * @param string $task
	 * @return null|int
	 */
	private function getItemid( $task ) {
		static $items			=	null;

		if ( ! isset( $items ) ) {
			$app				=	JFactory::getApplication();
			$menu				=	$app->getMenu();
			$items				=	$menu->getItems( 'component', 'com_comprofiler' );
		}

		$Itemid					=	null;

		if ( ( $task !== 'userprofile' ) && is_string( $task ) ) {
			if ( $items ) foreach ( $items as $item ) {
				if ( ( isset( $item->query['view'] ) && ( $item->query['view'] == $task ) ) || ( isset( $item->query['task'] ) && ( $item->query['task'] == $task ) ) ) {
					$Itemid		=	$item->id;
				}
			}
		}

		if ( ( $task === 'userprofile' ) || ( ( ! $Itemid ) && ( ! in_array( $task, array( 'login', 'logout', 'registers', 'lostpassword' ) ) ) ) ) {
			if ( $items ) foreach ( $items as $item ) {
				if ( ( ! isset( $item->query['view'] ) ) && ( ! isset( $item->query['task'] ) ) ) {
					$Itemid		=	$item->id;
				}
			}

			if ( ! $Itemid ) {
				if ( $items ) foreach ( $items as $item ) {
					if ( ( isset( $item->query['view'] ) && ( $item->query['view'] == 'userslist' ) ) || ( isset( $item->query['task'] ) && ( $item->query['task'] == 'userslist' ) ) ) {
						$Itemid	=	$item->id;
					}
				}
			}
		}

		return $Itemid;
	}

	/**
	 * Grabs the login form return url and sets it to all available cb login forms on the page
	 *
	 * @param JApplication $app
	 */
	private function setReturnURL( $app ) {
		if ( ! $this->getUserIsGuest() ) {
			return;
		}

		static $cache						=	0;

		if ( ! $cache++ ) {
			if ( $this->params->get( 'return_urls', 1 ) ) {
				$redirect					=	$app->getUserState( 'users.login.form.return', null );

				if ( ! $redirect ) {
					$data					=	$app->getUserState( 'users.login.form.data', array() );
					$redirect				=	( isset( $data['return'] ) ? $data['return'] : null );

					if ( $redirect ) {
						$data['return']		=	null;

						$app->setUserState( 'users.login.form.data', $data );
					}
				} else {
					$app->setUserState( 'users.login.form.return', null );
				}

				if ( $redirect ) {
					$document				=	JFactory::getDocument();

					$js						=	"function cbLoginReturn() {"
											.		"var pageForms = document.forms;"
											.		"if ( pageForms ) for ( i = 0; i < pageForms.length; i++ ) {"
											.			"if ( pageForms[i].id == 'login-form' ) {"
											.				"pageForms[i].return.value = '" . addslashes( 'B:' . base64_encode( $redirect ) ) . "';"
											.			"}"
											.		"}"
											.	"}"
											.	"if ( window.addEventListener ) {"
											.		"window.addEventListener( 'load', cbLoginReturn, false );"
											.	"} else if ( window.attachEvent ) {"
											.		"window.attachEvent( 'onload', cbLoginReturn );"
											.	"}";

					$document->addScriptDeclaration( $js );
				}
			}
		}
	}

	/**
	 * Checks if the viewing user is a guest
	 *
	 * @return bool
	 */
	private function getUserIsGuest() {
		static $cache	=	null;

		if ( $cache === null ) {
			$cache		=	JFactory::getUser()->get( 'guest' );
		}

		return $cache;
	}

	/**
	 * Checks online status of the site and if the user is a guest
	 * Used to determine if URL rewriting is safe to perform
	 *
	 * @return bool
	 */
	private function isRerouteSafe() {
		return ( ( JFactory::getConfig()->get( 'offline' ) == 1 ) && $this->getUserIsGuest() ? false : true );
	}
}
