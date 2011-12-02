<?php
/**
 * Generic_Sniffs_Formatting_NoSpaceAfterCastSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   CVS: $Id: NoSpaceAfterCastSniff.php 8 2010-11-06 00:40:23Z elkuku $
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

/**
 * Ensures there is no space after cast tokens.
 *
 * Example:
 * <b class="bad">(int) $foo;</b>
 * <b class="good">(int)$foo;</b>
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   Release: 1.3.0RC1
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class Joomla_Sniffs_WhiteSpace_SpaceAfterCastSniff implements PHP_CodeSniffer_Sniff
{
	/**
	 * Returns an array of tokens this test wants to listen for.
	 *
	 * @return array
	 */
	public function register()
	{
		return PHP_CodeSniffer_Tokens::$castTokens;
	}//function

	/**
	 * Processes this test, when one of its tokens is encountered.
	 *
	 * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
	 * @param integer                  $stackPtr  The position of the current token in
	 *                                        the stack passed in $tokens.
	 *
	 * @return void
	 */
	public function process(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
	{
		$tokens = $phpcsFile->getTokens();

		if($tokens[($stackPtr + 1)]['code'] === T_WHITESPACE)
		{
			$content       = $tokens[($stackPtr + 1)]['content'];
			$contentLength = strlen($content);

			if($contentLength !== 1)
			{
				$error = 'Cast statements must be followed by a single space; expected 1 space but found %s';
				$data  = array($contentLength);
				$phpcsFile->addError($error, $stackPtr, 'IncorrectSingle', $data);
			}
		}
		else
		{
			$error = 'Cast statements must be followed by a single space; expected "%s" but found "%s"';
			$data  = array(
					$tokens[$stackPtr]['content'].' '.$tokens[($stackPtr + 1)]['content'],
					$tokens[$stackPtr]['content'].$tokens[($stackPtr + 1)]['content'],
			);
			$phpcsFile->addError($error, $stackPtr, 'Incorrect', $data);
		}

// 		if($tokens[($stackPtr + 1)]['code'] === T_WHITESPACE)
// 		{
// 			$error = 'A cast statement must not be followed by a space';
// 			$phpcsFile->addError($error, $stackPtr, 'SpaceFound');
// 		}
	}//function
}//class
