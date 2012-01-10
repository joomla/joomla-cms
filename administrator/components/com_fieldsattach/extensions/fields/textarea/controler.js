// TEXTAREA  ========================================================================
function controler_percha_textarea()
{ 
    var extras = $('jform_extras');
    extras.set({
				'value':$('jform_params_field_textarea').value
			});
    hide_input()
}

function hide_textarea()
{
    var nom =  'input-params';

    if($(nom)!=null)
    {
        
        $($(nom).getParent( )).setStyle('display','none');
    }
 

}
 



