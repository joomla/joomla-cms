<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\Render;

defined('_JEXEC') || die;

use FOF30\Container\Container;
use FOF30\Toolbar\Toolbar;
use JHtmlSidebar;
use Joomla\CMS\Factory as JoomlaFactory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Toolbar\Toolbar as JoomlaToolbar;

/**
 * Renderer class for use with Joomla! 3.x and 4.x
 *
 * Renderer options
 *
 * wrapper_id              The ID of the wrapper DIV. Default: akeeba-renderjoomla
 * linkbar_style           Style for linkbars: joomla3|classic. Default: joomla3
 * remove_wrapper_classes  Comma-separated list of classes to REMOVE from the container
 * add_wrapper_classes     Comma-separated list of classes to ADD to the container
 *
 * @package FOF30\Render
 * @since   3.6.0
 */
class Joomla extends RenderBase implements RenderInterface
{
	/** @inheritDoc */
	public function __construct(Container $container)
	{
		$this->priority = 30;
		$this->enabled  = true;

		parent::__construct($container);
	}

	/**
	 * Echoes any HTML to show before the view template
	 *
	 * @param   string  $view  The current view
	 * @param   string  $task  The current task
	 *
	 * @return  void
	 */
	function preRender(string $view, string $task): void
	{
		$input    = $this->container->input;
		$platform = $this->container->platform;

		$format = $input->getCmd('format', 'html');

		if (empty($format))
		{
			$format = 'html';
		}

		if ($format != 'html')
		{
			return;
		}

		if ($platform->isCli())
		{
			return;
		}

		HTMLHelper::_('behavior.core');
		HTMLHelper::_('jquery.framework', true);

		// Wrap output in various classes
		$versionParts = explode('.', JVERSION);
		$minorVersion = $versionParts[0] . $versionParts[1];
		$majorVersion = $versionParts[0];

		$classes = [];

		if ($platform->isBackend())
		{
			$area            = $platform->isBackend() ? 'admin' : 'site';
			$option          = $input->getCmd('option', '');
			$viewForCssClass = $input->getCmd('view', '');
			$layout          = $input->getCmd('layout', '');
			$taskForCssClass = $input->getCmd('task', '');

			$classes = [
				'joomla-version-' . $majorVersion,
				'joomla-version-' . $minorVersion,
				$area,
				$option,
				'view-' . $view,
				'view-' . $viewForCssClass,
				'layout-' . $layout,
				'task-' . $task,
				'task-' . $taskForCssClass,
				// We have a floating sidebar, they said. It looks great, they said. They must've been blind, I say!
				'j-toggle-main',
				'j-toggle-transition',
				'row-fluid',
			];

			$classes = array_unique($classes);
		}

		$this->openPageWrapper($classes);

		// Render the submenu and toolbar
		if ($input->getBool('render_toolbar', true))
		{
			$this->renderButtons($view, $task);
			$this->renderLinkbar($view, $task);
		}

		parent::preRender($view, $task);
	}

	/**
	 * Echoes any HTML to show after the view template
	 *
	 * @param   string  $view  The current view
	 * @param   string  $task  The current task
	 *
	 * @return  void
	 */
	function postRender(string $view, string $task): void
	{
		$input    = $this->container->input;
		$platform = $this->container->platform;

		$format = $input->getCmd('format', 'html');

		if (empty($format))
		{
			$format = 'html';
		}

		if ($format != 'html')
		{
			return;
		}

		// Closing tag only if we're not in CLI
		if ($platform->isCli())
		{
			return;
		}

		// Closes akeeba-renderjoomla div
		$this->closePageWrapper();
	}

	/**
	 * Renders the submenu (link bar)
	 *
	 * @param   string  $view  The active view name
	 * @param   string  $task  The current task
	 *
	 * @return  void
	 */
	protected function renderLinkbar(string $view, string $task): void
	{
		$style = $this->getOption('linkbar_style', 'joomla');

		switch ($style)
		{
			case 'joomla':
				$this->renderLinkbar_joomla($view, $task);
				break;

			case 'classic':
			default:
				$this->renderLinkbar_classic($view, $task);
				break;
		}
	}

	/**
	 * Renders the submenu (link bar) in F0F's classic style, using a Bootstrapped
	 * tab bar.
	 *
	 * @param   string  $view  The active view name
	 * @param   string  $task  The current task
	 *
	 * @return  void
	 */
	protected function renderLinkbar_classic(string $view, string $task): void
	{
		$platform = $this->container->platform;

		if ($platform->isCli())
		{
			return;
		}

		$isJoomla4 = version_compare(JVERSION, '3.99999.99999', 'gt');
		$isJoomla3 = !$isJoomla4 && version_compare(JVERSION, '3.0.0', 'ge');

		// Do not render a submenu unless we are in the the admin area
		$toolbar               = $this->container->toolbar;
		$renderFrontendSubmenu = $toolbar->getRenderFrontendSubmenu();

		if (!$platform->isBackend() && !$renderFrontendSubmenu)
		{
			return;
		}

		$links = $toolbar->getLinks();

		if (!empty($links))
		{
			echo "<ul class=\"nav nav-tabs\">\n";

			foreach ($links as $link)
			{
				$dropdown = false;

				if (array_key_exists('dropdown', $link))
				{
					$dropdown = $link['dropdown'];
				}

				if ($dropdown)
				{
					echo "<li";
					$class = 'nav-item dropdown';

					if ($link['active'])
					{
						$class .= ' active';
					}

					echo ' class="' . $class . '">';

					echo '<a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#">';

					if ($link['icon'])
					{
						echo "<i class=\"icon icon-" . $link['icon'] . "\"></i>";
					}

					echo $link['name'];
					echo '<b class="caret"></b>';
					echo '</a>';

					echo "\n<ul class=\"dropdown-menu\">";

					foreach ($link['items'] as $item)
					{
						echo "<li class=\"dropdown-item";

						if ($item['active'])
						{
							echo ' active';
						}

						echo "\">";

						if ($item['icon'])
						{
							echo "<i class=\"icon icon-" . $item['icon'] . "\"></i>";
						}

						if ($item['link'])
						{
							echo "<a href=\"" . $item['link'] . "\">" . $item['name'] . "</a>";
						}
						else
						{
							echo $item['name'];
						}

						echo "</li>";
					}

					echo "</ul>\n";
				}
				else
				{
					echo "<li class=\"nav-item";

					if ($link['active'] && $isJoomla3)
					{
						echo ' active"';
					}

					echo "\">";

					if ($link['icon'])
					{
						echo "<span class=\"icon icon-" . $link['icon'] . "\"></span>";
					}

					if ($isJoomla3)
					{
						if ($link['link'])
						{
							echo "<a href=\"" . $link['link'] . "\">" . $link['name'] . "</a>";
						}
						else
						{
							echo $link['name'];
						}
					}
					else
					{
						$class = $link['active'] ? 'active' : '';

						$href = $link['link'] ? $link['link'] : '#';

						echo "<a href=\"$href\" class=\"nav-link $class\">{$link['name']}</a>";
					}
				}

				echo "</li>\n";
			}

			echo "</ul>\n";
		}
	}

	/**
	 * Renders the submenu (link bar) using Joomla!'s style. On Joomla! 2.5 this
	 * is a list of bar separated links, on Joomla! 3 it's a sidebar at the
	 * left-hand side of the page.
	 *
	 * @param   string  $view  The active view name
	 * @param   string  $task  The current task
	 *
	 * @return  void
	 */
	protected function renderLinkbar_joomla(string $view, string $task): void
	{
		$platform = $this->container->platform;

		// On command line don't do anything
		if ($platform->isCli())
		{
			return;
		}

		// Do not render a submenu unless we are in the the admin area
		$toolbar               = $this->container->toolbar;
		$renderFrontendSubmenu = $toolbar->getRenderFrontendSubmenu();

		if (!$platform->isBackend() && !$renderFrontendSubmenu)
		{
			return;
		}

		$this->renderLinkbarItems($toolbar);
	}

	/**
	 * Render the linkbar
	 *
	 * @param   Toolbar  $toolbar  An FOF toolbar object
	 *
	 * @return  void
	 */
	protected function renderLinkbarItems(Toolbar $toolbar): void
	{
		$links = $toolbar->getLinks();

		if (!empty($links))
		{
			foreach ($links as $link)
			{
				JHtmlSidebar::addEntry($link['name'], $link['link'], $link['active']);

				$dropdown = false;

				if (array_key_exists('dropdown', $link))
				{
					$dropdown = $link['dropdown'];
				}

				if ($dropdown)
				{
					foreach ($link['items'] as $item)
					{
						JHtmlSidebar::addEntry('– ' . $item['name'], $item['link'], $item['active']);
					}
				}
			}
		}
	}

	/**
	 * Renders the toolbar buttons
	 *
	 * @param   string  $view  The active view name
	 * @param   string  $task  The current task
	 *
	 * @return  void
	 */
	protected function renderButtons(string $view, string $task): void
	{
		$platform = $this->container->platform;

		if ($platform->isCli())
		{
			return;
		}

		// Do not render buttons unless we are in the the frontend area and we are asked to do so
		$toolbar               = $this->container->toolbar;
		$renderFrontendButtons = $toolbar->getRenderFrontendButtons();

		// Load main backend language, in order to display toolbar strings
		// (JTOOLBAR_BACK, JTOOLBAR_PUBLISH etc etc)
		$platform->loadTranslations('joomla');

		if ($platform->isBackend() || !$renderFrontendButtons)
		{
			return;
		}

		$bar   = JoomlaToolbar::getInstance('toolbar');
		$items = $bar->getItems();

		$substitutions = [
			'icon-32-new'       => 'icon-plus',
			'icon-32-publish'   => 'icon-eye-open',
			'icon-32-unpublish' => 'icon-eye-close',
			'icon-32-delete'    => 'icon-trash',
			'icon-32-edit'      => 'icon-edit',
			'icon-32-copy'      => 'icon-th-large',
			'icon-32-cancel'    => 'icon-remove',
			'icon-32-back'      => 'icon-circle-arrow-left',
			'icon-32-apply'     => 'icon-ok',
			'icon-32-save'      => 'icon-hdd',
			'icon-32-save-new'  => 'icon-repeat',
		];

		if (isset(JoomlaFactory::getApplication()->JComponentTitle))
		{
			$title = JoomlaFactory::getApplication()->JComponentTitle;
		}
		else
		{
			$title = '';
		}

		$html    = [];
		$actions = [];

		// We have to use the same id we're using inside other renderers
		$html[] = '<div class="well" id="FOFHeaderContainer">';
		$html[] = '<div class="titleContainer">' . $title . '</div>';
		$html[] = '<div class="buttonsContainer">';

		foreach ($items as $node)
		{
			$type   = $node[0];
			$button = $bar->loadButtonType($type);

			if ($button !== false)
			{
				$action    = call_user_func_array([&$button, 'fetchButton'], $node);
				$action    = str_replace('class="toolbar"', 'class="toolbar btn"', $action);
				$action    = str_replace('<span ', '<i ', $action);
				$action    = str_replace('</span>', '</i>', $action);
				$action    = str_replace(array_keys($substitutions), array_values($substitutions), $action);
				$actions[] = $action;
			}
		}

		$html   = array_merge($html, $actions);
		$html[] = '</div>';
		$html[] = '</div>';

		echo implode("\n", $html);
	}

	/**
	 * Opens the wrapper DIV element. Our component's output will be inside this wrapper.
	 *
	 * @param   array  $classes  An array of additional CSS classes to add to the outer page wrapper element.
	 *
	 * @return  void
	 */
	protected function openPageWrapper(array $classes): void
	{
		$this->setOption('wrapper_id', $this->getOption('wrapper_id', 'akeeba-renderjoomla'));

		$classes[] = 'akeeba-renderer-joomla';

		parent::openPageWrapper($classes);
	}

}
