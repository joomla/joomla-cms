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
		'type'=>'content',
		'addon_name'=>'sp_feature',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_DESC'),
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
					'std'=>  'Feature Box'
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
						'css'=>'.sppb-addon-title { font-family: {{ VALUE }}; }'
					)
				),

				'title_position'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_ICON_IMAGE_POSITION'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_ICON_IMAGE_POSITION_DESC'),
					'values'=>array(
						'after'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_TITLE_POSITION_BEFORE_TITLE'),
						'before'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_TITLE_POSITION_AFTER_TITLE'),
						'left'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LEFT'),
						'right'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_RIGHT'),
					),
					'std'=>'after',
					'depends'=>array(array('title', '!=', '')),
				),

				'title_fontsize'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_SIZE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_SIZE_DESC'),
					'std'=>array('md'=>16),
					'depends'=>array(array('title', '!=', '')),
					'responsive' => true,
					'max'=> 400,
				),

				'title_lineheight'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_LINE_HEIGHT'),
					'std'=>array('md'=>22),
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
					'std'=>'#4A4A4A',
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

				'title_url'=>array(
					'type'=>'media',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_TITLE_IMAGE_URL'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_TITLE_IMAGE_URL_DESC'),
					'placeholder'=>'http://',
					'hide_preview'=>true,
					'std'=>'',
				),

				'link_open_new_window' => array(
					'type' => 'checkbox',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_LINK_NEW_WINDOW'),
					'std' => 0,
					'depends'=>array(array('title_url', '!=', '')),
				),

				'url_appear'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_URL_APEAR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_URL_APEAR_DESC'),
					'values'=>array(
						'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_URL_APEAR_TITLE'),
						'icon'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_URL_APEAR_ICON'),
						'both'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_URL_APEAR_BOTH'),
					),
					'std'=>'title',
					'depends'=>array(array('title_url', '!=', '')),
				),

				'feature_type'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_LAYOUT_TYPE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_LAYOUT_TYPE_DESC'),
					'values'=> array(
						'icon'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_LAYOUT_TYPE_ICON'),
						'image'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_LAYOUT_TYPE_IMAGE'),
						'both'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_LAYOUT_TYPE_BOTH'),
					),
					'std' => 'icon'
				),

				'separator_image_options'=>array(
					'type'=>'separator',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_image_OPTIONS'),
					'depends'=>array(
                        array('feature_type', '!=', 'icon'),
                    )
				),

				'feature_image'=>array(
					'type' => 'media',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_IMAGE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_IMAGE_DESC'),
					'std' => '',
					'depends'=>array(
                        array('feature_type', '!=', 'icon'),
                    )
				),
				'feature_image_width'=>array(
					'type' => 'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_IMAGE_WIDTH'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_IMAGE_WIDTH_DESC'),
                    'responsive' => true,
                    'max'=> 100,
					'std' => array('md'=>50),
					'depends' => array(
                        array('feature_type', '!=', 'icon'),
                        array('title_position', '!=', 'before'),
                        array('title_position', '!=', 'after'),
                    )
				),
                'feature_image_margin'=>array(
                    'type'=>'margin',
                    'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_MARGIN'),
                    'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_MARGIN_DESC'),
                    'depends'=>array(
                        array('feature_type', '!=', 'icon'),
                    ),
                    'responsive' => true
				),
                            
                'separator_icon_options'=>array(
					'type'=>'separator',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ICON_OPTIONS'),
					'depends'=>array(
                        array('feature_type', '!=', 'image'),
                    )
				),

				'icon_name'=>array(
					'type'=>'icon',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_ICON_NAME'),
					'std'=> 'fa-trophy',
					'depends'=>array(
                        array('feature_type', '!=', 'image'),
                    )
				),

				'icon_size'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_ICON_SIZE'),
					'placeholder'=>36,
					'std'=>array('md'=>36),
					'depends'=>array(
                        array('feature_type', '!=', 'image'),
                    ),
					'responsive' => true,
					'max'=> 400,
				),

				'icon_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_COLOR'),
					'std'=>'#0080FE',
					'depends'=>array(
                        array('feature_type', '!=', 'image'),
                    )
				),

				'icon_background'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_COLOR'),
					'depends'=>array(
                        array('feature_type', '!=', 'image'),
                    )
				),

				'icon_border_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_COLOR'),
					'depends'=>array(
                        array('feature_type', '!=', 'image'),
                    )
				),

				'icon_border_width'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_WIDTH'),
					'depends'=>array(
                        array('feature_type', '!=', 'image'),
                    ),
					'responsive' => true,
					'max'=> 400,
				),

				'icon_border_radius'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_RADIUS'),
					'depends'=>array(
                        array('feature_type', '!=', 'image'),
                    ),
					'responsive' => true,
					'max'=> 400,
				),

				'icon_boxshadow'=>array(
					'type'=>'boxshadow',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ICON_BOXSHADOW'),
					'std'=>'0 0 0 0 #ffffff'
				),

				'icon_margin_top'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_MARGIN_TOP'),
					'depends'=>array(
                        array('feature_type', '!=', 'image'),
                    ),
					'responsive' => true,
					'min'=> -200,
					'max'=> 400,
				),

				'icon_margin_bottom'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_MARGIN_BOTTOM'),
					'depends'=>array(
                        array('feature_type', '!=', 'image'),
                    ),
					'responsive' => true,
					'max'=> 400,
				),

				'icon_padding'=>array(
					'type'=>'padding',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_PADDING'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_PADDING_DESC'),
					'depends'=>array(
                        array('feature_type', '!=', 'image'),
                    ),
					'responsive' => true
				),

				'separator_addon_options'=>array(
					'type'=>'separator',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CONTENT_OPTIONS')
				),

				'text'=>array(
					'type'=>'editor',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_CONTENT'),
					'std'=>'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer adipiscing erat eget risus sollicitudin pellentesque et non erat. Maecenas nibh dolor, malesuada et bibendum a, sagittis accumsan ipsum.'
				),

				'text_font_family'=>array(
					'type'=>'fonts',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CONTENT_FONT_FAMILY'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_CONTENT_FONT_FAMILY_DESC'),
					'depends'=>array(array('text', '!=', '')),
					'selector'=> array(
						'type'=>'font',
						'font'=>'{{ VALUE }}',
						'css'=>'.sppb-addon-content .sppb-addon-text { font-family: "{{ VALUE }}"; }'
					)
				),

				'text_fontsize'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_CONTENT_FONT_SIZE'),
					'std'=>'',
					'max'=>400,
					'responsive'=>true
				),

				'text_fontweight'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CONTENT_FONTWEIGHT'),
					'values'=>array(
						'100'=>100,
						'200'=>200,
						'300'=>300,
						'400'=>400,
						'500'=>500,
						'600'=>600,
						'700'=>700,
						'900'=>900,
					),
					'std'=>'',
				),

				'text_lineheight'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_CONTENT_LINE_HEIGHT'),
					'std'=>'',
					'max'=>400,
					'responsive'=>true
				),
                                
                'text_background'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_COLOR'),
				),
                'text_padding'=>array(
					'type'=>'padding',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_PADDING'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_PADDING_DESC'),
					'responsive' => true
				),

				'alignment'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_CONTENT_ALIGNMENT'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_CONTENT_ALIGNMENT'),
					'values'=>array(
						'sppb-text-left'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LEFT'),
						'sppb-text-center'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_CENTER'),
						'sppb-text-right'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_RIGHT'),
					),
					'std'=>'sppb-text-center',
				),

				'global_separator'=>array(
					'type'=>'separator',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_HOVER_OPTIONS'),
				),
				'addon_hover_bg'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_HOVER_BG'),
				),
				'addon_hover_boxshadow'=>array(
					'type'=>'boxshadow',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_HOVER_BOXSHADOW'),
					'std'=>'0 0 0 0 #ffffff'
				),
				'addon_hover_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_ADDON_HOVER_COLOR'),
				),
				'title_hover_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_TITLE_HOVER_COLOR'),
				),
				'icon_hover_bg'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_ICON_HOVER_BG'),
				),
				'icon_hover_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_FEATURE_BOX_ICON_HOVER_COLOR'),
				),

				'class'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS_DESC'),
					'std'=>''
				),

			),
		),
	)
);