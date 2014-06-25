<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Config
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

use Joomla\Registry\Registry;

defined('JPATH_PLATFORM') or die;

/**
 * @package     Joomla.Libraries
 * @subpackage  Config
 * @since       3.3
 */
class JConfigLoader implements \ArrayAccess {

	/**
	 * @var \JConfigResolverInterface
	 */
	protected $resolver;

	/**
	 * Create a configuration loader object
	 *
	 * @param   string  $resolver   The name of the configuration resolver to use.
	 * @param   array   $options    An array of options.
	 */
	public function __construct($resolver, array $options = array())
	{
		$this->setResolver($this->buildResolverInstance($resolver, $options));
	}

	/**
	 * @param string $key
	 * 
	 * @return bool
	 * 
	 * @see {JConfigResolverInterface::exists()}
	 */
	public function exists($key)
	{
		return $this->resolver->exists($key);
	}

	/**
	 * @param string $key
	 * @param mixed $default
	 * 
	 * @return mixed
	 * 
	 * @see {JConfigResolverInterface::get()}
	 */
	public function get($key, $default = null)
	{
		return $this->resolver->get($key, $default);
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 * 
	 * @return void
	 * 
	 * @see {JConfigResolverInterface::set()}
	 */
	public function set($key, $value)
	{
		$this->resolver->set($key, $value);
	}

	/**
	 * @param string $key
	 * 
	 * @return void
	 * 
	 * @see {JConfigResolverInterface::unset()}
	 */
	public function unset($key)
	{
		$this->resolver->unset($key);
	}

	/**
	 * @return \JConfigResolverInterface
	 */
	public function getResolver()
	{
		return $this->resolver;
	}

	/**
	 * @param \JConfigResolverInterface $resolver
	 * @return static
	 */
	public function setResolver(JConfigResolverInterface $resolver)
	{
		$this->resolver = $resolver;

		return $this;
	}

	/**
	 * @param string $type
	 * @return \JConfigResolverInterface
	 * @throws \RuntimeException
	 */
	protected function buildResolverInstance($type, array $options)
	{
		$className = 'JConfigResolver' . ucfirst(strtolower($type));

		if (! class_exists($className))
		{
			throw new \RuntimeException('Invalid configuration resolver.');
		}

		return new $className($options);
	}

	/**
	 * PHP Magic method.
	 * 
	 * @param string $key
	 * 
	 * @return mixed
	 * 
	 * @see {JConfigLoader::get()}
	 */
	public function __get($key)
	{
		return $this->get($key);
	}

	/**
	 * PHP Magic method.
	 * 
	 * @param string $key
	 * @param mixed $value
	 * 
	 * @return mixed
	 * 
	 * @see {JConfigLoader::set()}
	 */
	public function __set($key, $value)
	{
		$this->set($key, $value);
	}

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
    	return $this->exists($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
    	return $this->get($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value)
    {
    	return $this->set($offset, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset)
    {
    	return $this->unset($offset);
    }
}