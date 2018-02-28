/**
 * @package     Joomla.Tests
 * @subpackage  JavaScript Tests
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since       3.6.3
 * @version     1.0.0
 */

define(['jquery', 'testsRoot/joomla-field-permissions/spec-setup', 'jasmineJquery'],  ($) => {
	describe('sendPermissions', () => {
		beforeAll( () => {
			jasmine.Ajax.install();

			renderFn = Joomla.renderMessages;
			removeFn = Joomla.removeMessages;
			jtxtFn = Joomla.JText._;
			ajxerrFn = Joomla.ajaxErrorsMessages;

			Joomla.JText._ = jasmine.createSpy('_');
			Joomla.renderMessages = jasmine.createSpy('renderMessages');
			Joomla.removeMessages = jasmine.createSpy('removeMessages');
			Joomla.ajaxErrorsMessages = jasmine.createSpy('ajaxErrorsMessages');

			sendPermissions(event);
		});

		afterAll( () => {
			jasmine.Ajax.uninstall();
			
			Joomla.renderMessages = renderFn;
			Joomla.removeMessages = removeFn;
			Joomla.ajaxErrorsMessages = ajxerrFn;
			Joomla.JText._ = jtxtFn;
		});

		it("should set style attribute to display the spinner in icon", () => {
			expect($('#icon_0')).toHaveAttr('class', 'fa fa-spinner fa-spin');
		});

		it("should call Joomla.removeMessages()", () => {
			expect(Joomla.removeMessages).toHaveBeenCalled();
		});

		describe("on success with resp.data.result === true & resp.messages an object", () => {
			var $spanContainer = $('#ajax-test');
        
			beforeAll(() => {
				sendPermissions(event);
				request = jasmine.Ajax.requests.mostRecent();
				request.respondWith(responses.success);
			});
        
			it("should make an AJAX request of type POST", () => {
				expect(request.method).toBe('POST');
			});
        
			it("should set attribute class in icon to fa fa-check", () => {
				expect($('#icon_0')).toHaveAttr('class', 'fa fa-check');
			});

			it("should add class in icon to fa fa-check", () => {
				expect($spanContainer.find('span')).toHaveClass('test-class');
			});

			it("should class in icon to fa fa-check", () => {
				expect($spanContainer.find('span')).toContainText('Sample text');
			});
        
			it("should call Joomla.renderMessages({})", () => {
				expect(Joomla.renderMessages).toHaveBeenCalledWith({});
			});

		});

		describe("on success with resp.data.result !=== true & resp.messages an object", () => {
			beforeAll(() => {
				sendPermissions(event);
				request = jasmine.Ajax.requests.mostRecent();
				responses.success.responseText = '{"data": {"result": false}, "messages": {}}';
				request.respondWith(responses.success);
			});

			it("should set attribute class in icon to fa fa-times", () => {
				expect($('#icon_0')).toHaveAttr('class', 'fa fa-times');
			});

			it("should call Joomla.renderMessages({})", () => {
				expect(Joomla.renderMessages).toHaveBeenCalledWith({});
			});

		});

		describe("on failure", () => {
			beforeAll(() => {
				sendPermissions(event);
				request = jasmine.Ajax.requests.mostRecent();
				request.respondWith(responses.fail);
			});

			it("should call Joomla.ajaxErrorsMessages(jqXHR, 'error', 'HTTP/1.1 404 Not Found')", () => {
				expect(Joomla.ajaxErrorsMessages).toHaveBeenCalledWith(jasmine.any(Object), 'error', 'HTTP/1.1 404 Not Found');
			});
			
			it("should call Joomla.renderMessages(undefined)", () => {
				expect(Joomla.renderMessages).toHaveBeenCalledWith(undefined);
			});

			it("should call window.scrollTo(0, 0)", () => {
				expect(window.scrollTo).toHaveBeenCalledWith(0, 0);
			});
        
		});
	});
});
