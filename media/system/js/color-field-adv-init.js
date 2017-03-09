jQuery(document).ready(function ($){
	// Array of rendered color-pickers
    var $miniColors = [],
		lineNum, $input, format;


    $('.minicolors').each(function () {
        $miniColors.push($(this).minicolors({
            control: $(this).attr('data-control') || 'hue',
            format: $(this).attr('data-validate') === 'color'
                ? 'hex'
                : ($(this).attr('data-format') === 'rgba'
                    ? 'rgb'
                    : $(this).attr('data-format'))
                || 'hex',
            keywords: $(this).attr('data-keywords') || '',
            opacity: $(this).attr('data-format') === 'rgba',
            position: $(this).attr('data-position') || 'default',
            theme: 'bootstrap'
        }));
    });

    $('.color-picker-group .color-format-btn').each(function(){
        $(this).click(function(){
        	// Get the line number from button format number
        	lineNum = Math.floor($('.color-format-btn').index(this) / 3);
        	format = $(this).data('format');

        	// Radio input implementation
            $(this).parent().find('.color-format-btn').removeClass('active');
            $(this).addClass('active');

            $input = $(this).parent().parent().find("input.minicolors-input");
            $input.removeClass("hex").removeClass("rgb").removeClass("rgba").addClass(format);

            // Replace input value
            switch(format){
				case 'rgba':
					$input.val($miniColors[lineNum].minicolors('rgbaString'));
					break;
				case 'rgb':
					$input.val($miniColors[lineNum].minicolors('rgbString'));
					break;
				case 'hex':
                    $input.val(rgbToHex($input.val()));
					break;
			}

            $miniColors[lineNum].minicolors('settings', {
                format: format == 'rgba' ? 'rgb' : format,
				opacity: format == 'rgba'
            })
        })
    });

    // Convert number to hex
    function componentToHex(c) {
        var hex = c.toString(16);
        return hex.length == 1 ? "0" + hex : hex;
    }

    function rgbToHex(rgb) {
		rgb = rgb.indexOf("rgba") === 0 ? rgb.slice(5) : rgb.slice(4);
    	rgb = rgb.replace(")", "").split(",");

        return "#" + componentToHex(parseInt(rgb[0])) + componentToHex(parseInt(rgb[1])) + componentToHex(parseInt(rgb[2]));
    }

});
