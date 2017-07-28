<?php

namespace Srmklive\Dropbox\Test;

use GuzzleHttp\Psr7\Response;
use League\Flysystem\Config;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Srmklive\Dropbox\Adapter\DropboxAdapter;
use Srmklive\Dropbox\Client\DropboxClient as Client;
use Srmklive\Dropbox\Exceptions\BadRequest;

class DropboxAdapterTest extends TestCase
{
    /** @var \Srmklive\Dropbox\Client\DropboxClient|\Prophecy\Prophecy\ObjectProphecy */
    protected $client;

    /** @var \Srmklive\Dropbox\Adapter\DropboxAdapter */
    protected $dropboxAdapter;

    public function setUp()
    {
        $this->client = $this->prophesize(Client::class);

        $this->dropboxAdapter = new DropboxAdapter($this->client->reveal(), 'prefix');
    }

    /** @test */
    public function it_can_write()
    {
        $this->client->upload(Argument::any(), Argument::any(), Argument::any())->willReturn([
            'server_modified' => '2015-05-12T15:50:38Z',
            'path_display'    => '/prefix/something',
            '.tag'            => 'file',
        ]);

        $result = $this->dropboxAdapter->write('something', 'contents', new Config());

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('file', $result['type']);
    }

    /** @test */
    public function it_can_update()
    {
        $this->client->upload(Argument::any(), Argument::any(), Argument::any())->willReturn([
            'server_modified' => '2015-05-12T15:50:38Z',
            'path_display'    => '/prefix/something',
            '.tag'            => 'file',
        ]);

        $result = $this->dropboxAdapter->update('something', 'contents', new Config());

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('file', $result['type']);
    }

    /** @test */
    public function it_can_write_a_stream()
    {
        $this->client->upload(Argument::any(), Argument::any(), Argument::any())->willReturn([
            'server_modified' => '2015-05-12T15:50:38Z',
            'path_display'    => '/prefix/something',
            '.tag'            => 'file',
        ]);

        $result = $this->dropboxAdapter->writeStream('something', tmpfile(), new Config());

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('file', $result['type']);
    }

    /** @test */
    public function it_can_upload_using_a_stream()
    {
        $this->client->upload(Argument::any(), Argument::any(), Argument::any())->willReturn([
            'server_modified' => '2015-05-12T15:50:38Z',
            'path_display'    => '/prefix/something',
            '.tag'            => 'file',
        ]);

        $result = $this->dropboxAdapter->updateStream('something', tmpfile(), new Config());

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertEquals('file', $result['type']);
    }

    /**
     * @test
     *
     * @dataProvider  metadataProvider
     */
    public function it_has_calls_to_get_meta_data($method)
    {
        $this->client = $this->prophesize(Client::class);
        $this->client->getMetaData('/one')->willReturn([
            '.tag'            => 'file',
            'server_modified' => '2015-05-12T15:50:38Z',
            'path_display'    => '/one',
        ]);

        $this->dropboxAdapter = new DropboxAdapter($this->client->reveal());

        $this->assertInternalType('array', $this->dropboxAdapter->{$method}('one'));
    }

    public function metadataProvider()
    {
        return [
            ['getMetadata'],
            ['getTimestamp'],
            ['getSize'],
            ['has'],
        ];
    }

    /** @test */
    public function it_will_not_hold_metadata_after_failing()
    {
        $this->client = $this->prophesize(Client::class);

        $this->client->getMetaData('/one')->willThrow(new BadRequest(new Response(409)));

        $this->dropboxAdapter = new DropboxAdapter($this->client->reveal());

        $this->assertFalse($this->dropboxAdapter->has('one'));
    }

    /** @test */
    public function it_can_read()
    {
        $stream = tmpfile();
        fwrite($stream, 'something');

        $this->client->download(Argument::any(), Argument::any())->willReturn($stream);

        $this->assertInternalType('array', $this->dropboxAdapter->read('something'));
    }

    /** @test */
    public function it_can_read_using_a_stream()
    {
        $stream = tmpfile();
        fwrite($stream, 'something');

        $this->client->download(Argument::any(), Argument::any())->willReturn($stream);

        $this->assertInternalType('array', $this->dropboxAdapter->readStream('something'));

        fclose($stream);
    }

    /** @test */
    public function it_can_delete_stuff()
    {
        $this->client->delete('/prefix/something')->willReturn(['.tag' => 'file']);

        $this->assertTrue($this->dropboxAdapter->delete('something'));
        $this->assertTrue($this->dropboxAdapter->deleteDir('something'));
    }

    /** @test */
    public function it_can_create_a_directory()
    {
        $this->client->createFolder('/prefix/fail/please')->willThrow(new BadRequest(new Response(409)));
        $this->client->createFolder('/prefix/pass/please')->willReturn([
            '.tag'           => 'folder',
            'path_display'   => '/prefix/pass/please',
        ]);

        $this->assertFalse($this->dropboxAdapter->createDir('fail/please', new Config()));

        $expected = ['path' => 'pass/please', 'type' => 'dir'];
        $this->assertEquals($expected, $this->dropboxAdapter->createDir('pass/please', new Config()));
    }

    /** @test */
    public function it_can_list_contents()
    {
        $this->client->listFolder(Argument::type('string'), Argument::any())->willReturn(
            ['entries' => [
                ['.tag' => 'folder', 'path_display' => 'dirname'],
                ['.tag' => 'file', 'path_display' => 'dirname/file'],
            ]]
        );

        $result = $this->dropboxAdapter->listContents('', true);

        $this->assertCount(2, $result);
    }

    /** @test */
    public function it_can_rename_stuff()
    {
        $this->client->move(Argument::type('string'), Argument::type('string'))->willReturn(['.tag' => 'file', 'path' => 'something']);

        $this->assertTrue($this->dropboxAdapter->rename('something', 'something'));
    }

    /** @test */
    public function it_will_return_false_when_a_rename_has_failed()
    {
        $this->client->move('/prefix/something', '/prefix/something')->willThrow(new BadRequest(new Response(409)));

        $this->assertFalse($this->dropboxAdapter->rename('something', 'something'));
    }

    /** @test */
    public function it_can_copy_a_file()
    {
        $this->client->copy(Argument::type('string'), Argument::type('string'))->willReturn(['.tag' => 'file', 'path' => 'something']);

        $this->assertTrue($this->dropboxAdapter->copy('something', 'something'));
    }

    /** @test */
    public function it_will_return_false_when_the_copy_process_has_failed()
    {
        $this->client->copy(Argument::any(), Argument::any())->willThrow(new BadRequest(new Response(409)));

        $this->assertFalse($this->dropboxAdapter->copy('something', 'something'));
    }

    /** @test */
    public function it_can_get_a_client()
    {
        $this->assertInstanceOf(Client::class, $this->dropboxAdapter->getClient());
    }
}
