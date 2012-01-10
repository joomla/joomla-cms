<?php
/**
 * @version		$Id: edit.php 15 2011-09-02 18:37:15Z cristian $
 * @package		fieldsattach
 * @subpackage		Components
 * @copyright		Copyright (C) 2011 - 2020 Open Source Cristian Grañó, Inc. All rights reserved.
 * @author		Cristian Grañó
 * @link		http://joomlacode.org/gf/project/fieldsattach_1_6/
 * @license		License GNU General Public License version 2 or later
 */
// No direct access
defined('_JEXEC') or die('Restricted access');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
$params = $this->form->getFieldsets('params');
?>
<div style="overflow: hidden;">
<form action="<?php echo JRoute::_('index.php?option=com_fieldsattach&task=save&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="fieldsattach-form" class="form-validate">
	<div class="width-60 fltlft" style=" ">
		<fieldset class="adminform"> 
			<legend><?php echo JText::_( 'COM_fieldsattach_fieldsattach_DETAILS' ); ?></legend>
			<ul class="adminformlist">
<?php foreach($this->form->getFieldset('details') as $field): ?>
				<li><?php echo $field->label; ?>
                                    <?php  if ($field->name != "jform[type]") { 
                                        echo $field->input; 
                                    }
                                    else{
                                         
                                        echo '<select id="jform_type" name="jform[type]" class="inputbox required">';
                                        echo ' <option value="">Select one please</option>';
                                        JPluginHelper::importPlugin('fieldsattachment'); // very important
                                        //select
                                        $db = &JFactory::getDBO(  );
                                        $query = 'SELECT *  FROM #__extensions as a WHERE a.folder = "fieldsattachment"  AND a.enabled= 1';
                                        $db->setQuery( $query );
                                        $results = $db->loadObjectList();
                                        foreach ($results as $obj)
                                        {
                                            $function  = "plgfieldsattachment_".$obj->element."::construct();";
                                            eval('echo '. $function.';');
                                            $function  = "plgfieldsattachment_".$obj->element."::getoptionConfig('".$field->value."');";
                                            eval('echo '. $function.';');
                                             
                                        }
                                        /*echo ' <option value="">Select one please</option>
                                            <option value="checkbox">Checkbox</option>
                                            <option value="input" selected="selected">Input</option>
                                            <option value="textarea">TextArea</option>
                                            <option value="select">Select</option>
                                            <option value="select_multiple">Select Multiple</option>
                                            <option value="file">File</option>
                                            <option value="image">Image</option>
                                            <option value="picasaimage">Picasa image</option>
                                            <option value="image gallery">Image gallery</option>
                                            <option value="listunits">List of units</option>
                                            <option value="vimeo">Vimeo</option>
                                            <option value="youtube">Youtube</option>';*/
                                        echo '</select>';
                                    }
                                    ?>
                                </li>
<?php endforeach; ?>
                                
			</ul>
               </fieldset>
	</div>

	<div class="width-40 fltrt" style=" overflow: hidden;">
            <?php

            $db = &JFactory::getDBO(  );
            $query = 'SELECT *  FROM #__extensions as a WHERE a.folder = "fieldsattachment"  AND a.enabled= 1';
            $db->setQuery( $query );
            $results = $db->loadObjectList();
           
	   
            foreach ($results as $obj)
            {
                echo JHtml::_('sliders.start', 'fieldsattach-slider-'.$obj->element);
                echo  fieldsattachHelper::getForm($obj->element);
                /*$function  = "plgfieldsattachment_".$obj->element."::construct();";
                eval('echo '. $function.';'); 
                $function  = "plgfieldsattachment_".$obj->element."::renderHelpConfig();";
                eval('echo '. $function.';');*/
                echo  JHtml::_('sliders.end');
            } 
            ?>
		
	</div>

	<div>
		<input type="hidden" name="task" value="fieldsattachunidad.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form></div>

