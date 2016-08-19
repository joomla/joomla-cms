<?php
/**
 * Verifies that control statements conform to their coding standards.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   CVS: $Id: ControlSignatureSniff.php 244676 2007-10-23 06:05:14Z squiz $
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

if (class_exists('PHP_CodeSniffer_Standards_AbstractPatternSniff', true) === false) {
	throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_AbstractPatternSniff not found');
}

/**
 * Verifies that control statements conform to their coding standards.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   Release: 1.3.0RC2
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class Joomla_Sniffs_ControlStructures_ControlSignatureSniff extends PHP_CodeSniffer_Standards_AbstractPatternSniff
{

	/**
	 * Constructs a Joomla_Sniffs_ControlStructures_ControlSignatureSniff.
	 */
	public function __construct()
	{
		parent::__construct(true);

	}//end __construct()

	/**
	 * Returns the patterns that this test wishes to verify.
	 *
	 * @return array(string)
	 */
	protected function getPatterns()
	{
		return array(
			'if (...)EOL...{...}EOL...elseEOL',
			'if (...)EOL...{...}EOL...elseif (...)EOL',
			'if (...)EOL',

			'tryEOL...{EOL...}EOL',
			'catch (...)EOL...{EOL',

			'doEOL...{...}EOL',
			'while (...)EOL...{EOL',

			'for (...)EOL...{EOL',
			'foreach (...)EOL...{EOL',

			'switch (...)EOL...{EOL',
		);

	}//end getPatterns()

	/**
	 * Process a pattern.
	 *
	 * Returns if we are inside a "tmpl" folder - workaround for the Joomla! CMS :(
	 *
	 * @param array $patternInfo Information about the pattern used for checking, which includes are
	 *               parsed token representation of the pattern.
	 * @param PHP_CodeSniffer_File $phpcsFile The PHP_CodeSniffer file where the token occurred.
	 * @param integer $stackPtr The postion in the tokens stack where the listening token type was found.
	 *
	 * @return return_type
	 */
	protected function processPattern($patternInfo, PHP_CodeSniffer_File $phpcsFile
	, $stackPtr)
	{
		if (0)
		{
			/*
			 * @todo disabled - This is a special sniff for the Joomla! CMS to exclude
			* the tmpl folder which may contain constructs in colon notation
			*/

			$parts = explode(DIRECTORY_SEPARATOR, $phpcsFile->getFileName());

			if ('tmpl' == $parts[count($parts) - 2])
			{
				return false;
			}
		}

		return parent::processPattern($patternInfo, $phpcsFile, $stackPtr);
	}//function

}//end class

?>
