window.onload = function() {
    var ajaxUrl = document.getElementById('ajax-validation').getAttribute('data-url');

    // check if username is already in database
    jQuery('#jform_username').change(function(){
        var name = jQuery(this).val();
        if(name.length){
            jQuery.ajax({
                type: 'POST',
                url: ajaxUrl + '&username=' + name
            }).done(function(data){
                if(data.success){
                    var message = {
                        'error' : [data.message]
                    };
                    Joomla.renderMessages(message);
                }
            });
        }
    });
    // check if email is already in database
    jQuery('#jform_email1').change(function(){
        var mail = jQuery(this).val();
        if(mail.length) {
            jQuery.ajax({
                type: 'POST',
                url:  ajaxUrl + '&email=' + mail
            }).done(function (data) {
                if (data.success) {
                    var message = {
                        'error': [data.message]
                    };
                    Joomla.renderMessages(message);
                }
            });
        }
    });
    //check if emails are equal
    jQuery('#jform_email2').change(function(){
        var mail1 = jQuery('#jform_email1').val();
        var mail2 = jQuery('#jform_email2').val();
        if(mail1.length && mail2.length){
            if(mail1 != mail2){
                var message = {
                    'error' : [ Joomla.JText._('COM_USERS_PROFILE_EMAIL2_MESSAGE') ]
                };
                Joomla.renderMessages(message);
            }
        }
    });
    //check if password are equal
    jQuery('#jform_password2').change(function(){
        var pass1 = jQuery('#jform_password1').val();
        var pass2 = jQuery('#jform_password2').val();
        if(pass1.length && pass2.length){
            if(pass1 != pass2){
                var message = {
                    'error' : [ Joomla.JText._('COM_USERS_FIELD_RESET_PASSWORD1_MESSAGE')]
                };
                Joomla.renderMessages(message);
            }
        }
    });
}
