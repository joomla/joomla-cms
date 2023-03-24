<?php

/**
 * @package     Joomla.Tests
 * @subpackage  Api.tests
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

use Codeception\Util\FileSystem;
use Codeception\Util\HttpCode;

/**
 * Class MediaCest.
 *
 * Basic com_media (files) tests.
 *
 * @since   4.1.0
 */
class MediaCest
{
    /**
     * The name of the test directory, which gets deleted after each test.
     *
     * @var     string
     *
     * @since   4.1.0
     */
    private $testDirectory = 'test-dir';

    /**
     * Runs before every test.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @since   4.1.0
     *
     * @throws Exception
     */
    public function _before(ApiTester $I)
    {
        if (file_exists($this->getImagesDirectory($I))) {
            FileSystem::deleteDir($this->getImagesDirectory($I));
        }

        // Copied from \Step\Acceptance\Administrator\Media:createDirectory()
        $oldUmask     = @umask(0);
        @mkdir($this->getImagesDirectory($I), 0755, true);

        if (!empty($user = $I->getConfig('localUser'))) {
            @chown($this->getImagesDirectory($I), $user);
        }

        @umask($oldUmask);
    }

    /**
     * Runs after every test.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @since   4.1.0
     *
     * @throws Exception
     */
    public function _after(ApiTester $I)
    {
        // Delete the test directory
        FileSystem::deleteDir($this->getImagesDirectory($I));
    }

    /**
     * Test the GET media adapter endpoint of com_media from the API.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
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
     * Test the GET media adapter endpoint for a single adapter of com_media from the API.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
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
     * Test the GET media files endpoint of com_media from the API.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function testGetFiles(ApiTester $I)
    {
        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendGET('/media/files');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['data' => ['attributes' => ['type' => 'dir', 'name' => 'banners']]]);
        $I->seeResponseContainsJson(['data' => ['attributes' => ['type' => 'file', 'name' => 'joomla_black.png']]]);
    }

    /**
     * Test the GET media files endpoint of com_media from the API.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function testGetFilesInSubfolder(ApiTester $I)
    {
        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendGET('/media/files/sampledata/cassiopeia/');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['data' => ['attributes' => ['type' => 'file', 'name' => 'nasa1-1200.jpg']]]);
    }

    /**
     * Test the GET media files endpoint of com_media from the API.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function testGetFilesWithAdapter(ApiTester $I)
    {
        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendGET('/media/files/local-images:/sampledata/cassiopeia/');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['data' => ['attributes' => ['type' => 'file', 'name' => 'nasa1-1200.jpg']]]);
    }

    /**
     * Test the GET media files endpoint of com_media from the API.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function testSearchFiles(ApiTester $I)
    {
        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendGET('/media/files?filter[search]=joomla');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['data' => ['attributes' => ['type' => 'file', 'name' => 'joomla_black.png']]]);
        $I->dontSeeResponseContainsJson(['data' => ['attributes' => ['type' => 'dir', 'name' => 'powered_by.png']]]);
        $I->dontSeeResponseContainsJson(['data' => ['attributes' => ['type' => 'dir', 'name' => 'banners']]]);
    }

    /**
     * Test the GET media files endpoint for a single file of com_media from the API.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function testGetFile(ApiTester $I)
    {
        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendGET('/media/files/joomla_black.png');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['data' => ['attributes' => ['type' => 'file', 'name' => 'joomla_black.png']]]);
        $I->dontSeeResponseContainsJson(['data' => ['attributes' => ['url' => $I->getConfig('url') . '/images/joomla_black.png']]]);
    }

    /**
     * Test the GET media files endpoint for a single file of com_media from the API.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function testGetFileWithUrl(ApiTester $I)
    {
        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendGET('/media/files/joomla_black.png?url=1');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['data' => ['attributes' => ['url' => $I->getConfig('url') . '/images/joomla_black.png']]]);
    }

    /**
     * Test the GET media files endpoint for a single file of com_media from the API.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function testGetFolder(ApiTester $I)
    {
        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendGET('/media/files/sampledata/cassiopeia');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['data' => ['attributes' => ['type' => 'dir', 'name' => 'cassiopeia']]]);
    }

    /**
     * Test the POST media files endpoint of com_media from the API without adapter information.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function testCreateFileWithoutAdapter(ApiTester $I)
    {
        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendPost(
            '/media/files',
            [
                'path'    => $this->testDirectory . '/test.jpg',
                'content' => base64_encode(file_get_contents(codecept_data_dir() . '/com_media/test-image-1.jpg')),
            ]
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['data' => ['attributes' => ['type' => 'file', 'name' => 'test.jpg']]]);
    }

    /**
     * Test the POST media files endpoint of com_media from the API without adapter information.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function testCreateFolderWithoutAdapter(ApiTester $I)
    {
        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendPost(
            '/media/files',
            ['path' => $this->testDirectory . '/test-from-create']
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['data' => ['attributes' => ['type' => 'dir', 'name' => 'test-from-create']]]);
    }

    /**
     * Test the POST media files endpoint of com_media from the API with adapter information.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function testCreateFileWithAdapter(ApiTester $I)
    {
        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendPost(
            '/media/files',
            [
                'path'    => 'local-images:/' . $this->testDirectory . '/test.jpg',
                'content' => base64_encode(file_get_contents(codecept_data_dir() . '/com_media/test-image-1.jpg')),
            ]
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['data' => ['attributes' => ['type' => 'file', 'name' => 'test.jpg']]]);
    }

    /**
     * Test the POST media files endpoint of com_media from the API with adapter information.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function testCreateFolderWithAdapter(ApiTester $I)
    {
        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendPost(
            '/media/files',
            ['path' => 'local-images:/' . $this->testDirectory . '/test-from-create']
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['data' => ['attributes' => ['type' => 'dir', 'name' => 'test-from-create']]]);
    }

    /**
     * Test the PATCH media files endpoint of com_media from the API without adapter information.
     *
     * @param  ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function testUpdateFileWithoutAdapter(ApiTester $I)
    {
        file_put_contents($this->getImagesDirectory($I) . '/override.jpg', '1');

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendPatch(
            '/media/files/' . $this->testDirectory . '/override.jpg',
            [
                'path'    => $this->testDirectory . '/override.jpg',
                'content' => base64_encode(file_get_contents(codecept_data_dir() . '/com_media/test-image-1.jpg')),
            ]
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['data' => ['attributes' => ['type' => 'file', 'name' => 'override.jpg']]]);
        $I->dontSeeResponseContainsJson(['data' => ['attributes' => ['content' => '1']]]);
    }

    /**
     * Test the PATCH media files endpoint of com_media from the API without adapter information.
     *
     * @param  ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function testUpdateFolderWithoutAdapter(ApiTester $I)
    {
        mkdir($this->getImagesDirectory($I) . '/override');

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendPatch(
            '/media/files/' . $this->testDirectory . '/override',
            ['path' => $this->testDirectory . '/override-new']
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['data' => ['attributes' => ['type' => 'dir', 'name' => 'override-new']]]);
    }

    /**
     * Test the PATCH media files endpoint of com_media from the API wit adapter information.
     *
     * @param  ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function testUpdateFileWithAdapter(ApiTester $I)
    {
        file_put_contents($this->getImagesDirectory($I) . '/override.jpg', '1');

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendPatch(
            '/media/files/local-images:/' . $this->testDirectory . '/override.jpg',
            [
                'path'    => 'local-images:/' . $this->testDirectory . '/override.jpg',
                'content' => base64_encode(file_get_contents(codecept_data_dir() . '/com_media/test-image-1.jpg')),
            ]
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['data' => ['attributes' => ['type' => 'file', 'name' => 'override.jpg']]]);
        $I->dontSeeResponseContainsJson(['data' => ['attributes' => ['content' => '1']]]);
    }

    /**
     * Test the PATCH media files endpoint of com_media from the API with adapter information.
     *
     * @param  ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function testUpdateFolderWithAdapter(ApiTester $I)
    {
        mkdir($this->getImagesDirectory($I) . '/override');

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendPatch(
            '/media/files/local-images:/' . $this->testDirectory . '/override',
            ['path' => 'local-images:/' . $this->testDirectory . '/override-new']
        );

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseContainsJson(['data' => ['attributes' => ['type' => 'dir', 'name' => 'override-new']]]);
    }

    /**
     * Test the DELETE media files endpoint of com_media from the API without adapter information.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function testDeleteFileWithoutAdapter(ApiTester $I)
    {
        touch($this->getImagesDirectory($I) . '/todelete.jpg');

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendDelete('/media/files/' . $this->testDirectory . '/todelete.jpg');

        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }

    /**
     * Test the DELETE media files endpoint of com_media from the API without adapter information.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function testDeleteFolderWithoutAdapter(ApiTester $I)
    {
        mkdir($this->getImagesDirectory($I) . '/todelete');

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendDelete('/media/files/' . $this->testDirectory . '/todelete');

        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }

    /**
     * Test the DELETE media files endpoint of com_media from the API with adapter information.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function testDeleteFileWithAdapter(ApiTester $I)
    {
        touch($this->getImagesDirectory($I) . '/todelete.jpg');

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendDelete('/media/files/local-images:/' . $this->testDirectory . '/todelete.jpg');

        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }

    /**
     * Test the DELETE media files endpoint of com_media from the API with adapter information.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @return  void
     *
     * @since   4.1.0
     */
    public function testDeleteFolderWithAdapter(ApiTester $I)
    {
        mkdir($this->getImagesDirectory($I) . '/todelete');

        $I->amBearerAuthenticated($I->getBearerToken());
        $I->haveHttpHeader('Accept', 'application/vnd.api+json');
        $I->sendDelete('/media/files/local-images:/' . $this->testDirectory . '/todelete');

        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
    }

    /**
     * Returns the absolute tmp image folder path to work on.
     *
     * @param   ApiTester  $I  Api tester
     *
     * @return  string  The absolute folder path
     *
     * @since   4.1.0
     */
    private function getImagesDirectory(ApiTester $I): string
    {
        return $I->getConfig('cmsPath') . '/images/' . $this->testDirectory;
    }
}
