// JavaScript Document
/* scroll spy plugin / class */
var ScrollSpy = new Class({
	
	/* implements */
	Implements: [Options,Events],

	/* options */
	options: {
		min: 0,
		mode: 'vertical',
		max: 0,
		container: window,
		onEnter: $empty,
		onLeave: $empty,
		onTick: $empty
	},
	
	/* initialization */
	initialize: function(options) {
		/* set options */
		this.setOptions(options);
		this.container = $(this.options.container);
		this.enters = this.leaves = 0;
		this.max = this.options.max;
		
		/* fix max */
		if(this.max == 0) 
		{ 
			var ss = this.container.getScrollSize();
			this.max = this.options.mode == 'vertical' ? ss.y : ss.x;
		}
		/* make it happen */
		this.addListener();
	},
	
	/* a method that does whatever you want */
	addListener: function() {
		/* state trackers */
		this.inside = false;
		this.container.addEvent('scroll',function() {
			/* if it has reached the level */
			var position = this.container.getScroll();
			var xy = this.options.mode == 'vertical' ? position.y : position.x;
			/* if we reach the minimum and are still below the max... */
			if(xy >= this.options.min &amp;&amp; xy <= this.max) {
					/* trigger Enter event if necessary */
					if(!this.inside) {
						/* record as inside */
						this.inside = true;
						this.enters++;
						/* fire enter event */
						this.fireEvent('enter',[position,this.enters]);
					}
					/* trigger the "tick", always */
					this.fireEvent('tick',[position,this.inside,this.enters,this.leaves]);
			}
			else {
				/* trigger leave */
				if(this.inside) 
				{
					this.inside = false;
					this.leaves++;
					this.fireEvent('leave',[position,this.leaves]);
				}
			}
		}.bind(this));
	}
});

/* usage */
window.addEvent('domready',function() {
	/* smooth */
	new SmoothScroll({duration:500});
	
	/* link management */
	$('gototop').set('opacity','0').setStyle('display','block');
	
	/* scrollspy instance */
	var ss = new ScrollSpy({
		min: 200,
		onEnter: function(position,enters) {
			if(console) { console.log('Entered [' + enters + '] at: ' + position.x + ' / ' + position.y); }
			$('gototop').fade('in');
		},
		onLeave: function(position,leaves) {
			if(console) { console.log('Left [' + leaves + '] at: ' + position.x + ' / ' + position.y); }
			$('gototop').fade('out');
		},
		onTick: function(position,state,enters,leaves) {
			if(console) { console.log('Tick  [' + enters + ', ' + leaves + '] at: ' + position.x + ' / ' + position.y); }
		},
		container: window
	});
});