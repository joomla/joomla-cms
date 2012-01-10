// TEXTAREA  ========================================================================
function controler_percha_checkbox()
{ 
    var extras = $('jform_extras');
    extras.value  = $('jform_params_field_checkbox_name').value+"|"+$('jform_params_field_checkbox_value').value
    extras.set({
				'value': extras.value
			});
    hide_input()
}

function hide_checkbox()
{
    var nom =  'checkbox-params';

    if($(nom)!=null)
    {
        
        $($(nom).getParent( )).setStyle('display','none');
    }
 

}
 



