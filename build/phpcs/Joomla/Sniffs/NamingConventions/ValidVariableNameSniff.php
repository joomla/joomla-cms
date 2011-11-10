<?php
/**
 * Joomla_Sniffs_NamingConventions_ValidVariableNameSniff.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   CVS: $Id: ValidVariableNameSniff.php 261129 2008-06-13 04:10:43Z squiz $
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */

if (class_exists('PHP_CodeSniffer_Standards_AbstractVariableSniff', true) === false) {
    $error = 'Class PHP_CodeSniffer_Standards_AbstractVariableSniff not found';
    throw new PHP_CodeSniffer_Exception($error);
}

/**
 * Joomla_Sniffs_NamingConventions_ValidVariableNameSniff.
 *
 * Checks the naming of member variables.
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @copyright 2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   http://matrix.squiz.net/developer/tools/php_cs/licence BSD Licence
 * @version   Release: 1.3.0RC2
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class Joomla_Sniffs_NamingConventions_ValidVariableNameSniff extends PHP_CodeSniffer_Standards_AbstractVariableSniff
{


    /**
     * Processes class member variables.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file being scanned.
     * @param int                  $stackPtr  The position of the current token
     *                                        in the stack passed in $tokens.
     *
     * @return void
     */
    protected function processMemberVar(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $memberProps = $phpcsFile->getMemberProperties($stackPtr);
        if (empty($memberProps) === true) {
            return;
        }

        $memberName     = ltrim($tokens[$stackPtr]['content'], '$');
        $isPublic       = ($memberProps['scope'] === 'private') ? false : true;
        $scope          = $memberProps['scope'];
        $scopeSpecified = $memberProps['scope_specified'];

		// Detect if it is marked deprecated
		$find = array(
				 T_COMMENT,
				 T_DOC_COMMENT,
				 T_CLASS,
				 T_FUNCTION,
				 T_OPEN_TAG,
				);
		$tokens = $phpcsFile->getTokens();
		$commentEnd = $phpcsFile->findPrevious($find, ($stackPtr - 1));
		if ($commentEnd !== false && $tokens[$commentEnd]['code'] === T_DOC_COMMENT) {
			$commentStart = $phpcsFile->findPrevious(T_DOC_COMMENT, ($commentEnd - 1), null, true) + 1;
			$comment = $phpcsFile->getTokensAsString($commentStart, ($commentEnd - $commentStart + 1));

			try {
				$this->commentParser = new PHP_CodeSniffer_CommentParser_FunctionCommentParser($comment, $phpcsFile);
				$this->commentParser->parse();
			} catch (PHP_CodeSniffer_CommentParser_ParserException $e) {
				$line = ($e->getLineWithinComment() + $commentStart);
				$phpcsFile->addError($e->getMessage(), $line, 'FailedParse');
				return;
			}

			$deprecated = $this->commentParser->getDeprecated();
			$isDeprecated = !is_null($deprecated);
		}
		else {
			$isDeprecated = false;
		}

        // If it's a private member, it must have an underscore on the front.
        if ($isPublic === false && $memberName{0} !== '_') {
            $error = 'Private member variable "%s" must be prefixed with an underscore';
            $data  = array($memberName);
            $phpcsFile->addError($error, $stackPtr, 'PrivateNoUnderscore', $data);
            return;
        }

        // If it's not a private member, it must not have an underscore on the front.
        if ($isDeprecated === false && $isPublic === true && $scopeSpecified === true && $memberName{0} === '_') {
            $error = '%s member variable "%s" must not be prefixed with an underscore';
            $data  = array(
                      ucfirst($scope),
                      $memberName,
                     );
            // AJE Changed from error to warning.
            $phpcsFile->addWarning($error, $stackPtr, 'PublicUnderscore', $data);
            return;
        }

    }//end processMemberVar()


    /**
     * Processes normal variables.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param int                  $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariable(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        // We don't care about normal variables.
        return;

    }//end processVariable()


    /**
     * Processes variables in double quoted strings.
     *
     * @param PHP_CodeSniffer_File $phpcsFile The file where this token was found.
     * @param int                  $stackPtr  The position where the token was found.
     *
     * @return void
     */
    protected function processVariableInString(PHP_CodeSniffer_File $phpcsFile, $stackPtr)
    {
        // We don't care about normal variables.
        return;

    }//end processVariableInString()


}//end class

?>
