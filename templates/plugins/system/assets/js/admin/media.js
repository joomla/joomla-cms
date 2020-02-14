/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

jQuery(function ($) {

	// Media
	$('.helix-ultimate-media-picker').on('click', function (e) {
		e.preventDefault();
		var self = this;
		var target_type = 'id';
		var target = '';

		if (typeof $(this).data('id') != 'undefined') {
			target_type = 'id';
			target = $(this).data('id');
		} else if (typeof $(this).data('target') != 'undefined') {
			target_type = 'data';
			target = $(this).data('target');
		}

		$(this).helixUltimateModal({
			target_type: target_type,
			target: target
		});

		var request = {
			'action': 'view-media',
			'option': 'com_ajax',
			'helix': 'ultimate',
			'request': 'task',
			'format': 'json'
		};

		$.ajax({
			type: 'POST',
			data: request,
			beforeSend: function () {
				$(self).find('.fa').removeClass('fa-picture-o').addClass('fa-spinner fa-spin');
			},
			success: function (response) {
				var data = $.parseJSON(response);
				$(self).find('.fa').removeClass('fa-spinner fa-spin').addClass('fa-picture-o');
				if (data.status) {
					$('.helix-ultimate-modal-breadcrumbs').html(data.breadcrumbs);
					$('.helix-ultimate-modal-inner').html(data.output);
				} else {
					$('.helix-ultimate-modal-overlay, .helix-ultimate-modal').remove();
					$('body').addClass('helix-ultimate-modal-open');
					alert(data.output);
				}
			},
			error: function () {
				alert('Somethings wrong, Try again');
			}
		});
	});

	$(document).on('dblclick', '.helix-ultimate-media-folder', function (e) {
		e.preventDefault();
		var self = this;

		var request = {
			'action': 'view-media',
			'option': 'com_ajax',
			'helix': 'ultimate',
			'request': 'task',
			'path': $(self).data('path'),
			'format': 'json'
		};

		$.ajax({
			type: 'POST',
			data: request,
			beforeSend: function () {
				$('.helix-ultimate-media-selected').removeClass('helix-ultimate-media-selected');
				$('.helix-ultimate-modal-actions-left').hide();
				$('.helix-ultimate-modal-actions-right').show();
				$('.helix-ultimate-modal-inner').html('<div class="helix-ultimate-modal-preloader"><span class="fa fa-spinner fa-pulse fa-spin fa-3x fa-fw"></span></div>');
			},
			success: function (response) {
				var data = $.parseJSON(response);
				if (data.status) {
					$('.helix-ultimate-modal-breadcrumbs').html(data.breadcrumbs);
					$('.helix-ultimate-modal-inner').html(data.output);
				} else {
					alert(data.output);
				}
			},
			error: function () {
				alert('Somethings wrong, Try again');
			}
		});
	});

	$(document).on('click', '.helix-ultimate-media-breadcrumb-item > a', function (e) {
		e.preventDefault();
		var self = this;

		var request = {
			'action': 'view-media',
			'option': 'com_ajax',
			'helix': 'ultimate',
			'request': 'task',
			'path': $(self).data('path'),
			'format': 'json'
		};

		$.ajax({
			type: 'POST',
			data: request,
			beforeSend: function () {
				$('.helix-ultimate-modal-inner').html('<div class="helix-ultimate-modal-preloader"><span class="fa fa-spinner fa-pulse fa-spin fa-3x fa-fw"></span></div>');
			},
			success: function (response) {
				var data = $.parseJSON(response);
				if (data.status) {
					$('.helix-ultimate-modal-breadcrumbs').html(data.breadcrumbs);
					$('.helix-ultimate-modal-inner').html(data.output);
				} else {
					alert(data.output);
				}
			},
			error: function () {
				alert('Somethings wrong, Try again');
			}
		});
	});

	$(document).on('click', '.helix-ultimate-media-folder, .helix-ultimate-media-image', function (event) {
		event.preventDefault();
		$('.helix-ultimate-media-selected').removeClass('helix-ultimate-media-selected');
		$(this).addClass('helix-ultimate-media-selected');
		if ($(this).hasClass('helix-ultimate-media-folder')) {
			$('.helix-ultimate-modal-action-select').hide();
		} else {
			$('.helix-ultimate-modal-action-select').removeAttr('style');
		}
		$('.helix-ultimate-modal-actions-left').show();
		$('.helix-ultimate-modal-actions-right').hide();
	});

	$(document).on('click', '.helix-ultimate-modal-action-select', function (event) {
		event.preventDefault();
		var value = $('.helix-ultimate-media-selected').data('path');
		var preview = $('.helix-ultimate-media-selected').data('preview');
		var target = $('.helix-ultimate-modal').attr('data-target');
		var target_type = $('.helix-ultimate-modal').attr('data-target_type');

		if (target_type == 'data') {
			$('.helix-ultimate-options-modal').find('[data-attrname="' + target + '"]').val(value);
			$('.helix-ultimate-options-modal').find('[data-attrname="' + target + '"]').prev('.helix-ultimate-image-holder').html('<img src="' + preview + '" alt="">')
		} else {
			$('#' + target).val(value);
			$('#' + target).prev('.helix-ultimate-image-holder').html('<img src="' + preview + '" alt="">');
		}

		$('.helix-ultimate-modal-overlay, .helix-ultimate-modal').remove();
		$('body').removeClass('helix-ultimate-modal-open');
	});

	$(document).on('click', '.helix-ultimate-modal-action-cancel', function (event) {
		event.preventDefault();
		$('.helix-ultimate-media-selected').removeClass('helix-ultimate-media-selected');
		$('.helix-ultimate-modal-actions-left').hide();
		$('.helix-ultimate-modal-actions-right').show();
	});

	$(document).on('click', '.action-helix-ultimate-modal-close', function (event) {
		event.preventDefault();
		$('.helix-ultimate-modal-overlay, .helix-ultimate-modal').remove();
		$('body').removeClass('helix-ultimate-modal-open');
	});

	$(document).on('click', '.helix-ultimate-media-clear', function (event) {
		event.preventDefault();
		$(this).parent().find('input').val('');
		$(this).parent().find('.helix-ultimate-image-holder').empty();
	})

	//Delete Media
	$(document).on('click', '.helix-ultimate-modal-action-delete', function (e) {
		e.preventDefault();
		var self = this;
		var deleteType = 'file';

		if ($('.helix-ultimate-media-selected').length) {
			if ($('.helix-ultimate-media-selected').hasClass('helix-ultimate-media-folder')) {
				deleteType = 'folder';
			} else {
				deleteType = 'file';
			}
		} else {
			alert('Please select a file or directory first to delete.');
			return;
		}

		if (confirm('Are you sure you want to delete this ' + deleteType + '?')) {
			var request = {
				'action': 'delete-media',
				'option': 'com_ajax',
				'helix': 'ultimate',
				'request': 'task',
				'type': deleteType,
				'path': $('.helix-ultimate-media-selected').data('path'),
				'format': 'json'
			};

			$.ajax({
				type: 'POST',
				data: request,
				success: function (response) {
					var data = $.parseJSON(response);
					if (data.status) {
						$('.helix-ultimate-media-selected').remove();
						$('.helix-ultimate-modal-actions-left').hide();
						$('.helix-ultimate-modal-actions-right').show();
					} else {
						alert(data.message);
					}
				},
				error: function () {
					alert('Somethings wrong, Try again');
				}
			});
		}
	});

	// Create folder
	$(document).on('click', '.helix-ultimate-modal-action-new-folder', function (e) {
		e.preventDefault();
		var self = this;
		var folder_name = prompt("Please enter the name of the directory which should be created.");

		if (folder_name == null || folder_name == "") {

		} else {
			var request = {
				'action': 'create-folder',
				'option': 'com_ajax',
				'helix': 'ultimate',
				'request': 'task',
				'folder_name': folder_name,
				'path': $('.helix-ultimate-media-breadcrumb-item.active').data('path'),
				'format': 'json'
			};

			$.ajax({
				type: 'POST',
				data: request,
				success: function (response) {
					var data = $.parseJSON(response);
					if (data.status) {
						$('.helix-ultimate-modal-inner').html(data.output);
					} else {
						alert(data.message);
					}
				},
				error: function () {
					alert('Somethings wrong, Try again');
				}
			});
		}
	});

	$.fn.uploadMedia = function (options) {
		var options = $.extend({
			data: '',
			index: ''
		}, options);

		$.ajax({
			type: 'POST',
			url: 'index.php?option=com_ajax&helix=ultimate&request=task&action=upload-media&format=json',
			data: options.data,
			contentType: false,
			cache: false,
			processData: false,
			beforeSend: function () {
				var progress = '<li class="helix-ultimate-media-progress ' + options.index + '">';
				progress += '<div class="helix-ultimate-media-thumb">';
				progress += '<div class="helix-ultimate-progress"><div class="helix-ultimate-progress-bar"></div></div>';
				progress += '</div>';
				progress += '<div class="helix-ultimate-media-label"><span class="fa fa-spinner fa-spin"></span> <span class="helix-ultimate-media-upload-percentage"></span>Uploading...</div>';
				progress += '</li>';

				$("#helix-ultimate-media-manager").animate({ scrollTop: $('#helix-ultimate-media-manager').prop("scrollHeight") }, 1000);
				$('.helix-ultimate-media').append(progress);
			},
			success: function (response) {
				var data = $.parseJSON(response);
				if (data.status) {
					$('.' + options.index).removeClass().addClass('helix-ultimate-media-image').attr('data-path', data.path).attr('data-preview', data.src).html(data.output);
				} else {
					$('.' + options.index).remove();
					alert(data.message);
				}
			},
			xhr: function () {
				myXhr = $.ajaxSettings.xhr();
				if (myXhr.upload) {
					myXhr.upload.addEventListener('progress', function (evt) {
						$('.' + options.index).find('.helix-ultimate-progress-bar').css('width', Math.floor(evt.loaded / evt.total * 100) + '%');
						$('.' + options.index).find('.helix-ultimate-media-upload-percentage').text(Math.floor(evt.loaded / evt.total * 100) + '% ');
					}, false);
				} else {
					alert('Uploadress is not supported.');
				}
				return myXhr;
			}
		});
	}

	// Upload Image
	$(document).on('click', '.helix-ultimate-modal-action-upload', function (e) {
		e.preventDefault();
		$('#helix-ultimate-file-input').click();
	});

	$(document).on('change', '#helix-ultimate-file-input', function (event) {
		event.preventDefault();
		var $this = $(this);
		var files = $(this).prop('files')

		for (i = 0; i < files.length; i++) {
			var file_ext = files[i].name.split('.').pop();
			var allowed = ((file_ext == 'png') || (file_ext == 'jpg') || (file_ext == 'jpeg') || (file_ext == 'gif') || (file_ext == 'svg') || (file_ext == 'ico'));
			if (allowed) {
				var formdata = new FormData();
				formdata.append('file', files[i]);
				formdata.append('path', $('.helix-ultimate-media-breadcrumb-item.active').data('path'));
				formdata.append('index', 'media-id-' + Math.floor(Math.random() * (1e6 - 1 + 1) + 1));
				$(this).uploadMedia({
					data: formdata,
					index: 'media-id-' + Math.floor(Math.random() * (1e6 - 1 + 1) + 1)
				})
			}
		}

		$this.val('')
	});

});
