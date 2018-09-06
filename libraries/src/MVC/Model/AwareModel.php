<?php
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;




/**
 * @subpackage  amber
 *
 * @copyright   Copyright (C) 2005 - 2018 Chris Rutten. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * Purpose:    to make joomla's admin model aware of subforms so you can actually
 *             handle them in seperate tables
 *     
 * Usage:      (1) have your model extend this class instead of AdminModel
 *             (2) use the field-type "subform" in your form, like in the example below.
 *             (3) create a controller extending "AdminController" for your subform, as you
 *                 would for a regulare list-view (In this example AmberControllerHistories)
 *             (4) create a model extending "ListModel" for your subform, as your would for a
 *                 regular list-view. (In this example AmberModelHistories)
 *             (5) define your subform as you would define a form. In this example you would
 *                 create histories.xml
 *                
 *                 
 *             P.S. the "name" of the subform needs to match the filename of your formdefinition.xml
 *               
 *       EXAMPLE EXCERPT FROM THE MAIN FORM CONTAINING A SUBFORM   
 *     		<field
 *				name="histories"
 *				type="subform"
 *				formsource="/administrator/components/com_amber/models/forms/histories.xml"
 *				multiple="true"
 *				layout="joomla.form.field.subform.repeatable-table"
 *				/>
 *
 *     
 *       EXAMPLE EXCERPT FROM THE SUBFORM CALLED-UPON
 *      <fieldgroup name="histories">
 *		<field
 *				name="historyID"
 *				type="hidden"
 *				/>
 *		<field
 *				name="resourceID"
 *				type="resource"
 *				label="COM_AMBER_RESOURCE_TITLE_LABEL"
 *				description="COM_AMBER_RESOURCE_TITLE_DESC"
 *				class="inputbox"
 *				/>
 *
 *           
 *
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');


class AmberModelAdmin extends AdminModel
{
    /**
     * An array of stock Joomla Form objects 
     * @var Form[$pluralname]   
     */
    private $subforms = null;   
    
    /**
     * An array of table objects
     * @var Table[$pluralname]  
     */
    private $subtables = null;  
    
    /**
     * An array of sub models 
     * @var AdminModel[$pluralname]  
     */
    private $submodels = null; 
    
    /**
     * @var string  The prefix to use when loading submodels. 
     */
    private $prefix = null;    
    
    
    public function save($data)
    { 
        $result=parent::save($data);           
        $this->saveSubForms($data);           
        $this->checkForDeletions($data);
        return $result;
    }
    
    public function getSubTable($name)
    {
        return $this->getSubTables()[$name];
    }
    
    public function getSubModel($name)
    {
        return $this->getSubModels()[$name];
    }
    
    public function getSubForm($name)
    {
        return $this->getSubForms()[$name];
    }
    

    /**
     * iterates through the items that have originally been saved in the subform; to
     * find $items the user wants deleted, and then actually delete them.
     */
    private function checkForDeletions($data)
    {
        $app=Factory::getApplication();
        $formHash = key($this->_forms);
        foreach ($this->getSubForms() as $name=>$formitems)
        {
            $statename=$formHash . '_' . $name;
            $oldsubform = $app->getUserState($statename);
            $table=$this->getSubTable($name);
            $key = $table->getKeyName();  
            foreach ($oldsubform as $oldItem)
            {
                $stillExists=false;
                foreach ($data[$name] as $newItem)
                {
                    if ($newItem[$key]==$oldItem->$key) $stillExists=true;
                }
                if (!$stillExists) $table->delete($oldItem->$key);
            }
        }
    }
    
    

    
    
    /**
     *  traverse the available subtables to see which parts of the given $data to store in which $table 
     * 
     */
    private function saveSubForms($data)
    {

         
        foreach ($this->getSubForms() as &$subForm)
        { 
            $name=$subForm->getName();
            if ($saveableItems=$data[$name])
            {
                $table=$this->getSubTable($name);
                foreach ($saveableItems as &$item)
                { 
                  $table->save($item);
                }
                
            }
           
        }        

    }
    
    
   
    private function getSubModels()
    {
        if ($this->submodels==null and $this->subforms==null) $this->loadSubForms();
        return $this->submodels;
    }

    
    
    private function loadSubForms()
    {
        $prefix=$this->getPrefix(); 
        foreach($this->_forms as $tag=>$form)
        {
            $this->subforms = array();
            foreach ($form->getFieldset() as &$formfield)
            {
                if ($formfield instanceof JFormFieldSubform)
                { 
                    $xmlElement =new SimpleXMLElement($formfield->__get('formsource'),null,$data_is_url=true);
                    $name=(string) $xmlElement->fieldgroup->attributes()->name;
                    
                    $newform= new Form($name);
                    $newform->load($xmlElement);
                    $this->subforms[$name]=$newform;
                    
                    $modelname = $prefix.'Model'.ucfirst($name);
                    $this->submodels[$name] = new $modelname;
                }
            }
            
        } 
     }


     
     private function getPrefix()
     {
         if ($this->prefix==null)
         {
             //todo: make this more sensable; like asking the controller or the form. For now letś guess
             $thing=Factory::getApplication()->scope;
             $thing=str_replace('com_', '', $thing);
             $this->prefix = ucfirst($thing);
         }
         return ($this->prefix);
     }
     
     public function setPrefix ($prefix)
     {
         $this->prefix = $prefix;
     }
     
     
     public function getSubForms()
     { 
         if ($this->subforms==null) $this->loadSubForms();
         return $this->subforms;
     }
     

     private function loadSubTables()
     {
         $this->subtables= array();
         $prefix=$this->getPrefix();
         foreach($this->getSubForms() as &$subform)
         {
             $pluralname=$subform->getName();
             $controllerclass = ucfirst($prefix) . 'Controller' . ucfirst($pluralname);
             $filename=JPATH_COMPONENT_ADMINISTRATOR.'/controllers/'.$pluralname.'.php';
             require_once $filename;
             $controller= new $controllerclass;
             $singularname=$controller->getModel()->get('name');
             $this->subtables[$pluralname]=$controller->getModel()->getTable($singularname);

         }
     }
     
     
     protected function getSubTables()
     {
         if ($this->subtables==null) $this->loadSubTables();
         return $this->subtables;
     }
     
     
     public function loadForm($name, $source = null, $options = array(), $clear = false, $xpath = false)
     {
         $form=parent::loadForm($name, $source , $options , $clear , $xpath );
         $data=$form->getData();
         $formHash = key($this->_forms);
         $app=Factory::getApplication();
         foreach ($this->getSubForms() as $name=>$subform)
         {
             $items=$this->submodels[$name]->getItems();
             $i=0;
             $value=array();
             foreach ($items as $item)
             {
                 $index=$name.$i;
                 $value[$index]=$item;
                 $i=$i+1;
             }
             $data->set($name,$value);      // push $items into the $form
             
                                            // also remember what $items have been delivered , to recognise if the user wants to delete any later on
             $statename=$formHash . '_' . $name;
             $app->setUserState($statename, $value);
             
         }
         return $form;
     }
         
     
    public function getForm($data = array(), $loadData = true)
    {
    }
    
       
    
}
