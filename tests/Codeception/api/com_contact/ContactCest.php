<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Api.tests
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Codeception\Util\HttpCode;

/**
 * Class ContactCest.
 *
 * Basic com_contact (contact) tests.
 *
 * @since   4.0.0
 */
class ContactCest
{
	/**
	 * Api test before running.
	 *
	 * @param   mixed   ApiTester  $I  Api tester
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 */
	public function _before(ApiTester $I)
	{
	}

	/**
	 * Api test after running.
	 *
	 * @param   mixed   ApiTester  $I  Api tester
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 */
	public function _after(ApiTester $I)
	{
	}

	/**
	 * Test the crud endpoints of com_contact from the API.
	 *
	 * @param   mixed   ApiTester  $I  Api tester
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 *
	 * @TODO: Make these separate tests but requires sample data being installed so there are existing contacts
	 */
	public function testCrudOnArticle(ApiTester $I)
	{
		$I->amHttpAuthenticated('admin', 'admin');
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');

		$testarticle = [
			'alias' => 'contact-the-ceo',
			'catid' => 4,
			'language' => '*',
			'name' => 'Francine Blogs'
		];

		$I->sendPOST('/contact', $testarticle);

		$I->seeResponseCodeIs(HttpCode::OK);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendGET('/contact/1');
		$I->seeResponseCodeIs(HttpCode::OK);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendPATCH('/contact/1', ['name' => 'Frankie Blogs', 'state' => -2]);
		$I->seeResponseCodeIs(HttpCode::OK);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendDELETE('/contact/1');
		$I->seeResponseCodeIs(HttpCode::NO_CONTENT);
	}
}
