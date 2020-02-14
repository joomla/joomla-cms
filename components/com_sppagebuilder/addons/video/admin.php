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
		'addon_name'=>'sp_video',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO_DESC'),
		'category'=>'Media',
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
	
				'url'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO_URL'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO_URL_DESC'),
					'std'=>'https://www.youtube.com/watch?v=BWLRMBrKH_c',
					'depends'=>array(
						array('mp4_enable', '!=', 1)
					)
				),
				'show_rel_video'=>array(
					'type'=>'checkbox',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO_OWN_CHANNEL_REL'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO_OWN_CHANNEL_REL_DESC'),
					'std'=> 0,
					'depends'=>array(
						array('mp4_enable', '!=', 1)
					)
				),

				'no_cookie'=>array(
					'type'=>'checkbox',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO_NO_COOKIE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO_NO_COOKIE_DESC'),
					'std'=>0,
					'depends'=>array(
						array('mp4_enable', '!=', 1)
					)
				),

				// Mp4 Video
				'mp4_enable'=>array(
					'type'=>'checkbox',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO_MP4_ENABLE'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO_MP4_ENABLE_DESC'),
					'std'=> 0,
				),

				'mp4_video'=>array(
					'type'=>'media',
					'format'=>'video',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO_MP4'),
					'depends'=>array(
						array('mp4_enable', '!=', 0)
					),
					'std'=>'https://www.joomshaper.com/media/videos/2017/11/10/pb-intro-video.mp4'
				),
	
				'ogv_video'=>array(
					'type'=>'media',
					'format'=>'video',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO_OGV'),
					'depends'=>array(
						array('mp4_enable', '!=', 0)
					)
				),
				
				'video_poster'=>array(
					'type'=>'media',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO_POSTER'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO_POSTER_DESC'),
					'show_input' => true,
					'std'=>'https://www.joomshaper.com/images/2017/11/10/real-time-frontend.jpg',
					'depends'=>array(
						array('mp4_enable', '!=', 0)
					)
				),

				'show_control'=> array(
					'type'=> 'checkbox',
					'title'=> JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO_CONTROL'),
					'desc'=> JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO_CONTROL_DESC'),
					'std'=> 1,
					'depends'=>array(
						array('mp4_enable', '!=', 0)
					),
				),
				'video_loop'=> array(
					'type'=> 'checkbox',
					'title'=> JText::_('COM_SPPAGEBUILDER_ROW_VIDEO_LOOP'),
					'desc'=> JText::_('COM_SPPAGEBUILDER_ROW_VIDEO_LOOP_DESC'),
					'std'=> 0,
					'depends'=>array(
						array('mp4_enable', '!=', 0)
					),
				),
				'video_mute'=> array(
					'type'=> 'checkbox',
					'title'=> JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO_MUTE'),
					'desc'=> JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO_MUTE_DESC'),
					'std'=> 1,
					'depends'=>array(
						array('mp4_enable', '!=', 0)
					),
				),
				'autoplay_video'=> array(
					'type'=> 'checkbox',
					'title'=> JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO_AUTOPLAY'),
					'desc'=> JText::_('COM_SPPAGEBUILDER_ADDON_VIDEO_AUTOPLAY_DESC'),
					'std'=> 0,
					'depends'=>array(
						array('mp4_enable', '!=', 0)
					),
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
