!(function($){
    "use strict";
    $(document).ready(function(){
        var ajaxUrl = document.getElementById('ajax-validation').getAttribute('data-url');

        // check if username is already in database
        $('#jform_username').change(function(){
            var name = $(this).val();
            if(name.length){
                $.ajax({
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
        $('#jform_email1').change(function(){
            var mail = $(this).val();
            if(mail.length) {
                $.ajax({
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
        $('#jform_email2').change(function(){
            var mail1 = $('#jform_email1').val();
            var mail2 = $('#jform_email2').val();
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
        $('#jform_password2').change(function(){
            var pass1 = $('#jform_password1').val();
            var pass2 = $('#jform_password2').val();
            if(pass1.length && pass2.length){
                if(pass1 != pass2){
                    var message = {
                        'error' : [ Joomla.JText._('COM_USERS_FIELD_RESET_PASSWORD1_MESSAGE')]
                    };
                    Joomla.renderMessages(message);
                }
            }
        });
    });
})(jQuery);


