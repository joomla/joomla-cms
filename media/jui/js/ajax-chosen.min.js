
(function($){return $.fn.ajaxChosen=function(settings,callback,chosenOptions){var chosenXhr,defaultOptions,options,select;if(settings==null){settings={};}
if(callback==null){callback={};}
if(chosenOptions==null){chosenOptions=function(){};}
defaultOptions={minTermLength:3,afterTypeDelay:500,jsonTermKey:"term",keepTypingMsg:Joomla.JText._('JGLOBAL_KEEP_TYPING'),lookingForMsg:Joomla.JText._('JGLOBAL_LOOKING_FOR')};select=this;chosenXhr=null;options=$.extend({},defaultOptions,$(select).data(),settings);this.chosen(chosenOptions?chosenOptions:{});return this.each(function(){return $(this).next('.chzn-container').find(".search-field > input, .chzn-search > input").bind('keyup',function(){var field,msg,success,untrimmed_val,val;untrimmed_val=$(this).attr('value');val=$.trim($(this).attr('value'));msg=val.length<options.minTermLength?options.keepTypingMsg:options.lookingForMsg+(" '"+val+"'");select.next('.chzn-container').find('.no-results').text(msg);if(val===$(this).data('prevVal')){return false;}
$(this).data('prevVal',val);if(this.timer){clearTimeout(this.timer);}
if(val.length<options.minTermLength){return false;}
field=$(this);if(!(options.data!=null)){options.data={};}
options.data[options.jsonTermKey]=val;if(options.dataCallback!=null){options.data=options.dataCallback(options.data);}
success=options.success;options.success=function(data){var items,selected_values;if(!(data!=null)){return;}
selected_values=[];select.find('option').each(function(){if(!$(this).is(":selected")){return $(this).remove();}else{return selected_values.push($(this).val()+"-"+$(this).text());}});select.find('optgroup:empty').each(function(){return $(this).remove();});items=callback(data);$.each(items,function(i,element){var group,text,value;if(element.group){group=select.find("optgroup[label='"+element.text+"']");if(!group.size()){group=$("<optgroup />");}
group.attr('label',element.text).appendTo(select);return $.each(element.items,function(i,element){var text,value;if(typeof element==="string"){value=i;text=element;}else{value=element.value;text=element.text;}
if($.inArray(value+"-"+text,selected_values)===-1){return $("<option />").attr('value',value).html(text).appendTo(group);}});}else{if(typeof element==="string"){value=i;text=element;}else{value=element.value;text=element.text;}
if($.inArray(value+"-"+text,selected_values)===-1){return $("<option />").attr('value',value).html(text).appendTo(select);}}});if(Object.keys(items).length){select.trigger("liszt:updated");}else{select.data().chosen.no_results_clear();select.data().chosen.no_results(field.attr('value'));}
if(success!=null){success(data);}
return field.attr('value',untrimmed_val);};return this.timer=setTimeout(function(){if(chosenXhr){chosenXhr.abort();}
return chosenXhr=$.ajax(options);},options.afterTypeDelay);});});};})(jQuery);