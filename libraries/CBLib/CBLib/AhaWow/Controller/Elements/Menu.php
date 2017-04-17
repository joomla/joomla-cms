<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 11/26/13 1:22 AM $
* @package CBLib\AhaWow\Controller\Elements
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\AhaWow\Controller\Elements;

use CBLib\AhaWow\Access;
use CBLib\AhaWow\Controller\DrawController;
use CBLib\AhaWow\Model\XmlQuery;
use CBLib\AhaWow\View\RegistryEditView;
use CBLib\Application\Application;
use CBLib\Database\Table\Table;
use CBLib\DependencyInjection\Container;
use CBLib\Input\InputInterface;
use CBLib\Language\CBTxt;
use CBLib\Registry\RegistryInterface;
use CBLib\Xml\SimpleXMLElement;
use CBLib\Database\DatabaseDriverInterface;
use CB\Database\Table\PluginTable;

defined('CBLIB') or die();

/**
 * CBLib\AhaWow\Controller\Elements\Menu Class implementation
 * 
 */
class Menu {
	/** database
	 * @var DatabaseDriverInterface */
	public $_db;
	/**
	 * xml <tablebrowser> node element
	 * @var SimpleXMLElement */
	public $_controllerModel;
	/**
	 * xml <types> node element
	 * @var SimpleXMLElement */
	public $_types;
	/**
	 * xml <actions> node element
	 * @var SimpleXMLElement */
	public $_actions;
	/**
	 * xml <views> node element
	 * @var SimpleXMLElement */
	public $_views;
	public $_options;
	public $_pluginParams;
	/** plugin object
	 * @var PluginTable */
	public $_pluginObject = null;
	/** @var int */
	public $_tabid = null;

	public $name = null;
	public $label;
	public $description;

	/**
	 * xml <styling> node element
	 * @var SimpleXMLElement */
	public $styling;
	/**
	 * @var string[]
	 */
	public $_cssImgBkgndStyles = array();

	public $rows = array();

	/**
	 * @var  RegistryEditView  $registryEditVew
	 */
	protected $registryEditVew;

	/**
	 * @var InputInterface
	 */
	protected $input			=	null;

	/**
	 * Constructor
	 *
	 * @param  InputInterface           $input            The user form input
	 * @param  SimpleXMLElement         $controllerModel  The model of the controller
	 * @param  array                    $options          The routing options
	 * @param  RegistryInterface        $pluginParams     The parameters of the plugin
	 * @param  SimpleXMLElement         $types            The types definitions in XML
	 * @param  SimpleXMLElement         $actions          The actions definitions in XML
	 * @param  SimpleXMLElement         $views            The views definitions in XML
	 * @param  PluginTable              $pluginObject     The plugin object
	 * @param  int                      $tabId            The tab id (if there is one)
	 * @param  DatabaseDriverInterface  $db               The tab id (if there is one)
	 * @param  RegistryEditView         $registryEditVew  The Registry Edit View (the calling object)
	 */
	public function __construct( InputInterface $input, SimpleXMLElement $controllerModel, $options,
								 RegistryInterface $pluginParams, SimpleXMLElement $types, SimpleXMLElement $actions,
								 SimpleXMLElement $views, PluginTable $pluginObject = null, $tabId = null,
								 DatabaseDriverInterface $db, RegistryEditView $registryEditVew )
	{
		$this->input				=	$input;
		$this->_controllerModel		=	$controllerModel;
		$this->_options				=	$options;
		$this->_pluginParams		=	$pluginParams;
		$this->_types				=	$types;
		$this->_actions				=	$actions;
		$this->_views				=	$views;
		$this->_pluginObject		=	$pluginObject;
		$this->_tabid				=	$tabId;
		$this->_db					=	$db;
		$this->registryEditVew		=	$registryEditVew;
	}

	protected function parseXML( ) {
		$this->name					=	$this->_controllerModel->attributes( 'name' );
		$this->label				=	$this->_controllerModel->attributes( 'label' );
		$this->description			=	$this->_controllerModel->attributes( 'description' );
		$this->styling				=	$this->_controllerModel->getElementByPath( 'styling' );
	}

	protected function loadRows( ) {
		global $_PLUGINS;

		foreach ( $this->_controllerModel->children() as $child ) {
			/** @var $child SimpleXMLElement */
			if ( $child->getName() == 'menu' ) {
				$this->_loadMenuRows( $child );
			} elseif ( $child->getName() == 'showview' ) {
				$showviewType				=	$child->attributes( 'type' );
				if ( $showviewType == 'plugins' ) {
					$groups							=	explode( ',', $child->attributes( 'groups' ) );
					$action							=	$child->attributes( 'action' );
					$path							=	$child->attributes( 'path' );

					foreach ($groups as $group ) {
						$matches						=	null;
						if ( preg_match( '/^([^\[]+)\[(.+)\]$/', $group, $matches ) ) {
							$classId					=	$matches[2];
							$group						=	$matches[1];
						} else {
							$classId					=	null;
						}
						$_PLUGINS->loadPluginGroup( $group, $classId, 0 );
						$loadedPlugins					=	$_PLUGINS->getLoadedPluginGroup( $group );
						foreach ( $loadedPlugins as $plugin ) {
							$element					=	$_PLUGINS->loadPluginXML( 'action', $action, $plugin->id );
							$viewModel					=	$element->getElementByPath( $path );
							if ( $viewModel ) {
								foreach ($viewModel->children() as $extChild ) {
									/** @var $extChild SimpleXMLElement */
									if ( $extChild->getName() == 'menu' ) {

										// Check if ACL authorizes to use the action linked by that menu:
										if ( ! $this->authorised( $extChild ) ) {
											continue;
										}

										$this->_loadMenuRows( $extChild );
									}
								}
							}
						}
					}
				} elseif ( $showviewType == 'xml' ) {
					// e.g.: <showview name="gateway_paymentstatus_information" mode="view" type="xml" file="processors/{payment_method}/edit.gateway" path="/*/views/view[@name=&quot;paymentstatusinformation&quot;]" mandatory="false" />
					$fromNode			=	$child->attributes( 'path' );
					$fromFile			=	$child->attributes( 'file' );
					$mandatory			=	$child->attributes( 'mandatory' );
					if ( $fromNode && ( $fromFile !== null ) ) {
						// $this->substituteName( $fromFile, true );
						// $this->substituteName( $fromNode, false );
						if ( $fromFile !== '' ) {
							$fromFile	=	RegistryEditView::pathFromXML( $fromFile . '.xml', $child, $this->_pluginObject );
						}
						if ( strpos( $fromFile, '/*/' ) !== false ) {
							$parts		=	explode( '/*/', $fromFile );
							$fromFiles	=	cbReadDirectory( $parts[0], '.', false, true );		// '^' . preg_quote( $subparts[0], '/' ) . '$'
						} else {
							$parts		=	null;
							$fromFiles	=	array( $fromFile );
						}
						foreach ( $fromFiles as $fromDirOrFile ) {
							$viewModel				=	null;
							if ( $fromDirOrFile === '' ) {
								$viewModel			=	$this->_views->xpath( $fromNode );
							} else {
								if ( ( ! isset( $parts ) ) || is_dir( $fromDirOrFile ) ) {
									$fromDirOrFile	=	$fromDirOrFile . ( isset( $parts[1] ) ? '/' . $parts[1] : '' );
									if ( file_exists( $fromDirOrFile ) ) {
										$fromRoot	=	new SimpleXMLElement( $fromDirOrFile, LIBXML_NONET | ( defined('LIBXML_COMPACT') ? LIBXML_COMPACT : 0 ), true );
										$viewModel	=	$fromRoot->xpath( $fromNode );
									}
								} else {
									continue;
								}
							}
							if ( $viewModel && count( $viewModel ) ) {

								foreach ($viewModel[0]->children() as $extChild ) {
									/** @var $extChild SimpleXMLElement */
									if ( $extChild->getName() == 'menu' ) {

										// Check if ACL authorizes to use the action linked by that menu:
										if ( ! $this->authorised( $extChild ) ) {
											continue;
										}
										$this->_loadMenuRows( $extChild );
									}
								}

							} elseif ( $mandatory == 'false' ) {
								continue;
							} else {
								trigger_error( 'MenuController:showview: View file ' . $fromDirOrFile . ', path: ' . $fromNode . ' does not exist or is empty.', E_USER_NOTICE );
							}
						}
					}

				}
			}
		}

	}
	/**
	 * Loads the rows of menu
	 *
	 * @param  SimpleXMLElement  $child
	 */
	protected function _loadMenuRows( &$child ) {
		if ( $child->getName() == 'menu' ) {
			$listFieldsRows				=	$child->getElementByPath( 'fields' );
			$menuName					=	$child->attributes( 'name' );
			$this->rows[$menuName]		=	new Table();
			if ( $listFieldsRows ) {
				foreach ( $listFieldsRows->children() as $field ) {

					/** @var $field SimpleXMLElement */
					if ( $field->attributes( 'type' ) == 'private' ) {
						$name			= $field->attributes( 'name' );
						// $className	= $field->attributes( 'class' );
						// $methodName	= $field->attributes( 'method' );
						$value			= $field->attributes( 'value' );
						$content		=	$value;		// it will be called at rendering time:
						/*				
												if ( $className && $methodName && class_exists( $className ) ) {
													$obj = new $className( $this->_db );				//TBD: implement the singleton similarly/calling _form_private
													if ( method_exists( $obj, $methodName ) ) {
									/*
														$row	=	$this->_modelOfData[0];				//TBD: checked....
														foreach (get_object_vars($obj) as $key => $v) {
															if( substr( $key, 0, 1 ) != '_' ) {			// internal attributes of an object are ignored
																if (isset($row->$key)) {
																	$obj->$key = $row->$key;
																}
															}
														}
									*
														$content = $obj->$methodName( $value, $this->_pluginParams );	//TBD: pluginParams should be available by the method params() of $obj, not as function parameter
													} else {
														$content	=	'Missing method ' . $methodName;
													}
												} else {
													$content = 'Missing class, or method in xml';
												}
						*/
						$this->rows[$menuName]->$name = $content;

					} else {

						$xmlsql					=	new XmlQuery( $this->_db, null, $this->_pluginParams );
						$xmlsql->process_field( $field );
						$obj	=	null;
						if ( $xmlsql->queryLoadObject( $obj ) ) {			// get the resulting object
							foreach (get_object_vars($obj) as $k => $v) {
								if ( substr( $k, 0, 1 ) != '_' ) {			// internal attributes of an object are ignored
									$this->rows[$menuName]->$k = $v;
								}
							}
							// } else {
							// error in query...
						}

					}

				}
			}
		}
	}
	/**
	 * Draws a list of a SQL table
	 *
	 * @return string   HTML of table
	 */
	public function draw( ) {
		if ( ! $this->name ) {
			$this->parseXML();		// get List scheme
		}
		if ( count( $this->rows ) == 0 ) {
			$this->loadRows();			// get List content
		}

		$controller = new DrawController( $this->input, $this->_controllerModel, $this->_actions, $this->_options );

		ob_start();
		$this->renderMenuGroup( $this->_controllerModel, $this->rows, $controller, $this->_options );
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	}

	/**
	 * Checks if a menu's action is permitted for the user
	 *
	 * @param  SimpleXMLElement  $menu
	 * @return boolean
	 */
	protected function authorised( $menu ) {
		$childAction			=	$menu->attributes( 'action' );
		if ( substr( $childAction, 0, 10 ) == 'cb_action:' ) {
			$actionName			=	substr( $childAction, 10 );
			$action				=	$this->_actions->getChildByNameAttr( 'action', 'name', $actionName );
			if ( $action ) {
				if ( ! Access::authorised( $action ) ) {
					return false;
				}
			}
		}
		return true;
	}
	/**
	 * Renders as ECHO HTML code
	 *
	 * @param  SimpleXMLElement  $modelView
	 * @param  array             $modelRows
	 * @param  DrawController    $controllerView
	 * @param  array             $options
	 * @return void
	 */
	protected function renderMenuGroup( /** @noinspection PhpUnusedParameterInspection */ &$modelView, &$modelRows, &$controllerView, $options  ) {
		global $_PLUGINS;

		$htmlFormatting	=	'span';

		if ( count( $this->_controllerModel->children() ) > 0 ) {
			//TBD: not needed yet, but kept if needed    $this->_applyStylingToMeAndChildren( $this->_controllerModel, $this->styling );

			echo $this->renderMenuGroupHeader( $this->_controllerModel, $htmlFormatting );

			foreach ( $this->_controllerModel->children() as $child ) {
				/** @var $child SimpleXMLElement */

				// Check if ACL authorizes to view and to use that menu:
				if ( ! Access::authorised( $child ) ) {
					continue;
				}

				if ( $child->getName() == 'menu' ) {

					// Check if ACL authorizes to use the action linked by that menu:
					if ( ! $this->authorised( $child ) ) {
						continue;
					}

					$menuName				=	$child->attributes( 'name' );
					echo $this->renderMenu( $child, $modelRows[$menuName], $controllerView, $options, $htmlFormatting );

				} elseif ( $child->getName() == 'showview' ) {

					$showviewType				=	$child->attributes( 'type' );
					if ( $showviewType == 'plugins' ) {
						$groups							=	explode( ',', $child->attributes( 'groups' ) );
						$action							=	$child->attributes( 'action' );
						$path							=	$child->attributes( 'path' );

						foreach ($groups as $group ) {
							$matches						=	null;
							if ( preg_match( '/^([^\[]+)\[(.+)\]$/', $group, $matches ) ) {
								$classId					=	$matches[2];
								$group						=	$matches[1];
							} else {
								$classId					=	null;
							}
							$_PLUGINS->loadPluginGroup( $group, $classId, 0 );
							$loadedPlugins					=	$_PLUGINS->getLoadedPluginGroup( $group );
							foreach ( $loadedPlugins as $plugin ) {
								$element					=	$_PLUGINS->loadPluginXML( 'action', $action, $plugin->id );
								$viewModel					=	$element->getElementByPath( $path );
								if ( $viewModel ) {
									foreach ($viewModel->children() as $extChild ) {
										/** @var $extChild SimpleXMLElement */
										if ( $extChild->getName() == 'menu' ) {

											// Check if ACL authorizes to use the action linked by that menu:
											if ( ! $this->authorised( $extChild ) ) {
												continue;
											}

											$menuName		=	$extChild->attributes( 'name' );
											echo $this->renderMenu( $extChild, $modelRows[$menuName], $controllerView, $options, $htmlFormatting );
										}
									}
								}
							}
						}
					} elseif ( $showviewType == 'xml' ) {
						// e.g.: <showview name="gateway_paymentstatus_information" mode="view" type="xml" file="processors/{payment_method}/edit.gateway" path="/*/views/view[@name=&quot;paymentstatusinformation&quot;]" mandatory="false" />
						$fromNode			=	$child->attributes( 'path' );
						$fromFile			=	$child->attributes( 'file' );
						$mandatory			=	$child->attributes( 'mandatory' );
						if ( $fromNode && ( $fromFile !== null ) ) {
							// $this->substituteName( $fromFile, true );
							// $this->substituteName( $fromNode, false );
							if ( $fromFile !== '' ) {
								$fromFile	=	RegistryEditView::pathFromXML( $fromFile . '.xml', $child, $this->_pluginObject );
							}
							if ( strpos( $fromFile, '/*/' ) !== false ) {
								$parts		=	explode( '/*/', $fromFile );
								$fromFiles	=	cbReadDirectory( $parts[0], '.', false, true );		// '^' . preg_quote( $subparts[0], '/' ) . '$'
							} else {
								$parts		=	null;
								$fromFiles	=	array( $fromFile );
							}
							foreach ( $fromFiles as $fromDirOrFile ) {
								$viewModel				=	null;
								if ( $fromDirOrFile === '' ) {
									$viewModel			=	$this->_views->xpath( $fromNode );
								} else {
									if ( ( ! isset( $parts ) ) || is_dir( $fromDirOrFile ) ) {
										$fromDirOrFile	=	$fromDirOrFile . ( isset( $parts[1] ) ? '/' . $parts[1] : '' );
										if ( file_exists( $fromDirOrFile ) ) {
											$fromRoot	=	new SimpleXMLElement( $fromDirOrFile, LIBXML_NONET | ( defined('LIBXML_COMPACT') ? LIBXML_COMPACT : 0 ), true );
											$viewModel	=	$fromRoot->xpath( $fromNode );
										}
									} else {
										continue;
									}
								}
								if ( $viewModel && count( $viewModel ) ) {

									foreach ($viewModel[0]->children() as $extChild ) {
										/** @var $extChild SimpleXMLElement */
										if ( $extChild->getName() == 'menu' ) {

											// Check if ACL authorizes to use the action linked by that menu:
											if ( ! $this->authorised( $extChild ) ) {
												continue;
											}

											$menuName		=	$extChild->attributes( 'name' );
											echo $this->renderMenu( $extChild, $modelRows[$menuName], $controllerView, $options, $htmlFormatting );
										}
									}

								} elseif ( $mandatory == 'false' ) {
									continue;
								} else {
									trigger_error( 'MenuController:showview: View file ' . $fromDirOrFile . ', path: ' . $fromNode . ' does not exist or is empty.', E_USER_NOTICE );
								}
							}
						}

					}
				}
			}

			echo $this->renderMenuGroupFooter( $this->_controllerModel, $htmlFormatting );
		}
	}

	/**
	 * Renders as ECHO HTML code
	 *
	 * @param  SimpleXMLElement     $modelView
	 * @param  RegistryInterface[]  $modelRows
	 * @param  DrawController       $controllerView
	 * @param  array                $options
	 * @param  boolean              $htmlFormatting
	 * @return string
	 */
	protected function renderMenu( &$modelView, &$modelRows, &$controllerView, $options, /** @noinspection PhpUnusedParameterInspection */ $htmlFormatting  ) {
		$renderer				=	new RegistryEditView( $this->input, $this->_db, $this->_pluginParams, $this->_types, $this->_actions, $this->_views, $this->_pluginObject, $this->_tabid );

		$renderer->setParentView( $modelView );
		$renderer->setModelOfDataRows( $modelRows );

		$name					=	$modelView->attributes( 'name' );
		$label					=	$modelView->attributes( 'label' );
		$description			=	$modelView->attributes( 'description' );
		$action					=	$modelView->attributes( 'action' );
		$width					=	$modelView->attributes( 'width' );
		$height					=	$modelView->attributes( 'height' );
		$icon					=	$modelView->attributes( 'icon' );
		$iconwidth				=	$modelView->attributes( 'iconwidth' );
		$iconheight				=	$modelView->attributes( 'iconheight' );
		$iconhover				=	$modelView->attributes( 'iconhover' );

		if ( ( ! $icon ) && ( ! $modelView->attributes( 'buttonclass' ) ) ) {
			$modelView->addAttribute( 'buttonclass', 'default' );
		}

		if ( ! $modelView->attributes( 'textclass' ) ) {
			$modelView->addAttribute( 'textclass', 'primary' );
		}

		$cssclass				=	RegistryEditView::buildClasses( $modelView );

		if ( $label ) {
			$header				=	'<div class="cbButtonMenuItemLabel"><span>' . CBTxt::Th( $label ) . '</span></div>';
		} else {
			$header				=	null;
		}

		if ( count( $modelView->children() ) > 0 ) {
			$modelHtml			=	$renderer->renderEditRowView( $modelView, $modelRows, $controllerView, $options, 'view', 'span' );
		} else {
			$modelHtml			=	null;
		}

		$footer					=	( $modelHtml ? '<div class="cbButtonMenuItemData">' . $modelHtml . '</div>' : null );

		$attributes				=	' class="cbButtonMenuItem' . ( $cssclass ? ' ' . htmlspecialchars( $cssclass ) : '' ) . '"';

		if ( $width || $height ) {
			$attributes			.=	' style="' . ( $width ? 'width:' . htmlspecialchars( $width ) . ';' : '' ) . ( $height ? 'height:' . htmlspecialchars( $height ) . ';' : '' ) . '"';
		}

		if ( $description ) {
			$tooltipTitle		=	addslashes( CBTxt::Th( $label ) );
			$tooltip			=	addslashes( CBTxt::Th( $description ) );
			$attributes			=	cbTooltip( null, $tooltip, $tooltipTitle, null, null, null, null, $attributes );
		}

		$return					=	'<div' . $attributes . '>';

		if ( $action ) {
			$data				=	null;		// see later if we need to put data here...
			$link				=	$controllerView->drawUrl( $action, $modelView, $data, 0, true );

			if ( $link ) {
				$return			.=	'<a href="' . $link . '" class="cbButtonMenuItemLink">';
			}
		} else {
			$link				=	null;
		}

		if ( $icon ) {
			$renderer->substituteName( $icon, false );

			$icon				=	RegistryEditView::pathFromXML( $icon, $modelView, $this->_pluginObject, 'live' );
		}

		if ( $iconhover ) {
			$renderer->substituteName( $iconhover, false );

			$iconhover			=	RegistryEditView::pathFromXML( $iconhover, $modelView, $this->_pluginObject, 'live' );
		}

		if ( $iconwidth || $iconheight ) {
			$iconattributes		=	' style="' . ( $iconwidth ? 'width:' . htmlspecialchars( $iconwidth ) : '' ) . ( $iconheight ? 'height:' . htmlspecialchars( $iconheight ) : '' ) . '"';
		} else {
			$iconattributes		=	null;
		}

		$return					.=	'<div class="cbButtonMenuItemInner' . ( $name ? ' cbDIIM' . htmlspecialchars( $name ) : '' ) . '">'
								.		$header
								.		( $icon ? '<img src="' . htmlspecialchars( $icon ) . '" alt="" class="cbButtonMenuItemImg"' . ( $iconattributes ? ' ' . $iconattributes : '' ) . ' />' : null )
								.		( $iconhover ? '<img src="' . htmlspecialchars( $iconhover ) . '" alt="" class="cbButtonMenuItemImgHover"' . ( $iconattributes ? ' ' . $iconattributes : '' ) . ' />' : null )
								.		$footer
								.	'</div>';

		if ( $action && $link ) {
			$return				.=	'</a>';
		}

		$return					.=	'</div>';

		return $return;
	}

	/**
	 * Renders the header of the menu group
	 *
	 * @param  SimpleXMLElement  $param
	 * @param  string              $htmlFormatting
	 * @return string
	 */
	protected function renderMenuGroupHeader( &$param, $htmlFormatting ) {
		$html				=	array();
		$legend				=	$param->attributes( 'label' );
		$description		=	$param->attributes( 'description' );
		$cssclass			=	RegistryEditView::buildClasses( $param );

		if ( $htmlFormatting == 'table' ) {
			$html[]			=	'<tr><td colspan="3" style="width: 100%;"' . ( $cssclass ? ' class="' . htmlspecialchars( $cssclass ) . '"' : '' ) . '>';
		} elseif ( $htmlFormatting == 'td' ) {
			$html[]			=	'<td' . ( $cssclass ? ' class="' . htmlspecialchars( $cssclass ) . '"' : '' ) . '>';
		}

		if ( $legend ) {
			$html[]			=	'<h2>' . CBTxt::Th( $legend ) . '</h2>';
		}

		if ( $htmlFormatting == 'table' ) {
			$html[]			=	'<table class="table table-noborder">';

			if ( $description ) {
				$html[]		=	'<tr><td colspan="3" style="width: 100%;"><strong>' . CBTxt::Th( $description ) . '</strong></td></tr>';
			}
		} elseif ( $htmlFormatting == 'td' ) {
			if ( $description ) {
				$html[]		=	'<td colspan="3" style="width: 100%;"><strong>' . CBTxt::Th( $description ) . '</strong></td>';
			}
		} else {
			if ( $description ) {
				$html[]		=	'<strong>' . CBTxt::Th( $description ) . '</strong>';
			}
		}

		if ( ! in_array( $htmlFormatting, array('table', 'td' ) ) ) {
			$html[]			=	'<div class="cbButtonMenu' . ( $cssclass ? ' ' . htmlspecialchars( $cssclass ) : '' ) . '">';
		}

		return implode( '', $html );
	}

	/**
	 * Renders the footer of the menu group
	 *
	 * @param  SimpleXMLElement  $param
	 * @param  string              $htmlFormatting
	 * @return string
	 */
	protected function renderMenuGroupFooter( /** @noinspection PhpUnusedParameterInspection */ &$param, $htmlFormatting ) {
		$html			=	array();

		if ( $htmlFormatting == 'table' ) {
			$html[]		=	'</table>';
			$html[]		=	'</td></tr>';
		} elseif ( $htmlFormatting == 'td' ) {
			$html[]		=	'</td>';
		} else {
			$html[]		=	'</div>';
		}

		return implode( '', $html );
	}
	/*
	 * Applies the $styling to children of $element
	 *
	 * @param SimpleXMLElement $element
	 * @param SimpleXMLElement $styling
	 *
	protected function _applyStylingToMeAndChildren( &$element, &$styling ) {
		if ( $styling && $element ) {
			$elementChildren	=	$element->children();		// potentially crashing PHP 4.4.4+ion !!!!
			foreach ( $styling->children() as $style ) {
				if ( $style->getName() == 'style' ) {
					$tag			=	$style->attributes( 'tag' );
					$styleChildren	=	$style->children();
					foreach ( $styleChildren as $apply ) {
						if ( $apply->getName() == 'apply' ) {
							$applyAttributes	=	$apply->attributes();
							
							if ( $tag == '..' ) {
								// $name	=	$element->attributes( 'name' );
								foreach ( $applyAttributes as $attrName => $attrValue ) {
									$prevVal	=	$element->attributes( $attrName );
									$element->addAttribute( $attrName, sprintf( $attrValue, $prevVal ) );
								}
							} else {
								foreach( $elementChildren as $k => $el ){
									if ( ( $el->getName() == $tag ) || ( $tag == '*' ) ) {
										foreach ( $applyAttributes as $attrName => $attrValue ) {
											$prevVal	=	$el->attributes( $attrName );
											$elementChildren[$k]->addAttribute( $attrName, sprintf( $attrValue, $prevVal ) );
										}
									}
								}
							}
							
						}
					}
				}
			}
		}
	}
	*/
}
