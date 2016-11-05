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

define(['jquery', 'testsRoot/combobox/spec-setup', 'jasmineJquery'], function ($) {

	describe('Combobox render', function () {
		it('Should set CSS of drop down menu', function () {
			var inputWidth = $('#cbox').width();
			var btnWidth = $('#cbox-btn-group').width();
			var dropDownLeft = -inputWidth + btnWidth;

			expect($('#cbox-drop-menu')).toHaveCss({'left': dropDownLeft + 'px', 'max-height': '150px', 'overflow-y': 'scroll'});
		});
	});

	describe('Combobox addEventHandlers', function () {
		beforeAll(function () {
			this.$container = $('#comboboxjs');
			this.$listitems = $('#comboboxjs li a');
		});

		it('Should bind focus handler for <input>', function () {
			expect($('#cbox-input')).toHandle('focus');
		});

		it('Should bind blur handler for <input>', function () {
			expect($('#cbox-input')).toHandle('blur');
		});

		it('Should bind keyup handler for <input>', function () {
			expect($('#cbox-input')).toHandle('keyup');
		});

		it('Should bind mouseenter handler for drop menu', function () {
			expect($('#cbox-drop-menu')).toHandle('mouseover');
		});

		it('Should bind mouseleave handler for input', function () {
			expect($('#cbox-drop-menu')).toHandle('mouseout');
		});

		it('Should bind click handler for button', function () {
			expect($('#cbox-btn')).toHandle('click');
		});

		it('Should bind click handler for <li> elements', function () {
			expect(this.$container.find('li').first()).toHandle('click');
			expect(this.$container.find('li').last()).toHandle('click');
		});

		it('Should bind mouseenter handler for a elements inside li', function () {
			expect(this.$listitems.first()).toHandle('mouseover');
			expect(this.$listitems.last()).toHandle('mouseover');
		});

		it('Should bind mouseleave handler for a elements inside li', function () {
			expect(this.$listitems.first()).toHandle('mouseout');
			expect(this.$listitems.last()).toHandle('mouseout');
		});
	});

	describe('Combobox drop', function () {
		beforeEach(function () {
			$('#cbox-input').focus();
		});

		it('Should handle focus event', function () {
			expect($('#cbox-input')).toHandle('focus');
		});

		it('Should add class nav-hover to combobox div', function () {
			expect($('#cbox')).toHaveClass('nav-hover');
		});

		it('Should add class open to dropBtnDiv div', function () {
			expect($('#cbox-btn-group')).toHaveClass('open');
		});

		it('Should bind keydown handler for <input>', function () {
			expect($('#cbox-input')).toHandle('keydown');
		});

		it('Should bind keypress handler for <input>', function () {
			expect($('#cbox-input')).toHandle('keypress');
		});

		it('Should bind keyup handler for <input>', function () {
			expect($('#cbox-input')).toHandle('keyup');
		});
	});

	describe('Combobox pick', function () {
		beforeEach(function () {
			$('#cbox-input').blur();
		});

		it('Should handle blur event', function () {
			expect($('#cbox-input')).toHandle('blur');
		});

		it('Should remove class nav-hover from combobox div', function () {
			expect($('#cbox')).not.toHaveClass('nav-hover');
		});

		it('Should remove class open from dropBtnDiv div', function () {
			expect($('#cbox-btn-group')).not.toHaveClass('open');
		});

		it('Should unbind keydown handler from <input>', function () {
			expect($('#cbox-input')).not.toHandle('keydown');
		});

		it('Should unbind keypress handler from <input>', function () {
			expect($('#cbox-input')).not.toHandle('keypress');
		});

	});

	describe('Combobox updateList', function () {
		describe('When input an 1 in the input box', function () {
			beforeAll(function () {
				$('#cbox-input').val('1').trigger(jQuery.Event('keyup', {which: 49}));
			});

			it('Should make link1 not visible', function () {
				expect($('#cbox-link1')).not.toBeVisible();
			});

			it('Should make link2 not visible', function () {
				expect($('#cbox-link2')).not.toBeVisible();
			});
		});

		describe('When input text "link1" in the input box', function () {
			beforeAll(function () {
				var $input = $('#cbox-input');

				$input.val('l').trigger(jQuery.Event('keyup', {which: 76}));
				$input.val('li').trigger(jQuery.Event('keyup', {which: 73}));
				$input.val('lin').trigger(jQuery.Event('keyup', {which: 78}));
				$input.val('link').trigger(jQuery.Event('keyup', {which: 75}));
				$input.val('link1').trigger(jQuery.Event('keyup', {which: 49}));
			});

			it('Should make link1 visible', function () {
				expect($('#cbox-link1')).toBeVisible();
			});

			it('Should make link2 not visible', function () {
				expect($('#cbox-link2')).not.toBeVisible();
			});
		});

		describe('When input text "link2" in the input box', function () {
			beforeAll(function () {
				var $input = $('#cbox-input');

				$input.val('l').trigger(jQuery.Event('keyup', {which: 76}));
				$input.val('li').trigger(jQuery.Event('keyup', {which: 73}));
				$input.val('lin').trigger(jQuery.Event('keyup', {which: 78}));
				$input.val('link').trigger(jQuery.Event('keyup', {which: 75}));
				$input.val('link2').trigger(jQuery.Event('keyup', {which: 50}));
			});

			it('Should make link1 not visible', function () {
				expect($('#cbox-link1')).not.toBeVisible();
			});

			it('Should make link2 not visible', function () {
				expect($('#cbox-link2')).toBeVisible();
			});
		});

		describe('When input text "link" in the input box', function () {
			beforeAll(function () {
				var $input = $('#cbox-input');

				$input.val('l').trigger(jQuery.Event('keyup', {which: 76}));
				$input.val('li').trigger(jQuery.Event('keyup', {which: 73}));
				$input.val('lin').trigger(jQuery.Event('keyup', {which: 78}));
				$input.val('link').trigger(jQuery.Event('keyup', {which: 75}));
			});

			it('Should make link1 visible', function () {
				expect($('#cbox-link1')).toBeVisible();
			});

			it('Should make link2 visible', function () {
				expect($('#cbox-link2')).toBeVisible();
			});
		});
	});

	describe('Combobox focusCombo', function () {
		describe('When trigger click on button', function () {
			beforeEach(function () {
				$('#cbox-btn').click();
			});

			it('Should make link1 visible', function () {
				expect($('#cbox-link1')).toBeVisible();
			});

			it('Should make link2 visible', function () {
				expect($('#cbox-link2')).toBeVisible();
			});

			it('Should focus on input', function () {
				expect($('#cbox-input')).toBeFocused();
			});
		});
	});

	describe('Combobox updateCombo', function () {
		describe('When trigger click on li element', function () {
			beforeEach(function () {
				$('#cbox-item1').click();
			});

			it('Should set link1 as value of input', function () {
				expect($('#cbox-input').val()).toEqual('link1');
			});

			it('Should remove class nav-hover from combobox div', function () {
				expect($('#cbox')).not.toHaveClass('nav-hover');
			});

			it('Should remove class open from dropBtnDiv div', function () {
				expect($('#cbox-btn-group')).not.toHaveClass('open');
			});
		});
	});
});
