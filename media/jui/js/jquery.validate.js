/**
 * @author GeekTantra
 * @date 20 September 2009
 * http://www.geektantra.com/2009/09/jquery-live-form-validation/
 * Modified by dsmith: added support for twitter boostrap
 */
(function(jQuery){
    var ValidationErrors = new Array();
    jQuery.fn.validate = function(options){
        options = jQuery.extend({
            expression: "return true;",
            message: "",
            error_message_class: "help-inline",
            error_container_class: "form-validate",
            live: true
        }, options);
        var SelfID = jQuery(this).attr("id");
        var unix_time = new Date();
        unix_time = parseInt(unix_time.getTime() / 1000);
        if (!jQuery(this).parents('form:first').attr("id")) {
            jQuery(this).parents('form:first').attr("id", "Form_" + unix_time);
        }
        var FormID = jQuery(this).parents('form:first').attr("id");
        if (!((typeof(ValidationErrors[FormID]) == 'object') && (ValidationErrors[FormID] instanceof Array))) {
            ValidationErrors[FormID] = new Array();
        }
        if (options['live']) {
            if (jQuery(this).find('input').length > 0) {
                jQuery(this).find('input').bind('blur', function(){
                    if (validate_field("#" + SelfID, options)) {
                        if (options.callback_success) 
                            options.callback_success(this);
                    }
                    else {
                        if (options.callback_failure) 
                            options.callback_failure(this);
                    }
                });
                jQuery(this).find('input').bind('focus keypress click', function(){
                    jQuery("#" + SelfID).next('.' + options['error_message_class']).remove();
                    jQuery("#" + SelfID).parents("." + options['error_container_class']).removeClass('error');
                });
            }
            else {
                jQuery(this).bind('blur', function(){
                    validate_field(this);
                });
                jQuery(this).bind('focus keypress', function(){
                    jQuery(this).next('.' + options['error_message_class']).fadeOut("fast", function(){
                        jQuery(this).remove();
                    });
    				jQuery(this).parents("." + options['error_container_class']).removeClass('error');
                });
            }
        }
        jQuery(this).parents("form").submit(function(){

            if (validate_field('#' + SelfID)) {
				
				jQuery('.' + options['error_message_class']).remove();
				jQuery('.' + options['error_container_class']).removeClass('error');
			
                return true;
			}
            else 
                return false;
        });
        function validate_field(id){
            var self = jQuery(id).attr("id");
            var expression = 'function Validate(){' + options['expression'].replace(/VAL/g, 'jQuery(\'#' + self + '\').val()') + '} Validate()';
            var validation_state = eval(expression);
            if (!validation_state) {
                if (jQuery(id).next('.' + options['error_message_class']).length == 0) {
                    jQuery(id).after('<span class="' + options['error_message_class'] + '">' + options['message'] + '</span>');
					jQuery(id).parents("div .clearfix").addClass("error")
                }
                if (ValidationErrors[FormID].join("|").search(id) == -1) 
                    ValidationErrors[FormID].push(id);
                return false;
            }
            else {
                for (var i = 0; i < ValidationErrors[FormID].length; i++) {
                    if (ValidationErrors[FormID][i] == id) 
                        ValidationErrors[FormID].splice(i, 1);
                }
                return true;
            }
        }
    };
    jQuery.fn.validated = function(callback){
        jQuery(this).each(function(){
            if (this.tagName == "FORM") {
                jQuery(this).submit(function(){
                    if (ValidationErrors[jQuery(this).attr("id")].length == 0) 
                        callback();
					return false;
                });
            }
        });
    };
})(jQuery);
