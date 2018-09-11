<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;

/**
 *
 * Extends AdminModel to be aware of subforms
 *
 * @since 3.9
 *
 */
class AwareModel extends AdminModel
{

	/**
	 *
	 * An array of Joomla! Form objects
	 * Joomla\CMS\Form\Form
	 *
	 * @var array($pluralName)
	 *
	 */
	private $subforms = null;

	/**
	 *
	 * An array of Joomla! table objects
	 * Joomla\CMS\Table\Table
	 *
	 * @var array($pluralName)
	 *
	 */
	private $subtables = null;

	/**
	 *
	 * An array of Joomla! models of the type AdminModel
	 * Joomla\CMS\MVC\Model\AdminModel
	 *
	 * @var array($pluralName)
	 *
	 */
	private $submodels = null;

	/**
	 *
	 * @var string The prefix to use when loading submodels.
	 *
	 */
	private $prefix = null;

	/**
	 *
	 * {@inheritdoc}
	 * @see \Joomla\CMS\MVC\Model\AdminModel::save()
	 *
	 */
	public function save (array $data)
	{
		$result = parent::save($data);
		$this->saveSubForms($data);
		$this->checkForDeletions($data);
		return $result;
	}

	/**
	 *
	 * @param string $name
	 * @return Table[$pluralname]
	 *
	 */
	public function getSubTable (string $name)
	{
		return $this->getSubTables()[$name];
	}

	/**
	 *
	 * @param string $name
	 * @return AdminModel[$pluralname]
	 *
	 */
	public function getSubModel (string $name)

	{
		return $this->getSubModels()[$name];
	}

	/**
	 *
	 * @param string $name
	 * @return Form[$pluralname]
	 *
	 */
	public function getSubForm (string $name)
	{
		return $this->getSubForms()[$name];
	}

	/**
	 *
	 * iterates through the items that have originally been saved in the
	 * subform; to find $items the user wants deleted, and then actually
	 * delete them.
	 *
	 * @param array $data
	 *        	$return void
	 *
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
						$stillExists = true;
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

	private function getSubModels ()
	{
		if ($this->submodels == null and $this->subforms == null)
			$this->loadSubForms();
		return $this->submodels;
	}

	private function loadSubForms ()
	{
		$prefix = $this->getPrefix();
		foreach ($this->_forms as $tag => $form)
		{
			$this->subforms = array();
			foreach ($form->getFieldset() as &$formfield)
			{
				if ($formfield instanceof JFormFieldSubform)
				{
					$xmlElement = new SimpleXMLElement($formfield->__get('formsource'), null, $data_is_url = true);
					$name = (string) $xmlElement->fieldgroup->attributes()->name;

					$newform = new Form($name);
					$newform->load($xmlElement);
					$this->subforms[$name] = $newform;

					$modelname = $prefix . 'Model' . ucfirst($name);
					$this->submodels[$name] = new $modelname();
				}
			}
		}
	}

	private function getPrefix ()
	{
		if ($this->prefix == null)
		{
			// todo: make this more sensable; like asking the controller or
			// the form. For now letś guess
			$thing = Factory::getApplication()->scope;
			$thing = str_replace('com_', '', $thing);
			$this->prefix = ucfirst($thing);
		}
		return ($this->prefix);
	}

	/**
	 *
	 * @param string $prefix
	 *
	 */
	public function setPrefix (string $prefix)
	{
		$this->prefix = $prefix;
	}

	/**
	 *
	 * @return Form[$pluralname]
	 *
	 */
	public function getSubForms ()
	{
		if ($this->subforms == null)
			$this->loadSubForms();
		return $this->subforms;
	}

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
			$controller = new $controllerclass();
			$singularname = $controller->getModel()->get('name');
			$this->subtables[$pluralname] = $controller->getModel()->getTable($singularname);
		}
	}

	protected function getSubTables ()
	{
		if ($this->subtables == null)
			$this->loadSubTables();
		return $this->subtables;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Joomla\CMS\MVC\Model\FormModel::loadForm()
	 *
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
				$i = $i + 1;
			}
			$data->set($name, $value); // Push $items into the $form

			// Also remember which $items have been delivered , to recognise
			// if the user wants to delete any later on
			$statename = $formHash . '_' . $name;
			$app->setUserState($statename, $value);
		}
		return $form;
	}

	/**
	 *
	 * {@inheritdoc}
	 * @see \Joomla\CMS\MVC\Model\FormModel::getForm()
	 *
	 */
	public function getForm ($data = array(), $loadData = true)
	{
	}
}
