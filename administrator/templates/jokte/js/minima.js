/** 
 * @package			Minima
 * @author			Marco Barbosa
 * @contributors	Henrik Hussfelt
 * @copyright		Copyright (C) 2010 Marco Barbosa. All rights reserved.
 * @license			GNU General Public License version 2 or later; see LICENSE.txt
 */

// EXTRAS
// ==================================================
// outerClick function
(function(){var b;var a=function(f){var d=$(f.target);var c=d.getParents();b.each(function(g){var e=g.element;if(e!=d&&!c.contains(e)){g.fn.call(e,f)}})};Element.Events.outerClick={onAdd:function(c){if(!b){document.addEvent("click",a);b=[]}b.push({element:this,fn:c})},onRemove:function(c){b=b.filter(function(d){return d.element!=this||d.fn!=c},this);if(!b.length){document.removeEvent("click",a);b=null}}}})();

// switchClass function
Element.implement('switchClass', function(a, b){ var toggle = this.hasClass(a); this.removeClass(toggle ? a : b).addClass(toggle ? b : a); return this; });

// extending Selector for a visible boolean
$extend(Selectors.Pseudo,{visible:function(){if(this.getStyle("visibility")!="hidden"&&this.isVisible()&&this.isDisplayed()){return this}}});

// toggle for reveal or dissolve
Element.implement('toggleReveal', function(el, options) {
    return el.isDisplayed() ? el.dissolve(options) : el.reveal(options);
});

// PLUGINS
// ==================================================
// ScrollSpy by David Walsh (http://davidwalsh.name/js/scrollspy)
var ScrollSpy=new Class({Implements:[Options,Events],options:{container:window,max:0,min:0,mode:"vertical"},initialize:function(a){this.setOptions(a);this.container=document.id(this.options.container);this.enters=this.leaves=0;this.inside=false;this.listener=function(d){var b=this.container.getScroll(),c=b[this.options.mode=="vertical"?"y":"x"];if(c>=this.options.min&&(this.options.max==0||c<=this.options.max)){if(!this.inside){this.inside=true;this.enters++;this.fireEvent("enter",[b,this.enters,d])}this.fireEvent("tick",[b,this.inside,this.enters,this.leaves,d])}else{if(this.inside){this.inside=false;this.leaves++;this.fireEvent("leave",[b,this.leaves,d])}}this.fireEvent("scroll",[b,this.inside,this.enters,this.leaves,d])};this.addListener()},start:function(){this.container.addEvent("scroll",this.listener.bind(this))},stop:function(){this.container.removeEvent("scroll",this.listener.bind(this))},addListener:function(){this.start()}});

// DOMREADY
window.addEvent('domready', function() {

    // Initiate some global variables
    // -------------------------------     
    var 
        // get the language strings
        language        = MooTools.lang.get('Minima');
        // DOM variables                
        contentTop      = $('content-top'),
        toolbar         = $('toolbar'),
        topHead         = $('tophead'),
        minima          = $('minima'),
        subMenu         = $('submenu'),
        itemForm        = $('item-form'),
        filterBar       = $('filter-bar'),
        // Initiate MimimaClass
        Minima          = new MinimaClass({},{
                                  systemMessage : $('system-message'),
                                  jformTitle    : $('jform_title')
                              }),
        // Initiate MinimaToolbar
        MinimaToolbar   = new MinimaToolbarClass({
                                  'toolbar'         : toolbar, // toolbar parent
                                  'toolbarElements' : minima.getElements('.toolbar-list li a'), // list of the anchor elements
                                  'label'           : language['actionBtn']
                              }),
        MinimaFilterBar = new MinimaFilterBarClass({}, {
                                  'filterBar' : filterBar
                              }, {
                                  'hideFilter' : language['hideFilter'],
                                  'showFilter' : language['showFilter']
                              })
    ;

    // ------------------------------- 

    // TRIGGERS
    // =============================    
    // smooth scroll when clicking "back to top"
    new Fx.SmoothScroll({
        links: '#topLink'
    });    

    // fix the filterbar
    if (filterBar) {        
        MinimaFilterBar.doFilterBar();
    }

    // Show system message (if applicable)
    Minima.showSystemMessage();

    // make title dynamic if we have one
    if ($('jform_title')) {
        Minima.dynamicTitle();
    };

    // Make whole row clickable, if there are any    
    if (minima.getElements('.adminlist').length) {
    	Minima.makeRowsClickable();
    };

    // TOOLBAR
    // =============================
    // fix the toolbar
    MinimaToolbar.doToolbar();

    // show it afterwards
    if (toolbar) {
        toolbar.show();
    };

    // TABS
    // =============================
    if (subMenu && itemForm) {
        
        // Start tabs actions, create instances of class
    	var MinimaTabsHorizontal = new MinimaTabsClass({}, {
                'tabs'    : subMenu.getElements('a'), 
                'content' : itemForm.getChildren('div')
            }),
        MinimaTabsVertical = new MinimaTabsClass({}, {
                'tabs'    : minima.getElements('.vertical-tabs a'), 
                'content' : $('tabs').getChildren('.panelform')
            });

        if (subMenu.hasClass('out')) {
            MinimaTabsHorizontal.moveTabs(subMenu);   
        }

    	// Add tabs for horizontal submenu
        // Hide all content elements
        MinimaTabsHorizontal.hideAllContent();
        // Show the first
        MinimaTabsHorizontal.showFirst();
        // Add onClick
        MinimaTabsHorizontal.addTabsAction();

        // Add tabs for vertical menu
        // Hide all content elements
        MinimaTabsVertical.hideAllContent();
        // Show the first
        MinimaTabsVertical.showFirst();
        // Add onClick
        MinimaTabsVertical.addTabsAction();
    };    

    // individual tabs, not necessairly in a form
    if (subMenu && subMenu.hasClass('out')) {
        subMenu.inject( $('content'), 'top' );
    }

    // SCROLLING
    // =============================
    // fixed content-box header when scrolling    
    var scrollSize = document.getScrollSize().y - document.getSize().y;
    
    /* scrollspy instance */    
    new ScrollSpy({
        // the minimum ammount of scrolling before it triggers
        min: 200, 
        onEnter: function() {            
            // we are in locked mode, must fix positioning
            if (scrollSize > 400) {
                if (document.body.hasClass('locked')) {
                    contentTop.setStyle('left', (topHead.getSize().x - 1140) / 2);
                };
                contentTop.setStyle('width', topHead.getSize().x - 40).addClass('fixed');
            };
        },
        onLeave: function() {
            if (scrollSize > 400) {
                contentTop.removeClass('fixed');
                if(document.body.hasClass('locked')) {
                    contentTop.setStyle('width', '100%');
                };
            };
        }
    }); 
    
    // PANEL TAB
    // ==================================================
    // tabs wrapper
    var $panelWrapper = $('panel-wrapper'),
        extra        = $('more')
        extraLists   = $('list-content'),
        openPanel    = $('panel-tab'),
        listWrapper  = $('list-wrapper');

    if ($panelWrapper) {

	    // Fixing wrapper bug
	    Fx.Slide.Mine = new Class({
	        Extends: Fx.Slide,
	        initialize: function(el, options) {
	            this.parent(el, options);
	            this.wrapper = this.element.getParent();
	        }
	    });

        // cache elements
        var $panelPagination = $('panel-pagination'),
            $prev            = $('prev'),
            $next            = $('next');

		// Create a Panel instance
		var Panel = new MinimaPanelClass({
				panelWrapper: $panelWrapper,
				prev: $prev,
				next: $next,
				panelList: $('panel-list'),
				panelPage: $panelPagination
		});

		// Setup click event for previous
		$prev.addEvent('click', function() {
			Panel.doPrevious();
		});
		// Setup click event for previous
		$next.addEvent('click', function() {
			Panel.doNext();
		});

		// Fix panel pagination
		$panelPagination.getChildren("li").addEvent('click', function() {
			// Send ID to changepage as this contains pagenumber
			Panel.changeToPage(this);
		});

        // Open the panel slide
        openPanel.addEvents({
            'click': function(){                
                //minima.getElements("#shortcuts .parent").getChildren('.sub').dissolve({duration: 200}).removeClass('hover');
                minima.getElements("#shortcuts .parent").removeClass('hover');
        		Panel.panel.toggle();
            }
        });

        // change status on toggle complete
        Panel.panel.addEvent('complete', function() {
            openPanel.set('class', Panel.panelStatus[Panel.panel.open]);
        });

        // slide up panel when clicking a link
        minima.getElements('#panel-list li').addEvent('click', function(){            
            Panel.panel.toggle();
        });

    }; // end of if($panelWrapper)


    // dropdown menu
    if (extra) {
        extra.addEvent('click', function(){            
            this.switchClass('active','inactive');            
            //extraLists.toggle();
            this.toggleReveal(extraLists, {heightOverride: '155',duration: 250});
        });
    }

    var hideLists = function() {
        extra.set('class','inactive');
        listWrapper.removeClass('active');
        extraLists.dissolve();            
    };

    // turn off list when click outside
    if (listWrapper) {
        listWrapper.addEvent('outerClick', function(){
            hideLists();
        });
    }

    // turn off list when clicking a link
    if (extraLists) {
        extraLists.getElements("a").addEvent('click', function(){
            hideLists();
        });
    }

    minima.getElements('#shortcuts .parent').each(function(li) {             
        // add events to the list elements
        li.addEvents({
           'click' : function() {                    
                // show or hide when click on the arrow                    
                this.toggleReveal(this.getChildren('.sub')[0]).toggleClass('hover');                    
                this.getElement('a').toggleClass('hover');
           },
           'outerClick' : function() {
                // hide when clicking outside or on a different element                    
                this.getChildren('.sub').dissolve({duration: 200}).removeClass('hover');
                this.getElement('a').removeClass('hover');
           }
        });            
    });

    // dashboard icons actions
    //if (minima.hasClass('com_cpanel')) {        
        // minima.getElements('.box-icon').addEvent('click', function() {        
        //     this.toggleClass('hover').getParent('nav').toggleReveal(this.getNext('ul'), { duration: 200});
        // });
        // minima.getElements('.box-icon').addEvent('outerClick', function(){
            //this.toggleClass('hover').getParent('nav').dissolve();
            //this.getParent('nav').getNext('ul').dissolve();
        // }); 
    // }
/*
    var cleanSelectedRows = function() {        
        minima.getElements('td.selected').removeClass('selected');
    }

    minima.getElements('.adminlist thead th').addEvents({        
        mouseenter : function() {
            var nColumn = this.getAllPrevious('th').length + 1;
            if (nColumn > 1) {
                cleanSelectedRows();
                minima.getElements('.adminlist td:nth-child('+nColumn+')').addClass('selected');            
            }
        },
        mouseleave : function() {
            cleanSelectedRows();            
        }
    });
*/
});
