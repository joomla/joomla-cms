/**
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @package     Joomla
 * @subpackage  JavaScript Tests
 * @since       3.6.3
 * @version     1.0.0
 */

define(['jquery', 'testsRoot/highlighter/spec-setup', 'jasmineJquery'], function ($) {
	describe('Highlight single text', function () {
		beforeAll(function () {
			highlighter.highlight(["text"]);
		});
		
		it('Should highlight sample text at depth 1', function () {
			expect($('#text-depth-1').html()).toContain('class=\"highlight\"');
		});
		
		it('Should highlight sample text at depth 2', function () {
			expect($('#text-depth-2').html()).toContain('class=\"highlight\"');
		});
	});

	describe('Highlight multiple text', function () {
		beforeAll(function () {
			highlighter.highlight(["element", "1", "2"]);
		});
		
		it('Should highlight sample text at depth 1', function () {
			var $element = $('#text-depth-1');
			expect($element.html()).toContain('class="highlight"');
		});
		
		it('Should highlight sample text at depth 2', function () {
			var $element = $('#text-depth-2');
			expect($element.html()).toContain('class="highlight"');
		});
	});
	
	describe('Highlight with string input with case insensitivity', function () {
		beforeAll(function () {
			highlighter.highlight(["sample"]);
		});
		
		it('Should highlight word \'sample\' in sample text at depth 1', function () {
			expect($('#text-depth-1').html()).toContain('class="highlight"');;
		});
		
		it('Should highlight word \'sample\' in sample text at depth 2', function () {
			expect($('#text-depth-2').html()).toContain('class="highlight"');;
		});
	});

	describe('Highlight with half word input', function () {
		beforeAll(function () {
			highlighter.highlight("dep");
		});
		
		it('Should not highlight the word depth in sample text at depth 1', function () {
			expect($('#text-depth-1')).not.toContainHtml('<span rel="depth" class="highlight">depth</span>');
		});
		
		it('Should not highlight the word depth in sample text at depth 2', function () {
			expect($('#text-depth-2')).not.toContainHtml('<span rel="depth" class="highlight">depth</span>');
		});
	});

	describe('Highlight with input lying inside a textarea', function () {
		beforeAll(function () {
			highlighter.highlight("textarea");
		});
		
		it('Should not highlight the word textarea in the sample text inside textarea element', function () {
			expect($('#txtarea-highlight')).not.toContainHtml('<span rel="textarea" class="highlight">textarea</span>');
		});
	});

	describe('Highlight with input lying inside a span element having class=\'highlight\'', function () {
		beforeAll(function () {
			highlighter.highlight("span");
		});
		
		it('Should not highlight the word span in the sample text inside span element', function () {
			expect($('#span-highlight')).not.toContainHtml('<span rel="span" class="highlight">span</span>');
		});
	});

	describe('Unhighlight with input lying inside a textarea', function () {
		beforeAll(function () {
			highlighter.unhighlight("textarea");
		});

		it('Should not highlight the word textarea in the sample text inside textarea element', function () {
			expect($('#txtarea-highlight')).not.toContainHtml('<span rel="textarea" class="highlight">textarea</span>');
		});
	});

	describe('Highlight single text with AutoUnhighlight false', function () {
		beforeAll(function () {
			highlighterAutohighlight.highlight(["text"]);
		});

		it('Should highlight sample text at depth 1', function () {
			expect($('#text-depth-1').html()).toContain('class=\"highlight\"');
		});

		it('Should highlight sample text at depth 2', function () {
			expect($('#text-depth-2').html()).toContain('class=\"highlight\"');
		});
	});
});
