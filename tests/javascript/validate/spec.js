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

define(['jquery', 'testsRoot/validate/spec-setup', 'jasmineJquery'], ($) => {
	var $element = $('#validatejs');

	describe('Validate', () => {
		beforeAll(() => {
			var ob = {
				'JLIB_FORM_CONTAINS_INVALID_FIELDS': 'invalid',
				'JLIB_FORM_FIELD_REQUIRED_VALUE': 'required',
				'JLIB_FORM_FIELD_REQUIRED_CHECK': 'checked',
				'JLIB_FORM_FIELD_INVALID_VALUE': 'invalid'
			};

			Joomla.JText.load(ob);
			renderFn = Joomla.renderMessages;
			jtxtFn = Joomla.JText._;

			Joomla.renderMessages = jasmine.createSpy('renderMessages');
			Joomla.JText._ = jasmine.createSpy('JText._');
		});

		afterAll(function () {
			Joomla.renderMessages = renderFn;
			Joomla.JText._ = jtxtFn;
		});

		describe('The input fields in the form', () => {
			it('with class \'required\' should have attributes aria-required = true', () => {
				expect($element.find('#attach-to-form input')).toHaveAttr('aria-required', 'true');
			});

			it('with class \'required\' should have attributes required = required', () => {
				expect($element.find('#attach-to-form input')).toHaveAttr('required');
			});
		});

		describe('The textarea fields in the form', () => {
			it('with class \'required\' should have attributes aria-required = true', () => {
				expect($element.find('#attach-to-form textarea')).toHaveAttr('aria-required', 'true');
			});

			it('with class \'required\' should have attributes required = required', () => {
				expect($element.find('#attach-to-form textarea')).toHaveAttr('required');
			});
		});

		describe('The select fields in the form', () => {
			it('with class \'required\' should have attributes aria-required = true', () => {
				expect($element.find('#attach-to-form select')).toHaveAttr('aria-required', 'true');
			});

			it('with class \'required\' should have attributes required = required', () => {
				expect($element.find('#attach-to-form select')).toHaveAttr('required');
			});
		});

		describe('The fieldset fields in the form', () => {
			it('with class \'required\' should have attributes aria-required = true', () => {
				expect($element.find('#attach-to-form fieldset')).toHaveAttr('aria-required', 'true');
			});

			it('with class \'required\' should have attributes required = required', () => {
				expect($element.find('#attach-to-form fieldset')).toHaveAttr('required');
			});
		});

		describe('validate method on #validate-disabled', () => {
			var res = document.formvalidator.validate($element.find('#validate-disabled').get(0));

			it('should return true', () => {
				expect(res).toEqual(true);
			});

			it('should remove class invalid from element', () => {
				expect($element.find('#validate-disabled')).not.toHaveClass('invalid');
			});

			it('should have aria-invalid = false in element', () => {
				expect($element.find('#validate-disabled')).toHaveAttr('aria-invalid', 'false');
			});

			it('should remove class invalid from the label for element', () => {
				expect($element.find('#validate-test label[for=validate-disabled]')).not.toHaveClass('invalid');
			});
		});

		// @TODO re add the 'validate method on #validate-required-unchecked' tests

		describe('validate method on #validate-required-checked', () => {
			var res = document.formvalidator.validate($element.find('#validate-required-checked').get(0));

			it('should return true', () => {
				expect(res).toEqual(true);
			});
		});

		describe('validate method on #validate-numeric-number', function () {
			var res = document.formvalidator.validate($element.find('#validate-numeric-number').get(0));

			it('should return true', function () {
				expect(res).toEqual(true);
			});

			it('should remove class invalid from element', () => {
				expect($element.find('#validate-numeric-number')).not.toHaveClass('invalid');
			});

			it('should have aria-invalid = false in element', () => {
				expect($element.find('#validate-numeric-number')).toHaveAttr('aria-invalid', 'false');
			});

			it('should remove class invalid from the label for element', () => {
				expect($element.find('#validate-numeric-number-lbl')).not.toHaveClass('invalid');
			});
		});

		describe('validate method on #validate-numeric-nan', () => {
			var elNan   = document.getElementById('validate-numeric-nan'),
				res = document.formvalidator.validate(elNan);

			it('should return false', () => {
				expect(res).toEqual(false);
			});

			it('should add class invalid to element', (done) => {
				setTimeout(() => {
					expect(elNan.classList).toContain('invalid');
					done();
				}, 100)
			});

			it('should have aria-invalid = true in element', (done) => {
				setTimeout(() => {
					expect(elNan.getAttribute('aria-invalid')).toEqual('true');
					done();
				}, 100)
			});

			it('should add class invalid to the label for element', (done) => {
				setTimeout(() => {
					expect(document.querySelector('[for="validate-numeric-nan"]').classList).toContain('invalid');
					done();
				}, 100)
			});
		});

		describe('validate method on #validate-no-options', () => {
			var res = document.formvalidator.validate($element.find('#validate-no-options').get(0));

			it('should return true', () => {
				expect(res).toEqual(true);
			});

			it('should remove class invalid from element', () => {
				expect($element.find('#validate-no-options')).not.toHaveClass('invalid');
			});

			it('should have aria-invalid = false in element', () => {
				expect($element.find('#validate-no-options')).toHaveAttr('aria-invalid', 'false');
			});
		});

		describe('isValid method on button click', () => {
			beforeAll(() => {
				document.getElementById('button').click();
			});

			it('should call Joomla.JText._(\'JLIB_FORM_CONTAINS_INVALID_FIELDS\')', () => {
				expect(Joomla.JText._).toHaveBeenCalledWith('JLIB_FORM_CONTAINS_INVALID_FIELDS');
			});

			it('should add class invalid to element #isvalid-numeric-nan', () => {
				expect(document.getElementById('isvalid-numeric-nan')).toHaveClass('invalid');
			});

			it('should have aria-invalid = true in element #isvalid-numeric-nan', () => {
				expect($element.find('#isvalid-numeric-nan')).toHaveAttr('aria-invalid', 'true');
			});

			it('should not add class invalid to element #isvalid-novalidate', () => {
				expect($element.find('#isvalid-novalidate')).not.toHaveClass('invalid');
			});
		});

		describe('Invalid element should become valid when passing the correct data', () => {

			it('should remove class invalid from element #isvalid-numeric-nan after correcting value', (done) => {
				document.getElementById('isvalid-numeric-nan').setAttribute('value', 12345);
				document.getElementById('button').click();

				setTimeout(() => {
					expect($element.find('#isvalid-numeric-nan').classList).not.toContain('invalid');
					done();
				}, 100)
			});
		});
	});
});
