<?php
/**
 * Part of the Joomla Framework Console Package
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Console\Helper;

use Joomla\Console\Descriptor\TextDescriptor;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Describes an object.
 *
 * @since  __DEPLOY_VERSION__
 */
class DescriptorHelper extends Helper
{
	/**
	 * Describes an object if supported.
	 *
	 * @param   OutputInterface  $output   The output object to use.
	 * @param   object           $object   The object to describe.
	 * @param   array            $options  Options for the descriptor.
	 *
	 * @return  void
	 *
	 * @since   ___DEPLOY_VERSION__
	 */
	public function describe(OutputInterface $output, $object, array $options = [])
	{
		(new TextDescriptor)->describe($output, $object, $options);
	}

	/**
	 * Returns the canonical name of this helper.
	 *
	 * @return  string  The canonical name
	 *
	 * @since   ___DEPLOY_VERSION__
	 */
	public function getName()
	{
		return 'descriptor';
	}
}
