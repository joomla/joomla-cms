<?php
/**
 * @package     SP Page Builder
 *
 * @copyright   Copyright (C) 2010 - 2018 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.form.formfield');

class JFormFieldResetcss extends JFormField {

	protected $type = 'Resetcss';

	protected function getInput() {

		Jhtml::_('jquery.framework');
		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration('jQuery(function($) {
			$("#btn-reset-css").on("click", function(event) {
				event.preventDefault();
				var $this = $(this);
				$this.text($this.data("loading"));
				var request = {
					"option" : "com_sppagebuilder",
					"task" : "resetcss"
				};
				$.ajax({
					type   : "POST",
					data   : request,
					success: function (data) {
						$this.text($this.data("text"));
					}
				});
				
			});
		});');

		return '<a id="btn-reset-css" class="btn btn-default" data-text="'. JText::_('COM_SPPAGEBUILDER_RESET_CSS_TEXT') .'" data-loading="'. JText::_('COM_SPPAGEBUILDER_RESET_CSS_TEXT_LOADING') .'" href="#">'. JText::_('COM_SPPAGEBUILDER_RESET_CSS_TEXT') .'</a>';
	}
}
