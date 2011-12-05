<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/html/html/user.php';

/**
 * Test class for JHtmlUser.
 *
 * @since  11.4
 */
class JHtmlUserTest extends JoomlaDatabaseTestCase
{
	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  xml dataset
	 *
	 * @since   11.4
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(__DIR__.'/testfiles/JHtmlTest.xml');
	}

    /**
	 * @covers JHtmlUser::groups
     * @todo Implement testGroups().
     */
    public function testGroups()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }

    /**
	 * @covers JHtmlUser::userlist
     * @todo Implement testUserlist().
     */
    public function testUserlist()
    {
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
    }
}
