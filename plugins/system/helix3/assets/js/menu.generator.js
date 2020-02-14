/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

jQuery(function($) {

  $('#attrib-spmegamenu').find('.control-group').first().find('.control-label').remove();
  $('#attrib-spmegamenu').find('.control-group').first().find('>.controls').removeClass().addClass('megamenu').unwrap();

  //Section Sortable callbck
  $.fn.sectionSort = function(){
    $(this).sortable({
      items: ".menu-section",
      placeholder: "menu-section-state-highlight",
      forcePlaceholderSize: true,
      opacity: 0.8,
      handle: ".row-move",
      distance: 0.5,
      tolerance: 'pointer',
      start: function(event, ui) {
        ui.placeholder.height(ui.item.height());
      }}).disableSelection();
    };

    $.fn.columnSort = function(){
      $(this).sortable({
        items: '.column',
        placeholder: 'ui-state-highlight',
        opacity: 0.8,
        dropOnEmpty: true,
        distance: 0.5,
        tolerance: 'pointer',
        start: function(event, ui) {
          var plus;
          if(ui.item.hasClass('sp-col-sm-1')) plus = 'sp-col-sm-1'; else
          if(ui.item.hasClass('sp-col-sm-2')) plus = 'sp-col-sm-2'; else
          if(ui.item.hasClass('sp-col-sm-3')) plus = 'sp-col-sm-3'; else
          if(ui.item.hasClass('sp-col-sm-4')) plus = 'sp-col-sm-4'; else
          if(ui.item.hasClass('sp-col-sm-5')) plus = 'sp-col-sm-5'; else
          if(ui.item.hasClass('sp-col-sm-7')) plus = 'sp-col-sm-7'; else
          if(ui.item.hasClass('sp-col-sm-8')) plus = 'sp-col-sm-8'; else
          if(ui.item.hasClass('sp-col-sm-9')) plus = 'sp-col-sm-9'; else
          if(ui.item.hasClass('sp-col-sm-10')) plus = 'sp-col-sm-10'; else
          if(ui.item.hasClass('sp-col-sm-11')) plus = 'sp-col-sm-11'; else
          if(ui.item.hasClass('sp-col-sm-12')) plus = 'sp-col-sm-12'; else
          plus = 'sp-col-sm-6';
          ui.placeholder.addClass(plus);
          ui.placeholder.height(ui.item.height());
        }}).disableSelection();
      };

      function mdouleSortable(){
        $('.modules-container').sortable({
          connectWith: '.modules-container',
          items: '.draggable-module',
          placeholder: 'ui-state-highlight',
          opacity: 0.8,
          dropOnEmpty: true,
          distance: 0.5,
          tolerance: 'pointer'
        });
      };

      $('#megamenulayout').sectionSort();
      $('.spmenu').columnSort();

      mdouleSortable();

      $('.modules-list').find('.draggable-module').draggable({
        connectToSortable: '.modules-container',
        items: '.draggable-module',
        helper: 'clone',
        stop: function( event, ui ) {
          mdouleSortable();
          ui.helper.removeAttr('style');
        }
      });

      // Menu Width
      $('#menuWidth').change(function(){
        var width = $('#menuWidth').val();
        if(width >= 200){
          $('#megamenulayout').css('width',width)
          .data('width',width);
        }else{
          alert("Width can't be less than 200 Pixels");
          $('#menuWidth').val($('#megamenulayout').data('width'));
        }
      });

      // Mega menu alignment
      $('.action-bar').on('click','.alignment',function(event){
        event.preventDefault();

        var $that = $(this);
        $('.alignment').removeClass('active');
        $that.addClass('active');
        $('#megamenulayout').data('menu_align',$(this).data('al_flag'));
      });

      //Modal
      $(document).on('click','.add-layout',function(event){
        event.preventDefault();
        $('#layout-modal').spmodal();
      });

      //Remove Module
      $(document).on('click', '.modules-container .fa-remove', function(event) {
        event.preventDefault();
        $(this).closest('.draggable-module').fadeOut(400).delay(400, function(){
          $(this).remove();
        });
      });

      $('.layout-reset').on('click', function(event) {
        event.preventDefault();
        var $that = $(this);

        var data = {
          action   : 'resetLayout',
          layoutName : $that.data('current_item')
        };

        var request = {
          'option' : 'com_ajax',
          'plugin' : 'helix3',
          'data'   : data,
          'format' : 'raw'
        };

        $.ajax({
          type   : 'POST',
          data   : request,
          dataType: "html",
          success: function (response) {
            if (response) {
              $('#megamenulayout').find('.menu-section').remove();
              $('#megamenulayout').append(response);

              // sorting layout
              $('#megamenulayout').sectionSort();
              $('.spmenu').columnSort();
              mdouleSortable();
            }
          }
        });
      });

      // new layout generator
      $(document).on('click', '#layout-modal a', function(event){
        event.preventDefault();

        if($(this).hasClass('active')){
          return;
        }

        var $that           = $(this),
        newLayoutData   = $that.data('layout'),
        layoutDesign    = $that.data('design'),
        $parent         = $('#layout-modal'),
        oldLayout       = $('#megamenulayout').data('menu_item'),
        newLayout       = [12];

        if(newLayoutData != 12 ){
          newLayout = newLayoutData.split(',');
        }

        if (newLayout.length !== 1 && newLayout.length < oldLayout) {
          alert("You can't add small layout than default layout");
          return;
        }

        var colHtml = [];

        var designString = $('#'+layoutDesign).html();

        $('#megamenulayout').find('.column-items-wrap').each(function(i,val){
          var $that = $(this);
          colHtml[i] = this.innerHTML;
        });

        if (!String.prototype.format) {
          String.prototype.format = function() {
            var args = arguments;
            return this.replace(/{(\d+)}/g, function(match, number) {
              return typeof args[number] != 'undefined'
              ? args[number]
              : '<div class="modules-container"></div>'
              ;
            });
          };
        }

        if (newLayout.length === 1) {
          var html = '';
          for (var i = 0; i < colHtml.length; i++) {

            html += colHtml[i];
          }
          var newLayoutHtml = designString.format(html);
        }else{
          var newLayoutHtml = designString.format(colHtml[0],colHtml[1],colHtml[2],colHtml[3],colHtml[4],colHtml[5]);
        }

        // Manage Modal layout
        $parent.find('.active').removeClass('active');
        $parent.spmodal('hide');
        $(this).addClass('active');

        var $oldLayoutHtml = $('#megamenulayout').find('.menu-section');

        $oldLayoutHtml.remove();
        $('#megamenulayout').append(newLayoutHtml);

        if (newLayout.length === 1) {
          $('#megamenulayout').find('.modules-container').remove();
          $('#megamenulayout').find('.column-items-wrap').append('<div class="modules-container"></div>');
        }

        $('#megamenulayout').sectionSort();
        $('.spmenu').columnSort();
        mdouleSortable();

      });

      document.adminForm.onsubmit = function(event){
        var layout = [];

        // Get each row data;
        $('#megamenulayout').find('.spmenu').each(function(index) {
          var $row = $(this),
          rowIndex = index;
          layout[rowIndex] = {
            'type'      : 'row',
            'attr'      : []
          };

          // Get each column data;
          $row.find('.column').each(function(index) {
            var $column = $(this),
            colIndex = index,
            colGrid = $column.data('column');

            layout[rowIndex].attr[colIndex] = {
              'type'          : 'column',
              'colGrid'       : colGrid,
              'menuParentId'  : '',
              'moduleId'      : ''
            };

            // get current child id
            var menuParentId = '';

            $column.find('h4').each(function(index, el) {
              menuParentId += $(this).data('current_child')+',';
            });

            if (menuParentId) {
              menuParentId = menuParentId.slice(',',-1);
              layout[rowIndex].attr[colIndex].menuParentId = menuParentId;
            }

            // get modules id
            var moduleId = '';
            $column.find('.draggable-module').each(function(index, el) {
              moduleId += $(this).data('mod_id')+',';
            });

            if(moduleId){
              moduleId = moduleId.slice(',',-1);
              layout[rowIndex].attr[colIndex].moduleId = moduleId;
            }
          });
        });

        var initData = $('#megamenulayout').data();

        var menumData = {
          'width'         : initData.width,
          'menuItem'      : initData.menu_item,
          'menuAlign'     : initData.menu_align,
          'layout'        : layout
        };

        var megamenu = 0,
        mega_lenght = $('#megamenulayout').find('.column').length;

        if (mega_lenght > 1) {
          megamenu = 1;
        }

        $('#jform_params_megamenu').val(megamenu);
        $('#jform_params_menulayout').val( JSON.stringify(menumData) );
      }
    });
