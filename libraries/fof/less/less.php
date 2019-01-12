<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  less
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * This class is taken near verbatim (changes marked with **FOF** comment markers) from:
 *
 * lessphp v0.3.9
 * http://leafo.net/lessphp
 *
 * LESS css compiler, adapted from http://lesscss.org
 *
 * Copyright 2012, Leaf Corcoran <leafot@gmail.com>
 * Licensed under MIT or GPLv3, see LICENSE
 *
 * THIS IS THIRD PARTY CODE. Code comments are mostly useless placeholders to
 * stop phpcs from complaining...
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFLess
{
	public static $VERSION = "v0.3.9";

	protected static $TRUE = array("keyword", "true");

	protected static $FALSE = array("keyword", "false");

	protected $libFunctions = array();

	protected $registeredVars = array();

	protected $preserveComments = false;

	/**
	 * Prefix of abstract properties
	 *
	 * @var  string
	 */
	public $vPrefix = '@';

	/**
	 * Prefix of abstract blocks
	 *
	 * @var  string
	 */
	public $mPrefix = '$';

	public $parentSelector = '&';

	public $importDisabled = false;

	public $importDir = '';

	protected $numberPrecision = null;

	/**
	 * Set to the parser that generated the current line when compiling
	 * so we know how to create error messages
	 *
	 * @var  FOFLessParser
	 */
	protected $sourceParser = null;

	protected $sourceLoc = null;

	public static $defaultValue = array("keyword", "");

	/**
	 * Uniquely identify imports
	 *
	 * @var  integer
	 */
	protected static $nextImportId = 0;

	/**
	 * Attempts to find the path of an import url, returns null for css files
	 *
	 * @param   string  $url  The URL of the import
	 *
	 * @return  string|null
	 */
	protected function findImport($url)
	{
		foreach ((array) $this->importDir as $dir)
		{
			$full = $dir . (substr($dir, -1) != '/' ? '/' : '') . $url;

			if ($this->fileExists($file = $full . '.less') || $this->fileExists($file = $full))
			{
				return $file;
			}
		}

		return null;
	}

	/**
	 * Does file $name exists? It's a simple proxy to JFile for now
	 *
	 * @param   string  $name  The file we check for existence
	 *
	 * @return  boolean
	 */
	protected function fileExists($name)
	{
		/** FOF - BEGIN CHANGE * */
		return FOFPlatform::getInstance()->getIntegrationObject('filesystem')->fileExists($name);
		/** FOF - END CHANGE * */
	}

	/**
	 * Compresslist
	 *
	 * @param   array   $items  Items
	 * @param   string  $delim  Delimiter
	 *
	 * @return  array
	 */
	public static function compressList($items, $delim)
	{
		if (!isset($items[1]) && isset($items[0]))
		{
			return $items[0];
		}
		else
		{
			return array('list', $delim, $items);
		}
	}

	/**
	 * Quote for regular expression
	 *
	 * @param   string  $what  What to quote
	 *
	 * @return  string  Quoted string
	 */
	public static function preg_quote($what)
	{
		return preg_quote($what, '/');
	}

	/**
	 * Try import
	 *
	 * @param   string     $importPath   Import path
	 * @param   stdObject  $parentBlock  Parent block
	 * @param   string     $out          Out
	 *
	 * @return  boolean
	 */
	protected function tryImport($importPath, $parentBlock, $out)
	{
		if ($importPath[0] == "function" && $importPath[1] == "url")
		{
			$importPath = $this->flattenList($importPath[2]);
		}

		$str = $this->coerceString($importPath);

		if ($str === null)
		{
			return false;
		}

		$url = $this->compileValue($this->lib_e($str));

		// Don't import if it ends in css
		if (substr_compare($url, '.css', -4, 4) === 0)
		{
			return false;
		}

		$realPath = $this->findImport($url);

		if ($realPath === null)
		{
			return false;
		}

		if ($this->importDisabled)
		{
			return array(false, "/* import disabled */");
		}

		$this->addParsedFile($realPath);
		$parser = $this->makeParser($realPath);
		$root = $parser->parse(file_get_contents($realPath));

		// Set the parents of all the block props
		foreach ($root->props as $prop)
		{
			if ($prop[0] == "block")
			{
				$prop[1]->parent = $parentBlock;
			}
		}

		/**
		 * Copy mixins into scope, set their parents, bring blocks from import
		 * into current block
		 * TODO: need to mark the source parser	these came from this file
		 */
		foreach ($root->children as $childName => $child)
		{
			if (isset($parentBlock->children[$childName]))
			{
				$parentBlock->children[$childName] = array_merge(
					$parentBlock->children[$childName], $child
				);
			}
			else
			{
				$parentBlock->children[$childName] = $child;
			}
		}

		$pi = pathinfo($realPath);
		$dir = $pi["dirname"];

		list($top, $bottom) = $this->sortProps($root->props, true);
		$this->compileImportedProps($top, $parentBlock, $out, $parser, $dir);

		return array(true, $bottom, $parser, $dir);
	}

	/**
	 * Compile Imported Props
	 *
	 * @param   array          $props         Props
	 * @param   stdClass       $block         Block
	 * @param   string         $out           Out
	 * @param   FOFLessParser  $sourceParser  Source parser
	 * @param   string         $importDir     Import dir
	 *
	 * @return  void
	 */
	protected function compileImportedProps($props, $block, $out, $sourceParser, $importDir)
	{
		$oldSourceParser = $this->sourceParser;

		$oldImport = $this->importDir;

		// TODO: this is because the importDir api is stupid
		$this->importDir = (array) $this->importDir;
		array_unshift($this->importDir, $importDir);

		foreach ($props as $prop)
		{
			$this->compileProp($prop, $block, $out);
		}

		$this->importDir = $oldImport;
		$this->sourceParser = $oldSourceParser;
	}

	/**
	 * Recursively compiles a block.
	 *
	 * A block is analogous to a CSS block in most cases. A single LESS document
	 * is encapsulated in a block when parsed, but it does not have parent tags
	 * so all of it's children appear on the root level when compiled.
	 *
	 * Blocks are made up of props and children.
	 *
	 * Props are property instructions, array tuples which describe an action
	 * to be taken, eg. write a property, set a variable, mixin a block.
	 *
	 * The children of a block are just all the blocks that are defined within.
	 * This is used to look up mixins when performing a mixin.
	 *
	 * Compiling the block involves pushing a fresh environment on the stack,
	 * and iterating through the props, compiling each one.
	 *
	 * @param   stdClass  $block  Block
	 *
	 * @see  FOFLess::compileProp()
	 *
	 * @return  void
	 */
	protected function compileBlock($block)
	{
		switch ($block->type)
		{
			case "root":
				$this->compileRoot($block);
				break;
			case null:
				$this->compileCSSBlock($block);
				break;
			case "media":
				$this->compileMedia($block);
				break;
			case "directive":
				$name = "@" . $block->name;

				if (!empty($block->value))
				{
					$name .= " " . $this->compileValue($this->reduce($block->value));
				}

				$this->compileNestedBlock($block, array($name));
				break;
			default:
				$this->throwError("unknown block type: $block->type\n");
		}
	}

	/**
	 * Compile CSS block
	 *
	 * @param   stdClass  $block  Block to compile
	 *
	 * @return  void
	 */
	protected function compileCSSBlock($block)
	{
		$env = $this->pushEnv();

		$selectors = $this->compileSelectors($block->tags);
		$env->selectors = $this->multiplySelectors($selectors);
		$out = $this->makeOutputBlock(null, $env->selectors);

		$this->scope->children[] = $out;
		$this->compileProps($block, $out);

		// Mixins carry scope with them!
		$block->scope = $env;
		$this->popEnv();
	}

	/**
	 * Compile media
	 *
	 * @param   stdClass  $media  Media
	 *
	 * @return  void
	 */
	protected function compileMedia($media)
	{
		$env = $this->pushEnv($media);
		$parentScope = $this->mediaParent($this->scope);

		$query = $this->compileMediaQuery($this->multiplyMedia($env));

		$this->scope = $this->makeOutputBlock($media->type, array($query));
		$parentScope->children[] = $this->scope;

		$this->compileProps($media, $this->scope);

		if (count($this->scope->lines) > 0)
		{
			$orphanSelelectors = $this->findClosestSelectors();

			if (!is_null($orphanSelelectors))
			{
				$orphan = $this->makeOutputBlock(null, $orphanSelelectors);
				$orphan->lines = $this->scope->lines;
				array_unshift($this->scope->children, $orphan);
				$this->scope->lines = array();
			}
		}

		$this->scope = $this->scope->parent;
		$this->popEnv();
	}

	/**
	 * Media parent
	 *
	 * @param   stdClass  $scope  Scope
	 *
	 * @return  stdClass
	 */
	protected function mediaParent($scope)
	{
		while (!empty($scope->parent))
		{
			if (!empty($scope->type) && $scope->type != "media")
			{
				break;
			}

			$scope = $scope->parent;
		}

		return $scope;
	}

	/**
	 * Compile nested block
	 *
	 * @param   stdClass  $block      Block
	 * @param   array     $selectors  Selectors
	 *
	 * @return  void
	 */
	protected function compileNestedBlock($block, $selectors)
	{
		$this->pushEnv($block);
		$this->scope = $this->makeOutputBlock($block->type, $selectors);
		$this->scope->parent->children[] = $this->scope;

		$this->compileProps($block, $this->scope);

		$this->scope = $this->scope->parent;
		$this->popEnv();
	}

	/**
	 * Compile root
	 *
	 * @param   stdClass  $root  Root
	 *
	 * @return  void
	 */
	protected function compileRoot($root)
	{
		$this->pushEnv();
		$this->scope = $this->makeOutputBlock($root->type);
		$this->compileProps($root, $this->scope);
		$this->popEnv();
	}

	/**
	 * Compile props
	 *
	 * @param   type  $block  Something
	 * @param   type  $out    Something
	 *
	 * @return  void
	 */
	protected function compileProps($block, $out)
	{
		foreach ($this->sortProps($block->props) as $prop)
		{
			$this->compileProp($prop, $block, $out);
		}
	}

	/**
	 * Sort props
	 *
	 * @param   type  $props  X
	 * @param   type  $split  X
	 *
	 * @return  type
	 */
	protected function sortProps($props, $split = false)
	{
		$vars    = array();
		$imports = array();
		$other   = array();

		foreach ($props as $prop)
		{
			switch ($prop[0])
			{
				case "assign":
					if (isset($prop[1][0]) && $prop[1][0] == $this->vPrefix)
					{
						$vars[] = $prop;
					}
					else
					{
						$other[] = $prop;
					}
					break;
				case "import":
					$id        = self::$nextImportId++;
					$prop[]    = $id;
					$imports[] = $prop;
					$other[]   = array("import_mixin", $id);
					break;
				default:
					$other[] = $prop;
			}
		}

		if ($split)
		{
			return array(array_merge($vars, $imports), $other);
		}
		else
		{
			return array_merge($vars, $imports, $other);
		}
	}

	/**
	 * Compile media query
	 *
	 * @param   type  $queries  Queries
	 *
	 * @return  string
	 */
	protected function compileMediaQuery($queries)
	{
		$compiledQueries = array();

		foreach ($queries as $query)
		{
			$parts = array();

			foreach ($query as $q)
			{
				switch ($q[0])
				{
					case "mediaType":
						$parts[] = implode(" ", array_slice($q, 1));
						break;
					case "mediaExp":
						if (isset($q[2]))
						{
							$parts[] = "($q[1]: " .
								$this->compileValue($this->reduce($q[2])) . ")";
						}
						else
						{
							$parts[] = "($q[1])";
						}
						break;
					case "variable":
						$parts[] = $this->compileValue($this->reduce($q));
						break;
				}
			}

			if (count($parts) > 0)
			{
				$compiledQueries[] = implode(" and ", $parts);
			}
		}

		$out = "@media";

		if (!empty($parts))
		{
			$out .= " " .
				implode($this->formatter->selectorSeparator, $compiledQueries);
		}

		return $out;
	}

	/**
	 * Multiply media
	 *
	 * @param   type  $env           X
	 * @param   type  $childQueries  X
	 *
	 * @return  type
	 */
	protected function multiplyMedia($env, $childQueries = null)
	{
		if (is_null($env)
			|| !empty($env->block->type)
			&& $env->block->type != "media")
		{
			return $childQueries;
		}

		// Plain old block, skip
		if (empty($env->block->type))
		{
			return $this->multiplyMedia($env->parent, $childQueries);
		}

		$out = array();
		$queries = $env->block->queries;

		if (is_null($childQueries))
		{
			$out = $queries;
		}
		else
		{
			foreach ($queries as $parent)
			{
				foreach ($childQueries as $child)
				{
					$out[] = array_merge($parent, $child);
				}
			}
		}

		return $this->multiplyMedia($env->parent, $out);
	}

	/**
	 * Expand parent selectors
	 *
	 * @param   type  &$tag     Tag
	 * @param   type  $replace  Replace
	 *
	 * @return  type
	 */
	protected function expandParentSelectors(&$tag, $replace)
	{
		$parts = explode("$&$", $tag);
		$count = 0;

		foreach ($parts as &$part)
		{
			$part = str_replace($this->parentSelector, $replace, $part, $c);
			$count += $c;
		}

		$tag = implode($this->parentSelector, $parts);

		return $count;
	}

	/**
	 * Find closest selectors
	 *
	 * @return  array
	 */
	protected function findClosestSelectors()
	{
		$env = $this->env;
		$selectors = null;

		while ($env !== null)
		{
			if (isset($env->selectors))
			{
				$selectors = $env->selectors;
				break;
			}

			$env = $env->parent;
		}

		return $selectors;
	}

	/**
	 * Multiply $selectors against the nearest selectors in env
	 *
	 * @param   array  $selectors  The selectors
	 *
	 * @return  array
	 */
	protected function multiplySelectors($selectors)
	{
		// Find parent selectors

		$parentSelectors = $this->findClosestSelectors();

		if (is_null($parentSelectors))
		{
			// Kill parent reference in top level selector
			foreach ($selectors as &$s)
			{
				$this->expandParentSelectors($s, "");
			}

			return $selectors;
		}

		$out = array();

		foreach ($parentSelectors as $parent)
		{
			foreach ($selectors as $child)
			{
				$count = $this->expandParentSelectors($child, $parent);

				// Don't prepend the parent tag if & was used
				if ($count > 0)
				{
					$out[] = trim($child);
				}
				else
				{
					$out[] = trim($parent . ' ' . $child);
				}
			}
		}

		return $out;
	}

	/**
	 * Reduces selector expressions
	 *
	 * @param   array  $selectors  The selector expressions
	 *
	 * @return  array
	 */
	protected function compileSelectors($selectors)
	{
		$out = array();

		foreach ($selectors as $s)
		{
			if (is_array($s))
			{
				list(, $value) = $s;
				$out[] = trim($this->compileValue($this->reduce($value)));
			}
			else
			{
				$out[] = $s;
			}
		}

		return $out;
	}

	/**
	 * Equality check
	 *
	 * @param   mixed  $left   Left operand
	 * @param   mixed  $right  Right operand
	 *
	 * @return  boolean  True if equal
	 */
	protected function eq($left, $right)
	{
		return $left == $right;
	}

	/**
	 * Pattern match
	 *
	 * @param   type  $block        X
	 * @param   type  $callingArgs  X
	 *
	 * @return  boolean
	 */
	protected function patternMatch($block, $callingArgs)
	{
		/**
		 * Match the guards if it has them
		 * any one of the groups must have all its guards pass for a match
		 */
		if (!empty($block->guards))
		{
			$groupPassed = false;

			foreach ($block->guards as $guardGroup)
			{
				foreach ($guardGroup as $guard)
				{
					$this->pushEnv();
					$this->zipSetArgs($block->args, $callingArgs);

					$negate = false;

					if ($guard[0] == "negate")
					{
						$guard = $guard[1];
						$negate = true;
					}

					$passed = $this->reduce($guard) == self::$TRUE;

					if ($negate)
					{
						$passed = !$passed;
					}

					$this->popEnv();

					if ($passed)
					{
						$groupPassed = true;
					}
					else
					{
						$groupPassed = false;
						break;
					}
				}

				if ($groupPassed)
				{
					break;
				}
			}

			if (!$groupPassed)
			{
				return false;
			}
		}

		$numCalling = count($callingArgs);

		if (empty($block->args))
		{
			return $block->isVararg || $numCalling == 0;
		}

		// No args
		$i = -1;

		// Try to match by arity or by argument literal
		foreach ($block->args as $i => $arg)
		{
			switch ($arg[0])
			{
				case "lit":
					if (empty($callingArgs[$i]) || !$this->eq($arg[1], $callingArgs[$i]))
					{
						return false;
					}
					break;
				case "arg":
					// No arg and no default value
					if (!isset($callingArgs[$i]) && !isset($arg[2]))
					{
						return false;
					}
					break;
				case "rest":
					// Rest can be empty
					$i--;
					break 2;
			}
		}

		if ($block->isVararg)
		{
			// Not having enough is handled above
			return true;
		}
		else
		{
			$numMatched = $i + 1;

			// Greater than becuase default values always match
			return $numMatched >= $numCalling;
		}
	}

	/**
	 * Pattern match all
	 *
	 * @param   type  $blocks       X
	 * @param   type  $callingArgs  X
	 *
	 * @return  type
	 */
	protected function patternMatchAll($blocks, $callingArgs)
	{
		$matches = null;

		foreach ($blocks as $block)
		{
			if ($this->patternMatch($block, $callingArgs))
			{
				$matches[] = $block;
			}
		}

		return $matches;
	}

	/**
	 * Attempt to find blocks matched by path and args
	 *
	 * @param   array   $searchIn  Block to search in
	 * @param   string  $path      The path to search for
	 * @param   array   $args      Arguments
	 * @param   array   $seen      Your guess is as good as mine; that's third party code
	 *
	 * @return  null
	 */
	protected function findBlocks($searchIn, $path, $args, $seen = array())
	{
		if ($searchIn == null)
		{
			return null;
		}

		if (isset($seen[$searchIn->id]))
		{
			return null;
		}

		$seen[$searchIn->id] = true;

		$name = $path[0];

		if (isset($searchIn->children[$name]))
		{
			$blocks = $searchIn->children[$name];

			if (count($path) == 1)
			{
				$matches = $this->patternMatchAll($blocks, $args);

				if (!empty($matches))
				{
					// This will return all blocks that match in the closest
					// scope that has any matching block, like lessjs
					return $matches;
				}
			}
			else
			{
				$matches = array();

				foreach ($blocks as $subBlock)
				{
					$subMatches = $this->findBlocks($subBlock, array_slice($path, 1), $args, $seen);

					if (!is_null($subMatches))
					{
						foreach ($subMatches as $sm)
						{
							$matches[] = $sm;
						}
					}
				}

				return count($matches) > 0 ? $matches : null;
			}
		}

		if ($searchIn->parent === $searchIn)
		{
			return null;
		}

		return $this->findBlocks($searchIn->parent, $path, $args, $seen);
	}

	/**
	 * Sets all argument names in $args to either the default value
	 * or the one passed in through $values
	 *
	 * @param   array  $args    Arguments
	 * @param   array  $values  Values
	 *
	 * @return  void
	 */
	protected function zipSetArgs($args, $values)
	{
		$i = 0;
		$assignedValues = array();

		foreach ($args as $a)
		{
			if ($a[0] == "arg")
			{
				if ($i < count($values) && !is_null($values[$i]))
				{
					$value = $values[$i];
				}
				elseif (isset($a[2]))
				{
					$value = $a[2];
				}
				else
				{
					$value = null;
				}

				$value = $this->reduce($value);
				$this->set($a[1], $value);
				$assignedValues[] = $value;
			}

			$i++;
		}

		// Check for a rest
		$last = end($args);

		if ($last[0] == "rest")
		{
			$rest = array_slice($values, count($args) - 1);
			$this->set($last[1], $this->reduce(array("list", " ", $rest)));
		}

		$this->env->arguments = $assignedValues;
	}

	/**
	 * Compile a prop and update $lines or $blocks appropriately
	 *
	 * @param   array     $prop   Prop
	 * @param   stdClass  $block  Block
	 * @param   string    $out    Out
	 *
	 * @return  void
	 */
	protected function compileProp($prop, $block, $out)
	{
		// Set error position context
		$this->sourceLoc = isset($prop[-1]) ? $prop[-1] : -1;

		switch ($prop[0])
		{
			case 'assign':
				list(, $name, $value) = $prop;

				if ($name[0] == $this->vPrefix)
				{
					$this->set($name, $value);
				}
				else
				{
					$out->lines[] = $this->formatter->property($name, $this->compileValue($this->reduce($value)));
				}
				break;
			case 'block':
				list(, $child) = $prop;
				$this->compileBlock($child);
				break;
			case 'mixin':
				list(, $path, $args, $suffix) = $prop;

				$args = array_map(array($this, "reduce"), (array) $args);
				$mixins = $this->findBlocks($block, $path, $args);

				if ($mixins === null)
				{
					// Throw error here??
					break;
				}

				foreach ($mixins as $mixin)
				{
					$haveScope = false;

					if (isset($mixin->parent->scope))
					{
						$haveScope = true;
						$mixinParentEnv = $this->pushEnv();
						$mixinParentEnv->storeParent = $mixin->parent->scope;
					}

					$haveArgs = false;

					if (isset($mixin->args))
					{
						$haveArgs = true;
						$this->pushEnv();
						$this->zipSetArgs($mixin->args, $args);
					}

					$oldParent = $mixin->parent;

					if ($mixin != $block)
					{
						$mixin->parent = $block;
					}

					foreach ($this->sortProps($mixin->props) as $subProp)
					{
						if ($suffix !== null
							&& $subProp[0] == "assign"
							&& is_string($subProp[1])
							&& $subProp[1]{0} != $this->vPrefix)
						{
							$subProp[2] = array(
								'list', ' ',
								array($subProp[2], array('keyword', $suffix))
							);
						}

						$this->compileProp($subProp, $mixin, $out);
					}

					$mixin->parent = $oldParent;

					if ($haveArgs)
					{
						$this->popEnv();
					}

					if ($haveScope)
					{
						$this->popEnv();
					}
				}

				break;
			case 'raw':
				$out->lines[] = $prop[1];
				break;
			case "directive":
				list(, $name, $value) = $prop;
				$out->lines[] = "@$name " . $this->compileValue($this->reduce($value)) . ';';
				break;
			case "comment":
				$out->lines[] = $prop[1];
				break;
			case "import";
				list(, $importPath, $importId) = $prop;
				$importPath = $this->reduce($importPath);

				if (!isset($this->env->imports))
				{
					$this->env->imports = array();
				}

				$result = $this->tryImport($importPath, $block, $out);

				$this->env->imports[$importId] = $result === false ?
					array(false, "@import " . $this->compileValue($importPath) . ";") :
					$result;

				break;
			case "import_mixin":
				list(, $importId) = $prop;
				$import = $this->env->imports[$importId];

				if ($import[0] === false)
				{
					$out->lines[] = $import[1];
				}
				else
				{
					list(, $bottom, $parser, $importDir) = $import;
					$this->compileImportedProps($bottom, $block, $out, $parser, $importDir);
				}

				break;
			default:
				$this->throwError("unknown op: {$prop[0]}\n");
		}
	}

	/**
	 * Compiles a primitive value into a CSS property value.
	 *
	 * Values in lessphp are typed by being wrapped in arrays, their format is
	 * typically:
	 *
	 *     array(type, contents [, additional_contents]*)
	 *
	 * The input is expected to be reduced. This function will not work on
	 * things like expressions and variables.
	 *
	 * @param   array  $value  Value
	 *
	 * @return  void
	 */
	protected function compileValue($value)
	{
		switch ($value[0])
		{
			case 'list':
				// [1] - delimiter
				// [2] - array of values
				return implode($value[1], array_map(array($this, 'compileValue'), $value[2]));
			case 'raw_color':
				if (!empty($this->formatter->compressColors))
				{
					return $this->compileValue($this->coerceColor($value));
				}

				return $value[1];
			case 'keyword':
				// [1] - the keyword
				return $value[1];
			case 'number':
				// Format: [1] - the number -- [2] - the unit
				list(, $num, $unit) = $value;

				if ($this->numberPrecision !== null)
				{
					$num = round($num, $this->numberPrecision);
				}

				return $num . $unit;
			case 'string':
				// [1] - contents of string (includes quotes)
				list(, $delim, $content) = $value;

				foreach ($content as &$part)
				{
					if (is_array($part))
					{
						$part = $this->compileValue($part);
					}
				}

				return $delim . implode($content) . $delim;
			case 'color':
				/**
				 * Format:
				 *
				 * [1] - red component (either number or a %)
				 * [2] - green component
				 * [3] - blue component
				 * [4] - optional alpha component
				 */
				list(, $r, $g, $b) = $value;
				$r = round($r);
				$g = round($g);
				$b = round($b);

				if (count($value) == 5 && $value[4] != 1)
				{
					// Return an rgba value
					return 'rgba(' . $r . ',' . $g . ',' . $b . ',' . $value[4] . ')';
				}

				$h = sprintf("#%02x%02x%02x", $r, $g, $b);

				if (!empty($this->formatter->compressColors))
				{
					// Converting hex color to short notation (e.g. #003399 to #039)
					if ($h[1] === $h[2] && $h[3] === $h[4] && $h[5] === $h[6])
					{
						$h = '#' . $h[1] . $h[3] . $h[5];
					}
				}

				return $h;

			case 'function':
				list(, $name, $args) = $value;

				return $name . '(' . $this->compileValue($args) . ')';

			default:
				// Assumed to be unit
				$this->throwError("unknown value type: $value[0]");
		}
	}

	/**
	 * Lib is number
	 *
	 * @param   type  $value  X
	 *
	 * @return  boolean
	 */
	protected function lib_isnumber($value)
	{
		return $this->toBool($value[0] == "number");
	}

	/**
	 * Lib is string
	 *
	 * @param   type  $value  X
	 *
	 * @return  boolean
	 */
	protected function lib_isstring($value)
	{
		return $this->toBool($value[0] == "string");
	}

	/**
	 * Lib is color
	 *
	 * @param   type  $value  X
	 *
	 * @return  boolean
	 */
	protected function lib_iscolor($value)
	{
		return $this->toBool($this->coerceColor($value));
	}

	/**
	 * Lib is keyword
	 *
	 * @param   type  $value  X
	 *
	 * @return  boolean
	 */
	protected function lib_iskeyword($value)
	{
		return $this->toBool($value[0] == "keyword");
	}

	/**
	 * Lib is pixel
	 *
	 * @param   type  $value  X
	 *
	 * @return  boolean
	 */
	protected function lib_ispixel($value)
	{
		return $this->toBool($value[0] == "number" && $value[2] == "px");
	}

	/**
	 * Lib is percentage
	 *
	 * @param   type  $value  X
	 *
	 * @return  boolean
	 */
	protected function lib_ispercentage($value)
	{
		return $this->toBool($value[0] == "number" && $value[2] == "%");
	}

	/**
	 * Lib is em
	 *
	 * @param   type  $value  X
	 *
	 * @return  boolean
	 */
	protected function lib_isem($value)
	{
		return $this->toBool($value[0] == "number" && $value[2] == "em");
	}

	/**
	 * Lib is rem
	 *
	 * @param   type  $value  X
	 *
	 * @return  boolean
	 */
	protected function lib_isrem($value)
	{
		return $this->toBool($value[0] == "number" && $value[2] == "rem");
	}

	/**
	 * LIb rgba hex
	 *
	 * @param   type  $color  X
	 *
	 * @return  boolean
	 */
	protected function lib_rgbahex($color)
	{
		$color = $this->coerceColor($color);

		if (is_null($color))
		{
			$this->throwError("color expected for rgbahex");
		}

		return sprintf("#%02x%02x%02x%02x", isset($color[4]) ? $color[4] * 255 : 255, $color[1], $color[2], $color[3]);
	}

	/**
	 * Lib argb
	 *
	 * @param   type  $color  X
	 *
	 * @return  type
	 */
	protected function lib_argb($color)
	{
		return $this->lib_rgbahex($color);
	}

	/**
	 * Utility func to unquote a string
	 *
	 * @param   string  $arg  Arg
	 *
	 * @return  string
	 */
	protected function lib_e($arg)
	{
		switch ($arg[0])
		{
			case "list":
				$items = $arg[2];

				if (isset($items[0]))
				{
					return $this->lib_e($items[0]);
				}

				return self::$defaultValue;

			case "string":
				$arg[1] = "";

				return $arg;

			case "keyword":
				return $arg;

			default:
				return array("keyword", $this->compileValue($arg));
		}
	}

	/**
	 * Lib sprintf
	 *
	 * @param   type  $args  X
	 *
	 * @return  type
	 */
	protected function lib__sprintf($args)
	{
		if ($args[0] != "list")
		{
			return $args;
		}

		$values = $args[2];
		$string = array_shift($values);
		$template = $this->compileValue($this->lib_e($string));

		$i = 0;

		if (preg_match_all('/%[dsa]/', $template, $m))
		{
			foreach ($m[0] as $match)
			{
				$val = isset($values[$i]) ?
					$this->reduce($values[$i]) : array('keyword', '');

				// Lessjs compat, renders fully expanded color, not raw color
				if ($color = $this->coerceColor($val))
				{
					$val = $color;
				}

				$i++;
				$rep = $this->compileValue($this->lib_e($val));
				$template = preg_replace('/' . self::preg_quote($match) . '/', $rep, $template, 1);
			}
		}

		$d = $string[0] == "string" ? $string[1] : '"';

		return array("string", $d, array($template));
	}

	/**
	 * Lib floor
	 *
	 * @param   type  $arg  X
	 *
	 * @return  array
	 */
	protected function lib_floor($arg)
	{
		$value = $this->assertNumber($arg);

		return array("number", floor($value), $arg[2]);
	}

	/**
	 * Lib ceil
	 *
	 * @param   type  $arg  X
	 *
	 * @return  array
	 */
	protected function lib_ceil($arg)
	{
		$value = $this->assertNumber($arg);

		return array("number", ceil($value), $arg[2]);
	}

	/**
	 * Lib round
	 *
	 * @param   type  $arg  X
	 *
	 * @return  array
	 */
	protected function lib_round($arg)
	{
		$value = $this->assertNumber($arg);

		return array("number", round($value), $arg[2]);
	}

	/**
	 * Lib unit
	 *
	 * @param   type  $arg  X
	 *
	 * @return  array
	 */
	protected function lib_unit($arg)
	{
		if ($arg[0] == "list")
		{
			list($number, $newUnit) = $arg[2];
			return array("number", $this->assertNumber($number), $this->compileValue($this->lib_e($newUnit)));
		}
		else
		{
			return array("number", $this->assertNumber($arg), "");
		}
	}

	/**
	 * Helper function to get arguments for color manipulation functions.
	 * takes a list that contains a color like thing and a percentage
	 *
	 * @param   array  $args  Args
	 *
	 * @return  array
	 */
	protected function colorArgs($args)
	{
		if ($args[0] != 'list' || count($args[2]) < 2)
		{
			return array(array('color', 0, 0, 0), 0);
		}

		list($color, $delta) = $args[2];
		$color = $this->assertColor($color);
		$delta = floatval($delta[1]);

		return array($color, $delta);
	}

	/**
	 * Lib darken
	 *
	 * @param   type  $args  X
	 *
	 * @return  type
	 */
	protected function lib_darken($args)
	{
		list($color, $delta) = $this->colorArgs($args);

		$hsl = $this->toHSL($color);
		$hsl[3] = $this->clamp($hsl[3] - $delta, 100);

		return $this->toRGB($hsl);
	}

	/**
	 * Lib lighten
	 *
	 * @param   type  $args  X
	 *
	 * @return  type
	 */
	protected function lib_lighten($args)
	{
		list($color, $delta) = $this->colorArgs($args);

		$hsl = $this->toHSL($color);
		$hsl[3] = $this->clamp($hsl[3] + $delta, 100);

		return $this->toRGB($hsl);
	}

	/**
	 * Lib saturate
	 *
	 * @param   type  $args  X
	 *
	 * @return  type
	 */
	protected function lib_saturate($args)
	{
		list($color, $delta) = $this->colorArgs($args);

		$hsl = $this->toHSL($color);
		$hsl[2] = $this->clamp($hsl[2] + $delta, 100);

		return $this->toRGB($hsl);
	}

	/**
	 * Lib desaturate
	 *
	 * @param   type  $args  X
	 *
	 * @return  type
	 */
	protected function lib_desaturate($args)
	{
		list($color, $delta) = $this->colorArgs($args);

		$hsl = $this->toHSL($color);
		$hsl[2] = $this->clamp($hsl[2] - $delta, 100);

		return $this->toRGB($hsl);
	}

	/**
	 * Lib spin
	 *
	 * @param   type  $args  X
	 *
	 * @return  type
	 */
	protected function lib_spin($args)
	{
		list($color, $delta) = $this->colorArgs($args);

		$hsl = $this->toHSL($color);

		$hsl[1] = $hsl[1] + $delta % 360;

		if ($hsl[1] < 0)
		{
			$hsl[1] += 360;
		}

		return $this->toRGB($hsl);
	}

	/**
	 * Lib fadeout
	 *
	 * @param   type  $args  X
	 *
	 * @return  type
	 */
	protected function lib_fadeout($args)
	{
		list($color, $delta) = $this->colorArgs($args);
		$color[4] = $this->clamp((isset($color[4]) ? $color[4] : 1) - $delta / 100);

		return $color;
	}

	/**
	 * Lib fadein
	 *
	 * @param   type  $args  X
	 *
	 * @return  type
	 */
	protected function lib_fadein($args)
	{
		list($color, $delta) = $this->colorArgs($args);
		$color[4] = $this->clamp((isset($color[4]) ? $color[4] : 1) + $delta / 100);

		return $color;
	}

	/**
	 * Lib hue
	 *
	 * @param   type  $color  X
	 *
	 * @return  type
	 */
	protected function lib_hue($color)
	{
		$hsl = $this->toHSL($this->assertColor($color));

		return round($hsl[1]);
	}

	/**
	 * Lib saturation
	 *
	 * @param   type  $color  X
	 *
	 * @return  type
	 */
	protected function lib_saturation($color)
	{
		$hsl = $this->toHSL($this->assertColor($color));

		return round($hsl[2]);
	}

	/**
	 * Lib lightness
	 *
	 * @param   type  $color  X
	 *
	 * @return  type
	 */
	protected function lib_lightness($color)
	{
		$hsl = $this->toHSL($this->assertColor($color));

		return round($hsl[3]);
	}

	/**
	 * Get the alpha of a color
	 * Defaults to 1 for non-colors or colors without an alpha
	 *
	 * @param   string  $value  Value
	 *
	 * @return  string
	 */
	protected function lib_alpha($value)
	{
		if (!is_null($color = $this->coerceColor($value)))
		{
			return isset($color[4]) ? $color[4] : 1;
		}
	}

	/**
	 * Set the alpha of the color
	 *
	 * @param   array  $args  Args
	 *
	 * @return  string
	 */
	protected function lib_fade($args)
	{
		list($color, $alpha) = $this->colorArgs($args);
		$color[4] = $this->clamp($alpha / 100.0);

		return $color;
	}

	/**
	 * Third party code; your guess is as good as mine
	 *
	 * @param   array  $arg  Arg
	 *
	 * @return  string
	 */
	protected function lib_percentage($arg)
	{
		$num = $this->assertNumber($arg);

		return array("number", $num * 100, "%");
	}

	/**
	 * mixes two colors by weight
	 * mix(@color1, @color2, @weight);
	 * http://sass-lang.com/docs/yardoc/Sass/Script/Functions.html#mix-instance_method
	 *
	 * @param   array  $args  Args
	 *
	 * @return  string
	 */
	protected function lib_mix($args)
	{
		if ($args[0] != "list" || count($args[2]) < 3)
		{
			$this->throwError("mix expects (color1, color2, weight)");
		}

		list($first, $second, $weight) = $args[2];
		$first = $this->assertColor($first);
		$second = $this->assertColor($second);

		$first_a = $this->lib_alpha($first);
		$second_a = $this->lib_alpha($second);
		$weight = $weight[1] / 100.0;

		$w = $weight * 2 - 1;
		$a = $first_a - $second_a;

		$w1 = (($w * $a == -1 ? $w : ($w + $a) / (1 + $w * $a)) + 1) / 2.0;
		$w2 = 1.0 - $w1;

		$new = array('color',
			$w1 * $first[1] + $w2 * $second[1],
			$w1 * $first[2] + $w2 * $second[2],
			$w1 * $first[3] + $w2 * $second[3],
		);

		if ($first_a != 1.0 || $second_a != 1.0)
		{
			$new[] = $first_a * $weight + $second_a * ($weight - 1);
		}

		return $this->fixColor($new);
	}

	/**
	 * Third party code; your guess is as good as mine
	 *
	 * @param   array  $arg  Arg
	 *
	 * @return  string
	 */
	protected function lib_contrast($args)
	{
		if ($args[0] != 'list' || count($args[2]) < 3)
		{
			return array(array('color', 0, 0, 0), 0);
		}

		list($inputColor, $darkColor, $lightColor) = $args[2];

		$inputColor = $this->assertColor($inputColor);
		$darkColor = $this->assertColor($darkColor);
		$lightColor = $this->assertColor($lightColor);
		$hsl = $this->toHSL($inputColor);

		if ($hsl[3] > 50)
		{
			return $darkColor;
		}

		return $lightColor;
	}

	/**
	 * Assert color
	 *
	 * @param   type  $value  X
	 * @param   type  $error  X
	 *
	 * @return  type
	 */
	protected function assertColor($value, $error = "expected color value")
	{
		$color = $this->coerceColor($value);

		if (is_null($color))
		{
			$this->throwError($error);
		}

		return $color;
	}

	/**
	 * Assert number
	 *
	 * @param   type  $value  X
	 * @param   type  $error  X
	 *
	 * @return  type
	 */
	protected function assertNumber($value, $error = "expecting number")
	{
		if ($value[0] == "number")
		{
			return $value[1];
		}

		$this->throwError($error);
	}

	/**
	 * To HSL
	 *
	 * @param   type  $color  X
	 *
	 * @return  type
	 */
	protected function toHSL($color)
	{
		if ($color[0] == 'hsl')
		{
			return $color;
		}

		$r = $color[1] / 255;
		$g = $color[2] / 255;
		$b = $color[3] / 255;

		$min = min($r, $g, $b);
		$max = max($r, $g, $b);

		$L = ($min + $max) / 2;

		if ($min == $max)
		{
			$S = $H = 0;
		}
		else
		{
			if ($L < 0.5)
			{
				$S = ($max - $min) / ($max + $min);
			}
			else
			{
				$S = ($max - $min) / (2.0 - $max - $min);
			}

			if ($r == $max)
			{
				$H = ($g - $b) / ($max - $min);
			}
			elseif ($g == $max)
			{
				$H = 2.0 + ($b - $r) / ($max - $min);
			}
			elseif ($b == $max)
			{
				$H = 4.0 + ($r - $g) / ($max - $min);
			}
		}

		$out = array('hsl',
			($H < 0 ? $H + 6 : $H) * 60,
			$S * 100,
			$L * 100,
		);

		if (count($color) > 4)
		{
			// Copy alpha
			$out[] = $color[4];
		}

		return $out;
	}

	/**
	 * To RGB helper
	 *
	 * @param   type  $comp   X
	 * @param   type  $temp1  X
	 * @param   type  $temp2  X
	 *
	 * @return  type
	 */
	protected function toRGB_helper($comp, $temp1, $temp2)
	{
		if ($comp < 0)
		{
			$comp += 1.0;
		}
		elseif ($comp > 1)
		{
			$comp -= 1.0;
		}

		if (6 * $comp < 1)
		{
			return $temp1 + ($temp2 - $temp1) * 6 * $comp;
		}

		if (2 * $comp < 1)
		{
			return $temp2;
		}

		if (3 * $comp < 2)
		{
			return $temp1 + ($temp2 - $temp1) * ((2 / 3) - $comp) * 6;
		}

		return $temp1;
	}

	/**
	 * Converts a hsl array into a color value in rgb.
	 * Expects H to be in range of 0 to 360, S and L in 0 to 100
	 *
	 * @param   type  $color  X
	 *
	 * @return  type
	 */
	protected function toRGB($color)
	{
		if ($color == 'color')
		{
			return $color;
		}

		$H = $color[1] / 360;
		$S = $color[2] / 100;
		$L = $color[3] / 100;

		if ($S == 0)
		{
			$r = $g = $b = $L;
		}
		else
		{
			$temp2 = $L < 0.5 ?
				$L * (1.0 + $S) :
				$L + $S - $L * $S;

			$temp1 = 2.0 * $L - $temp2;

			$r = $this->toRGB_helper($H + 1 / 3, $temp1, $temp2);
			$g = $this->toRGB_helper($H, $temp1, $temp2);
			$b = $this->toRGB_helper($H - 1 / 3, $temp1, $temp2);
		}

		// $out = array('color', round($r*255), round($g*255), round($b*255));
		$out = array('color', $r * 255, $g * 255, $b * 255);

		if (count($color) > 4)
		{
			// Copy alpha
			$out[] = $color[4];
		}

		return $out;
	}

	/**
	 * Clamp
	 *
	 * @param   type  $v    X
	 * @param   type  $max  X
	 * @param   type  $min  X
	 *
	 * @return  type
	 */
	protected function clamp($v, $max = 1, $min = 0)
	{
		return min($max, max($min, $v));
	}

	/**
	 * Convert the rgb, rgba, hsl color literals of function type
	 * as returned by the parser into values of color type.
	 *
	 * @param   type  $func  X
	 *
	 * @return  type
	 */
	protected function funcToColor($func)
	{
		$fname = $func[1];

		if ($func[2][0] != 'list')
		{
			// Need a list of arguments
			return false;
		}

		$rawComponents = $func[2][2];

		if ($fname == 'hsl' || $fname == 'hsla')
		{
			$hsl = array('hsl');
			$i = 0;

			foreach ($rawComponents as $c)
			{
				$val = $this->reduce($c);
				$val = isset($val[1]) ? floatval($val[1]) : 0;

				if ($i == 0)
				{
					$clamp = 360;
				}
				elseif ($i < 3)
				{
					$clamp = 100;
				}
				else
				{
					$clamp = 1;
				}

				$hsl[] = $this->clamp($val, $clamp);
				$i++;
			}

			while (count($hsl) < 4)
			{
				$hsl[] = 0;
			}

			return $this->toRGB($hsl);
		}
		elseif ($fname == 'rgb' || $fname == 'rgba')
		{
			$components = array();
			$i = 1;

			foreach ($rawComponents as $c)
			{
				$c = $this->reduce($c);

				if ($i < 4)
				{
					if ($c[0] == "number" && $c[2] == "%")
					{
						$components[] = 255 * ($c[1] / 100);
					}
					else
					{
						$components[] = floatval($c[1]);
					}
				}
				elseif ($i == 4)
				{
					if ($c[0] == "number" && $c[2] == "%")
					{
						$components[] = 1.0 * ($c[1] / 100);
					}
					else
					{
						$components[] = floatval($c[1]);
					}
				}
				else
				{
					break;
				}

				$i++;
			}

			while (count($components) < 3)
			{
				$components[] = 0;
			}

			array_unshift($components, 'color');

			return $this->fixColor($components);
		}

		return false;
	}

	/**
	 * Reduce
	 *
	 * @param   type  $value          X
	 * @param   type  $forExpression  X
	 *
	 * @return  type
	 */
	protected function reduce($value, $forExpression = false)
	{
		switch ($value[0])
		{
			case "interpolate":
				$reduced = $this->reduce($value[1]);
				$var     = $this->compileValue($reduced);
				$res     = $this->reduce(array("variable", $this->vPrefix . $var));

				if (empty($value[2]))
				{
					$res = $this->lib_e($res);
				}

				return $res;
			case "variable":
				$key = $value[1];
				if (is_array($key))
				{
					$key = $this->reduce($key);
					$key = $this->vPrefix . $this->compileValue($this->lib_e($key));
				}

				$seen = & $this->env->seenNames;

				if (!empty($seen[$key]))
				{
					$this->throwError("infinite loop detected: $key");
				}

				$seen[$key] = true;
				$out = $this->reduce($this->get($key, self::$defaultValue));
				$seen[$key] = false;

				return $out;
			case "list":
				foreach ($value[2] as &$item)
				{
					$item = $this->reduce($item, $forExpression);
				}

				return $value;
			case "expression":
				return $this->evaluate($value);
			case "string":
				foreach ($value[2] as &$part)
				{
					if (is_array($part))
					{
						$strip = $part[0] == "variable";
						$part = $this->reduce($part);

						if ($strip)
						{
							$part = $this->lib_e($part);
						}
					}
				}

				return $value;
			case "escape":
				list(, $inner) = $value;

				return $this->lib_e($this->reduce($inner));
			case "function":
				$color = $this->funcToColor($value);

				if ($color)
				{
					return $color;
				}

				list(, $name, $args) = $value;

				if ($name == "%")
				{
					$name = "_sprintf";
				}

				$f = isset($this->libFunctions[$name]) ?
					$this->libFunctions[$name] : array($this, 'lib_' . $name);

				if (is_callable($f))
				{
					if ($args[0] == 'list')
					{
						$args = self::compressList($args[2], $args[1]);
					}

					$ret = call_user_func($f, $this->reduce($args, true), $this);

					if (is_null($ret))
					{
						return array("string", "", array(
								$name, "(", $args, ")"
							));
					}

					// Convert to a typed value if the result is a php primitive
					if (is_numeric($ret))
					{
						$ret = array('number', $ret, "");
					}
					elseif (!is_array($ret))
					{
						$ret = array('keyword', $ret);
					}

					return $ret;
				}

				// Plain function, reduce args
				$value[2] = $this->reduce($value[2]);

				return $value;
			case "unary":
				list(, $op, $exp) = $value;
				$exp = $this->reduce($exp);

				if ($exp[0] == "number")
				{
					switch ($op)
					{
						case "+":
							return $exp;
						case "-":
							$exp[1] *= -1;

							return $exp;
					}
				}

				return array("string", "", array($op, $exp));
		}

		if ($forExpression)
		{
			switch ($value[0])
			{
				case "keyword":
					if ($color = $this->coerceColor($value))
					{
						return $color;
					}
					break;
				case "raw_color":
					return $this->coerceColor($value);
			}
		}

		return $value;
	}

	/**
	 * Coerce a value for use in color operation
	 *
	 * @param   type  $value  X
	 *
	 * @return  null
	 */
	protected function coerceColor($value)
	{
		switch ($value[0])
		{
			case 'color':
				return $value;
			case 'raw_color':
				$c = array("color", 0, 0, 0);
				$colorStr = substr($value[1], 1);
				$num = hexdec($colorStr);
				$width = strlen($colorStr) == 3 ? 16 : 256;

				for ($i = 3; $i > 0; $i--)
				{
					// It's 3 2 1
					$t = $num % $width;
					$num /= $width;

					$c[$i] = $t * (256 / $width) + $t * floor(16 / $width);
				}

				return $c;
			case 'keyword':
				$name = $value[1];

				if (isset(self::$cssColors[$name]))
				{
					$rgba = explode(',', self::$cssColors[$name]);

					if (isset($rgba[3]))
					{
						return array('color', $rgba[0], $rgba[1], $rgba[2], $rgba[3]);
					}

					return array('color', $rgba[0], $rgba[1], $rgba[2]);
				}

				return null;
		}
	}

	/**
	 * Make something string like into a string
	 *
	 * @param   type  $value  X
	 *
	 * @return  null
	 */
	protected function coerceString($value)
	{
		switch ($value[0])
		{
			case "string":
				return $value;
			case "keyword":
				return array("string", "", array($value[1]));
		}

		return null;
	}

	/**
	 * Turn list of length 1 into value type
	 *
	 * @param   type  $value  X
	 *
	 * @return  type
	 */
	protected function flattenList($value)
	{
		if ($value[0] == "list" && count($value[2]) == 1)
		{
			return $this->flattenList($value[2][0]);
		}

		return $value;
	}

	/**
	 * To bool
	 *
	 * @param   type  $a  X
	 *
	 * @return  type
	 */
	protected function toBool($a)
	{
		if ($a)
		{
			return self::$TRUE;
		}
		else
		{
			return self::$FALSE;
		}
	}

	/**
	 * Evaluate an expression
	 *
	 * @param   type  $exp  X
	 *
	 * @return  type
	 */
	protected function evaluate($exp)
	{
		list(, $op, $left, $right, $whiteBefore, $whiteAfter) = $exp;

		$left = $this->reduce($left, true);
		$right = $this->reduce($right, true);

		if ($leftColor = $this->coerceColor($left))
		{
			$left = $leftColor;
		}

		if ($rightColor = $this->coerceColor($right))
		{
			$right = $rightColor;
		}

		$ltype = $left[0];
		$rtype = $right[0];

		// Operators that work on all types
		if ($op == "and")
		{
			return $this->toBool($left == self::$TRUE && $right == self::$TRUE);
		}

		if ($op == "=")
		{
			return $this->toBool($this->eq($left, $right));
		}

		if ($op == "+" && !is_null($str = $this->stringConcatenate($left, $right)))
		{
			return $str;
		}

		// Type based operators
		$fname = "op_${ltype}_${rtype}";

		if (is_callable(array($this, $fname)))
		{
			$out = $this->$fname($op, $left, $right);

			if (!is_null($out))
			{
				return $out;
			}
		}

		// Make the expression look it did before being parsed
		$paddedOp = $op;

		if ($whiteBefore)
		{
			$paddedOp = " " . $paddedOp;
		}

		if ($whiteAfter)
		{
			$paddedOp .= " ";
		}

		return array("string", "", array($left, $paddedOp, $right));
	}

	/**
	 * String concatenate
	 *
	 * @param   type    $left   X
	 * @param   string  $right  X
	 *
	 * @return  string
	 */
	protected function stringConcatenate($left, $right)
	{
		if ($strLeft = $this->coerceString($left))
		{
			if ($right[0] == "string")
			{
				$right[1] = "";
			}

			$strLeft[2][] = $right;

			return $strLeft;
		}

		if ($strRight = $this->coerceString($right))
		{
			array_unshift($strRight[2], $left);

			return $strRight;
		}
	}

	/**
	 * Make sure a color's components don't go out of bounds
	 *
	 * @param   type  $c  X
	 *
	 * @return  int
	 */
	protected function fixColor($c)
	{
		foreach (range(1, 3) as $i)
		{
			if ($c[$i] < 0)
			{
				$c[$i] = 0;
			}

			if ($c[$i] > 255)
			{
				$c[$i] = 255;
			}
		}

		return $c;
	}

	/**
	 * Op number color
	 *
	 * @param   type  $op   X
	 * @param   type  $lft  X
	 * @param   type  $rgt  X
	 *
	 * @return  type
	 */
	protected function op_number_color($op, $lft, $rgt)
	{
		if ($op == '+' || $op == '*')
		{
			return $this->op_color_number($op, $rgt, $lft);
		}
	}

	/**
	 * Op color number
	 *
	 * @param   type  $op   X
	 * @param   type  $lft  X
	 * @param   int   $rgt  X
	 *
	 * @return  type
	 */
	protected function op_color_number($op, $lft, $rgt)
	{
		if ($rgt[0] == '%')
		{
			$rgt[1] /= 100;
		}

		return $this->op_color_color($op, $lft, array_fill(1, count($lft) - 1, $rgt[1]));
	}

	/**
	 * Op color color
	 *
	 * @param   type  $op     X
	 * @param   type  $left   X
	 * @param   type  $right  X
	 *
	 * @return  type
	 */
	protected function op_color_color($op, $left, $right)
	{
		$out = array('color');
		$max = count($left) > count($right) ? count($left) : count($right);

		foreach (range(1, $max - 1) as $i)
		{
			$lval = isset($left[$i]) ? $left[$i] : 0;
			$rval = isset($right[$i]) ? $right[$i] : 0;

			switch ($op)
			{
				case '+':
					$out[] = $lval + $rval;
					break;
				case '-':
					$out[] = $lval - $rval;
					break;
				case '*':
					$out[] = $lval * $rval;
					break;
				case '%':
					$out[] = $lval % $rval;
					break;
				case '/':
					if ($rval == 0)
					{
						$this->throwError("evaluate error: can't divide by zero");
					}

					$out[] = $lval / $rval;
					break;
				default:
					$this->throwError('evaluate error: color op number failed on op ' . $op);
			}
		}

		return $this->fixColor($out);
	}

	/**
	 * Lib red
	 *
	 * @param   type  $color  X
	 *
	 * @return  type
	 */
	public function lib_red($color)
	{
		$color = $this->coerceColor($color);

		if (is_null($color))
		{
			$this->throwError('color expected for red()');
		}

		return $color[1];
	}

	/**
	 * Lib green
	 *
	 * @param   type  $color  X
	 *
	 * @return  type
	 */
	public function lib_green($color)
	{
		$color = $this->coerceColor($color);

		if (is_null($color))
		{
			$this->throwError('color expected for green()');
		}

		return $color[2];
	}

	/**
	 * Lib blue
	 *
	 * @param   type  $color  X
	 *
	 * @return  type
	 */
	public function lib_blue($color)
	{
		$color = $this->coerceColor($color);

		if (is_null($color))
		{
			$this->throwError('color expected for blue()');
		}

		return $color[3];
	}

	/**
	 * Operator on two numbers
	 *
	 * @param   type  $op     X
	 * @param   type  $left   X
	 * @param   type  $right  X
	 *
	 * @return  type
	 */
	protected function op_number_number($op, $left, $right)
	{
		$unit = empty($left[2]) ? $right[2] : $left[2];

		$value = 0;

		switch ($op)
		{
			case '+':
				$value = $left[1] + $right[1];
				break;
			case '*':
				$value = $left[1] * $right[1];
				break;
			case '-':
				$value = $left[1] - $right[1];
				break;
			case '%':
				$value = $left[1] % $right[1];
				break;
			case '/':
				if ($right[1] == 0)
				{
					$this->throwError('parse error: divide by zero');
				}

				$value = $left[1] / $right[1];
				break;
			case '<':
				return $this->toBool($left[1] < $right[1]);
			case '>':
				return $this->toBool($left[1] > $right[1]);
			case '>=':
				return $this->toBool($left[1] >= $right[1]);
			case '=<':
				return $this->toBool($left[1] <= $right[1]);
			default:
				$this->throwError('parse error: unknown number operator: ' . $op);
		}

		return array("number", $value, $unit);
	}

	/**
	 * Make output block
	 *
	 * @param   type  $type       X
	 * @param   type  $selectors  X
	 *
	 * @return  stdclass
	 */
	protected function makeOutputBlock($type, $selectors = null)
	{
		$b = new stdclass;
		$b->lines = array();
		$b->children = array();
		$b->selectors = $selectors;
		$b->type = $type;
		$b->parent = $this->scope;

		return $b;
	}

	/**
	 * The state of execution
	 *
	 * @param   type  $block  X
	 *
	 * @return  stdclass
	 */
	protected function pushEnv($block = null)
	{
		$e = new stdclass;
		$e->parent = $this->env;
		$e->store = array();
		$e->block = $block;

		$this->env = $e;

		return $e;
	}

	/**
	 * Pop something off the stack
	 *
	 * @return  type
	 */
	protected function popEnv()
	{
		$old = $this->env;
		$this->env = $this->env->parent;

		return $old;
	}

	/**
	 * Set something in the current env
	 *
	 * @param   type  $name   X
	 * @param   type  $value  X
	 *
	 * @return  void
	 */
	protected function set($name, $value)
	{
		$this->env->store[$name] = $value;
	}

	/**
	 * Get the highest occurrence entry for a name
	 *
	 * @param   type  $name     X
	 * @param   type  $default  X
	 *
	 * @return  type
	 */
	protected function get($name, $default = null)
	{
		$current = $this->env;

		$isArguments = $name == $this->vPrefix . 'arguments';

		while ($current)
		{
			if ($isArguments && isset($current->arguments))
			{
				return array('list', ' ', $current->arguments);
			}

			if (isset($current->store[$name]))
			{
				return $current->store[$name];
			}
			else
			{
				$current = isset($current->storeParent) ?
					$current->storeParent : $current->parent;
			}
		}

		return $default;
	}

	/**
	 * Inject array of unparsed strings into environment as variables
	 *
	 * @param   type  $args  X
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	protected function injectVariables($args)
	{
		$this->pushEnv();
		/** FOF -- BEGIN CHANGE * */
		$parser = new FOFLessParser($this, __METHOD__);
		/** FOF -- END CHANGE * */
		foreach ($args as $name => $strValue)
		{
			if ($name{0} != '@')
			{
				$name = '@' . $name;
			}

			$parser->count = 0;
			$parser->buffer = (string) $strValue;

			if (!$parser->propertyValue($value))
			{
				throw new Exception("failed to parse passed in variable $name: $strValue");
			}

			$this->set($name, $value);
		}
	}

	/**
	 * Initialize any static state, can initialize parser for a file
	 *
	 * @param   type  $fname  X
	 */
	public function __construct($fname = null)
	{
		if ($fname !== null)
		{
			// Used for deprecated parse method
			$this->_parseFile = $fname;
		}
	}

	/**
	 * Compile
	 *
	 * @param   type  $string  X
	 * @param   type  $name    X
	 *
	 * @return  type
	 */
	public function compile($string, $name = null)
	{
		$locale = setlocale(LC_NUMERIC, 0);
		setlocale(LC_NUMERIC, "C");

		$this->parser = $this->makeParser($name);
		$root = $this->parser->parse($string);

		$this->env = null;
		$this->scope = null;

		$this->formatter = $this->newFormatter();

		if (!empty($this->registeredVars))
		{
			$this->injectVariables($this->registeredVars);
		}

		// Used for error messages
		$this->sourceParser = $this->parser;
		$this->compileBlock($root);

		ob_start();
		$this->formatter->block($this->scope);
		$out = ob_get_clean();
		setlocale(LC_NUMERIC, $locale);

		return $out;
	}

	/**
	 * Compile file
	 *
	 * @param   type  $fname     X
	 * @param   type  $outFname  X
	 *
	 * @return  type
	 *
	 * @throws  Exception
	 */
	public function compileFile($fname, $outFname = null)
	{
		if (!is_readable($fname))
		{
			throw new Exception('load error: failed to find ' . $fname);
		}

		$pi = pathinfo($fname);

		$oldImport = $this->importDir;

		$this->importDir = (array) $this->importDir;
		$this->importDir[] = $pi['dirname'] . '/';

		$this->allParsedFiles = array();
		$this->addParsedFile($fname);

		$out = $this->compile(file_get_contents($fname), $fname);

		$this->importDir = $oldImport;

		if ($outFname !== null)
		{
			/** FOF - BEGIN CHANGE * */
			return FOFPlatform::getInstance()->getIntegrationObject('filesystem')->fileWrite($outFname, $out);
			/** FOF - END CHANGE * */
		}

		return $out;
	}

	/**
	 * Compile only if changed input has changed or output doesn't exist
	 *
	 * @param   type  $in   X
	 * @param   type  $out  X
	 *
	 * @return  boolean
	 */
	public function checkedCompile($in, $out)
	{
		if (!is_file($out) || filemtime($in) > filemtime($out))
		{
			$this->compileFile($in, $out);

			return true;
		}

		return false;
	}

	/**
	 * Execute lessphp on a .less file or a lessphp cache structure
	 *
	 * The lessphp cache structure contains information about a specific
	 * less file having been parsed. It can be used as a hint for future
	 * calls to determine whether or not a rebuild is required.
	 *
	 * The cache structure contains two important keys that may be used
	 * externally:
	 *
	 * compiled: The final compiled CSS
	 * updated: The time (in seconds) the CSS was last compiled
	 *
	 * The cache structure is a plain-ol' PHP associative array and can
	 * be serialized and unserialized without a hitch.
	 *
	 * @param   mixed  $in     Input
	 * @param   bool   $force  Force rebuild?
	 *
	 * @return  array  lessphp cache structure
	 */
	public function cachedCompile($in, $force = false)
	{
		// Assume no root
		$root = null;

		if (is_string($in))
		{
			$root = $in;
		}
		elseif (is_array($in) and isset($in['root']))
		{
			if ($force or !isset($in['files']))
			{
				/**
				 * If we are forcing a recompile or if for some reason the
				 * structure does not contain any file information we should
				 * specify the root to trigger a rebuild.
				 */
				$root = $in['root'];
			}
			elseif (isset($in['files']) and is_array($in['files']))
			{
				foreach ($in['files'] as $fname => $ftime)
				{
					if (!file_exists($fname) or filemtime($fname) > $ftime)
					{
						/**
						 * One of the files we knew about previously has changed
						 * so we should look at our incoming root again.
						 */
						$root = $in['root'];
						break;
					}
				}
			}
		}
		else
		{
			/**
			 * TODO: Throw an exception? We got neither a string nor something
			 * that looks like a compatible lessphp cache structure.
			 */
			return null;
		}

		if ($root !== null)
		{
			// If we have a root value which means we should rebuild.
			$out = array();
			$out['root'] = $root;
			$out['compiled'] = $this->compileFile($root);
			$out['files'] = $this->allParsedFiles();
			$out['updated'] = time();

			return $out;
		}
		else
		{
			// No changes, pass back the structure
			// we were given initially.
			return $in;
		}
	}

	//
	// This is deprecated
	/**
	 * Parse and compile buffer
	 *
	 * @param   null  $str               X
	 * @param   type  $initialVariables  X
	 *
	 * @return  type
	 *
	 * @throws  Exception
	 *
	 * @deprecated  2.0
	 */
	public function parse($str = null, $initialVariables = null)
	{
		if (is_array($str))
		{
			$initialVariables = $str;
			$str = null;
		}

		$oldVars = $this->registeredVars;

		if ($initialVariables !== null)
		{
			$this->setVariables($initialVariables);
		}

		if ($str == null)
		{
			if (empty($this->_parseFile))
			{
				throw new exception("nothing to parse");
			}

			$out = $this->compileFile($this->_parseFile);
		}
		else
		{
			$out = $this->compile($str);
		}

		$this->registeredVars = $oldVars;

		return $out;
	}

	/**
	 * Make parser
	 *
	 * @param   type  $name  X
	 *
	 * @return  FOFLessParser
	 */
	protected function makeParser($name)
	{
		/** FOF -- BEGIN CHANGE * */
		$parser = new FOFLessParser($this, $name);
		/** FOF -- END CHANGE * */
		$parser->writeComments = $this->preserveComments;

		return $parser;
	}

	/**
	 * Set Formatter
	 *
	 * @param   type  $name  X
	 *
	 * @return  void
	 */
	public function setFormatter($name)
	{
		$this->formatterName = $name;
	}

	/**
	 * New formatter
	 *
	 * @return  FOFLessFormatterLessjs
	 */
	protected function newFormatter()
	{
		/** FOF -- BEGIN CHANGE * */
		$className = "FOFLessFormatterLessjs";
		/** FOF -- END CHANGE * */
		if (!empty($this->formatterName))
		{
			if (!is_string($this->formatterName))
				return $this->formatterName;
			/** FOF -- BEGIN CHANGE * */
			$className = "FOFLessFormatter" . ucfirst($this->formatterName);
			/** FOF -- END CHANGE * */
		}

		return new $className;
	}

	/**
	 * Set preserve comments
	 *
	 * @param   type  $preserve  X
	 *
	 * @return  void
	 */
	public function setPreserveComments($preserve)
	{
		$this->preserveComments = $preserve;
	}

	/**
	 * Register function
	 *
	 * @param   type  $name  X
	 * @param   type  $func  X
	 *
	 * @return  void
	 */
	public function registerFunction($name, $func)
	{
		$this->libFunctions[$name] = $func;
	}

	/**
	 * Unregister function
	 *
	 * @param   type  $name  X
	 *
	 * @return  void
	 */
	public function unregisterFunction($name)
	{
		unset($this->libFunctions[$name]);
	}

	/**
	 * Set variables
	 *
	 * @param   type  $variables  X
	 *
	 * @return  void
	 */
	public function setVariables($variables)
	{
		$this->registeredVars = array_merge($this->registeredVars, $variables);
	}

	/**
	 * Unset variable
	 *
	 * @param   type  $name  X
	 *
	 * @return  void
	 */
	public function unsetVariable($name)
	{
		unset($this->registeredVars[$name]);
	}

	/**
	 * Set import dir
	 *
	 * @param   type  $dirs  X
	 *
	 * @return  void
	 */
	public function setImportDir($dirs)
	{
		$this->importDir = (array) $dirs;
	}

	/**
	 * Add import dir
	 *
	 * @param   type  $dir  X
	 *
	 * @return  void
	 */
	public function addImportDir($dir)
	{
		$this->importDir = (array) $this->importDir;
		$this->importDir[] = $dir;
	}

	/**
	 * All parsed files
	 *
	 * @return  type
	 */
	public function allParsedFiles()
	{
		return $this->allParsedFiles;
	}

	/**
	 * Add parsed file
	 *
	 * @param   type  $file  X
	 *
	 * @return  void
	 */
	protected function addParsedFile($file)
	{
		$this->allParsedFiles[realpath($file)] = filemtime($file);
	}

	/**
	 * Uses the current value of $this->count to show line and line number
	 *
	 * @param   type  $msg  X
	 *
	 * @return  void
	 */
	protected function throwError($msg = null)
	{
		if ($this->sourceLoc >= 0)
		{
			$this->sourceParser->throwError($msg, $this->sourceLoc);
		}

		throw new exception($msg);
	}

	/**
	 * Compile file $in to file $out if $in is newer than $out
	 * Returns true when it compiles, false otherwise
	 *
	 * @param   type  $in    X
	 * @param   type  $out   X
	 * @param   self  $less  X
	 *
	 * @return  type
	 */
	public static function ccompile($in, $out, $less = null)
	{
		if ($less === null)
		{
			$less = new self;
		}

		return $less->checkedCompile($in, $out);
	}

	/**
	 * Compile execute
	 *
	 * @param   type  $in     X
	 * @param   type  $force  X
	 * @param   self  $less   X
	 *
	 * @return  type
	 */
	public static function cexecute($in, $force = false, $less = null)
	{
		if ($less === null)
		{
			$less = new self;
		}

		return $less->cachedCompile($in, $force);
	}

	protected static $cssColors = array(
		'aliceblue'				 => '240,248,255',
		'antiquewhite'			 => '250,235,215',
		'aqua'					 => '0,255,255',
		'aquamarine'			 => '127,255,212',
		'azure'					 => '240,255,255',
		'beige'					 => '245,245,220',
		'bisque'				 => '255,228,196',
		'black'					 => '0,0,0',
		'blanchedalmond'		 => '255,235,205',
		'blue'					 => '0,0,255',
		'blueviolet'			 => '138,43,226',
		'brown'					 => '165,42,42',
		'burlywood'				 => '222,184,135',
		'cadetblue'				 => '95,158,160',
		'chartreuse'			 => '127,255,0',
		'chocolate'				 => '210,105,30',
		'coral'					 => '255,127,80',
		'cornflowerblue'		 => '100,149,237',
		'cornsilk'				 => '255,248,220',
		'crimson'				 => '220,20,60',
		'cyan'					 => '0,255,255',
		'darkblue'				 => '0,0,139',
		'darkcyan'				 => '0,139,139',
		'darkgoldenrod'			 => '184,134,11',
		'darkgray'				 => '169,169,169',
		'darkgreen'				 => '0,100,0',
		'darkgrey'				 => '169,169,169',
		'darkkhaki'				 => '189,183,107',
		'darkmagenta'			 => '139,0,139',
		'darkolivegreen'		 => '85,107,47',
		'darkorange'			 => '255,140,0',
		'darkorchid'			 => '153,50,204',
		'darkred'				 => '139,0,0',
		'darksalmon'			 => '233,150,122',
		'darkseagreen'			 => '143,188,143',
		'darkslateblue'			 => '72,61,139',
		'darkslategray'			 => '47,79,79',
		'darkslategrey'			 => '47,79,79',
		'darkturquoise'			 => '0,206,209',
		'darkviolet'			 => '148,0,211',
		'deeppink'				 => '255,20,147',
		'deepskyblue'			 => '0,191,255',
		'dimgray'				 => '105,105,105',
		'dimgrey'				 => '105,105,105',
		'dodgerblue'			 => '30,144,255',
		'firebrick'				 => '178,34,34',
		'floralwhite'			 => '255,250,240',
		'forestgreen'			 => '34,139,34',
		'fuchsia'				 => '255,0,255',
		'gainsboro'				 => '220,220,220',
		'ghostwhite'			 => '248,248,255',
		'gold'					 => '255,215,0',
		'goldenrod'				 => '218,165,32',
		'gray'					 => '128,128,128',
		'green'					 => '0,128,0',
		'greenyellow'			 => '173,255,47',
		'grey'					 => '128,128,128',
		'honeydew'				 => '240,255,240',
		'hotpink'				 => '255,105,180',
		'indianred'				 => '205,92,92',
		'indigo'				 => '75,0,130',
		'ivory'					 => '255,255,240',
		'khaki'					 => '240,230,140',
		'lavender'				 => '230,230,250',
		'lavenderblush'			 => '255,240,245',
		'lawngreen'				 => '124,252,0',
		'lemonchiffon'			 => '255,250,205',
		'lightblue'				 => '173,216,230',
		'lightcoral'			 => '240,128,128',
		'lightcyan'				 => '224,255,255',
		'lightgoldenrodyellow'	 => '250,250,210',
		'lightgray'				 => '211,211,211',
		'lightgreen'			 => '144,238,144',
		'lightgrey'				 => '211,211,211',
		'lightpink'				 => '255,182,193',
		'lightsalmon'			 => '255,160,122',
		'lightseagreen'			 => '32,178,170',
		'lightskyblue'			 => '135,206,250',
		'lightslategray'		 => '119,136,153',
		'lightslategrey'		 => '119,136,153',
		'lightsteelblue'		 => '176,196,222',
		'lightyellow'			 => '255,255,224',
		'lime'					 => '0,255,0',
		'limegreen'				 => '50,205,50',
		'linen'					 => '250,240,230',
		'magenta'				 => '255,0,255',
		'maroon'				 => '128,0,0',
		'mediumaquamarine'		 => '102,205,170',
		'mediumblue'			 => '0,0,205',
		'mediumorchid'			 => '186,85,211',
		'mediumpurple'			 => '147,112,219',
		'mediumseagreen'		 => '60,179,113',
		'mediumslateblue'		 => '123,104,238',
		'mediumspringgreen'		 => '0,250,154',
		'mediumturquoise'		 => '72,209,204',
		'mediumvioletred'		 => '199,21,133',
		'midnightblue'			 => '25,25,112',
		'mintcream'				 => '245,255,250',
		'mistyrose'				 => '255,228,225',
		'moccasin'				 => '255,228,181',
		'navajowhite'			 => '255,222,173',
		'navy'					 => '0,0,128',
		'oldlace'				 => '253,245,230',
		'olive'					 => '128,128,0',
		'olivedrab'				 => '107,142,35',
		'orange'				 => '255,165,0',
		'orangered'				 => '255,69,0',
		'orchid'				 => '218,112,214',
		'palegoldenrod'			 => '238,232,170',
		'palegreen'				 => '152,251,152',
		'paleturquoise'			 => '175,238,238',
		'palevioletred'			 => '219,112,147',
		'papayawhip'			 => '255,239,213',
		'peachpuff'				 => '255,218,185',
		'peru'					 => '205,133,63',
		'pink'					 => '255,192,203',
		'plum'					 => '221,160,221',
		'powderblue'			 => '176,224,230',
		'purple'				 => '128,0,128',
		'red'					 => '255,0,0',
		'rosybrown'				 => '188,143,143',
		'royalblue'				 => '65,105,225',
		'saddlebrown'			 => '139,69,19',
		'salmon'				 => '250,128,114',
		'sandybrown'			 => '244,164,96',
		'seagreen'				 => '46,139,87',
		'seashell'				 => '255,245,238',
		'sienna'				 => '160,82,45',
		'silver'				 => '192,192,192',
		'skyblue'				 => '135,206,235',
		'slateblue'				 => '106,90,205',
		'slategray'				 => '112,128,144',
		'slategrey'				 => '112,128,144',
		'snow'					 => '255,250,250',
		'springgreen'			 => '0,255,127',
		'steelblue'				 => '70,130,180',
		'tan'					 => '210,180,140',
		'teal'					 => '0,128,128',
		'thistle'				 => '216,191,216',
		'tomato'				 => '255,99,71',
		'transparent'			 => '0,0,0,0',
		'turquoise'				 => '64,224,208',
		'violet'				 => '238,130,238',
		'wheat'					 => '245,222,179',
		'white'					 => '255,255,255',
		'whitesmoke'			 => '245,245,245',
		'yellow'				 => '255,255,0',
		'yellowgreen'			 => '154,205,50'
	);
}
