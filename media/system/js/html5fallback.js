jQuery(document).ready(function() {
    	if (!("placeholder" in document.createElement("input"))){
  		jQuery('[placeholder]').focus(function() {
		  var input = jQuery(this);
		  if (input.hasClass('placeholder')) {
		    input.val('');
		    input.removeClass('placeholder');
		  }
		}).blur(function() {
		  var input = jQuery(this);
		  if (input.val() == '') {
		    input.addClass('placeholder');
		    input.val(input.attr('placeholder'));
		  }
		})
		.blur()
		.parents('form').submit(function() {
		    jQuery(this).find('[placeholder]').each(function() {
		      var input = jQuery(this);
		      if (input.hasClass('placeholder'))input.val('');
		    });
		});
		jQuery(window).unload(function() {
		    $('[placeholder]').val('');
		});
	}
});
