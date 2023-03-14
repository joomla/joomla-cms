<?php

/**
 * @package     Joomla.Tests
 * @subpackage  Api.tests
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

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
        $I->amBearerAuthenticated($I->getBearerToken());
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
        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendGET('/not/existing/1');
        $I->seeResponseCodeIs(Codeception\Util\HttpCode::NOT_FOUND);
    }
}
