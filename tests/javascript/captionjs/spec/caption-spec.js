/**
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @package     Joomla
 * @subpackage  JavaScript Tests
 * @since       3.6
 * @version     1.0.0
 */

define(['jquery', 'testsRoot/captionjs/spec/caption-spec-setup', 'jasmineJquery'], function ($) {

    describe('JCaption applied to single image', function () {
        it('Should have caption as "Joomla Title 1" under image', function () {
            expect($('.single p')).toHaveText('Joomla Title 1');
        });
    });

    describe('JCaption applied for multiple images', function () {
        it('Should have caption "Joomla Title 1" under image 1', function () {
            expect($('.multiple p').first()).toHaveText('Joomla Title 1');
        });

        it('Should have caption as "Joomla Title 2" under image 2', function() {
            expect($('.multiple p').last()).toHaveText('Joomla Title 2');
        });
    });

    describe('JCaption with empty title attribute value', function () {
        it('Should not have a <p> element inside the image container', function () {
            expect($('.empty #empty-title').parent()).not.toContainElement('p');
        });
    });

    describe('JCaption with no additional options', function () {
        it('Should have container CSS as {width: element.width, float: none}', function () {
            expect($('.options #no-options').parent()).toHaveCss({
                float: 'none',
                width: $('.options #no-options').width() + 'px'
            });
        });
    });

    describe('JCaption with additional options', function () {
        it('Should have 2 elements with class right', function () {
            expect($('.options .right').length).toEqual(2);
        });

        it('Should have container width as 100 when element width attribute is set to 100', function () {
            expect($('.options #width-attr').parent().width()).toEqual(100);
        });

        it('Should have container width as 100 when element style is set to width: 100px', function () {
            expect($('.options #width-style').parent().width()).toEqual(100);
        });

        it('Should have float: right in container CSS when element attribute align is set to right', function () {
            expect($('.options #align-attr').parent()).toHaveCss({
                float: 'right'
            });
        });

        it('Should have float: right in container CSS when element style is set to float: right', function () {
            expect($('.options #align-style').parent()).toHaveCss({
                float: 'right'
            });
        });
    });
});
