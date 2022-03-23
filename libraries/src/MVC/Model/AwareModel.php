<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\CMS\MVC\Model;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;

/**
 * Extends AdminModel to be aware of subforms
 *
 * @since  __DEPLOY_VERSION__
 *
 */
class AwareModel extends AdminModel
{
	/**
	 * An array of Joomla! Form objects
	 * Joomla\CMS\Form\Form
	 *
	 * @var    array($pluralName)
	 * @since  __DEPLOY_VERSION__
	 */
	private $subforms = null;
	
	/**
	 * An array of Joomla! table objects
	 * Joomla\CMS\Table\Table
	 *
	 * @var    array($pluralName)
	 * @since  __DEPLOY_VERSION__
	 */
	private $subtables = null;
	
	/*
	 * An array of Joomla! models of the type AdminModel
	 * Joomla\CMS\MVC\Model\AdminModel
	 *
	 * @var    array($pluralName)
	 * @since  __DEPLOY_VERSION__
	 */
	private $submodels = null;
	
	/**
	 * The prefix to use when loading submodels.
	 *
	 * @var    string  
	 * @since  3.9
	 */
	private $prefix = null;
	
	/**
	 * Have the parent save the base Form and afterwards
	 * save the subforms by ourselves.
	 *
	 * @param   array  $data  The form data.
	 * 
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   3.9
	 */
	public function save (array $data)
	{
		$result = parent::save($data);
		$this->saveSubForms($data);
		$this->checkForDeletions($data);
		return $result;
	}
	/**
	 * Retreive a certain subTable
	 *
	 * @param   string  $name  The name of the subtable to retrieve
	 * 
	 * @return  Table
	 *
	 * @since   3.9
	 */
	public function getSubTable (string $name)
	{
		return $this->getSubTables()[$name];
	}
	/**
	 * Retreive a certain subModel
	 *
	 * @param   string  $name  The name of the submodel to retrieve
	 * 
	 * @return  AdminModel
	 *
	 * @since   3.9
	 */
	public function getSubModel (string $name)
	{
		return $this->getSubModels()[$name];
	}
	/**
	 * Retrieve a certain subForm
	 *
	 * @param   string  $name  The name of the subForm to retrieve
	 * 
	 * @return  Form
	 *
	 * @since   3.9
	 */
	public function getSubForm (string $name)
	{
		return $this->getSubForms()[$name];
	}
	/**
	 * Compare the posted form with the previously served form so see if anything
	 * has been deleted by the user in the meantime
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  void
	 *
	 * @since   3.9
	 */
	private function checkForDeletions (array $data)
	{
		$app = Factory::getApplication();
		$formHash = key($this->_forms);
		foreach ($this->getSubForms() as $name => $formitems)
		{
			$statename = $formHash . '_' . $name;
			$oldsubform = $app->getUserState($statename);
			$table = $this->getSubTable($name);
			$key = $table->getKeyName();
			foreach ($oldsubform as $oldItem)
			{
				$stillExists = false;
				foreach ($data[$name] as $newItem)
				{
					if ($newItem[$key] == $oldItem->$key)
					{
						$stillExists = true;
					}
				}
				if (! $stillExists)
				{
					$table->delete($oldItem->$key);
				}
			}
		}
	}
	/**
	 * traverse the available subtables to see which parts of the given $data to
	 * store in which $table
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 * @since   3.9
	 */
	private function saveSubForms (array $data)
	{
		$app = Factory::getApplication();
		$myOwnPK = $this->getTable()->getKeyName();
		$myOwnID = $app->input->getInt($myOwnPK);
		foreach ($this->getSubForms() as &$subForm)
		{
			$name = $subForm->getName();
			if ($saveableItems = $data[$name])
			{
				$table = $this->getSubTable($name);
				foreach ($saveableItems as &$item)
				{
					$item[$myOwnPK] = $myOwnID;
					$table->save($item);
				}
			}
		}
	}
	/**
	 * Retrieve an array with all submodels in this object
	 *
	 * @return  array  An array of Joomla models
	 * 
	 * @since   3.9
	 */
	private function getSubModels ()
	{
		if ($this->submodels == null and $this->subforms == null)
		{
			$this->loadSubForms();
		}
		return $this->submodels;
	}
	/**
	 * Loads all the subforms in this form object
	 *
	 * @return  void
	 *
	 * @since   3.9
	 */
	private function loadSubForms ()
	{
		$prefix = $this->getPrefix();
		foreach ($this->_forms as $tag => $form)
		{
			$this->subforms = array();
			foreach ($form->getFieldset() as &$formfield)
			{
				if ($formfield instanceof \JFormFieldSubform)
				{
					$xmlElement = new \SimpleXMLElement($formfield->__get('formsource'), null, $data_is_url = true);
					$name = (string) $xmlElement->fieldgroup->attributes()->name;
					$newform = new Form($name);
					$newform->load($xmlElement);
					$this->subforms[$name] = $newform;
					$modelname = $prefix . 'Model' . ucfirst($name);
					$this->submodels[$name] = new $modelname;
				}
			}
		}
	}
	/**
	 * Retrieve the prefix to use when loading forms or tables
	 * Guessing one if none has been set.
	 *
	 * @return  string
	 *
	 * @since   3.9
	 */
	private function getPrefix ()
	{
		if ($this->prefix == null)
		{
			// TODO: Make this more sensable; like asking the controller or
			// the form. For now letś guess
			$thing = Factory::getApplication()->scope;
			$thing = str_replace('com_', '', $thing);
			$this->prefix = ucfirst($thing);
		}
		return ($this->prefix);
	}
	/**
	 * Sets the prefix to use when loading tables or forms
	 *
	 * @param   string  $prefix  The prefix to use when loading tables or forms
	 * 
	 * @return  void
	 *
	 * @since   3.9
	 */
	public function setPrefix (string $prefix)
	{
		$this->prefix = $prefix;
	}
	/**
	 * Retrieves a certain subform
	 *
	 * @return  Form
	 * 
	 * @since   3.9
	 */
	public function getSubForms ()
	{
		if ($this->subforms == null)
		{
			$this->loadSubForms();
		}
		return $this->subforms;
	}
	/**
	 * Loads the subtables into the object
	 *
	 * @return  void
	 *
	 * @since   3.9
	 */
	private function loadSubTables ()
	{
		$this->subtables = array();
		$prefix = $this->getPrefix();
		foreach ($this->getSubForms() as &$subform)
		{
			$pluralname = $subform->getName();
			$controllerclass = ucfirst($prefix) . 'Controller' . ucfirst($pluralname);
			$filename = JPATH_COMPONENT_ADMINISTRATOR . '/controllers/' . $pluralname . '.php';
			require_once $filename;
			$controller = new $controllerclass;
			$singularname = $controller->getModel()->get('name');
			$this->subtables[$pluralname] = $controller->getModel()->getTable($singularname);
		}
	}
	/**
	 * Retreives an array with all the subforms in this form
	 *
	 * @return  array($pluralName)
	 *
	 * @since   3.9
	 */
	protected function getSubTables ()
	{
		if ($this->subtables == null)
		{
			$this->loadSubTables();
		}
		return $this->subtables;
	}
	/**
	 * Method to get a form object.
	 *
	 * @param   string   $name     The name of the form.
	 * @param   string   $source   The form source. Can be XML string if file flag is set to false.
	 * @param   array    $options  Optional array of options for the form creation.
	 * @param   boolean  $clear    Optional argument to force load a new form.
	 * @param   string   $xpath    An optional xpath to search for the fields.
	 *
	 * @return  Form|boolean  Form object on success, false on error.
	 *
	 * @see     \Joomla\CMS\Form\Form
	 * @since   1.6
	 */
	public function loadForm ($name, $source = null, $options = array(), $clear = false, $xpath = false)
	{
		$form = parent::loadForm($name, $source, $options, $clear, $xpath);
		$data = $form->getData();
		$formHash = key($this->_forms);
		$app = Factory::getApplication();
		$myOwnPK = $this->getTable()->getKeyName();
		$myOwnID = $app->input->getInt($myOwnPK);
		foreach ($this->getSubForms() as $name => $subform)
		{
			$submodel = $this->submodels[$name];
			$submodel->setState('filter.' . $myOwnPK, $myOwnID);
			$items = $submodel->getItems();
			$i = 0;
			$value = array();
			foreach ($items as $item)
			{
				$index = $name . $i;
				$value[$index] = $item;
				$i ++;
			}
			
			// Push $items into the $form
			$data->set($name, $value);
			
			// Also remember which $items have been delivered , to recognise
			// if the user wants to delete any later on
			$statename = $formHash . '_' . $name;
			$app->setUserState($statename, $value);
		}
		return $form;
	}
	/**
	 * Abstract method for getting the form from the model.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  Form|boolean  A Form object on success, false on failure
	 *
	 * @since   1.6
	 */
	public function getForm ($data = array(), $loadData = true)
	{
	}
}
