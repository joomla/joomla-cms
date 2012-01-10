/**
 * @version		$Id: submitbutton.js 15 2011-09-02 18:37:15Z cristian $
 * @package		fieldsattach
 * @subpackage		Components
 * @copyright		Copyright (C) 2011 - 2020 Open Source Cristian Grañó, Inc. All rights reserved.
 * @author		Cristian Grañó
 * @link		http://joomlacode.org/gf/project/fieldsattach_1_6/
 * @license		License GNU General Public License version 2 or later
 */
Joomla.submitbutton = function(task)
{ 
        if(task == 'cancel') {
                Joomla.submitform(task);
                return true;
        }
	if (task == '')
	{
		return false;
	}
	else
	{
		var isValid=true;
		var action = task.split('.');
		if (action[1] != 'cancel' && action[1] != 'close' && task!='cancel')
		{
			var forms = $$('form.form-validate');
			for (var i=0;i<forms.length;i++)
			{
				if (!document.formvalidator.isValid(forms[i]))
				{
					isValid = false;
					break;
				}
			}
		}else{isValid = true;}
	
		if (isValid)
		{
			Joomla.submitform(task);
                          
			return true;
		}
		else
		{
			alert(Joomla.JText._('COM_HELLOWORLD_HELLOWORLD_ERROR_UNACCEPTABLE','Some values are unacceptable'));
			return false;
		}
	}
}

