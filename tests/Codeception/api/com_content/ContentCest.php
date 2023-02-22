<?php

/**
 * @package     Joomla.Tests
 * @subpackage  Api.tests
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

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
        $I->deleteFromDatabase('content');
        $I->deleteFromDatabase('categories', ['id >' => 7]);
    }

    /**
     * Test the article crud endpoints of com_content from the API.
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
        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');

        $testarticle = [
            'title'       => 'Just for you',
            'catid'       => 2,
            'articletext' => 'A dummy article to save to the database',
            'language'    => '*',
            'alias'       => 'tobias',
        ];

        $I->sendPOST('/content/articles', $testarticle);

        $I->seeResponseCodeIs(HttpCode::OK);
        $id = $I->grabDataFromResponseByJsonPath('$.data.id')[0];

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendGET('/content/articles/' . $id);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendPATCH('/content/articles/' . $id, ['title' => 'Another Title', 'state' => -2, 'catid' => 2]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendDELETE('/content/articles/' . $id);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }

    /**
     * Test the category crud endpoints of com_content from the API.
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
            'parent_id' => 2,
            'params'    => [
                'workflow_id' => 'inherit',
            ],
        ];

        $I->sendPOST('/content/categories', $testarticle);

        $I->seeResponseCodeIs(HttpCode::OK);
        $categoryId = $I->grabDataFromResponseByJsonPath('$.data.id')[0];

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendGET('/content/categories/' . $categoryId);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendPATCH('/content/categories/' . $categoryId, ['title' => 'Another Title', 'params' => ['workflow_id' => 'inherit'], 'published' => -2]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendDELETE('/content/categories/' . $categoryId);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }
}
