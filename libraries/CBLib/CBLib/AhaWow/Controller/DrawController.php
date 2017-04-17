<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 11/12/13 5:18 PM $
* @package CBLib\AhaWow
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\AhaWow\Controller;

use CBLib\AhaWow\Access;
use CBLib\AhaWow\View\RegistryEditView;
use CBLib\Database\Table\TableInterface;
use CBLib\Input\InputInterface;
use CBLib\Language\CBTxt;
use CBLib\Registry\Registry;
use CBLib\Xml\SimpleXMLElement;
// Temporarily:
use moscomprofilerHTML;
use \cbPageNav;

defined('CBLIB') or die();

/**
 * CBLib\AhaWow\DrawController Class implementation
 * 
 */
class DrawController {
	/** CB page navigator (and ordering)
	 *  @var cbPageNav */
	var $pageNav;

	/** @var SimpleXMLElement */
	var $_tableBrowserModel;

	/**  <actions> element
	 * 	@var SimpleXMLElement*/
	var $_actions;

	/**
	 * Options from input request
	 * @var string[]
	 */
	var $_options;

	/**
	 * Name attribute of the view model
	 * @var string
	 */
	var $_tableName;

	/**
	 * Search name
	 * @var string
	 */
	var $_search;

	/**
	 * Weather it has quick-search fields
	 * @var bool
	 */
	var $_searchFields;

	/**
	 * Orderby name
	 * @var string
	 */
	var $_orderby;

	/**
	 * Weather it has orderby fields
	 * @var bool
	 */
	var $_orderbyFields;

	/**
	 * Filters
	 *
	 * @var SimpleXMLElement[][]
	 */
	var $_filters;

	/**
	 * Batch Process
	 *
	 * @var SimpleXMLElement[][]
	 */
	var $_batchprocess;

	/**
	 * Statistic footers
	 *
	 * @var SimpleXMLElement[]
	 */
	var $_statistics;

	/**
	 * Control name
	 *
	 * @var string
	 */
	var $_control_name;

	/**
	 * @var InputInterface
	 */
	protected $input			=	null;

	/**
	 * Constructor
	 *
	 * @param  InputInterface      $input              The user form input
	 * @param  SimpleXMLElement    $tableBrowserModel  The model for the browser
	 * @param  SimpleXMLElement    $actions            The actions node
	 * @param  string[]            $options            The input request options
	 */
	public function __construct( InputInterface $input, SimpleXMLElement $tableBrowserModel, SimpleXMLElement $actions = null, $options ) {
		$this->input				=	$input;
		$this->_tableBrowserModel	=	$tableBrowserModel;
		$this->_actions				=	$actions;
		$this->_options				=	$options;

		$this->_tableName			= $tableBrowserModel->attributes( 'name' );			// TBD: does this really belong here ???!
	}

	/**
	 * Forms the field name, e.g.: search, toggle, idcid[], order[], subtask, table[fieldname][]
	 *
	 * @param  string  $fieldName  The XML field name
	 * @return string              The HTML field name
	 */
	function fieldName( $fieldName ) {
		$arrayBrackets = '';
		if ( substr( $fieldName, -2 ) == '[]' ) {
			$fieldName = substr( $fieldName, 0 , -2 );
			$arrayBrackets = '[]';
		}
		return $this->_tableName . '[' . $fieldName . ']' . $arrayBrackets;
	}

	/**
	 * Returns the HTML field id cb{tablename}{$fieldId}{$number}
	 *
	 * @param  string  $fieldId    The field Id
	 * @param  int     $number     The number
	 * @param  bool    $htmlspecs  (unused)
	 * @return string              The HTML field Id
	 */
	function fieldId( $fieldId, $number=null,  /** @noinspection PhpUnusedParameterInspection */ $htmlspecs=true ) {
		// id
		return 'cb' . $this->_tableName . $fieldId . $number;
	}

	/**
	 * Returns the task name
	 *
	 * @param string  $subTask    (unused)
	 * @param bool    $htmlspecs  (unused)
	 * @return string             The task name from input request options
	 */
	function taskName( /** @noinspection PhpUnusedParameterInspection */ $subTask,  /** @noinspection PhpUnusedParameterInspection */  $htmlspecs=true ) {
		// for saveorder,  publish, unpublish, orderup, orderdown
		return $this->_options['view'];
	}

	/**
	 * Returns the field value if it's 'search'
	 *
	 * @param  string  $fieldName  The field name
	 * @return string              The value for the quick search
	 */
	function fieldValue( $fieldName ) {
		if ( $fieldName == 'search' ) {
			return $this->_search;
		} elseif ( $fieldName == 'orderby' ) {
			return $this->_orderby;
		}
		return '';
	}

	/**
	 * Returns the field-name for 'subtask'
	 *
	 * @param  bool    $htmlspecs  (unused)
	 * @return string              The HTML field name for the sub-task
	 */
	function subtaskName( /** @noinspection PhpUnusedParameterInspection */ $htmlspecs = true ) {
		// saveorder,  publish, unpublish, orderup, orderdown
		return $this->fieldName( 'subtask' );
	}

	/**
	 * Returns the value of the $subTask
	 *
	 * @param  string  $subTask   The subtask
	 * @param  bool   $htmlspecs  (unused)
	 * @return string
	 */
	function subtaskValue( $subTask, /** @noinspection PhpUnusedParameterInspection */ $htmlspecs = true  ) {
		return $subTask;
	}

	/**
	 * Sets the search
	 *
	 * @param  string  $search        The quick-search input string value
	 * @param  bool    $searchFields  Weather we have quick-search fields
	 */
	function setSearch( &$search, $searchFields ) {
		$this->_search			=	$search;
		$this->_searchFields	=	$searchFields;
	}

	/**
	 * Returns if it has search fields
	 *
	 * @return bool  Has search fields
	 */
	function hasSearchFields( ) {
		return ( $this->_searchFields == true );
	}

	/**
	 * Renders the quick-search field html
	 *
	 * @return string  The HTML rendering for the quick-search field
	 */
	function quicksearchfields() {
		$return						=	'';

		if ( $this->hasSearchFields() ) {
			$quickSearch			=	$this->_tableBrowserModel->getElementByPath( 'quicksearchfields' );
			$quickSearchLabel		=	trim( CBTxt::T( $quickSearch->attributes( 'label' ) ) );
			$quickSearchPlaceholder	=	htmlspecialchars( trim( CBTxt::T( $quickSearch->attributes( 'placeholder' ) ) ) );
			$quickSearchSize		=	(int) $quickSearch->attributes( 'size' );

			if ( $this->pageNav !== null ) {
				$quickSearchJS		=	$this->pageNav->limitstartJs(0);
			} else {
				$quickSearchJS		=	'cbParentForm(this).submit();';
			}

			$return					.=	( $quickSearchLabel ? '<label for="' . $this->fieldId( 'search' ) . '">' . $quickSearchLabel . ':</label> ' : null )
									.	'<div class="input-group clearfix" style="display: inline-block; vertical-align: bottom;">'
									.		'<input type="text" id="' . $this->fieldId( 'search' ) . '" name="' . $this->fieldName( 'search' ) . '" value="' . $this->fieldValue( 'search' ) . '"' . ( $quickSearchPlaceholder ? ' placeholder="' . $quickSearchPlaceholder . '"' : null ) . ( $quickSearchSize ? ' size="' . $quickSearchSize . '"' : null ) . ' class="form-control" onchange="' . $quickSearchJS . '" />'
									.		'<span class="input-group-btn" style="float: left;">'
									.			'<button class="btn btn-default" type="button" onclick="' . $quickSearchJS . '"><span class="fa-raw fa-search"></span></button>'
									.		'</span>'
									.	'</div>';
		}

		return $return;
	}

	/**
	 * Sets the orderby
	 *
	 * @param  string  $orderby  The orderby sorting option
	 */
	function setOrderBy( $orderby ) {
		$this->_orderby	=	$orderby;
	}

	/**
	 * Returns if it has search fields
	 *
	 * @return bool  Has search fields
	 */
	function hasOrderbyFields( ) {
		static $cache					=	array();

		if ( ! isset( $cache[$this->_tableName] ) ) {
			$orderBy					=	$this->_tableBrowserModel->getElementByPath( 'orderby' );

			$cache[$this->_tableName]	=	( $orderBy && $orderBy->getElementByPath( 'ordergroup' ) );
		}

		return $cache[$this->_tableName];
	}

	/**
	 * Renders the orderby field html
	 *
	 * @return string  The HTML rendering for the orderby field
	 */
	function orderbyfields() {
		/** @var SimpleXMLElement $orderbyFields */
		$orderbyFields				=	$this->_tableBrowserModel->getElementByPath( 'orderby' );
		$orderbyOptions				=	array();
		$orderbyOptions[]			=	moscomprofilerHTML::makeOption( '', CBTxt::T( '- Select Sort By -' ) );

		foreach ( $orderbyFields as $orderbyField ) {
			/** @var SimpleXMLElement $orderbyField */
			if ( $orderbyField->getName() == 'ordergroup' ) {
				$val				=	$orderbyField->attributes( 'name' );
				$label				=	CBTxt::T( $orderbyField->attributes( 'label' ) );

				$orderbyOptions[]	=	moscomprofilerHTML::makeOption( $val, ( $label !== '' ? $label : $val ) );
			}
		}

		if ( $this->pageNav !== null ) {
			$orderbyJS				=	$this->pageNav->limitstartJs(0);
		} else {
			$orderbyJS				=	'cbParentForm(this).submit();';
		}

		return moscomprofilerHTML::selectList( $orderbyOptions, $this->fieldName( 'orderby' ), 'class="form-control" onchange="' . htmlspecialchars( $orderbyJS ) . '"', 'value', 'text', $this->fieldValue( 'orderby' ), 0, true, null, false );
	}

	/**
	 * returns HTML code for the filters
	 *
	 * @param  RegistryEditView  $editRowView     The edit view for the row
	 * @param  string            $htmlFormatting  The HTML formatting for the filters ( 'table', 'td', 'none' )
	 * @return array
	 */
	function filters( &$editRowView, $htmlFormatting = 'none' ) {
		$items						=	$this->xmlItems( $this->_filters, 'filter', $editRowView, $htmlFormatting );

		if ( count( $items ) > 0 ) {
			if ( $this->pageNav !== null ) {
				$searchButtonJs		=	$this->pageNav->limitstartJs(0);
			} else {
				$searchButtonJs		=	'cbParentForm(this).submit();';
			}

			$items[]				=	'<div class="cbSearchSubmit">'
									.		'<button type="button" class="cbSearchSubmitButton btn btn-primary" onclick="' . htmlspecialchars( $searchButtonJs ) . '">'
									.			CBTxt::T( 'Search' )
									.		'</button>'
									.	'</div>';
		}

		return $items;
	}

	/**
	 * returns HTML code for the filters
	 *
	 * @param  RegistryEditView  $editRowView     The edit view for the row
	 * @param  string            $htmlFormatting  The HTML formatting for the filters ( 'table', 'td', 'none' )
	 * @return array
	 */
	function batchprocess( &$editRowView, $htmlFormatting = 'none' ) {
		$items					=	$this->xmlItems( $this->_batchprocess, 'batch', $editRowView, $htmlFormatting );

		if ( count( $items ) > 0 ) {
			$batchButtonJs		= 'javascript:cbDoListTask(this, '				// cb					//TBD: this is duplicate of pager.
								. "'" . addslashes( $this->taskName( false ) ) . "','" 				// task
								. addslashes( $this->subtaskName( false ) ) . "','" 					// subtaskName
								. addslashes( $this->subtaskValue( 'batchrows', false ) ) . "','" 	// subtaskValue
								. addslashes( $this->fieldId( 'id', null, false ) ) . "'"				// fldName
								. ");";

			$items[]			=	'<div class="cbBatchSubmit">'
								.		'<button type="button" class="cbBatchSubmitButton btn btn-primary" onclick="' . htmlspecialchars( $batchButtonJs ) . '">'
								.			CBTxt::T( 'Process' )
								.		'</button>'
								.	'</div>';
		}

		return $items;
	}

	/**
	 * returns HTML code for the filters
	 *
	 * @param  SimpleXMLElement[]  $items           The xml items to parse output
	 * @param  string              $type            The type of xml items (e.g. filter, batch, import, export...)
	 * @param  RegistryEditView    $editRowView     The edit view for the row
	 * @param  string              $htmlFormatting  The HTML formatting for the filters ( 'table', 'td', 'none' )
	 * @return array
	 */
	function xmlItems( $items, $type, $editRowView, $htmlFormatting = 'none' ) {
		$lists 								=	array();

		if ( count( $items ) > 0 ) {
			$valueObj						=	new Registry();
			$saveName						=	array();

			foreach ( $items as $k => $v ) {
				$valname					=	$type . '_' . $v['name'];

				$valueObj->set( $valname, $v['value'] );

				/** @var $v SimpleXMLElement[] */
				$saveName[$k]				=	$v['xml']->attributes( 'name' );

				/** @noinspection PhpUndefinedMethodInspection */
				$items[$k]['xml']->addAttribute( 'name', $type . '_' . $saveName[$k] );

				/** @var $v array */
				$editRowView->setSelectValues( $v['xml'], $v['selectValues'] );
			}

			$renderedViews					=	array();

			foreach ( $items as $k => $v ) {
				/** @var $v SimpleXMLElement[] */
				$viewName					=	$v['xml']->attributes( 'view' );

				if ( $viewName ) {
					/** @noinspection PhpUndefinedMethodInspection */
					$view					=	$items[$k]['xmlparent']->getChildByNameAttr( 'view', 'name', $viewName );

					if ( ! $view ) {
						echo 'filter view ' . $viewName . ' not defined in filters';
					}
				} else {
					/** @noinspection PhpUndefinedMethodInspection */
					$view					=	$items[$k]['xml']->getElementByPath( 'view' );
				}

				$value						=	$items[$k]['value'];

				if ( ( $value !== null ) && ( $value !== '' ) ) {
					/** @noinspection PhpUndefinedMethodInspection */
					$classes				=	$items[$k]['xml']->attributes( 'cssclass' );

					/** @noinspection PhpUndefinedMethodInspection */
					$items[$k]['xml']->addAttribute( 'cssclass', $classes . ' focus' );
				}

				if ( $view ) {
					if ( ( ! $viewName ) || ! in_array( $viewName, $renderedViews ) ) {
						/** @var SimpleXMLElement $view */
						$htmlFormattingView	=	$view->attributes( 'viewformatting' );
						if ( $htmlFormattingView == '' ) {
							$htmlFormattingView	=	$htmlFormatting;
						}
						$lists[$k]			=	'<div class="cb' . htmlspecialchars( ucfirst( $type ) ) . ' cb' . htmlspecialchars( ucfirst( $type ) ) . 'View">'
											.		$editRowView->renderEditRowView( $view, $valueObj, $this, $this->_options, 'param', $htmlFormattingView )
											.	'</div>';
					}

					if ( $viewName ) {
						$renderedViews[]	=	$viewName;
					}
				} else {
					$editRowView->pushModelOfData( $valueObj );
					$editRowView->extendParamAttributes( $items[$k]['xml'], $this->control_name() );

					$result					=	$editRowView->renderParam( $items[$k]['xml'], $this->control_name(), false );

					$editRowView->popModelOfData();

					if ( $result[0] || $result[1] || $result[2] ) {
						$lists[$k]			=	'<div class="cb' . htmlspecialchars( ucfirst( $type ) ) . '">'
											.		( $result[0] ? '<span class="cbLabelSpan">' . $result[0] . '</span> ' : null )
											.		'<span class="cbFieldSpan">' . $result[1] . '</span>'
											.		( $result[2] ? ' <span class="cbDescrSpan">' . $result[2] . '</span>' : null )
											.	'</div>';
					}
				}
			}

			foreach ( $items as $k => $v ) {
				/** @noinspection PhpUndefinedMethodInspection */
				$items[$k]['xml']->addAttribute( 'name', $saveName[$k] );
			}
		}

		return $lists;
	}

	/**
	 * Sets the filters
	 *
	 * @param  array  $filters  The filters ( SimpleXMLElement[][] )
	 * @return void
	 */
	function setFilters( $filters ){
		$this->_filters			=	$filters;
	}

	/**
	 * Sets the batchprocess
	 *
	 * @param  SimpleXMLElement[]  $batchprocess  The batchprocess XML fields
	 * @return void
	 */
	function setBatchProcess( $batchprocess ){
		$this->_batchprocess	=	$batchprocess;
	}

	/**
	 * Sets the statistics fields
	 *
	 * @param  SimpleXMLElement[]  $statsArray  The statistics XML fields
	 * @return void
	 */
	function setStatistics( $statsArray ) {
		$this->_statistics		=	$statsArray;
	}

	/**
	 * Gets the statistics fields
	 *
	 * @return SimpleXMLElement[]  The statistics XML fields
	 */
	function & getStatistics( ) {
		return $this->_statistics;
	}

	/**
	 * Returns the control name
	 *
	 * @return string  The control name
	 */
	function control_name( ) {
		return $this->_control_name;
	}

	/**
	 * Sets the contorl name
	 *
	 * @param  string  $control_name  The control name
	 * @return void
	 */
	function setControl_name( $control_name ) {
		$this->_control_name = $control_name;
	}

	/**
	 * @param  string             $cbUri             The CB-URI (cbo;,,,)
	 * @param  SimpleXMLElement   $sourceElem        The XML element from which the URL is computed
	 * @param  TableInterface     $data              The data of the object for dynamic URL request values
	 * @param  int                $id                The id of the current row
	 * @param  bool               $htmlspecialchars  If htmlspecialchars should be made for this
	 * @param  bool               $inPage            URL target: true: html (full page), false: raw (only center component content)
	 * @return string                                The URL
	 */
	function drawUrl( $cbUri, SimpleXMLElement $sourceElem, $data, /** @noinspection PhpUnusedParameterInspection */ $id, $htmlspecialchars = true, $inPage = true ) {
		global $_CB_framework;

		if ( ! Access::authorised( $sourceElem ) ) {
			return null;
		}

		$ui						=	$_CB_framework->getUi();
		$actionName				=	null;

		if ( substr( $cbUri, 0, 4 ) == 'cbo:' ) {
			$subTaskValue	=	substr( $cbUri, 4 );
			switch ( $subTaskValue ) {
				/** @noinspection PhpMissingBreakStatementInspection */
				case 'newrow':
					// $id	=	0;
				// fallthrough: no break on purpose.
				case 'rowedit':				//TBD this is duplicate of below
					$baseUrl	=	'index.php';
					if ( $this->_options['view'] == 'editPlugin' ) {
						$task	=	$this->_options['view'];
					} else {
						$task	=	'editrow';
					}
					$baseUrl	.=		'?option=' . $this->_options['option'] . '&view=' . $task;
					if ( isset( $this->_options['pluginid'] ) ) {
						$baseUrl .=		'&cid=' . $this->_options['pluginid'];
					}
					$url	= $baseUrl . '&table=' . $this->_tableBrowserModel->attributes( 'name' ) . '&action=editrow';		// below: . '&tid=' . $id;
					break;
				case 'saveorder':
				case 'editrows':
				case 'deleterows':
				case 'copyrows':
				case 'updaterows':
				case 'publish':
				case 'unpublish':
				case 'enable':
				case 'disable':
				default:
					$url	= 'javascript:cbDoListTask(this, '				// cb					//TBD: this is duplicate of pager.
						. "'" . addslashes( $this->taskName( false ) ) . "','" 				// task
						. addslashes( $this->subtaskName( false ) ) . "','" 					// subtaskName
						. addslashes( $this->subtaskValue( $subTaskValue, false ) ) . "','" 	// subtaskValue
						. addslashes( $this->fieldId( 'id', null, false ) ) . "'"				// fldName
						. ");";
					break;
			}

		} elseif ( substr( $cbUri, 0, 10 ) == 'cb_action:' ) {

			$actionName				=	substr( $cbUri, 10 );
			$action					=	$this->_actions->getChildByNameAttr( 'action', 'name', $actionName );
			if ( $action ) {
				if ( ! Access::authorised( $action ) ) {
					return null;
				}

				$requestNames		=	explode( ' ', $action->attributes( 'request' ) );
				$requestValues		=	explode( ' ', $action->attributes( 'action' ) );
				$parametersValues	=	explode( ' ', $action->attributes( 'parameters' ) );

				$baseUrl			=	'index.php';
				$baseUrl			.=	'?';
				$baseRequests		=	array( 'option' => 'option', 'view' => 'view', 'cid' => 'pluginid' );
				$urlParams			=	array();
				foreach ( $baseRequests as $breq => $breqOptionsValue ) {
					if ( ( ! ( in_array( $breq, $requestNames ) || in_array( $breq, $parametersValues ) ) ) && isset( $this->_options[$breqOptionsValue] ) ) {
						$urlParams[$breq]	=	$breq . '=' . $this->_options[$breqOptionsValue];
					}
				}

				for ( $i = 0, $n = count( $requestNames ); $i < $n; $i++ ) {
					$urlParams[$requestNames[$i]]	=	$requestNames[$i] . '=' . $requestValues[$i];				// other parameters = paramvalues added below
				}
				$url		=	$baseUrl . implode( '&', $urlParams );
			} else {
				$url = "#action_not_defined:" . $actionName;
			}

		} else {

			$url = cbUnHtmlspecialchars( $cbUri );

		}

		if ( cbStartOfStringMatch( $url, 'index.php' ) ) {
			// get the parameters of action/link from XML :
			$parametersNames				=	explode( ' ', $sourceElem->attributes( 'parameters' ) );
			$parametersValues				=	explode( ' ', $sourceElem->attributes( 'paramvalues' ) );
			$parametersValuesTypes			=	explode( ' ', $sourceElem->attributes( 'paramvaluestypes' ) );

			// generate current action (and parameters ?) as cbprevstate
			$cbprevstate					=	array();
			foreach ( $this->_options as $req => $act ) {
				if ( $req && $act && ! in_array( $req, array( 'cbprevstate' ) ) ) {
					$cbprevstate[]			=	$req . '=' . $act;
				}
			}
			$parametersNames[]				=	'cbprevstate';
			$parametersValues[]				=	"'" . base64_encode( implode( '&', $cbprevstate ) ) . "'";

			// finally generate URL:
			for ( $i = 0, $n = count( $parametersNames ); $i < $n; $i++ ) {
				$nameOfVariable				=	$parametersValues[$i];
				if ( $nameOfVariable != '' ) {

					if ( isset( $parametersValuesTypes[$i] ) && $parametersValuesTypes[$i] ) {
						if ( $parametersValuesTypes[$i] == 'sql:field' ) {
							if ( is_callable( array( $data, 'get' ) ) ) {
								$nameOfVariable	=	$data->get( $nameOfVariable );
							} else {
								$nameOfVariable	=	$data->$nameOfVariable;
							}
						} else {
							// $nameOfVariable untouched
						}
					} elseif ( ( substr( $nameOfVariable, 0, 1 ) == "'" ) && ( substr( $nameOfVariable, -1 ) == "'" ) ) {
						$nameOfVariable		=	substr( $nameOfVariable, 1, -1 );
					} else {
						if ( is_callable( array( $data, 'get' ) ) ) {
							$nameOfVariable	=	$data->get( $nameOfVariable );
						} else {
							$nameOfVariable	=	$data->$nameOfVariable;
						}
					}
					$url					.=	'&' . $parametersNames[$i] . '=' . urlencode( $nameOfVariable );
				}
			}

			if ( $ui == 2 ) {
				$url						=	$_CB_framework->backendUrl( $url, $htmlspecialchars, ( $inPage ? 'html' : 'component' ) );
			} else {
				$url						=	cbSef( $url, $htmlspecialchars, ( $inPage ? 'html' : 'component' ) );
			}
		} elseif ( $htmlspecialchars ) {
			$url							=	htmlspecialchars( $url );
		}
		return $url;
	}

	/**
	 * Draws the page navigator
	 *
	 * @param $positionType
	 */
	function drawPageNvigator( $positionType /* , $viewModelElement ??? */ ) {
	}

	/**
	 * Creates the page navigator object
	 *
	 * @param  int    $total       Total number of rows
	 * @param  int    $limitstart  First entry
	 * @param  int    $limit       Number of entries
	 * @param  int[]  $limits      Limits to propose in the pagination setting in the table form
	 * @return void
	 */
	function createPageNvigator( $total, $limitstart, $limit, $limits = null ) {
		cbimport( 'cb.pagination' );
		$this->pageNav = new cbPageNav( $total, $limitstart, $limit, array( &$this, 'fieldName' ), $this );
		$this->pageNav->setControllerView( $this );
		if ( $limits ) {
			$this->pageNav->setLimits( $limits );
		}
	}
} 