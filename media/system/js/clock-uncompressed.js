/*
ClockPick, by Josh Nathanson
Version 1.2.9
Timepicker plugin for jQuery
See copyright at end of file
name	 clockpick
type	 jQuery
param	 options                  hash                    object containing config options
param	 options[starthour]       int                     starting hour (use military int)
param	 options[endhour]         int                     ending hour (use military int)
param	 options[showminutes]     bool                    show minutes
param 	 options[minutedivisions] int                     number of divisions, i.e. 4 = :00, :15, :30, :45
param 	 options[military]        bool                    use 24hr time if true
param	 options[event]           string                  mouse event to trigger plugin
param	 options[layout]          string                  set div layout to vertical or horizontal
                                  ('vertical','horizontal')
param	 options[valuefield]      string                  field to insert time value, if not same as click field
                                  (name of input field)
param	 options[hoursopacity]	  float					  set opacity of hours container
param 	 options[minutesopacity]  float					  set opacity of minutes container
param	 callback                 function                callback function - gets passed back the time value as a 
														  string
*/

jQuery.fn.clockpick = function(options, callback) {

	var settings = {
		starthour       : 8,
		endhour         : 18,
		showminutes     : true,
		minutedivisions : 4,
		military        : false,
		event           : 'click',
		layout			: 'vertical',
		valuefield		: null,
		hoursopacity	: 1,
		minutesopacity  : 1
		};
		
	if(options) {
		jQuery.extend(settings, options);
	};
	
	var callback = callback || function() { },
	 	v = (settings.layout == 'vertical'); // boolean for vertical, shorten footprint
	errorcheck();	
	
	jQuery(this)[settings.event](function(e) {
		
		var self = this,
		$self = jQuery( this ),
		$body = jQuery( "body" );
		
		if ( !settings.valuefield ) {
			$self.unbind( "keydown" ).bind( "keydown", keyhandler );
		}
		else {
			var inputfield = jQuery("[name=" + settings.valuefield + "]");
			inputfield
				.unbind( "keydown" )
				.bind( "keydown", keyhandler)[0]
				.focus();
			inputfield
				.bind("click", function() { inputfield.unbind("keydown"); } );										
		}
		// clear any malingerers
		jQuery("#CP_hourcont,#CP_minutecont").remove();
		
		// append hourcont to body
		// add class "CP" for mouseout recognition, although there is only
		// one hourcont on the screen at a time
		var $hourcont = jQuery("<div id='CP_hourcont' class='CP' />").appendTo( $body );
		$hourcont.css("opacity",settings.hoursopacity);
		binder( $hourcont );
		
		var $hourcol1 = jQuery("<div class='CP_hourcol' id='hourcol1' />").appendTo( $body );
		var $hourcol2 = jQuery("<div class='CP_hourcol' id='hourcol2' />").appendTo( $body );

		// if showminutes, append minutes cont to body
		if (settings.showminutes) {
			var $mc = jQuery("<div id='CP_minutecont' class='CP' />").appendTo( $body );
			$mc.css("opacity",settings.minutesopacity)
			binder($mc);
		}
		if ( !v ) {
			$hourcont.css("width","auto");
			if (settings.showminutes) {
				$mc.css("width","auto");
			}
		}
		else {
			$hourcol1.addClass('floatleft');
			$hourcol2.addClass('floatleft');
		}
				
		// all the action right here
		// fill in the hours container (minutes rendered in hour mouseover)
		// then make hour container visible
		renderhours();
		putcontainer();
		
		/*----------------------helper functions below-------------------------*/
				
		function renderhours() {
			// fill in the $hourcont div
			var c = 1; 
			// counter as index 2 of hr id, gives us index 
			// in group of hourdivs for calculating where to put minutecont on keydown
			for (var h=settings.starthour; h<=settings.endhour; h++) {
				
				if(h==12) { c = 1; } // reset counter for col 2
				
				var displayhours = ((!settings.military && h > 12) ? h - 12 : h);
				// rectify zero hour
				if (!settings.military && h == 0) {
					displayhours = '12';
				}
				if ( settings.military && h < 10 ) {
					displayhours = '0' + displayhours;
				}
				if ( settings.military ) {
					displayhours += ':00';
				}
				var $hd = jQuery("<div class='CP_hour' id='hr_" + h + "_" + c + "'>" + displayhours + set_tt(h) + "</div>");
				// shrink width a bit if military
				binder($hd);
				if (!v) {
					$hd.css("float","left");
				}
				(h<12) ? $hourcol1.append($hd) : $hourcol2.append($hd);
				c++;
			}
			$hourcont.append($hourcol1);
			!v ? $hourcont.append("<div style='clear:left' />") : '';
			$hourcont.append($hourcol2);
		}
		
		function renderminutes( h ) {
			var realhours = h;
			var displayhours = (!settings.military && h > 12) ? h - 12 : h;
			if (!settings.military && h == 0) {
				displayhours = '12';
			}
			if ( settings.military && h < 10 ) {
				displayhours = '0' + displayhours;
			}
			$mc.empty();
			var n = 60 / settings.minutedivisions,
				tt = set_tt(realhours),
				counter = 1;
		
			for(var m=0;m<60;m=m+n) {
				$md = jQuery("<div class='CP_minute' id='" + realhours + "_" + m + "'>" 
							 + displayhours + ":" + ((m<10) ? "0" : "") + m + tt 
							 + "</div>");
				if ( !v ) {
					$md.css("float","left");
					if (settings.minutedivisions > 6 
						&& counter == settings.minutedivisions / 2 + 1) {
						// long horizontal, kick in extra row after half
						$mc.append("<div style='clear:left' />");
					}
				}
				$mc.append($md);
				binder($md);
				counter++;
			}
		}
		
		function set_tt(realhours) {
			if (!settings.military) { 
				return (realhours >= 12) ? ' PM' : ' AM'; 
				}
			else { 
				return '';
			}
		}
		
		function putcontainer() {
			if ( e.type != 'focus') {
				$hourcont[0].style.left = e.pageX - 5 + 'px';
				$hourcont[0].style.top = e.pageY - (Math.floor($hourcont.height() / 2)) + 'px';
				rectify($hourcont);
			}
			else {
				$self.after($hourcont);
			}
			$hourcont.slideDown('fast');		
		}
		
		function rectify($obj) { 
			// if a div is off the screen, move it accordingly
			var ph = document.documentElement.clientHeight 
						? document.documentElement.clientHeight 
						: document.body.clientHeight;
			var pw = document.documentElement.clientWidth
						? document.documentElement.clientWidth
						: document.body.clientWidth;
			var t = parseInt( $obj[0].style.top );
			var l = parseInt( $obj[0].style.left );
			var st = document.documentElement.scrollTop 
						? document.documentElement.scrollTop 
						: document.body.scrollTop;
			// run off top
			if ( t <= st && !$obj.is("#CP_minutecont") ) {
				$obj.css("top",st+10+'px');
			}
			else if (t + $obj.height() - st > ph) {
				$obj.css("top",st + ph - $obj.height() - 10 + 'px');
			}
			if ( l <= 0 ) {
				$obj.css("left", '10px');
			}
		}
		
		function binder($obj) {
		// all the binding is done here
		// event handlers have been abstracted out,
		// so they can handle mouse or key events
		
			// bindings for hc (hours container)
			if($obj.attr("id") == 'CP_hourcont') {
				$obj.mouseout(function(e) { hourcont_out(e) });
			}
			
			// bindings for mc (minute container)
			else if ($obj.attr("id") == 'CP_minutecont') {
				$obj.mouseout(function(e) { minutecont_out(e) });
			}
			
			// bindings for $hd (hour divs)
			else if ($obj.attr("class") == 'CP_hour') {
				$obj.mouseover(function(e) { hourdiv_over($obj, e) });
				$obj.mouseout(function() { hourdiv_out($obj) });					
				$obj.click(function() {	hourdiv_click($obj) });
			}
			
			// bindings for $md (minute divs)
			else if ($obj.attr("class") == 'CP_minute') {
				$obj.mouseover(function() { minutediv_over($obj) });
				$obj.mouseout(function() { minutediv_out($obj) });					
				$obj.click(function() {	minutediv_click($obj) });
			}
		};
		
		function hourcont_out(e) {
			/*
			this tells divs to clear only if rolling all the way 
			out of hourcont.
			relatedTarget "looks ahead" to see where the mouse
			has moved to on mouseOut.
			IE uses the more sensible "toElement".
			try/catch for Mozilla bug on relatedTarget-input field.
			*/
			try {
				var t = (e.toElement) ? e.toElement : e.relatedTarget;
				if (!(jQuery(t).is("div[class^=CP], iframe"))) {
					// Safari incorrect mouseover/mouseout
					//if (!jQuery.browser.safari) {
						cleardivs();
					//}
				}	
			}
			catch(e) {
				cleardivs();
			}
		}
		
		function minutecont_out(e) {
			try {
				var t = (e.toElement) ? e.toElement : e.relatedTarget;
				if (!(jQuery(t).is("div[class^=CP], iframe"))) {
					cleardivs();
				}		
			}
			catch(e) {
				cleardivs();
			}
		}
		
		function hourdiv_over($obj, e) {
			var h = $obj.attr("id").split('_')[1],
				i = $obj.attr("id").split('_')[2],
				l,
				t;
			$obj.addClass("CP_over");
			if ( settings.showminutes ) {
				$mc.hide();
				renderminutes(h);
				
				// set position & show minutes container
				if (v) {
					t = e.type == 'mouseover'
						? e.pageY - 15
						: $hourcont.offset().top + 2 + ($obj.height() * i);
					if ( h < 12 )
						l = $hourcont.offset().left - $mc.width() - 2;
					else
						l = $hourcont.offset().left + $hourcont.width() + 2;
				}
				else {
					l = (e.type == 'mouseover') 
						? e.pageX - 10 
						: $hourcont.offset().left + ($obj.width()-5) * i;
					if(h<12) {
						t = $hourcont.offset().top - $mc.height() - 2;
					}
					else {
						t = $hourcont.offset().top + $hourcont.height();
					}
				}
				$mc.css("left",l+'px').css("top",t+'px');
				rectify( $mc );
				$mc.show();
			}
			return false;
		}
		
		
		
		function hourdiv_out($obj) {
			$obj.removeClass("CP_over");
			return false;
		}
		
		function hourdiv_click($obj) {
			var h = $obj.attr("id").split('_')[1],
				tt = set_tt(h),
				str = $obj.text();
			if(str.indexOf(' ') != -1) {
				var cleanstr = str.substring(0,str.indexOf(' '));
			}
			else {
				var cleanstr = str;
			}
			$obj.text(cleanstr + ':00' + tt);
			setval($obj);
			cleardivs();
		}
		
		function minutediv_over($obj) {
			$obj.addClass("CP_over");
			return false;
		}
		
		function minutediv_out($obj) {
			$obj.removeClass("CP_over");	
			return false;
		}
		
		function minutediv_click($obj) {
			setval($obj);
			cleardivs();
		}
		
		function setval($obj) { // takes either hour or minute obj
			if(!settings.valuefield) {
				self.value = $obj.text();
			}
			else {
				jQuery("input[name=" + settings.valuefield + "]").val($obj.text());
			}
			callback.apply( $self, [ $obj.text() ]);
			// unbind keydown handler, otherwise it will double-bind if 
			// field is activated more than once
			$self.unbind( "keydown", keyhandler );
		}
		
		function cleardivs() {
			if (settings.showminutes) {
				$mc.hide();
			}
			$hourcont.slideUp('fast');
			$self.unbind( "keydown", keyhandler );
		}
		
		// keyboard handling
		
		function keyhandler( e ) {
			
			// $obj is current active div
			var $obj = $("div.CP_over").size() ? $("div.CP_over") : $("div.CP_hour:first"),
				divtype = $obj.is(".CP_hour") ? 'hour' : 'minute',
				hi = (divtype == 'hour') ? $obj[0].id.split('_')[2] : 0, // hour index
				h = (divtype == 'minute') ? $obj[0].id.split('_')[0] : $obj[0].id.split('_')[1]; // real hour 
			if (divtype == 'minute') 
				{ var curloc = h<12 ? 'm1' : 'm2' }
			else 
				{ var curloc = h<12 ? 'h1' : 'h2' }
			
			function divprev($obj) {
				if ($obj.prev().size()) {
					eval(divtype + 'div_out($obj)');
					eval(divtype + 'div_over($obj.prev(), e)');
				}
				else { return false; }
			}
			
			function divnext($obj) {
				if ($obj.next().size()) {
					eval(divtype + 'div_out($obj)');
					eval(divtype + 'div_over($obj.next(), e)');
				}
				else { return false; }
			}
			
			function hourtohour($obj) {
				var ctx = h>=12 ? '#hourcol1' : '#hourcol2';
				$newobj = jQuery(".CP_hour[id$=_" + hi + "]", ctx );
				if ($newobj.size()) {
					hourdiv_out($obj);
					hourdiv_over($newobj, e);
				}
				else { return false; }
			}
			
			function hourtominute($obj) {
				hourdiv_out($obj);
				minutediv_over($(".CP_minute:first"));
			}
			
			function minutetohour($obj) {
				minutediv_out($obj);
				var ctx = h>=12 ? '#hourcol2' : '#hourcol1';
				// extract hour from minutediv, then find hourdiv with that hour
				var $newobj = jQuery(".CP_hour[id^=hr_" + h + "]", ctx);
				hourdiv_over($newobj, e);
			}

			switch (e.keyCode) {
				case 37: // left arrow
					if (v) {
						switch (curloc) {
							case 'm1':
								return false;
								break;
							case 'm2':
								minutetohour($obj);
								break;
							case 'h1':
								hourtominute($obj);
								break;
							case 'h2':
								hourtohour($obj);
								break;
						}
					}
					else {
						divprev($obj);
					}
					break;
					
				case 38: // up arrow
					if(v) {
						divprev($obj);
					}
					else {
						switch (curloc) {
							case 'm1':
								return false;
								break;
							case 'm2':
								minutetohour($obj);
								break;
							case 'h1':
								hourtominute($obj);
								break;
							case 'h2':
								hourtohour($obj);
								break;
						}
					}
					break;
				case 39: // right arrow
					if (v) {
						switch (curloc) {
							case 'm1':
								minutetohour($obj);
								break;
							case 'm2':
								return false;
								break;
							case 'h1':
								hourtohour($obj);
								break;
							case 'h2':
								hourtominute($obj);
								break;
						}
					}
					else {
						divnext($obj);
					}
					break;
				
				case 40: // down arrow
					if(v) {
						divnext($obj);
					}
					else {
						switch (curloc) {
							case 'm1':
								minutetohour($obj);
								break;
							case 'm2':
								return false;
								break;
							case 'h1':
								hourtohour($obj);
								break;
							case 'h2':
								hourtominute($obj);
								break;
						}
					}
					break;
					
				case 13: // return
					eval(divtype + 'div_click($obj)');
					break;
					
				default:
					return true;
			}
					
		return false;
			
		}

	return false;
	});
	
	function errorcheck() {
		if (settings.starthour >= settings.endhour) {
			alert('Error - start hour must be less than end hour.');
			return false;
		}
		else if (60 % settings.minutedivisions != 0) {
			alert('Error - param minutedivisions must divide evenly into 60.');
			return false;
		}
	}
	
	return this;

}

/*
+-----------------------------------------------------------------------+
| Copyright (c) 2007-2013 Josh Nathanson                  |
| All rights reserved.                                                  |
|                                                                       |
| Redistribution and use in source and binary forms, with or without    |
| modification, are permitted provided that the following conditions    |
| are met:                                                              |
|                                                                       |
| o Redistributions of source code must retain the above copyright      |
|   notice, this list of conditions and the following disclaimer.       |
| o Redistributions in binary form must reproduce the above copyright   |
|   notice, this list of conditions and the following disclaimer in the |
|   documentation and/or other materials provided with the distribution.|
|                                                                       |
| THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
| "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
| LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
| A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
| OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
| SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
| LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
| DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
| THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
| (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
| OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
|                                                                       |
+-----------------------------------------------------------------------+
*/
