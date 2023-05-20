<?php

class MarkdownExtraTest extends \PHPUnit\Framework\TestCase
{
	public function testSetupOfPredefinedAttributes()
	{
		$obj = new \Michelf\MarkdownExtra();

		// Allows custom expansions of arreviations to their full version with the abbr tag
		$obj->predef_abbr = array(
			'foo' => 'foobar-test',
		);
		$result = $obj->transform('**Hello world, foo**');

		$this->assertSame(
			'<p><strong>Hello world, <abbr title="foobar-test">foo</abbr></strong></p>',
			trim($result)
		);
	}

	public function testSetupOfMultiplePredefinedAttributes()
	{
		$obj = new \Michelf\MarkdownExtra();

		// Allows custom expansions of arreviations to their full version with the abbr tag
		$obj->predef_abbr = array(
			'foo' => 'foobar-test',
			'ISP' => 'Internet Service Provider',
		);
		$result = $obj->transform('**I get internet from an ISP. foo.**');

		$this->assertSame(
			'<p><strong>I get internet from an <abbr title="Internet Service Provider">ISP' .
			'</abbr>. <abbr title="foobar-test">foo</abbr>.</strong></p>',
			trim($result)
		);
	}

	public function testTransformWithNoMarkup()
	{
		$obj = new \Michelf\MarkdownExtra();
		$obj->no_markup = true;

		$result = $obj->transform('This is a <strong class="custom">no markup</strong> test.');

		$this->assertSame(
			'<p>This is a &lt;strong class="custom">no markup&lt;/strong> test.</p>',
			trim($result)
		);
	}
}
