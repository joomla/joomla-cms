<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla! CMS.
 *
 * @since  1.6
 */
class JFormFieldModuleOrder extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   1.6
	 */
	protected $type = 'ModuleOrder';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  Empty div that will be replaced by the included javascript
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		$attr = '';

		// Initialize some field attributes.
		$attr .= !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$attr .= $this->disabled ? ' disabled' : '';
		$attr .= !empty($this->size) ? ' size="' . $this->size . '"' : '';

		// Initialize JavaScript field attributes.
		$attr .= !empty($this->onchange) ? ' onchange="' . $this->onchange . '"' : '';

		$ordering = $this->form->getValue('ordering');
		$clientId = $this->form->getValue('client_id');
		$name     = $this->name;
		$id       = $this->id;
		$token    = JSession::getFormToken() . '=1';

		JHtml::_('jquery.framework');

		JFactory::getDocument()->addScriptDeclaration(
<<<JS
		jQuery(document).ready(function($) {
			var client_id = "$clientId";
			var elem = document.getElementById("parent_$id");
			var originalOrder = "$ordering";
			var originalPos = $("#jform_position").chosen().val();
			var orders = new Array();
			var getNewOrder = function() {
				$.ajax({
					type: "GET",
					dataType: "json",
					url: "index.php?option=com_modules&task=module.orderPosition&$token",
					data: { "client_id": client_id,
							"position": originalPos
					 },
					success:function(response) {
					 	console.log(response.data);
						for (key in response.data) {
							orders[key] =  response.data[key].split(',');
						}
						writeDynaList('name="$name" id="$id"$attr', orders, originalPos, originalPos, originalOrder, elem);
					}
				});
			}
			getNewOrder();

			$("#jform_position").chosen().change( function() {
				originalPos = $("#jform_position").chosen().val();

				getNewOrder();
			});
		});

JS
		);

		return '<div id="parent_' . $id . '" ></div>';
	}
}
