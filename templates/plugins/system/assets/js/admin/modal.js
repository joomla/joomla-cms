/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

jQuery(function ($) {

  $.fn.helixUltimateModal = function (options) {
    var options = $.extend({
      target_type: '',
      target: ''
    }, options);

    $('.helix-ultimate-modal-overlay, .helix-ultimate-modal').remove();

    var mediaModal = '<div class="helix-ultimate-modal-overlay"></div>';
    mediaModal += '<div class="helix-ultimate-modal" data-target_type="' + options.target_type + '" data-target="' + options.target + '">';

    mediaModal += '<div class="helix-ultimate-modal-header">';
    mediaModal += '<a href="#" class="action-helix-ultimate-modal-close"><span class="fa fa-times"></span></a>'
    mediaModal += '<input type="file" id="helix-ultimate-file-input" accept="image/png, image/jpg, image/jpeg, image/gif, image/svg+xml, image/x-icon" style="display:none;" multiple>';
    mediaModal += '<div class="helix-ultimate-modal-breadcrumbs"></div>';

    mediaModal += '<div class="helix-ultimate-modal-actions-left">'
    mediaModal += '<a href="#" class="btn btn-success btn-xs helix-ultimate-modal-action-select"><span class="fa fa-check"></span> Select</a>'
    mediaModal += '<a href="#" class="btn btn-secondary btn-xs helix-ultimate-modal-action-cancel"><span class="fa fa-times"></span> Cancel</a>'
    mediaModal += '<a href="#" class="btn btn-danger btn-xs btn-last helix-ultimate-modal-action-delete"><span class="fa fa-minus-circle"></span> Delete</a>'
    mediaModal += '</div>';

    mediaModal += '<div class="helix-ultimate-modal-actions-right">'
    mediaModal += '<a href="#" class="btn btn-success btn-xs helix-ultimate-modal-action-upload"><span class="fa fa-upload"></span> Upload</a>'
    mediaModal += '<a href="#" class="btn btn-primary btn-xs btn-last helix-ultimate-modal-action-new-folder"><span class="fa fa-plus"></span> New Folder</a>'
    mediaModal += '</div>';
    mediaModal += '</div>';

    mediaModal += '<div class="helix-ultimate-modal-inner">';
    mediaModal += '<div class="helix-ultimate-modal-preloader"><span class="fa fa-spinner fa-pulse fa-spin fa-3x fa-fw"></span></div>';
    mediaModal += '</div>';
    mediaModal += '</div>';

    $('body').addClass('helix-ultimate-modal-open').append(mediaModal);
  }

  $.fn.helixUltimateOptionsModal = function (options) {
    var options = $.extend({
      target: '',
      title: 'Options',
      flag: '',
      class: ''
    }, options);

    $('.helix-ultimate-options-modal-overlay, .helix-ultimate-options-modal').remove();

    var optionsModal = '<div class="helix-ultimate-options-modal-overlay"></div>';
    optionsModal += '<div class="helix-ultimate-options-modal ' + options.class + '" data-target="#' + options.target + '">';

    optionsModal += '<div class="helix-ultimate-options-modal-header">';
    optionsModal += '<span class="helix-ultimate-options-modal-header-title">' + options.title + '</span>';
    optionsModal += '<a href="#" class="action-helix-ultimate-options-modal-close"><span class="fa fa-times"></span></a>'
    optionsModal += '</div>';

    optionsModal += '<div class="helix-ultimate-options-modal-inner">';
    optionsModal += '<div class="helix-ultimate-options-modal-content">';
    optionsModal += '</div>';
    optionsModal += '</div>';

    optionsModal += '<div class="helix-ultimate-options-modal-footer">';
    optionsModal += '<a href="#" class="btn btn-success btn-xs helix-ultimate-settings-apply" data-flag="' + options.flag + '"><span class="fa fa-check"></span> Apply</a>'
    optionsModal += '<a href="#" class="btn btn-secondary btn-xs helix-ultimate-settings-cancel"><span class="fa fa-times"></span> Cancel</a>'
    optionsModal += '</div>';

    optionsModal += '</div>';

    $('body').addClass('helix-ultimate-options-modal-open').append(optionsModal);
  }
});
