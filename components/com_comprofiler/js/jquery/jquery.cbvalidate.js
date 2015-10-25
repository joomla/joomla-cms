(function($) {
	var instances = [];
	var methods = {
		init: function( options ) {
			return this.each( function () {
				var $this = this;
				var cbvalidate = $( $this ).data( 'cbvalidate' );

				if ( cbvalidate ) {
					return; // cbvalidate is already bound; so no need to rebind below
				}

				cbvalidate = {};
				cbvalidate.options = ( typeof options != 'undefined' ? options : {} );
				cbvalidate.defaults = $.fn.cbvalidate.defaults;
				cbvalidate.settings = $.extend( true, {}, cbvalidate.defaults, cbvalidate.options );
				cbvalidate.element = $( $this );

				if ( cbvalidate.settings.useData ) {
					$.each( cbvalidate.defaults, function( key, value ) {
						if ( ( key != 'init' ) && ( key != 'useData' ) ) {
							// Dash Separated:
							var dataValue = cbvalidate.element.data( 'cbvalidate' + key.charAt( 0 ).toUpperCase() + key.slice( 1 ) );

							if ( typeof dataValue != 'undefined' ) {
								cbvalidate.settings[key] = dataValue;
							} else {
								// No Separater:
								dataValue = cbvalidate.element.data( 'cbvalidate' + key.charAt( 0 ).toUpperCase() + key.slice( 1 ).toLowerCase() );

								if ( typeof dataValue != 'undefined' ) {
									cbvalidate.settings[key] = dataValue;
								}
							}
						}
					});
				}

				cbvalidate.element.triggerHandler( 'cbvalidate.init.before', [cbvalidate] );

				if ( ! cbvalidate.settings.init ) {
					return;
				}

				// Add the translated messages to jQuery Validator:
				if ( cbvalidate.settings.messages ) {
					$.extend( $.validator.messages, cbvalidate.settings.messages );
				}

				// Bind to the forms submit handling so we can set submit variable state:
				cbvalidate.element.on( 'submit', function( event ) {
					var buttons = $( this ).find( 'button[type="submit"][data-submit-text],input[type="submit"][data-submit-text]' );

					buttons.each( function() {
						var submitText = $( this ).data( 'submit-text' );
						var buttonText = null;

						$( this ).prop( 'disabled', true );

						if ( $( this ).is( 'input' ) ) {
							buttonText = $( this ).val();

							$( this ).val( submitText );
						} else {
							buttonText = $( this ).html();

							$( this ).html( submitText );
						}

						$( this ).data( 'button-text', buttonText );
					});

					var validate = $( this ).validate();

					validate.cbIsFormSubmitting = true;

					var valid = validate.form();

					validate.cbIsFormSubmitting = false;

					if ( ! valid ) {
						buttons.each( function() {
							var buttonText = $( this ).data( 'button-text' );

							$( this ).prop( 'disabled', false );

							if ( $( this ).is( 'input' ) ) {
								$( this ).val( buttonText );
							} else {
								$( this ).html( buttonText );
							}

							$( this ).removeData( 'button-text' );
						});

						var errors = cbvalidate.validate.errorList;

						if ( errors.length ) {
							var errorElement = $( errors[0].element );
							var tabPane = errorElement.closest( '.cbTabPane' );

							if ( tabPane.length ) {
								var tab = tabPane.closest( '.cbTabs' );

								if ( tab.length ) {
									var cbtabs = tab.data( 'cbtabs' );

									if ( cbtabs ) {
										cbtabs.element.cbtabs( 'select', tabPane.attr( 'id' ) );
									}
								}
							}

							$.scrollTo( errorElement, 0, { axis: 'y', offset: { top: - ( ( $( window ).outerHeight() - errorElement.outerHeight() ) / 2 ), left: 0 } } );
						}

						validate.focusInvalid();
						event.preventDefault();
					}

					cbvalidate.element.triggerHandler( 'cbvalidate.validate', [cbvalidate, valid] );
				});

				// Bind customized jQuery Validate usage to the form:
				cbvalidate.validate = cbvalidate.element.validate({
					onsubmit: false,
					ignoreTitle: true,
					errorClass: 'cbValidationMessage',
					validClass: 'cbValidationMessage',
					ignore: '.cbValidationDisabled,.hidden,.disabled,.ignore,:disabled,[readonly],[type="hidden"]',
					cbIsFormSubmitting: false,
					cbIsOnFocusOut: false,
					cbIsOnKeyUp: false,
					cbIsOnClick: false,
					cbIsOnChange: false,
					success: function( label, element ) {
						var labels = cbvalidate.validate.errorsFor( element );

						// Duplicate validation labels exist for this element.. remove them:
						if ( labels.length > 1 ) {
							labels.not( label ).remove();
						}

						var message = $( element ).data( 'remote-response' );

						if ( ! message ) {
							message = $( element ).data( 'msg-success' );
						}

						if ( message ) {
							label.find( '.cbValidationIcon' ).remove();
							label.removeClass( 'text-danger' );

							label.html( message );

							if ( message.toString().charAt( 0 ) != '<' ) {
								label.prepend( '<span class="cbValidationIcon fa fa-check"></span>' );
								label.addClass( 'text-success' );
							}
						} else {
							label.remove(); // Remove the validate label if there's nothing to show
						}

						cbvalidate.element.triggerHandler( 'cbvalidate.success', [cbvalidate, label, element] );
					},
					showErrors: function( errorMap, errorList ) {
						var validator = this;

						validator.defaultShowErrors();

						$.each( errorList, function( i, error ) {
							var label = validator.errorsFor( error.element );

							// Duplicate validation labels exist for this element.. remove them:
							if ( label.length > 1 ) {
								label.not( ':last' ).remove();
							}

							label.find( '.cbValidationIcon' ).remove();
							label.removeClass( 'text-success' );

							if ( error.message.toString().charAt( 0 ) != '<' ) {
								label.prepend( '<span class="cbValidationIcon fa fa-times"></span>' );
								label.addClass( 'text-danger' );
							}
						});

						cbvalidate.element.triggerHandler( 'cbvalidate.showerrors', [cbvalidate, errorMap, errorList] );
					},
					highlight: function( element, errorClass, validClass ) {
						$( element ).addClass( 'cbValidationError' ); // input
						$( element ).closest( '.cb_form_line' ).addClass( 'cbValidationError has-error' ); // divs
						$( element ).closest( 'tr' ).addClass( 'cbValidationError has-error' ); // tables

						var tabs = cbvalidate.element.find( '.cbTabs' ); // tabs

						if ( tabs.length ) {
							tabs.each( function() {
								var cbtabs = $( this ).data( 'cbtabs' );

								if ( cbtabs ) {
									$.each( cbtabs.tabs, function( i, tab ) {
										var hasErrors = tab.tabPane.find( 'input.cbValidationError,select.cbValidationError,textarea.cbValidationError' ).not( ':submit,:reset,:image,[disabled],[readonly]' ).not( cbvalidate.validate.settings.ignore );

										if ( hasErrors.length ) {
											tab.tabNav.addClass( 'cbValidationError has-error' );
										}
									});
								}
							});
						}

						cbvalidate.element.triggerHandler( 'cbvalidate.highlight', [cbvalidate, element, errorClass, validClass] );
					},
					unhighlight: function( element, errorClass, validClass ) {
						$( element ).removeClass( 'cbValidationError' ); // input
						$( element ).closest( '.cb_form_line' ).removeClass( 'cbValidationError has-error' ); // divs
						$( element ).closest( 'tr' ).removeClass( 'cbValidationError has-error' ); // tables

						var tabs = cbvalidate.element.find( '.cbTabs' ); // tabs

						if ( tabs.length ) {
							tabs.each( function() {
								var cbtabs = $( this ).data( 'cbtabs' );

								if ( cbtabs ) {
									$.each( cbtabs.tabs, function( i, tab ) {
										var hasErrors = tab.tabPane.find( 'input.cbValidationError,select.cbValidationError,textarea.cbValidationError' ).not( ':submit,:reset,:image,[disabled],[readonly]' ).not( cbvalidate.validate.settings.ignore );

										if ( ! hasErrors.length ) {
											tab.tabNav.removeClass( 'cbValidationError has-error' );
										}
									});
								}
							});
						}

						cbvalidate.element.triggerHandler( 'cbvalidate.unhighlight', [cbvalidate, element, errorClass, validClass] );
					},
					errorElement: 'div',
					errorPlacement: function( error, element ) {
						var field = element.closest( '.fieldCell,.cbFieldSpan,.cb_field' ); // .fieldCell : tables, .cbFieldSpan : span, .cb_field : div

						if ( field.length ) {
							error.appendTo( field );
						} else {
							error.insertAfter( element );
						}

						cbvalidate.element.triggerHandler( 'cbvalidate.errorplacement', [cbvalidate, error, element] );
					},
					onfocusout: function( element ) { // Validate on blur
						this.cbIsOnFocusOut = true;

						this.element( element );

						this.cbIsOnFocusOut = false;

						cbvalidate.element.triggerHandler( 'cbvalidate.focusout', [cbvalidate, element] );
					},
					onkeyup: function( element ) { // Validate on keyup
						this.cbIsOnKeyUp = true;

						this.element( element );

						this.cbIsOnKeyUp = false;

						cbvalidate.element.triggerHandler( 'cbvalidate.keyup', [cbvalidate, element] );
					},
					onclick: function( element ) { // Validate on click
						this.cbIsOnClick = true;

						this.element( element );

						this.cbIsOnClick = false;

						cbvalidate.element.triggerHandler( 'cbvalidate.click', [cbvalidate, element] );
					},
					onchange: function( element ) { // Validate on change
						this.cbIsOnChange = true;

						this.element( element );

						this.cbIsOnChange = false;

						cbvalidate.element.triggerHandler( 'cbvalidate.change', [cbvalidate, element] );
					}
				});

				// Bind to the change event so we can validate on change
				cbvalidate.element.validateDelegate( 'input,select,textarea', 'change', function( event ) {
					var validator = $.data( this[0].form, 'validator' );
					var eventType = 'on' + event.type.replace( /^validate/, '' );
					var settings = validator.settings;

					if ( settings[eventType] && ( ! this.is( settings.ignore ) ) ) {
						settings[eventType].call( validator, this[0], event );
					}
				});

				// Pass the cbvalidator options to validator plugin object so new validate methods can access the options:
				cbvalidate.validate.options = cbvalidate.settings;

				cbvalidate.element.triggerHandler( 'cbvalidate.init.after', [cbvalidate] );

				// Bind the cbvalidate to the element so it's reusable and chainable:
				cbvalidate.element.data( 'cbvalidate', cbvalidate );

				// Add this instance to our instance array so we can keep track of our cbvalidator instances:
				instances.push( cbvalidate );
			});
		},
		validate: function( element, children ) {
			var cbvalidate = $( this ).data( 'cbvalidate' );

			if ( ! cbvalidate ) {
				return true;
			}

			if ( typeof element == 'undefined' ) {
				element = null;
			}

			if ( typeof children == 'undefined' ) {
				children = false;
			}

			cbvalidate.validate.cbIsFormSubmitting = true;

			var valid = true;

			if ( element ) {
				if ( children ) {
					var form = cbvalidate.validate.currentForm;

					cbvalidate.validate.currentForm = element;

					var elements = cbvalidate.validate.elements();

					cbvalidate.validate.currentForm = form;

					var invalid = 0;

					elements.each( function() {
						var child = this;

						$.each( form, function( i, input ) {
							if ( input == child ) {
								if ( ! cbvalidate.validate.element( input ) ) {
									invalid++;
								}
							}
						});
					});

					valid = ( invalid ? false : true );
				} else {
					valid = cbvalidate.validate.element( element );
				}
			} else {
				valid = cbvalidate.validate.form();
			}

			cbvalidate.validate.cbIsFormSubmitting = false;

			if ( ! valid ) {
				cbvalidate.validate.focusInvalid();
			}

			cbvalidate.element.triggerHandler( 'cbvalidate.validate', [cbvalidate, valid] );

			return valid;
		},
		reset: function() {
			var cbvalidate = $( this ).data( 'cbvalidate' );

			if ( ! cbvalidate ) {
				return false;
			}

			cbvalidate.validate.resetForm();

			cbvalidate.element.triggerHandler( 'cbvalidate.reset', [cbvalidate] );

			return true;
		},
		invalid: function() {
			var cbvalidate = $( this ).data( 'cbvalidate' );

			if ( ! cbvalidate ) {
				return 0;
			}

			var invalid = cbvalidate.validate.numberOfInvalids();

			cbvalidate.element.triggerHandler( 'cbvalidate.invalid', [cbvalidate, invalid] );

			return invalid;
		},
		focus: function() {
			var cbvalidate = $( this ).data( 'cbvalidate' );

			if ( ! cbvalidate ) {
				return false;
			}

			cbvalidate.validate.focusInvalid();

			cbvalidate.element.triggerHandler( 'cbvalidate.focus', [cbvalidate] );

			return true;
		},
		valid: function() {
			var cbvalidate = $( this ).data( 'cbvalidate' );

			if ( ! cbvalidate ) {
				return true;
			}

			var valid = cbvalidate.validate.valid();

			cbvalidate.element.triggerHandler( 'cbvalidate.valid', [cbvalidate, valid] );

			return valid;
		},
		elements: function( type ) {
			var cbvalidate = $( this ).data( 'cbvalidate' );

			if ( ! cbvalidate ) {
				return [];
			}

			if ( typeof type == 'undefined' ) {
				type = null;
			}

			var elements = [];

			if ( type == 'invalid' ) {
				elements = cbvalidate.validate.invalidElements();
			} else if ( type == 'valid' ) {
				elements = cbvalidate.validate.validElements();
			} else {
				elements = cbvalidate.validate.elements();
			}

			cbvalidate.element.triggerHandler( 'cbvalidate.elements', [cbvalidate, elements] );

			return elements;
		},
		errors: function() {
			var cbvalidate = $( this ).data( 'cbvalidate' );

			if ( ! cbvalidate ) {
				return [];
			}

			var errors = cbvalidate.validate.errors();

			cbvalidate.element.triggerHandler( 'cbvalidate.errors', [cbvalidate, errors] );

			return errors;
		},
		instances: function() {
			return instances;
		}
	};

	// custom extension of cbremote for cbfield fieldclass usage
	$.validator.addMethod( 'cbfield', function( value, element, params ) {
		var options = this.options;

		if ( ! params.method ) {
			params.method = 'cbfield';
		}

		if ( ! params['function'] ) {
			params['function'] = 'checkvalue';
		}

		params.data = {
			user: params.user,
			field: params.field,
			reason: params.reason,
			value: value
		};

		if ( options.settings.cbfield.url ) {
			params.url = options.settings.cbfield.url;
		}

		if ( options.settings.cbfield.spooffield && options.settings.cbfield.spooffield ) {
			params.data[options.settings.cbfield.spooffield] = options.settings.cbfield.spoofstring;
		}

		if ( options.settings.cbfield.spamfield && options.settings.cbfield.spamstring ) {
			params.data[options.settings.cbfield.spamfield] = options.settings.cbfield.spamstring;
		}

		return $.validator.methods.cbremote.call( this, value, element, params );
	}, 'Please fix this field.' );

	// same as remote, but specifically for cb
	$.validator.addMethod( 'cbremote', function( value, element, params ) {
		$( element ).data( 'remote-response', null );

		if ( this.optional( element ) || this.cbIsOnChange || this.cbIsFormSubmitting || ( ! params.url ) ) {
			return true;
		}

		// substitute params into the url
		$.each( params, function( k, v ) {
			if ( ( typeof v != 'object' ) && ( typeof v != 'array' ) && ( k != 'url' ) && ( k != 'data' ) ) { // Be sure to ignore objects, arrays, data, and self (url)
				params.url = params.url.replace( '[' + k + ']', v );
			}
		});

		var previous = this.previousValue( element );
		var validator = this;

		if ( ! this.settings.messages[element.name] ) {
			this.settings.messages[element.name] = {};
		}

		if ( ! params.method ) {
			params.method = 'cbremote';
		}

		previous.originalMessage = this.settings.messages[element.name][params.method];
		this.settings.messages[element.name][params.method] = previous.message;

		if ( previous.old === value ) {
			$( element ).data( 'remote-response', $( previous ).data( 'remote-response' ) );

			return previous.valid;
		} else if ( this.cbIsOnKeyUp ) {
			return true;
		}

		previous.old = value;
		$( previous ).data( 'remote-response', null );

		this.startRequest( element );

		$.ajax({
			url: params.url,
			type: 'POST',
			mode: 'abort',
			port: 'validate' + element.name,
			dataType: 'json',
			data: params.data,
			converters: {
				'text json': function( result ) {
					try {
						return $.parseJSON( result );
					} catch( e ) {
						return { "valid": true, "message": result };
					}
				}
			},
			beforeSend: function() {
				$( '<span class="cbSpinner fa fa-spinner fa-spin-fast fa-infix"></span>' ).insertAfter( $( element ) );
			}
		}).done( function( response ) {
			var valid = ( ( response.valid === true ) || ( response.valid === 'true' ) );
			var errors = {};
			var message = null;
			var submitted = null;

			validator.settings.messages[element.name][params.method] = previous.originalMessage;

			if ( valid ) {
				$( element ).data( 'remote-response', response.message );
				$( previous ).data( 'remote-response', response.message );

				submitted = validator.formSubmitted;
				validator.prepareElement( element );
				validator.formSubmitted = submitted;
				validator.successList.push( element );
				delete validator.invalid[element.name];
				validator.showErrors();
			} else {
				message = ( response.message || validator.defaultMessage( element, params.method ) );
				errors[element.name] = previous.message = ( $.isFunction( message ) ? message( value ) : message );
				validator.invalid[element.name] = true;
				validator.showErrors( errors );
			}

			previous.valid = valid;

			validator.stopRequest( element, valid );
		}).always( function() {
			$( element ).siblings( '.cbSpinner' ).remove();
		});

		return 'pending';
	}, 'Please fix this field.' );

	// file mimetype validation
	$.validator.addMethod( 'accept', function( value, element, params ) {
		// Split mime on commas in case we have multiple types we can accept
		var typeParam = ( typeof params === 'string' ? params.replace( /\s/g, '' ).replace( /,/g, '|' ) : 'image/*' );
		var optionalValue = this.optional( element );

		// Element is optional
		if ( optionalValue ) {
			return optionalValue;
		}

		if ( $( element ).attr( 'type' ) === 'file' ) {
			// If we are using a wildcard, make it regex friendly
			typeParam = typeParam.replace( /\*/g, '.*' );

			// Check if the element has a FileList before checking each file
			if ( element.files && element.files.length ) {
				for ( var i = 0; i < element.files.length; i++ ) {
					var file = element.files[i];

					// Grab the mimetype from the loaded file, verify it matches
					if ( file.type && ( ! file.type.replace( /mp3/, 'mpeg' ).replace( /m4a/, 'mp4' ).match( new RegExp( '.?(' + typeParam + ')$', 'i' ) ) ) ) {
						return false;
					}
				}
			}
		}

		// Either return true because we've validated each file, or because the
		// browser does not support element.files and the FileList feature
		return true;
	}, $.validator.format( 'Please enter a value with a valid extension.' ) );

	// file extension validation
	$.validator.addMethod( 'extension', function( value, element, params ) {
		params = ( typeof params === 'string' ? params.replace( /,/g, '|' ) : 'png|jpe?g|gif' );

		return this.optional( element ) || value.match( new RegExp( '.(' + params + ')$', 'i' ) );
	}, $.validator.format( 'Please enter a value with a valid extension.' ) );

	// maximum word count
	$.validator.addMethod( 'maxWords', function( value, element, params ) {
		return this.optional( element ) || ( $( value ).text().match( /\b\w+\b/g ).length <= params );
	}, $.validator.format( 'Please enter {0} words or less.' ) );

	// minimum word count
	$.validator.addMethod( 'minWords', function( value, element, params ) {
		return this.optional( element ) || ( $( value ).text().match( /\b\w+\b/g ).length >= params );
	}, $.validator.format( 'Please enter at least {0} words.' ) );

	// range of words count
	$.validator.addMethod( 'rangeWords', function( value, element, params ) {
		var valueStripped = $( value ).text();
		var regex = /\b\w+\b/g;

		return this.optional( element ) || ( valueStripped.match( regex ).length >= params[0] ) && ( valueStripped.match( regex ).length <= params[1] );
	}, $.validator.format( 'Please enter between {0} and {1} words.' ) );

	// regex validation
	$.validator.addMethod( 'pattern', function( value, element, params ) {
		if ( this.optional( element ) ) {
			return true;
		}

		if ( typeof params === 'string' ) {
			var delimiter = params.substr( 0, 1 );
			var end = params.lastIndexOf( delimiter );
			var pattern = params.slice( 1, end );
			var modifiers = params.substr( ( end + 1 ) );

			pattern = pattern.replace( ( delimiter + delimiter ), delimiter );

			params = new RegExp( pattern, modifiers );
		}

		return params.test( value );
	}, 'Invalid format.' );

	// same as url, but Protocol is optional and specific to CB url usages
	$.validator.addMethod( 'cburl', function( value, element ) {
		return this.optional( element ) || /^(?:(https?|ftp):\/\/)?(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
	}, 'Please enter a valid URL.' );

	// same as pattern, but tests specifically for a valid Joomla/CB username
	$.validator.addMethod( 'cbusername', function( value, element ) {
		return this.optional( element ) || ! /^\s+|[<>"'%;()&\\]|\.\.\/|\s+$/i.test( value );
	}, 'Please enter a valid username with no space at beginning or end and must not contain the following characters: < > \\ " \' % ; ( ) &' );

	// tests password strength; result is always true and is for display purposes only
	$.validator.addMethod( 'passwordstrength', function( value, element ) {
		var strengths	=	[/.{8,}/, /[A-Z]+/, /[a-z]+/, /\d+/, /[\-\]\\`~!@#$%^&*()_=+[{}|;:'",<.>\/?]+/, /.{12,}/, /[A-Z]{2,}/, /[a-z]{2,}/, /\d{2,}/, /[\-\]\\`~!@#$%^&*()_=+[{}|;:'",<.>\/?]{2,}/];
		var strength	=	0;

		$.each( strengths, function( i, regexp ) {
			if ( value.match( regexp ) ) {
				strength++;
			}
		});

		if ( strength > strengths.length ) {
			strength	=	strengths.length;
		} else if ( strength < 0 ) {
			strength	=	0;
		}

		var normalized	=	( strength * ( 100 / strengths.length ) );
		var quality = 'cbPasswordStrengthStrong progress-bar-success';

		if ( normalized <= 30 ) {
			quality = 'cbPasswordStrengthVeryWeak progress-bar-danger';
		} else if ( normalized <= 50 ) {
			quality = 'cbPasswordStrengthWeak progress-bar-warning';
		} else if ( normalized <= 70 ) {
			quality = 'cbPasswordStrengthOk progress-bar-info';
		}

		var width = $( element ).outerWidth();

		if ( $( element ).is( ':hidden' ) ) {
			var temporary = $( element ).clone( false ).attr({
				id: '',
				'class': ''
			}).css({
				position: 'absolute',
				display: 'block',
				width: 'auto',
				visibility: 'hidden',
				padding: $( element ).css( 'padding' ),
				border: $( element ).css( 'border' ),
				margin: $( element ).css( 'margin' ),
				fontFamily: $( element ).css( 'font-family' ),
				fontSize: $( element ).css( 'font-size' ),
				fontWeight: $( element ).css( 'font-weight' ),
				boxSizing: $( element ).css( 'box-sizing' )
			}).appendTo( 'body' );

			width = temporary.outerWidth();

			temporary.remove();
		}

		$( element ).data( 'msg-success', '<div class="cbPasswordStrength progress" style="width: ' + width + 'px"><div class="cbPasswordStrengthBar progress-bar ' + quality + '" style="width: ' + normalized + '%"></div></div>' );

		return true;
	}, '' );

	// minimum and maximum file size
	$.validator.addMethod( 'filesize', function( value, element, params ) {
		var optionalValue = this.optional( element );

		// Element is optional
		if ( optionalValue ) {
			return optionalValue;
		}

		var exceedsMin = false;
		var exceedsMax = false;

		if ( ( params[0] || params[1] ) && ( $( element ).attr( 'type' ) === 'file' ) ) {
			// Check if the element has a FileList before checking each file
			if ( element.files && element.files.length ) {
				for ( var i = 0; i < element.files.length; i++ ) {
					var file = element.files[i];
					var size = bytesToType( file.size, params[2] );

					if ( params[0] && ( size < params[0] ) ) {
						exceedsMin = true;
					}

					if ( params[1] && ( size > params[1] ) ) {
						exceedsMax = true;
					}
				}
			}
		}

		if ( exceedsMin || exceedsMax ) {
			if ( ! this.settings.messages[ element.name ] ) {
				this.settings.messages[element.name] = {};
				this.settings.messages[element.name].min = this.defaultMessage( element, 'filesizemin' );
				this.settings.messages[element.name].max = this.defaultMessage( element, 'filesizemax' );
				this.settings.messages[element.name].both = this.defaultMessage( element, 'filesize' );
			}

			if ( exceedsMin && ( ! exceedsMax ) ) {
				this.settings.messages[element.name].filesize = this.settings.messages[element.name].min;
			} else if ( exceedsMax && ( ! exceedsMin ) ) {
				this.settings.messages[element.name].filesize = this.settings.messages[element.name].max;
			} else {
				this.settings.messages[element.name].filesize = this.settings.messages[element.name].both;
			}

			return false;
		}

		// Either return true because we've validated each file, or because the
		// browser does not support element.files and the FileList feature
		return true;
	}, $.validator.format( 'File size must exceed the minimum of {0} {2}s, but not the maximum of {1} {2}s.' ) );

	function bytesToType( bytes, type ) {
		if ( type == 'TB' ) {
			bytes = ( ( ( ( bytes / 1024 ) / 1024 ) / 1024 ) / 1024 );
		} else if ( type == 'GB' ) {
			bytes = ( ( ( bytes / 1024 ) / 1024 ) / 1024 );
		} else if ( type == 'MB' ) {
			bytes = ( ( bytes / 1024 ) / 1024 );
		} else if ( type == 'KB' ) {
			bytes = ( bytes / 1024 );
		}

		return bytes;
	}

	$.fn.cbvalidate = function( options ) {
		if ( methods[options] ) {
			return methods[ options ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
		} else if ( ( typeof options === 'object' ) || ( ! options ) ) {
			return methods.init.apply( this, arguments );
		}

		return this;
	};

	$.fn.cbvalidate.defaults = {
		init: true,
		useData: false,
		messages: null,
		settings: null
	};
})(jQuery);