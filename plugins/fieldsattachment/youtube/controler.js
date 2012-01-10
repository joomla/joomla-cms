// TEXTAREA  ========================================================================
function controler_percha_youtube()
{  
hide_youtube()
}

function hide_youtube()
{
    var nom =  'input-params';

    if($(nom)!=null)
    {
        
        $($(nom).getParent( )).setStyle('display','none');
    }
 

}
 



