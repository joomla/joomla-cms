/**
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @package     Joomla
 * @subpackage  JavaScript Tests
 * @since       3.6
 * @version     1.0.0
 */

define(['jquery', 'testsRoot/captionjs/spec/javascripts/caption-spec-setup', 'jasmineJquery'], function ($) {

    describe('JCaption initialized with valid selector', function () {
        it('Should have caption as "Joomla logo" under image 1', function () {
            expect($('p')[0]).toHaveText('Joomla logo');
        });

        it('Should have caption as "Joomla" under image 2', function() {
            expect($('p')[1]).toHaveText('Joomla');
        });
    });

    describe('JCaption with align and width options', function () {
        it('Should have and element with class right', function () {
            expect($('.right')).toExist();
        });

        it('Should have specified CSS', function () {
            expect($('.right')).toHaveCss({
                float: 'right',
                width: '100px'
            });
        });
    });
});
