<?php
/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

//no direct accees
defined ('_JEXEC') or die ('resticted aceess');

$rowSettings = array(
	'type'=>'general',
	'title'=>'',
	'attr'=>array(

		'name' => array(
			'type'		=> 'text',
			'title'		=> JText::_('HELIX_SECTION_TITLE'),
			'desc'		=> JText::_('HELIX_SECTION_TITLE_DESC'),
			'std'		=> ''
			),
		'background_color' => array(
			'type'		=> 'color',
			'title'		=> JText::_('HELIX_SECTION_BACKGROUND_COLOR'),
			'desc'		=> JText::_('HELIX_SECTION_BACKGROUND_COLOR_DESC')
			),
		'color' => array(
			'type'		=> 'color',
			'title'		=> JText::_('HELIX_SECTION_TEXT_COLOR'),
			'desc'		=> JText::_('HELIX_SECTION_TEXT_COLOR_DESC')
			),
		'background_image' => array(
			'type'		=> 'media',
			'title'		=> JText::_('HELIX_SECTION_BACKGROUND_IMAGE'),
			'desc'		=> JText::_('HELIX_SECTION_BACKGROUND_IMAGE_DESC'),
			'std'		=> '',
			),
		'background_repeat'=>array(
			'type'=>'select',
			'title'=>JText::_('HELIX_BG_REPEAT'),
			'desc'=>JText::_('HELIX_BG_REPEAT_DESC'),
			'values'=>array(
				'no-repeat'=>JText::_('HELIX_BG_REPEAT_NO'),
				'repeat'=>JText::_('HELIX_BG_REPEAT_ALL'),
				'repeat-x'=>JText::_('HELIX_BG_REPEAT_HORIZ'),
				'repeat-y'=>JText::_('HELIX_BG_REPEAT_VERTI'),
				'inherit'=>JText::_('HELIX_BG_REPEAT_INHERIT'),
				),
			'std'=>'no-repeat',
			),
		'background_size' => array(
			'type'		=> 'select',
			'title'=>JText::_('HELIX_BG_SIZE'),
			'desc'=>JText::_('HELIX_BG_SIZE_DESC'),
			'values'=>array(
				'cover'=>JText::_('HELIX_BG_COVER'),
				'contain'=>JText::_('HELIX_BG_CONTAIN'),
				'inherit'=>JText::_('HELIX_BG_INHERIT'),
				),
			'std'=>'cover',
			),
		'background_attachment'=>array(
			'type'=>'select',
			'title'=>JText::_('HELIX_BG_ATTACHMENT'),
			'desc'=>JText::_('HELIX_BG_ATTACHMENT_DESC'),
			'values'=>array(
				'fixed'=>JText::_('HELIX_BG_ATTACHMENT_FIXED'),
				'scroll'=>JText::_('HELIX_BG_ATTACHMENT_SCROLL'),
				'inherit'=>JText::_('HELIX_BG_ATTACHMENT_INHERIT'),
				),
			'std'=>'fixed',
			),
		'background_position' => array(
			'type'		=> 'select',
			'title'=>JText::_('HELIX_BG_POSITION'),
			'desc'=>JText::_('HELIX_BG_POSITION_DESC'),
			'values'=>array(
				'0 0'=>JText::_('HELIX_BG_POSITION_LEFT_TOP'),
				'0 50%'=>JText::_('HELIX_BG_POSITION_LEFT_CENTER'),
				'0 100%'=>JText::_('HELIX_BG_POSITION_LEFT_BOTTOM'),
				'50% 0'=>JText::_('HELIX_BG_POSITION_CENTER_TOP'),
				'50% 50%'=>JText::_('HELIX_BG_POSITION_CENTER_CENTER'),
				'50% 100%'=>JText::_('HELIX_BG_POSITION_CENTER_BOTTOM'),
				'100% 0'=>JText::_('HELIX_BG_POSITION_RIGHT_TOP'),
				'100% 50%'=>JText::_('HELIX_BG_POSITION_RIGHT_CENTER'),
				'100% 100%'=>JText::_('HELIX_BG_POSITION_RIGHT_BOTTOM'),
				),
			'std'=>'0 0',
			),
		'link_color' => array(
			'type'		=> 'color',
			'title'		=> JText::_('HELIX_LINK_COLOR'),
			'desc'		=> JText::_('HELIX_LINK_COLOR_DESC')
			),
		'link_hover_color' => array(
			'type'		=> 'color',
			'title'		=> JText::_('HELIX_LINK_HOVER_COLOR'),
			'desc'		=> JText::_('HELIX_LINK_HOVER_COLOR_DESC')
			),
		'hidden_xs' 		=> array(
			'type'		=> 'checkbox',
			'title'		=> JText::_('HELIX_HIDDEN_MOBILE'),
			'desc'		=> JText::_('HELIX_HIDDEN_MOBILE_DESC'),
			'std'		=> '',
			),
		'hidden_sm' 		=> array(
			'type'		=> 'checkbox',
			'title'		=> JText::_('HELIX_HIDDEN_TABLET'),
			'desc'		=> JText::_('HELIX_HIDDEN_TABLET_DESC'),
			'std'		=> '',
			),
		'hidden_md' 		=> array(
			'type'		=> 'checkbox',
			'title'		=> JText::_('HELIX_HIDDEN_DESKTOP'),
			'desc'		=> JText::_('HELIX_HIDDEN_DESKTOP_DESC'),
			'std'		=> '',
			),
		'padding' => array(
			'type'		=> 'text',
			'title'		=> JText::_('HELIX_PADDING'),
			'desc'		=> JText::_('HELIX_PADDING_DESC'),
			'std'		=> ''
			),
		'margin' => array(
			'type'		=> 'text',
			'title'		=> JText::_('HELIX_MARGIN'),
			'desc'		=> JText::_('HELIX_MARGIN_DESC'),
			'std'		=> ''
			),
		'fluidrow' 		=> array(
			'type'		=> 'checkbox',
			'title'		=> JText::_('HELIX_ROW_FULL_WIDTH'),
			'desc'		=> JText::_('HELIX_ROW_FULL_WIDTH_DESC'),
			'std'		=> '',
			),
		'custom_class' => array(
			'type'		=> 'text',
			'title'		=> JText::_('HELIX_CUSTOM_CLASS'),
			'desc'		=> JText::_('HELIX_CUSTOM_CLASS_DESC'),
			'std'		=> ''
			),
		)
	);

$columnSettings = array(
	'type'=>'general',
	'title'=>'',
	'attr'=>array(

		'column_type' => array(
			'type'		=> 'checkbox',
			'title'		=> JText::_('HELIX_COMPONENT'),
			'desc'		=> JText::_('HELIX_COMPONENT_DESC'),
			'std'=>'',
			),
		'name' => array(
			'type'		=> 'select',
			'title'		=> JText::_('HELIX_MODULE_POSITION'),
			'desc'		=> JText::_('HELIX_MODULE_POSITION_DESC'),
			'values'	=> array(),
			'std'=>'none',
			),
		'hidden_xs' 		=> array(
			'type'		=> 'checkbox',
			'title'		=> JText::_('HELIX_HIDDEN_MOBILE'),
			'desc'		=> JText::_('HELIX_HIDDEN_MOBILE_DESC'),
			'std'		=> '',
			),
		'hidden_sm' 		=> array(
			'type'		=> 'checkbox',
			'title'		=> JText::_('HELIX_HIDDEN_TABLET'),
			'desc'		=> JText::_('HELIX_HIDDEN_TABLET_DESC'),
			'std'		=> '',
			),
		'hidden_md' 		=> array(
			'type'		=> 'checkbox',
			'title'		=> JText::_('HELIX_HIDDEN_DESKTOP'),
			'desc'		=> JText::_('HELIX_HIDDEN_DESKTOP_DESC'),
			'std'		=> '',
			),
		'sm_col' 		=> array(
			'type'		=> 'select',
			'title'		=> JText::_('HELIX_TABLET_LAYOUT'),
			'desc'		=> JText::_('HELIX_TABLET_LAYOUT_DESC'),
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
			'title'		=> JText::_('HELIX_MOBILE_LAYOUT'),
			'desc'		=> JText::_('HELIX_MOBILE_LAYOUT_DESC'),
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
		'custom_class' => array(
			'type'		=> 'text',
			'title'		=> JText::_('HELIX_CUSTOM_CLASS'),
			'desc'		=> JText::_('HELIX_CUSTOM_CLASS_DESC'),
			'std'		=> ''
			),
		)
	);

class RowColumnSettings{

	private static function getInputElements( $key, $attr )
	{
		return call_user_func(array( 'SpType' . ucfirst( $attr['type'] ), 'getInput'), $key, $attr );
	}

	public static function getRowSettings($row_settings = array())
	{

		$output = '<div class="hidden">';
		$output .= '<div class="row-settings">';

		foreach ($row_settings['attr'] as $key => $rowAttr) {
			$output .= self::getInputElements( $key, $rowAttr );
		}

		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	public static function getColumnSettings($col_settings = array())
	{

		$col_settings['attr']['name']['values'] = self::getPositionss();

		$output = '<div class="hidden">';
		$output .= '<div class="column-settings">';

		foreach ($col_settings['attr'] as $key => $rowAttr) {
			$output .= self::getInputElements( $key, $rowAttr );
		}

		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	public static function getTemplateName()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('template')));
		$query->from($db->quoteName('#__template_styles'));
		$query->where($db->quoteName('client_id') . ' = 0');
		$query->where($db->quoteName('home') . ' = 1');
		$db->setQuery($query);

		return $db->loadObject()->template;
	}


	public static function getPositionss() {

	    $db = JFactory::getDBO();
	    $query = 'SELECT `position` FROM `#__modules` WHERE  `client_id`=0 AND ( `published` !=-2 AND `published` !=0 ) GROUP BY `position` ORDER BY `position` ASC';

	    $db->setQuery($query);
	    $dbpositions = (array) $db->loadAssocList();

		$template  = self::getTemplateName();

	    $templateXML = JPATH_SITE.'/templates/'.$template.'/templateDetails.xml';
	    $template = simplexml_load_file( $templateXML );
	    $options = array();

	    foreach($dbpositions as $positions) $options[] = $positions['position'];

	    foreach($template->positions[0] as $position)  $options[] =  (string) $position;

	    $options = array_unique($options);

	    $selectOption = array();
	    sort($selectOption);

	    foreach($options as $option) $selectOption[$option] = $option;

	    return $selectOption;
	}

	public static function getSettings($config = null){
		$data = '';
		if ($config) {
			foreach ($config as $key => $value) {
				$data .= ' data-'.$key.'="'.$value.'"';
			}
		}
		return $data;
	}
}