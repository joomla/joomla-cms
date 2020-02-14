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
		'addon_name'=>'sp_testimonial',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_DESC'),
		'category'=>'Content',
		'attr'=>array(
			'general' => array(

				'admin_label'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL_DESC'),
					'std'=> ''
				),

				// Title
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

				'title_text_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR_DESC'),
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
					'max'=>400,
					'responsive'=>true
				),

				'title_lineheight'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_LINE_HEIGHT'),
					'std'=>'',
					'depends'=>array(array('title', '!=', '')),
					'max'=>400,
					'responsive'=>true
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

				'title_margin_top'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_TOP'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_TOP_DESC'),
					'placeholder'=>'10',
					'depends'=>array(array('title', '!=', '')),
					'max'=>400,
					'responsive'=>true
				),

				'title_margin_bottom'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_BOTTOM'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_BOTTOM_DESC'),
					'placeholder'=>'10',
					'depends'=>array(array('title', '!=', '')),
					'max'=>400,
					'responsive'=>true
				),

				'show_quote'=>array(
					'type'=>'checkbox',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_SHOW_ICON'),
					'values'=>array(
						1=>JText::_('JYES'),
						0=>JText::_('JNO'),
					),
					'std'=>1,
				),

				'icon_size'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_ICON_SIZE'),
					'std'=>array('md'=>48, 'sm'=>48, 'xs'=>48),
					'min'=>10,
					'max'=>200,
					'responsive'=>true,
					'depends'=>array(array('show_quote', '=', 1)),
				),

				'icon_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_ICON_COLOR'),
					'std'=>'#EDEEF2',
					'depends'=>array(array('show_quote', '=', 1)),
				),

				// Content
				'review'=>array(
					'type'=>'editor',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_REVIEW'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_REVIEW_DESC'),
					'std'=>'Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch.'
				),

				'review_size'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_REVIEW_SIZE'),
					'min'=>10,
					'max'=>200,
					'responsive'=>true,
					'depends'=>array(array('review', '!=', '')),
				),

				'review_font_family'=>array(
					'type'=>'fonts',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CONTENT_FONT_FAMILY'),
					'depends'=>array(array('review', '!=', '')),
					'selector'=> array(
						'type'=>'font',
						'font'=>'{{ VALUE }}',
						'css'=>'.sppb-addon-testimonial-review { font-family: "{{ VALUE }}"; }'
					)
				),

				'review_fontweight'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CONTENT_FONTWEIGHT'),
					'values'=>array(
						100=>100,
						200=>200,
						300=>300,
						400=>400,
						500=>500,
						600=>600,
						700=>700,
						800=>800,
						900=>900,
					),
					'depends'=>array(array('review', '!=', '')),
				),

				'review_line_height'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_REVIEW_LINE_HEIGHT'),
					'min'=>10,
					'max'=>200,
					'responsive'=>true,
					'depends'=>array(array('review', '!=', '')),
				),

				'review_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_REVIEW_COLOR'),
					'depends'=>array(array('review', '!=', '')),
				),
				'review_margin'=>array(
					'type'=>'margin',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CONTENT_MARGIN'),
					'responsive'=>true,
					'std'=>array('md'=>'','sm'=>'','xs'=>''),
				),
				//Name Options
				'name_separator'=>array(
					'type'=>'separator',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_PERSON_NAME_COMPANY_OPTION'),
				),
				'name'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_NAME'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_NAME_DESC'),
					'std'=>'John Doe'
				),

				'name_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_NAME_COLOR'),
				),
				'name_font_size'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_NAME_FONTSIZE'),
					'max'=>100,
					'responsive'=>true,
					'std'=>array('md'=>'','sm'=>'','xs'=>''),
				),
				'name_font_style'=>array(
					'type'=>'fontstyle',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_NAME_FONTSTYLE'),
				),
				'name_font_family'=>array(
					'type'=>'fonts',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_NAME_FONT_FAMILY'),
					'selector'=> array(
						'type'=>'font',
						'font'=>'{{ VALUE }}',
						'css'=>'.sppb-addon-testimonial-client { font-family: "{{ VALUE }}"; }'
					)
				),
				'name_line_height'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_NAME_LINEHEIGHT'),
					'max'=>100,
					'responsive'=>true,
					'std'=>array('md'=>'','sm'=>'','xs'=>''),
				),
				'name_margin'=>array(
					'type'=>'margin',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_MARGIN'),
					'responsive'=>true,
					'std'=>array('md'=>'0px 0px 0px 0px','sm'=>'0px 0px 0px 0px','xs'=>'0px 0px 0px 0px'),
				),
				//Company Options
				'company'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_COMPANY'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_COMPANY_DESC'),
					'std'=>  'CEO, JoomShaper',
				),

				'company_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_COMPANY_COLOR'),
				),
				'company_font_size'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_COMPANY_FONTSIZE'),
					'max'=>100,
					'responsive'=>true,
					'std'=>array('md'=>'','sm'=>'','xs'=>''),
				),
				'company_font_style'=>array(
					'type'=>'fontstyle',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_COMPANY_FONTSTYLE'),
				),
				'company_font_family'=>array(
					'type'=>'fonts',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_COMPANY_FONT_FAMILY'),
					'selector'=> array(
						'type'=>'font',
						'font'=>'{{ VALUE }}',
						'css'=>'.sppb-addon-testimonial-client-url { font-family: "{{ VALUE }}"; }'
					)
				),
				'company_line_height'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_COMPANY_LINEHEIGHT'),
					'max'=>100,
					'responsive'=>true,
					'std'=>array('md'=>'','sm'=>'','xs'=>''),
				),

				'designation_position'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_NAME_DESIGNATION_POSITION'),
					'values'=>array(
						'top'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_POSITION_TOP'),
						'bottom'=>JText::_('COM_SPPAGEBUILDER_ADDON_OPTIN_POSITION_BOTTOM'),
					),
					'std'=>'bottom'
				),

				//Avatar
				'avatar_separator'=>array(
					'type'=>'separator',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_AVATAR_OPTIONS'),
				),

				'avatar'=>array(
					'type'=>'media',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_AVATAR'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_AVATAR_DESC'),
				),

				'avatar_width'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_AVATAR_WIDTH'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_AVATAR_WIDTH_DESC'),
					'std'=>32,
					'min'=>16,
					'max'=>128
				),

				'avatar_shape'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_AVATAR_SHAPE'),
					'values' =>array(
						'sppb-avatar-sqaure'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_SQUARE'),
						'sppb-avatar-round'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_ROUNDED'),
						'sppb-avatar-circle'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_CIRCLE'),
					),
					'std' => 'sppb-avatar-circle'
				),

				'avatar_dis_block'=>array(
					'type'=>'checkbox',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_AVATAR_BLOCK'),
					'std'=>0,
				),

				'avatar_margin'=>array(
					'type'=>'margin',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_AVATAR_MARGIN'),
					'responsive'=>true,
					'std'=>array('md'=>'','sm'=>'','xs'=>''),
				),

				'link'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_URL'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_URL_DESC'),
					'std' => 'http://www.joomshaper.com'
				),
				'link_target'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LINK_NEWTAB'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LINK_NEWTAB_DESC'),
					'values'=>array(
						''=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_TARGET_SAME_WINDOW'),
						'_blank'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_TARGET_NEW_WINDOW'),
					),
					'depends'=> array(
						array('link', '!=', ''),
					)
				),
				'alignment'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_CONTENT_ALIGNMENT'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_CONTENT_ALIGNMENT_DESC'),
					'values'=>array(
						'sppb-text-left'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LEFT'),
						'sppb-text-center'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_CENTER'),
						'sppb-text-right'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_RIGHT'),
					),
					'std'=>'sppb-text-center',
				),
				//Rating
				'rating_separator'=>array(
					'type'=>'separator',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_RATING_OPTIONS'),
				),
				'client_rating_enable'=>array(
					'type'=>'checkbox',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_RATING_ENABLE'),
					'std'=>0
				),
				'client_rating'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_RATING'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_RATING_DESC'),
					'depends'=>array(
						array('client_rating_enable', '=', 1),
					),
					'max'=>5,
					'min'=>1,
					'std'=>5,
				),
				'client_rating_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_RATING_COLOR'),
					'depends'=>array(
						array('client_rating_enable', '=', 1),
					),
					'std'=>''
				),
				'client_unrated_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_UNRATED_COLOR'),
					'depends'=>array(
						array('client_rating_enable', '=', 1),
					),
					'std'=>''
				),
				'client_rating_fontsize'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_RATING_FONTSIZE'),
					'depends'=>array(
						array('client_rating_enable', '=', 1),
					),
					'responsive'=>true,
					'std'=>array('md'=>16),
				),
				'client_rating_margin'=>array(
					'type'=>'margin',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_CLIENT_RATING_MARGIN'),
					'depends'=>array(
						array('client_rating_enable', '=', 1),
					),
					'responsive'=>true,
					'std'=>'10px 5px 10px 5px',
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
