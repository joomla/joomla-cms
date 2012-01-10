// TEXTAREA  ========================================================================
function controler_percha_vimeo()
{  
hide_vimeo()
}

function hide_vimeo()
{
    var nom =  'input-params';

    if($(nom)!=null)
    {
        
        $($(nom).getParent( )).setStyle('display','none');
    }
 

}
 



