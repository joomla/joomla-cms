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
 *      stringLocalWEEKEND          The array with the day numbers signify weekend eg: [0,6] refers to (Sun,Sat)
 *      stringLocalSDN              The array with the short day names eg: ["Sun", "Mon" ... ]
 *
 *      Also needed a function that will bind on form submit and will convert local to gregorian.
 **/
document.onreadystatechange = function () {
	if (document.readyState == "interactive") {

		JoomlaCalendar.setup = function (elem) {

			var element = elem.getElementsByTagName("button")[0];

			Date.stringDN =element.getAttribute("data-weekdays_full") ? element.getAttribute("data-weekdays_full").split('_') :
				["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"];                                      // Translated full day names
			Date.stringSDN = element.getAttribute("data-weekdays_short") ? element.getAttribute("data-weekdays_short").split('_') :
				["Sun","Mon","Tue","Wed","Thu","Fri","Sat","Sun"];                                                                      // Translated short day names
			Date.stringMN  = element.getAttribute("data-months_long") ? element.getAttribute("data-months_long").split('_') :
				["January","February","March","April","May","June","July","August","September","October","November","December"];        // Translated full month names
			Date.stringSMN = element.getAttribute("data-months_short") ? element.getAttribute("data-months_short").split('_') :
				["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];                                              // Translated short month names
			Date.stringTODAY  = element.getAttribute("data-today_trans") ? element.getAttribute("data-today_trans") : "Today";               // Translated string for Today
			Date.stringWEEKEND = element.getAttribute("data-weekend") ? element.getAttribute("data-weekend").split(',').map(Number) :
				[0,6];                                                                                                                  // integers comma separated 0,6
			Date.stringWK   = element.getAttribute("data-wk") ? element.getAttribute("data-wk") : "wk";                                    // Translated string for wk
			Date.stringTIME  = element.getAttribute("data-time") ? element.getAttribute("data-time") : "Time:";                             // Translated string for Time:
			Date.stringTIMEAM = element.getAttribute("data-time_am") ? element.getAttribute("data-time_am") : "AM";                          // Translated string for AM
			Date.stringTIMEPM = element.getAttribute("data-time_pm") ? element.getAttribute("data-time_pm") : "PM";                          // Translated string for PM

			Date.localStringDN     = element.getAttribute("data-weekdays_full") ? element.getAttribute("data-weekdays_full").split('_') :
				["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"];                                      // Translated full day names
			Date.localStringSDN    = element.getAttribute("data-weekdays_short") ? element.getAttribute("data-weekdays_short").split('_') :
				["Sun","Mon","Tue","Wed","Thu","Fri","Sat","Sun"];                                                                      // Translated short day names
			Date.localStringMN     = element.getAttribute("data-months_long") ? element.getAttribute("data-months_long").split('_') :
				["January","February","March","April","May","June","July","August","September","October","November","December"];        // Translated full month names
			Date.localStringSMN    = element.getAttribute("data-months_short") ? element.getAttribute("data-months_short").split('_') :
				["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];                                              // Translated short month names
			Date.localStringTODAY  = element.getAttribute("data-today_trans") ? element.getAttribute("data-today_trans") : "Today";               // Translated string for Today
			Date.localStringWEEKEND= element.getAttribute("data-weekend") ? element.getAttribute("data-weekend").split(',').map(Number) :
				[0,6];                                                                                                                  // integers comma separated 0,6
			Date.localStringWK     = element.getAttribute("data-wk") ? element.getAttribute("data-wk") : "wk";                                    // Translated string for wk
			Date.localStringTIME   = element.getAttribute("data-time") ? element.getAttribute("data-time") : "Time:";                             // Translated string for Time:
			Date.localStringTIMEAM = element.getAttribute("data-time_am") ? element.getAttribute("data-time_am") : "AM";                          // Translated string for AM
			Date.localStringTIMEPM = element.getAttribute("data-time_pm") ? element.getAttribute("data-time_pm") : "PM";                          // Translated string for PM
			Date.localLangNumbers  = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9"];

			JoomlaCalendar.addEvent(element, "click", function () {

				var params = {
					inputField   : element.parentNode.getElementsByTagName('INPUT')[0],                                                         // The related input
					dateFormat   : element.getAttribute("data-dayformat") ? element.getAttribute("data-dayformat") : "%Y-%m-%d %H:%M:%S",             // The date format
					button       : element,                                                                                                     // The button associated
					firstDayOfWeek     : element.getAttribute("data-firstday") ? parseInt(element.getAttribute("data-firstday")) : 0,                 // First day (from translated strings) integer 0 = Sun
					showsTodayBtn     : false, //(parseInt(element.getAttribute("data-today_btn")) == 0) ? false : true,                                      // Enable today button?
					compressedHeader   : true, //(parseInt(element.getAttribute("data-only_months_nav")) == 1) ? true : false,                                // Month and year in one line?
					minYear      : element.getAttribute("data-min_year") ? parseInt(element.getAttribute("data-min_year")) : 1970,              // Minimum year
					maxYear      : element.getAttribute("data-max_year") ? parseInt(element.getAttribute("data-max_year")) : 2050,              // Maximum year
					weekNumbers  : (parseInt(element.getAttribute("data-week_numbers")) == 1) ? true : false,                                   // Display week numbers column?
					showsTime    : true, //(parseInt(element.getAttribute("data-shows_time")) == 1) ? true : false,                             // Enable time picker? Make sure that the date format also INCLUDES time
					time24       : (parseInt(element.getAttribute("data-time_24")) == 24) ? true : false,                                       // Use 24 hour format?
					showsOthers   : (parseInt(element.getAttribute("data-show_others")) == 0) ? false : true,                                   // Show days form the month before and after?
					/**
					 * Support for different calendars, e.g.: jalali
					 */
					dateType: 'gregorian'
				};

				// Initialize only if the button and input field are set
				if (!(params.inputField || params.button)) {
					console.log("Calendar.setup:\n  Nothing to setup (no fields found). Please check your code");
					return false;
				}

				// Initialize the calendar

				//
				// // Get the date from the input
				// if (dateEl) {
				// 	params.dateStr = Date.parseDate(element.parentNode.getElementsByTagName('INPUT')[0].value, params.dateFormat, params.dateType);
				// }

				console.log(params.dateStr);
				// Create the calendar

				window.jCalendar = new JoomlaCalendar(params);
				jCalendar.params = params;

				jCalendar.setDateFormat(params.dateFormat);
				jCalendar.create(params.inputField);
				jCalendar.date = params.dateStr;
				console.log(cal.date);
				jCalendar.refresh();
				cal.show();
				return cal;
			})
		};

		// Get all the calendar fields
		var calendars = document.getElementsByClassName("field-calendar");

		// Loop to initialize them all
		for (var index = 0, len = calendars.length; index < len; ++index) {
			JoomlaCalendar.setup(calendars[index]);
		}
	}
};
