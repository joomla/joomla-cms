<?php

use PHPUnit\Framework\TestCase;
use Michelf\Markdown;
use Michelf\MarkdownExtra;

class PhpMarkdownTest extends TestCase
{
	/**
	 * Returns all php-markdown.mdtest tests
	 * @return array
	 */
	public function dataProviderForPhpMarkdown() {
		$dir = TEST_RESOURCES_ROOT . '/php-markdown.mdtest';
		return MarkdownTestHelper::getInputOutputPaths($dir);
	}

	/**
	 * Runs php-markdown.mdtest against Markdown::defaultTransform
	 *
	 * @dataProvider dataProviderForPhpMarkdown
	 *
	 * @param string $inputPath Input markdown path
	 * @param string $htmlPath File path of expected transformed output (X)HTML
	 *
	 * @param bool $xhtml True if XHTML. Otherwise, HTML is assumed.
	 *
	 * @return void
	 */
	public function testTransformingOfPhpMarkdown($inputPath, $htmlPath, $xhtml = false) {
		$inputMarkdown = file_get_contents($inputPath);
		$expectedHtml = file_get_contents($htmlPath);
		$result = Markdown::defaultTransform($inputMarkdown);

		MarkdownTestHelper::assertSameNormalized(
			$expectedHtml,
			$result,
			"Markdown in $inputPath converts exactly to expected $htmlPath",
			$xhtml
		);
	}

	/**
	 * Returns all php-markdown.mdtest tests EXCEPT Emphasis test.
	 * @return array
	 */
	public function dataProviderForPhpMarkdownExceptEmphasis()
	{
		$dir = TEST_RESOURCES_ROOT . '/php-markdown.mdtest';
		$allTests = MarkdownTestHelper::getInputOutputPaths($dir);

		foreach ($allTests as $index => $test) {
			// Because MarkdownExtra treats underscore emphasis differently, this one test has to be excluded
			if (preg_match('~/Emphasis\.text$~', $test[0])) {
				unset($allTests[$index]);
			}
		}

		return array_values($allTests);
	}

	/**
	 * Runs php-markdown.mdtest against MarkdownExtra::defaultTransform
	 *
	 * @dataProvider dataProviderForPhpMarkdownExceptEmphasis
	 *
	 * @param $inputPath
	 * @param $htmlPath
	 * @param bool $xhtml
	 */
	public function testPhpMarkdownMdTestWithMarkdownExtra($inputPath, $htmlPath, $xhtml = false)
	{
		$inputMarkdown = file_get_contents($inputPath);
		$expectedHtml = file_get_contents($htmlPath);

		$result = MarkdownExtra::defaultTransform($inputMarkdown);

		MarkdownTestHelper::assertSameNormalized(
			$expectedHtml,
			$result,
			"Markdown in $inputPath converts exactly to expected $htmlPath",
			$xhtml
		);
	}

	/**
	 * @return array
	 */
	public function dataProviderForMarkdownExtra() {
		$dir = TEST_RESOURCES_ROOT . '/php-markdown-extra.mdtest';
		return MarkdownTestHelper::getInputOutputPaths($dir);
	}

	/**
	 * @dataProvider dataProviderForMarkdownExtra
	 *
	 * @param string $inputPath Input markdown path
	 * @param string $htmlPath File path of expected transformed output (X)HTML
	 *
	 * @param bool $xhtml True if XHTML. Otherwise, HTML is assumed.
	 *
	 * @return void
	 */
	public function testTransformingOfMarkdownExtra($inputPath, $htmlPath, $xhtml = false) {
		$inputMarkdown = file_get_contents($inputPath);
		$expectedHtml = file_get_contents($htmlPath);
		$result = MarkdownExtra::defaultTransform($inputMarkdown);

		MarkdownTestHelper::assertSameNormalized(
			$expectedHtml,
			$result,
			"Markdown in $inputPath converts exactly to expected $htmlPath",
			$xhtml
		);
	}

	/**
	 * @return array
	 */
	public function dataProviderForRegularMarkdown()
	{
		$dir = TEST_RESOURCES_ROOT . '/markdown.mdtest';
		return MarkdownTestHelper::getInputOutputPaths($dir);
	}

	/**
	 * @dataProvider dataProviderForRegularMarkdown
	 *
	 * @param string $inputPath Input markdown path
	 * @param string $htmlPath File path of expected transformed output (X)HTML
	 *
	 * @param bool $xhtml True if XHTML. Otherwise, HTML is assumed.
	 *
	 * @return void
	 */
	public function testTransformingOfRegularMarkdown($inputPath, $htmlPath, $xhtml = false)
	{
		$inputMarkdown = file_get_contents($inputPath);
		$expectedHtml = file_get_contents($htmlPath);
		$result = Markdown::defaultTransform($inputMarkdown);

		MarkdownTestHelper::assertSameNormalized(
			$expectedHtml,
			$result,
			"Markdown in $inputPath converts exactly to expected $htmlPath",
			$xhtml
		);
	}

	/**
	 * Runs markdown.mdtest against MarkdownExtra::defaultTransform
	 *
	 * @dataProvider dataProviderForRegularMarkdown
	 *
	 * @param $inputPath
	 * @param $htmlPath
	 * @param bool $xhtml
	 */
	public function testMarkdownMdTestWithMarkdownExtra($inputPath, $htmlPath, $xhtml = false)
	{
		$inputMarkdown = file_get_contents($inputPath);
		$expectedHtml = file_get_contents($htmlPath);

		$result = MarkdownExtra::defaultTransform($inputMarkdown);

		MarkdownTestHelper::assertSameNormalized(
			$expectedHtml,
			$result,
			"Markdown in $inputPath converts exactly to expected $htmlPath",
			$xhtml
		);
	}
}
