(function($) {
	var instances = [];
	var methods = {
		init: function( options ) {
			return this.each( function () {
				var $this = this;
				var cbdatepicker = $( $this ).data( 'cbdatepicker' );

				if ( cbdatepicker ) {
					return; // cbdatepicker is already bound; so no need to rebind below
				}

				cbdatepicker = {};
				cbdatepicker.options = ( typeof options != 'undefined' ? options : {} );
				cbdatepicker.defaults = $.fn.cbdatepicker.defaults;
				cbdatepicker.settings = $.extend( true, {}, cbdatepicker.defaults, cbdatepicker.options );
				cbdatepicker.strings = $.extend( true, {}, $.datepicker.regional[''], cbdatepicker.settings.strings );
				cbdatepicker.element = $( $this );

				if ( cbdatepicker.settings.useData ) {
					$.each( cbdatepicker.defaults, function( key, value ) {
						if ( ( key != 'init' ) && ( key != 'useData' ) ) {
							// Dash Separated:
							var dataValue = cbdatepicker.element.data( 'cbdatepicker' + key.charAt( 0 ).toUpperCase() + key.slice( 1 ) );

							if ( typeof dataValue != 'undefined' ) {
								cbdatepicker.settings[key] = dataValue;
							} else {
								// No Separater:
								dataValue = cbdatepicker.element.data( 'cbdatepicker' + key.charAt( 0 ).toUpperCase() + key.slice( 1 ).toLowerCase() );

								if ( typeof dataValue != 'undefined' ) {
									cbdatepicker.settings[key] = dataValue;
								}
							}
						}
					});
				}

				cbdatepicker.element.triggerHandler( 'cbdatepicker.init.before', [cbdatepicker] );

				if ( ! cbdatepicker.settings.init ) {
					return;
				}

				var currentYear = new Date().getFullYear();

				if ( cbdatepicker.settings.minYear ) {
					if ( typeof cbdatepicker.settings.minYear == 'string' ) {
						var minYearRelative = cbdatepicker.settings.minYear.substr( 0, 1 );
						var minYearAdjust = cbdatepicker.settings.minYear.substr( 1 );

						if ( minYearRelative == '+' ) {
							cbdatepicker.settings.minYear = ( currentYear + parseInt( minYearAdjust ) );
						} else if ( minYearRelative == '-' ) {
							cbdatepicker.settings.minYear = ( currentYear - parseInt( minYearAdjust ) );
						}
					}
				} else {
					cbdatepicker.settings.minYear = ( currentYear - 99 );
				}

				if ( cbdatepicker.settings.maxYear ) {
					if ( typeof cbdatepicker.settings.maxYear == 'string' ) {
						var maxYearRelative = cbdatepicker.settings.maxYear.substr( 0, 1 );
						var maxYearAdjust = cbdatepicker.settings.maxYear.substr( 1 );

						if ( maxYearRelative == '+' ) {
							cbdatepicker.settings.maxYear = ( currentYear + parseInt( maxYearAdjust ) );
						} else if ( maxYearRelative == '-' ) {
							cbdatepicker.settings.maxYear = ( currentYear - parseInt( maxYearAdjust ) );
						}
					}
				} else {
					cbdatepicker.settings.maxYear = ( currentYear + 99 );
				}

				if ( ( cbdatepicker.settings.calendarType == 2 ) || ( cbdatepicker.settings.calendarType == 3 ) ) {
					var momentCache	=	null;

					if ( typeof moment != 'undefined' ) {
						momentCache = moment.locale();

						moment.locale( Math.random(), {
							months: cbdatepicker.strings.monthNames,
							monthsShort: cbdatepicker.strings.monthNamesShort,
							weekdays: cbdatepicker.strings.dayNames,
							weekdaysShort: cbdatepicker.strings.dayNamesShort,
							weekdaysMin: cbdatepicker.strings.dayNamesMin
						});
					}

					cbdatepicker.combodate = cbdatepicker.element.combodate({
						format: cbdatepicker.settings.format,
						template: cbdatepicker.settings.template,
						minYear: cbdatepicker.settings.minYear,
						maxYear: cbdatepicker.settings.maxYear,
						firstItem: cbdatepicker.settings.firstItem,
						smartDays: true,
						yearDescending: false,
						customClass: cbdatepicker.settings.customClass
					});

					if ( momentCache ) {
						moment.locale( momentCache );
					}

					cbdatepicker.element.siblings( '.combodate' ).children().on( 'change', function( event ) {
						var selected = $( this ).val();

						if ( selected !== '' ) {
							$( this ).siblings().each( function() {
								if ( ! $( this ).val() ) {
									var option = null;

									if ( $( this ).hasClass( 'year' ) ) {
										option = $( this ).children( 'option[value="' + currentYear + '"]' ).val();
									}

									if ( ! option ) {
										option = $( this ).children( 'option[value!=""]:first' ).val();
									}

									if ( option ) {
										$( this ).val( option );
									}
								}
							});
						} else{
							$( this ).siblings().each( function() {
								$( this ).val( '' );
							});
						}

						cbdatepicker.element.triggerHandler( 'cbdatepicker.select', [cbdatepicker, cbdatepicker.element.combodate( 'getValue' )] );
					});
				}

				if ( ( cbdatepicker.settings.calendarType == 1 ) || cbdatepicker.settings.addPopup ) {
					if ( cbdatepicker.settings.showTime ) {
						cbdatepicker.datetimepicker = cbdatepicker.element.datetimepicker({
							stepMinute: 5,
							stepSecond: 1,
							timeFormat: cbdatepicker.settings.timeFormat,
							amNames: cbdatepicker.strings.amNames,
							pmNames: cbdatepicker.strings.pmNames,
							timeOnlyTitle: cbdatepicker.strings.timeOnlyTitle,
							timeText: cbdatepicker.strings.timeText,
							hourText: cbdatepicker.strings.hourText,
							minuteText: cbdatepicker.strings.minuteText,
							secondText: cbdatepicker.strings.secondText,
							millisecText: cbdatepicker.strings.millisecText,
							microsecText: cbdatepicker.strings.microsecText,
							timezoneText: cbdatepicker.strings.timezoneText,
							onSelect: function( selected, popup ) {
								if ( cbdatepicker.settings.addPopup ) {
									cbdatepicker.element.combodate( 'setValue', selected );
								} else {
									cbdatepicker.element.change();
								}

								cbdatepicker.element.triggerHandler( 'cbdatepicker.select', [cbdatepicker, selected] );
							},
							yearRange: cbdatepicker.settings.minYear + ':' + cbdatepicker.settings.maxYear,
							isRTL: cbdatepicker.settings.isRTL,
							firstDay: cbdatepicker.settings.firstDay,
							changeMonth: true,
							changeYear: true,
							showButtonPanel: false,
							dateFormat: cbdatepicker.settings.dateFormat,
							dayNames: cbdatepicker.strings.dayNames,
							dayNamesMin: cbdatepicker.strings.dayNamesMin,
							dayNamesShort: cbdatepicker.strings.dayNamesShort,
							monthNames: cbdatepicker.strings.monthNames,
							monthNamesShort: cbdatepicker.strings.monthNamesShort,
							prevText: cbdatepicker.strings.prevText,
							nextText: cbdatepicker.strings.nextText,
							currentText: cbdatepicker.strings.currentText,
							closeText: cbdatepicker.strings.closeText
						});
					} else {
						cbdatepicker.datepicker = cbdatepicker.element.datepicker({
							onSelect: function( selected, popup ) {
								if ( cbdatepicker.settings.addPopup ) {
									cbdatepicker.element.combodate( 'setValue', selected );
								} else {
									cbdatepicker.element.change();
								}

								cbdatepicker.element.triggerHandler( 'cbdatepicker.select', [cbdatepicker, selected] );
							},
							yearRange: cbdatepicker.settings.minYear + ':' + cbdatepicker.settings.maxYear,
							isRTL: cbdatepicker.settings.isRTL,
							firstDay: cbdatepicker.settings.firstDay,
							changeMonth: true,
							changeYear: true,
							showButtonPanel: false,
							dateFormat: cbdatepicker.settings.dateFormat,
							dayNames: cbdatepicker.strings.dayNames,
							dayNamesMin: cbdatepicker.strings.dayNamesMin,
							dayNamesShort: cbdatepicker.strings.dayNamesShort,
							monthNames: cbdatepicker.strings.monthNames,
							monthNamesShort: cbdatepicker.strings.monthNamesShort,
							prevText: cbdatepicker.strings.prevText,
							nextText: cbdatepicker.strings.nextText,
							currentText: cbdatepicker.strings.currentText,
							closeText: cbdatepicker.strings.closeText
						});
					}
				}

				cbdatepicker.calendarHandler = function( e ) {
					e.preventDefault();

					var widget = cbdatepicker.element.datepicker( 'widget' );

					if ( widget.not( ':visible' ).length ) {
						cbdatepicker.element.datepicker( 'show' );

						widget.position({
							my: 'left top+20',
							at: 'left top',
							of: $( this )
						});
					}

					cbdatepicker.element.triggerHandler( 'cbdatepicker.calendar', [cbdatepicker] );
				};

				cbdatepicker.element.siblings( '.hasCalendar' ).on( 'click', cbdatepicker.calendarHandler );

				// If the value has changed and we have a target we need to adjust the target with the utc offset applied as we always store in utc:
				cbdatepicker.changeHandler = function() {
					var target = $( this );
					var value = $( this ).val();

					if ( cbdatepicker.settings.target ) {
						target = $( cbdatepicker.settings.target );
					}

					if ( cbdatepicker.settings.offset && value ) {
						var date = moment( value );

						if ( date.isValid() ) {
							value = date.utc().utcOffset( String( cbdatepicker.settings.offset ) ).format( cbdatepicker.settings.format );
						}
					}

					target.val( value );
				};

				cbdatepicker.element.on( 'change', cbdatepicker.changeHandler );

				// Rebind the cbdatepicker element to pick up any data attribute modifications:
				cbdatepicker.element.on( 'rebind.cbdatepicker', function() {
					cbdatepicker.element.cbdatepicker( 'rebind' );
				});

				// If the cbdatepicker element is modified we need to rebuild it to ensure all our bindings are still ok:
				cbdatepicker.element.on( 'modified.cbdatepicker', function( e, oldId, newId, index ) {
					if ( oldId != newId ) {
						cbdatepicker.element.cbdatepicker( 'rebind' );
					}
				});

				// If the cbdatepicker is cloned we need to rebind it back:
				cbdatepicker.element.on( 'cloned.cbdatepicker', function() {
					$( this ).off( 'rebind.cbdatepicker' );
					$( this ).off( 'cloned.cbdatepicker' );
					$( this ).off( 'modified.cbdatepicker' );
					$( this ).removeData( 'cbdatepicker' );
					$( this ).removeData( 'combodate' );
					$( this ).removeData( 'datepicker' );
					$( this ).siblings( '.combodate' ).remove();
					$( this ).removeClass( 'hasDatepicker' );
					$( this ).siblings( '.hasCalendar' ).off( 'click', cbdatepicker.calendarHandler );
					$( this ).off( 'change', cbdatepicker.changeHandler );
					$( this ).cbdatepicker( cbdatepicker.options );
				});

				cbdatepicker.element.triggerHandler( 'cbdatepicker.init.after', [cbdatepicker] );

				// Bind the cbdatepicker to the element so it's reusable and chainable:
				cbdatepicker.element.data( 'cbdatepicker', cbdatepicker );

				// Add this instance to our instance array so we can keep track of our cbdatepicker instances:
				instances.push( cbdatepicker );
			});
		},
		rebind: function() {
			var cbdatepicker = $( this ).data( 'cbdatepicker' );

			if ( ! cbdatepicker ) {
				return this;
			}

			cbdatepicker.element.cbdatepicker( 'destroy' );
			cbdatepicker.element.cbdatepicker( cbdatepicker.options );

			return this;
		},
		destroy: function() {
			var cbdatepicker = $( this ).data( 'cbdatepicker' );

			if ( ! cbdatepicker ) {
				return this;
			}

			if ( cbdatepicker.combodate ) {
				cbdatepicker.element.combodate( 'destroy' );
			}

			if ( cbdatepicker.datetimepicker ) {
				cbdatepicker.element.datetimepicker( 'destroy' );
			}

			if ( cbdatepicker.datepicker ) {
				cbdatepicker.element.datepicker( 'destroy' );
			}

			cbdatepicker.element.siblings( '.hasCalendar' ).off( 'click', cbdatepicker.calendarHandler );
			cbdatepicker.element.off( 'change', cbdatepicker.changeHandler );
			cbdatepicker.element.off( 'rebind.cbdatepicker' );
			cbdatepicker.element.off( 'cloned.cbdatepicker' );
			cbdatepicker.element.off( 'modified.cbdatepicker' );

			$.each( instances, function( i, instance ) {
				if ( instance.element == cbdatepicker.element ) {
					instances.splice( i, 1 );

					return false;
				}

				return true;
			});

			cbdatepicker.element.removeData( 'cbdatepicker' );
			cbdatepicker.element.triggerHandler( 'cbdatepicker.destroyed', [cbdatepicker] );

			return this;
		},
		instances: function() {
			return instances;
		}
	};

	$.fn.cbdatepicker = function( options ) {
		if ( methods[options] ) {
			return methods[ options ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
		} else if ( ( typeof options === 'object' ) || ( ! options ) ) {
			return methods.init.apply( this, arguments );
		}

		return this;
	};

	$.fn.cbdatepicker.defaults = {
		init: true,
		useData: true,
		calendarType: 2,
		template: 'Y-m-d H:i:s',
		format: 'Y-m-d H:i:s',
		timeFormat: 'H:i:s',
		dateFormat: 'Y-m-d',
		minYear: '-99',
		maxYear: '+99',
		firstDay: 0,
		showTime: false,
		addPopup: false,
		isRTL: false,
		customClass: null,
		firstItem: 'empty',
		offset: null,
		target: null
	};
})(jQuery);