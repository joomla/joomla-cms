<?php
/**
 * Verifies that class members have scope modifiers.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   CVS: $Id: MemberVarScopeSniff.php 8 2010-11-06 00:40:23Z elkuku $
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

if(class_exists('PHP_CodeSniffer_Standards_AbstractVariableSniff', true) === false)
{
    throw new PHP_CodeSniffer_Exception('Class PHP_CodeSniffer_Standards_AbstractVariableSniff not found');
}

/**
 * Verifies that class members have scope modifiers.
 *
 * Example:
 * class Foo
 * {
 *     <b class="good">private $foo</b>
 *     <b class="bad">var $foo</b>
 * }
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
class Joomla_Sniffs_Classes_MemberVarScopeSniff extends PHP_CodeSniffer_Standards_AbstractVariableSniff
{
    /**
     * Processes the function tokens within the class.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param integer                  $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processMemberVar(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $modifier = $phpcsFile->findPrevious(PHP_CodeSniffer_Tokens::$scopeModifiers, $stackPtr);

        if(($modifier === false) || ($tokens[$modifier]['line'] !== $tokens[$stackPtr]['line']))
        {
            $error = sprintf('Scope modifier not specified for member variable "%s"'
            , $tokens[$stackPtr]['content']);

            $phpcsFile->addWarning($error, $stackPtr, 'Missing');
        }
    }//function

    /**
     * Processes normal variables.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param integer                  $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariable(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        // We don't care about normal variables.
        return;
    }//function

    /**
     * Processes variables in double quoted strings.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param integer                  $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariableInString(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        // We don't care about normal variables.
        return;
    }//function
}//class
