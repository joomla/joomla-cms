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

define(['jquery', 'testsRoot/core/spec-setup', 'jasmineJquery'], function ($) {

	describe('Core Joomla.submitform', function () {
		var form = document.getElementById('adminForm');
		form.task = {};

		beforeEach(function () {
			spyOnEvent('#adminForm', 'submit');
			form.removeChild = jasmine.createSpy('removeChild');

			Joomla.submitform('article.add', form, false);
		});

		it('should assign task to form.task.value', function () {
			expect(form.task.value).toEqual('article.add');
		});
		it('should set attribute "novalidate"', function () {
			expect($(form)).toHaveAttr('novalidate', '');
		});
		it('should add input submit button to DOM', function () {
			expect($('#adminForm')).toContainElement('input[type="submit"]');
		});
		it('should make the added input element invisible', function () {
			expect($('#adminForm').children('input[type="submit"]')).not.toBeVisible();
		});
		it('should click the input element', function () {
			expect('submit').toHaveBeenTriggeredOn('#adminForm');
		});
		it('should remove the input element', function () {
			expect(form.removeChild).toHaveBeenCalled();
		});
	});

	describe('Core Joomla.getOptions', function () {
		it('should return options array Joomla.getOptions("com_foobar")', function () {
			expect(Joomla.getOptions("com_foobar")).toEqual(["my options"])
		});
		it('should return option string Joomla.getOptions("com_foobar2")', function () {
			expect(Joomla.getOptions("com_foobar2")).toEqual("Alert message!")
		});
		it('should return option Boolean false Joomla.getOptions("com_foobar3")', function () {
			expect(Joomla.getOptions("com_foobar3")).toEqual(false)
		});
		it('should return default value for not existing key Joomla.getOptions("com_foobar4", 123)', function () {
			expect(Joomla.getOptions("com_foobar4", 123)).toEqual(123)
		});
	});

	describe('Core Joomla.getOptions programmatically', function () {
		// Test dynamically added options
		it('should return dynamically added options Joomla.getOptions("com_foobar5")', function () {
			$('#get-options').append($('<script>', {
				type: 'application/json',
				'class': 'joomla-script-options new',
				text: '{"com_foobar5": true}'
			}));
			Joomla.loadOptions();

			expect(Joomla.getOptions("com_foobar5")).toEqual(true)
		});
		it('amount of the loaded options containers should equal 2', function () {
			expect($('.joomla-script-options.loaded').length).toEqual(2)
		});
	});

	describe('Core Joomla.JText', function () {
		var ob = {
			'JTOGGLE_SHOW_SIDEBAR': 'Show Sidebar',
			'Jtoggle_Hide_Sidebar': 'Hide Sidebar'
		};

		beforeAll(function () {
			Joomla.JText.load(ob);
		});

		it('should add content passed via load() to the strings object', function () {
			expect(Joomla.JText.strings.JTOGGLE_SHOW_SIDEBAR).toEqual('Show Sidebar');
			expect(Joomla.JText.strings.JTOGGLE_HIDE_SIDEBAR).toEqual('Hide Sidebar');
		});
		it('should return \'Show Sidebar\' on calling Joomla.JText._(\'JTOGGLE_SHOW_SIDEBAR\', \'test\')', function () {
			expect(Joomla.JText._('JTOGGLE_SHOW_SIDEBAR', 'test')).toEqual('Show Sidebar');
		});
		it('should return \'Show Sidebar\' on calling Joomla.JText._(\'Jtoggle_Show_Sidebar\', \'test\')', function () {
			expect(Joomla.JText._('Jtoggle_Show_Sidebar', 'test')).toEqual('Show Sidebar');
		});
		it('should return \'test\' on calling Joomla.JText._(\'JTOGGLE_REMOVE_SIDEBAR\', \'test\')', function () {
			expect(Joomla.JText._('JTOGGLE_REMOVE_SIDEBAR', 'test')).toEqual('test');
		});

		// Test strings in optionsStorage
		it('should return \'String 1\' on calling Joomla.JText._(\'stRing1\')', function () {
			expect(Joomla.JText._('stRing1')).toEqual('String 1');
		});
		it('should return \'String 2\' on calling Joomla.JText._(\'StrinG2\')', function () {
			expect(Joomla.JText._('StrinG2')).toEqual('String 2');
		});
	});



	describe('Core Joomla.replaceTokens', function () {
		var newToken = '123456789123456789123456789ABCDE';

		beforeAll(function () {
			Joomla.replaceTokens(newToken);
		});

		it('should set name of all hidden input elements with value = 1 and name = old token to new token', function () {
			var $elements =$('.replace-tokens-input');
			expect($elements[0].name).toEqual(newToken);
			expect($elements[1].name).toEqual(newToken);
		});
		it('should not set name of non hidden input elements to new token', function () {
			expect($('.replace-tokens-input #invalid-type').name).not.toEqual(newToken);
		});
		it('should not set name of input elements with value other than 1 to new token', function () {
			expect($('.replace-tokens-input #invalid-value').name).not.toEqual(newToken);
		});
		it('should not set name of input elements with invalid name to new token', function () {
			expect($('.replace-tokens-input #invalid-name').name).not.toEqual(newToken);
		});
	});

	describe('Core Joomla.checkAll', function () {
		var form = document.getElementById('check-all-form');
		form.boxchecked = {};
		var element = document.getElementById('cb0');

		beforeAll(function () {
			Joomla.checkAll(element);
		});

		it('should return false when input element is not inside a form', function () {
			expect(Joomla.checkAll(document.getElementById('cb-no-form'))).toEqual(false);
		});

		it('should check all the checkboxes that has id starting with \'cb\' inside the form', function () {
			expect($('#cb0')).toBeChecked();
			expect($('#cb1')).toBeChecked();
			expect($('#cb2')).toBeChecked();
			expect($('#no-cb3')).not.toBeChecked();
		});

		it('should set the number of checked boxes in the form to form.boxchecked', function () {
			expect(form.boxchecked.value).toEqual(3);
		});

		it('should use passed in stub to look for input elements', function () {
			var element = document.getElementById('stub-check-test-1');
			Joomla.checkAll(element, 'stub');

			expect($('#stub-check-test-1')).toBeChecked();
			expect($('#stub-check-test-2')).toBeChecked();
		});
	});

	describe('Core Joomla.renderMessages and Joomla.removeMessages', function () {
		var messages = {
			"message": ["Message one", "Message two"],
			"error": ["Error one", "Error two"]
		};

		beforeAll(function () {
			Joomla.JText.load({"message": "Message"});
			Joomla.renderMessages(messages);
		});

		it('renderMessages should render titles when translated strings are available', function () {
			expect($('h4.alert-heading').first()).toContainText('Message');
		});

		it('renderMessages should render messages inside a div having class alert-message', function () {
			var $messages = $('joomla-alert[level="success"]').children('div');
			expect($messages[0]).toContainText('Message two');
			expect($messages[1]).toContainText('Message one');
		});

		it('renderMessages should render errors inside a div having class alert-error', function () {
			var $messages = $('joomla-alert[level="danger"]').children('div');
			expect($messages[0]).toContainText('Error two');
			expect($messages[1]).toContainText('Error one');
		});

		it('removeMessages should remove all content from system-message-container', function () {
			Joomla.removeMessages();

			// Alerts need some time for the close animation
			setTimeout(function () {
				expect($("#system-message-container")).toBeEmpty();
			}, 400);
		});
	});

	describe('Core Joomla.isChecked', function () {
		var form = document.getElementById('ischecked-test-form');
		form.boxchecked = {value: 5};

		beforeAll(function () {
			Joomla.isChecked(true, form);
		});

		it('should increase form.boxchecked.value from 5 to 6', function () {
			expect(form.boxchecked.value).toEqual(6);
		});
		it('should set checkAllToggle.checked to false', function () {
			expect(form.elements[ 'checkall-toggle' ].checked).toEqual(false);
		});
	});

	describe('Core Joomla.tableOrdering', function () {
		beforeAll(function () {
			submitformFn = Joomla.submitform;
			Joomla.submitform = jasmine.createSpy('submitform');

			this.form = document.getElementById('table-ordering-test-form');
			this.form.filter_order = {};
			this.form.filter_order_Dir = {};

			Joomla.tableOrdering('order', 'dir', 'task', this.form);
		});

		afterAll(function() {
			Joomla.submitform = submitformFn;
		});

		it('should call Joomla.submitform with params task and form', function () {
			expect(Joomla.submitform).toHaveBeenCalledWith('task', this.form);
		});
		it('should set form.filter_order.value = order', function () {
			expect(this.form.filter_order.value).toEqual('order')
		});
		it('should set form.filter_order_Dir.value = dir', function () {
			expect(this.form.filter_order_Dir.value).toEqual('dir')
		});
	});
});
