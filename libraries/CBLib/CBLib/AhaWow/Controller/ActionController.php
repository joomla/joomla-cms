<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 11/28/13 6:07 PM $
* @package CBLib\AhaWow\Controller
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\AhaWow\Controller;

use CBLib\AhaWow\Access;
use CBLib\AhaWow\AutoLoaderXml;
use CBLib\AhaWow\Model\Context;
use CBLib\AhaWow\Model\XmlQuery;
use CBLib\AhaWow\View\ActionView;
use CBLib\AhaWow\View\ActionViewAdmin;
use CBLib\AhaWow\View\RegistryEditView;
use CBLib\Application\Application;
use CBLib\Cms\CmsInterface;
use CBLib\Database\DatabaseDriverInterface;
use CBLib\Input\Input;
use CBLib\Input\InputInterface;
use CBLib\Language\CBTxt;
use CBLib\Registry\GetterInterface;
use CBLib\Registry\ParamsInterface;
use CBLib\Registry\Registry;
use CBLib\Registry\RegistryInterface;
use CBLib\Xml\SimpleXMLElement;
use CBLib\Database\Table\TableInterface;
use CB\Database\Table\PluginTable;
// Temporarily:
use cbTabs;

defined('CBLIB') or die();

/**
 * CBLib\AhaWow\Controller\ActionController Class implementation
 * 
 */
class ActionController {
	/**
	 * @var RegistryInterface
	 */
	protected $params;
	protected $input;
	/**
	 * @var
	 */
	protected $db;
	protected $clientId;
	/**
	 * @var TableInterface
	 */
	public $_data				=	null;
	public $_options			=	array();
	public $_getParams			=	array();

	/**
	 * Constructor
	 *
	 * @param  InputInterface           $input  Inputs
	 * @param  DatabaseDriverInterface  $db     Database
	 * @param  CmsInterface             $cms    Client id (0: front, 1: admin)
	 */
	public function __construct( InputInterface $input, DatabaseDriverInterface $db, CmsInterface $cms )
	{
		$this->input			=	$input;
		$this->db				=	$db;
		$this->clientId			=	$cms->getClientId();
	}

	/**
	 * Sets the data for the Model
	 *
	 * @param  TableInterface  $data
	 * @return void
	 */
	public function setData( $data )
	{
		$this->_data			=	$data;
	}

	/**
	 * Sets the options
	 *
	 * @param  array  $options  Options
	 * @return void
	 */
	public function setOptions( $options )
	{
		$this->_options			=	$options;
	}

	/**
	 * Sets the GET parameters for the request
	 *
	 * @param  array  $options  GET parameters
	 * @return void
	 */
	public function setGetParams( $options )
	{
		$this->_getParams		=	$options;
	}

	/**
	 * Gets the base options task, plugin and tabid
	 *
	 * @return array  array( 'view' => string, 'plugin' => string, 'tabid' => null|int )
	 */
	public function getBaseOptions( )
	{
		global $_CB_framework, $_PLUGINS;
		if ( $_CB_framework->getUi() == 2 ) {

			$plugin				=	$_PLUGINS->getPluginObject();
			if ( $plugin ) {
				$pluginOptions	=	array(	'view'		=>	$this->input->get( 'view', null, GetterInterface::STRING ),
											'pluginid'	=>	$_PLUGINS->getPluginObject()->id,
											'tabid'		=>	null
											// ? 'action'		=>	$this->input->get( 'action', null, GetterInterface::STRING )
									);
			} else {
				$pluginOptions	=	$this->_options;
			}
		} else {
			$pluginOptions		=	$this->_options;
		}
		return array_merge( $_CB_framework->getUrlRoutingOfCb(), $pluginOptions );
	}

	/**
	 * Handles a request with a route to the controller action
	 *
	 * @param  array   $route  Ordered Route array with at least option, view, ..., method (where method is view, edit, apply, save)
	 * @return string
	 *
	 * @throws \LogicException
	 */
	public function handleAction( array $route )
	{
		$option						=	$route['option'];
		$view						=	$route['view'];
		$method						=	$route['method'];
		if ( ! $method ) {
			$method					=	( isset( $route['act'] ) ? $route['act'] : 'edit' );
		}

		$frontOrAdmin				=	$this->clientId == 1 ? 'admin' : 'front';

		/** @var AutoLoaderXml $autoLoaderXml */
		$autoLoaderXml				=	Application::DI()->get( 'CBLib\AhaWow\AutoLoaderXml' );

		$element					=	$autoLoaderXml->loadXML( $route );

		if ( $element ) {
			if ( $view === null ) {
				$adminActionsModel	=	$element->getChildByNameAttr( 'actions', 'ui', $frontOrAdmin );

				if ( $adminActionsModel ) {
					$defaultAction	=	$adminActionsModel->getChildByNameAttr( 'action', 'name', 'default' );
					$actionRequest	=	$defaultAction->attributes( 'request' );
					$actionAction	=	$defaultAction->attributes( 'action' );

					if ( $actionRequest === '' ) {
						$view		=	$actionAction;
					}
				}
			}

			return $this->drawView( $option, $view, $element, $method );
		}

		throw new \LogicException( 'No route found for this request.', 404 );
	}

	/**
	 * Handles the backend plugin edition
	 *
	 * @param  string           $option
	 * @param  string           $action
	 * @param  SimpleXMLElement $element
	 * @param  string           $mode
	 * @return string                        HTML
	 *
	 * @throws \LogicException
	 */
	public function drawView( /** @noinspection PhpUnusedParameterInspection */ $option, $action, $element, $mode )
	{
		global $_CB_Backend_Menu;

		$ui												=	$this->clientId == 1 ? 2 : 1;

		$context										=	new Context();
		$pluginParams									=	$context->getParams();


		$interfaceUi									=	( $ui == 2 ? 'admin' : 'frontend' );
		$adminActionsModel								=	$element->getChildByNameAttr( 'actions', 'ui', $interfaceUi );
		if ( ! $adminActionsModel ) {
			$adminActionsModel							=	$element->getChildByNameAttr( 'actions', 'ui', 'all' );
		}
		if ( ! $adminActionsModel ) {
			throw new \LogicException( 'No ' . $interfaceUi . ' actions defined in XML' );
		}

		// Check permission if specified:
		if ( ! Access::authorised( $adminActionsModel ) ) {
			return CBTxt::T("Access to these actions is not authorized by the permissions of your user groups.");
		}

		// General-purpose extenders:
		$extenders										=	$adminActionsModel->xpath( 'extend' );
		/** @var SimpleXMLElement[] $extenders */
		foreach ( $extenders as $k => $extends ) {
			$error										=	RegistryEditView::extendXMLnode( $extenders[$k], $element, null, $context->getPluginObject() );
			if ( $error ) {
				echo $error;
			}
		}

		$found											=	false;

		$actionPath										=	 array();
		if ( $action ) {
			$actionsModel								=	$adminActionsModel->getChildByNameAttr( 'action', 'name', $action );
			$found										=	( $actionsModel != null );
			if ( $found ) {
				$requests								=	explode( ' ', $actionsModel->attributes( 'request' ) );
				$values									=	explode( ' ', $actionsModel->attributes( 'action'  ) );
				$actionPath								=	 array();
				for ( $i = 0, $n = count( $requests ); $i < $n; $i++ ) {
					$actionPath[$requests[$i]]			=	$values[$i];
				}
			}
		}

		if ( ! $found ) {
			// EVENT: select the event from URL and compute the selected $actionPath
			$found										=	false;
			$actionsModel								=	null;

			foreach ( $adminActionsModel->children() as $actionsModel ) {
				/** @var SimpleXMLElement $actionsModel */
				$request								=	$actionsModel->attributes( 'request' );
				if ( $request ) {
					$requests							=	explode( ' ', $request );
					$values								=	explode( ' ', $actionsModel->attributes( 'action'  ) );
					$actionPath							=	 array();
					for ( $i = 0, $n = count( $requests ); $i < $n; $i++ ) {
						$actionPath[$requests[$i]]		=	$this->input->get( $requests[$i], null, GetterInterface::STRING );

						// Temporary fix for older versions of CBSubs before CBSubs 4.0.0 stable to avoid warnings on ajax version checks:
						if ( ( $requests[$i] === 'view' ) && ( $actionPath['view'] === null ) ) {
							$actionPath['view']			=	$this->input->get( 'task', null, GetterInterface::STRING );
						}

						if ( $actionPath[$requests[$i]] != $values[$i] ) {
							break;
						}
					}
					if ( $i == $n ) {
						$found							=	true;
						break;
					}
				}
			}
		}

		if ( ! $found ) {
			$actionPath									=	 array();

			// try finding first default one:
			if ( $ui == 2 ) {
				$actionsModel							=	$adminActionsModel->getChildByNameAttr( 'action', 'request', '' );
			}
			if ( ! isset( $actionsModel ) ) {
				return CBTxt::T( 'AHAWOW_REQUESTED_ACTION_NOT_DEFINED_IN_XML', "Requested action '[ACTION]' not defined in XML.", array( '[ACTION]' => htmlspecialchars( $action ) ) );
			}
		}
		// Check permission if specified:
		if ( ! isset( $actionsModel ) || ( ! Access::authorised( $actionsModel ) ) ) {
			return CBTxt::T("This action is not authorized by the permissions of your user groups.");
		}
		if ( ! isset( $actionPath['view'] ) ) {
			$actionPath['view']							=	( $ui == 2 ? 'editPlugin' : '' );	//TODO: 2nd should come from target routing
		} elseif ( $actionPath['view'] != 'editPlugin' ) {
			$actionPath['act']							=	'';
		}

		// EVENT: fetch the input parameters from URL:
		$parametersNames								=	explode( ' ', $actionsModel->attributes( 'requestparameters' ));
		$parametersValues								=	array();
		foreach ($parametersNames as $paraNam ) {
			$parametersValues[$paraNam]					=	null;
			if ( strpos($paraNam, '[' ) === false ) {
				if ( trim( $paraNam ) ) {
					$parametersValues[$paraNam]			=	$this->input->get( $paraNam, '', GetterInterface::STRING );
				}
			} else {
				$matches								=	null;
				preg_match_all( '/(.*)(?:\[(.*)\])+/', $paraNam, $matches );
				if ( is_array( $matches ) && ( count( $matches ) >= 3 ) && ( count( $matches[2] ) >= 1 ) ) {
					$parametersValues[$paraNam] 		=	$this->input->get( $matches[1][0] . '.' . $matches[2][0], null, GetterInterface::STRING );
				}
			}
		}
		$keyValues										=	array();

		// Action-specific general extenders:
		$extenders										=	$adminActionsModel->xpath( 'actionspecific/extend' );
		/** @var SimpleXMLElement[] $extenders */
		foreach ( $extenders as $k => $extends ) {
			$error										=	RegistryEditView::extendXMLnode( $extenders[$k], $element, $actionsModel, $context->getPluginObject() );
			if ( $error ) {
				echo $error;
			}
		}

		// First extend what can be extended so the showview's below have a complete XML tree:

		/** @var $actionItem SimpleXMLElement */
		foreach ( $actionsModel->xpath( 'extend' ) as $actionItem ) {
			$error = RegistryEditView::extendXMLnode( $actionItem, $element, $actionsModel, $context->getPluginObject() );

			if ( $error ) {
				echo $error;
			}
		}

		/** @var $actionItem SimpleXMLElement */
		foreach ( $actionsModel->children() as $actionItem ) {

			// CONTROLLER: select the controller:
			switch ( $actionItem->getName() ) {

				case 'extend':
					// Treated just above.
					break;

				case 'showview':
					$viewName							=	$actionItem->attributes( 'view' );
					$showviewType						=	$actionItem->attributes( 'type' );
					$viewMode							=	$actionItem->attributes( 'mode' );

					// MODEL: load data to view:
					$dataModel							=	$actionItem->getElementByPath( 'data' );
					if ( $dataModel ) {
						$dataModelType					=	$dataModel->attributes( 'type' );

						$cbDatabase						=	$this->db;
						if ( in_array( $dataModelType, array( 'sql:row', 'sql:multiplerows', 'sql:field', 'parameters' ) ) ) {
							$xmlsql						=	new XmlQuery( $cbDatabase, null, $pluginParams );
							$data						=	$xmlsql->loadObjectFromData( $dataModel );
							if ( $data === null ) {
								return 'showview::sql:row: load failed: ' . $cbDatabase->getErrorMsg();
							}
							$dataModelValueName			=	$dataModel->attributes( 'value' );
							$dataModelValueType			=	$dataModel->attributes( 'valuetype' );
							// if the value of key is a parameter name, replace it with the corresponding parameter:
							$dataModelValueTypeArray	=	explode( ':', $dataModelValueType );
							if ( $dataModelValueTypeArray[0] == 'request' ) {
								if ( isset( $parametersValues[$dataModelValueName] ) ) {
									$dataModelValue		=	$parametersValues[$dataModelValueName];		// database escaping to int is done at request time
									$keyValues[$dataModelValueName]	=	$dataModelValue;
									unset( $parametersValues[$dataModelValueName] );
								} else {
									echo sprintf('showview::sql::row %s: request %s not in parameters of action.', $dataModel->attributes( 'name' ), $dataModelValueName );
								}
							}

							if ( $dataModelType == 'sql:field' ) {
								$data					=	new Registry( $data );
							}
						} elseif ( $dataModelType == 'class' ) {
							$dataModelClass				=	$dataModel->attributes( 'class' );
							$dataModelValue				=	$dataModel->attributes( 'value' );
							$dataModelValueName			=	$dataModelValue;
							$dataModelValueType			=	$dataModel->attributes( 'valuetype' );
							$dataModelValueTypeArray	=	explode( ':', $dataModelValueType );

							if ( $dataModelValueTypeArray[0] == 'request' ) {
								if ( isset( $parametersValues[$dataModelValueName] ) ) {
									$dataModelValue					=	$parametersValues[$dataModelValueName];

									$keyValues[$dataModelValueName]	=	$dataModelValue;

									unset( $parametersValues[$dataModelValueName] );
								} else {
									echo sprintf( 'showview::sql::row %s: request %s not in parameters of action.', $dataModel->attributes( 'name' ), $dataModelValue );
								}
							}

							if ( strpos( $dataModelClass, '::' ) === false ) {
								$data					=	new $dataModelClass( $cbDatabase ); // normal clas="className"

								/** @var $data TableInterface */
								$data->load( $dataModelValue );
							} else {
								$dataModelSingleton		=	explode( '::', $dataModelClass ); // class object loader from singleton: class="loaderClass::loadStaticMethor" with 1 parameter, the key value.

								if ( is_callable( $dataModelSingleton ) ) {
									if ( is_callable( array( $dataModelSingleton[0], 'getInstance' ) ) ) {
										$instance		=	call_user_func_array( array( $dataModelSingleton[0], 'getInstance' ), array( &$cbDatabase ) );
										$rows			=	call_user_func_array( array( $instance, $dataModelSingleton[1] ), array( $dataModelValue ) );
									} else {
										$rows			=	call_user_func_array( $dataModelSingleton, array( $dataModelValue ) );
									}
								} else {
									echo sprintf('showview::class %s: missing singleton class creator %s.', $dataModel->attributes( 'name' ), $dataModelClass );

									$std				=	new \stdClass();
									$rows				=	array( $std );
								}

								$data					=	$rows[0];
							}
						} else {
							$data						=	null;
							echo 'showview: Data model type ' . $dataModelType . ' is not implemented !';
						}
					} else {
						if ( $this->_data instanceof TableInterface || $this->_data instanceof \comprofilerDBTable ) {
							$data						=	$this->_data;
							$dataModelType				=	'sql:row';
						} elseif ( $this->_data instanceof ParamsInterface ) {
							$data						=	$this->_data;
							$dataModelType				=	'sql:row';
						} else {
							$data						=	null;
							$dataModelType				=	null;
						}
					}

					// VIEW: select view:
					$allViewsModels			=	$element->getElementByPath( 'views' );

					if ( $viewName && ( ( ! $showviewType ) || ( $showviewType == 'view' ) ) ) {
						////// $viewModel		= $allViewsModels->getChildByNameAttributes( 'view', array( 'name' => $viewName ) );
						$xpathUi			=	'/*/views/view[@ui="' . $interfaceUi . '" and @name="' . $viewName . '"]';
						$xpathAll			=	'/*/views/view[@ui="all" and @name="' . $viewName . '"]';
						$viewModel			=	$element->xpath( $xpathUi );
						if ( !$viewModel ) {
							$viewModel		=	$element->xpath( $xpathAll );
						}
						if ( !$viewModel ) {
							$viewModel			=	RegistryEditView::xpathWithAutoLoad( $element, $xpathUi );
						}
						if ( ! $viewModel ) {
							$viewModel		=	RegistryEditView::xpathWithAutoLoad( $element, $xpathAll );
						}
/*
						if ( ! $viewModel ) {
							$viewModel		=	RegistryEditView::xpathWithAutoLoad( $element, '/ * / views/view[not(@ui) and @name="' . $viewName . '"]' );
						}
*/

						if ( $viewModel ) {
							$viewModel				=	$viewModel[0];
						} else {
							return 'XML:showview: View ' . $viewName . ' not defined in ui ' . $interfaceUi . ' in XML';
						}
					} elseif ( $showviewType == 'xml' ) {
						// e.g.: <showview name="gateway_paymentstatus_information" mode="view" type="xml" file="processors/{payment_method}/edit.gateway" path="/*/views/view[@name=&quot;paymentstatusinformation&quot;]" mandatory="false" />
						$fromNode			=	$actionItem->attributes( 'path' );
						$fromFile			=	$actionItem->attributes( 'file' );
						if ( $fromNode && ( $fromFile !== null ) ) {
							// $this->substituteName( $fromFile, true );
							// $this->substituteName( $fromNode, false );
							$fromFile		=	$context->getPluginPath() . '/' . $fromFile . '.xml';
							if ( ( $fromFile === '' ) || is_readable( $fromFile ) ) {
								if ( $fromFile === '' ) {
									$fromRoot	=	$element;
								} else {
									$fromRoot	=	new SimpleXMLElement( $fromFile, LIBXML_NONET | ( defined('LIBXML_COMPACT') ? LIBXML_COMPACT : 0 ), true );
								}
								$viewModel		=	$fromRoot->xpath( $fromNode );
								if ( !$viewModel ) {
									trigger_error( 'Admin:showview: View ' . $viewName . ': file ' . $fromFile . ', path: ' . $fromNode . ' does not exist or is empty.', E_USER_NOTICE );
								}
								$viewModel		=	$viewModel[0];
							} else {
								throw new \LogicException( 'Admin:showview: View ' . $viewName . ': file ' . $fromFile . ' does not exist or is not readable.' );
							}
						} else {
							throw new \LogicException( 'Admin:showview: View ' . $viewName . ' file or path not defined..', E_USER_NOTICE );
						}
					} else {
						throw new \LogicException( 'Admin:showview: View ' . $viewName . ' not of supported type.', E_USER_NOTICE );
					}

					$viewUi			=	$viewModel->attributes( 'ui' );
					if ( $viewUi & ( $viewUi != 'all' ) && ( $viewUi != $interfaceUi ) ) {
						throw new \LogicException( 'showview: View ' . $viewName . ' not allowed for ' . $interfaceUi );
					}
					$extendedParser	=	$allViewsModels->getElementByPath( 'extendxmlparser' );

					$actionPath		=	array_merge( $actionPath, $keyValues );
					$options		=	array_merge( $this->getBaseOptions(), $actionPath, $parametersValues );
					if ( $ui == 2 ) {
						$options	=	array_merge( $options, $actionPath, $parametersValues );
					}
					$cbprevstate	=	$this->input->get( 'cbprevstate', null, GetterInterface::STRING );

					$params			=	new RegistryEditController( $this->input, $this->db, new Registry(), $viewModel, $element, $context->getPluginObject() );

					$displayData	=	$this->bindInput( $viewMode, $data );

					// Set the parameters with the $displayData :
					$registry						=	new Registry();
					$registry->load( $displayData );
					$registry->setStorage( $displayData );
					$params->setRegistry( $registry );
					$params->setPluginParams( $pluginParams );
					$params->setOptions( $options );
					if ( $extendedParser ) {
						$params->setExtendedViewParser( $extendedParser );
					}

					$extenders		=	$allViewsModels->xpath( 'extend' );
					foreach ($extenders as $extends ) {
						RegistryEditView::extendXMLnode( $extends, $element, $actionsModel, $context->getPluginObject() );
					}

					$viewType		= $viewModel->attributes( 'type' );
					switch ( $viewType ) {
						case 'params':
							if ( $mode == 'edit' ) {
								if ( ( $viewMode == 'edit' ) || ( $viewMode == 'show' ) ) {
									$viewTypeMode	=	( $viewMode == 'edit' ? 'param' : 'view' );

									if ( $ui == 2 ) {
										$htmlOutput						=	( $this->input->get( 'no_html', 0, GetterInterface::COMMAND ) != 1 ) && ( $this->input->get( 'format', null, GetterInterface::COMMAND ) != 'raw' );
										ActionViewAdmin::editPluginView( $options, $actionPath, $viewModel, $displayData, $params, $context->getPluginObject(), $viewTypeMode, $cbprevstate, $htmlOutput );
										$settings						=	null;
										$html							=	null;
									} else {
										/** @global \stdClass $_CB_Backend_Menu   : 'show' : only displays close button, 'edit' : special close button */
										global $_CB_Backend_Menu;
										$_CB_Backend_Menu = new \stdClass();

										$html							=	'';
										outputCbTemplate();
										outputCbJs();
										// $_CB_framework->outputCbJQuery( '' );
										initToolTip();
										$htmlFormatting					=	$viewModel->attributes( 'viewformatting' );
										if ( ! $htmlFormatting ) {
											global $ueConfig;
											if ( ( isset( $ueConfig['use_divs'] ) && ( $ueConfig['use_divs'] == 1 ) ) ) {
												$htmlFormatting			=	'div';
											} else {
												$htmlFormatting			=	'table';
											}
										}
										$settings						=	$params->draw( null, null, null, null, null, null, false, $viewTypeMode, $htmlFormatting );
									}

									if ( $ui == 2 ) {
										$_CB_Backend_Menu->mode			=	$viewMode;
										// Implemented in lower level in RegistryEditView:  $toolbarMenu = $viewModel->getElementByPath( 'toolbarmenu' );
									}
									if ( $ui != 2 ) {
										$actionView						=	new ActionView();
										$buttonSaveText					=	$actionsModel->attributes( 'label' );
										if ( ! $buttonSaveText ) {
											$buttonSaveText				=	'Save';
										}
										$buttonSaveText					=	CBTxt::Th( $buttonSaveText );			//	CBTxt::Th("Save"); For translation strings extraction

										$warning						=	null;
										if ( $viewTypeMode == 'param' ) {
											$settings					.=	'<div class="cbControlButtonsLine">' . "\n\t"
												.	'<span class="cb_button_wrapper">'
												.	'<button type="submit" name="actbutton" value="' . 'save' . $action . '" class="button cbregButton cbregSaveButton">'
												.	$buttonSaveText
												.	'</button>'
												.	'</span>'
												.	"\n\t"
												/*
												.	' &nbsp; '
												.	'<span class="cb_button_wrapper">'
												.	'<button type="reset" class="button cbregButton cbregUndoButton">'
												.	CBTxt::Th("Undo changes")
												.	'</button>'
												.	'</span>' . "\n"
												*/
												.	'</div>' . "\n"
											;
											$postedActionPath			=	$actionPath;
											unset( $postedActionPath['view'] );
											$formHiddens				=	array_merge( $this->getBaseOptions(), array( 'act' => 'save' . $action /* , 'cbprevstate' => $cbprevstate */ ), $postedActionPath );
										} else {
											$formHiddens				=	null;
										}
										$html							.=	$actionView->drawForm( $settings, $warning, $formHiddens, array_merge( $this->_getParams, array( 'act' => $action ) ), RegistryEditView::buildClasses( $viewModel ) );
										return $html;

									}
								} else {
									echo 'showview::params: mode is ' . $mode . ' but view mode is ' . $viewMode . ' instead of edit.';
								}
							} elseif ( in_array( $mode, array( 'apply', 'save', 'savenew', 'savecopy' ) ) ) {
								$this->savePluginView( $options, $actionPath, $keyValues, $parametersValues, $viewModel, $data, $params, $mode, $dataModelType, $context->getPluginObject(), $dataModel, $pluginParams, $cbprevstate, $ui );

								if ( ( $ui == 2 ) && ( $mode == 'apply' ) ) {
									// We arrive here only in case of saving error, as redirect (performed in savePluginView) would loose the inputs:
									return $this->drawView( $option, $action, $element, 'edit' );
								}
							} else {
								echo 'showview::params: view type params mode ' . $mode . ' is not implemented !';
							}

							break;

						default:
							echo 'showview::not-params: type of view ' . $viewType . ' is not implemented !';
							break;
					}
					break;

				default:
					echo 'action::not-showview: child xml element "' . $actionItem->getName() . '" of action is not implemented !';
					break;
			}
		}
		return null;
	}
	/**
	 * Flattens an hierarchical multi-levels array of arrays $a to a simple array of the values at leafs
	 * @param  array    $a  Array to flatten
	 * @param  boolean  $i  (do not use, used for recursion)
	 * @return array        Flattened array
	 */
	protected function _flattenArray( $a, $i = true ) {
		static $s	=	array();

		if ( $i ) {
			$s		=	array();
		}
		foreach ( $a as $v ) {
			if ( is_array( $v ) ) {
				$this->_flattenArray( $v, false );
			} elseif ( $v !== '' ) {
				$s[]	=	$v;
			}
		}
		return $s;
	}

	/**
	 * Binds the input to the $data if needed and returns a new object if bound
	 *
	 * @param  string          $viewMode
	 * @param  TableInterface  $data
	 * @return TableInterface
	 */
	protected function bindInput( $viewMode, $data ) {

		$input					=	$this->input->getNamespaceRegistry( 'post' )->asArray();

		if ( ( $viewMode == 'edit' ) && ( count( $input ) > 1 ) ) {
			// simple spoof check security
			cbSpoofCheck( 'plugin' );
			/* /NEW:
									RegistryEditView::setFieldsListArrayValues( true );
									$fields		=	$params->draw( null, null, null, null, null, null, false, 'param', 'fieldsListArray' );

									// New CB2.0 way for bind():
									foreach ( $fields as $key => $value ) {
										if ( property_exists( $data, $key ) ) {
											$data->$key	=	( is_array( $value ) ? json_encode( $value ) : $value );
										}
									}

								}
								$displayData	=	$data;
			*/

			if ( is_object( $data ) && method_exists( $data, 'bind' ) && method_exists( $data, 'check' ) ) {
				$displayData				=	clone $data;

				// Fix multi-selects and multi-checkboxes arrays to |*|-delimited strings:
				$postArray				=	$this->recursiveMultiSelectFix( $input );

				if ( ! $displayData->bind( $postArray, '', null, false ) ) {
					echo "<script type=\"text/javascript\"> alert('".$displayData->getError()."'); window.history.go(-1); </script>\n";
					exit();
				}
				// We don't need to perform a check() on data bind for display as we're not doing a store.. we'll check at store time:
//				if (!$displayData->check()) {
//					echo "<script type=\"text/javascript\"> alert('".$displayData->getError()."'); window.history.go(-1); </script>\n";
//					exit();
//				}
			} else {
				$displayData			=	$data;
			}
		} else {
			$displayData				=	$data;
		}
		return $displayData;
	}

	/**
	 * Fix multi-selects and multi-checkboxes arrays to |*|-delimited strings recursively.
	 *
	 * @param  ParamsInterface|array  $input  Input or array
	 * @return array
	 */
	protected function recursiveMultiSelectFix( $input )
	{
		$postArray			=	array();

		foreach ( $input as $k => $v ) {
			if ( is_array( $v ) ) {
				if ( ( count( $v ) == 0 ) || ( isset( $v[0] ) && ( ! is_array( $v[0] ) ) ) ) {
					// Empty and numeric arrays are saved as just a string:
					$v		=	implode( '|*|', $v );
				} else {
					// Other arrays are recursed:
					$v		=	$this->recursiveMultiSelectFix( $v );
				}
			}
			$postArray[$k]	=	$v;
		}

		return $postArray;
	}

	/**
	 * Saves the CB plugin view after an edit view form submit
	 *
	 * @param  array                     $options
	 * @param  array                     $actionPath
	 * @param  array                     $keyValues
	 * @param  array                     $parametersValues
	 * @param  SimpleXMLElement          $viewModel
	 * @param  TableInterface            $data
	 * @param  RegistryEditController    $params
	 * @param  string                    $mode
	 * @param  string                    $dataModelType
	 * @param  PluginTable               $plugin
	 * @param  SimpleXMLElement          $dataModel
	 * @param  RegistryInterface         $pluginParams
	 * @param  string                    $cbprevstate
	 * @param  int                       $ui
	 * @return null|string                                  NULL: ok, STRING: error
	 */
	protected function savePluginView( $options, $actionPath, $keyValues, $parametersValues, $viewModel, $data, $params, &$mode, $dataModelType, $plugin, $dataModel, $pluginParams, $cbprevstate, $ui )
	{
		global $_CB_framework;

		new cbTabs( false, 2, -1, false );		// prevents output of CB tabs js code until we are done with drawing (or redirecting)

		$resultingMsg	=	null;

		cbSpoofCheck( 'plugin' );

		$postArray		=	$this->input->getNamespaceRegistry( 'post' )->asArray();

		// List of variables to exclude from the $postArray:
		$exclude		=	array( 'option', 'cid', 'cbprevstate', cbSpoofField() );

		foreach ( $actionPath as $k => $v ) {
			$exclude[]	=	$k;
		}

		// Remove the exclude variables from the $postArray before being used in the below cases:
		foreach ( $exclude as $v ) {
			if ( isset( $postArray[$v] ) ) {
				unset( $postArray[$v] );
			}
		}

		// Fix multi-selects and multi-checkboxes arrays to |*|-delimited strings:
		$postArray					=	$this->recursiveMultiSelectFix( $postArray );

		foreach ( $postArray as $key => $value ) {
			if ( property_exists( $data, $key ) ) {
				$postArray[$key]	=	( is_array( $value ) ? json_encode( $value ) : $value );
			}
		}

		$errorMsg					=	null;

		switch ( $dataModelType ) {
			case 'sql:row':
				if ( $ui == 2 ) {
					if ( true !== ( $error = RegistryEditView::validateAndBindPost( $params, $postArray ) ) ) {
						$errorMsg	=	$error;
						break;
					}

					if (!$data->bind( $postArray )) {
						$errorMsg	=	$data->getError();
						break;
					}
				} else {
					RegistryEditView::setFieldsListArrayValues( true );
					$fields		=	$params->draw( null, null, null, null, null, null, false, 'param', 'fieldsListArray' );

					// New CB2.0 way for bind():
					foreach ( $fields as $key => $value ) {
						if ( property_exists( $data, $key ) ) {
							$data->$key	=	( is_array( $value ) ? json_encode( $value ) : $value );
						}
					}
				}
				if (!$data->check()) {
					$errorMsg		=	$data->getError();
					break;
				}

				$dataModelKey				=	$data->getKeyName();
				$dataModelValueOld			=	$data->$dataModelKey;

				if ( $mode == 'savecopy' ) {
					if ( ! $data->canCopy( $data ) ) {
						$errorMsg			=	$data->getError();
						break;
					}

					if ( ! $data->copy( $data ) ) {
						$errorMsg			=	$data->getError();
						break;
					}
				} else {
					if ( ! $data->store() ) {
						$errorMsg			=	$data->getError();
						break;
					}
				}

				$dataModelValue				=	$data->$dataModelKey;

				// Id changed; be sure to update the url encase of redirect:
				if ( count( $keyValues ) == 1 ) {
					$urlKeys						=	array_keys( $keyValues );
					$urlDataKey						=	$urlKeys[0];

					if ( $mode == 'savenew' ) {
						unset( $actionPath[$urlDataKey] );
					} elseif ( $dataModelValue != $dataModelValueOld ) {
						$actionPath[$urlDataKey]	=	$dataModelValue;
					}
				}

				if ( $data->hasFeature( 'checkout' ) ) {
					/** @var \CBLib\Database\Table\CheckedOrderedTable $data */
					$data->checkin();
				}

				$this->savePluginViewOrder( $data, $viewModel );

				$resultingMsg	=	$data->cbResultOfStore();
				break;

			case 'sql:field':			// <data name="params" type="sql:field" table="#__cbsubs_config" class="cbpaidConfig" key="id" value="1" valuetype="sql:int" />
				$dataModelName				=  $dataModel->attributes( 'name' );
				$dataModelKey				=  $dataModel->attributes( 'key' );
				$dataModelValue				=  $dataModel->attributes( 'value' );

				if ( $ui == 2 ) {
					if ( true !== ( $error = RegistryEditView::validateAndBindPost( $params, $postArray ) ) ) {
						$errorMsg			=	$error;
						break;
					}
				}

				$rawParams					=	array();
				$rawParams[$dataModelName]	=	json_encode( $postArray );

				$xmlsql						=	new XmlQuery( $this->db, null, $pluginParams );

				$xmlsql->process_data( $dataModel );

				if ( $dataModelValue ) {
					$result 				=	$xmlsql->queryUpdate( $rawParams );
				} else {
					$result					=	$xmlsql->queryInsert( $rawParams, $dataModelKey );
				}

				if ( ! $result ) {
					$errorMsg				=	$xmlsql->getErrorMsg();
				}
				break;

			case 'parameters':
				if ( $ui == 2 ) {
					if ( true !== ( $error = RegistryEditView::validateAndBindPost( $params, $postArray ) ) ) {
						$errorMsg			=	$error;
						break;
					}
				}

				$rawParams					=	array();
				$rawParams['params']		=	json_encode( $postArray );

				// $plugin = new PluginTable( $this->_db );
				// $plugin->load( $pluginId );
				if ( ! $plugin->bind( $rawParams ) ) {
					$errorMsg				=	$plugin->getError();
					break;
				}
				if (!$plugin->check()) {
					$errorMsg				=	$plugin->getError();
					break;
				}
				if (!$plugin->store()) {
					$errorMsg				=	$plugin->getError();
					break;
				}
				$plugin->checkin();

				$plugin->updateOrder( "type='".$plugin->getDbo()->getEscaped($plugin->type)."' AND ordering > -10000 AND ordering < 10000 " );

				$resultingMsg				=	$plugin->cbResultOfStore();
				break;

			case 'class':

				if ( $ui == 2 ) {
					if ( true !== ( $error = RegistryEditView::validateAndBindPost( $params, $postArray ) ) ) {
						$errorMsg	=	$error;
						break;
					}
				}

				if ( ! $data->bind( $postArray ) ) {
					$errorMsg		=	$data->getError();
					break;
				}
				if (!$data->check()) {
					$errorMsg		=	$data->getError();
					break;
				}
				if (!$data->store()) {
					$errorMsg		=	$data->getError();
					break;
				}
				if ( $data->hasFeature( 'checkout' ) ) {
					/** @var \CBLib\Database\Table\CheckedOrderedTable $data */
					$data->checkin();
				}

				$this->savePluginViewOrder( $data, $viewModel );

				$resultingMsg	=	$data->cbResultOfStore();
				break;

			case 'sql:multiplerows':
			default:
				echo 'Save error: showview data type: ' . $dataModelType . ' not implemented !';
				exit;
				break;
		}

		if ( $ui == 2 ) {
			$url					=	'index.php?option='. $options['option'] . '&view=' . $options['view'];
			if ( $options['view'] == 'editPlugin' ) {
				$url				.=	'&cid='. $options['pluginid'];
			}
			$url					=	$_CB_framework->backendUrl( $url );
		} else {
			$url					=	'index.php';
			if ( count( $options ) > 0 ) {
				$fixOptions			=	array();
				foreach ( $options as $k => $v ) {
					$fixOptions[$k]	=	$k . '=' . urlencode( $v );
				}
				$url				.=	'?' . implode( '&', $fixOptions );
			}
		}

		if ( isset( $data->title ) ) {
			$dataItem	=	CBTxt::T( $data->title );
		} elseif ( isset( $data->name ) ) {
			$dataItem	=	CBTxt::T( $data->name );
		} else {
			$dataItem	=	null;
		}

		if ( $errorMsg ) {
			if ( in_array( $mode, array( 'save', 'savenew', 'savecopy' ) ) ) {
				$mode	=	'apply';
			}

			$msg		=	CBTxt::T( 'FAILED_TO_SAVE_LABEL_ITEM_BECAUSE_ERROR', 'Failed to save [label] [item] because: [error]', array( '[label]' => $viewModel->attributes( 'label' ), '[item]' => $dataItem, '[error]' => $errorMsg ) );
			$msgType	=	'error';
		} else {
			$msg		=	CBTxt::T( 'SUCCESSFULLY_SAVED_LABEL_ITEM', 'Successfully saved [label] [item]', array( '[label]' => $viewModel->attributes( 'label' ), '[item]' => $dataItem ) );
			$msgType	=	'message';
		}

		switch ( $mode ) {
			case 'apply':
			case 'savenew':
			case 'savecopy':
				unset( $actionPath['view'] );
				foreach ( $actionPath as $k => $v ) {
					if ( $v !== '' ) {
						$url .= '&' . $k . '=' . $v;
					}
				}
				foreach ( $parametersValues as $k => $v ) {
					$url .= '&' . $k . '=' . $v;
				}
				if ( $cbprevstate ) {
					$url .= '&cbprevstate=' . $cbprevstate;
				}
				break;
			case 'save':
				if ( $cbprevstate ) {
					$prevUrl	=	base64_decode( $cbprevstate );			// $parametersValues[]		=	"'" . base64_encode( implode( '&', $cbprevstate ) ) . "'";
					if ( ! preg_match( '$[:/]$', $prevUrl ) ) {
						$prevUrl	=	str_replace( '&pluginid=', '&cid=', $prevUrl );
						if ( $ui == 2 ) {
							$url	=	$_CB_framework->backendUrl( 'index.php?' . $prevUrl );
						} else {
							$url	=	'index.php?' . $prevUrl;
						}
					}
				}
				break;
		}
		if ( $resultingMsg ) {
			if ( $ui != 2 ) {
				return $resultingMsg;		// in frontend, for now, don't redirect here: think this is right !
			} else {
				// If not an apply then change it to an apply so we can redisplay the view with the resulting message above it:
				if ( in_array( $mode, array( 'save', 'savenew', 'savecopy' ) ) ) {
					$mode	=	'apply';
				}

				echo $resultingMsg;
			}
		} else {
			if ( $ui != 2 ) {
				return null;				// in frontend, for now, don't redirect here: think this is right !
				// $url	=	cbUnHtmlspecialchars( cbSef( $url ) );
			}
			if ( ( $mode == 'apply' ) && $errorMsg ) {
				$_CB_framework->enqueueMessage( $msg, $msgType );
			} else {
				cbRedirect( $ui == 2 ? $url : cbSef( htmlspecialchars( $url ), false ), $msg, $msgType );
			}
		}
		return null;
	}

	/**
	 * @param TableInterface    $data
	 * @param SimpleXMLElement  $viewModel
	 */
	protected function savePluginViewOrder( $data, $viewModel ) {
		$ordering								=	$viewModel->xpath( '//param[@type="ordering"]' );

		if ( ! $ordering ) {
			$ordering							=	$viewModel->xpath( '//field[@type="ordering"]' );
		}

		/** @var $ordering SimpleXMLElement|null */
		if ( $ordering ) {
			foreach ( $ordering as $node ) {
				/** @var $node SimpleXMLElement */
				$where							=	'';
				$field							=	$node->attributes( 'name' );
				$orderingGroups					=	$node->getElementByPath( 'orderinggroups');

				/** @var $orderingGroups SimpleXMLElement|null */
				if ( $orderingGroups ) {
					foreach ( $orderingGroups->children() as $group ) {
						/** @var $group SimpleXMLElement */
						$orderingFieldName		=	$group->attributes( 'name' );

						if ( ( $group->getName() == 'ordering' ) && $orderingFieldName && array_key_exists( $orderingFieldName, get_object_vars( $data ) ) ) {
							$where				.=	$data->getDbo()->NameQuote( $orderingFieldName ) . " = " . $data->getDbo()->Quote( $data->$orderingFieldName ) . " AND ";
						}
					}
				}

				if ( $data->hasFeature( 'ordered', $field ) ) {
					/** @var \CBLib\Database\Table\OrderedTable $data */
					$data->updateOrder( $where . $data->getDbo()->NameQuote( $field ) . " > -10000 AND " . $data->getDbo()->NameQuote( $field ) . " < 10000" );
				}
			}
		}
	}
}
