<?php

/**
 * Class basicCest
 *
 * Basic API function tests
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 *
 * @since   4.0.0
 */
class BasicCest
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
	 * Test logging in with wrong credentials
	 *
	 * @param ApiTester $I
	 *
	 * @since   4.0.0
	 */
	public function testWrongCredentials(ApiTester $I)
	{
		$I->amHttpAuthenticated('admin', 'wrong');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendGET('/article/1');
		$I->seeResponseCodeIs(Codeception\Util\HttpCode::UNAUTHORIZED);
	}

	/**
	 * Test content negotation fails when accepting no json
	 *
	 * @param ApiTester $I
	 *
	 * @since   4.0.0
	 */
	public function testContentNegotation(ApiTester $I)
	{
		$I->amHttpAuthenticated('admin', 'admin');
		$I->haveHttpHeader('Accept', 'text/xml');
		$I->sendGET('/article/1');
		$I->seeResponseCodeIs(Codeception\Util\HttpCode::NOT_ACCEPTABLE);
	}

	/**
	 * Test not found Resources return 404
	 *
	 * @param ApiTester $I
	 *
	 * @since   4.0.0
	 */
	public function testRouteNotFound(ApiTester $I)
	{
		$I->amHttpAuthenticated('admin', 'admin');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendGET('/not/existing/1');
		$I->seeResponseCodeIs(Codeception\Util\HttpCode::NOT_FOUND);
	}
}
