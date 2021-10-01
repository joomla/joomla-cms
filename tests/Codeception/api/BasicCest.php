<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Api.tests
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


/**
 * Class basicCest.
 *
 * Basic API function tests.
 *
 * @since   4.0.0
 */
class BasicCest
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
		// TODO: Improve this to retrieve a specific ID to replace with a known ID
		$desiredUserId = 3;
		$I->updateInDatabase('users', ['id' => 3], []);
		$I->updateInDatabase('user_usergroup_map', ['user_id' => 3], []);
		$enabledData = ['user_id' => $desiredUserId, 'profile_key' => 'joomlatoken.enabled', 'profile_value' => 1];
		$tokenData = ['user_id' => $desiredUserId, 'profile_key' => 'joomlatoken.token', 'profile_value' => 'dOi2m1NRrnBHlhaWK/WWxh3B5tqq1INbdf4DhUmYTI4='];
		$I->haveInDatabase('user_profiles', $enabledData);
		$I->haveInDatabase('user_profiles', $tokenData);
	}

	/**
	 * Api test after running.
	 *
	 * @param   mixed   ApiTester  $I  Api tester
	 *
	 * @return void
	 * @since   4.0.0
	 */
	public function _after(ApiTester $I)
	{
	}

	/**
	 * Test logging in with wrong credentials.
	 *
	 * @param   mixed   ApiTester  $I  Api tester
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 */
	public function testWrongCredentials(ApiTester $I)
	{
		$I->amBearerAuthenticated('BADTOKEN');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendGET('/content/articles/1');
		$I->seeResponseCodeIs(Codeception\Util\HttpCode::UNAUTHORIZED);
	}

	/**
	 * Test content negotiation fails when accepting no json.
	 *
	 * @param   mixed   ApiTester  $I  Api tester
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 */
	public function testContentNegotiation(ApiTester $I)
	{
		$I->amBearerAuthenticated('c2hhMjU2OjM6ZTJmMjJlYTNlNTU0NmM1MDJhYTIzYzMwN2MxYzAwZTQ5NzJhMWRmOTUyNjY5MTk2YjE5ODJmZWMwZTcxNzgwMQ==');
		$I->haveHttpHeader('Accept', 'text/xml');
		$I->sendGET('/content/articles/1');
		$I->seeResponseCodeIs(Codeception\Util\HttpCode::NOT_ACCEPTABLE);
	}

	/**
	 * Test not found Resources return 404.
	 *
	 * @param   mixed   ApiTester  $I  Api tester
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 */
	public function testRouteNotFound(ApiTester $I)
	{
		$I->amBearerAuthenticated('c2hhMjU2OjM6ZTJmMjJlYTNlNTU0NmM1MDJhYTIzYzMwN2MxYzAwZTQ5NzJhMWRmOTUyNjY5MTk2YjE5ODJmZWMwZTcxNzgwMQ==');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendGET('/not/existing/1');
		$I->seeResponseCodeIs(Codeception\Util\HttpCode::NOT_FOUND);
	}
}
