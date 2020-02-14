<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

namespace HelixUltimate;

defined ('_JEXEC') or die();

jimport( 'joomla.filesystem.file' );
jimport('joomla.filesystem.folder');
require_once __DIR__.'/helper.php';

use Joomla\CMS\Form as JoomlaForm;
use HelixUltimate\Helper\Helper as Helper;

$app = \JFactory::getApplication();
$input = $app->input;

if(($input->get('option', '', 'STRING') == 'com_ajax') && ($input->get('helix', '', 'STRING') == 'ultimate') && ($input->get('id', 0, 'INT') != 0))
{
    \JHtml::_('jquery.framework');
    \JHtml::_('script', 'jui/cms.js', array('version' => 'auto', 'relative' => true));
}

class Options{

    public function renderBuilderSidebar()
    {

        $input  = \JFactory::getApplication()->input;
        $id = $input->get('id',NULL);

        $templateStyle = Helper::getTemplateStyle($id);
        $formData = array();

        if(isset($templateStyle->params)){
            $formData = json_decode($templateStyle->params);
        }
        // Set custom field data for social share button
        if( !isset( $formData->social_share_lists )){
            $formData->social_share_lists = array('facebook','twitter','linkedin');
        }
        $form = new JoomlaForm\Form('template');
        $form->loadFile( \JPATH_ROOT.'/templates/' . $templateStyle->template . '/options.xml');
        if($formData){
            $form->bind($formData);
        } else {
            $layout_file = \JPATH_ROOT.'/templates/' . $templateStyle->template . '/options.json';
            $formData = file_get_contents($layout_file);
            $form->bind(json_decode($formData));
        }

        $fieldsets = $form->getFieldsets();

        $raw_html = '<div id="helix-ultimate-options">';
        $raw_html .= '<form id="helix-ultimate-style-form" action="index.php">';

        foreach( $fieldsets as $key => $fieldset ) {

            $raw_html .= $this->renderFieldsetStart($fieldset);
            $fields = $form->getFieldset($key);

            $fieldArray = array();

            foreach( $fields as $key => $field ) {
              $group = $field->getAttribute('helixgroup') ? $field->getAttribute('helixgroup') : 'no-group';
              $filed_html = $this->renderInputField( $field, $group );
              $fieldArray[$group]['fields_html'][] = $filed_html;
            }

            $raw_html .= $this->renderGroups($fieldArray);
            $raw_html .= $this->renderFieldsetEnd();
        }

        $raw_html .= '</form>';
        $raw_html .= '</div>';

        return $raw_html;
    }

    private function renderFieldsetStart( $fieldset )
    {

        $html  = '<div class="helix-ultimate-fieldset helix-ultimate-fieldset-'. $fieldset->name .' clearfix">';
        $html .= '<div class="helix-ultimate-fieldset-header">';
        $html .= '<div class="helix-ultimate-fieldset-toggle-icon"><i class="fa fa-long-arrow-left"></i></div>';
        $html .= '<div class="helix-ultimate-fieldset-header-inner" data-fieldset="'. $fieldset->name .'">';
        $html .= '<span class="helix-ultimate-fieldset-icon"><i class="'. ( ( isset( $fieldset->icon ) && $fieldset->icon )? $fieldset->icon : 'fa fa-address-book-o' ) .'"></i></span>';
        $html .= '<span class="helix-ultimate-fieldset-title">'. \JText::_($fieldset->label) .'</span>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="helix-ultimate-group-list">';

        return $html;
    }

    private function renderFieldsetEnd()
    {
        return '</div></div>';
    }


    private function renderGroups($groups)
    {
        $html = '';
        foreach( $groups as $key => $group ){
          if($key != 'no-group') {
            $html .= $this->renderGroupStart( $key );
          }

          $html .= $this->getFields($group['fields_html']);

          if($key != 'no-group') {
            $html .= $this->renderGroupEnd();
          }
        }

        return $html;
    }

    private function renderGroupStart( $group )
    {
        $html  = '<div class="helix-ultimate-group-wrap helix-ultimate-group-'. $group .'">';
        $html .= '<div class="helix-ultimate-group-header-box">';
        $html .= '<span class="helix-ultimate-group-toggle-icon">';
        $html .= '<i class="fa fa-angle-down" aria-hidden="true"></i>';
        $html .= '<i class="fa fa-angle-up" aria-hidden="true"></i>';
        $html .= '</span>';
        $html .= '<span class="helix-ultimate-group-title">'. \JText::_('HELIX_ULTIMATE_GROUP_' . strtoupper($group)) .'</span>';
        $html .= '<span class="helix-ultimate-group-more-icon"></span>';
        $html .= '</div>';
        $html .= '<div class="helix-ultimate-field-list">';

        return $html;
    }

    private function renderGroupEnd()
    {
        return '</div></div>';
    }

    private function getFields( $fields )
    {
        $html = '';
        foreach( $fields as $field ){
            $html .= $field;
        }

        return $html;
    }

    private function renderInputField($field = '', $group = '')
    {

        $showon = $field->getAttribute('showon');
        $attribs = '';
        if($showon) {
          $attribs .= ' data-showon=\'' . json_encode(self::parseShowOnConditions($showon)) . '\'';
        }

        $field_html = '';
        $field_html .= '<div class="control-group ' . (( $group ) ? 'group-style-'.$group : '') . '"'. $attribs .'>';
        
        $field_html .= '<div class="control-group-inner">';
        if(!$field->getAttribute('hideLabel')) {
          $field_html .= '<div class="control-label">' . $field->label .'</div>';
        }
        $field_html .= '<div class="controls">';
        $field_html .= $field->input;
        $field_html .= '</div>';
        $field_html .= '</div>';

        if($field->getAttribute('description') != '') {
            $field_html .= '<div class="control-help">' . \JText::_($field->getAttribute('description')) . '</div>';
        }
        
        $field_html .= '</div>';

        return $field_html;
    }

    public static function parseShowOnConditions($showOn, $formControl = null, $group = null)
  	{
  		// Process the showon data.
  		if (!$showOn)
  		{
  			return array();
  		}

  		$formPath = $formControl ?: '';

  		if ($group)
  		{
  			$groups = explode('.', $group);

  			// An empty formControl leads to invalid shown property
  			// Use the 1st part of the group instead to avoid.
  			if (empty($formPath) && isset($groups[0]))
  			{
  				$formPath = $groups[0];
  				array_shift($groups);
  			}

  			foreach ($groups as $group)
  			{
  				$formPath .= '[' . $group . ']';
  			}
  		}

  		$showOnData  = array();
  		$showOnParts = preg_split('#(\[AND\]|\[OR\])#', $showOn, -1, PREG_SPLIT_DELIM_CAPTURE);
  		$op          = '';

  		foreach ($showOnParts as $showOnPart)
  		{
  			if (($showOnPart === '[AND]') || $showOnPart === '[OR]')
  			{
  				$op = trim($showOnPart, '[]');
  				continue;
  			}

  			$compareEqual     = strpos($showOnPart, '!:') === false;
  			$showOnPartBlocks = explode(($compareEqual ? ':' : '!:'), $showOnPart, 2);

  			$showOnData[] = array(
  				'field'  => $formPath ? $formPath . '[' . $showOnPartBlocks[0] . ']' : $showOnPartBlocks[0],
  				'values' => explode(',', $showOnPartBlocks[1]),
  				'sign'   => $compareEqual === true ? '=' : '!=',
  				'op'     => $op,
  			);

  			if ($op !== '')
  			{
  				$op = '';
  			}
  		}

  		return $showOnData;
  	}
}
