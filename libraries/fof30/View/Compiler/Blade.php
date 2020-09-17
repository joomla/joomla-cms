<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\View\Compiler;

defined('_JEXEC') || die;

use FOF30\Container\Container;

class Blade implements CompilerInterface
{
	/**
	 * Are the results of this engine cacheable?
	 *
	 * @var bool
	 */
	protected $isCacheable = true;

	/**
	 * The extension of the template files supported by this compiler
	 *
	 * @var    string
	 * @since  3.3.1
	 */
	protected $fileExtension = 'blade.php';

	/**
	 * All of the registered compiler extensions.
	 *
	 * @var array
	 */
	protected $extensions = [];

	/**
	 * The file currently being compiled.
	 *
	 * @var string
	 */
	protected $path;

	/**
	 * All of the available compiler functions. Each one is called against every HTML block in the template.
	 *
	 * @var array
	 */
	protected $compilers = [
		'Extensions',
		'Statements',
		'Comments',
		'Echos',
	];

	/**
	 * Array of opening and closing tags for escaped echos.
	 *
	 * @var array
	 */
	protected $contentTags = ['{{', '}}'];

	/**
	 * Array of opening and closing tags for escaped echos.
	 *
	 * @var array
	 */
	protected $escapedTags = ['{{{', '}}}'];

	/**
	 * Array of footer lines to be added to template.
	 *
	 * @var array
	 */
	protected $footer = [];

	/**
	 * Counter to keep track of nested forelse statements.
	 *
	 * @var int
	 */
	protected $forelseCounter = 0;

	/**
	 * The FOF container we are attached to
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * Should I use the PHP Tokenizer extension to compile Blade templates? Default is true and is preferable. We expect
	 * this to be false only on bad quality hosts. It can be overridden with Reflection for testing purposes.
	 *
	 * @var bool
	 */
	protected $usingTokenizer = false;

	public function __construct(Container $container)
	{
		$this->container      = $container;
		$this->usingTokenizer = false;

		if (function_exists('token_get_all') && defined('T_INLINE_HTML'))
		{
			$this->usingTokenizer = true;
		}
	}

	/**
	 * Report if the PHP Tokenizer extension is being used
	 *
	 * @return  bool
	 */
	public function isUsingTokenizer()
	{
		return $this->usingTokenizer;
	}

	/**
	 * Are the results of this compiler engine cacheable? If the engine makes use of the forcedParams it must return
	 * false.
	 *
	 * @return  mixed
	 */
	public function isCacheable()
	{
		if (defined('JDEBUG') && JDEBUG)
		{
			return false;
		}

		return $this->isCacheable;
	}

	/**
	 * Compile a view template into PHP and HTML
	 *
	 * @param   string  $path         The absolute filesystem path of the view template
	 * @param   array   $forceParams  Any parameters to force (only for engines returning raw HTML)
	 *
	 * @return mixed
	 */
	public function compile($path, array $forceParams = [])
	{
		$this->footer = [];

		$fileData = @file_get_contents($path);

		if ($path)
		{
			$this->setPath($path);
		}

		return $this->compileString($fileData);
	}


	/**
	 * Get the path currently being compiled.
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}

	/**
	 * Set the path currently being compiled.
	 *
	 * @param   string  $path
	 *
	 * @return void
	 */
	public function setPath($path)
	{
		$this->path = $path;
	}

	/**
	 * Compile the given Blade template contents.
	 *
	 * @param   string  $value
	 *
	 * @return string
	 */
	public function compileString($value)
	{
		$result = '';

		if ($this->usingTokenizer)
		{
			// Here we will loop through all of the tokens returned by the Zend lexer and
			// parse each one into the corresponding valid PHP. We will then have this
			// template as the correctly rendered PHP that can be rendered natively.
			foreach (token_get_all($value) as $token)
			{
				$result .= is_array($token) ? $this->parseToken($token) : $token;
			}
		}
		else
		{
			foreach ($this->compilers as $type)
			{
				$value = $this->{"compile{$type}"}($value);
			}

			$result .= $value;
		}

		// If there are any footer lines that need to get added to a template we will
		// add them here at the end of the template. This gets used mainly for the
		// template inheritance via the extends keyword that should be appended.
		if (count($this->footer) > 0)
		{
			$result = ltrim($result, PHP_EOL)
				. PHP_EOL . implode(PHP_EOL, array_reverse($this->footer));
		}

		return $result;
	}

	/**
	 * Compile the default values for the echo statement.
	 *
	 * @param   string  $value
	 *
	 * @return string
	 */
	public function compileEchoDefaults($value)
	{
		return preg_replace('/^(?=\$)(.+?)(?:\s+or\s+)(.+?)$/s', 'isset($1) ? $1 : $2', $value);
	}

	/**
	 * Register a custom Blade compiler. Using a tag or not changes the behavior of this method when you try to redefine
	 * an existing custom Blade compiler.
	 *
	 * If you use a tag which already exists the old compiler is replaced by the new one you are defining.
	 *
	 * If you do not use a tag, the new compiler you are defining will always be added to the bottom of the list. That
	 * is to say, if another compiler would be matching the same function name (e.g. `@foobar`) it would get compiled by
	 * the first compiler, the one already set, not the one you are defining now. You are suggested to always use a tag
	 * for this reason.
	 *
	 * Finally, note that custom Blade compilers are compiled last. This means that you cannot override a core Blade
	 * compiler with a custom one. If you need to do that you need to create a new Compiler class -- probably extending
	 * this one -- and override the protected compiler methods. Remember to also create a custom Container and override
	 * its 'blade' key with a callable which returns an object of your custom class.
	 *
	 * @param   callable  $compiler
	 * @param   string    $tag  Optional. Give the callable a tag you can look for with hasExtensionByName
	 *
	 * @return void
	 */
	public function extend($compiler, $tag = null)
	{
		if (!is_null($tag))
		{
			$this->extensions[$tag] = $compiler;

			return;
		}

		$this->extensions[] = $compiler;
	}

	/**
	 * Look if a custom Blade compiler exists given its optional tag name.
	 *
	 * @param   string  $tag
	 *
	 * @return  bool
	 */
	public function hasExtension($tag)
	{
		return array_key_exists($tag, $this->extensions);
	}

	/**
	 * Remove a custom BLade compiler given its optional tag name
	 *
	 * @param   string  $tag
	 */
	public function removeExtension($tag)
	{
		if (!$this->hasExtension($tag))
		{
			return;
		}

		unset ($this->extensions[$tag]);
	}

	/**
	 * Get the regular expression for a generic Blade function.
	 *
	 * @param   string  $function
	 *
	 * @return string
	 */
	public function createMatcher($function)
	{
		return '/(?<!\w)(\s*)@' . $function . '(\s*\(.*\))/';
	}

	/**
	 * Get the regular expression for a generic Blade function.
	 *
	 * @param   string  $function
	 *
	 * @return string
	 */
	public function createOpenMatcher($function)
	{
		return '/(?<!\w)(\s*)@' . $function . '(\s*\(.*)\)/';
	}

	/**
	 * Create a plain Blade matcher.
	 *
	 * @param   string  $function
	 *
	 * @return string
	 */
	public function createPlainMatcher($function)
	{
		return '/(?<!\w)(\s*)@' . $function . '(\s*)/';
	}

	/**
	 * Sets the escaped content tags used for the compiler.
	 *
	 * @param   string  $openTag
	 * @param   string  $closeTag
	 *
	 * @return void
	 */
	public function setEscapedContentTags($openTag, $closeTag)
	{
		$this->setContentTags($openTag, $closeTag, true);
	}

	/**
	 * Gets the content tags used for the compiler.
	 *
	 * @return string
	 */
	public function getContentTags()
	{
		return $this->getTags();
	}

	/**
	 * Sets the content tags used for the compiler.
	 *
	 * @param   string  $openTag
	 * @param   string  $closeTag
	 * @param   bool    $escaped
	 *
	 * @return void
	 */
	public function setContentTags($openTag, $closeTag, $escaped = false)
	{
		$property = ($escaped === true) ? 'escapedTags' : 'contentTags';

		$this->{$property} = [preg_quote($openTag), preg_quote($closeTag)];
	}

	/**
	 * Gets the escaped content tags used for the compiler.
	 *
	 * @return string
	 */
	public function getEscapedContentTags()
	{
		return $this->getTags(true);
	}

	/**
	 * Returns the file extension supported by this compiler
	 *
	 * @return  string
	 *
	 * @since   3.3.1
	 */
	public function getFileExtension()
	{
		return $this->fileExtension;
	}

	/**
	 * Parse the tokens from the template.
	 *
	 * @param   array  $token
	 *
	 * @return string
	 */
	protected function parseToken($token)
	{
		[$id, $content] = $token;

		if ($id == T_INLINE_HTML)
		{
			foreach ($this->compilers as $type)
			{
				$content = $this->{"compile{$type}"}($content);
			}
		}

		return $content;
	}

	/**
	 * Execute the user defined extensions.
	 *
	 * @param   string  $value
	 *
	 * @return string
	 */
	protected function compileExtensions($value)
	{
		foreach ($this->extensions as $compiler)
		{
			$value = call_user_func($compiler, $value, $this);
		}

		return $value;
	}

	/**
	 * Compile Blade comments into valid PHP.
	 *
	 * @param   string  $value
	 *
	 * @return string
	 */
	protected function compileComments($value)
	{
		$pattern = sprintf('/%s--((.|\s)*?)--%s/', $this->contentTags[0], $this->contentTags[1]);

		return preg_replace($pattern, '<?php /*$1*/ ?>', $value);
	}

	/**
	 * Compile Blade echos into valid PHP.
	 *
	 * @param   string  $value
	 *
	 * @return string
	 */
	protected function compileEchos($value)
	{
		$difference = strlen($this->contentTags[0]) - strlen($this->escapedTags[0]);

		if ($difference > 0)
		{
			return $this->compileEscapedEchos($this->compileRegularEchos($value));
		}

		return $this->compileRegularEchos($this->compileEscapedEchos($value));
	}

	/**
	 * Compile Blade Statements that start with "@"
	 *
	 * @param   string  $value
	 *
	 * @return mixed
	 */
	protected function compileStatements($value)
	{
		return preg_replace_callback('/\B@(\w+)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x', [
			$this, 'compileStatementsCallback',
		], $value);
	}

	/**
	 * Callback for compileStatements, since $this is not allowed in Closures under PHP 5.3.
	 *
	 * @param   $match
	 *
	 * @return  string
	 */
	protected function compileStatementsCallback($match)
	{
		if (method_exists($this, $method = 'compile' . ucfirst($match[1])))
		{
			$match[0] = $this->$method(array_get($match, 3));
		}

		return isset($match[3]) ? $match[0] : $match[0] . $match[2];
	}

	/**
	 * Compile the "regular" echo statements.
	 *
	 * @param   string  $value
	 *
	 * @return  string
	 */
	protected function compileRegularEchos($value)
	{
		$pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->contentTags[0], $this->contentTags[1]);

		return preg_replace_callback($pattern, [$this, 'compileRegularEchosCallback'], $value);
	}

	/**
	 * Callback for compileRegularEchos, since $this is not allowed in Closures under PHP 5.3.
	 *
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	protected function compileRegularEchosCallback($matches)
	{
		$whitespace = empty($matches[3]) ? '' : $matches[3] . $matches[3];

		return $matches[1] ? substr($matches[0], 1) : '<?php echo ' . $this->compileEchoDefaults($matches[2]) . '; ?>' . $whitespace;
	}

	/**
	 * Compile the escaped echo statements.
	 *
	 * @param   string  $value
	 *
	 * @return string
	 */
	protected function compileEscapedEchos($value)
	{
		$pattern = sprintf('/%s\s*(.+?)\s*%s(\r?\n)?/s', $this->escapedTags[0], $this->escapedTags[1]);

		return preg_replace_callback($pattern, [$this, 'compileEscapedEchosCallback'], $value);
	}

	/**
	 * Callback for compileEscapedEchos, since $this is not allowed in Closures under PHP 5.3.
	 *
	 * @param   array  $matches
	 *
	 * @return  string
	 */
	protected function compileEscapedEchosCallback($matches)
	{
		$whitespace = empty($matches[2]) ? '' : $matches[2] . $matches[2];

		return '<?php echo $this->escape(' . $this->compileEchoDefaults($matches[1]) . '); ?>' . $whitespace;
	}

	/**
	 * Compile the each statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileEach($expression)
	{
		return "<?php echo \$this->renderEach{$expression}; ?>";
	}

	/**
	 * Compile the yield statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileYield($expression)
	{
		return "<?php echo \$this->yieldContent{$expression}; ?>";
	}

	/**
	 * Compile the show statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileShow($expression)
	{
		return "<?php echo \$this->yieldSection(); ?>";
	}

	/**
	 * Compile the section statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileSection($expression)
	{
		return "<?php \$this->startSection{$expression}; ?>";
	}

	/**
	 * Compile the append statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileAppend($expression)
	{
		return "<?php \$this->appendSection(); ?>";
	}

	/**
	 * Compile the end-section statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileEndsection($expression)
	{
		return "<?php \$this->stopSection(); ?>";
	}

	/**
	 * Compile the stop statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileStop($expression)
	{
		return "<?php \$this->stopSection(); ?>";
	}

	/**
	 * Compile the overwrite statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileOverwrite($expression)
	{
		return "<?php \$this->stopSection(true); ?>";
	}

	/**
	 * Compile the unless statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileUnless($expression)
	{
		return "<?php if ( ! $expression): ?>";
	}

	/**
	 * Compile the end unless statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileEndunless($expression)
	{
		return "<?php endif; ?>";
	}

	/**
	 * Compile the end repeatable statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileRepeatable($expression)
	{
		$expression = trim($expression, '()');
		$parts      = explode(',', $expression, 2);

		$functionName  = '_fof_blade_repeatable_' . md5($this->path . trim($parts[0]));
		$argumentsList = $parts[1] ?? '';

		return "<?php @\$$functionName = function($argumentsList) { ?>";
	}

	/**
	 * Compile the end endRepeatable statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileEndRepeatable($expression)
	{
		return "<?php }; ?>";
	}

	/**
	 * Compile the end yieldRepeatable statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileYieldRepeatable($expression)
	{
		$expression = trim($expression, '()');
		$parts      = explode(',', $expression, 2);

		$functionName  = '_fof_blade_repeatable_' . md5($this->path . trim($parts[0]));
		$argumentsList = $parts[1] ?? '';

		return "<?php \$$functionName($argumentsList); ?>";
	}

	/**
	 * Compile the lang statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileLang($expression)
	{
		return "<?php echo \\Joomla\\CMS\\Language\\Text::_$expression; ?>";
	}

	/**
	 * Compile the sprintf statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileSprintf($expression)
	{
		return "<?php echo \\Joomla\\CMS\\Language\\Text::sprintf$expression; ?>";
	}

	/**
	 * Compile the plural statements into valid PHP.
	 *
	 * e.g. @plural('COM_FOOBAR_N_ITEMS_SAVED', $countItemsSaved)
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 * @see JText::plural()
	 *
	 */
	protected function compilePlural($expression)
	{
		return "<?php echo \\Joomla\\CMS\\Language\\Text::plural$expression; ?>";
	}

	/**
	 * Compile the token statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileToken($expression)
	{
		return "<?php echo \$this->container->platform->getToken(true); ?>";
	}

	/**
	 * Compile the else statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileElse($expression)
	{
		return "<?php else: ?>";
	}

	/**
	 * Compile the for statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileFor($expression)
	{
		return "<?php for{$expression}: ?>";
	}

	/**
	 * Compile the foreach statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileForeach($expression)
	{
		return "<?php foreach{$expression}: ?>";
	}

	/**
	 * Compile the forelse statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileForelse($expression)
	{
		$empty = '$__empty_' . ++$this->forelseCounter;

		return "<?php {$empty} = true; foreach{$expression}: {$empty} = false; ?>";
	}

	/**
	 * Compile the if statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileIf($expression)
	{
		return "<?php if{$expression}: ?>";
	}

	/**
	 * Compile the else-if statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileElseif($expression)
	{
		return "<?php elseif{$expression}: ?>";
	}

	/**
	 * Compile the forelse statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileEmpty($expression)
	{
		$empty = '$__empty_' . $this->forelseCounter--;

		return "<?php endforeach; if ({$empty}): ?>";
	}

	/**
	 * Compile the while statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileWhile($expression)
	{
		return "<?php while{$expression}: ?>";
	}

	/**
	 * Compile the end-while statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileEndwhile($expression)
	{
		return "<?php endwhile; ?>";
	}

	/**
	 * Compile the end-for statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileEndfor($expression)
	{
		return "<?php endfor; ?>";
	}

	/**
	 * Compile the end-for-each statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileEndforeach($expression)
	{
		return "<?php endforeach; ?>";
	}

	/**
	 * Compile the end-if statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileEndif($expression)
	{
		return "<?php endif; ?>";
	}

	/**
	 * Compile the end-for-else statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileEndforelse($expression)
	{
		return "<?php endif; ?>";
	}

	/**
	 * Compile the extends statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileExtends($expression)
	{
		if (starts_with($expression, '('))
		{
			$expression = substr($expression, 1, -1);
		}

		$data = "<?php echo \$this->loadAnyTemplate($expression); ?>";

		$this->footer[] = $data;

		return '';
	}

	/**
	 * Compile the include statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileInclude($expression)
	{
		if (starts_with($expression, '('))
		{
			$expression = substr($expression, 1, -1);
		}

		return "<?php echo \$this->loadAnyTemplate($expression); ?>";
	}

	/**
	 * Compile the jlayout statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileJlayout($expression)
	{
		if (starts_with($expression, '('))
		{
			$expression = substr($expression, 1, -1);
		}

		return "<?php echo \\FOF30\\Layout\\LayoutHelper::render(\$this->container, $expression); ?>";
	}

	/**
	 * Compile the stack statements into the content
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileStack($expression)
	{
		return "<?php echo \$this->yieldContent{$expression}; ?>";
	}

	/**
	 * Compile the push statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compilePush($expression)
	{
		return "<?php \$this->startSection{$expression}; ?>";
	}

	/**
	 * Compile the endpush statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileEndpush($expression)
	{
		return "<?php \$this->appendSection(); ?>";
	}

	/**
	 * Compile the route statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileRoute($expression)
	{
		return "<?php echo \$this->container->template->route{$expression}; ?>";
	}

	/**
	 * Compile the css statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileCss($expression)
	{
		return "<?php \$this->addCssFile{$expression}; ?>";
	}

	/**
	 * Compile the inlineCss statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileInlineCss($expression)
	{
		return "<?php \$this->addCssInline{$expression}; ?>";
	}

	/**
	 * Compile the inlineJs statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileInlineJs($expression)
	{
		return "<?php \$this->addJavascriptInline{$expression}; ?>";
	}

	/**
	 * Compile the js statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileJs($expression)
	{
		return "<?php \$this->addJavascriptFile{$expression}; ?>";
	}

	/**
	 * Compile the jhtml statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileJhtml($expression)
	{
		return "<?php echo \\Joomla\\CMS\\HTML\\HTMLHelper::_{$expression}; ?>";
	}

	/**
	 * Compile the `sortgrid` statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 *
	 * @since 3.3.0
	 */
	protected function compileSortgrid($expression)
	{
		return "<?php echo \\FOF30\\Utils\\FEFHelper\\BrowseView::sortGrid{$expression} ?>";
	}

	/**
	 * Compile the `fieldtitle` statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 *
	 * @since 3.3.0
	 */
	protected function compileFieldtitle($expression)
	{
		return "<?php echo \\FOF30\\Utils\\FEFHelper\\BrowseView::fieldLabel{$expression} ?>";
	}

	/**
	 * Compile the `modelfilter($localField, [$modelTitleField, $modelName, $placeholder, $params])` statements into
	 * valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 *
	 * @since 3.3.0
	 */
	protected function compileModelfilter($expression)
	{
		return "<?php echo \\FOF30\\Utils\\FEFHelper\\BrowseView::modelFilter{$expression} ?>";
	}

	/**
	 * Compile the `selectfilter($localField, $options [, $placeholder, $params])` statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 *
	 * @since 3.3.0
	 */
	protected function compileSelectfilter($expression)
	{
		return "<?php echo \\FOF30\\Utils\\FEFHelper\\BrowseView::selectFilter{$expression} ?>";
	}

	/**
	 * Compile the `searchfilter($localField, $searchField = null, $placeholder = null, array $attributes = [])`
	 * statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 *
	 * @since 3.3.0
	 */
	protected function compileSearchfilter($expression)
	{
		return "<?php echo \\FOF30\\Utils\\FEFHelper\\BrowseView::searchFilter{$expression} ?>";
	}

	/**
	 * Compile the media statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileMedia($expression)
	{
		return "<?php echo \$this->container->template->parsePath{$expression}; ?>";
	}

	/**
	 * Compile the modules statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileModules($expression)
	{
		return "<?php echo \$this->container->template->loadPosition{$expression}; ?>";
	}

	/**
	 * Compile the module statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileModule($expression)
	{
		return "<?php echo \$this->container->template->loadModule{$expression}; ?>";
	}

	/**
	 * Compile the editor statements into valid PHP.
	 *
	 * @param   string  $expression
	 *
	 * @return string
	 */
	protected function compileEditor($expression)
	{
		return '<?php echo \\Joomla\\CMS\\Editor\\Editor::getInstance($this->container->platform->getConfig()->get(\'editor\', \'tinymce\'))' .
			'->display' . $expression . '; ?>';
	}

	/**
	 * Gets the tags used for the compiler.
	 *
	 * @param   bool  $escaped
	 *
	 * @return array
	 */
	protected function getTags($escaped = false)
	{
		$tags = $escaped ? $this->escapedTags : $this->contentTags;

		return array_map('stripcslashes', $tags);
	}
}
