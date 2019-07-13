<?php

/**
 * Class contentCest
 *
 * Basic com_content (article) tests
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since   4.0.0
 */
class ContentCest
{
	/**
	 * @param   ApiTester  $I
	 *
	 *
	 * @since   4.0.0
	 */
	public function _before(ApiTester $I)
	{
	}

	/**
	 * @param   ApiTester  $I
	 *
	 *
	 * @since   4.0.0
	 */
	public function _after(ApiTester $I)
	{
	}

	/**
	 * Test the crud endpoints of com_content from the API
	 *
	 * @param ApiTester $I
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
			'catid' => 1,
			'articletext' => 'A dummy article to save to the database',
			'metakey' => '',
			'metadesc' => '',
			'language' => '*',
			'alias' => 'tobias'
		];

		$I->sendPOST('/article', $testarticle);

		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendGET('/article/1');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendGET('/article/1', ['title' => 'Another Title']);
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::OK);

		$I->amHttpAuthenticated('admin', 'admin');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendDELETE('/article/1');
		$I->seeResponseCodeIs(\Codeception\Util\HttpCode::NO_CONTENT);
	}
}
