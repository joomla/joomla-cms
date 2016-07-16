/*********************************************************************
 ************************** Initialize *******************************
 *********************************************************************/
document.onreadystatechange = function () {
	if (document.readyState == "interactive") {

		JoomlaCalendar.setup = function (params, el) {

			// Set the button (caller) and the input field elements
			params["button"]     = el;
			params["inputField"] = document.getElementById(params["inputField"]);

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
				if (update && p.singleClick && cal.dateClicked)
					cal.callCloseHandler();
			}

			// Initialize the calendar
			var dateEl = params.inputField;
			var dateFmt = params.ifFormat; //params.inputField ? params.ifFormat : params.daFormat;
			var cal = window.jCalendar;

			// Get the date from the input
			if (dateEl) {
				params.date = Date.parseDate(params.inputField.value, params.ifFormat);
			}

			// Create the calendar
			window.jCalendar = cal = new JoomlaCalendar(
				params.firstDay,
				null,
				params.onSelect || onSelect,
				null,
				params
			);
			cal.params = params;
			cal.setDateFormat(dateFmt);
			cal.create(params.inputField);
			cal.refresh();
			cal.show();
			return cal;
		};

		// Get all the calendar fields
		var calendars = document.getElementsByClassName("field-calendar");

		// Loop to initialize them all
		for (index = 0, len = calendars.length; index < len; ++index) {
			var btn = calendars[index].getElementsByTagName("button");
			JoomlaCalendar.addEvent(btn[0], "click", function () {

				var params = {
					inputField   : this.getAttribute("data-inputfield") ? this.getAttribute("data-inputfield") :
						this.parentNode.getElementsByTagName('INPUT')[0],                                                                       // The related input
					ifFormat     : this.getAttribute("data-ifformat") ? this.getAttribute("data-ifformat") : "%Y-%m-%d %H:%M:%S",               // The date format
					button       : this.getAttribute("data-button"),                                                                            // The button associated
					firstDay     : this.getAttribute("data-firstday") ? parseInt(this.getAttribute("data-firstday")) : 0,                       // First day (from translated strings) integer 0 = Sun
					todayBtn     : (parseInt(this.getAttribute("data-today_btn")) == 0) ? false : true,                                         // Enable today button?
					onlyMonths   : (parseInt(this.getAttribute("data-only_months_nav")) == 1) ? true : false,                                   // Month and year in one line?
					hiliteToday  : (parseInt(this.getAttribute("data-hilite_today")) != 1) ? false : true,                                      // Highlight today?
					minYear      : this.getAttribute("data-min_year") ? parseInt(this.getAttribute("data-min_year")) : 1970,                    // Minimum year
					maxYear      : this.getAttribute("data-max_year") ? parseInt(this.getAttribute("data-max_year")) : 2050,                    // Maximum year
					weekNumbers  : (parseInt(this.getAttribute("data-week_numbers")) == 1) ? true : false,                                      // Display week numbers column?
					showsTime    : (parseInt(this.getAttribute("data-shows_time")) == 1) ? true : false,                                        // Enable time picker? Make sure that the date format also INCLUDES time
					time24       : (parseInt(this.getAttribute("data-time_24")) == 24) ? true : false,                                          // Use 24 hour format?
					showOthers   : (parseInt(this.getAttribute("data-show_others")) == 0) ? false : true,                                       // Show days form the month before and after?

					stringDN     : this.getAttribute("data-weekdays_full") ? this.getAttribute("data-weekdays_full").split('_') :
						["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday","Sunday"],                                      // Translated full day names
					stringSDN    : this.getAttribute("data-weekdays_short") ? this.getAttribute("data-weekdays_short").split('_') :
						["Sun","Mon","Tue","Wed","Thu","Fri","Sat","Sun"],                                                                      // Translated short day names
					stringMN     : this.getAttribute("data-months_long") ? this.getAttribute("data-months_long").split('_') :
						["January","February","March","April","May","June","July","August","September","October","November","December"],        // Translated full month names
					stringSMN    : this.getAttribute("data-months_short") ? this.getAttribute("data-months_short").split('_') :
						["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],                                              // Translated short month names
					stringTODAY  : this.getAttribute("data-today_trans") ? this.getAttribute("data-today_trans") : "Today",                     // Translated string for Today
					stringWEEKEND: this.getAttribute("data-weekend") ? this.getAttribute("data-weekend").split(',').map(Number) :
						[0,6],                                                                                                                  // integers comma separated 0,6
					stringWK     : this.getAttribute("data-wk") ? this.getAttribute("data-wk") : "wk",                                          // Translated string for wk
					stringTIME   : this.getAttribute("data-time") ? this.getAttribute("data-time") : "Time:",                                   // Translated string for Time:
					stringTIMEAM : this.getAttribute("data-time_am") ? this.getAttribute("data-time_am") : "AM",                                // Translated string for AM
					stringTIMEPM : this.getAttribute("data-time_pm") ? this.getAttribute("data-time_pm") : "PM",                                // Translated string for PM
				};

				JoomlaCalendar.setup(params, this);
			})
		}
	}
};
