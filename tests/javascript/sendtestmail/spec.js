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

define(['jquery', 'testsRoot/sendtestmail/spec-setup', 'jasmineJquery'], function ($) {
	describe('Sendtestmail', function () {
		beforeAll(function() {
			jasmine.Ajax.install();

			renderFn = Joomla.renderMessages;
			removeFn = Joomla.removeMessages;
			ajxerrFn = Joomla.ajaxErrorsMessages;
			scrollFn = window.scrollTo;

			Joomla.renderMessages = jasmine.createSpy('renderMessages');
			Joomla.removeMessages = jasmine.createSpy('removeMessages');
			Joomla.ajaxErrorsMessages = jasmine.createSpy('ajaxErrorsMessages');
			window.scrollTo = jasmine.createSpy('scrollTo');

			$('#sendtestmail').click();
		});

		afterAll(function () {
			jasmine.Ajax.uninstall();

			Joomla.renderMessages = renderFn;
			Joomla.removeMessages = removeFn;
			Joomla.ajaxErrorsMessages = ajxerrFn;
			window.scrollTo = scrollFn;
		});

		it('Should call removeMessages()', function () {
			expect(Joomla.removeMessages).toHaveBeenCalled();
		});

		describe("on success with typeof response.messages !== 'object'", function() {
			beforeAll(function() {
				request = jasmine.Ajax.requests.mostRecent();
				request.respondWith(mailResponses.successInvalid);
			});

			it("should call Joomla.renderMessages({})", function() {
				expect(Joomla.renderMessages).not.toHaveBeenCalledWith({});
			});

			it("should call window.scrollTo(0, 0)", function() {
				expect(window.scrollTo).not.toHaveBeenCalledWith(0, 0);
			});
		});

		describe("on success with typeof response.messages == 'object' && response.messages !== null", function() {
			beforeAll(function() {
				$('#sendtestmail').click();

				request = jasmine.Ajax.requests.mostRecent();
				request.respondWith(mailResponses.success);
			});

			it("should make an AJAX request of type POST", function() {
				console.log(request);
				expect(request.method).toBe('POST');
			});

			it("should set data to the request", function() {
				expect(request.data()).toEqual(email_data);
			});

			it("should set url value to 'uri'", function() {
				expect(request.url).toBe('uri');
			});

			it("should call Joomla.renderMessages({'message': 'text'})", function() {
				expect(Joomla.renderMessages).toHaveBeenCalledWith({"message": "text"});
			});

			it("should call window.scrollTo(0, 0)", function() {
				expect(window.scrollTo).toHaveBeenCalledWith(0, 0);
			});
		});

		describe("on failure", function() {
			beforeAll(function() {
				$('#sendtestmail').click();

				request = jasmine.Ajax.requests.mostRecent();
				request.respondWith(mailResponses.fail);
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
		});
	});
});
