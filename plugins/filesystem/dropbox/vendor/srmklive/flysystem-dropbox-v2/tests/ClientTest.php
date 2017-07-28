<?php

namespace Srmklive\Dropbox\Test;

use GuzzleHttp\Client as HttpClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Srmklive\Dropbox\Client\DropboxClient as Client;

class ClientTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $client = new Client('test_token');

        $this->assertInstanceOf(Client::class, $client);
    }

    /** @test */
    public function it_can_copy_a_file()
    {
        $expectedResponse = [
            '.tag' => 'file',
            'name' => 'Prime_Numbers.txt',
        ];

        $mockHttpClient = $this->mock_http_request(
            json_encode($expectedResponse),
            'https://api.dropboxapi.com/2/files/copy',
            [
                'json' => [
                    'from_path' => '/from/path/file.txt',
                    'to_path'   => '/to/path/file.txt',
                ],
            ]
        );

        $client = new Client('test_token', $mockHttpClient);

        $this->assertEquals($expectedResponse, $client->copy('from/path/file.txt', 'to/path/file.txt'));
    }

    /** @test */
    public function it_can_create_a_folder()
    {
        $mockHttpClient = $this->mock_http_request(
            json_encode(['name' => 'math']),
            'https://api.dropboxapi.com/2/files/create_folder',
            [
                'json' => [
                    'path' => '/Homework/math',
                ],
            ]
        );

        $client = new Client('test_token', $mockHttpClient);

        $this->assertEquals(['.tag' => 'folder', 'name' => 'math'], $client->createFolder('Homework/math'));
    }

    /** @test */
    public function it_can_delete_a_folder()
    {
        $mockHttpClient = $this->mock_http_request(
            json_encode(['name' => 'math']),
            'https://api.dropboxapi.com/2/files/delete',
            [
                'json' => [
                    'path' => '/Homework/math',
                ],
            ]
        );

        $client = new Client('test_token', $mockHttpClient);

        $this->assertEquals(['name' => 'math'], $client->delete('Homework/math'));
    }

    /** @test */
    public function it_can_download_a_file()
    {
        $expectedResponse = $this->getMockBuilder(StreamInterface::class)
            ->getMock();
        $expectedResponse->expects($this->once())
            ->method('isReadable')
            ->willReturn(true);

        $mockHttpClient = $this->mock_http_request(
            $expectedResponse,
            'https://content.dropboxapi.com/2/files/download',
            [
                'headers' => [
                    'Dropbox-API-Arg' => json_encode(['path' => '/Homework/math/answers.txt']),
                ],
                'body'    => '',
            ]
        );

        $client = new Client('test_token', $mockHttpClient);

        $this->assertTrue(is_resource($client->download('Homework/math/answers.txt')));
    }

    /** @test */
    public function it_can_retrieve_metadata()
    {
        $mockHttpClient = $this->mock_http_request(
            json_encode(['name' => 'math']),
            'https://api.dropboxapi.com/2/files/get_metadata',
            [
                'json' => [
                    'path' => '/Homework/math',
                ],
            ]
        );

        $client = new Client('test_token', $mockHttpClient);

        $this->assertEquals(['name' => 'math'], $client->getMetaData('Homework/math'));
    }

    /** @test */
    public function it_can_get_a_temporary_link()
    {
        $mockHttpClient = $this->mock_http_request(
            json_encode([
                'name' => 'math',
                'link' => 'https://dl.dropboxusercontent.com/apitl/1/YXNkZmFzZGcyMzQyMzI0NjU2NDU2NDU2',
            ]),
            'https://api.dropboxapi.com/2/files/get_temporary_link',
            [
                'json' => [
                    'path' => '/Homework/math',
                ],
            ]
        );

        $client = new Client('test_token', $mockHttpClient);

        $this->assertEquals(
            'https://dl.dropboxusercontent.com/apitl/1/YXNkZmFzZGcyMzQyMzI0NjU2NDU2NDU2',
            $client->getTemporaryLink('Homework/math')
        );
    }

    /** @test */
    public function it_can_get_a_thumbnail()
    {
        $expectedResponse = $this->getMockBuilder(StreamInterface::class)
            ->getMock();

        $mockHttpClient = $this->mock_http_request(
            $expectedResponse,
            'https://content.dropboxapi.com/2/files/get_thumbnail',
            [
                'headers' => [
                    'Dropbox-API-Arg' => json_encode(
                        [
                            'path'   => '/Homework/math/answers.jpg',
                            'format' => 'jpeg',
                            'size'   => 'w64h64',
                        ]
                    ),
                ],
                'body'    => '',
            ]
        );

        $client = new Client('test_token', $mockHttpClient);

        $this->assertTrue(is_string($client->getThumbnail('Homework/math/answers.jpg')));
    }

    /** @test */
    public function it_can_list_a_folder()
    {
        $mockHttpClient = $this->mock_http_request(
            json_encode(['name' => 'math']),
            'https://api.dropboxapi.com/2/files/list_folder',
            [
                'json' => [
                    'path'      => '/Homework/math',
                    'recursive' => true,
                ],
            ]
        );

        $client = new Client('test_token', $mockHttpClient);

        $this->assertEquals(['name' => 'math'], $client->listFolder('Homework/math', true));
    }

    /** @test */
    public function it_can_continue_to_list_a_folder()
    {
        $mockHttpClient = $this->mock_http_request(
            json_encode(['name' => 'math']),
            'https://api.dropboxapi.com/2/files/list_folder/continue',
            [
                'json' => [
                    'cursor' => 'ZtkX9_EHj3x7PMkVuFIhwKYXEpwpLwyxp9vMKomUhllil9q7eWiAu',
                ],
            ]
        );

        $client = new Client('test_token', $mockHttpClient);

        $this->assertEquals(
            ['name' => 'math'],
            $client->listFolderContinue('ZtkX9_EHj3x7PMkVuFIhwKYXEpwpLwyxp9vMKomUhllil9q7eWiAu')
        );
    }

    /** @test */
    public function it_can_move_a_file()
    {
        $expectedResponse = [
            '.tag' => 'file',
            'name' => 'Prime_Numbers.txt',
        ];

        $mockHttpClient = $this->mock_http_request(
            json_encode($expectedResponse),
            'https://api.dropboxapi.com/2/files/move',
            [
                'json' => [
                    'from_path' => '/from/path/file.txt',
                    'to_path'   => '',
                ],
            ]
        );

        $client = new Client('test_token', $mockHttpClient);

        $this->assertEquals($expectedResponse, $client->move('/from/path/file.txt', ''));
    }

    /** @test */
    public function it_can_upload_a_file()
    {
        $mockHttpClient = $this->mock_http_request(
            json_encode(['name' => 'answers.txt']),
            'https://content.dropboxapi.com/2/files/upload',
            [
                'headers' => [
                    'Dropbox-API-Arg' => json_encode(
                        [
                            'path' => '/Homework/math/answers.txt',
                            'mode' => 'add',
                        ]
                    ),
                    'Content-Type'    => 'application/octet-stream',
                ],
                'body'    => 'testing text upload',
            ]
        );

        $client = new Client('test_token', $mockHttpClient);

        $this->assertEquals(
            ['.tag' => 'file', 'name' => 'answers.txt'],
            $client->upload('Homework/math/answers.txt', 'testing text upload')
        );
    }

    private function mock_http_request($expectedResponse, $expectedEndpoint, $expectedParams)
    {
        $mockResponse = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();
        $mockResponse->expects($this->once())
            ->method('getBody')
            ->willReturn($expectedResponse);

        $mockHttpClient = $this->getMockBuilder(HttpClient::class)
            ->setMethods(['post'])
            ->getMock();
        $mockHttpClient->expects($this->once())
            ->method('post')
            ->with($expectedEndpoint, $expectedParams)
            ->willReturn($mockResponse);

        return $mockHttpClient;
    }
}
