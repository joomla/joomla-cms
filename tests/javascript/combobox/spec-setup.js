/**
 * @package     Joomla.Tests
 * @subpackage  JavaScript Tests
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since       3.6.3
 * @version     1.0.0
 */

define(['jquery', 'text!testsRoot/combobox/fixtures/fixture.html', 'libs/combobox'], function ($, fixture) {
	$('body').append(fixture);

	$('div.combobox').ComboTransform({updateList : true});
});
