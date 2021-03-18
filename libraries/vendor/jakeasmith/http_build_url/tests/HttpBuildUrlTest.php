<?php

class HttpBuildUrlTest extends \PHPUnit_Framework_TestCase
{
	private $full_url = "http://user:pass@www.example.com:8080/pub/index.php?a=b#files";

	/**
	 * Test example one.
	 *
	 * @see http://us2.php.net/manual/en/function.http-build-url.php
	 */
	public function testExampleOne()
	{
		$expected = 'ftp://ftp.example.com/pub/files/current/?a=c';
		$actual   = http_build_url(
			"http://user@www.example.com/pub/index.php?a=b#files",
			array(
				"scheme" => "ftp",
				"host"   => "ftp.example.com",
				"path"   => "files/current/",
				"query"  => "a=c"
			),
			HTTP_URL_STRIP_AUTH | HTTP_URL_JOIN_PATH | HTTP_URL_JOIN_QUERY | HTTP_URL_STRIP_FRAGMENT
		);

		$this->assertSame($expected, $actual);
	}

	public function trailingSlashProvider()
	{
		return array(
			array(
				'http://example.com',
				array(
					'scheme' => 'http',
					'host' => 'example.com'
				)
			),
			array(
				'http://example.com',
				array(
					'scheme' => 'http',
					'host' => 'example.com',
					'path' => ''
				)
			),
			array(
				'http://example.com/',
				array(
					'scheme' => 'http',
					'host' => 'example.com',
					'path' => '/'
				)
			),
			array(
				'http://example.com/yes',
				array(
					'scheme' => 'http',
					'host' => 'example.com',
					'path' => 'yes'
				)
			),
			array(
				'http://example.com/yes',
				array(
					'scheme' => 'http',
					'host' => 'example.com',
					'path' => '/yes'
				)
			),
			array(
				'http://example.com:81?a=b',
				array(
					'scheme' => 'http',
					'host' => 'example.com',
					'query' => 'a=b',
					'port' => 81
				)
			)
		);
	}

	/**
	 * @dataProvider trailingSlashProvider
	 */
	public function testTrailingSlash($expected, $config)
	{
		$this->assertEquals($expected, http_build_url($config));
	}

	public function testUrlQueryArrayIsIgnored()
	{
		$expected = 'http://user:pass@www.example.com:8080/pub/index.php#files';
		$url      = parse_url($this->full_url);
		parse_str($url['query'], $url['query']);
		$actual = http_build_url($url);

		$this->assertSame($expected, $actual);
	}

	public function testPartsQueryArrayIsIgnored()
	{
		$expected = $this->full_url;
		$actual   = http_build_url($this->full_url, array('query' => array('foo' => 'bar')));

		$this->assertSame($expected, $actual);
	}

	public function testAcceptStrings()
	{
		$expected = 'http://user:pass@foobar.com:8080/pub/index.php?a=b#files';
		$actual   = http_build_url($this->full_url, 'http://foobar.com:8080');

		$this->assertSame($expected, $actual);
	}

	public function testAcceptArrays()
	{
		$expected = 'http://user:pass@foobar.com:8080/pub/index.php?a=b#files';
		$actual   = http_build_url(parse_url($this->full_url), parse_url('http://foobar.com:8080'));

		$this->assertSame($expected, $actual);
	}

	public function testDefaults()
	{
		$expected = $this->full_url;
		$actual   = http_build_url($this->full_url);

		$this->assertSame($expected, $actual);
	}

	public function testNewUrl()
	{
		$expected = parse_url($this->full_url);
		http_build_url($this->full_url, null, null, $actual);

		$this->assertEquals($expected, $actual);
	}

	/**
	 * @dataProvider queryProvider
	 */
	public function testJoinQuery($query, $expected)
	{
		$actual = http_build_url($this->full_url, array('query' => $query), HTTP_URL_JOIN_QUERY);

		$this->assertSame($expected, $actual);
	}

	/**
	 * @dataProvider pathProvider
	 */
	public function testJoinPath($path, $expected)
	{
		$actual = http_build_url($this->full_url, array('path' => $path), HTTP_URL_JOIN_PATH);

		$this->assertSame($expected, $actual);
	}

	public function testJoinPathTwo()
	{
		$expected = "http://site.testing.com/preview/testing/09-2013/p04/image/15.jpg";
		$actual = http_build_url(
			"http://site.testing.com/preview/testing/09-2013/p04/?code=asdfghjkl",
			array('path' => 'image/15.jpg'),
			HTTP_URL_JOIN_PATH | HTTP_URL_STRIP_FRAGMENT | HTTP_URL_STRIP_QUERY
		);

		$this->assertSame($expected, $actual);
	}

	/**
	 * @dataProvider bitmaskProvider
	 */
	public function testBitmasks($constant, $expected)
	{
		$actual = http_build_url($this->full_url, array(), constant($constant));

		$this->assertSame($expected, $actual);
	}

	public function pathProvider()
	{
		return array(
			array('/donuts/brownies', 'http://user:pass@www.example.com:8080/donuts/brownies?a=b#files'),
			array('chicken/wings', 'http://user:pass@www.example.com:8080/pub/chicken/wings?a=b#files'),
			array('sausage/bacon/', 'http://user:pass@www.example.com:8080/pub/sausage/bacon/?a=b#files')
		);
	}

	public function queryProvider()
	{
		return array(
			array('a=c', 'http://user:pass@www.example.com:8080/pub/index.php?a=c#files'),
			array('d=a', 'http://user:pass@www.example.com:8080/pub/index.php?a=b&d=a#files')
		);
	}

	public function bitmaskProvider()
	{
		return array(
			array('HTTP_URL_REPLACE', 'http://user:pass@www.example.com:8080/pub/index.php?a=b#files'),
			array('HTTP_URL_JOIN_PATH', 'http://user:pass@www.example.com:8080/pub/index.php?a=b#files'),
			array('HTTP_URL_JOIN_QUERY', 'http://user:pass@www.example.com:8080/pub/index.php?a=b#files'),
			array('HTTP_URL_STRIP_USER', 'http://www.example.com:8080/pub/index.php?a=b#files'),
			array('HTTP_URL_STRIP_PASS', 'http://user@www.example.com:8080/pub/index.php?a=b#files'),
			array('HTTP_URL_STRIP_AUTH', 'http://www.example.com:8080/pub/index.php?a=b#files'),
			array('HTTP_URL_STRIP_PORT', 'http://user:pass@www.example.com/pub/index.php?a=b#files'),
			array('HTTP_URL_STRIP_PATH', 'http://user:pass@www.example.com:8080?a=b#files'),
			array('HTTP_URL_STRIP_QUERY', 'http://user:pass@www.example.com:8080/pub/index.php#files'),
			array('HTTP_URL_STRIP_FRAGMENT', 'http://user:pass@www.example.com:8080/pub/index.php?a=b'),
			array('HTTP_URL_STRIP_ALL', 'http://www.example.com'),
		);
	}
}
