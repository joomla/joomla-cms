<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Model
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JModelRecord extends JModelCollection
{
	/**
	 * Method to get a single record.
	 *
	 * @param integer $pk    The id of the primary key.
	 * @param string  $class the class name of the return item default is JRegistryCms
	 *
	 * @throws ErrorException
	 * @return  object  instance of $class.
	 */
	public function getItem($pk = null, $class = 'JRegistryCms')
	{
		if (empty($pk))
		{
			$context = $this->getContext();
			$pk      = (int) $this->getState($context . '.id');
		}

		$dbo = $this->getDbo();
		$query = $dbo->getQuery(true);
		$query->select('a.*');
		$query->from($this->getTableName().' AS a');

		$this->observers->update('onBeforeGetItem', array($this, $query, $pk, $class));

		$query->where('a.'.$this->getKeyName().' = '.$pk);

		$dbo->setQuery($query);
		$item = $dbo->loadObject($class);

		if(!$item instanceof $class)
		{
			$item = new $class;
		}

		$this->observers->update('onAfterGetItem', array($this, $query, $item));

		return $item;
	}

	/**
	 * Convenience Method to get the JForm object with the jform control value
	 *
	 * @param string  $name
	 * @param string  $source
	 * @param array $config
	 *
	 * @return bool|JForm
	 */
	public function getForm($name = null, $source = null, $config = array())
	{
		$config += $this->config;

		if(is_null($source))
		{
			$source = $config['resource'];
		}

		if(is_null($name))
		{
			$name = $config['option'] . '.' . $config['resource'];
		}

		$this->observers->update('onBeforeGetForm', array($this, $name, $source, $config));

		/** @var JForm $form */
		$form   = $this->loadForm($name, $source, $config);

		$this->observers->update('onAfterGetForm', array($this, $form));

		return $form;
	}

	/**
	 * Method to get a form object.
	 *
	 * @param   string    $name   The name of the form.
	 * @param   string    $source The form source. Can be XML string if file flag is set to false.
	 * @param   array     $config Optional array of options for the form creation.
	 * @param   boolean   $clear  Optional argument to force load a new form.
	 * @param bool|string $xpath  An optional xpath to search for the fields.
	 *
	 * @return  mixed  JForm object on success, False on error.
	 */
	public function loadForm($name, $source = null, $config = array(), $clear = false, $xpath = false)
	{
		// Handle the optional arguments.
		if(!isset($config['control']))
		{
			$config['control'] = 'jform';
		}

		$this->setFormPaths();
		$this->setFieldPaths();

		$form = JForm::getInstance($name, $source, $config, $clear, $xpath);

		return $form;
	}

	/**
	 * Method to set form search paths
	 * Inheriting classes can override this method
	 *
	 * @param array $paths of paths to search for form definitions
	 */
	public function setFormPaths($paths = array())
	{
		if(empty($paths))
		{
			$config = $this->config;
			$paths[] = JPATH_ADMINISTRATOR .'/components/'.$config['option'].'/model/forms';
			$paths[] = JPATH_SITE .'/components/'.$config['option']. '/model/forms';
		}

		foreach($paths AS $path)
		{
			JForm::addFormPath($path);
		}
	}

	/**
	 * Method to set field search paths
	 * Inheriting classes can override this method
	 *
	 * @param array $paths of paths to search for field definitions
	 */
	public  function setFieldPaths($paths = array())
	{
		if(empty($paths))
		{
			$paths[] = JPATH_COMPONENT_ADMINISTRATOR . '/model/fields';
			$paths[] = JPATH_COMPONENT_SITE . '/model/fields';
		}

		foreach($paths AS $path)
		{
			JForm::addFieldPath($path);
		}
	}

	/**
	 * Method to validate the form data.
	 *
	 * @param   JForm  $form  The form to validate against.
	 * @param   array  $data  The data to validate.
	 * @param   string $group The name of the field group to validate.
	 *
	 * @throws ErrorException
	 * @return  mixed  Array of filtered data if valid
	 */
	public function validate($form, $data, $group = null)
	{
		// Filter and validate the form data.
		$data = $form->filter($data);

		// Check the validation results.
		if (!$form->validate($data, $group))
		{
			$msg = '';
			$i   = 0;

			// Get the validation messages from the form.
			foreach ($form->getErrors() as $e)
			{
				if ($i != 0)
				{
					$msg .= '<br/>';
				}

				$msg .= $e->getMessage();
				$i++;
			}

			throw new ErrorException($msg);
		}

		return $data;
	}
}