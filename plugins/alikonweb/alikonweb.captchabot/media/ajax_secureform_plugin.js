/*
06/07/2009 20.22
$js = "window.addEvent('domready', function(){checkCaptcha('".$form."','".$buttonid."','".$pswid."','".$msgid."'); })";
  function checkCaptcha(form) {
*/
//window.addEvent("domready",function(){
function checkCaptcha(form, buttonid,pswid,msgid ) {
	$(buttonid).setProperty('disabled', 'true');
	var box = $(msgid);
	//var fx = box.effects({duration: 1000, transition: Fx.Transitions.Quart.easeOut});
            
            $(pswid).addEvent("change",function(){
            	$(buttonid).setProperty('disabled', 'true');
            if ( $(pswid).value.length > 0 ){
              
            
                var url1="index.php?option=com_"+form+"&amp;task=chkCaptcha&amp;format=raw&amp;"+pswid+"="+$(pswid).value+"&amp;campo="+pswid+"&amp;fid="+buttonid;
                box.style.display="block";     
            		box.set('html','Check in progress...');	 
            	
            			var req = new Request.JSON({
		                    url: url1, 
	               onComplete: function(response) {
			                //  var resp=JSON	.evaluate(response);
                      if (response.msg==='false'){
                         $(pswid).value='';
                         $(pswid).focus();
                           $(buttonid).setProperty('disabled', 'true');
                        }else{
                        	$(buttonid).removeProperty('disabled'); 
                        }
                        box.set('html', response.html);                        
                                       
                          
		                  }
		                  
	                   });
	
	               // azioniamo la richiesta
	               req.get();
             
            		/****
                var a=new Ajax(url,{
                    method:"get",
                    onComplete: function(response){
                        var resp=Json.evaluate(response);
            
                        if (resp.msg==='false'){
                         $(pswid).value='';
                         $(pswid).focus();
                           $(buttonid).setProperty('disabled', 'true');
                        }else{
                        	$(buttonid).removeProperty('disabled'); 
                        }
            
                        fx.start({	
		                    	}).chain(function() {
		                    		box.setHTML(resp.html);
		                    		this.start.delay(1500, this, {'opacity': 0});
		                    	}).chain(function() {
		                    		box.style.display="none";
		                    		this.start.delay(1501, this, {'opacity': 1});
		                    	});	
              
                    }
                }).request();
                
                ****/
              } //la if  
            });
        }
        
//  );

function checkTestCaptcha(formid,form, buttonid,pswid,msgid ) {
           
            var box = $(msgid);
	        //  var fx = box.effects({duration: 1000, transition: Fx.Transitions.Quart.easeOut});
            var formbox=formid;
           $(formid).addEvent('submit', function(e) {
 if ( $(pswid).value.length > 0 ){
				   new Event(e).stop();
      
            		  box.style.display="block";     
            		  box.setHTML('Check in progress...');	                             
               
               var url="index.php?option=com_"+form+"&amp;task=chkCaptcha&amp;format=raw&amp;"+pswid+"="+$(pswid).value+"&amp;campo="+pswid+"&amp;fid="+buttonid; 
                var a=new Ajax(url,{
                    method:"get",
                    onComplete: function(response){
                        var resp=Json.evaluate(response);
                        if (resp.msg==='false'){
                         $(pswid).value='';
                         $(pswid).focus();;
                        }
                        if (resp.msg==='true'){
                        $('ajax-captcha-desc').setHTML('Captcha solved');
                        $(formid).remove();	                        
                        box.style.display="block"; 
                         
                        box.setHTML(resp.html);	      
                        }                      
                                
              
                         fx.start({	
		                    	}).chain(function() {
		                    		box.setHTML(resp.html);
		                    		this.start.delay(2500, this, {'opacity': 0});
		                    	}).chain(function() {
		                    		box.style.display="none";
		                    		this.start.delay(5001, this, {'opacity': 1});
		                    	});
                    }
                }).request();
             } //la if  
             })
    }              

	