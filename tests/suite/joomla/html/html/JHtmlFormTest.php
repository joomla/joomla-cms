<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM.'/joomla/html/html/form.php';

/**
 * Test class for JHtmlForm.
 *
 * @since  11.1
 */
class JHtmlFormTest extends PHPUnit_Framework_TestCase
{
	/**
	 * @todo Implement testToken().
	 */
	public function testToken()
	{
		$token = JSession::getFormToken();

		$this->assertThat(
			JHtmlForm::token(),
			$this->equalTo('<input type="hidden" name="' . $token . '" value="1" />')
		);
	}
}
