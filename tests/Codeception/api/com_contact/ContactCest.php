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
        $I->deleteFromDatabase('contact_details');
        $I->deleteFromDatabase('categories', ['id >' => 7]);
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
    public function testCrudOnContact(ApiTester $I)
    {
        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');

        $testarticle = [
            'alias'    => 'contact-the-ceo',
            'catid'    => 4,
            'language' => '*',
            'name'     => 'Francine Blogs',
        ];

        $I->sendPOST('/contacts', $testarticle);

        $I->seeResponseCodeIs(HttpCode::OK);
        $id = $I->grabDataFromResponseByJsonPath('$.data.id')[0];

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendGET('/contacts/' . $id);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');

        // Category is a required field for this patch request for now @todo: Remove this dependency
        $I->sendPATCH('/contacts/' . $id, ['name' => 'Frankie Blogs', 'catid' => 4, 'published' => -2]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendDELETE('/contacts/' . $id);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }

    /**
     * Test the category crud endpoints of com_contact from the API.
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

        $testContact = [
            'title'     => 'A test category',
            'parent_id' => 4,
            'params'    => [
                'workflow_id' => 'inherit',
            ],
        ];

        $I->sendPOST('/contacts/categories', $testContact);

        $I->seeResponseCodeIs(HttpCode::OK);
        $categoryId = $I->grabDataFromResponseByJsonPath('$.data.id')[0];

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendGET('/contacts/categories/' . $categoryId);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendPATCH('/contacts/categories/' . $categoryId, ['title' => 'Another Title', 'published' => -2]);
        $I->seeResponseCodeIs(HttpCode::OK);

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendDELETE('/contacts/categories/' . $categoryId);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }
}
