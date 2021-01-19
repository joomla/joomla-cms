<?php
/**
 * @package   FOF
 * @copyright Copyright (c)2010-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 2, or later
 */

namespace FOF30\View\Engine;

defined('_JEXEC') || die;

use FOF30\Utils\Buffer;
use FOF30\View\Compiler\CompilerInterface;
use FOF30\View\Exception\PossiblySuhosin;
use JLoader;
use Joomla\CMS\Filesystem\File;

/**
 * View engine for compiling PHP template files.
 */
abstract class CompilingEngine extends AbstractEngine implements EngineInterface
{
	/** @var  CompilerInterface  The compiler used by this engine */
	protected $compiler = null;

	/**
	 * Get the 3ναlυa+3d contents of the view template. (I use leetspeak here because of bad quality hosts with broken
	 * scanners)
	 *
	 * @param   string  $path         The path to the view template
	 * @param   array   $forceParams  Any additional information to pass to the view template engine
	 *
	 * @return  array  Content evaluation information
	 */
	public function get($path, array $forceParams = [])
	{
		// If it's cached return the path to the cached file's path
		if ($this->isCached($path))
		{
			return [
				'type'    => 'path',
				'content' => $this->getCachePath($path),
			];
		}

		/**
		 * Compile and cache the file. We also add the file path in a comment at the top of the file so phpStorm can
		 * debug it.
		 *
		 * @see https://blog.jetbrains.com/phpstorm/2019/02/phpstorm-2019-1-eap-191-5849-26/
		 * @see https://laravel-news.com/laravel-5-8-blade-template-file-path
		 */
		$content   = "<?php /* $path */ ?>\n";
		$content   .= $this->compile($path, $forceParams);
		$cachePath = $this->putToCache($path, $content);
		$isPHPFile = substr($path, -4) == '.php';

		// If we could cache it, return the cached file's path
		if ($cachePath !== false)
		{
			// Bust the opcode cache for .php files
			if ($isPHPFile)
			{
				$this->bustOpCache($path);
			}

			return [
				'type'    => 'path',
				'content' => $cachePath,
			];
		}

		// We could not write to the cache. Hm, can I use a stream wrapper?
		$canUseStreams = Buffer::canRegisterWrapper();

		if ($canUseStreams)
		{
			$id         = $this->getIdentifier($path);
			$streamPath = 'fof://' . $this->view->getContainer()->componentName . '/compiled_templates/' . $id . '.php';
			file_put_contents($streamPath, $content);

			// Bust the opcode cache for .php files
			if ($isPHPFile)
			{
				$this->bustOpCache($path);
			}

			return [
				'type'    => 'path',
				'content' => $streamPath,
			];
		}

		// I couldn't use a stream wrapper. I have to give up.
		throw new PossiblySuhosin;
	}

	/**
	 * Returns the path where I can find a precompiled version of the unprocessed view template which lives in $path
	 *
	 * @param   string  $path  The path to the uncompiled view template
	 *
	 * @return  bool|string  False if the view template is outside the component's front- or backend.
	 *
	 * @since   3.3.1
	 */
	public function getPrecompiledPath($path)
	{
		// Normalize the path to the file
		$path = realpath($path);

		if ($path === false)
		{
			// The file doesn't exist
			return false;
		}

		// Is this path under the component's front- or backend?
		$frontendPath = realpath($this->view->getContainer()->frontEndPath);
		$backendPath  = realpath($this->view->getContainer()->backEndPath);

		$backPos  = strpos($path, $backendPath);
		$frontPos = strpos($path, $frontendPath);

		if (($backPos !== 0) && ($frontPos !== 0))
		{
			// This is not a view template shipped with the component, i.e. it can't be precompiled
			return false;
		}

		// Eliminate the component path from $path to get the relative path to the file
		$componentPath = $frontendPath;

		if ($backPos === 0)
		{
			$componentPath = $backendPath;
		}

		$relativePath = ltrim(substr($path, strlen($componentPath)), '\\/');

		// Break down the relative path to its parts
		$relativePath = str_replace('\\', '/', $relativePath);
		$pathParts    = explode('/', $relativePath);

		// Remove the prefix
		$prefix = array_shift($pathParts);

		// If it's a legacy view, View, Views, or views prefix remove the 'tmpl' part
		if ($prefix != 'ViewTemplates')
		{
			unset($pathParts[1]);
		}

		// Get the last part and process the extension
		$viewFile            = array_pop($pathParts);
		$extensionWithoutDot = $this->compiler->getFileExtension();
		$pathParts[]         = substr($viewFile, 0, -strlen($extensionWithoutDot)) . 'php';

		$precompiledRelativePath = implode(DIRECTORY_SEPARATOR, $pathParts);

		return $componentPath . DIRECTORY_SEPARATOR . 'PrecompiledTemplates' . DIRECTORY_SEPARATOR . $precompiledRelativePath;
	}

	/**
	 * A method to compile the raw view template into valid PHP
	 *
	 * @param   string  $path         The path to the view template
	 * @param   array   $forceParams  Any additional information to pass to the view template compiler
	 *
	 * @return  string  The template compiled to executable PHP
	 */
	protected function compile($path, array $forceParams = [])
	{
		return $this->compiler->compile($path, $forceParams);
	}

	protected function getIdentifier($path)
	{
		if (function_exists('sha1'))
		{
			return sha1($path);
		}

		return md5($path);
	}

	protected function getCachePath($path)
	{
		$id = $this->getIdentifier($path);

		return JPATH_CACHE . '/' . $this->view->getContainer()->componentName . '/compiled_templates/' . $id . '.php';
	}

	protected function isCached($path)
	{
		if (!$this->compiler->isCacheable())
		{
			return false;
		}

		$cachePath = $this->getCachePath($path);

		if (!file_exists($cachePath))
		{
			return false;
		}

		$cacheTime = filemtime($cachePath);
		$fileTime  = filemtime($path);

		return $fileTime <= $cacheTime;
	}

	protected function getCached($path)
	{
		$cachePath = $this->getCachePath($path);

		return file_get_contents($cachePath);
	}

	protected function putToCache($path, $content)
	{
		$cachePath = $this->getCachePath($path);

		if (@file_put_contents($cachePath, $content))
		{
			return $cachePath;
		}

		if (File::write($cachePath, $content))
		{
			return $cachePath;
		}

		return false;
	}

	/**
	 * Bust the opcode cache for a given .php file
	 *
	 * This method can address opcode caching with:
	 * - Zend OPcache
	 * - Alternative PHP Cache (now defunct)
	 * - Windows Cache Extension for PHP (versions lower than 2.0.0)
	 * - XCache (now defunct)
	 *
	 * @param   string  $path  The file to bus the cache for
	 *
	 * @return  void
	 */
	private function bustOpCache($path)
	{
		if (function_exists('opcache_invalidate'))
		{
			opcache_invalidate($path);
		}

		if (function_exists('apc_compile_file'))
		{
			apc_compile_file($path);
		}

		if (function_exists('wincache_refresh_if_changed'))
		{
			wincache_refresh_if_changed([$path]);
		}

		if (function_exists('xcache_asm'))
		{
			xcache_asm($path);
		}
	}
}
