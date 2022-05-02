<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Api.tests
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Codeception\Util\HttpCode;

/**
 * Class BannerCest.
 *
 * Basic com_banners (banner) tests.
 *
 * @since   4.0.0
 */
class BannerCest
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
	 *
	 * @since   4.0.0
	 */
	public function _after(ApiTester $I)
	{
	}

	/**
	 * Test the crud endpoints of com_banners from the API.
	 *
	 * @param   mixed   ApiTester  $I  Api tester
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 *
	 * @TODO: Make these separate tests but requires sample data being installed so there are existing banners
	 */
	public function testCrudOnBanner(ApiTester $I)
	{
		$I->amBearerAuthenticated('c2hhMjU2OjM6ZTJmMjJlYTNlNTU0NmM1MDJhYTIzYzMwN2MxYzAwZTQ5NzJhMWRmOTUyNjY5MTk2YjE5ODJmZWMwZTcxNzgwMQ==');
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');

		$testBanner = [
			'name' => 'My Custom Advert',
			'catid' => 3,
			'description' => '',
			'custombannercode' => '',
			'metakey' => '',
			'params' => [
				'imageurl' => '',
				'width' => '',
				'height' => '',
				'alt' => ''
			],
		];

		$I->sendPOST('/banners', $testBanner);

		$I->seeResponseCodeIs(HttpCode::OK);

		$I->amBearerAuthenticated('c2hhMjU2OjM6ZTJmMjJlYTNlNTU0NmM1MDJhYTIzYzMwN2MxYzAwZTQ5NzJhMWRmOTUyNjY5MTk2YjE5ODJmZWMwZTcxNzgwMQ==');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendGET('/banners/1');
		$I->seeResponseCodeIs(HttpCode::OK);

		$I->amBearerAuthenticated('c2hhMjU2OjM6ZTJmMjJlYTNlNTU0NmM1MDJhYTIzYzMwN2MxYzAwZTQ5NzJhMWRmOTUyNjY5MTk2YjE5ODJmZWMwZTcxNzgwMQ==');
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');

		// Category is a required field for this patch request for now TODO: Remove this dependency
		$I->sendPATCH('/banners/1', ['name' => 'Different Custom Advert', 'state' => -2, 'catid' => 3]);
		$I->seeResponseCodeIs(HttpCode::OK);

		$I->amBearerAuthenticated('c2hhMjU2OjM6ZTJmMjJlYTNlNTU0NmM1MDJhYTIzYzMwN2MxYzAwZTQ5NzJhMWRmOTUyNjY5MTk2YjE5ODJmZWMwZTcxNzgwMQ==');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendDELETE('/banners/1');
		$I->seeResponseCodeIs(HttpCode::NO_CONTENT);
	}

	/**
	 * Test the category crud endpoints of com_banners from the API.
	 *
	 * @param   mixed   ApiTester  $I  Api tester
	 *
	 * @return void
	 *
	 * @since   4.0.0
	 *
	 * @TODO: Make these separate tests but requires sample data being installed so there are existing categories
	 */
	public function testCrudOnCategory(ApiTester $I)
	{
		$I->amBearerAuthenticated('c2hhMjU2OjM6ZTJmMjJlYTNlNTU0NmM1MDJhYTIzYzMwN2MxYzAwZTQ5NzJhMWRmOTUyNjY5MTk2YjE5ODJmZWMwZTcxNzgwMQ==');
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');

		$testarticle = [
			'title' => 'A test category',
			'parent_id' => 3
		];

		$I->sendPOST('/banners/categories', $testarticle);

		$I->seeResponseCodeIs(HttpCode::OK);
		$categoryId = $I->grabDataFromResponseByJsonPath('$.data.id')[0];

		$I->amBearerAuthenticated('c2hhMjU2OjM6ZTJmMjJlYTNlNTU0NmM1MDJhYTIzYzMwN2MxYzAwZTQ5NzJhMWRmOTUyNjY5MTk2YjE5ODJmZWMwZTcxNzgwMQ==');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendGET('/banners/categories/' . $categoryId);
		$I->seeResponseCodeIs(HttpCode::OK);

		$I->amBearerAuthenticated('c2hhMjU2OjM6ZTJmMjJlYTNlNTU0NmM1MDJhYTIzYzMwN2MxYzAwZTQ5NzJhMWRmOTUyNjY5MTk2YjE5ODJmZWMwZTcxNzgwMQ==');
		$I->haveHttpHeader('Content-Type', 'application/json');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');

		// Trash in order to allow the delete in the next step
		$I->sendPATCH('/banners/categories/' . $categoryId, ['title' => 'Another Title', 'published' => -2]);
		$I->seeResponseCodeIs(HttpCode::OK);

		$I->amBearerAuthenticated('c2hhMjU2OjM6ZTJmMjJlYTNlNTU0NmM1MDJhYTIzYzMwN2MxYzAwZTQ5NzJhMWRmOTUyNjY5MTk2YjE5ODJmZWMwZTcxNzgwMQ==');
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendDELETE('/banners/categories/' . $categoryId);
		$I->seeResponseCodeIs(HttpCode::NO_CONTENT);
	}
}
