/**
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @package     Joomla
 * @subpackage  JavaScript Tests
 * @since       3.6
 * @version     1.0.0
 */

define(['jquery', 'testsRoot/permissions/spec-setup', 'jasmineJquery'], function ($) {
	describe('sendPermissions', function () {
		beforeAll(function() {
			jasmine.Ajax.install();
			Joomla.JText._ = jasmine.createSpy('_');
			Joomla.renderMessages = jasmine.createSpy('renderMessages');
			sendPermissions(event);
		});

		afterAll(function () {
			jasmine.Ajax.uninstall();
		});

		it("should remove attribute class from icon", function() {
			expect($('#icon_0')).not.toHaveAttr('class');
		});

		it("should set style attribute to display the spinner in icon", function() {
			expect($('#icon_0')).toHaveAttr('style', 'background: url(../media/system/images/modal/spinner.gif); display: inline-block; width: 16px; height: 16px');
		});

		describe("on success with resp.data == 'true' & resp.message == 0", function() {
			var spanContainer = $('#ajax-test');

			beforeAll(function() {
				sendPermissions(event);
				request = jasmine.Ajax.requests.mostRecent();
				request.respondWith(responses.success);
			});

			it("should make a AJAX request of type GET", function() {
				expect(request.method).toBe('GET');
			});

			it("should set attribute class in icon to icon-cancel", function() {
				expect($('#icon_0')).toHaveAttr('class', 'icon-cancel');
			});

			it("should remove classes label label-important from span elements", function() {
				expect(spanContainer.find('span')).not.toHaveClass('label label-important');
			});

			it("should add classes label label-success to span elements", function() {
				expect(spanContainer.find('span')).toHaveClass('label label-success');
			});

			it("should remove attribute style from icon", function() {
				expect($('#icon_0')).not.toHaveAttr('style');
			});

			it("should call Joomla.JText._('JLIB_RULES_ALLOWED')", function() {
				expect(Joomla.JText._).toHaveBeenCalledWith('JLIB_RULES_ALLOWED');
			});

			it("should call Joomla.renderMessages({ error: [undefined] })", function() {
				expect(Joomla.renderMessages).toHaveBeenCalledWith({ error: [undefined] });
			});
		});

		describe("on success with resp.data !== 'true' & resp.message !== 0", function() {
			beforeAll(function() {
				sendPermissions(event);
				request = jasmine.Ajax.requests.mostRecent();
				responses.success.responseText = '{"data": "false", "message": "1"}';
				request.respondWith(responses.success);
			});

			it("should call Joomla.renderMessages({ error: [undefined] })", function() {
				expect(Joomla.renderMessages).toHaveBeenCalledWith({ error: [undefined] });
			});

			it("should remove attribute style from icon", function() {
				expect($('#icon_0')).not.toHaveAttr('style');
			});

			it("should set attribute class in icon to icon-cancel", function() {
				expect($('#icon_0')).toHaveAttr('class', 'icon-cancel');
			});
		});

		describe("on failure", function() {
			beforeAll(function() {
				sendPermissions(event);
				request = jasmine.Ajax.requests.mostRecent();
				request.respondWith(responses.fail);
			});

			it("should call Joomla.renderMessages({ error: [undefined] })", function() {
				expect(Joomla.renderMessages).toHaveBeenCalledWith({ error: [undefined] });
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
