// image  ========================================================================
function controler_percha_image()
{ 
    var extras = $('jform_extras'); 

    contenr = $('jform_params_field_width').value+"|"+$('jform_params_field_height').value;
    contenr += "|"+$('jform_params_field_filter').value+"|"+$('jform_params_field_selectable').value;
    extras.value = contenr;
    hide_input()
}

function hide_textarea()
{
    var nom =  'image-params';

    if($(nom)!=null)
    {
        
        $($(nom).getParent( )).setStyle('display','none');
    }
 

}
 



