<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2019 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('Restricted access');

SpAddonsConfig::addonConfig(
array(
	'type'=>'repeatable',
	'addon_name'=>'accordion',
	'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION'),
	'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_DESC'),
	'category'=>'Content',
	'attr'=>array(
		'general' => array(
			'admin_label'=>array(
				'type'=>'text',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL_DESC'),
				'std'=> ''
			),

			'title'=>array(
				'type'=>'text',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_DESC'),
				'std'=>  ''
			),

			'heading_selector'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_DESC'),
				'values'=>array(
					'h1'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H1'),
					'h2'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H2'),
					'h3'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H3'),
					'h4'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H4'),
					'h5'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H5'),
					'h6'=>JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H6'),
				),
				'std'=>'h3',
				'depends'=>array(array('title', '!=', '')),
			),

			'title_font_family'=>array(
				'type'=>'fonts',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_FAMILY'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_FAMILY_DESC'),
				'depends'=>array(array('title', '!=', '')),
				'selector'=> array(
					'type'=>'font',
					'font'=>'{{ VALUE }}',
					'css'=>'.sppb-addon-title { font-family: "{{ VALUE }}"; }'
				)
			),

			'title_fontsize'=>array(
				'type'=>'slider',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_SIZE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_SIZE_DESC'),
				'std'=>'',
				'depends'=>array(array('title', '!=', '')),
				'responsive' => true,
				'max'=> 400,
			),

			'title_lineheight'=>array(
				'type'=>'slider',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_LINE_HEIGHT'),
				'std'=>'',
				'depends'=>array(array('title', '!=', '')),
				'responsive' => true,
				'max'=> 400,
			),

			'title_font_style'=>array(
				'type'=>'fontstyle',
				'title'=> JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_STYLE'),
				'depends'=>array(array('title', '!=', '')),
			),

			'title_letterspace'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LETTER_SPACING'),
				'values'=>array(
					'0'=> 'Default',
					'1px'=> '1px',
					'2px'=> '2px',
					'3px'=> '3px',
					'4px'=> '4px',
					'5px'=> '5px',
					'6px'=>	'6px',
					'7px'=>	'7px',
					'8px'=>	'8px',
					'9px'=>	'9px',
					'10px'=> '10px'
				),
				'std'=>'0',
				'depends'=>array(array('title', '!=', '')),
			),

			'title_text_color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR_DESC'),
				'depends'=>array(array('title', '!=', '')),
			),

			'title_margin_top'=>array(
				'type'=>'slider',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_TOP'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_TOP_DESC'),
				'placeholder'=>'10',
				'depends'=>array(array('title', '!=', '')),
				'responsive' => true,
				'max'=> 400,
			),

			'title_margin_bottom'=>array(
				'type'=>'slider',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_BOTTOM'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_BOTTOM_DESC'),
				'placeholder'=>'10',
				'depends'=>array(array('title', '!=', '')),
				'responsive' => true,
				'max'=> 400,
			),

			'style'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_STYLE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_STYLE_DESC'),
				'values'=> array(
					'panel-modern'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_MODERN'),
					'panel-default'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_DEFAULT'),
					'panel-primary'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_PRIMARY'),
					'panel-success'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_SUCCESS'),
					'panel-info'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_INFO'),
					'panel-warning'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_WARNING'),
					'panel-danger'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_DANGER'),
					'panel-faq'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_STYLE_FAQ'),
					'panel-custom'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_CUSTOM'),
				),
				'std'=> 'panel-modern'
			),

			'openitem'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_OPEN_ITEM'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_OPEN_ITEM_DESC'),
				'values'=> array(
					''=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_OPEN_FIRST_ITEM'),
					'show'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_OPEN_ALL_ITEM'),
					'hide'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_CLOSE_ALL_ITEM'),
				),
				'std'=> ''
			),

			'class'=>array(
				'type'=>'text',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS_DESC'),
				'std'=>''
			),

			// Repeatable Item
			'sp_accordion_item'=>array(
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ITEMS'),
				'attr'=>array(
					'title'=>array(
						'type'=>'text',
						'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_TITLE'),
						'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_TITLE_DESC'),
						'std'=>'Accordion Title',
					),
					'icon' => array(
						'type'	=>	'icon',
						'title'	=>	JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ICON'),
						'desc'	=>	JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ICON_DESC'),
						'std'	=> 	''
					),
					'content'	=>	array(
						'type'	=>	'builder',
						'title'	=>	JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_CONTENT'),
						'desc'	=>	JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_CONTENT_DESC'),
						'std'=> 'Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et.'
					),
				),
			),

			//Item style option
			'item_separator'=>array(
				'type'=>'separator',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ITEM_STYLE_OPTIONS'),
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'item_bg'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ITEM_BG'),
				'std'=>'',
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'item_margin'=>array(
				'type'=>'margin',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ITEM_MARGIN'),
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
				'responsive'=>true,
				'std'=>'',
			),

			'item_padding'=>array(
				'type'=>'padding',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ITEM_PADDING'),
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
				'responsive'=>true,
				'std'=>'',
			),

			'item_border_color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ITEM_BORDER_COLOR'),
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'item_border_width'=>array(
				'type'=>'margin',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ITEM_BORDER_WIDTH'),
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'item_border_radius'=>array(
				'type'=>'slider',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ITEM_BORDER_RADIUS'),
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
				'max'=>1000,
			),

			//title style
			'title_separator'=>array(
				'type'=>'separator',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_STYLE_OPTION'),
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'item_title_bg_color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ITEM_TITLE_BG_COLOR'),
				'std'=>'',
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'item_title_text_color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR'),
				'std'=>'',
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'item_title_fontsize'=>array(
				'type'=>'slider',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_SIZE'),
				'std'=>'',
				'responsive' => true,
				'max'=> 400,
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'item_title_lineheight'=>array(
				'type'=>'slider',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_TITLE_LINE_HEIGHT'),
				'std'=>'',
				'responsive' => true,
				'max'=> 400,
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'item_title_font_family'=>array(
				'type'=>'fonts',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_FAMILY'),
				'selector'=> array(
					'type'=>'font',
					'font'=>'{{ VALUE }}',
					'css'=>'.sppb-panel-heading { font-family: "{{ VALUE }}"; }'
				),
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'item_title_font_style'=>array(
				'type'=>'fontstyle',
				'title'=> JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_STYLE'),
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'item_title_letterspace'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LETTER_SPACING'),
				'values'=>array(
					'-10px'=> '-10px',
					'-9px'=>'-9px',
					'-8px'=>'-8px',
					'-7px'=>'-7px',
					'-6px'=>'-6px',
					'-5px'=> '-5px',
					'-4px'=> '-4px',
					'-3px'=> '-3px',
					'-2px'=> '-2px',
					'-1px'=> '-1px',
					'0px'=> 'Default',
					'1px'=> '1px',
					'2px'=> '2px',
					'3px'=> '3px',
					'4px'=> '4px',
					'5px'=> '5px',
					'6px'=>	'6px',
					'7px'=>	'7px',
					'8px'=>	'8px',
					'9px'=>	'9px',
					'10px'=> '10px',
				),
				'std'=>'0px',
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'item_title_padding'=>array(
				'type'=>'padding',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CAROUSEL_ITEM_TITLE_PADDING'),
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
				'responsive'=>true,
				'std'=>'',
			),

			//Icon style
			'icon_separator'=>array(
				'type'=>'separator',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ICON_STYLE_OPTIONS'),
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'icon_position'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_LINK_LIST_ICON_POSITION'),
				'values'=>array(
					'left'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LEFT'),
					'right'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_RIGHT'),
				),
				'std'=>'',
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'icon_text_color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_TAB_ICON_COLOR'),
				'std'=>'',
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'icon_fontsize'=>array(
				'type'=>'slider',
				'title'=>JText::_('COM_SPPAGEBUILDER_TAB_ICON_SIZE'),
				'std'=>'',
				'responsive' => true,
				'max'=> 400,
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'icon_margin'=>array(
				'type'=>'margin',
				'title'=>JText::_('COM_SPPAGEBUILDER_TAB_ICON_MARGIN'),
				'placeholder'=>'10',
				'responsive' => true,
				'max'=> 400,
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
				'std'=>'',
			),

			//accordion content style
			'content_separator'=>array(
				'type'=>'separator',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CONTENT_STYLE_OPTION'),
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),
			
			'item_content_padding'=>array(
				'type'=>'padding',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ITEM_CONTENT_PADDING'),
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
				'responsive'=>true,
				'std'=>'',
			),

			'item_content_border_color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ITEM_CONTENT_BORDER_COLOR'),
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'item_content_border_width'=>array(
				'type'=>'margin',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ITEM_CONTENT_BORDER'),
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			//Active accordion style
			'active_separator'=>array(
				'type'=>'separator',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ACTIVE_STYLE_OPTIONS'),
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'active_title_bg_color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ITEM_ACTIVE_TITLE_BG_COLOR'),
				'std'=>'',
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'active_title_text_color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ACTIVE_TITLE_TEXT_COLOR'),
				'std'=>'',
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'active_icon_color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ACTIVE_ICON_COLOR'),
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

			'active_icon_rotate'=>array(
				'type'=>'slider',
				'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ACCORDION_ICON_ROTATION'),
				'max'=>360,
				'std'=>0,
				'depends'=> array(
					array('style', '=', 'panel-custom'),
				),
			),

		),
	),
	)
);
