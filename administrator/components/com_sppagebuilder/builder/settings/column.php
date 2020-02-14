<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2016 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

$column_settings = array(
	'attr' => array(
		'general' => array(

			'color'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_TEXT_COLOR'),
			),

			'background'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_COLOR'),
			),

			'background_image'=>array(
				'type'=>'media',
				'format'=>'image',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_IMAGE'),
				'std'=>'',
			),

			'overlay'=>array(
				'type'=>'color',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_OVERLAY'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_OVERLAY_DESC'),
				'depends' => array(
					array('background_image', '!=', ''),
				),
			),

			'background_repeat'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_REPEAT'),
				'values'=>array(
					'no-repeat'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_NO_REPEAT'),
					'repeat'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_REPEAT_ALL'),
					'repeat-x'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_REPEAT_HORIZONTALLY'),
					'repeat-y'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_REPEAT_VERTICALLY'),
					'inherit'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_INHERIT'),
				),
				'std'=>'no-repeat',
				'depends' => array(
					array('background_image', '!=', ''),
				),
			),

			'background_size'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_SIZE'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_SIZE_DESC'),
				'values'=>array(
					'cover'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_SIZE_COVER'),
					'contain'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_SIZE_CONTAIN'),
					'inherit'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_INHERIT'),
				),
				'std'=>'cover',
				'depends' => array(
					array('background_image', '!=', ''),
				),
			),

			'background_attachment'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_ATTACHMENT'),
				'values'=>array(
					'fixed'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_ATTACHMENT_FIXED'),
					'scroll'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_ATTACHMENT_SCROLL'),
					'inherit'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_INHERIT'),
				),
				'std'=>'scroll',
				'depends' => array(
					array('background_image', '!=', ''),
				),
			),

			'background_position'=>array(
				'type'=>'select',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_POSITION'),
				'values'=>array(
					'0 0'=>JText::_('COM_SPPAGEBUILDER_LEFT_TOP'),
					'0 50%'=>JText::_('COM_SPPAGEBUILDER_LEFT_CENTER'),
					'0 100%'=>JText::_('COM_SPPAGEBUILDER_LEFT_BOTTOM'),
					'50% 0'=>JText::_('COM_SPPAGEBUILDER_CENTER_TOP'),
					'50% 50%'=>JText::_('COM_SPPAGEBUILDER_CENTER_CENTER'),
					'50% 100%'=>JText::_('COM_SPPAGEBUILDER_CENTER_BOTTOM'),
					'100% 0'=>JText::_('COM_SPPAGEBUILDER_RIGHT_TOP'),
					'100% 50%'=>JText::_('COM_SPPAGEBUILDER_RIGHT_CENTER'),
					'100% 100%'=>JText::_('COM_SPPAGEBUILDER_RIGHT_BOTTOM'),
				),
				'std'=>'0 0',
				'depends' => array(
					array('background_image', '!=', ''),
				),
			),

			'items_align_center'=>array(
				'type'=>'checkbox',
				'title'=>JText::_('COM_SPPAGEBUILDER_ROW_COLUMNS_ALIGN_CENTER'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_ROW_COLUMNS_ALIGN_CENTER_DESC'),
				'std'=>0
			),

			'padding'=>array(
				'type'=>'padding',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_PADDING'),
				'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_PADDING_DESC'),
				'responsive'=> true
			),

			'boxshadow'=>array(
				'type'=>'boxshadow',
				'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BOXSHADOW'),
				'std'=>'0 0 0 0 #fff',
			),

			'class'=>array(
				'type' 		=> 'text',
				'title' 	=> JText::_('COM_SPPAGEBUILDER_CSS_CLASS'),
				'desc' 		=> JText::_('COM_SPPAGEBUILDER_CSS_CLASS_DESC')
				)
			),

			'responsive' => array(
				'sm_col' 		=> array(
					'type'		=> 'select',
					'title'		=> JText::_('COM_SPPAGEBUILDER_LAYOUT_TABLET'),
					'desc'		=> JText::_('COM_SPPAGEBUILDER_LAYOUT_TABLET_DESC'),
					'values'	=> array(
						'' => "",
						'col-sm-1' => 'col-sm-1',
						'col-sm-2' => 'col-sm-2',
						'col-sm-3' => 'col-sm-3',
						'col-sm-4' => 'col-sm-4',
						'col-sm-5' => 'col-sm-5',
						'col-sm-6' => 'col-sm-6',
						'col-sm-7' => 'col-sm-7',
						'col-sm-8' => 'col-sm-8',
						'col-sm-9' => 'col-sm-9',
						'col-sm-10' => 'col-sm-10',
						'col-sm-11' => 'col-sm-11',
						'col-sm-12' => 'col-sm-12',
					),
					'std'		=> '',
				),
				'xs_col' 		=> array(
					'type'		=> 'select',
					'title'		=> JText::_('COM_SPPAGEBUILDER_LAYOUT_MOBILE'),
					'desc'		=> JText::_('COM_SPPAGEBUILDER_LAYOUT_MOBILE_DESC'),
					'values'	=> array(
						'' => "",
						'col-xs-1' => 'col-xs-1',
						'col-xs-2' => 'col-xs-2',
						'col-xs-3' => 'col-xs-3',
						'col-xs-4' => 'col-xs-4',
						'col-xs-5' => 'col-xs-5',
						'col-xs-6' => 'col-xs-6',
						'col-xs-7' => 'col-xs-7',
						'col-xs-8' => 'col-xs-8',
						'col-xs-9' => 'col-xs-9',
						'col-xs-10' => 'col-xs-10',
						'col-xs-11' => 'col-xs-11',
						'col-xs-12' => 'col-xs-12',
					),
					'std'		=> '',
				),

				'separator'=>array(
					'type'=>'separator',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_VISIBILITY_OPTIONS')
				),

				'hidden_xs' 		=> array(
					'type'		=> 'checkbox',
					'title'		=> JText::_('COM_SPPAGEBUILDER_GLOBAL_HIDDEN_XS'),
					'desc'		=> JText::_('COM_SPPAGEBUILDER_GLOBAL_HIDDEN_XS_DESC'),
					'std'		=> '',
				),
				'hidden_sm' 		=> array(
					'type'		=> 'checkbox',
					'title'		=> JText::_('COM_SPPAGEBUILDER_GLOBAL_HIDDEN_SM'),
					'desc'		=> JText::_('COM_SPPAGEBUILDER_GLOBAL_HIDDEN_SM_DESC'),
					'std'		=> '',
				),
				'hidden_md' 		=> array(
					'type'		=> 'checkbox',
					'title'		=> JText::_('COM_SPPAGEBUILDER_GLOBAL_HIDDEN_MD'),
					'desc'		=> JText::_('COM_SPPAGEBUILDER_GLOBAL_HIDDEN_MD_DESC'),
					'std'		=> '',
				),
				
			),

			'animation' => array(
				'animation'=>array(
					'type'=>'animation',
					'title'=>JText::_('COM_SPPAGEBUILDER_ANIMATION'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ANIMATION_DESC')
				),

				'animationduration'=>array(
					'type'=>'number',
					'title'=>JText::_('COM_SPPAGEBUILDER_ANIMATION_DURATION'),
					'desc'=> JText::_('COM_SPPAGEBUILDER_ANIMATION_DURATION_DESC'),
					'std'=>'300',
					'placeholder'=>'300',
				),

				'animationdelay'=>array(
					'type'=>'number',
					'title'=>JText::_('COM_SPPAGEBUILDER_ANIMATION_DELAY'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ANIMATION_DELAY_DESC'),
					'std'=>'0',
					'placeholder'=>'300',
				),
			),
			)
		);
