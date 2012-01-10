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

$session =& JFactory::getSession();
$articleid =  $session->get('articleid');
$fieldsattachid = $session->get('fieldsattachid');
 
//defino una sesion y guardo datos
session_start(); 
setcookie('loginin',"true" , time() + 3600,'/');
?>
<form action="<?php echo JRoute::_('index.php?option=com_fieldsattach&task=save&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="fieldsattach-form" class="form-validate">
      <br /><br /><div class=" ">
		<div class="toolbar-list" id="toolbar">
                    <ul>
                    <li class="button" id="toolbar-apply">
                    <a href="#" onclick="javascript:Joomla.submitbutton('fieldsattachimage.apply')" class="toolbar">
                    <span class="icon-32-apply">
                    </span>
                    Save
                    </a>
                    </li>

                    <li class="button" id="toolbar-save">
                    <a href="#" onclick="javascript:Joomla.submitbutton('fieldsattachimage.save')" class="toolbar">
                    <span class="icon-32-save">
                    </span>
                    Save &amp; Close
                    </a>
                    </li>
                    <li class="button" id="toolbar-cancel">
                    <a href="#" onclick="javascript:Joomla.submitbutton('fieldsattachimage.cancel')" class="toolbar">
                    <span class="icon-32-cancel">
                    </span>
                    Close
                    </a>
                    </li>
                    </ul>
                </div> <div class="pagetitle icon-48-mediamanager"><h2>Image Manager: Edit Image</h2></div> </div>
 <br /><br />

    <div class="width-100 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_( 'COM_fieldsattach_fieldsattach_DETAILS' ); ?></legend>

                        <input name="jform[articleid]" id="jform_articleid" value="<?php echo $articleid;?>" type="hidden" />
			<input name="jform[fieldsattachid]" id="jform_fieldsattachid" value="<?php echo $fieldsattachid;?>" type="hidden" />


                        <ul   style="overflow:hidden;" >
                                <li  style="overflow:hidden;"><?php echo $this->form->getLabel('title'); ?>
				<?php echo $this->form->getInput('title'); ?></li>

				<li  style="overflow:hidden;"><?php echo $this->form->getLabel('image1'); ?>
				<?php echo $this->form->getInput('image1'); ?>
                                <?php if ($this->item->image1) : ?>
					 <a class="modal"  href="../<?php echo $this->item->image1; ?>"><img src="components/com_fieldsattach/images/icon-image.png" alt="<?php echo $this->escape($this->item->title); ?>" /></a>
				<?php endif; ?>
                                <?php if ($this->item->image1 && JFolder::exists("../phpimageeditor")) : ?>
					 <a   href="#" onclick="window.open('../phpimageeditor/index.php?imagesrc=<?php echo  '../'.$this->item->image1 ; ?>' , 'ventana1' , 'width=900,height=600,scrollbars=YES')  "><img src="components/com_fieldsattach/images/icon-image-modify.png" alt="<?php echo $this->escape($this->item->title); ?>" /></a>
				<?php endif; ?>
                                 </li>

				<li  style="overflow:hidden;"><?php echo $this->form->getLabel('image2'); ?>
				<?php echo $this->form->getInput('image2'); ?>
                                 <?php if ($this->item->image2) : ?>
					 <a class="modal" href="../<?php echo $this->item->image2; ?>"><img src="components/com_fieldsattach/images/icon-image.png" alt="<?php echo $this->escape($this->item->title); ?>" /></a>
				<?php endif; ?>
                                         <?php if ($this->item->image2 && JFolder::exists("../phpimageeditor")) : ?>
					 <a   href="#" onclick="window.open('../phpimageeditor/index.php?imagesrc=<?php echo  '../'.$this->item->image2 ; ?>' , 'ventana1' , 'width=900,height=600,scrollbars=YES')  "><img src="components/com_fieldsattach/images/icon-image-modify.png" alt="MODIFY: <?php echo $this->escape($this->item->title); ?>" /></a>
				<?php endif; ?>
                                </li>

                                <li  style="overflow:hidden;"><?php echo $this->form->getLabel('image3'); ?>
				<?php echo $this->form->getInput('image3'); ?>
                                <?php if ($this->item->image3) : ?>
					 <a class="modal" href="../<?php echo $this->item->image3; ?>"><img src="components/com_fieldsattach/images/icon-image.png" alt="<?php echo $this->escape($this->item->title); ?>" /></a>
				<?php endif; ?>
                                         <?php if ($this->item->image3 && JFolder::exists("../phpimageeditor")) : ?>
					 <a   href="#" onclick="window.open('../phpimageeditor/index.php?imagesrc=<?php echo  '../'.$this->item->image3 ; ?>' , 'ventana1' , 'width=900,height=600,scrollbars=YES')  "><img src="components/com_fieldsattach/images/icon-image-modify.png" alt="MODIFY: <?php echo $this->escape($this->item->title); ?>" /></a>
				<?php endif; ?>
                                </li>

				<li  style="overflow:hidden;"><?php echo $this->form->getLabel('published'); ?>
				<?php echo $this->form->getInput('published'); ?></li>

                                <li  style="overflow:hidden;"><?php echo $this->form->getLabel('ordering'); ?>
				<?php echo $this->form->getInput('ordering'); ?></li>

                                <li  style="overflow:hidden;"><?php echo $this->form->getLabel('description'); ?>
				<div style="width:500px;" ><?php echo $this->form->getInput('description'); ?></div></li>

				<li  style="overflow:hidden;"><?php echo $this->form->getLabel('id'); ?>
				<?php echo $this->form->getInput('id'); ?></li>
			</ul>
                                
                                
                </fieldset>
	</div>
 
	<div class="width-80 fltrt"> 
		<?php echo JHtml::_('sliders.start', 'fieldsattach-slider'); ?>

<?php foreach ($params as $name => $fieldset): ?>
		<?php echo JHtml::_('sliders.panel', JText::_($fieldset->label), $name.'-params');?>

		<fieldset class="panelform" >
			<ul class="adminformlist">
                            
	 


			</ul>
		</fieldset>
<?php endforeach; ?>

		<?php echo JHtml::_('sliders.end'); ?>
	</div>

	<div>
		<input type="hidden" name="task" value="fieldsattachunidad.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

