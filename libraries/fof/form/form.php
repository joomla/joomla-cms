<?php
/**
 * @package    FrameworkOnFramework
 * @subpackage form
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

if (version_compare(JVERSION, '2.5.0', 'lt'))
{
	jimport('joomla.form.form');
	jimport('joomla.form.formfield');
	jimport('joomla.form.formrule');
}

/**
 * FOFForm is an extension to JForm which support not only edit views but also
 * browse (record list) and read (single record display) views based on XML
 * forms.
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFForm extends JForm
{
	/**
	 * The model attached to this view
	 *
	 * @var FOFModel
	 */
	protected $model;

	/**
	 * The view used to render this form
	 *
	 * @var FOFView
	 */
	protected $view;

	/**
	 * Method to get an instance of a form.
	 *
	 * @param   string  	$name		The name of the form.
	 * @param   string  	$data		The name of an XML file or string to load as the form definition.
	 * @param   array   	$options	An array of form options.
	 * @param   bool  		$replace	Flag to toggle whether form fields should be replaced if a field
	 *                      	      	already exists with the same group/name.
	 * @param   bool|string $xpath		An optional xpath to search for the fields.
	 *
	 * @return  object  FOFForm instance.
	 *
	 * @since   2.0
	 * @throws  InvalidArgumentException if no data provided.
	 * @throws  RuntimeException if the form could not be loaded.
	 */
	public static function getInstance($name, $data = null, $options = array(), $replace = true, $xpath = false)
	{
		// Reference to array with form instances
		$forms = &self::$forms;

		// Only instantiate the form if it does not already exist.
		if (!isset($forms[$name]))
		{
			$data = trim($data);

			if (empty($data))
			{
				throw new InvalidArgumentException(sprintf('FOFForm::getInstance(name, *%s*)', gettype($data)));
			}

			// Instantiate the form.
			$forms[$name] = new FOFForm($name, $options);

			// Load the data.
			if (substr(trim($data), 0, 1) == '<')
			{
				if ($forms[$name]->load($data, $replace, $xpath) == false)
				{
					throw new RuntimeException('FOFForm::getInstance could not load form');
				}
			}
			else
			{
				if ($forms[$name]->loadFile($data, $replace, $xpath) == false)
				{
					throw new RuntimeException('FOFForm::getInstance could not load file ' . $data . '.xml');
				}
			}
		}

		return $forms[$name];
	}

	/**
	 * Returns the value of an attribute of the form itself
	 *
	 * @param   string  $attribute  The name of the attribute
	 * @param   mixed   $default    Optional default value to return
	 *
	 * @return  mixed
	 *
	 * @since 2.0
	 */
	public function getAttribute($attribute, $default = null)
	{
		$value = $this->xml->attributes()->$attribute;

		if (is_null($value))
		{
			return $default;
		}
		else
		{
			return (string) $value;
		}
	}

	/**
	 * Loads the CSS files defined in the form, based on its cssfiles attribute
	 *
	 * @return  void
	 *
	 * @since 2.0
	 */
	public function loadCSSFiles()
	{
		// Support for CSS files
		$cssfiles = $this->getAttribute('cssfiles');

		if (!empty($cssfiles))
		{
			$cssfiles = explode(',', $cssfiles);

			foreach ($cssfiles as $cssfile)
			{
				FOFTemplateUtils::addCSS(trim($cssfile));
			}
		}

		// Support for LESS files
		$lessfiles = $this->getAttribute('lessfiles');

		if (!empty($lessfiles))
		{
			$lessfiles = explode(',', $lessfiles);

			foreach ($lessfiles as $def)
			{
				$parts = explode('||', $def, 2);
				$lessfile = $parts[0];
				$alt = (count($parts) > 1) ? trim($parts[1]) : null;
				FOFTemplateUtils::addLESS(trim($lessfile), $alt);
			}
		}
	}

	/**
	 * Loads the Javascript files defined in the form, based on its jsfiles attribute
	 *
	 * @return  void
	 *
	 * @since 2.0
	 */
	public function loadJSFiles()
	{
		$jsfiles = $this->getAttribute('jsfiles');

		if (empty($jsfiles))
		{
			return;
		}

		$jsfiles = explode(',', $jsfiles);

		foreach ($jsfiles as $jsfile)
		{
			FOFTemplateUtils::addJS(trim($jsfile));
		}
	}

	/**
	 * Returns a reference to the protected $data object, allowing direct
	 * access to and manipulation of the form's data.
	 *
	 * @return   JRegistry  The form's data registry
	 *
	 * @since 2.0
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Attaches a FOFModel to this form
	 *
	 * @param   FOFModel  &$model  The model to attach to the form
	 *
	 * @return  void
	 */
	public function setModel(FOFModel &$model)
	{
		$this->model = $model;
	}

	/**
	 * Returns the FOFModel attached to this form
	 *
	 * @return FOFModel
	 */
	public function &getModel()
	{
		return $this->model;
	}

	/**
	 * Attaches a FOFView to this form
	 *
	 * @param   FOFView  &$view  The view to attach to the form
	 *
	 * @return  void
	 */
	public function setView(FOFView &$view)
	{
		$this->view = $view;
	}

	/**
	 * Returns the FOFView attached to this form
	 *
	 * @return FOFView
	 */
	public function &getView()
	{
		return $this->view;
	}

	/**
	 * Method to get an array of FOFFormHeader objects in the headerset.
	 *
	 * @return  array  The array of FOFFormHeader objects in the headerset.
	 *
	 * @since   2.0
	 */
	public function getHeaderset()
	{
		$fields = array();

		$elements = $this->findHeadersByGroup();

		// If no field elements were found return empty.

		if (empty($elements))
		{
			return $fields;
		}

		// Build the result array from the found field elements.

		foreach ($elements as $element)
		{
			// Get the field groups for the element.
			$attrs = $element->xpath('ancestor::headers[@name]/@name');
			$groups = array_map('strval', $attrs ? $attrs : array());
			$group = implode('.', $groups);

			// If the field is successfully loaded add it to the result array.
			if ($field = $this->loadHeader($element, $group))
			{
				$fields[$field->id] = $field;
			}
		}

		return $fields;
	}

	/**
	 * Method to get an array of <header /> elements from the form XML document which are
	 * in a control group by name.
	 *
	 * @param   mixed    $group   The optional dot-separated form group path on which to find the fields.
	 *                            Null will return all fields. False will return fields not in a group.
	 * @param   boolean  $nested  True to also include fields in nested groups that are inside of the
	 *                            group for which to find fields.
	 *
	 * @return  mixed  Boolean false on error or array of SimpleXMLElement objects.
	 *
	 * @since   2.0
	 */
	protected function &findHeadersByGroup($group = null, $nested = false)
	{
		$false = false;
		$fields = array();

		// Make sure there is a valid JForm XML document.
		if (!($this->xml instanceof SimpleXMLElement))
		{
			return $false;
		}

		// Get only fields in a specific group?
		if ($group)
		{
			// Get the fields elements for a given group.
			$elements = &$this->findHeader($group);

			// Get all of the field elements for the fields elements.
			foreach ($elements as $element)
			{
				// If there are field elements add them to the return result.
				if ($tmp = $element->xpath('descendant::header'))
				{
					// If we also want fields in nested groups then just merge the arrays.
					if ($nested)
					{
						$fields = array_merge($fields, $tmp);
					}

					// If we want to exclude nested groups then we need to check each field.
					else
					{
						$groupNames = explode('.', $group);

						foreach ($tmp as $field)
						{
							// Get the names of the groups that the field is in.
							$attrs = $field->xpath('ancestor::headers[@name]/@name');
							$names = array_map('strval', $attrs ? $attrs : array());

							// If the field is in the specific group then add it to the return list.
							if ($names == (array) $groupNames)
							{
								$fields = array_merge($fields, array($field));
							}
						}
					}
				}
			}
		}
		elseif ($group === false)
		{
			// Get only field elements not in a group.
			$fields = $this->xml->xpath('descendant::headers[not(@name)]/header | descendant::headers[not(@name)]/headerset/header ');
		}
		else
		{
			// Get an array of all the <header /> elements.
			$fields = $this->xml->xpath('//header');
		}

		return $fields;
	}

	/**
	 * Method to get a header field represented as a FOFFormHeader object.
	 *
	 * @param   string  $name   The name of the header field.
	 * @param   string  $group  The optional dot-separated form group path on which to find the field.
	 * @param   mixed   $value  The optional value to use as the default for the field.
	 *
	 * @return  mixed  The FOFFormHeader object for the field or boolean false on error.
	 *
	 * @since   2.0
	 */
	public function getHeader($name, $group = null, $value = null)
	{
		// Make sure there is a valid FOFForm XML document.
		if (!($this->xml instanceof SimpleXMLElement))
		{
			return false;
		}

		// Attempt to find the field by name and group.
		$element = $this->findHeader($name, $group);

		// If the field element was not found return false.
		if (!$element)
		{
			return false;
		}

		return $this->loadHeader($element, $group, $value);
	}

	/**
	 * Method to get a header field represented as an XML element object.
	 *
	 * @param   string  $name   The name of the form field.
	 * @param   string  $group  The optional dot-separated form group path on which to find the field.
	 *
	 * @return  mixed  The XML element object for the field or boolean false on error.
	 *
	 * @since   2.0
	 */
	protected function findHeader($name, $group = null)
	{
		$element = false;
		$fields = array();

		// Make sure there is a valid JForm XML document.
		if (!($this->xml instanceof SimpleXMLElement))
		{
			return false;
		}

		// Let's get the appropriate field element based on the method arguments.
		if ($group)
		{
			// Get the fields elements for a given group.
			$elements = &$this->findGroup($group);

			// Get all of the field elements with the correct name for the fields elements.
			foreach ($elements as $element)
			{
				// If there are matching field elements add them to the fields array.
				if ($tmp = $element->xpath('descendant::header[@name="' . $name . '"]'))
				{
					$fields = array_merge($fields, $tmp);
				}
			}

			// Make sure something was found.
			if (!$fields)
			{
				return false;
			}

			// Use the first correct match in the given group.
			$groupNames = explode('.', $group);

			foreach ($fields as &$field)
			{
				// Get the group names as strings for ancestor fields elements.
				$attrs = $field->xpath('ancestor::headerfields[@name]/@name');
				$names = array_map('strval', $attrs ? $attrs : array());

				// If the field is in the exact group use it and break out of the loop.
				if ($names == (array) $groupNames)
				{
					$element = &$field;
					break;
				}
			}
		}
		else
		{
			// Get an array of fields with the correct name.
			$fields = $this->xml->xpath('//header[@name="' . $name . '"]');

			// Make sure something was found.
			if (!$fields)
			{
				return false;
			}

			// Search through the fields for the right one.
			foreach ($fields as &$field)
			{
				// If we find an ancestor fields element with a group name then it isn't what we want.
				if ($field->xpath('ancestor::headerfields[@name]'))
				{
					continue;
				}

				// Found it!
				else
				{
					$element = &$field;
					break;
				}
			}
		}

		return $element;
	}

	/**
	 * Method to load, setup and return a FOFFormHeader object based on field data.
	 *
	 * @param   string  $element  The XML element object representation of the form field.
	 * @param   string  $group    The optional dot-separated form group path on which to find the field.
	 * @param   mixed   $value    The optional value to use as the default for the field.
	 *
	 * @return  mixed  The FOFFormHeader object for the field or boolean false on error.
	 *
	 * @since   2.0
	 */
	protected function loadHeader($element, $group = null, $value = null)
	{
		// Make sure there is a valid SimpleXMLElement.
		if (!($element instanceof SimpleXMLElement))
		{
			return false;
		}

		// Get the field type.
		$type = $element['type'] ? (string) $element['type'] : 'field';

		// Load the JFormField object for the field.
		$field = $this->loadHeaderType($type);

		// If the object could not be loaded, get a text field object.
		if ($field === false)
		{
			$field = $this->loadHeaderType('field');
		}

		// Setup the FOFFormHeader object.
		$field->setForm($this);

		if ($field->setup($element, $value, $group))
		{
			return $field;
		}
		else
		{
			return false;
		}
	}
	
	/**
	 * Method to remove a header from the form definition.
	 *
	 * @param   string  $name   The name of the form field for which remove.
	 * @param   string  $group  The optional dot-separated form group path on which to find the field.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @throws  UnexpectedValueException
	 */
	public function removeHeader($name, $group = null)
	{
		// Make sure there is a valid JForm XML document.
		if (!($this->xml instanceof SimpleXMLElement))
		{
			throw new UnexpectedValueException(sprintf('%s::getFieldAttribute `xml` is not an instance of SimpleXMLElement', get_class($this)));
		}

		// Find the form field element from the definition.
		$element = $this->findHeader($name, $group);

		// If the element exists remove it from the form definition.
		if ($element instanceof SimpleXMLElement)
		{
			$dom = dom_import_simplexml($element);
			$dom->parentNode->removeChild($dom);

			return true;
		}

		return false;
	}

	/**
	 * Proxy for {@link FOFFormHelper::loadFieldType()}.
	 *
	 * @param   string   $type  The field type.
	 * @param   boolean  $new   Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return  mixed  FOFFormField object on success, false otherwise.
	 *
	 * @since   2.0
	 */
	protected function loadFieldType($type, $new = true)
	{
		return FOFFormHelper::loadFieldType($type, $new);
	}

	/**
	 * Proxy for {@link FOFFormHelper::loadHeaderType()}.
	 *
	 * @param   string   $type  The field type.
	 * @param   boolean  $new   Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return  mixed  FOFFormHeader object on success, false otherwise.
	 *
	 * @since   2.0
	 */
	protected function loadHeaderType($type, $new = true)
	{
		return FOFFormHelper::loadHeaderType($type, $new);
	}

	/**
	 * Proxy for {@link FOFFormHelper::loadRuleType()}.
	 *
	 * @param   string   $type  The rule type.
	 * @param   boolean  $new   Flag to toggle whether we should get a new instance of the object.
	 *
	 * @return  mixed  JFormRule object on success, false otherwise.
	 *
	 * @see     FOFFormHelper::loadRuleType()
	 * @since   2.0
	 */
	protected function loadRuleType($type, $new = true)
	{
		return FOFFormHelper::loadRuleType($type, $new);
	}

	/**
	 * Proxy for {@link FOFFormHelper::addFieldPath()}.
	 *
	 * @param   mixed  $new  A path or array of paths to add.
	 *
	 * @return  array  The list of paths that have been added.
	 *
	 * @since   2.0
	 */
	public static function addFieldPath($new = null)
	{
		return FOFFormHelper::addFieldPath($new);
	}

	/**
	 * Proxy for {@link FOFFormHelper::addHeaderPath()}.
	 *
	 * @param   mixed  $new  A path or array of paths to add.
	 *
	 * @return  array  The list of paths that have been added.
	 *
	 * @since   2.0
	 */
	public static function addHeaderPath($new = null)
	{
		return FOFFormHelper::addHeaderPath($new);
	}

	/**
	 * Proxy for FOFFormHelper::addFormPath().
	 *
	 * @param   mixed  $new  A path or array of paths to add.
	 *
	 * @return  array  The list of paths that have been added.
	 *
	 * @see     FOFFormHelper::addFormPath()
	 * @since   2.0
	 */
	public static function addFormPath($new = null)
	{
		return FOFFormHelper::addFormPath($new);
	}

	/**
	 * Proxy for FOFFormHelper::addRulePath().
	 *
	 * @param   mixed  $new  A path or array of paths to add.
	 *
	 * @return  array  The list of paths that have been added.
	 *
	 * @see FOFFormHelper::addRulePath()
	 * @since   2.0
	 */
	public static function addRulePath($new = null)
	{
		return FOFFormHelper::addRulePath($new);
	}
}
