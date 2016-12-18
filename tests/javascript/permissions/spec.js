/**
 * @package     Joomla.Tests
 * @subpackage  JavaScript Tests
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since       3.6.3
 * @version     1.0.0
 */

define(['jquery', 'testsRoot/permissions/spec-setup', 'jasmineJquery'], function ($) {
	describe('sendPermissions', function () {
		beforeAll(function() {
			jasmine.Ajax.install();

			renderFn = Joomla.renderMessages;
			removeFn = Joomla.removeMessages;
			jtxtFn = Joomla.JText._;
			ajxerrFn = Joomla.ajaxErrorsMessages;
			scrollFn = window.scrollTo;

			Joomla.JText._ = jasmine.createSpy('_');
			Joomla.renderMessages = jasmine.createSpy('renderMessages');
			Joomla.removeMessages = jasmine.createSpy('removeMessages');
			Joomla.ajaxErrorsMessages = jasmine.createSpy('ajaxErrorsMessages');
			window.scrollTo = jasmine.createSpy('scrollTo');

			sendPermissions(event);
		});

		afterAll(function () {
			jasmine.Ajax.uninstall();

			Joomla.renderMessages = renderFn;
			Joomla.removeMessages = removeFn;
			Joomla.ajaxErrorsMessages = ajxerrFn;
			window.scrollTo = scrollFn;
			Joomla.JText._ = jtxtFn;
		});

		it("should remove attribute class from icon", function() {
			expect($('#icon_0')).not.toHaveAttr('class');
		});

		it("should set style attribute to display the spinner in icon", function() {
			expect($('#icon_0')).toHaveAttr('style', 'background: url(../media/system/images/modal/spinner.gif); display: inline-block; width: 16px; height: 16px');
		});

		it("should call Joomla.removeMessages()", function() {
			expect(Joomla.removeMessages).toHaveBeenCalled();
		});

		describe("on success with resp.data.result == 'true' & resp.messages an object", function() {
			var $spanContainer = $('#ajax-test');

			beforeAll(function() {
				sendPermissions(event);
				request = jasmine.Ajax.requests.mostRecent();
				request.respondWith(responses.success);
			});

			it("should make an AJAX request of type POST", function() {
				expect(request.method).toBe('POST');
			});

			it("should set attribute class in icon to icon-save", function() {
				expect($('#icon_0')).toHaveAttr('class', 'icon-save');
			});

			it("should add class in icon to icon-save", function() {
				expect($spanContainer.find('span')).toHaveClass('test-class');
			});

			it("should class in icon to icon-save", function() {
				expect($spanContainer.find('span')).toContainText('Sample text');
			});

			it("should call Joomla.renderMessages({})", function() {
				expect(Joomla.renderMessages).toHaveBeenCalledWith({});
			});

			it("should call window.scrollTo(0, 0)", function() {
				expect(window.scrollTo).toHaveBeenCalledWith(0, 0);
			});
		});

		describe("on success with resp.data.result !== 'true' & resp.messages an object", function() {
			beforeAll(function() {
				sendPermissions(event);
				request = jasmine.Ajax.requests.mostRecent();
				responses.success.responseText = '{"data": {"result": false}, "messages": {}}';
				request.respondWith(responses.success);
			});

			it("should set attribute class in icon to icon-cancel", function() {
				expect($('#icon_0')).toHaveAttr('class', 'icon-cancel');
			});

			it("should call Joomla.renderMessages({})", function() {
				expect(Joomla.renderMessages).toHaveBeenCalledWith({});
			});

			it("should call window.scrollTo(0, 0)", function() {
				expect(window.scrollTo).toHaveBeenCalledWith(0, 0);
			});
		});

		describe("on failure", function() {
			beforeAll(function() {
				sendPermissions(event);
				request = jasmine.Ajax.requests.mostRecent();
				request.respondWith(responses.fail);
			});

			it("should call Joomla.ajaxErrorsMessages(jqXHR, 'error', 'HTTP/1.1 404 Not Found')", function() {
				expect(Joomla.ajaxErrorsMessages).toHaveBeenCalledWith(jasmine.any(Object), 'error', 'HTTP/1.1 404 Not Found');
			});

			it("should call Joomla.renderMessages(undefined)", function() {
				expect(Joomla.renderMessages).toHaveBeenCalledWith(undefined);
			});

			it("should call window.scrollTo(0, 0)", function() {
				expect(window.scrollTo).toHaveBeenCalledWith(0, 0);
			});

			it("should remove attribute style from icon", function() {
				expect($('#icon_0')).not.toHaveAttr('style');
			});

			it("should set attribute class in icon to icon-cancel", function() {
				expect($('#icon_0')).toHaveAttr('class', 'icon-cancel');
			});
		});
	});
});
