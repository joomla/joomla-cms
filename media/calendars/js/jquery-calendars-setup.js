if (typeof(gc) == 'undefined')
    gc = jQuery.calendars.instance('gregorian');

function setElToDate(id,jd)
{
    var calendar = jQuery('#'+id).calendarsPicker('option','calendar');
    var newdate = calendar.fromJD(jd);
    console.log(newdate);

    jQuery('#'+id).calendarsPicker('setDate',newdate);
}

/**
 * need to override the submitform as submitbutton may already be overridden
 */

Joomla.submitform = function(task, form) {
    
    // Convert the dates back to gregorian before submitting the form.
    jQuery.each(jQuery('.hascalpicker'),function(index,object)
    {
        var calendar = jQuery(object).calendarsPicker('option','calendar');
        var date = jQuery(object).calendarsPicker('getDate');

        // The calendar picker returns an array of dates we want the first.
        if (Array.isArray(date))
            date = date[0];

        // Convert to Julian
        var jd = calendar.toJD(date.year(),date.month(),date.day());

        // Convert to gregorian using the preloaded gregorian calendar
        var gd = gc.fromJD(jd);
        jQuery(object).val(gc.formatDate('yyyy-mm-dd',gd));
    });


    if (typeof(form) === 'undefined') {
        form = document.getElementById('adminForm');
    }

    if (typeof(task) !== 'undefined' && task !== "") {
        form.task.value = task;
    }

    // Submit the form.
    if (typeof form.onsubmit == 'function') {
        form.onsubmit();
    }
    if (typeof form.fireEvent == "function") {
        form.fireEvent('submit');
    }
    form.submit();
};
