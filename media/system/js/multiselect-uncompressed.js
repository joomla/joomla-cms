/**
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * JavaScript behavior to allow shift select in administrator grids
 */
(function($) {
    
    Joomla = window.Joomla || {};
    var $boxes;
    Joomla.JMultiSelect = function(table) {
        var $last,
        
        initialize = function(table) {
            $boxes = $('#' + table).find('input[type=checkbox]');
            $boxes.on('click', function(e) {
                doselect(e)
            });
        },
        
        doselect = function(e) {
            var $current = $(e.target), isChecked, lastIndex, currentIndex, swap;
            if (e.shiftKey && $last.length) {
                isChecked = $current.is(':checked');
                lastIndex = $boxes.index($last);
                currentIndex = $boxes.index($current);
                if (currentIndex < lastIndex) {
                    // handle selection from bottom up
                    swap = lastIndex;
                    lastIndex = currentIndex;
                    currentIndex = swap;
                }
                $boxes.slice(lastIndex, currentIndex + 1).attr('checked', isChecked);
            }

            $last = $current;
        }
        initialize(table);
    }

})(jQuery);
