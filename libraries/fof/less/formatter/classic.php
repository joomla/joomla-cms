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
 * This class is taken verbatim from:
 *
 * lessphp v0.3.9
 * http://leafo.net/lessphp
 *
 * LESS css compiler, adapted from http://lesscss.org
 *
 * Copyright 2012, Leaf Corcoran <leafot@gmail.com>
 * Licensed under MIT or GPLv3, see LICENSE
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFLessFormatterClassic
{
	public $indentChar			 = "  ";

	public $break				 = "\n";

	public $open				 = " {";

	public $close				 = "}";

	public $selectorSeparator	 = ", ";

	public $assignSeparator	 = ":";

	public $openSingle			 = " { ";

	public $closeSingle		 = " }";

	public $disableSingle		 = false;

	public $breakSelectors		 = false;

	public $compressColors		 = false;

	/**
	 * Public constructor
	 */
	public function __construct()
	{
		$this->indentLevel = 0;
	}

	/**
	 * Indent a string by $n positions
	 *
	 * @param   integer  $n  How many positions to indent
	 *
	 * @return  string  The indented string
	 */
	public function indentStr($n = 0)
	{
		return str_repeat($this->indentChar, max($this->indentLevel + $n, 0));
	}

	/**
	 * Return the code for a property
	 *
	 * @param   string  $name   The name of the porperty
	 * @param   string  $value  The value of the porperty
	 *
	 * @return  string  The CSS code
	 */
	public function property($name, $value)
	{
		return $name . $this->assignSeparator . $value . ";";
	}

	/**
	 * Is a block empty?
	 *
	 * @param   stdClass  $block  The block to check
	 *
	 * @return  boolean  True if the block has no lines or children
	 */
	protected function isEmpty($block)
	{
		if (empty($block->lines))
		{
			foreach ($block->children as $child)
			{
				if (!$this->isEmpty($child))
				{
					return false;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Output a CSS block
	 *
	 * @param   stdClass  $block  The block definition to output
	 *
	 * @return  void
	 */
	public function block($block)
	{
		if ($this->isEmpty($block))
		{
			return;
		}

		$inner	 = $pre	 = $this->indentStr();

		$isSingle = !$this->disableSingle &&
			is_null($block->type) && count($block->lines) == 1;

		if (!empty($block->selectors))
		{
			$this->indentLevel++;

			if ($this->breakSelectors)
			{
				$selectorSeparator = $this->selectorSeparator . $this->break . $pre;
			}
			else
			{
				$selectorSeparator = $this->selectorSeparator;
			}

			echo $pre .
			implode($selectorSeparator, $block->selectors);

			if ($isSingle)
			{
				echo $this->openSingle;
				$inner = "";
			}
			else
			{
				echo $this->open . $this->break;
				$inner = $this->indentStr();
			}
		}

		if (!empty($block->lines))
		{
			$glue = $this->break . $inner;
			echo $inner . implode($glue, $block->lines);

			if (!$isSingle && !empty($block->children))
			{
				echo $this->break;
			}
		}

		foreach ($block->children as $child)
		{
			$this->block($child);
		}

		if (!empty($block->selectors))
		{
			if (!$isSingle && empty($block->children))
			{
				echo $this->break;
			}

			if ($isSingle)
			{
				echo $this->closeSingle . $this->break;
			}
			else
			{
				echo $pre . $this->close . $this->break;
			}

			$this->indentLevel--;
		}
	}
}
