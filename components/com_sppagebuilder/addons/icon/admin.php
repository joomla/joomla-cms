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
		'addon_name'=>'sp_icon',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ICON'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ICON_DESC'),
		'category'=>'Media',
		'attr'=>array(

			'general' => array(
				'admin_label'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL_DESC'),
					'std'=> ''
				),

				'name'=>array(
					'type'=>'icon',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_ICON_NAME'),
					'std'=> 'fa-cogs'
				),

				'link'=>array(
					'type'=>'media',
					'format'=>'attachment',
					'hide_preview'=>true,
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LINK'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LINK_DESC'),
					'std'=>'',
					'depends'=>array(
						array('name', '!=', ''),
					),
				),

				'target'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_TARGET'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_TARGET_DESC'),
					'values'=>array(
						''=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_TARGET_SAME_WINDOW'),
						'_blank'=>JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_TARGET_NEW_WINDOW'),
					),

					'depends'=>array(
						array('name', '!=', ''),
						array('link', '!=', ''),
					),
				),
			
				'size'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_ICON_SIZE'),
					'std'=>array('md'=>36),
					'max'=>400,
					'responsive'=>true
				),

				'width'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_WIDTH'),
					'std'=>array('md'=>96),
					'max'=>500,
					'responsive'=>true
				),

				'height'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_HEIGHT'),
					'std'=>array('md'=>96),
					'max'=>500,
					'responsive'=>true
				),

				'color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_COLOR'),
				),

				'background'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_COLOR'),
				),

				'border_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_COLOR'),
				),

				'border_width'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_WIDTH'),
					'placeholder'=>'3',
					'responsive'=>true
				),

				'border_radius'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_RADIUS'),
					'placeholder'=>'5',
					'max'=>500,
					'responsive'=>true
				),

				'margin'=>array(
					'type'=>'margin',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_MARGIN'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_MARGIN_DESC'),
					'placeholder'=>'10',
					'responsive'=>true
				),

				'separator'=>array(
					'type'=>'separator',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_MOUSE_HOVER_OPTIONS')
				),

				'use_hover'=>array(
					'type'=>'checkbox',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ICON_USE_HOVER'),
					'std'=>0,
				),

				'hover_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_COLOR_HOVER'),
					'depends'=>array(
						array('use_hover', '=', 1)
					)
				),

				'hover_background'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BACKGROUND_COLOR_HOVER'),
					'depends'=>array(
						array('use_hover', '=', 1)
					)
				),

				'hover_border_color'=>array(
					'type'=>'color',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_COLOR_HOVER'),
					'depends'=>array(
						array('use_hover', '=', 1)
					)
				),

				'hover_border_width'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_WIDTH'),
					'responsive'=>true,
					'depends'=>array(
						array('use_hover', '=', 1)
					)
				),

				'hover_border_radius'=>array(
					'type'=>'slider',
					'title'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_BORDER_RADIUS'),
					'max'=>500,
					'responsive'=>true,
					'depends'=>array(
						array('use_hover', '=', 1)
					)
				),

				'hover_effect'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ICON_HOVER_EFFECT'),
					'values'=>array(
						''=>JText::_('COM_SPPAGEBUILDER_ADDON_ICON_HOVER_EFFECT_NONE'),
						'zoom-in'=>JText::_('COM_SPPAGEBUILDER_ADDON_ICON_HOVER_EFFECT_ZOOM_IN'),
						'zoom-out'=>JText::_('COM_SPPAGEBUILDER_ADDON_ICON_HOVER_EFFECT_ZOOM_OUT'),
						'rotate'=>JText::_('COM_SPPAGEBUILDER_ADDON_ICON_HOVER_EFFECT_ROTATE'),
					),
					'std'=>'zoom-in',
				),

				'alignment'=>array(
					'type'=>'select',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ICON_ALIGNMENT'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ICON_ALIGNMENT_DESC'),
					'values'=>array(
						'sppb-text-left'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_LEFT'),
						'sppb-text-center'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_CENTER'),
						'sppb-text-right'=>JText::_('COM_SPPAGEBUILDER_GLOBAL_RIGHT'),
					),
					'std'=>'sppb-text-center',
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
