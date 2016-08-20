<?php
/**
 * @package     Joomla.Platform
 * @subpackage  String
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

/**
 * A simple multi-byte aware content parser.
 *
 * This is a simple recursive-descent parser and the token syntax was specifically chosen to allow
 * straightforward prediction parsing that does not require the construction of a parse tree.
 *
 * Code syntax:
 *   $parser = (new JStringParser())
 *       ->registerToken('a', function(JStringTokenInterface $token, $content) { return 'replacement'; }, true)
 *       ->registerToken('b', function(JStringTokenInterface $token, $content) { return 'replacement'; }, false)
 *       ;
 *   $output = $parser->translate($content);
 *
 * Token syntax:
 *   Simple tags: {a [params]}
 *   Block tags: {b [params]} something {/b}
 *
 * Block tags may encompass content containing simple tags:
 *   {b} something {a} something else {/b}
 * Block tags may also be nested:
 *   {c} something {b} something else {/b} something more {/c}
 *
 * @since __DEPLOY_VERSION__
 */
class JStringParser
{
	/**
	 * Array of registered token definitions.
	 */
	private $tokens = array();

	/**
	 * The content being parsed.
	 */
	private $content = '';

	/**
	 * Length of content string being parsed.
	 */
	private $contentLength = 0;

	/**
	 * Lookahead token.
	 */
	private $lookahead = null;

	/**
	 * Position of the next token in the content.
	 */
	private $position = 0;

	/**
	 * Start of token string.
	 */
	private $startOfToken = '{';

	/**
	 * End of token string.
	 */
	private $endOfToken = '}';

	/**
	 * Parameter separator string.
	 */
	private $paramSeparator = ',';

	/**
	 * Return the next token from the input.
	 *
	 * If there are characters before the next "real" token, return them in a TokenString object.
	 * If there are no further "real" tokens, return the remaining characters in a TokenString object.
	 *
	 * @return  TokenInterface | null
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private function getNextToken()
	{
		$localPosition = $this->position;
		$matches = array();
		$pattern = '/' . $this->startOfToken . '(.+)' . $this->endOfToken . '/U';

		do
		{
			// Look for a possible token.
			preg_match($pattern, JString::substr($this->content, $localPosition), $matches, PREG_OFFSET_CAPTURE);

			// No more tokens, so stop looking.
			if (empty($matches))
			{
				break;
			}

			// Determine the position of the open brace.  Note that the offsets returned by preg_replace
			// are not multi-byte aware, so we have to do a bit of work to get the correct figure.
			$start = $localPosition + JString::strlen(substr($this->content, 0, $matches[0][1]));

			// Determine the position of the close brace.
			$end   = $start + JString::strlen($matches[0][0]) - 1;

			// Parse the token itself into a name and an optional string of parameters.
			$parts = explode(' ', $matches[1][0], 2);
			$tokenName = JString::strtolower($parts[0]);
			$tokenParams = isset($parts[1]) ? $parts[1] : '';

			$endToken = false;

			// Is this an end token?
			if (substr($tokenName, 0, 1) == '/')
			{
				$endToken = true;
				$tokenName = JString::substr($tokenName, 1);
			}

			// Is the token name registered?  If not, continue searching for tokens.
			if (!isset($this->tokens[$tokenName]))
			{
				$localPosition = $end + 1;

				continue;
			}

			// If there are characters prior to the token, we return them as a string object first.
			if ($start > $this->position)
			{
				$lexeme = new JStringTokenString(JString::substr($this->content, $this->position, $start - $this->position));
				$this->position = $start;

				return $lexeme;
			}

			// Otherwise, we're going to return the token.
			$tokenDefn = $this->tokens[$tokenName];
			$this->position = $end + 1;

			if ($endToken)
			{
				return new JStringTokenEnd($tokenDefn);
			}

			return new JStringTokenBegin($tokenDefn, $tokenParams == '' ? array() : explode($this->paramSeparator, $tokenParams));
		}
		while (true);

		// No more tokens and no more characters so return null.
		if ($this->position >= $this->contentLength)
		{
			return;
		}

		// No more tokens so return remaining characters as a string object.
		$lexeme = new JStringTokenString(JString::substr($this->content, $this->position, $this->contentLength - $this->position));
		$this->position = $this->contentLength;

		return $lexeme;
	}

	/**
	 * Match the next token from the input.
	 *
	 * If the next token matches what we expect, then return the output from it.
	 * Otherwise we have an error, but we silently return a null.
	 *
	 * @param   string  $expectedTokenType  Type of token expected.
	 *
	 * @return  string | null
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private function match($expectedTokenType)
	{
		if ($this->lookahead instanceof $expectedTokenType)
		{
			$output = $this->lookahead->getValue();
			$this->lookahead = $this->getNextToken();

			return $output;
		}
	}

	/**
	 * Handles the list production rule:
	 *    list ::= string | string token list
	 * Note that the string element may be empty.
	 *
	 * @return  string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private function parseList()
	{
		$output = '';

		// Expecting a string, which could be empty.
		if ($this->lookahead instanceof JStringTokenString)
		{
			$output .= $this->match('JStringTokenString');
		}

		// Expecting a begin (simple or block) token.
		if ($this->lookahead instanceof JStringTokenBegin)
		{
			$output .= $this->parseToken();
		}

		// If the next token is a string then we have another list.
		if ($this->lookahead instanceof JStringTokenString)
		{
			$output .= $this->parseList();
		}

		return $output;
	}

	/**
	 * Handles the token production rule:
	 *    token ::= simple | begin list end
	 *
	 * @return  string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	private function parseToken()
	{
		// If the token is simple, we're done.
		if ($this->lookahead->isSimple())
		{
			return $this->match('JStringTokenBegin');
		}

		// Save the begin block token for later.
		$tokenBegin = $this->lookahead;

		// Match the begin block token.
		$this->match('JStringTokenBegin');

		// Parse the string between the begin and end tokens.
		$output = $this->parseList();

		// Expecting an end block token.
		if ($this->lookahead instanceof JStringTokenEnd)
		{
			// Process the string through the begin block token.
			$output = $tokenBegin->getValue($output);

			// Match the end block token.
			$this->match('JStringTokenEnd');
		}

		return $output;
	}

	/**
	 * Register the definition of a token.
	 *
	 * @param   string    $name      A token name to look for.
	 * @param   callable  $callback  A callable that will return the replacement string.
	 * @param   boolean   $simple    True for a simple token; false for a block token.
	 *
	 * @return  This object for method chaining.
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function registerToken($name, callable $callback, $simple = true)
	{
		$this->tokens[JString::strtolower($name)] = new JStringTokenDefinition($name, $callback, $simple);

		return $this;
	}

	/**
	 * Syntax-directed translation of a string.
	 *
	 * Production rules:
	 *    list       ::= string | string token list
	 *    token      ::= simple | beginBlock list endBlock
	 *    simple     ::= startOfToken name endOfToken | startOfToken name space params endOfToken
	 *    beginBlock ::= startOfToken name endOfToken | startOfToken name space params endOfToken
	 *    endBlock   ::= startOfToken / name endOfToken
	 *    params     ::= param | param , params
	 *    string     ::= any sequence of zero or more characters not including startOfToken
	 *    name       ::= any sequence of at least one non-space character
	 *    param      ::= any sequence of zero or more characters except , and endOfToken
	 *
	 * Note: Format of an individual param is not defined.
	 *
	 * @param   string  $content  Content to be parsed and translated.
	 * @param   array   $options  Optional array of options.
	 *
	 * @return  string
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function translate($content, $options = array())
	{
		$this->content		  = $content;
		$this->contentLength  = JString::strlen($content);
		$this->position		  = 0;
		$this->lookahead	  = null;
		$this->startOfToken	  = isset($options['startOfToken'])   ? $options['startOfToken']   : '{';
		$this->endOfToken	  = isset($options['endOfToken'])     ? $options['endOfToken']     : '}';
		$this->paramSeparator = isset($options['paramSeparator']) ? $options['paramSeparator'] : ',';

		$output = '';

		// Start by looking for the first token.
		$this->lookahead = $this->getNextToken();

		// Loop until we've processed all the tokens.
		do
		{
			$output .= $this->parseList();
		}
		while ($this->position < $this->contentLength);

		return $output;
	}
}

