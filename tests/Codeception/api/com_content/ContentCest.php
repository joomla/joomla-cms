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
 * Class ContentCest.
 *
 * Basic com_content (article) tests.
 *
 * @since   4.0.0
 */
class ContentCest
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
	 * Test the crud endpoints of com_content from the API.
	 *
	 * @param   mixed   ApiTester  $I  Api tester
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 *
	 * @TODO: Make these separate tests but requires sample data being installed so there are existing articles
	 */
	public function testCrudOnArticle(ApiTester $I)
	{
		$I->amHttpAuthenticated('admin', 'admin');
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');

		$testarticle = [
			'title' => 'Just for you',
			'catid' => 2,
			'articletext' => 'A dummy article to save to the database',
			'language' => '*',
			'alias' => 'tobias'
		];

		$I->sendPOST('/content/article', $testarticle);

		$I->seeResponseCodeIs(HttpCode::OK);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendGET('/content/article/1');
		$I->seeResponseCodeIs(HttpCode::OK);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendGET('/content/article/1', ['title' => 'Another Title']);
		$I->seeResponseCodeIs(HttpCode::OK);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendDELETE('/content/article/1');
		$I->seeResponseCodeIs(HttpCode::NO_CONTENT);
	}
}
