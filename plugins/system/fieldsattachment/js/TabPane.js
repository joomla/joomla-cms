/*
---
description: TabPane Class 

license: MIT-style

authors: akaIDIOT

version: 0.1

requires:
  core/1.2.4:
  - Class
  - Class.Extras 
  - Element 
  - Element.Event
  - Selectors
  more/1.2.4:
  - Element.Delegation

provides: TabPane
...
*/

var TabPane = new Class({
    
    Implements: [Events, Options],

    options: {
        tabSelector: '.tab',
        contentSelector: '.content',
        activeClass: 'active'
    },

    container: null,
    showNow: false,

    initialize: function(container, options) {
        this.setOptions(options);

        this.container = document.id(container);
        this.container.getElements(this.options.contentSelector).setStyle('display', 'none');

        this.container.addEvent('click:relay(' + this.options.tabSelector + ')', function(event, tab) {
            this.showTab(this.container.getElements(this.options.tabSelector).indexOf(tab), tab);
        }.bind(this));

        $$(".tab").addEvent('mouseover',function() {
            /* execute whatever you want, but the pointer cursor is added */
             this.setStyle('cursor','pointer');
          });

        this.container.getElement(this.options.tabSelector).addClass(this.options.activeClass);
        this.container.getElement(this.options.contentSelector).setStyle('display', 'block');
    },

    showTab: function(index, tab) {
        var content = this.container.getElements(this.options.contentSelector)[index];
        if (!tab) {
            tab = this.container.getElements(this.options.tabSelector)[index];
        }

        if (content) {
            this.container.getElements(this.options.tabSelector).removeClass(this.options.activeClass);
            this.container.getElements(this.options.contentSelector).setStyle('display', 'none');
            tab.addClass(this.options.activeClass);
            content.setStyle('display', 'block');
            this.fireEvent('change', index);
        } 
    },

    closeTab: function(index) {
        var tabs     = this.container.getElements(this.options.tabSelector);
        var selected = tabs.indexOf(this.container.getElement('.' + this.options.activeClass)); // is always equals to index 
        
        tabs[index].destroy();
        this.container.getElements(this.options.contentSelector)[index].destroy();
        this.fireEvent('close', index);

        // 'intelligently' selecting a tab is sadly not possible, the tab has already been switched before this method is called 
        this.showTab(index == tabs.length - 1 ? selected - 1 : selected);
    }

});
