<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  render
 * @copyright   Copyright (C) 2010 - 2015 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('FOF_INCLUDED') or die;

/**
 * Abstract view renderer class. The renderer is what turns XML view templates
 * into actual HTML code, renders the submenu links and potentially wraps the
 * HTML output in a div with a component-specific ID.
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
abstract class FOFRenderAbstract
{
	/** @var int Priority of this renderer. Higher means more important */
	protected $priority = 50;

	/** @var int Is this renderer enabled? */
	protected $enabled = false;

	/**
	 * Returns the information about this renderer
	 *
	 * @return object
	 */
	public function getInformation()
	{
		return (object) array(
				'priority'	 => $this->priority,
				'enabled'	 => $this->enabled,
		);
	}

	/**
	 * Echoes any HTML to show before the view template
	 *
	 * @param   string    $view    The current view
	 * @param   string    $task    The current task
	 * @param   FOFInput  $input   The input array (request parameters)
	 * @param   array     $config  The view configuration array
	 *
	 * @return  void
	 */
	abstract public function preRender($view, $task, $input, $config = array());

	/**
	 * Echoes any HTML to show after the view template
	 *
	 * @param   string    $view    The current view
	 * @param   string    $task    The current task
	 * @param   FOFInput  $input   The input array (request parameters)
	 * @param   array     $config  The view configuration array
	 *
	 * @return  void
	 */
	abstract public function postRender($view, $task, $input, $config = array());

	/**
	 * Renders a FOFForm and returns the corresponding HTML
	 *
	 * @param   FOFForm   &$form     The form to render
	 * @param   FOFModel  $model     The model providing our data
	 * @param   FOFInput  $input     The input object
	 * @param   string    $formType  The form type: edit, browse or read
	 * @param   boolean   $raw       If true, the raw form fields rendering (without the surrounding form tag) is returned.
	 *
	 * @return  string    The HTML rendering of the form
	 */
	public function renderForm(FOFForm &$form, FOFModel $model, FOFInput $input, $formType = null, $raw = false)
	{
		if (is_null($formType))
		{
			$formType = $form->getAttribute('type', 'edit');
		}
		else
		{
			$formType = strtolower($formType);
		}

		switch ($formType)
		{
			case 'browse':
				return $this->renderFormBrowse($form, $model, $input);
				break;

			case 'read':
				if ($raw)
				{
					return $this->renderFormRaw($form, $model, $input, 'read');
				}
				else
				{
					return $this->renderFormRead($form, $model, $input);
				}

				break;

			default:
				if ($raw)
				{
					return $this->renderFormRaw($form, $model, $input, 'edit');
				}
				else
				{
					return $this->renderFormEdit($form, $model, $input);
				}
				break;
		}
	}

	/**
	 * Renders the submenu (link bar) for a category view when it is used in a
	 * extension
	 *
	 * Note: this function has to be called from the addSubmenu function in
	 * 		 the ExtensionNameHelper class located in
	 * 		 administrator/components/com_ExtensionName/helpers/Extensionname.php
	 *
	 * Example Code:
	 *
	 *	class ExtensionNameHelper
	 *	{
	 * 		public static function addSubmenu($vName)
	 *		{
	 *			// Load FOF
	 *			include_once JPATH_LIBRARIES . '/fof/include.php';
	 *
	 *			if (!defined('FOF_INCLUDED'))
	 *			{
	 *				JError::raiseError('500', 'FOF is not installed');
	 *			}
	 *
	 *			if (version_compare(JVERSION, '3.0', 'ge'))
	 *			{
	 *				$strapper = new FOFRenderJoomla3;
	 *			}
	 *			else
	 *			{
	 *				$strapper = new FOFRenderJoomla;
	 *			}
	 *
	 *			$strapper->renderCategoryLinkbar('com_babioonevent');
	 *		}
	 *	}
	 *
	 * @param   string  $extension  The name of the extension
	 * @param   array   $config     Extra configuration variables for the toolbar
	 *
	 * @return  void
	 */
	public function renderCategoryLinkbar($extension, $config = array())
	{
		// On command line don't do anything
		if (FOFPlatform::getInstance()->isCli())
		{
			return;
		}

		// Do not render a category submenu unless we are in the the admin area
		if (!FOFPlatform::getInstance()->isBackend())
		{
			return;
		}

		$toolbar = FOFToolbar::getAnInstance($extension, $config);
		$toolbar->renderSubmenu();

		$this->renderLinkbarItems($toolbar);
	}

	/**
	 * Renders a FOFForm for a Browse view and returns the corresponding HTML
	 *
	 * @param   FOFForm   &$form  The form to render
	 * @param   FOFModel  $model  The model providing our data
	 * @param   FOFInput  $input  The input object
	 *
	 * @return  string    The HTML rendering of the form
	 */
	abstract protected function renderFormBrowse(FOFForm &$form, FOFModel $model, FOFInput $input);

	/**
	 * Renders a FOFForm for a Read view and returns the corresponding HTML
	 *
	 * @param   FOFForm   &$form  The form to render
	 * @param   FOFModel  $model  The model providing our data
	 * @param   FOFInput  $input  The input object
	 *
	 * @return  string    The HTML rendering of the form
	 */
	abstract protected function renderFormRead(FOFForm &$form, FOFModel $model, FOFInput $input);

	/**
	 * Renders a FOFForm for an Edit view and returns the corresponding HTML
	 *
	 * @param   FOFForm   &$form  The form to render
	 * @param   FOFModel  $model  The model providing our data
	 * @param   FOFInput  $input  The input object
	 *
	 * @return  string    The HTML rendering of the form
	 */
	abstract protected function renderFormEdit(FOFForm &$form, FOFModel $model, FOFInput $input);

	/**
	 * Renders a raw FOFForm and returns the corresponding HTML
	 *
	 * @param   FOFForm   &$form     The form to render
	 * @param   FOFModel  $model     The model providing our data
	 * @param   FOFInput  $input     The input object
	 * @param   string    $formType  The form type e.g. 'edit' or 'read'
	 *
	 * @return  string    The HTML rendering of the form
	 */
	abstract protected function renderFormRaw(FOFForm &$form, FOFModel $model, FOFInput $input, $formType);

	/**
	 * Renders a raw fieldset of a FOFForm and returns the corresponding HTML
	 *
	 * @TODO: Convert to an abstract method or interface at FOF3
	 *
	 * @param   stdClass  &$fieldset   The fieldset to render
	 * @param   FOFForm   &$form       The form to render
	 * @param   FOFModel  $model       The model providing our data
	 * @param   FOFInput  $input       The input object
	 * @param   string    $formType    The form type e.g. 'edit' or 'read'
	 * @param   boolean   $showHeader  Should I render the fieldset's header?
	 *
	 * @return  string    The HTML rendering of the fieldset
	 */
	protected function renderFieldset(stdClass &$fieldset, FOFForm &$form, FOFModel $model, FOFInput $input, $formType, $showHeader = true)
	{

	}

	/**
	 * Renders a label for a fieldset.
	 *
	 * @TODO: Convert to an abstract method or interface at FOF3
	 *
	 * @param   object  	$field  	The field of the label to render
	 * @param   FOFForm   	&$form      The form to render
	 * @param 	string		$title		The title of the label
	 *
	 * @return 	string		The rendered label
	 */
	protected function renderFieldsetLabel($field, FOFForm &$form, $title)
	{

	}

	/**
	 * Checks if the fieldset defines a tab pane
	 *
	 * @param   SimpleXMLElement  $fieldset
	 *
	 * @return  boolean
	 */
	protected function isTabFieldset($fieldset)
	{
		if (!isset($fieldset->class) || !$fieldset->class)
		{
			return false;
		}

		$class = $fieldset->class;
		$classes = explode(' ', $class);

		if (!in_array('tab-pane', $classes))
		{
			return false;
		}
		else
		{
			return in_array('active', $classes) ? 2 : 1;
		}
	}
}
