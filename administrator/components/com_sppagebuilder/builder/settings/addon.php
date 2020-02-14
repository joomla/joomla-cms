<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

$addon_global_settings = array(
	'style' => array(
		'global_options'=>array(
			'type'=>'separator',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_OPTIONS'),
		),
		'global_text_color'=>array(
			'type'=>'color',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_TEXT_COLOR')
		),
		'global_link_color'=>array(
			'type'=>'color',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LINK_COLOR'),
		),
		'global_link_hover_color'=>array(
			'type'=>'color',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LINK_COLOR_HOVER'),
		),
		'global_use_background'=>array(
			'type'=>'checkbox',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_ENABLE_BACKGROUND_OPTIONS'),
			'std'=>0
		),
		'global_background_color'=>array(
			'type'=>'color',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_COLOR'),
			'depends'=>array('global_use_background'=>1)
		),
		'global_background_image'=>array(
			'type'=>'media',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_IMAGE'),
			'depends'=>array('global_use_background'=>1)
		),
		'global_background_repeat'=>array(
			'type'=>'select',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_REPEAT'),
			'values'=>array(
				'no-repeat'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_NO_REPEAT'),
				'repeat-all'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_REPEAT_ALL'),
				'repeat-horizontally'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_REPEAT_HORIZONTALLY'),
				'repeat-vertically'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_REPEAT_VERTICALLY'),
				'inherit'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_INHERIT'),
			),
			'std'=>'no-repeat',
			'depends'=>array(
				array('global_use_background', '=', 1),
				array('global_background_image', '!=', '')
			)
		),
		'global_background_size'=>array(
			'type'=>'select',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_SIZE'),
			'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_SIZE_DESC'),
			'values'=>array(
				'cover'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_SIZE_COVER'),
				'contain'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_SIZE_CONTAIN'),
				'inherit'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_INHERIT'),
			),
			'std'=>'cover',
			'depends'=>array(
				array('global_use_background', '=', 1),
				array('global_background_image', '!=', '')
			)
		),
		'global_background_attachment'=>array(
			'type'=>'select',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_ATTACHMENT'),
			'values'=>array(
				'fixed'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_ATTACHMENT_FIXED'),
				'scroll'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_ATTACHMENT_SCROLL'),
				'inherit'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_INHERIT'),
			),
			'std'=>'inherit',
			'depends'=>array(
				array('global_use_background', '=', 1),
				array('global_background_image', '!=', '')
			)
		),
		'global_user_border'=>array(
			'type'=>'checkbox',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_USE_BORDER'),
			'std'=>0
		),
		'global_border_width'=>array(
			'type'=>'slider',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_WIDTH'),
			'std'=>'',
			'depends'=>array('global_user_border'=>1),
			'responsive'=> true
		),
		'global_border_color'=>array(
			'type'=>'color',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_COLOR'),
			'depends'=>array('global_user_border'=>1)
		),
		'global_boder_style'=>array(
			'type'=>'select',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE'),
			'values'=>array(
				'none'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_NONE'),
				'solid'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_SOLID'),
				'double'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_DOUBLE'),
				'dotted'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_DOTTED'),
				'dashed'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_STYLE_DASHED'),
			),
			'depends'=>array('global_user_border'=>1)
		),
		'global_border_radius'=>array(
			'type'=>'slider',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_RADIUS'),
			'std'=>0,
			'max'=>500,
			'responsive'=> true
		),
		'global_margin'=>array(
			'type'=>'margin',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_MARGIN'),
			'std'=>array('md'=> '0px 0px 30px 0px', 'sm'=> '0px 0px 20px 0px', 'xs'=> '0px 0px 10px 0px'),
			'responsive' => true
		),
		'global_padding'=>array(
			'type'=>'padding',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_PADDING'),
			'std'=>'',
			'responsive' => true
		),
		'global_boxshadow'=>array(
			'type'=>'boxshadow',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BOXSHADOW'),
			'std'=>'0 0 0 0 #ffffff'
		),
		'global_use_animation'=>array(
			'type'=>'checkbox',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_USE_ANIMATION'),
			'std'=>0
		),
		'global_animation'=>array(
			'type'=>'animation',
			'title'=>JText::_('COM_SPPAGEBUILDER_ANIMATION'),
			'desc'=>JText::_('COM_SPPAGEBUILDER_ANIMATION_DESC'),
			'depends'=>array('global_use_animation'=>1)
		),

		'global_animationduration'=>array(
			'type'=>'number',
			'title'=>JText::_('COM_SPPAGEBUILDER_ANIMATION_DURATION'),
			'desc'=> JText::_('COM_SPPAGEBUILDER_ANIMATION_DURATION_DESC'),
			'std'=>'300',
			'placeholder'=>'300',
			'depends'=>array('global_use_animation'=>1)
		),

		'global_animationdelay'=>array(
			'type'=>'number',
			'title'=>JText::_('COM_SPPAGEBUILDER_ANIMATION_DELAY'),
			'desc'=>JText::_('COM_SPPAGEBUILDER_ANIMATION_DELAY_DESC'),
			'std'=>'0',
			'placeholder'=>'300',
			'depends'=>array('global_use_animation'=>1)
		),
	),

	'advanced' => array(
		'use_global_width'=>array(
			'type'=>'checkbox',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_USE_WIDTH'),
			'std'=>'0',
		),
		'global_width' => array(
			'type'=>'slider',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_WIDTH'),
			'max'=>100,
			'responsive'=>true,
			'depends'=>array('use_global_width'=>1)
		),
		'hidden_md'=>array(
			'type'=>'checkbox',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_HIDDEN_MD'),
			'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_HIDDEN_MD_DESC'),
			'std'=>'0',
			),

		'hidden_sm'=>array(
			'type'=>'checkbox',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_HIDDEN_SM'),
			'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_HIDDEN_SM_DESC'),
			'std'=>'0',
			),

		'hidden_xs'=>array(
			'type'=>'checkbox',
			'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_HIDDEN_XS'),
			'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_HIDDEN_XS_DESC'),
			'std'=>'0',
			),

		'acl' => array(
			'type' => 'accesslevel',
			'title' => JText::_('COM_SPPAGEBUILDER_ACCESS'),
			'desc' => JText::_('COM_SPPAGEBUILDER_ACCESS_DESC'),
			'placeholder' => '',
			'std' 			=> '',
			'multiple' => true
			)
		),
		'interaction' => array(
			'while_scroll_view'=> array(
				'type'=> 'interaction_view',
				'title' => JText::_('COM_SPPAGEBUILDER_INTERACTION_WHILTE_SCROLL_VIEW'),
				"desc"=> JText::_('COM_SPPAGEBUILDER_INTERACTION_WHILTE_SCROLL_VIEW_DESC'),
				'attr' => array(
					'enable_while_scroll_view'=>array(
						'type'=>'checkbox',
						'title'=> JText::_('COM_SPPAGEBUILDER_INTERACTION_WHILTE_SCROLL_VIEW_TITLE'),
						'desc'=> JText::_('COM_SPPAGEBUILDER_INTERACTION_WHILTE_SCROLL_VIEW_TITLE_DESC'),
						'std'=> 0,
					),
					
					'on_scroll_actions'=>array(
						'type' => 'timeline',
						'title' => JText::_('COM_SPPAGEBUILDER_INTERACTION_WHILTE_SCROLL_ACTION_TITLE'),
						'desc' => JText::_('COM_SPPAGEBUILDER_INTERACTION_WHILTE_SCROLL_ACTION_TITLE_DESC'),
						'depends'=>array('enable_while_scroll_view'=>1),
						'options' => array(
							array(
								'name'=>'move',
								'title'=> JText::_('COM_SPPAGEBUILDER_INTERACTION_WHILTE_SCROLL_ACTION_MOVE'),
								'property'=> array(
									'x'=>'0',
									'y'=>'0',
									'z'=>'0'
								),
								'range'=> array(
									'max'=> 500,
									'min'=> -500,
									'step'=> 1
								),
								'warning_message' => JText::_('COM_SPPAGEBUILDER_INTERACTION_WHILTE_SCROLL_ACTION_MOVE_WARNING'),
							),
							array(
								'name'=>'scale',
								'title'=> JText::_('COM_SPPAGEBUILDER_INTERACTION_WHILTE_SCROLL_ACTION_SCALE'),
								'property'=> array(
									'x'=>'1',
									'y'=>'1',
									'z'=>'1',
								),
								'range'=> array(
									'max'=> 2,
									'min'=> 0,
									'step'=> 0.1
								),
								'warning_message' => JText::_('COM_SPPAGEBUILDER_INTERACTION_WHILTE_SCROLL_ACTION_SCALE_WARNING'),
							),
							array(
								'name'=>'rotate',
								'title'=> JText::_('COM_SPPAGEBUILDER_INTERACTION_WHILTE_SCROLL_ACTION_ROTATE'),
								'property'=> array(
									'x'=>'0',
									'y'=>'0',
									'z'=>'0'
								),
								'range'=> array(
									'max'=> 180,
									'min'=> -180,
									'step'=> 1
								),
								'warning_message' => JText::_('COM_SPPAGEBUILDER_INTERACTION_WHILTE_SCROLL_ACTION_ROTATE_WARNING'),
							),
							array(
								'name'=>'skew',
								'title'=> JText::_('COM_SPPAGEBUILDER_INTERACTION_WHILTE_SCROLL_ACTION_SKEW'),
								'property'=> array(
									'x'=>'0',
									'y'=>'0'
								),
								'range'=> array(
									'max'=> 80,
									'min'=> -80,
									'step'=> 1
								),
								'warning_message' => JText::_('COM_SPPAGEBUILDER_INTERACTION_WHILTE_SCROLL_ACTION_SKEW_WARNING'),
							),
							array(
								'name'=>'opacity',
								'title'=> JText::_('COM_SPPAGEBUILDER_INTERACTION_WHILTE_SCROLL_ACTION_OPACITY'),
								'property'=> array('value'=>'0'),
								'range'=> array(
									'max'=> 1,
									'min'=> 0,
									'step'=> 0.1
								),
								'warning_message' => JText::_('COM_SPPAGEBUILDER_INTERACTION_WHILTE_SCROLL_ACTION_OPACITY_WARNING'),
							),
							array(
								'name'=>'blur',
								'title'=> JText::_('COM_SPPAGEBUILDER_INTERACTION_WHILTE_SCROLL_ACTION_BLUR'),
								'property'=> array('value'=>'0'),
								'range'=> array(
									'max'=> 100,
									'min'=> 0,
									'step'=> 1
								),
								'warning_message' => JText::_('COM_SPPAGEBUILDER_INTERACTION_WHILTE_SCROLL_ACTION_BLUR_WARNING'),
							),
						)
					),
				)
			),
			'mouse_movement' => array(
				'type'=> 'interaction_view',
				'title' => JText::_('COM_SPPAGEBUILDER_INTERACTION_MOUSE_MOVEMENT'),
				"description"=> JText::_('COM_SPPAGEBUILDER_INTERACTION_MOUSE_MOVEMENT_DESC'),
				"attr" => array(
					'enable_tilt_effect'=>array(
						'type'=>'checkbox',
						'title'=> JText::_('COM_SPPAGEBUILDER_INTERACTION_ENABLE_TILT_EFFECT_TITLE'),
						'desc'=> JText::_('COM_SPPAGEBUILDER_INTERACTION_ENABLE_TILT_EFFECT_TITLE_DESC'),
						'std'=> 0,
					),
					'mouse_tilt_direction'=>array(
						'type'=>'select',
						'title'=> JText::_('COM_SPPAGEBUILDER_INTERACTION_ENABLE_TILT_EFFECT_DIRECTION_TITLE'),
						'values'=>array(
							'direct'=> JText::_('COM_SPPAGEBUILDER_INTERACTION_ENABLE_TILT_EFFECT_DIRECTION_FORWARD'),
							'opposite'=> JText::_('COM_SPPAGEBUILDER_INTERACTION_ENABLE_TILT_EFFECT_DIRECTION_OPPOSITE')
						),
						'std' => 'direct',
						'depends'=>array('enable_tilt_effect'=>1)
					),
					'mouse_tilt_speed'=>array(
						'type'=>'slider',
						'title'=> JText::_('COM_SPPAGEBUILDER_INTERACTION_ENABLE_TILT_EFFECT_SPEED_TITLE'),
						'std'=>'1',
						'min' => 1,
						'max' => 10,
						'step' => 0.5,
						'depends'=>array('enable_tilt_effect'=>1)
					),
					'mouse_tilt_max'=>array(
						'type'=>'slider',
						'title'=> JText::_('COM_SPPAGEBUILDER_INTERACTION_ENABLE_TILT_EFFECT_MAX_TITLE'),
						'std'=> '15',
						'min' => 5,
						'max' => 75,
						'step' => 5,
						'depends'=>array('enable_tilt_effect'=>1)
					),
					'enable_tablet'=>array(
						'type'=>'checkbox',
						'title'=> JText::_('COM_SPPAGEBUILDER_INTERACTION_ENABLE_TABLET'),
						'desc'=> JText::_('COM_SPPAGEBUILDER_INTERACTION_ENABLE_TABLET_DESC'),
						'depends'=>array('enable_tilt_effect'=>1),
						'std'=> 0,
					),
					'enable_mobile'=>array(
						'type'=>'checkbox',
						'title'=> JText::_('COM_SPPAGEBUILDER_INTERACTION_ENABLE_MOBILE'),
						'desc'=> JText::_('COM_SPPAGEBUILDER_INTERACTION_ENABLE_MOBILE_DESC'),
						'depends'=>array('enable_tilt_effect'=>1),
						'std'=> 0,
					),
				)
		   )
		)
	);
