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

function getTitle($id)
{
    $db	= & JFactory::getDBO();
    $query = 'SELECT title FROM #__content as a WHERE a.id='.$id   ;
    //echo $query."<br>";
    $db->setQuery( $query );
    $tmp = $db->loadObject();
    return $tmp->title;
}

$articlesid = explode(",",$this->item->articlesid);
 

$str ='
    //FUNCTION AD LI =========================================
    function init_obj(){
    ';
if($articlesid)
{
    foreach($articlesid as $articleid)
    {
        //$str .='alert("'.getTitle($articleid).'");';
        $str .='var title = "'.getTitle($articleid).'" ;';
        if(!empty($articleid)) $str .= 'obj.AddId(  '.$articleid.', title);';
    }
}

$str .='
     //alert("init '.$articlesid.'");
     var myArray = String(document.id("jform_articlesid").value).split(\',\');
}';

$document = JFactory::getDocument();  
$document->addScriptDeclaration($str)


?>
<form action="<?php echo JRoute::_('index.php?option=com_fieldsattach&task=save&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="fieldsattach-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_fieldsattach_fieldsattach_DETAILS' ); ?></legend>
			<ul class="adminformlist">
<?php foreach($this->form->getFieldset('details') as $field): ?>
				<li><?php echo $field->label;echo $field->input;   ?></li>
<?php endforeach; ?>
                               <!-- <li><?php echo $this->form->getField('articlesid')->label ;    ?>
                                <?php echo $this->form->getField('articlesid')->input ;    ?>
                                </li>-->
			</ul>
                </fieldset>
                <fieldset class="adminform" >
                            <legend><?php echo JText::_( 'COM_FIELDSATTACH_FIELDSATTACH_LINKS' ); ?></legend>
                            <ul class="adminformlist">
                                <li>
                                    <?php echo $this->form->getField('relationinformation')->label ;    ?> 
                                </li>
                                <li  style="  padding:  25px  0px  0px  0px;">
                                    <fieldset class="adminform" style="width:90%; float: none; padding:  10px 10px 10px 10px;" >
                                            <legend><?php echo JText::_( 'COM_fieldsattach_fieldsattach_CATEGORY_LINKS' ); ?></legend>
                                            <?php echo $this->form->getField('catid')->label ;    ?>
                                            <?php echo $this->form->getField('catid')->input ;    ?>
                                            <?php echo $this->form->getField('recursive')->label ;    ?>
                                            <?php echo $this->form->getField('recursive')->input ;    ?>
                                    </fieldset>
                                </li>
                                <li style="text-align:center; font-size: 20px;   padding: 20px 0 50px 150px;">
                                    <?php echo $this->form->getField('otro')->label ;    ?>
                                </li>
                                <li>
                                      <fieldset class="adminform" style="width:90%; float: none; padding:  10px 10px 10px 10px;" >
                                            <legend><?php echo JText::_( 'COM_FIELDSATTACH_FIELDSATTACH_ARTICLES_LINKS' ); ?></legend>
                                             <?php echo $this->form->getField('selectarticle')->input ;    ?>
                                            <?php echo $this->form->getField('articlesid')->input ;    ?>
                                            <div style="width:100%; overflow: hidden;">
                                                <ul id="articleslist">
                                                    
                                                </ul>
                                            </div>

                                          
                                    </fieldset> 
                                </li> 

                            </ul>
                    </fieldset>
             
	</div>
       
	<div class="width-40 fltrt">
		<?php echo JHtml::_('sliders.start', 'fieldsattach-slider'); ?>
                    
		<?php echo JHtml::_('sliders.end'); ?>
	</div>

	<div>
		<input type="hidden" name="task" value="fieldsattachunidad.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

