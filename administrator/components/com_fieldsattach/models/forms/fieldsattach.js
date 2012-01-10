window.addEvent('domready', function() {
        var extras = $('jform_extras');


        //Text AREA PARAMETER ========================================================================
        var type = $('jform_type');
        //HIDE ALL
        hide_all();
        //Open actual
        var nom = 'percha_'+$('jform_type').value+'-params';
        open_actual(nom);

        type.addEvents({
		'change':function(){
                        hide_all();
                        var name_type = "jform_params_field_"+type.get('value'); 
                        var nom = 'percha_'+type.get('value')+'-params';

                         open_actual( nom );
                         $("jform_extras").value="";
                          
                    }
        });


        var imagetype = $('jform_params_field_selectable');
        imagetype.addEvents({
		'change':function(){
                         if(imagetype.get('value')=="selectable")
                         {
                            $("jform_params_field_width-lbl").setStyle('display','none');
                            $("jform_params_field_width").setStyle('display','none');
                            $("jform_params_field_height-lbl").setStyle('display','none');
                            $("jform_params_field_height").setStyle('display','none');
                            $("jform_params_field_filter-lbl").setStyle('display','none');
                            $("jform_params_field_filter").setStyle('display','none');
                         }else{
                            $("jform_params_field_width-lbl").setStyle('display','block');
                            $("jform_params_field_width").setStyle('display','block');
                            $("jform_params_field_height-lbl").setStyle('display','block');
                            $("jform_params_field_height").setStyle('display','block');
                            $("jform_params_field_filter-lbl").setStyle('display','block');
                            $("jform_params_field_filter").setStyle('display','block');
                         }
                          
                    }
        });





	//Text AREA PARAMETER ========================================================================
        /*var select = $('jform_params_field_textarea');
        select.addEvents({
		'change':function(){ 
                        extras.set({
				'value':select.get('value')
			});

                    }
        });*/

        //Text AREA PARAMETER ========================================================================

});

function hide_all()
{
    //Hidde all types 
    for(var cont=0;cont<  $('jform_type').length; cont++)
    {
        var nom = 'percha_'+$('jform_type')[cont].value+'-params';

        if($(nom)!=null)
            {
                    //$(nom).setStyle('display','none');
                   // $($(nom).getNext( )).setStyle('display','none');
                    $($(nom).getParent( )).setStyle('display','none');
            }
    }
    $("jform_extras-lbl").setStyle('display','none');
    $("jform_extras").setStyle('display','none');


}

function open_actual(nom)
{  
    var tt = nom.split("-");
    if($(nom)!=null){
            $($(nom).getParent( )).setStyle('display','block');
            show_extra();
            //eval(tt[0]+"();");
    }
        

    /*if($(nom)!=null)
    { 
            $($(nom).getParent( )).setStyle('display','block');
    }
    
    if(  (nom == "percha_textarea-params")
        || (nom == "percha_textarea-params")
        || (nom == "percha_checkbox-params")
        || (nom == "percha_select-params")
        || (nom == "percha_select_multiple-params")
        || (nom == "percha_image-params")
        || (nom == "percha_listunits-params")
    )
    show_extra();
*/
 
}

function show_extra()
{
    $("jform_extras-lbl").setStyle('display','block');
    $("jform_extras").setStyle('display','block');
}

function controler(txt)
{
  
   if(txt == "percha_textarea"){ controler_percha_textarea();}
   if(txt == "percha_checkbox"){ controler_percha_checkbox();}
   if(txt == "percha_select"){ controler_percha_select();}
   if(txt == "percha_selectmultiple"){ controler_percha_selectmultiple();}
   if(txt == "percha_image"){ controler_percha_image();}
   if(txt == "percha_listunits"){ controler_percha_listunits();}
   

}

// TEXTAREA  ========================================================================
function controler_percha_textarea1()
{
    
    var extras = $('jform_extras');
    extras.set({
				'value':$('jform_params_field_textarea').value
			});
}

// checkbox  ========================================================================
function controler_percha_checkbox()
{
    var extras = $('jform_extras');
    extras.value  = $('jform_params_field_checkbox_name').value+"|"+$('jform_params_field_checkbox_value').value
    extras.set({
				'value': extras.value
			});
}

// select ========================================================================
function controler_percha_select()
{
    var extras = $('jform_extras');
    var content = extras.value;
    if(String(extras.value).length){ extras.value +="\n" }
    extras.value += $('jform_params_field_select_name').value+"|"+$('jform_params_field_select_value').value
    extras.set({
				'value': extras.value
			});

}
// select multiple========================================================================
function controler_percha_select_multiple()
{
    var extras = $('jform_extras');
    var content = extras.value;
    if(String(extras.value).length){ extras.value +="\n" }
    extras.value += $('jform_params_field_select_multiple_name').value+"|"+$('jform_params_field_select_multiple_value').value
    extras.set({
				'value': extras.value
			});
}

// Image ========================================================================
function controler_percha_image()
{
    var extras = $('jform_extras'); 

    contenr = $('jform_params_field_width').value+"|"+$('jform_params_field_height').value;
    contenr += "|"+$('jform_params_field_filter').value+"|"+$('jform_params_field_selectable').value;
    extras.value = contenr;
}
// select ========================================================================
function controler_percha_listunits()
{
    var extras = $('jform_extras');
    var content = extras.value;
    if(String(extras.value).length){ extras.value +="|" }
    extras.value += $('jform_params_field_listunits_add_column').value
    extras.set({
				'value': extras.value
			});

}


