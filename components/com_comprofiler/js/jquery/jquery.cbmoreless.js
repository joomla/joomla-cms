(function($) {
	var instances = [];
	var methods = {
		init: function( options ) {
			return this.each( function () {
				var $this = this;
				var cbmoreless = $( $this ).data( 'cbmoreless' );

				if ( cbmoreless ) {
					return; // cbmoreless is already bound; so no need to rebind below
				}

				cbmoreless = {};
				cbmoreless.options = ( typeof options != 'undefined' ? options : {} );
				cbmoreless.defaults = $.fn.cbmoreless.defaults;
				cbmoreless.settings = $.extend( true, {}, cbmoreless.defaults, cbmoreless.options );
				cbmoreless.element = $( $this );
				cbmoreless.bg = null;

				if ( cbmoreless.settings.useData ) {
					$.each( cbmoreless.defaults, function( key, value ) {
						if ( ( key != 'init' ) && ( key != 'useData' ) ) {
							// Dash Separated:
							var dataValue = cbmoreless.element.data( 'cbmoreless' + key.charAt( 0 ).toUpperCase() + key.slice( 1 ) );

							if ( typeof dataValue != 'undefined' ) {
								cbmoreless.settings[key] = dataValue;
							} else {
								// No Separater:
								dataValue = cbmoreless.element.data( 'cbmoreless' + key.charAt( 0 ).toUpperCase() + key.slice( 1 ).toLowerCase() );

								if ( typeof dataValue != 'undefined' ) {
									cbmoreless.settings[key] = dataValue;
								}
							}
						}
					});
				}

				cbmoreless.element.triggerHandler( 'cbmoreless.init.before', [cbmoreless] );

				if ( ! cbmoreless.settings.init ) {
					return;
				}

				if ( ! cbmoreless.settings.height ) {
					cbmoreless.settings.height = 100;
				}

				cbmoreless.element.parentsUntil( 'body' ).each( function() {
					var bgColor = $( this ).css( 'background-color' );

					if ( bgColor && ( ( bgColor != '' ) && ( bgColor != 'transparent' ) && bgColor != 'rgba(0, 0, 0, 0)' ) ) {
						cbmoreless.bg = bgColor;

						return false;
					}
				});

				if ( cbmoreless.bg ) {
					cbmoreless.element.children( '.cbMoreLessOpen.fade-edge,.cbMoreLessClose.fade-edge' ).css( 'background-image', 'linear-gradient(to bottom, rgba(255, 255, 255, 0) 0%, ' + cbmoreless.bg + ' 100%)' );
					cbmoreless.element.children( '.cbMoreLessOpen,.cbMoreLessClose' ).find( '.cbMoreLessButton' ).css( 'background-color', cbmoreless.bg );
				}

				cbmoreless.resizeHandler = function() {
					updateToggle.call( $this, cbmoreless );
				};

				$( window ).on( 'resize', cbmoreless.resizeHandler );

				cbmoreless.openHandler = function( e ) {
					e.preventDefault();

					openToggle.call( $this, cbmoreless );
				};

				cbmoreless.closeHandler = function( e ) {
					e.preventDefault();

					closeToggle.call( $this, cbmoreless );
				};

				cbmoreless.element.children( '.cbMoreLessOpen' ).on( 'click', cbmoreless.openHandler );
				cbmoreless.element.children( '.cbMoreLessClose' ).on( 'click', cbmoreless.closeHandler );

				updateToggle.call( $this, cbmoreless );

				// Rebind the cbmoreless element to pick up any data attribute modifications:
				cbmoreless.element.on( 'rebind.cbmoreless', function() {
					cbmoreless.element.cbmoreless( 'rebind' );
				});

				// If the cbmoreless element is modified we need to rebuild it to ensure all our bindings are still ok:
				cbmoreless.element.on( 'modified.cbmoreless', function( e, oldId, newId, index ) {
					if ( oldId != newId ) {
						cbmoreless.element.cbmoreless( 'rebind' );
					}
				});

				// If the cbmoreless is cloned we need to rebind it back:
				cbmoreless.element.on( 'cloned.cbmoreless', function() {
					$( this ).off( 'rebind.cbmoreless' );
					$( this ).off( 'modified.cbmoreless' );
					$( this ).off( 'cloned.cbmoreless' );
					$( this ).removeData( 'cbmoreless' );
					$( this ).children( '.cbMoreLessOpen' ).off( 'click', cbmoreless.openHandler ).addClass( 'hidden' );
					$( this ).children( '.cbMoreLessClose' ).off( 'click', cbmoreless.closeHandler ).addClass( 'hidden' );
					$( this ).removeClass( 'cbMoreLessOpened cbMoreLessClosed' );
					$( this ).cbmoreless( cbmoreless.options );
				});

				cbmoreless.element.triggerHandler( 'cbmoreless.init.after', [cbmoreless] );

				// Bind the cbmoreless to the element so it's reusable and chainable:
				cbmoreless.element.data( 'cbmoreless', cbmoreless );

				// Add this instance to our instance array so we can keep track of our cbmoreless instances:
				instances.push( cbmoreless );
			});
		},
		update: function() {
			var cbmoreless = $( this ).data( 'cbmoreless' );

			if ( ! cbmoreless ) {
				return this;
			}

			updateToggle.call( this, cbmoreless );

			return this;
		},
		open: function() {
			var cbmoreless = $( this ).data( 'cbmoreless' );

			if ( ! cbmoreless ) {
				return this;
			}

			openToggle.call( this, cbmoreless );

			return this;
		},
		close: function() {
			var cbmoreless = $( this ).data( 'cbmoreless' );

			if ( ! cbmoreless ) {
				return this;
			}

			closeToggle.call( this, cbmoreless );

			return this;
		},
		rebind: function() {
			var cbmoreless = $( this ).data( 'cbmoreless' );

			if ( ! cbmoreless ) {
				return this;
			}

			cbmoreless.element.cbmoreless( 'destroy' );
			cbmoreless.element.cbmoreless( cbmoreless.options );

			return this;
		},
		destroy: function() {
			var cbmoreless = $( this ).data( 'cbmoreless' );

			if ( ! cbmoreless ) {
				return this;
			}

			cbmoreless.element.off( 'rebind.cbmoreless' );
			cbmoreless.element.off( 'modified.cbmoreless' );
			cbmoreless.element.off( 'cloned.cbmoreless' );

			$( window ).off( 'resize', cbmoreless.resizeHandler );

			cbmoreless.element.children( '.cbMoreLessOpen' ).off( 'click', cbmoreless.openHandler ).addClass( 'hidden' );
			cbmoreless.element.children( '.cbMoreLessClose' ).off( 'click', cbmoreless.closeHandler ).addClass( 'hidden' );
			cbmoreless.element.removeClass( 'cbMoreLessOpened cbMoreLessClosed' );
			cbmoreless.element.removeData( 'cbmoreless' );
			cbmoreless.element.triggerHandler( 'cbmoreless.destroyed', [cbmoreless] );

			return this;
		},
		instances: function() {
			return instances;
		}
	};

	function updateToggle( cbmoreless ) {
		if ( ! cbmoreless.element.hasClass( 'cbMoreLessOpened' ) ) {
			cbmoreless.element.removeClass( 'cbMoreLessClosed' );
			cbmoreless.element.children( '.cbMoreLessOpen,.cbMoreLessClose' ).addClass( 'hidden' );

			var content = cbmoreless.element.children( '.cbMoreLessContent' );

			content.css( 'max-height', cbmoreless.settings.height );
			content.css( 'height', cbmoreless.settings.height );

			if ( content.height() < content.get( 0 ).scrollHeight ) {
				cbmoreless.element.addClass( 'cbMoreLessClosed' );
				cbmoreless.element.children( '.cbMoreLessOpen' ).removeClass( 'hidden' );
			}

			content.css( 'height', '' );
		}
	}

	function openToggle( cbmoreless ) {
		if ( cbmoreless.settings.height ) {
			cbmoreless.element.children( '.cbMoreLessContent' ).css( 'max-height', 'none' );
		}

		cbmoreless.element.removeClass( 'cbMoreLessClosed' ).addClass( 'cbMoreLessOpened' );
		cbmoreless.element.children( '.cbMoreLessOpen' ).addClass( 'hidden' );
		cbmoreless.element.children( '.cbMoreLessClose' ).removeClass( 'hidden' );
	}

	function closeToggle( cbmoreless ) {
		if ( cbmoreless.settings.height ) {
			cbmoreless.element.children( '.cbMoreLessContent' ).css( 'max-height', cbmoreless.settings.height );
		}

		cbmoreless.element.removeClass( 'cbMoreLessOpened' ).addClass( 'cbMoreLessClosed' );
		cbmoreless.element.children( '.cbMoreLessOpen' ).removeClass( 'hidden' );
		cbmoreless.element.children( '.cbMoreLessClose' ).addClass( 'hidden' );
	}

	$.fn.cbmoreless = function( options ) {
		if ( methods[options] ) {
			return methods[ options ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
		} else if ( ( typeof options === 'object' ) || ( ! options ) ) {
			return methods.init.apply( this, arguments );
		}

		return this;
	};

	$.fn.cbmoreless.defaults = {
		init: true,
		useData: true,
		height: 100
	};
})(jQuery);