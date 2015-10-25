(function($) {
	var instances = [];
	var methods = {
		init: function( options ) {
			return this.each( function () {
				var $this = this;
				var cbtimeago = $( $this ).data( 'cbtimeago' );

				if ( cbtimeago ) {
					return; // cbtimeago is already bound; so no need to rebind below
				}

				cbtimeago = {};
				cbtimeago.options = ( typeof options != 'undefined' ? options : {} );
				cbtimeago.defaults = $.fn.cbtimeago.defaults;
				cbtimeago.settings = $.extend( true, {}, cbtimeago.defaults, cbtimeago.options );
				cbtimeago.element = $( $this );
				cbtimeago.datetime = cbtimeago.element.data( 'cbtimeago-datetime' );

				if ( ! cbtimeago.datetime ) {
					cbtimeago.datetime = cbtimeago.element.attr( 'title' );

					if ( ! cbtimeago.datetime ) {
						cbtimeago.datetime = cbtimeago.element.text();
					}
				}

				if ( cbtimeago.settings.useData ) {
					$.each( cbtimeago.defaults, function( key, value ) {
						if ( ( key != 'init' ) && ( key != 'useData' ) ) {
							// Dash Separated:
							var dataValue = cbtimeago.element.data( 'cbtimeago' + key.charAt( 0 ).toUpperCase() + key.slice( 1 ) );

							if ( typeof dataValue != 'undefined' ) {
								cbtimeago.settings[key] = dataValue;
							} else {
								// No Separater:
								dataValue = cbtimeago.element.data( 'cbtimeago' + key.charAt( 0 ).toUpperCase() + key.slice( 1 ).toLowerCase() );

								if ( typeof dataValue != 'undefined' ) {
									cbtimeago.settings[key] = dataValue;
								}
							}
						}
					});
				}

				cbtimeago.element.triggerHandler( 'cbtimeago.init.before', [cbtimeago] );

				if ( ! cbtimeago.settings.init ) {
					return;
				}

				if ( cbtimeago.settings.hideAgo ) {
					cbtimeago.settings.strings.future = '%s';
					cbtimeago.settings.strings.past = '%s';
				}

				var momentCache	=	null;

				if ( typeof moment != 'undefined' ) {
					momentCache = moment.locale();

					moment.locale( Math.random(), {
						relativeTime: {
							future: cbtimeago.settings.strings.future,
							past: cbtimeago.settings.strings.past,
							s: cbtimeago.settings.strings.seconds,
							m: cbtimeago.settings.strings.minute,
							mm: cbtimeago.settings.strings.minutes,
							h: cbtimeago.settings.strings.hour,
							hh: cbtimeago.settings.strings.hours,
							d: cbtimeago.settings.strings.day,
							dd: cbtimeago.settings.strings.days,
							M: cbtimeago.settings.strings.month,
							MM: cbtimeago.settings.strings.months,
							y: cbtimeago.settings.strings.year,
							yy: cbtimeago.settings.strings.years
						}
					});
				}

				cbtimeago.livestamp = cbtimeago.element.livestamp( cbtimeago.datetime );

				if ( momentCache ) {
					moment.locale( momentCache );
				}

				// Rebind the cbtooltip element to pick up any data attribute modifications:
				cbtimeago.element.on( 'rebind.cbtimeago', function() {
					cbtimeago.element.cbtimeago( 'rebind' );
				});

				// If the cbtimeago element is modified we need to rebuild it to ensure all our bindings are still ok:
				cbtimeago.element.on( 'modified.cbtimeago', function( e, oldId, newId, index ) {
					if ( oldId != newId ) {
						cbtimeago.element.cbtimeago( 'rebind' );
					}
				});

				// If the cbtimeago is cloned we need to rebind it back:
				cbtimeago.element.on( 'cloned.cbtimeago', function() {
					$( this ).off( 'rebind.cbtimeago' );
					$( this ).off( 'cloned.cbtimeago' );
					$( this ).off( 'modified.cbtimeago' );
					$( this ).removeData( 'cbtimeago' );
					$( this ).removeData( 'livestampdata' );
					$( this ).text( '' );

					if ( ( ! $( this ).data( 'cbtimeago-datetime' ) ) && ( ! $( this ).attr( 'title' ) ) ) {
						$( this ).text( cbtimeago.datetime );
					}

					$( this ).cbtimeago( cbtimeago.options );
				});

				cbtimeago.element.triggerHandler( 'cbtimeago.init.after', [cbtimeago] );

				// Bind the cbtimeago to the element so it's reusable and chainable:
				cbtimeago.element.data( 'cbtimeago', cbtimeago );

				// Add this instance to our instance array so we can keep track of our cbtimeago instances:
				instances.push( cbtimeago );
			});
		},
		rebind: function() {
			var cbtimeago = $( this ).data( 'cbtimeago' );

			if ( ! cbtimeago ) {
				return this;
			}

			cbtimeago.element.cbtimeago( 'destroy' );
			cbtimeago.element.cbtimeago( cbtimeago.options );

			return this;
		},
		destroy: function() {
			var cbtimeago = $( this ).data( 'cbtimeago' );

			if ( ! cbtimeago ) {
				return this;
			}

			cbtimeago.element.livestamp( 'destroy' );
			cbtimeago.element.off( 'rebind.cbtimeago' );
			cbtimeago.element.off( 'cloned.cbtimeago' );
			cbtimeago.element.off( 'modified.cbtimeago' );

			$.each( instances, function( i, instance ) {
				if ( instance.element == cbtimeago.element ) {
					instances.splice( i, 1 );

					return false;
				}

				return true;
			});

			cbtimeago.element.text( '' );

			if ( ( ! cbtimeago.element.data( 'cbtimeago-datetime' ) ) && ( ! cbtimeago.element.attr( 'title' ) ) ) {
				cbtimeago.element.text( cbtimeago.datetime );
			}

			cbtimeago.element.removeData( 'cbtimeago' );
			cbtimeago.element.triggerHandler( 'cbtimeago.destroyed', [cbtimeago] );

			return this;
		},
		instances: function() {
			return instances;
		}
	};

	$.fn.cbtimeago = function( options ) {
		if ( methods[options] ) {
			return methods[ options ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
		} else if ( ( typeof options === 'object' ) || ( ! options ) ) {
			return methods.init.apply( this, arguments );
		}

		return this;
	};

	$.fn.cbtimeago.defaults = {
		init: true,
		useData: true,
		hideAgo: false,
		strings: {
			future: 'in %s',
			past: '%s ago',
			seconds: 'less than a minute',
			minute: 'about a minute',
			minutes: '%d minutes',
			hour: 'about an hour',
			hours: 'about %d hours',
			day: 'a day',
			days: '%d days',
			month: 'about a month',
			months: '%d months',
			year: 'about a year',
			years: '%d years'
		}
	};
})(jQuery);