/** 
 * @package     Minima
 * @author      Henrik Hussfelt, Marco Barbosa
 * @copyright   Copyright (C) 2010 Marco Barbosa. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

var MinimaPanelClass = new Class({
    Implements: [Options],

    panelStatus: {
        'true': 'active',
        'false': 'inactive'
    },

    panel: null,

    options: {
        prev: '',
        next: '',
        panelList: '',
        panelPage: '',
        panelWrapper: '',
        toIncrement: 0,
        increment: 900
    },

    // minima node
    minima: null,

    // some private variables
    maxRightIncrement: null,
    panelSlide: null,
    numberOfExtensions: null,

    initialize: function(options){
        
        // set the main node for DOM selection
        this.minima = document.id(this.options.minima) || document.id('minima');
        
        // Set options
        this.setOptions(options);

        // Create a slide in this class scope
        this.panel = new Fx.Slide.Mine(this.options.panelWrapper, {
            mode: "vertical",
            transition: Fx.Transitions.Pow.easeOut
        }).hide();

        // Only execute code for tweening if there is a next-button
        if (this.options.next) {
            // Create the panel slide tween function
            this.panelSlide = new Fx.Tween( this.options.panelList, {duration: 500, transition: 'back:in:out'} );
            // how many extensions do we have
            this.numberOfExtensions = this.options.panelList.getElements("li").length;
            // increase the width basing on numberOfExtensions, allways divide by 9 because we have 9 extensions per page
            this.options.panelList.setStyle("width", Math.ceil(this.numberOfExtensions / 9) * this.options.increment );
            // dynamic max incrementation size (it depends on how many elements)
            //this.maxRightIncrement = ( this.options.increment * -( Math.ceil(this.numberOfExtensions / 9) ) ) + 900;
            this.maxRightIncrement = -Math.ceil(this.options.panelPage.getChildren().length*this.options.increment-this.options.increment);
            // Initiate showbuttons
            this.showButtons();
        };
    },

    doPrevious: function () {
        if(this.options.toIncrement < 0) {
            this.options.next.show();
            this.options.toIncrement += this.options.increment;
            this.panelSlide.pause();
            this.panelSlide.start('margin-left', this.options.toIncrement);
            // fix pagination
            this.options.panelPage.getFirst('.current').removeClass('current').getPrevious('li').addClass('current');
            // hide buttons if needed
            this.showButtons();
        }
    },

    doNext: function () {
        if(this.options.toIncrement > this.maxRightIncrement) {
            // Show previous
            this.options.prev.show();
            // Count what to increment
            this.options.toIncrement -= this.options.increment;
            // Paus slider
            this.panelSlide.pause();
            // Change marign
            this.panelSlide.start('margin-left', this.options.toIncrement);
            // fix pagination
            this.options.panelPage.getFirst('.current').removeClass('current').getNext('li').addClass('current');
            // hide buttons if needed
            this.showButtons();
        };
    },

    changeToPage: function(el) {
        // Get the page number
        var pageNumber = el.id.substr("panel-pagination-".length);
        // Paus the slidefunciton
        this.panelSlide.pause();
        // Change global toIncrement value
        this.options.toIncrement = Math.ceil(0-this.options.increment*pageNumber);
        // Execute slide
        this.panelSlide.start('margin-left', this.options.toIncrement);
        // Remove previous current class
        this.options.panelPage.getFirst('.current').removeClass('current');
        el.addClass('current');
        this.showButtons();
    },

    showButtons: function() {
        if (this.options.toIncrement == 0) {
            this.options.prev.hide();
        } else {
            this.options.prev.show();
        };
        if (this.options.toIncrement == this.maxRightIncrement) {
            this.options.next.hide();
        } else {
            this.options.next.show();
        };
    }
});