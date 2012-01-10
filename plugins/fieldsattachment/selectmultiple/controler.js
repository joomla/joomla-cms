// TEXTAREA  ========================================================================
function controler_percha_selectmultiple()
{
alert("controler_percha_selectmultiple");
    var extras = $('jform_extras');
    extras.value  =  extras.value . "\n". $('jform_params_field_selectmultiple_name').value+"|"+$('jform_params_field_selectmultiple_value').value
    extras.set({
				'value': extras.value
			});
    hide_selectmultiple();
    alert("controler_percha_selectmultiple");
}

function hide_selectmultiple()
{
    alert("selectmultiple");
    var nom =  'select-params';

    if($(nom)!=null)
    {
        
        $($(nom).getParent( )).setStyle('display','none');
    }
 

}
 



