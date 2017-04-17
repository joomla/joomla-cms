(function($) {
	var instances = [];
	var methods = {
		init: function( options ) {
			return this.each( function () {
				var $this = this;
				var cbtabs = $( $this ).data( 'cbtabs' );

				if ( cbtabs ) {
					return; // cbtabs is already bound; so no need to rebind below
				}

				cbtabs = {};
				cbtabs.options = ( typeof options != 'undefined' ? options : {} );
				cbtabs.defaults = $.fn.cbtabs.defaults;
				cbtabs.settings = $.extend( true, {}, cbtabs.defaults, cbtabs.options );
				cbtabs.element = $( $this );
				cbtabs.tabs = [];

				if ( cbtabs.settings.useData ) {
					$.each( cbtabs.defaults, function( key, value ) {
						if ( ( key != 'init' ) && ( key != 'useData' ) ) {
							// Dash Separated:
							var dataValue = cbtabs.element.data( 'cbtabs' + key.charAt( 0 ).toUpperCase() + key.slice( 1 ) );

							if ( typeof dataValue != 'undefined' ) {
								cbtabs.settings[key] = dataValue;
							} else {
								// No Separater:
								dataValue = cbtabs.element.data( 'cbtabs' + key.charAt( 0 ).toUpperCase() + key.slice( 1 ).toLowerCase() );

								if ( typeof dataValue != 'undefined' ) {
									cbtabs.settings[key] = dataValue;
								}
							}
						}
					});
				}

				cbtabs.element.triggerHandler( 'cbtabs.init.before', [cbtabs] );

				if ( ! cbtabs.settings.init ) {
					return;
				}

				var cookie = null;

				if ( cbtabs.settings.useCookies ) {
					cookie = document.cookie.match( new RegExp( cbtabs.element.attr( 'id' ) + '=([^;]+)' ) );
				}

				cbtabs.tabsNav = cbtabs.element.find( '.cbTabsNav:first' );
				cbtabs.tabsContent = cbtabs.element.find( '.cbTabsContent:first' );
				cbtabs.tabPanes = cbtabs.tabsContent.children( '.cbTabPane' );
				cbtabs.tabNavs = cbtabs.tabPanes.children( '.cbTabNav' );
				cbtabs.tabNavsPrevious = cbtabs.tabPanes.find( '.cbTabNavPrevious' );
				cbtabs.tabNavsNext = cbtabs.tabPanes.find( '.cbTabNavNext' );

				cbtabs.tabNavs.each( function( i ) {
					var tabNav = $( $( this )[0].outerHTML.replace( /h2/, 'li' ) ).appendTo( cbtabs.tabsNav );
					var tabPane = $( this ).parent();
					var tab = { tabNav: tabNav, tabPane: tabPane, tabIndex: ( i + 1 ) };

					cbtabs.tabs.push( tab );

					$( this ).remove();

					tabNav.children( 'a' ).on( 'click', function( e, external, event ) {
						e.preventDefault();

						if ( typeof event == 'undefined' ) {
							event = e;
						}

						if ( ( ! tabNav.hasClass( 'disabled' ) ) && ( ( ! cbtabs.settings.stepByStep ) || ( external === true ) ) ) {
							cbtabs.tabNavs.removeClass( 'active' );
							cbtabs.tabPanes.removeClass( 'active' );

							tabNav.addClass( 'active' );
							tabPane.addClass( 'active' );

							if ( cbtabs.settings.useCookies ) {
								var d = new Date();

								d.setTime( d.getTime() + 24 * 60 * 60 * 1000 );

								document.cookie = cbtabs.element.attr( 'id' ) + '=' + tabNav.attr( 'id' ) + '; expires=' + d.toGMTString() + '; path=/';
							}

							cbtabs.element.triggerHandler( 'cbtabs.selected', [event, cbtabs, tab] );
						}
					});

					if ( $.trim( tabPane.html() ) == '' ) {
						tab.tabNav.addClass( 'disabled hidden' ).removeClass( 'active' );
						tab.tabPane.addClass( 'disabled hidden' ).removeClass( 'active' );
					}
				});

				// We changed the DOM so lets reset this to the new nav li elements:
				cbtabs.tabNavs = cbtabs.tabsNav.children( '.cbTabNav' );

				// Check if there are any tabs or if they're all disabled; then see if we're a nested tab:
				if ( ( ! cbtabs.tabNavs.length ) || ( cbtabs.tabNavs.filter( '.disabled' ).length == cbtabs.tabNavs.length ) ) {
					var parentTab = cbtabs.element.parent();

					if ( parentTab.hasClass( 'cbTabPane' ) ) {
						// Check if this nested tab has any siblings before hiding the parent tab:
						if ( ! cbtabs.element.siblings().length ) {
							parentTab.closest( '.cbTabs' ).cbtabs( 'hide', parentTab.attr( 'id' ) );
						}
					}
				}

				// Bind to all children previous navigations:
				cbtabs.prevHandler = function( e ) {
					e.preventDefault();

					var tabPane = $( this ).closest( '.cbTabPane' );
					var tab = findTab.call( $this, tabPane.attr( 'id' ) );

					if ( tab ) {
						var prevTab = tab.tabNav.prevAll( ':not(.disabled)' ).first();

						if ( ! prevTab.length ) {
							prevTab = tab.tabNav.nextAll( ':not(.disabled)' ).last()
						}

						if ( prevTab.length ) {
							prevTab.children( 'a' ).trigger( 'click', [true, e] );

							prevTab[0].scrollIntoView();

							cbtabs.element.triggerHandler( 'cbtabs.previous', [e, cbtabs, tab, findTab.call( $this, prevTab.attr( 'id' ) )] );
						}
					}
				};

				cbtabs.tabNavsPrevious.on( 'click', cbtabs.prevHandler );

				// Bind to all children next navigations:
				cbtabs.nextHandler = function( e ) {
					e.preventDefault();

					var tabPane = $( this ).closest( '.cbTabPane' );
					var tab = findTab.call( $this, tabPane.attr( 'id' ) );

					if ( tab ) {
						var nextTab = tab.tabNav.nextAll( ':not(.disabled)' ).first();

						if ( ! nextTab.length ) {
							nextTab = tab.tabNav.prevAll( ':not(.disabled)' ).last()
						}

						if ( nextTab.length ) {
							var form = cbtabs.element.closest( 'form' );

							if ( form.length ) {
								var validator = form.data( 'cbvalidate' );

								if ( validator ) {
									if ( ! validator.element.cbvalidate( 'validate', tabPane, true ) ) {
										return;
									}
								}
							}

							nextTab.children( 'a' ).trigger( 'click', [true, e] );

							nextTab[0].scrollIntoView();

							cbtabs.element.triggerHandler( 'cbtabs.next', [e, cbtabs, tab, findTab.call( $this, nextTab.attr( 'id' ) )] );
						}
					}
				};

				cbtabs.tabNavsNext.on( 'click', cbtabs.nextHandler );

				// If step by step is enabled we want to disable the first previous button and replace the last next button with submit:
				if ( cbtabs.settings.stepByStep ) {
					cbtabs.tabPanes.not( '.disabled' ).first().find( '.cbTabNavPrevious' ).addClass( 'hidden' );

					var form = cbtabs.element.closest( 'form' );
					var lastTab = cbtabs.tabPanes.not( '.disabled' ).last().find( '.cbTabNavNext' );

					lastTab.addClass( 'hidden' );

					if ( form.length ) {
						var submit = form.find( 'input[type="submit"]:first' );

						submit.appendTo( lastTab.parent() );
					}
				}

				// Rebind the cbtabs element to pick up any data attribute modifications:
				cbtabs.element.on( 'rebind.cbtabs', function() {
					cbtabs.element.cbtabs( 'rebind' );
				});

				// If the cbtabs element is modified we need to rebuild it to ensure all our bindings are still ok:
				cbtabs.element.on( 'modified.cbtabs', function( e, oldId, newId, index ) {
					if ( oldId != newId ) {
						cbtabs.element.cbtabs( 'rebind' );
					}
				});

				// If the cbtabs is cloned we need to rebind it back:
				cbtabs.element.bind( 'cloned.cbtabs', function() {
					$( this ).off( 'rebind.cbtabs' );
					$( this ).off( 'cloned.cbtabs' );
					$( this ).off( 'modified.cbtabs' );
					$( this ).removeData( 'cbtabs' );

					var tabsNav = $( this ).find( '.cbTabsNav:first' );
					var tabsContent = $( this ).find( '.cbTabsContent:first' );
					var tabPanes = tabsContent.children( '.cbTabPane' );
					var tabNavs = tabsNav.children( '.cbTabNav' );
					var tabNavsPrevious = tabPanes.find( '.cbTabNavPrevious' );
					var tabNavsNext = tabPanes.find( '.cbTabNavNext' );

					tabNavs.each( function( i ) {
						var paneId = $( this ).children( 'a' ).attr( 'href' );

						$( this ).removeClass( 'disabled hidden active' );

						$( $( this )[0].outerHTML.replace( /li/, 'h2' ) ).prependTo( tabsContent.find( paneId ) );

						$( this ).remove();
					});

					tabNavsPrevious.off( 'click', cbtabs.prevHandler );
					tabNavsNext.off( 'click', cbtabs.nextHandler );

					$( this ).cbtabs( cbtabs.options );
				});

				cbtabs.element.triggerHandler( 'cbtabs.init.after', [cbtabs] );

				// Bind the cbtabs to the element so it's reusable and chainable:
				cbtabs.element.data( 'cbtabs', cbtabs );

				// Add this instance to our instance array so we can keep track of our cbtabs instances:
				instances.push( cbtabs );

				// Select the tab associated with the cookie:
				if ( cookie ) {
					selectTab.call( $this, findTab.call( $this, cookie[1] ), false );
				}

				// Select the tab as specified in the API call:
				if ( cbtabs.settings.tabSelected ) {
					selectTab.call( $this, findTab.call( $this, cbtabs.settings.tabSelected ) );
				}

				// Last resort select the first available tab:
				if ( ! cbtabs.tabNavs.is( '.active' ) ) {
					cbtabs.tabNavs.not( '.disabled' ).first().children( 'a' ).trigger( 'click', [true] );
				}
			});
		},
		selected: function() {
			var cbtabs = $( this ).data( 'cbtabs' );

			if ( ! cbtabs ) {
				return false;
			}

			var selected = false;

			$.each( cbtabs.tabs, function( i, tab ) {
				if ( tab.tabNav.hasClass( 'active' ) ) {
					selected = tab;
				}
			});

			return selected;
		},
		select: function( tabId ) {
			if ( typeof tabId == 'undefined' ) {
				tabId = null;
			}

			return selectTab.call( this, findTab.call( this, tabId ) );
		},
		disable: function( tabId ) {
			if ( typeof tabId == 'undefined' ) {
				tabId = null;
			}

			return disableTab.call( this, findTab.call( this, tabId ) );
		},
		enable: function( tabId ) {
			if ( typeof tabId == 'undefined' ) {
				tabId = null;
			}

			return enableTab.call( this, findTab.call( this, tabId ) );
		},
		hide: function( tabId ) {
			if ( typeof tabId == 'undefined' ) {
				tabId = null;
			}

			return hideTab.call( this, findTab.call( this, tabId ) );
		},
		show: function( tabId ) {
			if ( typeof tabId == 'undefined' ) {
				tabId = null;
			}

			return showTab.call( this, findTab.call( this, tabId ) );
		},
		rebind: function() {
			var cbtabs = $( this ).data( 'cbtabs' );

			if ( ! cbtabs ) {
				return this;
			}

			cbtabs.element.cbtabs( 'destroy' );
			cbtabs.element.cbtabs( cbtabs.options );

			return this;
		},
		destroy: function() {
			var cbtabs = $( this ).data( 'cbtabs' );

			if ( ! cbtabs ) {
				return this;
			}

			$.each( cbtabs.tabs, function( i, tab ) {
				tab.tabNav.removeClass( 'disabled hidden active' );
				tab.tabPane.removeClass( 'disabled hidden active' );

				$( tab.tabNav[0].outerHTML.replace( /li/, 'h2' ) ).prependTo( tab.tabPane );

				tab.tabNav.remove();
			});

			cbtabs.tabNavsPrevious.off( 'click', cbtabs.prevHandler );
			cbtabs.tabNavsNext.off( 'click', cbtabs.nextHandler );
			cbtabs.element.off( 'rebind.cbtabs' );
			cbtabs.element.off( 'cloned.cbtabs' );
			cbtabs.element.off( 'modified.cbtabs' );

			$.each( instances, function( i, instance ) {
				if ( instance.element == cbtabs.element ) {
					instances.splice( i, 1 );

					return false;
				}

				return true;
			});

			cbtabs.element.removeData( 'cbtabs' );
			cbtabs.element.triggerHandler( 'cbtabs.destroyed', [cbtabs] );

			return this;
		},
		instances: function() {
			return instances;
		}
	};

	function findTab( tabId ) {
		if ( ! tabId ) {
			return null;
		}

		var cbtabs = $( this ).data( 'cbtabs' );

		if ( ! cbtabs ) {
			return null;
		}

		var tabFound = null;

		$.each( cbtabs.tabs, function( i, tab ) {
			if (
					( tabId == tab.tabNav.text() ) // Tab title matched
					|| ( tabId == tab.tabNav.attr( 'id' ) ) // Tab nav id attribute matched
					|| ( tabId == tab.tabPane.attr( 'id' ) ) // Tab pane id attribute matched
					|| ( 'cbtabnav' + tabId == tab.tabNav.attr( 'id' ) ) // Tab nav id matched
					|| ( 'cbtabpane' + tabId == tab.tabPane.attr( 'id' ) ) // Tab pane id matched
			) {
				tabFound = tab;

				return false;
			}

			return true;
		});

		return tabFound;
	}

	function selectTab( tab, parents ) {
		if ( ! tab ) {
			return false;
		}

		var cbtabs = $( this ).data( 'cbtabs' );

		if ( ! cbtabs ) {
			return false;
		}

		var tabSelected = false;

		if ( typeof parents == 'undefined' ) {
			parents = true;
		}

		if ( tab ) {
			if ( ! tab.tabNav.hasClass( 'disabled' ) ) {
				tabSelected = true;

				tab.tabNav.children( 'a' ).trigger( 'click', [true] );

				if ( parents ) {
					tab.tabNav.parents( '.cbTabPane' ).each( function() {
						$( '#' + $( this ).attr( 'id' ).replace( /cbtabpane/, 'cbtabnav' ) ).children( 'a' ).trigger( 'click', [true] );
					});
				}
			}
		}

		return tabSelected;
	}

	function disableTab( tab ) {
		if ( ! tab ) {
			return false;
		}

		var cbtabs = $( this ).data( 'cbtabs' );

		if ( ! cbtabs ) {
			return false;
		}

		var tabDisabled = false;

		if ( tab ) {
			tabDisabled = true;

			tab.tabNav.addClass( 'disabled' );
			tab.tabPane.addClass( 'disabled' );

			if ( cbtabs.settings.stepByStep ) {
				cbtabs.tabPanes.find( '.cbTabNavPrevious.hidden' ).removeClass( 'hidden' );
				cbtabs.tabPanes.find( '.cbTabNavNext.hidden' ).removeClass( 'hidden' );
				cbtabs.tabPanes.not( '.disabled' ).first().find( '.cbTabNavPrevious' ).addClass( 'hidden' );

				var form = cbtabs.element.closest( 'form' );
				var lastTab = cbtabs.tabPanes.not( '.disabled' ).last().find( '.cbTabNavNext' );

				lastTab.addClass( 'hidden' );

				if ( form.length ) {
					var submit = form.find( 'input[type="submit"]:first' );

					submit.appendTo( lastTab.parent() );
				}
			}

			if ( tab.tabNav.hasClass( 'active' ) ) {
				cbtabs.tabNavs.not( '.disabled' ).first().children( 'a' ).trigger( 'click', [true] );
			}

			$( this ).triggerHandler( 'cbtabs.disable', [cbtabs, tab] );
		}

		return tabDisabled;
	}

	function enableTab( tab ) {
		if ( ! tab ) {
			return false;
		}

		var cbtabs = $( this ).data( 'cbtabs' );

		if ( ! cbtabs ) {
			return false;
		}

		var tabEnabled = false;

		if ( tab ) {
			tabEnabled = true;

			tab.tabNav.removeClass( 'disabled' );
			tab.tabPane.removeClass( 'disabled' );

			if ( cbtabs.settings.stepByStep ) {
				cbtabs.tabPanes.find( '.cbTabNavPrevious.hidden' ).removeClass( 'hidden' );
				cbtabs.tabPanes.find( '.cbTabNavNext.hidden' ).removeClass( 'hidden' );
				cbtabs.tabPanes.not( '.disabled' ).first().find( '.cbTabNavPrevious' ).addClass( 'hidden' );

				var form = cbtabs.element.closest( 'form' );
				var lastTab = cbtabs.tabPanes.not( '.disabled' ).last().find( '.cbTabNavNext' );

				lastTab.addClass( 'hidden' );

				if ( form.length ) {
					var submit = form.find( 'input[type="submit"]:first' );

					submit.appendTo( lastTab.parent() );
				}
			}

			$( this ).triggerHandler( 'cbtabs.enable', [cbtabs, tab] );
		}

		return tabEnabled;
	}

	function hideTab( tab ) {
		if ( ! tab ) {
			return false;
		}

		var cbtabs = $( this ).data( 'cbtabs' );

		if ( ! cbtabs ) {
			return false;
		}

		if ( disableTab.call( this, tab ) ) {
			tab.tabNav.addClass( 'hidden' );

			$( this ).triggerHandler( 'cbtabs.hide', [cbtabs, tab] );

			return true;
		}

		return false;
	}

	function showTab( tab ) {
		if ( ! tab ) {
			return false;
		}

		var cbtabs = $( this ).data( 'cbtabs' );

		if ( ! cbtabs ) {
			return false;
		}

		if ( enableTab.call( this, tab ) ) {
			tab.tabNav.removeClass( 'hidden' );

			$( this ).triggerHandler( 'cbtabs.show', [cbtabs, tab] );

			return true;
		}

		return false;
	}

	$.fn.cbtabs = function( options ) {
		if ( methods[options] ) {
			return methods[ options ].apply( this, Array.prototype.slice.call( arguments, 1 ) );
		} else if ( ( typeof options === 'object' ) || ( ! options ) ) {
			return methods.init.apply( this, arguments );
		}

		return this;
	};

	$.fn.cbtabs.defaults = {
		init: true,
		useData: true,
		useCookies: true,
		stepByStep: false,
		tabSelected: null
	};

	$( document ).on( 'click', '.cbTabNavExternal', function( e ) {
		e.preventDefault();

		var tabId = $( this ).data( 'tab' );

		if ( typeof tabId != 'undefined' ) {
			$.each( instances, function( i, instance ) {
				var tab = findTab.call( instance.element[0], tabId );

				if ( tab ) {
					selectTab.call( instance.element[0], tab );

					return false;
				}

				return true;
			});
		}
	});
})(jQuery);