// TEXTAREA  ========================================================================
function controler_percha_file()
{ 
    hide_file();
}

function hide_file()
{
    var nom =  'file-params'; 
    if($(nom)!=null)
    { 
        $($(nom).getParent( )).setStyle('display','none');
    } 

}
 



