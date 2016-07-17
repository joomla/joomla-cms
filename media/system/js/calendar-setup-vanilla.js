/*********************************************************************
 ************************** Initialize *******************************
 *********************************************************************/
/**
 *      Support for calendars other than gregorian should be done in this file ONLY!
 *
 *      Make sure you initialise those functions:
 *      setLocalCalFullYear()       Sets the year in the local calendar
 *      setLocalCalMonth()          Sets the month in the local calendar
 *      setLocalCalDate()           Sets the day in the local calendar
 *
 *      getLocalCalMonth()          Gets the month in the local calendar
 *      getLocalCalWeekNumber()     Gets the week number in the local calendar
 *      getLocalCalDate()           Gets the day in the local calendar
 *      getLocalCalDayOfYear()      Gets the day of the year in the local calendar
 *      getLocalCalMonthDays()      Gets the number of days for the month
 *
 *      Date.localCal_MD = new Array(31,28,31,30,31,30,31,31,30,31,30,31);  The array with the days per month
 *
 *      Also you need to provide an override for:
 *      Date.parseDate()            The string parser that converts it to date
 *      gregorianToLocal            Converts a gregorian calendar date to the local calendar
 *      localToGregorian            Converts a local calendar date to gregorian calendar
 *
 *      dateType: 'jalali'          The name of the local calendar
 *      localLangNumbers: [0-9]     The array with the translated numbers 0 to 9
 *
 *      Also needed a function that will bind on form submit and will convert local to gregorian.
 **/
document.onreadystatechange = function () {
	if (document.readyState == "interactive") {

		JoomlaCalendar.setup = function (elem) {

			var element = elem.getElementsByTagName("button")[0];
			JoomlaCalendar.addEvent(element, "click", function () {

				var params = {
					inputField   : element.parentNode.getElementsByTagName('INPUT')[0],                                                         // The related input
					ifFormat     : element.getAttribute("data-ifformat") ? element.getAttribute("data-ifformat") : "%Y-%m-%d %H:%M:%S",         // The date format
					button       : element,                                                                                                     // The button associated
					firstDay     : element.getAttribute("data-firstday") ? parseInt(element.getAttribute("data-firstday")) : 0,                 // First day (from translated strings) integer 0 = Sun
					todayBtn     : (parseInt(element.getAttribute("data-today_btn")) == 0) ? false : true,                                      // Enable today button?
					onlyMonths   : (parseInt(element.getAttribute("data-only_months_nav")) == 1) ? true : false,                                // Month and year in one line?
					hiliteToday  : (parseInt(element.getAttribute("data-hilite_today")) != 1) ? false : true,                                   // Highlight today?
					minYear      : element.getAttribute("data-min_year") ? parseInt(element.getAttribute("data-min_year")) : 1970,              // Minimum year
					maxYear      : element.getAttribute("data-max_year") ? parseInt(element.getAttribute("data-max_year")) : 2050,              // Maximum year
					weekNumbers  : (parseInt(element.getAttribute("data-week_numbers")) == 1) ? true : false,                                   // Display week numbers column?
					showsTime    : (parseInt(element.getAttribute("data-shows_time")) == 1) ? true : false,                                     // Enable time picker? Make sure that the date format also INCLUDES time
					time24       : (parseInt(element.getAttribute("data-time_24")) == 24) ? true : false,                                       // Use 24 hour format?
					showOthers   : (parseInt(element.getAttribute("data-show_others")) == 0) ? false : true,                                    // Show days form the month before and after?

					stringDN     : element.getAttribute("data-weekdays_full") ? element.getAttribute("data-weekdays_full").split('_') :
						["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],                                      // Translated full day names
					stringSDN    : element.getAttribute("data-weekdays_short") ? element.getAttribute("data-weekdays_short").split('_') :
						["Sun","Mon","Tue","Wed","Thu","Fri","Sat","Sun"],                                                                      // Translated short day names
					stringMN     : element.getAttribute("data-months_long") ? element.getAttribute("data-months_long").split('_') :
						["January","February","March","April","May","June","July","August","September","October","November","December"],        // Translated full month names
					stringSMN    : element.getAttribute("data-months_short") ? element.getAttribute("data-months_short").split('_') :
						["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],                                              // Translated short month names
					stringTODAY  : element.getAttribute("data-today_trans") ? element.getAttribute("data-today_trans") : "Today",               // Translated string for Today
					stringWEEKEND: element.getAttribute("data-weekend") ? element.getAttribute("data-weekend").split(',').map(Number) :
						[0,6],                                                                                                                  // integers comma separated 0,6
					stringWK     : element.getAttribute("data-wk") ? element.getAttribute("data-wk") : "wk",                                    // Translated string for wk
					stringTIME   : element.getAttribute("data-time") ? element.getAttribute("data-time") : "Time:",                             // Translated string for Time:
					stringTIMEAM : element.getAttribute("data-time_am") ? element.getAttribute("data-time_am") : "AM",                          // Translated string for AM
					stringTIMEPM : element.getAttribute("data-time_pm") ? element.getAttribute("data-time_pm") : "PM",                          // Translated string for PM
					/**
					 * Support for different calendars, e.g.: jalali
					 */
					dateType: 'gregorian'
					//localLangNumbers  : ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"]
				};

				// Initialize only if the button and input field are set
				if (!(params.inputField || params.button)) {
					console.log("Calendar.setup:\n  Nothing to setup (no fields found). Please check your code");
					return false;
				}

				// Method to set the value for the input field
				function onSelect(cal) {
					var p = cal.params;
					var update = cal.dateClicked;
					if (p.inputField) {
						p.inputField.value = cal.date.print(p.ifFormat);
						if (typeof p.inputField.onchange == "function")
							p.inputField.onchange();
					}
					if (update && typeof p.onUpdate == "function")
						p.onUpdate(cal);
					if (update && cal.dateClicked)
						cal.callCloseHandler();
				}

				// Initialize the calendar
				var dateEl = params.inputField;
				var dateFmt = params.ifFormat;
				var cal = window.jCalendar;

				// Get the date from the input
				if (dateEl) {
					params.dateStr = Date.parseDate(params.inputField.value, params.ifFormat);
				}

				// Create the calendar
				window.jCalendar = cal = new JoomlaCalendar(onSelect, null, params);
				cal.params = params;
				cal.setDateFormat(dateFmt);
				cal.create(params.inputField);
				cal.refresh();
				cal.show();
				return cal;
			})
		};

		// Get all the calendar fields
		var calendars = document.getElementsByClassName("field-calendar");

		// Loop to initialize them all
		for (index = 0, len = calendars.length; index < len; ++index) {
			JoomlaCalendar.setup(calendars[index]);
		}
	}
};
