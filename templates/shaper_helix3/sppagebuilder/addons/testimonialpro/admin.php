<?php
/**
* @package SP Page Builder
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/
//no direct accees
defined ('_JEXEC') or die ('restricted aceess');

SpAddonsConfig::addonConfig(
	array(
		'type'=>'repeatable',
		'addon_name'=>'sp_testimonialpro',
		'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_PRO'),
		'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_PRO_DESC'),
		'category'=>'Slider',
		'attr'=>array(
			'general' => array(

				'admin_label'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL_DESC'),
					'std'=> ''
				),

				'autoplay'=>array(
					'type'=>'checkbox',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_PRO_AUTOPLAY'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_PRO_AUTOPLAY_DESC'),
					'values'=>array(
						1=>JText::_('JYES'),
						0=>JText::_('JNO'),
					),
					'std'=>1,
				),

				'interval'=>array(
					'type'=>'number',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_PRO_INTERVAL'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_PRO_INTERVAL_DESC'),
					'std'=> 5,
					'depends'=> array(
						array('autoplay', '=', 1),
					)
				),

				'speed'=>array(
					'type'=>'number',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_PRO_SPEED'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_PRO_SPEED_DESC'),
					'std'=> 600,
				),

				'controls'=>array(
					'type'=>'checkbox',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CAROUSEL_SHOW_CONTROLLERS'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_CAROUSEL_SHOW_CONTROLLERS_DESC'),
					'values'=>array(
						1=>JText::_('JYES'),
						0=>JText::_('JNO'),
					),
					'std'=>1,
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

				'arrows'=>array(
					'type'=>'checkbox',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_PRO_SHOW_ARROWS'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_PRO_SHOW_ARROWS_DESC'),
					'values'=>array(
						1=>JText::_('JYES'),
						0=>JText::_('JNO'),
					),
					'std'=>1,
				),

				'class'=>array(
					'type'=>'text',
					'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS'),
					'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_CLASS_DESC'),
					'std'=> ''
				),

				// Repeatable Items
				'sp_testimonialpro_item'=>array(
					'title'=>JText::_('Testimonials'),

					'attr'=>array(
						'title'=>array(
							'type'=>'text',
							'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_PRO_ITEM_TITLE'),
							'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_PRO_ITEM_TITLE_DESC'),
							'std'=>'John Doe',
						),

						'avatar'=>array(
							'type'=>'media',
							'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_PRO_CLIENT_IMAGE'),
							'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_PRO_CLIENT_IMAGE_DESC'),
						),

						'message'=>array(
							'type'=>'editor',
							'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_PRO_ITEM_TEXT'),
							'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_PRO_ITEM_TEXT_DESC'),
							'std'=> 'Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et.'
						),

						'url'=>array(
							'type'=>'text',
							'title'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_PRO_CLIENT_URL'),
							'desc'=>JText::_('COM_SPPAGEBUILDER_ADDON_TESTIMONIAL_PRO_CLIENT_URL_DESC'),
						),

					),
				),
			),
		),
	)
);
