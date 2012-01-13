/**
 * @package		Joomla.JavaScript
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */


// Only define the Joomla namespace if not defined.
if (typeof(Joomla) === 'undefined') {
	var Joomla = {};
}

/**
* JCombobox JavaScript behavior.
*
* Inspired by: Subrata Chakrabarty <http://chakrabarty.com/editable_dropdown_samplecode.html>
*
* @package		Joomla.JavaScript
* @since		1.6
*/
Joomla.combobox = {};
Joomla.combobox.transform = function(el, options)
{
	// Add the editable option to the select.
	var o = new Element('option', {'class':'custom'}).set('text', Joomla.JText._('ComboBoxInitString', 'type custom...'));
	o.inject(el, 'top');
	document.id(el).set('changeType', 'manual');

	// Add a key press event handler.
	el.addEvent('keypress', function(e){

		// The change in selected option was browser behavior.
		if ((this.options.selectedIndex != 0) && (this.get('changeType') == 'auto'))
		{
			this.options.selectedIndex = 0;
			this.set('changeType', 'manual');
		}

		// Check to see if the character is valid.
		if ((e.code > 47 && e.code < 59) || (e.code > 62 && e.code < 127) || (e.code == 32)) {
			var validChar = true;
		} else {
			var validChar = false;
		}

		// If the editable option is selected, proceed.
		if (this.options.selectedIndex == 0)
		{
			// Get the custom string for editing.
			var customString = this.options[0].value;

			// If the string is being edited for the first time, nullify it.
			if ((validChar == true) || (e.key == 'backspace'))
			{
				if (customString == Joomla.JText._('ComboBoxInitString', 'type custom...')) {
					customString = '';
				}
			}

			// If the backspace key was used, remove a character from the end of the string.
			if (e.key == 'backspace')
			{
				customString = customString.substring(0, customString.length - 1);
				if (customString == '') {
					customString = Joomla.JText._('ComboBoxInitString', 'type custom...');
				}

				// Indicate that the change event was manually initiated.
				this.set('changeType', 'manual');
			}

			// Handle valid characters to add to the editable option.
			if (validChar == true)
			{
				// Concatenate the new character to the custom string.
				customString += String.fromCharCode(e.code);
			}

			// Set the new custom string into the editable select option.
			this.options.selectedIndex = 0;
			this.options[0].text = customString;
			this.options[0].value = customString;

			e.stop();
		}
	});

	// Add a change event handler.
	el.addEvent('change', function(e){

		// The change in selected option was browser behavior.
		if ((this.options.selectedIndex != 0) && (this.get('changeType') == 'auto'))
		{
			this.options.selectedIndex = 0;
			this.set('changeType', 'manual');
		}
	});

	// Add a keydown event handler.
	el.addEvent('keydown', function(e){

		// Stop the backspace key from firing the back button of the browser.
		if (e.code == 8 || e.code == 127) {
			e.stop();

			// Stopping the keydown event in WebKit stops the keypress event as well.
			if (Browser.Engine.webkit || Browser.Engine.trident) {
				this.fireEvent('keypress', e);
			}
		}

		if (this.options.selectedIndex == 0)
		{
			/*
			 * In some browsers a feature exists to automatically jump to select options which
			 * have the same letter typed as the first letter of the option.  The following
			 * section is designed to mitigate this issue when editing the custom option.
			 *
			 * Compare the entered character with the first character of all non-editable
			 * select options.  If they match, then we assume the change happened because of
			 * the browser trying to auto-change for the given character.
			 */
			var character = String.fromCharCode(e.code).toLowerCase();
			for (var i = 1; i < this.options.length; i++)
			{
				// Get the first character from the select option.
				var FirstChar = this.options[i].value.charAt(0).toLowerCase();

				// If the first character matches the entered character, the change was automatic.
				if ((FirstChar == character)) {
					this.options.selectedIndex = 0;
					this.set('changeType', 'auto');
				}
			}
		}
	});

	// Add a keyup event handler.
	el.addEvent('keyup', function(e){

		// If the left or right arrow keys are pressed, return to the editable option.
		if ((e.key == 'left') || (e.key == 'right')) {
			this.options.selectedIndex = 0;
		}

		// The change in selected option was browser behavior.
		if ((this.options.selectedIndex != 0) && (this.get('changeType') == 'auto'))
		{
			this.options.selectedIndex = 0;
			this.set('changeType', 'manual');
		}
	});

};

// Load the combobox behavior into the Joomla namespace when the document is ready.
window.addEvent('domready', function(){
	$$('select.combobox').each(function(el){
		Joomla.combobox.transform(el);
	});
});
