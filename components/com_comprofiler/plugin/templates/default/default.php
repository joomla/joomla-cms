<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class CBProfileView_html_default extends cbProfileView
{
	private $wLeft;
	private $wMiddle;
	private $wRight;
	private $nCols;

	/**
	 * Draws the layout part for $part
	 *
	 * @param  string  $part  Layout part to render
	 * @return string         HTML output
	 */
	public function draw( $part = null )
	{
		global $ueConfig;

		if ( $part == 'Profile' ) {
			$this->wLeft			=	( isset( $this->userViewTabs['cb_left'] ) ? 100 : 0 );
			$this->wMiddle			=	( isset( $this->userViewTabs['cb_middle'] ) ? 100 : 0 );
			$this->wRight			=	( isset( $this->userViewTabs['cb_right'] ) ? 100 : 0 );
			$this->nCols			=	intval( ( $this->wLeft + $this->wMiddle + $this->wRight ) / 100 );

			switch ( $this->nCols ) {
				case 2 :
					$this->wLeft	=	( $this->wLeft ? (int) $ueConfig['left2colsWidth'] : 0 );
					$this->wMiddle	=	( $this->wMiddle ? ( $this->wLeft ? ( 100 - (int) $ueConfig['left2colsWidth'] ) : (int) $ueConfig['left2colsWidth'] ) : 0 );
					$this->wRight	=	( $this->wRight ? ( 100 - (int) $ueConfig['left2colsWidth'] ) : 0 );
					break;
				case 3 :
					$this->wLeft	=	(int) $ueConfig['left3colsWidth'];
					$this->wMiddle	=	( 100 - (int) $ueConfig['left3colsWidth'] - (int) $ueConfig['right3colsWidth'] );
					$this->wRight	=	(int) $ueConfig['right3colsWidth'];
					break;
			}
		}

		return parent::draw( $part );
	}

	/**
	 * Converts a %-width into a CSS class
	 *
	 * @param  int     $width  width expressed in % (1-100)
	 * @return string          Corresponding CSS class in 12th of width
	 */
	private function widthToBootstrap( $width )
	{
		if ( $width < 14 ) {
			$class	=	'cbColumn1 col-sm-1';
		} elseif ( $width < 20 ) {
			$class	=	'cbColumn2 col-sm-2';
		} elseif ( $width < 30 ) {
			$class	=	'cbColumn3 col-sm-3';
		} elseif ( $width < 39 ) {
			$class	=	'cbColumn4 col-sm-4';
		} elseif ( $width < 48 ) {
			$class	=	'cbColumn5 col-sm-5';
		} elseif ( $width < 57 ) {
			$class	=	'cbColumn6 col-sm-6';
		} elseif ( $width < 65 ) {
			$class	=	'cbColumn7 col-sm-7';
		} elseif ( $width < 74 ) {
			$class	=	'cbColumn8 col-sm-8';
		} elseif ( $width < 82 ) {
			$class	=	'cbColumn9 col-sm-9';
		} elseif ( $width < 90 ) {
			$class	=	'cbColumn10 col-sm-10';
		} elseif ( $width < 99 ) {
			$class	=	'cbColumn11 col-sm-11';
		} else {
			$class	=	'cbColumn12 col-sm-12';
		}

		return $class;
	}

	/**
	 * Renders by ECHO the profile view
	 *
	 * @return void
	 */
	protected function _renderProfile( )
	{
		global $_CB_framework;

		$return							=	null;

		if ( isset( $this->userViewTabs['cb_head'] ) ) {
			$return						.=	'<div class="cbPosHead">'
										.		$this->userViewTabs['cb_head']
										.	'</div>';
		}

		$canvasMenu						=	( isset( $this->userViewTabs['canvas_menu'] )				? $this->userViewTabs['canvas_menu']				: null );
		$canvasBackground				=	( isset( $this->userViewTabs['canvas_background'] )			? $this->userViewTabs['canvas_background']			: null );
		$canvasStatsTop					=	( isset( $this->userViewTabs['canvas_stats_top'] )			? $this->userViewTabs['canvas_stats_top']			: null );
		$canvasStatsMiddle				=	( isset( $this->userViewTabs['canvas_stats_middle'] )		? $this->userViewTabs['canvas_stats_middle']		: null );
		$canvasStatsBottom				=	( isset( $this->userViewTabs['canvas_stats_bottom'] )		? $this->userViewTabs['canvas_stats_bottom']		: null );
		$canvasPhoto					=	( isset( $this->userViewTabs['canvas_photo'] )				? $this->userViewTabs['canvas_photo']				: null );
		$canvasTitleTop					=	( isset( $this->userViewTabs['canvas_title_top'] )			? $this->userViewTabs['canvas_title_top']			: null );
		$canvasTitleMiddle				=	( isset( $this->userViewTabs['canvas_title_middle'] )		? $this->userViewTabs['canvas_title_middle']		: null );
		$canvasTitleBottom				=	( isset( $this->userViewTabs['canvas_title_bottom'] )		? $this->userViewTabs['canvas_title_bottom']		: null );
		$canvasMainLeft					=	( isset( $this->userViewTabs['canvas_main_left'] )			? $this->userViewTabs['canvas_main_left']			: null );
		$canvasMainLeftStatic			=	( isset( $this->userViewTabs['canvas_main_left_static'] )	? $this->userViewTabs['canvas_main_left_static']	: null );
		$canvasMainMiddle				=	( isset( $this->userViewTabs['canvas_main_middle'] )		? $this->userViewTabs['canvas_main_middle']			: null );
		$canvasMainRight				=	( isset( $this->userViewTabs['canvas_main_right'] )			? $this->userViewTabs['canvas_main_right']			: null );
		$canvasMainRightStatic			=	( isset( $this->userViewTabs['canvas_main_right_static'] )	? $this->userViewTabs['canvas_main_right_static']	: null );

		$canvasHeader					=	( $canvasMenu || $canvasBackground || $canvasStatsTop || $canvasStatsMiddle || $canvasStatsBottom || $canvasPhoto || $canvasTitleTop || $canvasTitleMiddle || $canvasTitleBottom );
		$canvasMain						=	( $canvasMainLeft || $canvasMainLeftStatic || $canvasMainMiddle || $canvasMainRight || $canvasMainRight );

		if (  $canvasHeader || $canvasMain ) {
			$return						.=	'<div class="cbPosCanvas">';

			if ( $canvasHeader ) {
				$return					.=		'<div class="cbPosCanvasHeader">';

				if ( $canvasMenu ) {
					$return				.=			'<div class="cbPosCanvasMenu">'
										.				$canvasMenu
										.			'</div>';
				}

				if ( $canvasBackground ) {
					$return				.=			'<div class="cbPosCanvasBackground">'
										.				$canvasBackground
										.			'</div>';
				}

				if ( $canvasStatsTop || $canvasStatsMiddle || $canvasStatsBottom ) {
					$js					=	"$( '.cbPosCanvasHeader' ).children( '.cbScroller' ).cbscroller({"
										.		"elements: '.cb_form_line',"
										.		"height: false"
										.	"});";

					$_CB_framework->outputCbJQuery( $js, 'cbscroller' );
				}

				if ( $canvasStatsTop ) {
					$return				.=			'<div class="cbPosCanvasStats cbPosCanvasStatsTop cbScroller">'
										.				'<div class="cbScrollerLeft hidden">'
										.					'<button type="button" class="btn btn-xs btn-default"><span class="fa fa-angle-left"></span></button>'
										.				'</div>'
										.				'<div class="cbScrollerContent">'
										.					$canvasStatsTop
										.				'</div>'
										.				'<div class="cbScrollerRight hidden">'
										.					'<button type="button" class="btn btn-xs btn-default"><span class="fa fa-angle-right"></span></button>'
										.				'</div>'
										.			'</div>';
				}

				if ( $canvasStatsMiddle ) {
					$return				.=			'<div class="cbPosCanvasStats cbPosCanvasStatsMiddle cbScroller">'
										.				'<div class="cbScrollerLeft hidden">'
										.					'<button type="button" class="btn btn-xs btn-default"><span class="fa fa-angle-left"></span></button>'
										.				'</div>'
										.				'<div class="cbScrollerContent">'
										.					$canvasStatsMiddle
										.				'</div>'
										.				'<div class="cbScrollerRight hidden">'
										.					'<button type="button" class="btn btn-xs btn-default"><span class="fa fa-angle-right"></span></button>'
										.				'</div>'
										.			'</div>';
				}

				if ( $canvasStatsBottom ) {
					$return				.=			'<div class="cbPosCanvasStats cbPosCanvasStatsBottom cbScroller">'
										.				'<div class="cbScrollerLeft hidden">'
										.					'<button type="button" class="btn btn-xs btn-default"><span class="fa fa-angle-left"></span></button>'
										.				'</div>'
										.				'<div class="cbScrollerContent">'
										.					$canvasStatsBottom
										.				'</div>'
										.				'<div class="cbScrollerRight hidden">'
										.					'<button type="button" class="btn btn-xs btn-default"><span class="fa fa-angle-right"></span></button>'
										.				'</div>'
										.			'</div>';
				}

				if ( $canvasTitleTop ) {
					$return				.=			'<div class="cbPosCanvasTitle cbPosCanvasTitleTop">'
										.				$canvasTitleTop
										.			'</div>';
				}

				if ( $canvasTitleMiddle ) {
					$return				.=			'<div class="cbPosCanvasTitle cbPosCanvasTitleMiddle">'
										.				$canvasTitleMiddle
										.			'</div>';
				}

				if ( $canvasTitleBottom ) {
					$return				.=			'<div class="cbPosCanvasTitle cbPosCanvasTitleBottom">'
										.				$canvasTitleBottom
										.			'</div>';
				}

				if ( $canvasPhoto ) {
					$return				.=			'<div class="cbPosCanvasPhoto">'
										.				$canvasPhoto
										.			'</div>';
				}

				$return					.=		'</div>';
			}

			if ( $canvasMain ) {
				$js						=	"var mainTabs = $( '.cbPosCanvasMainMiddle' ).children( '.cbTabs' );"
										.	"if ( mainTabs.length ) {"
										.		"var canvasMainCondition = function( tab ) {"
										.			"if ( ! tab ) {"
										.				"return;"
										.			"}"
										.			"var middle = $( '.cbPosCanvasMainMiddle' );"
										.			"if ( middle.length ) {"
										.				"var left = $( '.cbPosCanvasMainLeft' ).removeClass( 'hidden' );"
										.				"var right = $( '.cbPosCanvasMainRight' ).removeClass( 'hidden' );"
										.				"var dynamicLeft = $( '.cbPosCanvasMainLeftDynamic' ).addClass( 'hidden' );"
										.				"var dynamicRight = $( '.cbPosCanvasMainRightDynamic' ).addClass( 'hidden' );"
										.				"var staticLeft = $( '.cbPosCanvasMainLeftStatic' ).addClass( 'hidden' );"
										.				"var staticRight = $( '.cbPosCanvasMainRightStatic' ).addClass( 'hidden' );"
										.				"var middleSize = 12;"
										.				"if ( tab.tabIndex == 1 ) {"
										.					"if ( dynamicLeft.length ) {"
										.						"dynamicLeft.removeClass( 'hidden' );"
										.					"} else {"
										.						"if ( staticLeft.length ) {"
										.							"staticLeft.removeClass( 'hidden' );"
										.						"} else {"
										.							"left.addClass( 'hidden' );"
										.						"}"
										.					"}"
										.					"if ( dynamicRight.length ) {"
										.						"dynamicRight.removeClass( 'hidden' );"
										.					"} else {"
										.						"if ( staticRight.length ) {"
										.							"staticRight.removeClass( 'hidden' );"
										.						"} else {"
										.							"right.addClass( 'hidden' );"
										.						"}"
										.					"}"
										.				"} else {"
										.					"if ( staticLeft.length ) {"
										.						"staticLeft.removeClass( 'hidden' );"
										.					"} else {"
										.						"left.addClass( 'hidden' );"
										.					"}"
										.					"if ( staticRight.length ) {"
										.						"staticRight.removeClass( 'hidden' );"
										.					"} else {"
										.						"right.addClass( 'hidden' );"
										.					"}"
										.				"}"
										.				"if ( left.length && ( ! left.hasClass( 'hidden' ) ) ) {"
										.					"middleSize -= 3;"
										.				"}"
										.				"if ( right.length && ( ! right.hasClass( 'hidden' ) ) ) {"
										.					"middleSize -= 3;"
										.				"}"
										.				"middle.removeClass( 'col-sm-6 col-sm-9 col-sm-12' ).addClass( 'col-sm-' + middleSize );"
										.			"}"
										.		"};"
										.		"mainTabs.on( 'cbtabs.selected', function( e, event, cbtabs, tab ) {"
										.			"canvasMainCondition( tab );"
										.		"});"
										.		"canvasMainCondition( mainTabs.cbtabs( 'selected' ) );"
										.	"}";

				$_CB_framework->outputCbJQuery( $js );

				$return					.=		'<div class="cbPosCanvasMain clearfix">'
										.			( $canvasMainLeft || $canvasMainLeftStatic ? '<div class="cbPosCanvasMainLeft col-sm-' . ( ( ! $canvasMainMiddle ) && ( $canvasMainRight || $canvasMainRightStatic ) ? 6 : ( ( ! $canvasMainMiddle ) && ( ! ( $canvasMainRight || $canvasMainRightStatic ) ) ? 12 : 3 ) ) . '">'
										.				( $canvasMainLeft ? '<div class="cbPosCanvasMainLeftDynamic">' . $canvasMainLeft . '</div>' : null )
										.				( $canvasMainLeftStatic ? '<div class="cbPosCanvasMainLeftStatic">' . $canvasMainLeftStatic . '</div>' : null )
										.			'</div>' : null )
										.			( $canvasMainMiddle ? '<div class="cbPosCanvasMainMiddle col-sm-' . ( ( $canvasMainLeft || $canvasMainLeftStatic ) && ( $canvasMainRight || $canvasMainRightStatic ) ? 6 : ( ( $canvasMainLeft || $canvasMainLeftStatic ) || ( $canvasMainRight || $canvasMainRightStatic ) ? 9 : 12 ) ) . '">' . $canvasMainMiddle . '</div>' : null )
										.			( $canvasMainRight || $canvasMainRightStatic ? '<div class="cbPosCanvasMainRight col-sm-' . ( ( ! $canvasMainMiddle ) && ( $canvasMainLeft || $canvasMainLeftStatic ) ? 6 : ( ( ! $canvasMainMiddle ) && ( ! ( $canvasMainLeft || $canvasMainLeftStatic ) ) ? 12 : 3 ) ) . '">'
										.				( $canvasMainRight ? '<div class="cbPosCanvasMainRightDynamic">' . $canvasMainRight . '</div>' : null )
										.				( $canvasMainRightStatic ? '<div class="cbPosCanvasMainRightStatic">' . $canvasMainRightStatic . '</div>' : null )
										.			'</div>' : null )
										.		'</div>';
			}

			$return						.=	'</div>';
		}

 		if ( $this->nCols != 0 ) {
			if ( $return ) {
				$return					.=	'<div class="cbPosSeparator"></div>';
			}

			$return						.=	'<div class="cbPosTop cbColumns clearfix">';

			if ( isset( $this->userViewTabs['cb_left'] ) ) {
				$return					.=		'<div class="cbPosLeft ' . $this->widthToBootstrap( $this->wLeft ) . '">'
										.				$this->userViewTabs['cb_left']
										.		'</div>';
			}

			if ( isset( $this->userViewTabs['cb_middle'] ) ) {
				$return					.=		'<div class="cbPosMiddle ' . $this->widthToBootstrap( $this->wMiddle ) . '">'
										.				$this->userViewTabs['cb_middle']
										.		'</div>';
			}

			if ( isset( $this->userViewTabs['cb_right'] ) ) {
				$return					.=		'<div class="cbPosRight ' . $this->widthToBootstrap( $this->wRight ) . '">'
										.			$this->userViewTabs['cb_right']
										.		'</div>';
			}

			$return						.=	'</div>';
		}

		if ( isset( $this->userViewTabs['cb_tabmain'] ) ) {
			if ( $return ) {
				$return					.=	'<div class="cbPosSeparator"></div>';
			}

			$return						.=	'<div class="cbPosTabMain">'
										.		$this->userViewTabs['cb_tabmain']
										.	'</div>';
		}

		if ( isset( $this->userViewTabs['cb_underall'] ) ) {
			if ( $return ) {
				$return					.=	'<div class="cbPosSeparator"></div>';
			}

			$return						.=	'<div class="cbPosUnderAll">'
										.		$this->userViewTabs['cb_underall']
										.	'</div>';
		}

		$line							=	null;
		$indexes						=	array_keys( $this->userViewTabs );

		if ( $indexes ) foreach ( $indexes as $k => $v ) {
			if ( $v && $v[0] == 'L' ) {
				$L						=	$v[1];

				if ( $line === null ) {
					$line				=	$k;
				}

				if ( ! ( isset( $indexes[$k + 1] ) && ( $indexes[$k + 1][1] == $L ) ) ) {
					$cols				=	( $k - $line + 1 );

					switch( $cols ) {
						case 9:
							$colClass	=	'cbColumnCustom col-sm-1';
							break;
						case 8:
							$colClass	=	'cbColumnCustom col-sm-1';
							break;
						case 7:
							$colClass	=	'cbColumnCustom col-sm-1';
							break;
						case 6:
							$colClass	=	'cbColumn2 col-sm-2';
							break;
						case 5:
							$colClass	=	'cbColumnCustom col-sm-2';
							break;
						case 4:
							$colClass	=	'cbColumn3 col-sm-3';
							break;
						case 3:
							$colClass	=	'cbColumn4 col-sm-4';
							break;
						case 2:
							$colClass	=	'cbColumn6 col-sm-6';
							break;
						case 1:
						default:
							$colClass	=	'cbColumn12 col-sm-12';
							break;
					}

					$width				=	100;
					$step				=	floor( $width / $cols );

					$return				.=	'<div class="cbPosGridSeparator" id="cbPosSep0"></div>'
										.	'<div class="cbPosGridLine cbColumns clearfix" id="cbPosLine' . substr( $v, 0, 2 ) . '">';

					for ( $i = $line ; $i <= $k ; $i++ ) {
						if ( $i == $k ) {
							$step		=	( $width - ( ( $cols - 1 ) * $step ) );
						}

						$return			.=		'<div class="cbPosGrid ' . $colClass . '" id="cbPos' . $v . '_' . $i . '"' . ( strpos( $colClass, 'cbColumnCustom' ) !== false ? ' style="width: ' . (int) $step . '%;"' : null ) . '>'
										.			$this->userViewTabs[$indexes[$i]]
										.		'</div>';
					}

					$return				.=	'</div>'
										.	'<div class="cbPosGridSeparator" id="cbPosSep' . substr( $v, 0, 2 ) . '"></div>';

					$line				=	null;
				}
			}
		}

		echo $return;
	}

	/**
	 * Renders by ECHO the profile edit view
	 *
	 * @return void
	 */
	protected function _renderEdit( )
	{
		$return			=	null;

		if ( $this->topIcons ) {
			$return		.=	'<div class="cbIconsTop form-group cb_form_line clearfix">'
						.		$this->topIcons
						.	'</div>';
		}

		$return			.=	$this->tabContent
						.	'<div class="form-group cb_form_line clearfix">'
						.		'<div class="col-sm-offset-3 col-sm-9">'
						.			'<input class="btn btn-primary cbProfileEditSubmit" type="submit" id="cbbtneditsubmit" value="' . $this->submitValue . '"' . cbValidator::getSubmitBtnHtmlAttributes() . ' />'
						.			' <input class="btn btn-default cbProfileEditCancel" type="button" id="cbbtncancel" name="btncancel" value="' . $this->cancelValue . '" />'
						.		'</div>'
						.	'</div>';

		if ( $this->bottomIcons ) {
			$return		.=	'<div class="cbIconsBottom form-group cb_form_line clearfix">'
						.		$this->bottomIcons
						.	'</div>';
		}

		echo $return;
	}
}

class CBRegisterFormView_html_default extends cbRegistrationView
{
	/**
	 * Renders the registration head part view
	 *
	 * @return string  HTML
	 */
	private function _renderRegistrationHead( )
	{
		global $_CB_framework, $ueConfig;

		$layout				=	( isset( $ueConfig['reg_layout'] ) ? $ueConfig['reg_layout'] : 'flat' );
		$titleCanvas		=	( isset( $ueConfig['reg_title_img'] ) ? $ueConfig['reg_title_img'] : 'general' );

		if ( $titleCanvas != 'none' ) {
			if ( in_array( $titleCanvas, array( 'general' ) ) ) {
				$canvasImg	=	selectTemplate() . 'images/title/' . $titleCanvas . '.jpg';
			} else {
				$canvasImg	=	$_CB_framework->getCfg( 'live_site' ) . '/images/' . $titleCanvas;
			}
		} else {
			$canvasImg		=	null;
		}

		$return				=	null;

		if ( $this->moduleContent ) {
			$return			.=	'<div class="cbRegistrationContainer">'
							.		'<div class="cbRegistrationLogin">'
							.			$this->moduleContent
							.		'</div>';
		}

		$pageClass			=	$_CB_framework->getMenuPageClass();

		$return				.=	'<div class="cb_template cb_template_' . selectTemplate( 'dir' ) . ' cbRegistration ' . ( $layout == 'tabbed' ? 'cbRegistrationTabbed' : ( $layout == 'stepped' ? 'cbRegistrationStepped' : 'cbRegistrationFlat' ) ) . ( $canvasImg ? ' cbRegistrationCanvas' : null ) . ( $pageClass ? ' ' . htmlspecialchars( $pageClass ) : null ) . '">';

		if ( is_array( $this->triggerResults ) ) {
			$return			.=		implode( '', $this->triggerResults );
		}

		if ( $this->registerTitle || $this->introMessage ) {
			$return			.=		'<div class="cbRegistrationHeader"' . ( $canvasImg ? ' style="background-image: url(' . $canvasImg . ')"' : null ) . '>'
							.			'<div class="cbRegistrationHeaderInner">';

			if ( $this->registerTitle ) {
				$return		.=			'<div class="cbRegistrationTitle page-header">'
							.				'<h3>' . $this->registerTitle . '</h3>'
							.			'</div>';
			}

			if ( $this->introMessage ) {
				$return		.=			'<div class="cbRegistrationIntro form-group cb_form_line clearfix">'
							.				$this->introMessage
							.			'</div>';
			}

			$return			.=			'</div>'
							.		'</div>';
		}

		if ( $this->topIcons ) {
			$return			.=		'<div class="cbIconsTop form-group cb_form_line clearfix">'
							.			$this->topIcons
							.		'</div>';
		}

		$return				.=		$this->regFormTag;

		return $return;
	}

	/**
	 * Renders by ECHO the registration view (divs mode)
	 *
	 * @return void
	 */
	protected function _renderdivs( )
	{
		$return			=	$this->_renderRegistrationHead()
						.			'<div id="registrationTable" class="cbRegistrationDiv">'
						.				$this->tabContent
						.				'<div class="form-group cb_form_line clearfix">'
						.					'<div class="col-sm-offset-3 col-sm-9">'
						.						'<input type="submit" value="' . $this->registerButton . '" class="btn btn-primary cbRegistrationSubmit"' . cbValidator::getSubmitBtnHtmlAttributes() . ' />'
						.					'</div>'
						.				'</div>'
						.			'</div>'
						.	$this->_renderRegistrationFooter();

		echo $return;
	}

	/**
	 * Renders by ECHO the registration view (table trs mode)
	 *
	 * @return void
	 */
	protected function _render( )
	{
		$return			=	$this->_renderRegistrationHead()
						.			'<table id="registrationTable" class="cbRegistrationTable table table-hover">'
						.				'<tbody>'
						.					$this->tabContent
						.					'<tr class="cbRegistrationButtonRow">'
						.						'<td>&nbsp;</td>'
						.						'<td>'
						.							'<input type="submit" value="' . $this->registerButton . '" class="btn btn-primary cbRegistrationSubmit"' . cbValidator::getSubmitBtnHtmlAttributes() . ' />'
						.						'</td>'
						.					'</tr>'
						.				'</tbody>'
						.			'</table>'
						.	$this->_renderRegistrationFooter();

		echo $return;
	}

	/**
	 * Renders the registration view footer
	 *
	 * @return string  HTML
	 */
	private function _renderRegistrationFooter( )
	{
		$return			=		'</form>';

		if ( $this->bottomIcons ) {
			$return		.=		'<div class="cbIconsBottom form-group cb_form_line clearfix">'
						.			$this->bottomIcons
						.		'</div>';
		}

		if ( $this->conclusionMessage ) {
			$return		.=		'<div class="cbRegistrationConclusion form-group cb_form_line clearfix">'
						.			$this->conclusionMessage
						.		'</div>';
		}

		if ( $this->moduleContent ) {
			$return		.=	'</div>';
		}

		$return			.=	'</div>';

		return $return;
	}
}

class CBListView_html_default extends cbListView
{
	/**
	 * Renders by ECHO the list view head
	 *
	 * @return void
	 */
	protected function _renderHead( )
	{
		global $_CB_framework;

		$headerRightColumn			=	( ( ( count( $this->lists ) > 0 ) && $this->allowListSelector ) || ( $this->searchTabContent && ( ( ( ! $this->searchResultDisplaying ) || $this->searchCollapsed ) || ( $this->searchResultDisplaying && $this->allowListAll ) ) ) );

		$return						=	'<div class="cbUserListHead">'
									.		( $this->listTitleHtml ? '<div class="page-header cbUserListTitle"><h3>' . $this->listTitleHtml . '</h3></div>' : null )
									.		'<div class="cbColumns clearfix">'
									.			'<div class="' . ( $headerRightColumn ? 'cbColumn9 col-sm-9' : 'cbColumn12 col-sm-12' ) . '">'
									.				( trim( $this->listDescription ) != '' ? '<div class="cbUserListDescription">' . $this->listDescription . '</div>' : null )
									.				'<div class="cbUserListResultCount">';

		if ( $this->totalIsAllUsers ) {
			$return					.=					CBTxt::Th( 'SITENAME_HAS_TOTAL_REGISTERED_MEMBERS', '[SITENAME] has %%TOTAL%% registered member|[SITENAME] has %%TOTAL%% registered members', array( '[SITENAME]' => $_CB_framework->getCfg( 'sitename' ), '%%TOTAL%%' => $this->total ) );
		} else {
			$return					.=					CBTxt::Th( 'USERS_COUNT_MEMBERS', '%%USERS_COUNT%% member|%%USERS_COUNT%% members', array( '%%USERS_COUNT%%' => $this->total ) );
		}

		$return						.=				'</div>'
									.			'</div>';

		if ( $headerRightColumn ) {
			$return					.=			'<div class="cbColumn3 col-sm-3">'
									.				'<div class="cbUserListChanger text-right">';

			if ( ( count( $this->lists ) > 0 ) && $this->allowListSelector ) foreach ( $this->lists as $keyName => $listName ) {
				$return				.=					'<div class="cbUserListChangeItem cbUserList' . $keyName . '">' . $listName . '</div>';
			}

			if ( $this->searchTabContent ) {
				if ( ( ! $this->searchResultDisplaying ) || $this->searchCollapsed ) {
					$return			.=						'<div class="cbUserListSearchButtons cbUserListsSearchTrigger">'
									.							'<button type="button" class="btn btn-default btn-block cbUserListsSearchButton">' . CBTxt::Th( 'UE_SEARCH_USERS', 'Search Users' ) . ' <span class="fa fa-caret-down"></span></button>'
									.						'</div>';
				}

				if ( $this->searchResultDisplaying && $this->allowListAll ) {
					$return			.=						'<div class="cbUserListSearchButtons cbUserListListAll">'
									.							'<button type="button" class="btn btn-default btn-block cbUserListListAllButton" onclick="window.location=\'' . $this->ue_base_url . '\'; return false;">' . CBTxt::Th( 'UE_LIST_ALL', 'List all' ) . '</button>'
									.						'</div>';
				}
			}

			$return					.=				'</div>'
									.			'</div>';
		}

		$return						.=		'</div>'
									.	'</div>';

		if ( $this->searchTabContent ) {
			$return					.=	'<div class="cbUserListSearch">'
									.		( $this->searchCriteriaTitleHtml ? '<div class="page-header cbUserListSearchTitle"><h3>' . $this->searchCriteriaTitleHtml . '</h3></div>' : null )
									.		'<div class="cbUserListSearchFields">'
									.			$this->searchTabContent
									.			'<div class="form-group cb_form_line clearfix">'
									.				'<div class="col-sm-offset-3 col-sm-9">'
									.					'<input type="submit" class="btn btn-primary cbUserlistSubmit" value="' . CBTxt::Th( 'UE_FIND_USERS', 'Find Users' ) . '"' . cbValidator::getSubmitBtnHtmlAttributes() . ' />';

			if ( $this->searchMode == 0 ) {
				$return				.=					' <input type="button" class="btn btn-default cbUserlistCancel" value="' . htmlspecialchars( CBTxt::Th( 'UE_CANCEL', 'Cancel' ) ) . '" />';
			}

			$return					.=				'</div>'
									.			'</div>'
									.		'</div>';

			if ( $this->searchResultDisplaying && $this->searchResultsTitleHtml ) {
				$return				.=		( $this->searchCriteriaTitleHtml ? '<div class="page-header searchCriteriaTitleHtml"><h3>' . $this->searchResultsTitleHtml . '</h3></div>' : null );
			}

			$return					.=	'</div>';
		}

		echo $return;
	}

	/**
	 * Renders by ECHO the list view body
	 *
	 * @return void
	 */
	protected function _renderBody( )
	{
		global $ueConfig;

		$formatting				=	( isset( $ueConfig['use_divs'] ) && ( ! $ueConfig['use_divs'] ) ? 'table' : 'divs' );
		$layout					=	( ( $formatting == 'divs' ) && ( $this->layout == 'grid' ) ? 'grid' : 'list' );
		$gridStyle				=	null;
		$columnCount			=	count( $this->columns );
		$hasCanvas				=	false;

		if ( $columnCount && isset( $this->columns[0]->fields ) ) {
			foreach ( $this->columns[0]->fields as $field ) {
				if ( isset( $field['fieldid'] ) && ( (int) $field['fieldid'] == 17 ) ) {
					$hasCanvas	=	true;
				}
			}
		}

		if ( (int) $this->gridHeight ) {
			$gridStyle			.=	'height:' . (int) $this->gridHeight . 'px;';
		}

		if ( (int) $this->gridWidth ) {
			$gridStyle			.=	'width:' . (int) $this->gridWidth . 'px;';
		}

		if ( $formatting == 'divs' ) {
			$return				=	'<div id="cbUserTable" class="cbUserListDiv ' . ( $layout == 'grid' ? 'cbUserListLayoutGrid' : 'cbUserListLayoutList' ) . ' cbUserListT_' . $this->listId . ( $hasCanvas ? ' cbUserListCanvas' : null ) . '">';
		} else {
			$return				=	'<table id="cbUserTable" class="table table-hover cbUserListTable cbUserListLayoutList cbUserListT_' . $this->listId . ( $hasCanvas ? ' cbUserListCanvas' : null ) . '">'
								.		'<thead>';
		}

		if ( $columnCount && ( $layout != 'grid' ) ) {
			if ( $formatting == 'divs' ) {
				$return			.=			'<div class="cbColumns clearfix cbUserListHeader">';
			} else {
				$return			.=			'<tr class="sectiontableheader cbUserListHeader">';
			}

			foreach ( $this->columns as $index => $column ) {
				if ( $formatting == 'divs' ) {
					$return		.=				'<div class="cbColumn' . $column->size . ' col-sm-' . $column->size . ' cbUserListHeaderCol' . ( $index + 1 ) . ( $column->cssclass ? ' ' . $column->cssclass : null ) . '">' . $column->titleRendered . '</div>';
				} else {
					$return		.=				'<th class="cbUserListHeaderCol' . ( $index + 1 ) . ( $column->cssclass ? ' ' . $column->cssclass : null ) . '">' . $column->titleRendered . '</th>';
				}
			}

			if ( $formatting == 'divs' ) {
				$return			.=			'</div>';
			} else {
				$return			.=			'</tr>';
			}
		}

		$return					.=		'</thead>'
								.		'<tbody>';

		$i						=	0;

		if ( is_array( $this->users ) && ( count( $this->users ) > 0 ) ) foreach ( $this->users as $userIndex => $user ) {
			if ( $this->allowProfileLink ) {
				$style			=	'cursor: hand; cursor: pointer;';
			} else {
				$style			=	null;
			}

			if ( $this->allowProfileLink ) {
				$attributes		=	' id="cbU' . $i . '"';
			} else {
				$attributes		=	null;
			}

			$class				=	'cbUserListRow';

			if ( $user->banned ) {
				$class			.=	' cbUserListRowBanned';
			}

			if ( ! $user->confirmed ) {
				$class			.=	' cbUserListRowUnconfirmed';
			}

			if ( ! $user->approved ) {
				$class			.=	' cbUserListRowUnapproved';
			}

			if ( $user->block ) {
				$class			.=	' cbUserListRowBlocked';
			}

			if ( $layout == 'grid' ) {
				$class			.=	' containerBox img-thumbnail';
			} else {
				$class			.=	' sectiontableentry' . ( 1 + ( $i % 2 ) );

				if ( $formatting == 'divs' ) {
					$class		.=	' cbColumns clearfix';
				}
			}

			if ( $columnCount ) {
				if ( $formatting == 'divs' ) {
					$return		.=			'<div class="' . $class . '"' . ( $style ? ' style="' . $style . '"' : null ) . $attributes . '>';
				} else {
					$return		.=			'<tr class="' . $class . '"' . ( $style ? ' style="' . $style . '"' : null ) . $attributes . '>';
				}

				if ( $layout == 'grid' ) {
					$return		.=			'<div class="containerBoxInner"' . ( $gridStyle ? ' style="' . $gridStyle . '"' : null ) . '>';
				}

				foreach ( $this->columns as $columnIndex => $column ) {
					$cellFields	=	$this->tableContent[$userIndex][$columnIndex];

					if ( $formatting == 'divs' ) {
						$return	.=				'<div class="cbUserListRowColumn cbUserListRowCol' . ( $columnIndex + 1 ) . ( $layout != 'grid' ? ' cbColumn' . $column->size . ' col-sm-' . $column->size : null ) . ( $column->cssclass ? ' ' . $column->cssclass : null ) . '">' . $this->_getUserListCell( $cellFields ) . '</div>';
					} else {
						$return	.=				'<td class="cbUserListRowColumn cbUserListRowCol' . ( $columnIndex + 1 ) . ( $column->cssclass ? ' ' . $column->cssclass : null ) . '">' . $this->_getUserListCell( $cellFields ) . '</td>';
					}
				}

				if ( $layout == 'grid' ) {
					$return		.=			'</div>';
				}

				if ( $formatting == 'divs' ) {
					$return		.=			'</div>';
				} else {
					$return		.=			'</tr>';
				}
			}

			$i++;
		} else {
			if ( $formatting == 'divs' ) {
				if ( $layout != 'grid' ) {
					$return		.=			'<div class="cbUserListRow cbColumns clearfix">';
				}

				$return			.=			'<div class="sectiontableentry1' . ( $layout != 'grid' ? ' cbColumn12 col-sm-12' : null ) . '">'
								.				CBTxt::Th( 'UE_NO_USERS_IN_LIST', 'No users in this list' )
								.			'</div>';

				if ( $layout != 'grid' ) {
					$return		.=			'</div>';
				}
			} else {
				$return			.=			'<tr class="sectiontableentry1">'
								.				'<td colspan="' . $columnCount . '">' . CBTxt::Th( 'UE_NO_USERS_IN_LIST', 'No users in this list' ) . '</td>'
								.			'</tr>';
			}
		}

		if ( $layout == 'grid' ) {
			$return				.=			'<div class="clearfix"></div>';
		}

		if ( $formatting == 'divs' ) {
			$return				.=	'</div>';
		} else {
			$return				.=		'<tbody>'
								.	'</table>';
		}

		echo $return;
	}

	/**
	 * Renders a cell for the list view
	 *
	 * @param  stdClass[]  $cellFields  CB fields in cell
	 * @return string                                        HTML
	 */
	private function _getUserListCell( $cellFields )
	{
		$return							=	null;

		foreach ( $cellFields as $fieldView ) {
			if ( $fieldView->value != '' ) {
				$return					.=	'<div class="cbUserListFieldLine cbUserListFL_' . $fieldView->name . '">';

				switch ( $fieldView->display ) {
					case 1:
						$return			.=		'<span class="cbUserListFieldTitle cbUserListFT_' . $fieldView->name . '">' . $fieldView->title . '</span> '
										.		'<span class="cbListFieldCont cbUserListFC_' . $fieldView->name . '">' . $fieldView->value . '</span>';
						break;
					case 2:
						$return			.=		'<div class="cbUserListFieldTitle cbUserListFT_' . $fieldView->name . '">' . $fieldView->title . '</div>'
										.		'<div class="cbListFieldCont cbUserListFC_' . $fieldView->name . '">' . $fieldView->value . '</div>';
						break;
					case 3:
						$return			.=		'<span class="cbUserListFieldTitle cbUserListFT_' . $fieldView->name . '"></span> '
										.		'<span class="cbListFieldCont cbUserListFC_' . $fieldView->name . '">' . $fieldView->value . '</span>';
						break;
					default:
						$return			.=		'<span class="cbListFieldCont cbUserListFC_' . $fieldView->name . '">' . $fieldView->value . '</span>';
						break;
				}

				$return					.=	'</div>';
			}
		}

		return $return;
	}
}
