<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form;

\defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

/**
 * Form Class for the Joomla Platform.
 *
 * This class implements a robust API for constructing, populating, filtering, and validating forms.
 * It uses XML definitions to construct form fields and a variety of field and rule classes to
 * render and validate the form.
 *
 * @link   https://www.w3.org/TR/html4/interact/forms.html
 * @link   https://html.spec.whatwg.org/multipage/forms.html
 * @since  1.7.0
 */
class Form
{
	/**
	 * The Registry data store for form fields during display.
	 *
	 * @var    Registry
	 * @since  1.7.0
	 */
	protected $data;

	/**
	 * The form object errors array.
	 *
	 * @var    array
	 * @since  1.7.0
	 */
	protected $errors = array();

	/**
	 * The name of the form instance.
	 *
	 * @var    string
	 * @since  1.7.0
	 */
	protected $name;

	/**
	 * The form object options for use in rendering and validation.
	 *
	 * @var    array
	 * @since  1.7.0
	 */
	protected $options = array();

	/**
	 * The form XML definition.
	 *
	 * @var    \SimpleXMLElement
	 * @since  1.7.0
	 */
	protected $xml;

	/**
	 * Form instances.
	 *
	 * @var    Form[]
	 * @since  1.7.0
	 */
	protected static $forms = array();

	/**
	 * Allows extensions to implement repeating elements
	 *
	 * @var    boolean
	 * @since  3.2
	 */
	public $repeat = false;

	/**
	 * Method to instantiate the form object.
	 *
	 * @param   string  $name     The name of the form.
	 * @param   array   $options  An array of form options.
	 *
	 * @since   1.7.0
	 */
	public function __construct($name, array $options = array())
	{
		// Set the name for the form.
		$this->name = $name;

		// Initialise the Registry data.
		$this->data = new Registry;

		// Set the options if specified.
		$this->options['control'] = $options['control'] ?? false;
	}

	/**
	 * Method to bind data to the form.
	 *
	 * @param   mixed  $data  An array or object of data to bind to the form.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.7.0
	 */
	public function bind($data)
	{
		// Make sure there is a valid Form XML document.
		if (!($this->xml instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::%s `xml` is not an instance of SimpleXMLElement', \get_class($this), __METHOD__));
		}

		// The data must be an object or array.
		if (!\is_object($data) && !\is_array($data))
		{
			return false;
		}

		$this->bindLevel(null, $data);

		return true;
	}

	/**
	 * Method to bind data to the form for the group level.
	 *
	 * @param   string  $group  The dot-separated form group path on which to bind the data.
	 * @param   mixed   $data   An array or object of data to bind to the form for the group level.
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected function bindLevel($group, $data)
	{
		// Ensure the input data is an array.
		if (\is_object($data))
		{
			if ($data instanceof Registry)
			{
				// Handle a Registry.
				$data = $data->toArray();
			}
			elseif ($data instanceof CMSObject)
			{
				// Handle a CMSObject.
				$data = $data->getProperties();
			}
			else
			{
				// Handle other types of objects.
				$data = (array) $data;
			}
		}

		// Process the input data.
		foreach ($data as $k => $v)
		{
			$level = $group ? $group . '.' . $k : $k;

			if ($this->findField($k, $group))
			{
				// If the field exists set the value.
				$this->data->set($level, $v);
			}
			elseif (\is_object($v) || ArrayHelper::isAssociative($v))
			{
				// If the value is an object or an associative array, hand it off to the recursive bind level method.
				$this->bindLevel($level, $v);
			}
		}
	}

	/**
	 * Return all errors, if any.
	 *
	 * @return  array  Array of error messages or RuntimeException objects.
	 *
	 * @since   1.7.0
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * Method to get a form field represented as a FormField object.
	 *
	 * @param   string  $name   The name of the form field.
	 * @param   string  $group  The optional dot-separated form group path on which to find the field.
	 * @param   mixed   $value  The optional value to use as the default for the field.
	 *
	 * @return  FormField|boolean  The FormField object for the field or boolean false on error.
	 *
	 * @since   1.7.0
	 */
	public function getField($name, $group = null, $value = null)
	{
		// Make sure there is a valid Form XML document.
		if (!($this->xml instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::%s `xml` is not an instance of SimpleXMLElement', \get_class($this), __METHOD__));
		}

		// Attempt to find the field by name and group.
		$element = $this->findField($name, $group);

		// If the field element was not found return false.
		if (!$element)
		{
			return false;
		}

		return $this->loadField($element, $group, $value);
	}

	/**
	 * Method to get an attribute value from a field XML element.  If the attribute doesn't exist or
	 * is null then the optional default value will be used.
	 *
	 * @param   string  $name       The name of the form field for which to get the attribute value.
	 * @param   string  $attribute  The name of the attribute for which to get a value.
	 * @param   mixed   $default    The optional default value to use if no attribute value exists.
	 * @param   string  $group      The optional dot-separated form group path on which to find the field.
	 *
	 * @return  mixed  The attribute value for the field.
	 *
	 * @since   1.7.0
	 * @throws  \UnexpectedValueException
	 */
	public function getFieldAttribute($name, $attribute, $default = null, $group = null)
	{
		// Make sure there is a valid Form XML document.
		if (!($this->xml instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::%s `xml` is not an instance of SimpleXMLElement', \get_class($this), __METHOD__));
		}

		// Find the form field element from the definition.
		$element = $this->findField($name, $group);

		// If the element exists and the attribute exists for the field return the attribute value.
		if (($element instanceof \SimpleXMLElement) && \strlen((string) $element[$attribute]))
		{
			return (string) $element[$attribute];
		}

		// Otherwise return the given default value.
		else
		{
			return $default;
		}
	}

	/**
	 * Method to get an array of FormField objects in a given fieldset by name.  If no name is
	 * given then all fields are returned.
	 *
	 * @param   string  $set  The optional name of the fieldset.
	 *
	 * @return  FormField[]  The array of FormField objects in the fieldset.
	 *
	 * @since   1.7.0
	 */
	public function getFieldset($set = null)
	{
		$fields = array();

		// Get all of the field elements in the fieldset.
		if ($set)
		{
			$elements = $this->findFieldsByFieldset($set);
		}

		// Get all fields.
		else
		{
			$elements = $this->findFieldsByGroup();
		}

		// If no field elements were found return empty.
		if (empty($elements))
		{
			return $fields;
		}

		// Build the result array from the found field elements.
		foreach ($elements as $element)
		{
			// Get the field groups for the element.
			$attrs = $element->xpath('ancestor::fields[@name]/@name');
			$groups = array_map('strval', $attrs ? $attrs : array());
			$group = implode('.', $groups);

			// If the field is successfully loaded add it to the result array.
			if ($field = $this->loadField($element, $group))
			{
				$fields[$field->id] = $field;
			}
		}

		return $fields;
	}

	/**
	 * Method to get an array of fieldset objects optionally filtered over a given field group.
	 *
	 * @param   string  $group  The dot-separated form group path on which to filter the fieldsets.
	 *
	 * @return  array  The array of fieldset objects.
	 *
	 * @since   1.7.0
	 */
	public function getFieldsets($group = null)
	{
		$fieldsets = array();
		$sets = array();

		// Make sure there is a valid Form XML document.
		if (!($this->xml instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::%s `xml` is not an instance of SimpleXMLElement', \get_class($this), __METHOD__));
		}

		if ($group)
		{
			// Get the fields elements for a given group.
			$elements = &$this->findGroup($group);

			foreach ($elements as &$element)
			{
				// Get an array of <fieldset /> elements and fieldset attributes within the fields element.
				if ($tmp = $element->xpath('descendant::fieldset[@name] | descendant::field[@fieldset]/@fieldset'))
				{
					$sets = array_merge($sets, (array) $tmp);
				}
			}
		}
		else
		{
			// Get an array of <fieldset /> elements and fieldset attributes.
			$sets = $this->xml->xpath('//fieldset[@name and not(ancestor::field/form/*)] | //field[@fieldset and not(ancestor::field/form/*)]/@fieldset');
		}

		// If no fieldsets are found return empty.
		if (empty($sets))
		{
			return $fieldsets;
		}

		// Process each found fieldset.
		foreach ($sets as $set)
		{
			if ((string) $set['hidden'] === 'true')
			{
				continue;
			}

			// Are we dealing with a fieldset element?
			if ((string) $set['name'])
			{
				// Only create it if it doesn't already exist.
				if (empty($fieldsets[(string) $set['name']]))
				{
					// Build the fieldset object.
					$fieldset = (object) array('name' => '', 'label' => '', 'description' => '');

					foreach ($set->attributes() as $name => $value)
					{
						$fieldset->$name = (string) $value;
					}

					// Add the fieldset object to the list.
					$fieldsets[$fieldset->name] = $fieldset;
				}
			}

			// Must be dealing with a fieldset attribute.
			else
			{
				// Only create it if it doesn't already exist.
				if (empty($fieldsets[(string) $set]))
				{
					// Attempt to get the fieldset element for data (throughout the entire form document).
					$tmp = $this->xml->xpath('//fieldset[@name="' . (string) $set . '"]');

					// If no element was found, build a very simple fieldset object.
					if (empty($tmp))
					{
						$fieldset = (object) array('name' => (string) $set, 'label' => '', 'description' => '');
					}

					// Build the fieldset object from the element.
					else
					{
						$fieldset = (object) array('name' => '', 'label' => '', 'description' => '');

						foreach ($tmp[0]->attributes() as $name => $value)
						{
							$fieldset->$name = (string) $value;
						}
					}

					// Add the fieldset object to the list.
					$fieldsets[$fieldset->name] = $fieldset;
				}
			}
		}

		return $fieldsets;
	}

	/**
	 * Method to get the form control. This string serves as a container for all form fields. For
	 * example, if there is a field named 'foo' and a field named 'bar' and the form control is
	 * empty the fields will be rendered like: `<input name="foo" />` and `<input name="bar" />`.  If
	 * the form control is set to 'joomla' however, the fields would be rendered like:
	 * `<input name="joomla[foo]" />` and `<input name="joomla[bar]" />`.
	 *
	 * @return  string  The form control string.
	 *
	 * @since   1.7.0
	 */
	public function getFormControl()
	{
		return (string) $this->options['control'];
	}

	/**
	 * Method to get an array of FormField objects in a given field group by name.
	 *
	 * @param   string   $group   The dot-separated form group path for which to get the form fields.
	 * @param   boolean  $nested  True to also include fields in nested groups that are inside of the
	 *                            group for which to find fields.
	 *
	 * @return  FormField[]  The array of FormField objects in the field group.
	 *
	 * @since   1.7.0
	 */
	public function getGroup($group, $nested = false)
	{
		$fields = array();

		// Get all of the field elements in the field group.
		$elements = $this->findFieldsByGroup($group, $nested);

		// If no field elements were found return empty.
		if (empty($elements))
		{
			return $fields;
		}

		// Build the result array from the found field elements.
		foreach ($elements as $element)
		{
			// Get the field groups for the element.
			$attrs  = $element->xpath('ancestor::fields[@name]/@name');
			$groups = array_map('strval', $attrs ? $attrs : array());
			$group  = implode('.', $groups);

			// If the field is successfully loaded add it to the result array.
			if ($field = $this->loadField($element, $group))
			{
				$fields[$field->id] = $field;
			}
		}

		return $fields;
	}

	/**
	 * Method to get a form field markup for the field input.
	 *
	 * @param   string  $name   The name of the form field.
	 * @param   string  $group  The optional dot-separated form group path on which to find the field.
	 * @param   mixed   $value  The optional value to use as the default for the field.
	 *
	 * @return  string  The form field markup.
	 *
	 * @since   1.7.0
	 */
	public function getInput($name, $group = null, $value = null)
	{
		// Attempt to get the form field.
		if ($field = $this->getField($name, $group, $value))
		{
			return $field->input;
		}

		return '';
	}

	/**
	 * Method to get the label for a field input.
	 *
	 * @param   string  $name   The name of the form field.
	 * @param   string  $group  The optional dot-separated form group path on which to find the field.
	 *
	 * @return  string  The form field label.
	 *
	 * @since   1.7.0
	 */
	public function getLabel($name, $group = null)
	{
		// Attempt to get the form field.
		if ($field = $this->getField($name, $group))
		{
			return $field->label;
		}

		return '';
	}

	/**
	 * Method to get the form name.
	 *
	 * @return  string  The name of the form.
	 *
	 * @since   1.7.0
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Method to get the value of a field.
	 *
	 * @param   string  $name     The name of the field for which to get the value.
	 * @param   string  $group    The optional dot-separated form group path on which to get the value.
	 * @param   mixed   $default  The optional default value of the field value is empty.
	 *
	 * @return  mixed  The value of the field or the default value if empty.
	 *
	 * @since   1.7.0
	 */
	public function getValue($name, $group = null, $default = null)
	{
		// If a group is set use it.
		if ($group)
		{
			$return = $this->data->get($group . '.' . $name, $default);
		}
		else
		{
			$return = $this->data->get($name, $default);
		}

		return $return;
	}

	/**
	 * Method to get a control group with label and input.
	 *
	 * @param   string  $name     The name of the field for which to get the value.
	 * @param   string  $group    The optional dot-separated form group path on which to get the value.
	 * @param   mixed   $default  The optional default value of the field value is empty.
	 * @param   array   $options  Any options to be passed into the rendering of the field
	 *
	 * @return  string  A string containing the html for the control goup
	 *
	 * @since   3.2.3
	 */
	public function renderField($name, $group = null, $default = null, $options = array())
	{
		$field = $this->getField($name, $group, $default);

		if ($field)
		{
			return $field->renderField($options);
		}

		return '';
	}

	/**
	 * Method to get all control groups with label and input of a fieldset.
	 *
	 * @param   string  $name     The name of the fieldset for which to get the values.
	 * @param   array   $options  Any options to be passed into the rendering of the field
	 *
	 * @return  string  A string containing the html for the control goups
	 *
	 * @since   3.2.3
	 */
	public function renderFieldset($name, $options = array())
	{
		$fields = $this->getFieldset($name);
		$html = array();

		foreach ($fields as $field)
		{
			$html[] = $field->renderField($options);
		}

		return implode('', $html);
	}

	/**
	 * Method to load the form description from an XML string or object.
	 *
	 * The replace option works per field.  If a field being loaded already exists in the current
	 * form definition then the behavior or load will vary depending upon the replace flag.  If it
	 * is set to true, then the existing field will be replaced in its exact location by the new
	 * field being loaded.  If it is false, then the new field being loaded will be ignored and the
	 * method will move on to the next field to load.
	 *
	 * @param   string   $data     The name of an XML string or object.
	 * @param   boolean  $replace  Flag to toggle whether form fields should be replaced if a field
	 *                             already exists with the same group/name.
	 * @param   string   $xpath    An optional xpath to search for the fields.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.7.0
	 */
	public function load($data, $replace = true, $xpath = null)
	{
		// If the data to load isn't already an XML element or string return false.
		if ((!($data instanceof \SimpleXMLElement)) && (!\is_string($data)))
		{
			return false;
		}

		// Attempt to load the XML if a string.
		if (\is_string($data))
		{
			try
			{
				$data = new \SimpleXMLElement($data);
			}
			catch (\Exception $e)
			{
				return false;
			}
		}

		// If we have no XML definition at this point let's make sure we get one.
		if (empty($this->xml))
		{
			// If no XPath query is set to search for fields, and we have a <form />, set it and return.
			if (!$xpath && ($data->getName() === 'form'))
			{
				$this->xml = $data;

				// Synchronize any paths found in the load.
				$this->syncPaths();

				return true;
			}

			// Create a root element for the form.
			else
			{
				$this->xml = new \SimpleXMLElement('<form></form>');
			}
		}

		// Get the XML elements to load.
		$elements = array();

		if ($xpath)
		{
			$elements = $data->xpath($xpath);
		}
		elseif ($data->getName() === 'form')
		{
			$elements = $data->children();
		}

		// If there is nothing to load return true.
		if (empty($elements))
		{
			return true;
		}

		// Load the found form elements.
		foreach ($elements as $element)
		{
			// Get an array of fields with the correct name.
			$fields = $element->xpath('descendant-or-self::field');

			foreach ($fields as $field)
			{
				// Get the group names as strings for ancestor fields elements.
				$attrs = $field->xpath('ancestor::fields[@name]/@name');
				$groups = array_map('strval', $attrs ? $attrs : array());

				// Check to see if the field exists in the current form.
				if ($current = $this->findField((string) $field['name'], implode('.', $groups)))
				{
					// If set to replace found fields, replace the data and remove the field so we don't add it twice.
					if ($replace)
					{
						$olddom = dom_import_simplexml($current);
						$loadeddom = dom_import_simplexml($field);
						$addeddom = $olddom->ownerDocument->importNode($loadeddom, true);
						$olddom->parentNode->replaceChild($addeddom, $olddom);
						$loadeddom->parentNode->removeChild($loadeddom);
					}
					else
					{
						unset($field);
					}
				}
			}

			// Merge the new field data into the existing XML document.
			self::addNode($this->xml, $element);
		}

		// Synchronize any paths found in the load.
		$this->syncPaths();

		return true;
	}

	/**
	 * Method to load the form description from an XML file.
	 *
	 * The reset option works on a group basis. If the XML file references
	 * groups that have already been created they will be replaced with the
	 * fields in the new XML file unless the $reset parameter has been set
	 * to false.
	 *
	 * @param   string   $file   The filesystem path of an XML file.
	 * @param   boolean  $reset  Flag to toggle whether form fields should be replaced if a field
	 *                           already exists with the same group/name.
	 * @param   string   $xpath  An optional xpath to search for the fields.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.7.0
	 */
	public function loadFile($file, $reset = true, $xpath = null)
	{
		// Check to see if the path is an absolute path.
		if (!is_file($file))
		{
			// Not an absolute path so let's attempt to find one using JPath.
			$file = Path::find(self::addFormPath(), strtolower($file) . '.xml');

			// If unable to find the file return false.
			if (!$file)
			{
				return false;
			}
		}

		// Attempt to load the XML file.
		$xml = simplexml_load_file($file);

		return $this->load($xml, $reset, $xpath);
	}

	/**
	 * Method to remove a field from the form definition.
	 *
	 * @param   string  $name   The name of the form field for which remove.
	 * @param   string  $group  The optional dot-separated form group path on which to find the field.
	 *
	 * @return  boolean  True on success, false otherwise.
	 *
	 * @since   1.7.0
	 * @throws  \UnexpectedValueException
	 */
	public function removeField($name, $group = null)
	{
		// Make sure there is a valid Form XML document.
		if (!($this->xml instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::%s `xml` is not an instance of SimpleXMLElement', \get_class($this), __METHOD__));
		}

		// Find the form field element from the definition.
		$element = $this->findField($name, $group);

		// If the element exists remove it from the form definition.
		if ($element instanceof \SimpleXMLElement)
		{
			$dom = dom_import_simplexml($element);
			$dom->parentNode->removeChild($dom);

			return true;
		}

		return false;
	}

	/**
	 * Method to remove a group from the form definition.
	 *
	 * @param   string  $group  The dot-separated form group path for the group to remove.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.7.0
	 * @throws  \UnexpectedValueException
	 */
	public function removeGroup($group)
	{
		// Make sure there is a valid Form XML document.
		if (!($this->xml instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::%s `xml` is not an instance of SimpleXMLElement', \get_class($this), __METHOD__));
		}

		// Get the fields elements for a given group.
		$elements = &$this->findGroup($group);

		foreach ($elements as &$element)
		{
			$dom = dom_import_simplexml($element);
			$dom->parentNode->removeChild($dom);
		}

		return true;
	}

	/**
	 * Method to reset the form data store and optionally the form XML definition.
	 *
	 * @param   boolean  $xml  True to also reset the XML form definition.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.7.0
	 */
	public function reset($xml = false)
	{
		unset($this->data);
		$this->data = new Registry;

		if ($xml)
		{
			unset($this->xml);
			$this->xml = new \SimpleXMLElement('<form></form>');
		}

		return true;
	}

	/**
	 * Method to set a field XML element to the form definition.  If the replace flag is set then
	 * the field will be set whether it already exists or not.  If it isn't set, then the field
	 * will not be replaced if it already exists.
	 *
	 * @param   \SimpleXMLElement  $element   The XML element object representation of the form field.
	 * @param   string             $group     The optional dot-separated form group path on which to set the field.
	 * @param   boolean            $replace   True to replace an existing field if one already exists.
	 * @param   string             $fieldset  The name of the fieldset we are adding the field to.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.7.0
	 * @throws  \UnexpectedValueException
	 */
	public function setField(\SimpleXMLElement $element, $group = null, $replace = true, $fieldset = 'default')
	{
		// Make sure there is a valid Form XML document.
		if (!($this->xml instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::%s `xml` is not an instance of SimpleXMLElement', \get_class($this), __METHOD__));
		}

		// Find the form field element from the definition.
		$old = $this->findField((string) $element['name'], $group);

		// If an existing field is found and replace flag is false do nothing and return true.
		if (!$replace && !empty($old))
		{
			return true;
		}

		// If an existing field is found and replace flag is true remove the old field.
		if ($replace && !empty($old) && ($old instanceof \SimpleXMLElement))
		{
			$dom = dom_import_simplexml($old);

			// Get the parent element, this should be the fieldset
			$parent   = $dom->parentNode;
			$fieldset = $parent->getAttribute('name');

			$parent->removeChild($dom);
		}

		// Create the search path
		$path = '//';

		if (!empty($group))
		{
			$path .= 'fields[@name="' . $group . '"]/';
		}

		$path .= 'fieldset[@name="' . $fieldset . '"]';

		$fs = $this->xml->xpath($path);

		if (isset($fs[0]) && ($fs[0] instanceof \SimpleXMLElement))
		{
			// Add field to the form.
			self::addNode($fs[0], $element);

			// Synchronize any paths found in the load.
			$this->syncPaths();

			return true;
		}

		// We couldn't find a fieldset to add the field. Now we are checking, if we have set only a group
		if (!empty($group))
		{
			$fields = &$this->findGroup($group);

			// If an appropriate fields element was found for the group, add the element.
			if (isset($fields[0]) && ($fields[0] instanceof \SimpleXMLElement))
			{
				self::addNode($fields[0], $element);
			}

			// Synchronize any paths found in the load.
			$this->syncPaths();

			return true;
		}

		// We couldn't find a parent so we are adding it at root level

		// Add field to the form.
		self::addNode($this->xml, $element);

		// Synchronize any paths found in the load.
		$this->syncPaths();

		return true;
	}

	/**
	 * Method to set an attribute value for a field XML element.
	 *
	 * @param   string  $name       The name of the form field for which to set the attribute value.
	 * @param   string  $attribute  The name of the attribute for which to set a value.
	 * @param   mixed   $value      The value to set for the attribute.
	 * @param   string  $group      The optional dot-separated form group path on which to find the field.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.7.0
	 * @throws  \UnexpectedValueException
	 */
	public function setFieldAttribute($name, $attribute, $value, $group = null)
	{
		// Make sure there is a valid Form XML document.
		if (!($this->xml instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::%s `xml` is not an instance of SimpleXMLElement', \get_class($this), __METHOD__));
		}

		// Find the form field element from the definition.
		$element = $this->findField($name, $group);

		// If the element doesn't exist return false.
		if (!($element instanceof \SimpleXMLElement))
		{
			return false;
		}

		// Otherwise set the attribute and return true.
		else
		{
			$element[$attribute] = $value;

			// Synchronize any paths found in the load.
			$this->syncPaths();

			return true;
		}
	}

	/**
	 * Method to set some field XML elements to the form definition.  If the replace flag is set then
	 * the fields will be set whether they already exists or not.  If it isn't set, then the fields
	 * will not be replaced if they already exist.
	 *
	 * @param   array    &$elements  The array of XML element object representations of the form fields.
	 * @param   string   $group      The optional dot-separated form group path on which to set the fields.
	 * @param   boolean  $replace    True to replace existing fields if they already exist.
	 * @param   string   $fieldset   The name of the fieldset we are adding the field to.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.7.0
	 * @throws  \UnexpectedValueException
	 */
	public function setFields(&$elements, $group = null, $replace = true, $fieldset = 'default')
	{
		// Make sure there is a valid Form XML document.
		if (!($this->xml instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::%s `xml` is not an instance of SimpleXMLElement', \get_class($this), __METHOD__));
		}

		// Make sure the elements to set are valid.
		foreach ($elements as $element)
		{
			if (!($element instanceof \SimpleXMLElement))
			{
				throw new \UnexpectedValueException(sprintf('%s::%s `xml` is not an instance of SimpleXMLElement', \get_class($this), __METHOD__));
			}
		}

		// Set the fields.
		$return = true;

		foreach ($elements as $element)
		{
			if (!$this->setField($element, $group, $replace, $fieldset))
			{
				$return = false;
			}
		}

		// Synchronize any paths found in the load.
		$this->syncPaths();

		return $return;
	}

	/**
	 * Method to set the value of a field. If the field does not exist in the form then the method
	 * will return false.
	 *
	 * @param   string  $name   The name of the field for which to set the value.
	 * @param   string  $group  The optional dot-separated form group path on which to find the field.
	 * @param   mixed   $value  The value to set for the field.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.7.0
	 */
	public function setValue($name, $group = null, $value = null)
	{
		// If the field does not exist return false.
		if (!$this->findField($name, $group))
		{
			return false;
		}

		// If a group is set use it.
		if ($group)
		{
			$this->data->set($group . '.' . $name, $value);
		}
		else
		{
			$this->data->set($name, $value);
		}

		return true;
	}

	/**
	 * Method to process the form data.
	 *
	 * @param   array   $data   An array of field values to filter.
	 * @param   string  $group  The dot-separated form group path on which to filter the fields.
	 *
	 * @return  mixed  Array or false.
	 *
	 * @since   4.0.0
	 */
	public function process($data, $group = null)
	{
		$data = $this->filter($data, $group);

		$valid = $this->validate($data, $group);

		if (!$valid)
		{
			return $valid;
		}

		return $this->postProcess($data, $group);
	}

	/**
	 * Method to filter the form data.
	 *
	 * @param   array   $data   An array of field values to filter.
	 * @param   string  $group  The dot-separated form group path on which to filter the fields.
	 *
	 * @return  mixed  Array or false.
	 *
	 * @since   4.0.0
	 */
	public function filter($data, $group = null)
	{
		// Make sure there is a valid Form XML document.
		if (!($this->xml instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::%s `xml` is not an instance of SimpleXMLElement', \get_class($this), __METHOD__));
		}

		$input = new Registry($data);
		$output = new Registry;

		// Get the fields for which to filter the data.
		$fields = $this->findFieldsByGroup($group);

		if (!$fields)
		{
			// PANIC!
			return false;
		}

		// Filter the fields.
		foreach ($fields as $field)
		{
			$name = (string) $field['name'];

			// Get the field groups for the element.
			$attrs = $field->xpath('ancestor::fields[@name]/@name');
			$groups = array_map('strval', $attrs ? $attrs : array());
			$attrGroup = implode('.', $groups);

			$key = $attrGroup ? $attrGroup . '.' . $name : $name;

			// Filter the value if it exists.
			if ($input->exists($key))
			{
				$fieldObj = $this->loadField($field, $group);

				// Only set into the output if the field was supposed to render on the page (i.e. setup returned true)
				if ($fieldObj)
				{
					$output->set($key, $fieldObj->filter($input->get($key, (string) $field['default']), $group, $input));
				}
			}
		}

		return $output->toArray();
	}

	/**
	 * Method to validate form data.
	 *
	 * Validation warnings will be pushed into JForm::errors and should be
	 * retrieved with JForm::getErrors() when validate returns boolean false.
	 *
	 * @param   array   $data   An array of field values to validate.
	 * @param   string  $group  The optional dot-separated form group path on which to filter the
	 *                          fields to be validated.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.7.0
	 */
	public function validate($data, $group = null)
	{
		// Make sure there is a valid Form XML document.
		if (!($this->xml instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::%s `xml` is not an instance of SimpleXMLElement', \get_class($this), __METHOD__));
		}

		$return = true;

		// Create an input registry object from the data to validate.
		$input = new Registry($data);

		// Get the fields for which to validate the data.
		$fields = $this->findFieldsByGroup($group);

		if (!$fields)
		{
			// PANIC!
			return false;
		}

		// Validate the fields.
		foreach ($fields as $field)
		{
			$name     = (string) $field['name'];

			// Define field name for messages
			if ($field['label'])
			{
				$fieldLabel = Text::_($field['label']);
			}
			else
			{
				$fieldLabel = Text::_($name);
			}

			$disabled = ((string) $field['disabled'] === 'true' || (string) $field['disabled'] === 'disabled');

			$fieldExistsInRequestData = $input->exists($name) || $input->exists($group . '.' . $name);

			// If the field is disabled but it is passed in the request this is invalid as disabled fields are not added to the request
			if ($disabled && $fieldExistsInRequestData)
			{
				throw new \RuntimeException(Text::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', $fieldLabel));
			}

			// Get the field groups for the element.
			$attrs = $field->xpath('ancestor::fields[@name]/@name');
			$groups = array_map('strval', $attrs ? $attrs : array());
			$attrGroup = implode('.', $groups);

			$key = $attrGroup ? $attrGroup . '.' . $name : $name;

			$fieldObj = $this->loadField($field, $attrGroup);

			if ($fieldObj)
			{
				$valid = $fieldObj->validate($input->get($key), $attrGroup, $input);

				// Check for an error.
				if ($valid instanceof \Exception)
				{
					$this->errors[] = $valid;
					$return         = false;
				}
			}
			elseif (!$fieldObj && $input->exists($key))
			{
				// The field returned false from setup and shouldn't be included in the page body - yet we received
				// a value for it. This is probably some sort of injection attack and should be rejected
				$this->errors[] = new \RuntimeException(Text::sprintf('JLIB_FORM_VALIDATE_FIELD_INVALID', $key));
			}
		}

		return $return;
	}

	/**
	 * Method to post-process form data.
	 *
	 * @param   array   $data   An array of field values to post-process.
	 * @param   string  $group  The optional dot-separated form group path on which to filter the
	 *                          fields to be validated.
	 *
	 * @return  mixed  Array or false.
	 *
	 * @since   4.0.0
	 */
	public function postProcess($data, $group = null)
	{
		// Make sure there is a valid SimpleXMLElement
		if (!($this->xml instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::%s `xml` is not an instance of SimpleXMLElement', \get_class($this), __METHOD__));
		}

		$input = new Registry($data);
		$output = new Registry;

		// Get the fields for which to postProcess the data.
		$fields = $this->findFieldsByGroup($group);

		if (!$fields)
		{
			// PANIC!
			return false;
		}

		// Filter the fields.
		foreach ($fields as $field)
		{
			$name = (string) $field['name'];

			// Get the field groups for the element.
			$attrs = $field->xpath('ancestor::fields[@name]/@name');
			$groups = array_map('strval', $attrs ? $attrs : array());
			$attrGroup = implode('.', $groups);

			$key = $attrGroup ? $attrGroup . '.' . $name : $name;

			// Filter the value if it exists.
			if ($input->exists($key))
			{
				$fieldobj = $this->loadField($field, $group);
				$output->set($key, $fieldobj->postProcess($input->get($key, (string) $field['default']), $group, $input));
			}
		}

		return $output->toArray();
	}

	/**
	 * Method to get a form field represented as an XML element object.
	 *
	 * @param   string  $name   The name of the form field.
	 * @param   string  $group  The optional dot-separated form group path on which to find the field.
	 *
	 * @return  \SimpleXMLElement|boolean  The XML element object for the field or boolean false on error.
	 *
	 * @since   1.7.0
	 */
	protected function findField($name, $group = null)
	{
		$element = false;
		$fields = array();

		// Make sure there is a valid Form XML document.
		if (!($this->xml instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::%s `xml` is not an instance of SimpleXMLElement', \get_class($this), __METHOD__));
		}

		// Let's get the appropriate field element based on the method arguments.
		if ($group)
		{
			// Get the fields elements for a given group.
			$elements = &$this->findGroup($group);

			// Get all of the field elements with the correct name for the fields elements.
			foreach ($elements as $el)
			{
				// If there are matching field elements add them to the fields array.
				if ($tmp = $el->xpath('descendant::field[@name="' . $name . '" and not(ancestor::field/form/*)]'))
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
				$attrs = $field->xpath('ancestor::fields[@name]/@name');
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
			$fields = $this->xml->xpath('//field[@name="' . $name . '" and not(ancestor::field/form/*)]');

			// Make sure something was found.
			if (!$fields)
			{
				return false;
			}

			// Search through the fields for the right one.
			foreach ($fields as &$field)
			{
				// If we find an ancestor fields element with a group name then it isn't what we want.
				if ($field->xpath('ancestor::fields[@name]'))
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
	 * Method to get an array of `<field>` elements from the form XML document which are in a specified fieldset by name.
	 *
	 * @param   string  $name  The name of the fieldset.
	 *
	 * @return  \SimpleXMLElement[]|boolean  Boolean false on error or array of SimpleXMLElement objects.
	 *
	 * @since   1.7.0
	 */
	protected function &findFieldsByFieldset($name)
	{
		$false = false;

		// Make sure there is a valid Form XML document.
		if (!($this->xml instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::%s `xml` is not an instance of SimpleXMLElement', \get_class($this), __METHOD__));
		}

		/*
		 * Get an array of <field /> elements that are underneath a <fieldset /> element
		 * with the appropriate name attribute, and also any <field /> elements with
		 * the appropriate fieldset attribute. To allow repeatable elements only fields
		 * which are not descendants of other fields are selected.
		 */
		$fields = $this->xml->xpath('(//fieldset[@name="' . $name . '"]//field | //field[@fieldset="' . $name . '"])[not(ancestor::field)]');

		return $fields;
	}

	/**
	 * Method to get an array of `<field>` elements from the form XML document which are in a control group by name.
	 *
	 * @param   mixed    $group   The optional dot-separated form group path on which to find the fields.
	 *                            Null will return all fields. False will return fields not in a group.
	 * @param   boolean  $nested  True to also include fields in nested groups that are inside of the
	 *                            group for which to find fields.
	 *
	 * @return  \SimpleXMLElement[]|boolean  Boolean false on error or array of SimpleXMLElement objects.
	 *
	 * @since   1.7.0
	 */
	protected function &findFieldsByGroup($group = null, $nested = false)
	{
		$false = false;
		$fields = array();

		// Make sure there is a valid Form XML document.
		if (!($this->xml instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::%s `xml` is not an instance of SimpleXMLElement', \get_class($this), __METHOD__));
		}

		// Get only fields in a specific group?
		if ($group)
		{
			// Get the fields elements for a given group.
			$elements = &$this->findGroup($group);

			// Get all of the field elements for the fields elements.
			foreach ($elements as $element)
			{
				// If there are field elements add them to the return result.
				if ($tmp = $element->xpath('descendant::field'))
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
							$attrs = $field->xpath('ancestor::fields[@name]/@name');
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
			$fields = $this->xml->xpath('descendant::fields[not(@name)]/field | descendant::fields[not(@name)]/fieldset/field ');
		}
		else
		{
			// Get an array of all the <field /> elements.
			$fields = $this->xml->xpath('//field[not(ancestor::field/form/*)]');
		}

		return $fields;
	}

	/**
	 * Method to get a form field group represented as an XML element object.
	 *
	 * @param   string  $group  The dot-separated form group path on which to find the group.
	 *
	 * @return  \SimpleXMLElement[]|boolean  An array of XML element objects for the group or boolean false on error.
	 *
	 * @since   1.7.0
	 */
	protected function &findGroup($group)
	{
		$false = false;
		$groups = array();
		$tmp = array();

		// Make sure there is a valid Form XML document.
		if (!($this->xml instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::%s `xml` is not an instance of SimpleXMLElement', \get_class($this), __METHOD__));
		}

		// Make sure there is actually a group to find.
		$group = explode('.', $group);

		if (!empty($group))
		{
			// Get any fields elements with the correct group name.
			$elements = $this->xml->xpath('//fields[@name="' . (string) $group[0] . '" and not(ancestor::field/form/*)]');

			// Check to make sure that there are no parent groups for each element.
			foreach ($elements as $element)
			{
				if (!$element->xpath('ancestor::fields[@name]'))
				{
					$tmp[] = $element;
				}
			}

			// Iterate through the nested groups to find any matching form field groups.
			for ($i = 1, $n = \count($group); $i < $n; $i++)
			{
				// Initialise some loop variables.
				$validNames = \array_slice($group, 0, $i + 1);
				$current = $tmp;
				$tmp = array();

				// Check to make sure that there are no parent groups for each element.
				foreach ($current as $element)
				{
					// Get any fields elements with the correct group name.
					$children = $element->xpath('descendant::fields[@name="' . (string) $group[$i] . '"]');

					// For the found fields elements validate that they are in the correct groups.
					foreach ($children as $fields)
					{
						// Get the group names as strings for ancestor fields elements.
						$attrs = $fields->xpath('ancestor-or-self::fields[@name]/@name');
						$names = array_map('strval', $attrs ? $attrs : array());

						// If the group names for the fields element match the valid names at this
						// level add the fields element.
						if ($validNames == $names)
						{
							$tmp[] = $fields;
						}
					}
				}
			}

			// Only include valid XML objects.
			foreach ($tmp as $element)
			{
				if ($element instanceof \SimpleXMLElement)
				{
					$groups[] = $element;
				}
			}
		}

		return $groups;
	}

	/**
	 * Method to load, setup and return a FormField object based on field data.
	 *
	 * @param   string  $element  The XML element object representation of the form field.
	 * @param   string  $group    The optional dot-separated form group path on which to find the field.
	 * @param   mixed   $value    The optional value to use as the default for the field.
	 *
	 * @return  FormField|boolean  The FormField object for the field or boolean false on error.
	 *
	 * @since   1.7.0
	 */
	protected function loadField($element, $group = null, $value = null)
	{
		// Make sure there is a valid SimpleXMLElement.
		if (!($element instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::%s `xml` is not an instance of SimpleXMLElement', \get_class($this), __METHOD__));
		}

		// Get the field type.
		$type = $element['type'] ? (string) $element['type'] : 'text';

		// Load the FormField object for the field.
		$field = FormHelper::loadFieldType($type);

		// If the object could not be loaded, get a text field object.
		if ($field === false)
		{
			$field = FormHelper::loadFieldType('text');
		}

		/*
		 * Get the value for the form field if not set.
		 * Default to the translated version of the 'default' attribute
		 * if 'translate_default' attribute if set to 'true' or '1'
		 * else the value of the 'default' attribute for the field.
		 */
		if ($value === null)
		{
			$default = (string) ($element['default'] ? $element['default'] : $element->default);

			if (($translate = $element['translate_default']) && ((string) $translate === 'true' || (string) $translate === '1'))
			{
				$lang = Factory::getLanguage();

				if ($lang->hasKey($default))
				{
					$debug = $lang->setDebug(false);
					$default = Text::_($default);
					$lang->setDebug($debug);
				}
				else
				{
					$default = Text::_($default);
				}
			}

			$value = $this->getValue((string) $element['name'], $group, $default);
		}

		// Setup the FormField object.
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
	 * Method to synchronize any field, form or rule paths contained in the XML document.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.7.0
	 * @todo    Maybe we should receive all addXXXpaths attributes at once?
	 */
	protected function syncPaths()
	{
		// Make sure there is a valid Form XML document.
		if (!($this->xml instanceof \SimpleXMLElement))
		{
			throw new \UnexpectedValueException(sprintf('%s::%s `xml` is not an instance of SimpleXMLElement', \get_class($this), __METHOD__));
		}

		// Get any addfieldpath attributes from the form definition.
		$paths = $this->xml->xpath('//*[@addfieldpath]/@addfieldpath');
		$paths = array_map('strval', $paths ? $paths : array());

		// Add the field paths.
		foreach ($paths as $path)
		{
			$path = JPATH_ROOT . '/' . ltrim($path, '/\\');
			self::addFieldPath($path);
		}

		// Get any addformpath attributes from the form definition.
		$paths = $this->xml->xpath('//*[@addformpath]/@addformpath');
		$paths = array_map('strval', $paths ? $paths : array());

		// Add the form paths.
		foreach ($paths as $path)
		{
			$path = JPATH_ROOT . '/' . ltrim($path, '/\\');
			self::addFormPath($path);
		}

		// Get any addrulepath attributes from the form definition.
		$paths = $this->xml->xpath('//*[@addrulepath]/@addrulepath');
		$paths = array_map('strval', $paths ? $paths : array());

		// Add the rule paths.
		foreach ($paths as $path)
		{
			$path = JPATH_ROOT . '/' . ltrim($path, '/\\');
			self::addRulePath($path);
		}

		// Get any addrulepath attributes from the form definition.
		$paths = $this->xml->xpath('//*[@addfilterpath]/@addfilterpath');
		$paths = array_map('strval', $paths ? $paths : array());

		// Add the rule paths.
		foreach ($paths as $path)
		{
			$path = JPATH_ROOT . '/' . ltrim($path, '/\\');
			self::addFilterPath($path);
		}

		// Get any addfieldprefix attributes from the form definition.
		$prefixes = $this->xml->xpath('//*[@addfieldprefix]/@addfieldprefix');
		$prefixes = array_map('strval', $prefixes ? $prefixes : array());

		// Add the field prefixes.
		foreach ($prefixes as $prefix)
		{
			FormHelper::addFieldPrefix($prefix);
		}

		// Get any addformprefix attributes from the form definition.
		$prefixes = $this->xml->xpath('//*[@addformprefix]/@addformprefix');
		$prefixes = array_map('strval', $prefixes ? $prefixes : array());

		// Add the field prefixes.
		foreach ($prefixes as $prefix)
		{
			FormHelper::addFormPrefix($prefix);
		}

		// Get any addruleprefix attributes from the form definition.
		$prefixes = $this->xml->xpath('//*[@addruleprefix]/@addruleprefix');
		$prefixes = array_map('strval', $prefixes ? $prefixes : array());

		// Add the field prefixes.
		foreach ($prefixes as $prefix)
		{
			FormHelper::addRulePrefix($prefix);
		}

		// Get any addruleprefix attributes from the form definition.
		$prefixes = $this->xml->xpath('//*[@addfilterprefix]/@addfilterprefix');
		$prefixes = array_map('strval', $prefixes ? $prefixes : array());

		// Add the field prefixes.
		foreach ($prefixes as $prefix)
		{
			FormHelper::addFilterPrefix($prefix);
		}

		return true;
	}

	/**
	 * Proxy for {@link FormHelper::addFieldPath()}.
	 *
	 * @param   mixed  $new  A path or array of paths to add.
	 *
	 * @return  array  The list of paths that have been added.
	 *
	 * @since   1.7.0
	 */
	public static function addFieldPath($new = null)
	{
		return FormHelper::addFieldPath($new);
	}

	/**
	 * Proxy for FormHelper::addFormPath().
	 *
	 * @param   mixed  $new  A path or array of paths to add.
	 *
	 * @return  array  The list of paths that have been added.
	 *
	 * @see     FormHelper::addFormPath()
	 * @since   1.7.0
	 */
	public static function addFormPath($new = null)
	{
		return FormHelper::addFormPath($new);
	}

	/**
	 * Proxy for FormHelper::addRulePath().
	 *
	 * @param   mixed  $new  A path or array of paths to add.
	 *
	 * @return  array  The list of paths that have been added.
	 *
	 * @see     FormHelper::addRulePath()
	 * @since   1.7.0
	 */
	public static function addRulePath($new = null)
	{
		return FormHelper::addRulePath($new);
	}

	/**
	 * Proxy for FormHelper::addFilterPath().
	 *
	 * @param   mixed  $new  A path or array of paths to add.
	 *
	 * @return  array  The list of paths that have been added.
	 *
	 * @see     FormHelper::addFilterPath()
	 * @since   4.0.0
	 */
	public static function addFilterPath($new = null)
	{
		return FormHelper::addFilterPath($new);
	}

	/**
	 * Method to get an instance of a form.
	 *
	 * @param   string          $name     The name of the form.
	 * @param   string          $data     The name of an XML file or string to load as the form definition.
	 * @param   array           $options  An array of form options.
	 * @param   boolean         $replace  Flag to toggle whether form fields should be replaced if a field
	 *                                    already exists with the same group/name.
	 * @param   string|boolean  $xpath    An optional xpath to search for the fields.
	 *
	 * @return  Form  Form instance.
	 *
	 * @since   1.7.0
	 * @deprecated  5.0 Use the FormFactory service from the container
	 * @throws  \InvalidArgumentException if no data provided.
	 * @throws  \RuntimeException if the form could not be loaded.
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
				throw new \InvalidArgumentException(sprintf('%1$s(%2$s, *%3$s*)', __METHOD__, $name, \gettype($data)));
			}

			// Instantiate the form.
			$forms[$name] = Factory::getContainer()->get(FormFactoryInterface::class)->createForm($name, $options);

			// Load the data.
			if (substr($data, 0, 1) === '<')
			{
				if ($forms[$name]->load($data, $replace, $xpath) == false)
				{
					throw new \RuntimeException(sprintf('%s() could not load form', __METHOD__));
				}
			}
			else
			{
				if ($forms[$name]->loadFile($data, $replace, $xpath) == false)
				{
					throw new \RuntimeException(sprintf('%s() could not load file', __METHOD__));
				}
			}
		}

		return $forms[$name];
	}

	/**
	 * Adds a new child SimpleXMLElement node to the source.
	 *
	 * @param   \SimpleXMLElement  $source  The source element on which to append.
	 * @param   \SimpleXMLElement  $new     The new element to append.
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected static function addNode(\SimpleXMLElement $source, \SimpleXMLElement $new)
	{
		// Add the new child node.
		$node = $source->addChild($new->getName(), htmlspecialchars(trim($new)));

		// Add the attributes of the child node.
		foreach ($new->attributes() as $name => $value)
		{
			$node->addAttribute($name, $value);
		}

		// Add any children of the new node.
		foreach ($new->children() as $child)
		{
			self::addNode($node, $child);
		}
	}

	/**
	 * Update the attributes of a child node
	 *
	 * @param   \SimpleXMLElement  $source  The source element on which to append the attributes
	 * @param   \SimpleXMLElement  $new     The new element to append
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected static function mergeNode(\SimpleXMLElement $source, \SimpleXMLElement $new)
	{
		// Update the attributes of the child node.
		foreach ($new->attributes() as $name => $value)
		{
			if (isset($source[$name]))
			{
				$source[$name] = (string) $value;
			}
			else
			{
				$source->addAttribute($name, $value);
			}
		}
	}

	/**
	 * Merges new elements into a source `<fields>` element.
	 *
	 * @param   \SimpleXMLElement  $source  The source element.
	 * @param   \SimpleXMLElement  $new     The new element to merge.
	 *
	 * @return  void
	 *
	 * @since   1.7.0
	 */
	protected static function mergeNodes(\SimpleXMLElement $source, \SimpleXMLElement $new)
	{
		// The assumption is that the inputs are at the same relative level.
		// So we just have to scan the children and deal with them.

		// Update the attributes of the child node.
		foreach ($new->attributes() as $name => $value)
		{
			if (isset($source[$name]))
			{
				$source[$name] = (string) $value;
			}
			else
			{
				$source->addAttribute($name, $value);
			}
		}

		foreach ($new->children() as $child)
		{
			$type = $child->getName();
			$name = $child['name'];

			// Does this node exist?
			$fields = $source->xpath($type . '[@name="' . $name . '"]');

			if (empty($fields))
			{
				// This node does not exist, so add it.
				self::addNode($source, $child);
			}
			else
			{
				// This node does exist.
				switch ($type)
				{
					case 'field':
						self::mergeNode($fields[0], $child);
						break;

					default:
						self::mergeNodes($fields[0], $child);
						break;
				}
			}
		}
	}

	/**
	 * Returns the value of an attribute of the form itself
	 *
	 * @param   string  $name     Name of the attribute to get
	 * @param   mixed   $default  Optional value to return if attribute not found
	 *
	 * @return  mixed             Value of the attribute / default
	 *
	 * @since   3.2
	 */
	public function getAttribute($name, $default = null)
	{
		if ($this->xml instanceof \SimpleXMLElement)
		{
			$value = $this->xml->attributes()->$name;

			if ($value !== null)
			{
				return (string) $value;
			}
		}

		return $default;
	}

	/**
	 * Getter for the form data
	 *
	 * @return   Registry  Object with the data
	 *
	 * @since    3.2
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * Method to get the XML form object
	 *
	 * @return  \SimpleXMLElement  The form XML object
	 *
	 * @since   3.2
	 */
	public function getXml()
	{
		return $this->xml;
	}

	/**
	 * Method to get a form field represented as an XML element object.
	 *
	 * @param   string  $name   The name of the form field.
	 * @param   string  $group  The optional dot-separated form group path on which to find the field.
	 *
	 * @return  \SimpleXMLElement|boolean  The XML element object for the field or boolean false on error.
	 *
	 * @since   3.7.0
	 */
	public function getFieldXml($name, $group = null)
	{
		return $this->findField($name, $group);
	}
}
