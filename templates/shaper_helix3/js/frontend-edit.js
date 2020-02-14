/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2015 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/


jQuery(function($) {

    $(document).ready(function() {

        // Turn radios into btn-group
        $('.radio.btn-group label').addClass('btn btn-default');
        $(".btn-group label:not(.active)").click(function()
        {
            var label = $(this);
            var input = $('#' + label.attr('for'));

            if (!input.prop('checked')) {
                label.closest('.btn-group').find("label").removeClass('active btn-success btn-danger btn-primary');
                if (input.val() == '') {
                    label.addClass('active btn-primary');
                } else if (input.val() == 0) {
                    label.addClass('active btn-danger');
                } else {
                    label.addClass('active btn-success');
                }
                input.prop('checked', true);
            }
        });
        
        $(".btn-group input[checked=checked]").each(function()
        {
            if ($(this).val() == '') {
                $("label[for=" + $(this).attr('id') + "]").addClass('active btn-primary');
            } else if ($(this).val() == 0) {
                $("label[for=" + $(this).attr('id') + "]").addClass('active btn-danger');
            } else {
                $("label[for=" + $(this).attr('id') + "]").addClass('active btn-success');
            }
        });
    })
});


if (typeof jQuery != 'undefined' && typeof MooTools != 'undefined' ) { 
    
    jQuery(function($) {
        $(document).ready(function(){
            $('.carousel').each(function(index, element) {
                $(this)[index].slide = null;
            });
        });

        window.addEvent('domready',function() {
            Element.prototype.hide = function() {};
        });

    });
}