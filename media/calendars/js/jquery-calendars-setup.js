var gc = jQuery.calendars.instance();

function setElToDate(id,jd)
{
    var calendar = jQuery('#'+id).calendarsPicker('option','calendar');
    var newdate = calendar.fromJD(jd);
    console.log(newdate);

    jQuery('#'+id).calendarsPicker('setDate',newdate);
}
