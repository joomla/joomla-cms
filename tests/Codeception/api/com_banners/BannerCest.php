<?php

/**
 * @package     Joomla.Tests
 * @subpackage  Api.tests
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

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
        $I->deleteFromDatabase('banners');
        $I->deleteFromDatabase('categories', ['id >' => 7]);
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
        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');

        $testBanner = [
            'name'             => 'My Custom Advert',
            'catid'            => 3,
            'description'      => '',
            'custombannercode' => '',
            'metakey'          => '',
            'params'           => [
                'imageurl' => '',
                'width'    => '',
                'height'   => '',
                'alt'      => '',
            ],
        ];

        $I->sendPOST('/banners', $testBanner);

        $I->seeResponseCodeIs(HttpCode::OK);
        $id = $I->grabDataFromResponseByJsonPath('$.data.id')[0];

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendGET('/banners/' . $id);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');

        // Category is a required field for this patch request for now @todo: Remove this dependency
        $I->sendPATCH('/banners/' . $id, ['name' => 'Different Custom Advert', 'state' => -2, 'catid' => 3]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendDELETE('/banners/' . $id);
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
        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');

        $testarticle = [
            'title'     => 'A test category',
            'parent_id' => 3,
        ];

        $I->sendPOST('/banners/categories', $testarticle);

        $I->seeResponseCodeIs(HttpCode::OK);
        $categoryId = $I->grabDataFromResponseByJsonPath('$.data.id')[0];

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendGET('/banners/categories/' . $categoryId);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');

        // Trash in order to allow the delete in the next step
        $I->sendPATCH('/banners/categories/' . $categoryId, ['title' => 'Another Title', 'published' => -2]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendDELETE('/banners/categories/' . $categoryId);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }
}
