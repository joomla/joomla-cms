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

define(['jquery', 'testsRoot/joomla-field-send-test-mail/spec-setup', 'jasmineJquery'], ($) => {
	describe('Sendtestmail', () => {
		beforeAll(() => {
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

		afterAll(() => {
			jasmine.Ajax.uninstall();

			Joomla.renderMessages = renderFn;
			Joomla.removeMessages = removeFn;
			Joomla.ajaxErrorsMessages = ajxerrFn;
			window.scrollTo = scrollFn;
		});

		it('Should call removeMessages()', () => {
			expect(Joomla.removeMessages).toHaveBeenCalled();
		});

		describe("on success with typeof response.messages !== 'object'", () => {
			beforeAll(() => {
				request = jasmine.Ajax.requests.mostRecent();
				request.respondWith(mailResponses.successInvalid);
			});

			it("should call Joomla.renderMessages({})", () => {
				expect(Joomla.renderMessages).not.toHaveBeenCalledWith({});
			});
		});

		describe("on success with typeof response.messages == 'object' && response.messages !== null", function() {
			beforeAll(() => {
				$('#sendtestmail').click();

				request = jasmine.Ajax.requests.mostRecent();
				request.respondWith(mailResponses.success);
			});

			it("should make an AJAX request of type POST", () => {
				expect(request.method).toBe('POST');
			});

			// @TODO re add should set data to the request test

			it("should set url value to 'uri'", () => {
				expect(request.url).toBe('uri');
			});

			it("should call Joomla.renderMessages({'message': 'text'})", () => {
				expect(Joomla.renderMessages).toHaveBeenCalledWith({"message": "text"});
			});
		});

		describe("on failure", () => {
			beforeAll(() => {
				$('#sendtestmail').click();

				request = jasmine.Ajax.requests.mostRecent();
				request.respondWith(mailResponses.fail);
			});

			// @TODO re add should call Joomla.ajaxErrorsMessages(jqXHR, 'error', 'HTTP/1.1 404 Not Found') test

			it("should call Joomla.renderMessages(undefined)", () => {
				expect(Joomla.renderMessages).toHaveBeenCalledWith(undefined);
			});
		});
	});
});
