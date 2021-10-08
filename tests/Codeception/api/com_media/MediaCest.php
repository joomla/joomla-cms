<?php
/**
 * @package     Joomla.Tests
 * @subpackage  Api.tests
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Codeception\Util\HttpCode;

/**
 * Class MediaCest.
 *
 * Basic com_media (files) tests.
 *
 * @since   __DEPLOY_VERSION__
 */
class MediaCest
{
	/**
	 * Test the media adapter endpoint of com_media from the API.
	 *
	 * @param   mixed   ApiTester  $I  Api tester
	 *
	 * @return void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetAdapters(ApiTester $I)
	{
		$I->amBearerAuthenticated($I->getBearerToken());
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendGET('/media/adapters');
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseContainsJson(['provider_id' => 'local', 'name' => 'images']);
	}

	/**
	 * Test the media adapter endpoint of com_media from the API.
	 *
	 * @param   mixed   ApiTester  $I  Api tester
	 *
	 * @return void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetAdapter(ApiTester $I)
	{
		$I->amBearerAuthenticated($I->getBearerToken());
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendGET('/media/adapters/local-images');
		$I->seeResponseCodeIs(HttpCode::OK);
		$I->seeResponseContainsJson(['provider_id' => 'local', 'name' => 'images']);
	}

	/**
	 * Test the media files endpoint of com_media from the API.
	 *
	 * @param   mixed   ApiTester  $I  Api tester
	 *
	 * @return void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function testGetFiles(ApiTester $I)
	{
		$I->amBearerAuthenticated($I->getBearerToken());
		$I->haveHttpHeader('Accept', 'application/vnd.api+json');
		$I->sendGET('/media/files');
		$I->seeResponseCodeIs(HttpCode::OK);
	}
}
