// JavaScript Document

window.addEvent('domready',function() {
	/* smooth */
	new SmoothScroll({duration:500});
	
	/* link management */
	$('gototop').set('opacity','0').setStyle('display','block');
	
	/* scrollspy instance */
	var ss = new ScrollSpy({
		min: 200,
		onEnter: function(position,state,enters) {
			$('gototop').fade('in');
		},
		onLeave: function(position,state,leaves) {
			$('gototop').fade('out');
		},
		container: window
	});
});