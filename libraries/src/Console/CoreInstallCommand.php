<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Console;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Installation\Model\ConfigurationModel;
use Joomla\Console\AbstractCommand;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Console command for installing the Joomla CMS
 *
 * @since  4.0.0
 */
class CoreInstallCommand extends AbstractCommand
{
	/**
	 * Stores the Input Object
	 * @var Input
	 * @since 4.0
	 */
	private $cliInput;

	/**
	 * SymfonyStyle Object
	 * @var SymfonyStyle
	 * @since 4.0
	 */
	private $ioStyle;

	/**
	 * Configures the IO
	 *
	 * @return void
	 *
	 * @since 4.0
	 */
	private function configureIO()
	{
		$this->cliInput = $this->getApplication()->getConsoleInput();
		$this->ioStyle = new SymfonyStyle($this->getApplication()->getConsoleInput(), $this->getApplication()->getConsoleOutput());
	}

	/**
	 * Execute the command.
	 *
	 * @return  integer  The exit code for the command.
	 *
	 * @since   4.0.0
	 *
	 * @throws null
	 */
	public function execute(): int
	{
		$this->configureIO();

		if (file_exists(JPATH_CONFIGURATION . '/configuration.php'))
		{
			$this->ioStyle->warning("Joomla is already installed and set up.");

			return 0;
		}

		$options = $this->collectOptions();

		$model = new ConfigurationModel;

		$completed = $model->setup($options);

		if ($completed)
		{
			$this->ioStyle->success("Joomla installation completed successfully!");

			return 0;
		}

		return 0;
	}

	/**
	 * Initialise the command.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function initialise()
	{
		$this->setName('core:install');

		$this->setDescription('Sets up the joomla CMS.');

		$help = "The <info>%command.name%</info> is used for setting up the Joomla CMS \n 
					<info>php %command.full_name%</info>";

		$this->setHelp($help);
	}


	/**
	 * Retrieves options Template
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	public function getOptionsTemplate()
	{
		return [
			'site_name' => [
				'question'  => "What's the name of your website?",
				'type'      => 'question',
			],
			'admin_user' => [
				'question'  => "Enter Admin username.",
				'type'      => 'question',
				'rules'     => 'isAlphanumeric',
			],
			'admin_password' => [
				'question'  => "Enter admin password.",
				'type'      => 'question',
			],
			'admin_email' => [
				'question'  => "Enter admin email.",
				'type'      => 'question',
				'rules'     => 'isEmail',
			],
			'db_type' => [
				'question'  => "What's your database type?",
				'type'      => 'select',
				'optionData'    => ['mysql', 'mysqli'],
				'default'   => 'mysql',
			],
			'db_host' => [
				'question'  => "Enter database host.",
				'type'      => 'question',
			],
			'db_user' => [
				'question'  => "Enter database user",
				'type'      => 'question',
			],
			'db_pass' => [
				'question'  => "Enter database password.",
				'type'      => 'question',
			],
			'db_name' => [
				'question'  => "Enter database name.",
				'type'      => 'question',
			],
			'db_prefix' => [
				'question'  => "Database prefix?",
				'type'      => 'question',
				'default'   => 'lmao_',
			],
			'helpurl' => [
				'question'  => "Help URL",
				'type'      => 'question',
				'default'   => 'https://joomla.org',
			],
			'db_old' => [
				'question'      => "What do you want to do about old DB?",
				'type'          => 'select',
				'optionData'    => ['remove', 'backup'],
				'default'       => 'backup',
			],
			'language' => [
				'question'      => "Site Language",
				'type'          => 'select',
				'optionData'    => ['en-GB', 'en-US'],
				'default'       => 'en-GB',
			],
		];
	}

	/**
	 * Retrieves options from user inputs
	 *
	 * @return array
	 *
	 * @since 4.0
	 */
	private function collectOptions()
	{
		$data = $this->getOptionsTemplate();

		$options = array();

		foreach ($data as $key => $value)
		{
			$valid = false;

			while (!$valid)
			{
				$val = $this->processType($value);
				$rules = isset($value['rules']) ?? null;

				if ($rules)
				{
					$validator = $this->validateInput($val, $value['rules']);

					if ($validator === true)
					{
						$valid = true;
						$options[$key] = $val;
					}

					$this->ioStyle->error($validator['message']);
				}
				else
				{
					$valid = true;
				}

				$options[$key] = $val;
			}

			$this->ioStyle->write(sprintf("\033\143"));
		}

		return $options;
	}

	/**
	 * Process a console input type
	 *
	 * @param   array  $data  The option template
	 *
	 * @return mixed
	 *
	 * @since 4.0
	 */
	private function processType($data)
	{
		switch ($data['type'])
		{
			case 'question':
				return $this->ioStyle->ask($data['question']);
				break;

			case 'select':
				return $this->ioStyle->choice($data['question'], $data['optionData']);
				break;
		}
	}


	/**
	 * Validates an Input based on some rule(s)
	 *
	 * @param   mixed   $input      Data to be validated
	 * @param   string  $validator  Validation rule
	 *
	 * @return boolean
	 *
	 * @since 4.0
	 */
	public function validateInput($input, $validator)
	{
		$rules = explode('|', $validator);

		foreach ($rules as $key => $rule)
		{
			if (strpos($rule, ':') === false)
			{
				$valid = $this->{$rule}($input);

				if (is_array($valid))
				{
					return $valid;
				}
			}
			else
			{
				list($function, $arg) = explode(':', $rule);
				$valid = $this->{$function}($input, $arg);

				if (is_array($valid))
				{
					return $valid;
				}
			}
		}

		return true;
	}

	/**
	 * Test if an input is integer
	 *
	 * @param   string  $input  The data to be tested
	 *
	 * @return array | boolean
	 *
	 * @since 4.0
	 */
	public function isInteger($input)
	{
		if (!is_numeric($input))
		{
			return ['message' => 'The input must be an integer.'];
		}

		return true;
	}

	/**
	 * Validates Maximum length
	 *
	 * @param   string   $input   Input that needs to be validated
	 * @param   integer  $length  Length to be matched
	 *
	 * @return array | bool
	 *
	 * @since 4.0
	 */
	public function maxLength($input, $length)
	{
		if (strlen($input) > $length)
		{
			return ['message' => "The input cannot be greater than $length."];
		}

		return true;
	}

	/**
	 * Validates Minimum length
	 *
	 * @param   string   $input   Input that needs to be validated
	 * @param   integer  $length  Length to be matched
	 *
	 * @return array | bool
	 *
	 * @since 4.0
	 */
	public function minLength($input, $length)
	{
		if (strlen($input) < $length)
		{
			return ['message' => "The input cannot be lesser than $length characters."];
		}

		return true;
	}

	/**
	 * Validates Email
	 *
	 * @param   string  $input  The string tht needs to be validated
	 *
	 * @return array | boolean
	 *
	 * @since 4.0
	 */
	public function isEmail($input)
	{
		if (!filter_var($input, FILTER_VALIDATE_EMAIL))
		{
			return ['message' => "The Email is not valid."];
		}

		return true;
	}

	/**
	 * Checks if the input is alphanumeric
	 *
	 * @param   string  $input  The string that needs to be validated
	 *
	 * @return array | boolean
	 *
	 * @since 4.0
	 */
	public function isAlphanumeric($input)
	{
		if (!preg_match('/^[a-z0-9]+$/i', $input))
		{
			return ['message' => "The input can only be alphanumeric."];
		}

		return true;
	}

}
