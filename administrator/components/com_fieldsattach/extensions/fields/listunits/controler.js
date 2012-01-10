// TEXTAREA  ========================================================================
function controler_percha_listunits()
{ 
  var extras = $('jform_extras');
    var content = extras.value;
    if(String(extras.value).length){ extras.value +="|" }
    extras.value += $('jform_params_field_listunits_add_column').value
    extras.set({
				'value': extras.value
			});
hide_listunits()
}

function hide_listunits()
{
    var nom =  'input-params';

    if($(nom)!=null)
    {
        
        $($(nom).getParent( )).setStyle('display','none');
    }
 

}
 



