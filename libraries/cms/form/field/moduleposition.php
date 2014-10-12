<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('text');

/**
 * Form Field class for the Joomla! CMS.
 *
 * @package     Joomla.Libraries
 * @subpackage  Form
 * @since       1.6
 */
class JFormFieldModulePosition extends JFormFieldText
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $type = 'ModulePosition';

	/**
	 * The client ID.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $clientId;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'clientId':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'clientId':
				$this->clientId = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$result = parent::setup($element, $value, $group);

		if ($result == true)
		{
			// Get the client id.
			$clientId = $this->element['client_id'];

			if (!isset($clientId))
			{
				$clientName = $this->element['client'];

				if (isset($clientName))
				{
					$client = JApplicationHelper::getClientInfo($clientName, true);
					$clientId = $client->id;
				}
			}

			if (!isset($clientId) && $this->form instanceof JForm)
			{
				$clientId = $this->form->getValue('client_id');
			}

			$this->clientId = (int) $clientId;
		}

		return $result;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		// Include jQuery
		JHtml::_('jquery.framework');
		JHtml::_('bootstrap.modal');

		// Build the script.
		$script = array();
		$script[] = '	function jSelectPosition_' . $this->id . '(name) {';
		$script[] = '		document.getElementById("' . $this->id . '").value = name;';
		$script[] = '		jQuery("#moduleModal").modal("hide");';
		$script[] = '	}';

		// Add normalized style.
		$style = '@media only screen and (min-width : 768px) {
			#userModal {
			width: 80% !important;
			margin-left:-40% !important;
			height:auto;
			}
			#userModal #userModal-container .modal-body iframe {
			margin:0;
			padding:0;
			display:block;
			width:100%;
			height:400px !important;
			border:none;
			}
		}';

		// Add the script to the document head.
		JFactory::getDocument()->addScriptDeclaration(implode("\n", $script));
		JFactory::getDocument()->addStyleDeclaration($style);

		// Setup variables for display.
		$html = array();
		$link = 'index.php?option=com_modules&view=positions&layout=modal&tmpl=component&function=jSelectPosition_' . $this->id . '&amp;client_id=' . $this->clientId;

		// The current user display field.
		$html[] = '<div class="input-append">';
		$html[] = parent::getInput()
			. '<a href="#moduleModal" role="button" class="btn btn-primary" data-toggle="modal" title="' . JText::_('COM_MODULES_CHANGE_POSITION_TITLE') . '">' . JText::_('COM_MODULES_CHANGE_POSITION_BUTTON') . '</a>';
		$html[] = JHtmlBootstrap::renderModal('moduleModal', array( 'url' => $link, 'title' => JText::_('COM_MODULES_CHANGE_POSITION_TITLE'),'height' => '800', 'width' => '600'), '');

$html[] = '</div>';

		return implode("\n", $html);
	}
}
