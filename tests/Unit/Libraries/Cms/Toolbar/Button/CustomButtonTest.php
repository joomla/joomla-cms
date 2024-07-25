<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Toolbar
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Toolbar\Button;

use Joomla\CMS\Toolbar\Button\CustomButton;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for CustomButton.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Toolbar
 * @since       3.0
 */
class CustomButtonTest extends UnitTestCase
{
    /**
     * Tests the fetchButton method
     *
     * @return  void
     *
     * @since   3.0
     */
    public function testFetchButton()
    {
        $button = new CustomButton();
        $html   = '<div class="custom-button"><a href="#">My Custom Button</a></div>';

        $this->assertEquals($html, $button->fetchButton('Custom', $html));
    }
}
