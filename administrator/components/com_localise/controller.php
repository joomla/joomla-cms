<?php
/**
* @version 1.5
* @package com_localise
* @author Ifan Evans
* @copyright Copyright (C) 2007 Ifan Evans. All rights reserved.
* @license GNU/GPL
* @bugs - please report to post@ffenest.co.uk
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// import controller parent class
jimport('joomla.application.component.controller');

/**
* Controller class for the localise component
*/
class TranslationsController extends JController
{
	protected $_options = array();

	/**
	 * Custom Constructor
	 */
	function __construct($default = array())
	{
		parent::__construct($default);

		$this->registerTask('cancel', 		'doTask');
		$this->registerTask('cancelxml', 	'doTask');
		$this->registerTask('checkin', 	'doTask');
		$this->registerTask('checkout', 	'doTask');
		$this->registerTask('publish', 	'doTask');
		$this->registerTask('remove', 		'doTask');
		$this->registerTask('removexml', 	'doTask');
		$this->registerTask('unpublish', 	'doTask');

		$this->registerTask('add', 		'edit');
		$this->registerTask('apply', 		'edit');
		$this->registerTask('save', 		'edit');

		$this->registerTask('addxml', 		'editXML');
		$this->registerTask('applyxml', 	'editXML');
		$this->registerTask('editxml', 	'editXML');
		$this->registerTask('savexml', 	'editXML');

		$this->registerTask('default', 	'setDefault');

	}

	/**
	* Build the filters for a view
	* @param array an associative array of allowed fields and values
	* @param string the namespace for this view
	* @return array	The Configuration in an array
	*/
	function _buildfilters($allowed=array(), $namespace='')
	{
		// initialise
		global $mainframe;
		$filters = array();
		if ($namespace) {
			$namespace = trim($namespace,'.') . '.';
		} else {
			$namespace = 'com_localise.';
		}

		// get the limitstart for this namespace
		$filters['limitstart'] = $mainframe->getUserStateFromRequest($namespace . 'limitstart', 'limitstart', 0);

		// then validate all the filters for this namespace
		foreach ($allowed as $k=>$v) {
			// values are all changed to lower case
			$values = explode('|',$v);
			foreach ($values as $k2=>$v2) {
				$values[$k2] = strtolower($v2);
			}
			// A: get the old/current value
			$old = $mainframe->getUserState('request.' . $namespace . $k);
			// B: get the new value from the user input
			$new = $mainframe->getUserStateFromRequest($namespace . $k, $k, $values[0]);
			// C: check that the new (lowercase) value is valid
			if ($k=='limit') {
				$new = (is_numeric($new)) ? abs($new) : $values[0];
			} else if ($v && array_search(strtolower($new),$values) === false) {
				$new = $values[0];
			}
			// D: reset the page to #1 if the value has changed
			if ($old != $new) {
				$options['limitstart'] = 0;
			}
			// set the value
			$filters[$k] = $new;
		}

		// return
		return $filters;
	}

	/**
	* Build the configuration options.
	* @return array	The Configuration in an array
	*/
	function _buildoptions ()
	{
		// initialise configuration variables
		global $mainframe;
		$task	= strtolower($this->_task);
		$options['config'] 			= JComponentHelper::getParams('com_localise');

		$options['autoCorrect']		= $options['config']->get('autoCorrect', 'a^=â');
		$options['backticks'] 		= $options['config']->get('backticks', 0);
		$options['cid'] 			= JRequest::getVar('cid', array(''), '', 'array');
		$options['client_lang']		= $mainframe->getUserStateFromRequest('com_localise.client_lang','client_lang','');
		$options['globalChanges'] 	= $options['config']->get('globalChanges', 0);
		$options['limitstart']		= $mainframe->getUserStateFromRequest('com_localise.limitstart','limitstart','');
		$options['newprocess'] 		= JRequest::getVar('newprocess',0,'','integer');
		$options['refLang'] 		= $options['config']->get('refLanguage', 'en-GB');
		$options['refLangMissing'] 	= false;
		$options['searchStyle']		= $options['config']->get('searchStyle', 'background-color:yellow;');
		$options['task'] 			= strtolower($task);

		// initialise a list of available languages
		$options['languages'] = array();
		$options['clients'] = array();
		$options['clients']['S'] = JText::_('Site');
		foreach (JLanguage::getKnownLanguages(JPATH_SITE) as $k=>$v) {
			$options['languages']['S_'.$k] = $options['clients']['S'] . ' - '.$v['name'];
		}
		$options['clients']['A'] = JText::_('Administrator');
		foreach (JLanguage::getKnownLanguages(JPATH_ADMINISTRATOR) as $k=>$v) {
			$options['languages']['A_'.$k] = $options['clients']['A'] . ' - '.$v['name'];
		}
		if (JFolder::exists(JPATH_INSTALLATION)) {
			$options['clients']['I'] = JText::_('Installation');
			foreach (JLanguage::getKnownLanguages(JPATH_INSTALLATION) as $k=>$v) {
				$options['languages']['I_'.$k] = $options['clients']['I'] . ' - '.$v['name'];
			}
		}

		// validate client_lang (split, reassemble with cases, check against allowed values, on failure default to first allowed value)
		$cl_split = preg_split("/[^a-z]/i",$options['client_lang']);
		$options['client_lang'] = strtoupper($cl_split[0]) . '_' . strtolower($cl_split[1]) . '-' . strtoupper($cl_split[2]);
		if (!isset($options['languages'][$options['client_lang']])) {
            $options['client_lang'] = key($options['languages']);
        }

		// set client variables
		$options['client'] = $options['client_lang']{0};
		if ($options['client']=='A') {
			$options['basePath'] = JPATH_ADMINISTRATOR;
			$options['clientKey'] = 'administrator';
		} else if ($options['client']=='I') {
			$options['basePath'] = JPATH_INSTALLATION;
			$options['clientKey'] = 'installation';
		} else {
			$options['basePath'] = JPATH_SITE;
			$options['clientKey'] = 'site';
		}
		$options['clientName'] = JText::_($options['clientKey']);

		// validate that the reference language exists on this client
		if (!isset($options['languages'][$options['client'].'_'.$options['refLang']])) {
			// initialise to en-GB
			$use = 'en-GB';
			// look for the first key index containing the reference language string
			foreach($options['languages'] as $k=>$v) {
				if ($k{0}==$options['client']) {
					$use = substr($k,-4);
					break;
				}
			}
			// set back to $options key
			$options['refLang'] = $use;
		}

		// set language variables
		$options['lang'] = substr($options['client_lang'],2);
        $options['langLen'] = strlen($options['lang']);
		$options['langName'] = $options['languages'][$options['client_lang']];
		$options['langPath'] 	= JLanguage::getLanguagePath($options['basePath'], $options['lang']);
        $options['refLangLen'] = strlen($options['refLang']);
		$options['refLangPath'] = JLanguage::getLanguagePath($options['basePath'], $options['refLang']);

		// set reference language variables
		$options['isReference'] = intval($options['lang']==$options['refLang']);

		// validate the cid array
		if (!is_array($options['cid'])) {
			if (!empty($options['cid'])) $options['cid'] = array($options['cid']);
			else $options['cid'] = array('');
		}

		// process the cid array to validate filenames
		foreach($options['cid'] as $k=>$v){
			if ($v) {
				// strip unpublished prefix
				if (substr($v,0,3)=='xx.') $v = substr($v,3);
				// strip files that don't match the selected language
				if (substr($v,0,$options['langLen'])!=$options['lang']) unset($options['cid'][$k]);
				// otherwise set back to $options
				else $options['cid'][$k] = $v;
			}
		}

		// set the filename
		$options['filename'] = $options['cid'][0];

		// build the autocorrect array
		$autoCorrect = array();
		if ($options['autoCorrect']) {
			foreach(explode(';',$options['autoCorrect']) as $v){
				list($k2,$v2)=explode('=',$v);
				$k2 = trim($k2);
				$v2 = trim($v2);
				if(($k2)&&($v2)) {
					$autoCorrect[$k2] = $v2;
				}
			}
		}
		$options['autoCorrect'] = $autoCorrect;

		// return the options array
		return $options;
	}

	/**
	* Processing File(s)
	* @param array $options		The configuration array for the component
	* @param string $task  		a specific task (overrides $options)
	* @param mixed $file  		a specific filename or array of filenames to process (overrides $options)
	* @param string $redirect_task	the task to use when redirecting (blank means no redirection)
	* @param boolean $report	whether or not to report processing success/failure
	*/
	function _multitask($task=null, $file=null, $redirect_task='files', $report=true)
	{
		// variables
		$options =& $this->getOptions();
		$task = strtolower(is_null($task) ? $this->_task : $task);

		// validate the task
		if ($task=='cancel') {
			$task = 'checkin';
			$redirect_task = 'files';
			$report = false;
		} else if ($task=='cancelxml') {
			$task = 'checkin';
			$redirect_task = 'languages';
			$report = false;
		}

		// validate the filename
		// 1: use a specific file or files
		// 2: use the client_lang
		// 3: check that we have at least one file
		if ($file) {
			$options['cid'] = (is_array($file)) ? $file : array($file);
		} else if ($task=='removexml') {
			$options['cid'][0] = $options['lang'].'.xml';
		} else if ((empty($options['cid'][0])) && ($task!='checkin')) {
			echo "<script> alert('". JText::_('Please make a selection from the list to') . ' ' . JText::_(str_replace('xml','',$task)) ."'); window.history.go(-1);</script>\n";
			exit();
		}

		// initialise file classes
		jimport('joomla.filesystem.file');

		// initialise checkout file content
		if ($task=='checkout') {
			$user = & JFactory::getUser();
			$chk_file_content = time() . '#' . $user->get('id','0') . '#' . $user->get('name','[ Unknown User ]');
		}

		// initialise variables
		global $mainframe;
		$file_list = array();
		$nofile_list = array();
		$inifile_list = array();
		$last = '';

		// process each passed file name (always the 'real' filename)
		foreach ($options['cid'] as $file) {

			// validate the filename language prefix
			if (preg_match('/^[a-z]{2,3}-[A-Z]{2}[.].*/',$file)) {

				// get the language and language path
				$lang = substr($file,0,$options['langLen']);
				$langPath = JLanguage::getLanguagePath($options['basePath'], $lang);

				// ensure that XML files are only affected by XML tasks
				if ((substr($file,-4)=='.xml') && (substr($task,-3)!='xml')) {
					// continue without error warning
					continue;
				}

				// ensure that there are no existing published INI files when we are deleting an XML file
				if (($task=='removexml') && (count(JFolder::files($langPath,'^'.$lang.'.*ini$')))) {
					// error and continue
					$inifile_list[$file] = $file;
					continue;
				};

				// get file path-names
				$chk_file = 'chk.'.$file;
				$pub_file = $file;
				$unpub_file = 'xx.'.$file;

				// check for an unpublished file
				if (JFile::exists($langPath.DS.$unpub_file)) {
					$file = $unpub_file;
				}
				// check the file exists
				else if (!JFile::exists($langPath.DS.$file)) {
					// error and continue
					$nofile_list[$file] = $file;
					continue;
				}

				// cancel/checkin a file
				// checkout a file
				// delete a file
				// delete an XML file
				// publish a file
				// unpublish a file
				// otherwise break because the task isn't recognised
				if (($task=='checkin') && (JFile::exists($langPath.DS.$chk_file))) {
					$do = JFile::delete($langPath.DS.$chk_file);
				} else if ($task=='checkout') {
					$do = Jfile::write($langPath.DS.$chk_file, $chk_file_content);
				} else if ($task=='remove') {
					$do = JFile::delete($langPath.DS.$file);
				} else if ($task=='removexml') {
					if ($do = JFile::delete($langPath.DS.$file)) {
						$do = JFolder::delete($langPath);
					}
				} else if ($task=='publish') {
					$do = JFile::move($file, $pub_file, $langPath);
				} else if ($task=='unpublish') {
					$do = JFile::move($file, $unpub_file, $langPath);
				} else {
					break;
				}

				// build an array of things to hide form the filename
				$filename_hide = array();

				// add the function to the file list on success
				if ($do) {
					$file_list[$file] = str_replace('xx.'.$lang, $lang,substr($file,0,-4));
				}
			}
		}

		if ($report) {
			// report processing success
			if (count($file_list)) {
				$mainframe->enqueueMessage(sprintf(JText::_($task.' success'), count($file_list), implode(', ',$file_list)));
			}
			// report existing ini files
			if (count($inifile_list)) {
				$mainframe->enqueueMessage(sprintf(JText::_($task.' inifile'), count($inifile_list), implode(', ',$inifile_list)));
			}
		}

		// redirect
		if ($redirect_task) {
			if ($task=='removexml') {
				$mainframe->redirect('index.php?option=com_localise');
			} else {
				$mainframe->redirect('index.php?option=com_localise&client_lang='.$options['client_lang'].'&task='.$redirect_task);
			}
		}
	}

	/**
	* Do various tasks
	* @uses _multitask
	*/
	function doTask()
	{
		return $this->_multitask();
	}

	/**
	* Create Edit or Save a Translation File
	*/
	function edit()
	{
		// import file functions
		jimport('joomla.filesystem.file');

		// variables
		$app		= &JFactory::getApplication();
		$options	= &$this->getOptions();

		// build the search highlight array
		$options['filter_search'] =	$app->getUserStateFromRequest('com_localise.files.filter_search',	'filter_search', '');

		// we are creating a new file
		// always in the reference language
		if ($options['task']=='add') {
			$options['newprocess'] = 1;
			if (! $options['isReference']) {
				$app->enqueueMessage(JText::_('Always create in reference language'));
			}
		}

		// we are in the process of creating a new file
		// the filename is set by the 'newfilename' field
		if ($options['newprocess']) {
			$options['newfilename'] = strtolower(JRequest::getVar('newfilename','','','string'));
			// validate the filename
			if ($options['newfilename']) {
				// strip off ini
				if (substr($options['newfilename'],-4)=='.ini') {
					$options['newfilename'] = substr($options['newfilename'],0,-4);
				}
				// strip off language
				if (preg_match('/^[a-z]{2}-[a-z]{2}[.].*/',$options['newfilename'])) {
					$options['newfilename'] = substr($options['newfilename'],6);
				}
				// set variables
				$options['filename'] = $options['lang'].'.'.$options['newfilename'].'.ini';
			}
			// no filename
			else {
				// report error
				$app->enqueueMessage(JText::_('filename desc'));
				// change task
				if ($options['task']!='add') {
					$options['task'] = 'edit';
					$options['field_error_list']['filename'] = JText::_('filename');

				}
			}
		}

		// 2: otherwise verify that we have a filename
		// 3: otherwise validate the checkout status of the selected file
		else if (empty($options['filename'])) {
			$app->enqueueMessage(JText::_('You must select a file to edit'));
			$app->redirect('index.php?option=com_localise&task=files');
		} else if ($content = @file_get_contents($options['langPath'].DS.'chk.'.$options['filename'])) {
			list ($timestamp,$userid,$username) = explode('#', $content.'##');
			$user = & JFactory::getUser();
			// validate the checkout
			if	(
				((time()-$timestamp) < 3600)
			&&	($userid <> 0)
			&&	($userid <> $user->get('id','0'))
				) {
				// report and redirect
				$checkin = '<a href="index.php?option=com_localise&task=checkin&id='.$options['filename'].'" title="'. JText::_('Force Checkin') . '" style="font-size:smaller">[' . JText::_('Checkin') . ']</a>';
				$app->enqueueMessage(sprintf(JText::_('checked out by'), $options['filename'], $username, $checkin));
				$app->redirect('index.php?option=com_localise&task=files');
			}
		}

		// set the reference language filename from the selected filename
		$options['refFilename'] = str_replace($options['lang'],$options['refLang'],$options['filename']);

		// find the published reference language file
		// default to an unpublished reference file
		if (JFile::exists($options['refLangPath'].DS.$options['refFilename'])) {
			$options['ref_path_file'] = $options['refLangPath'].DS.$options['refFilename'];
		} else {
			$options['ref_path_file'] = $options['refLangPath'].DS.'xx.'.$options['refFilename'];
		}

		// find the published selected language file
		// default to an unpublished new file
		if (JFile::exists($options['langPath'].DS.$options['filename'])) {
			$options['path_file'] = $options['langPath'].DS.$options['filename'];
		} else {
			$options['path_file'] = $options['langPath'].DS.'xx.'.$options['filename'];
		}

		// STRINGS: initialise $editData from the reference language file contents
		// $editData is an analogue of the reference file
		// header lines are skipped
		// comments and blank lines are strings with an integer index
		// key=value pairs are arrays with the key as an index
		$editData = array();
		$header = 0;
		$refStrings = array();
		if ($refContent = @file($options['ref_path_file'])) {
			foreach ($refContent as $k=>$v) {
				$v = trim($v);
				// grab the comments (but skip up to 6 lines before we have any strings in the file)
				// grab the strings
				if ((empty($v))||($v{0}=='#')||($v{0}==';')) {
					if($header++>6) $editData[$k] = $v;
				} else if(strpos($v,'=')) {
					$header = 7;
					list($key,$value) = explode('=',$v,2);
					$key = strtoupper($key);
					$refStrings[$key] = $value;
					$editData[$key] = array('ref'=>$value,'edit'=>$value);
					if ($options['isReference']) {
						$editData[$key]['lang_file'] = $value;
					}
				}
			}
		}

		// STRINGS: load the selected file contents and process into $editData
		// only when the selected language is not the same as the reference language
		if ($options['isReference']) {
			$fileContent = $refContent;
			$fileStrings = array();
			$fileMeta = TranslationsHelper::getINIMeta($fileContent, $fileStrings);
			$editStrings = $fileStrings;
		} else if ($fileContent = @file($options['path_file']))  {
			$fileStrings = array();
			$fileMeta = TranslationsHelper::getINIMeta($fileContent, $fileStrings);
			$editStrings = $fileStrings;
			foreach ($fileStrings as $k=>$v) {
				$editData[$k]['edit'] = $v;
				$editData[$k]['lang_file'] = $v;
			}
		} else {
			$fileContent = array();
			$fileStrings = array();
			$fileMeta = array('headertype'=>1, 'owner'=>'ff', 'complete'=>0);
			$editStrings = array();
		}

		// STRINGS: load the user form contents and process into $editData
		$editFormOnly = array();
		if ($FormKeys = JRequest::getVar('ffKeys', array(), '', 'ARRAY', JREQUEST_ALLOWRAW)) {
			$FormValues = JRequest::getVar('ffValues', array(), '', 'ARRAY', JREQUEST_ALLOWRAW);
			// process each key=value pair from the form into $editData
			foreach ($FormKeys as $k=>$v) {
				if (($v) && (isset($FormValues[$k]))) {
					$key = strtoupper(trim(stripslashes($v)));
					$value = trim(stripslashes(str_replace('\n',"\n",$FormValues[$k])));
					$editStrings[$key] = $value;
					$editData[$key]['edit'] = $value;
					$editData[$key]['user_form'] = $value;
				}
			}
			// every element of $editData must have a form entry
			foreach($editData as $k=>$v){
				if (is_array($v) && !isset($v['user_form'])) {
					unset($editStrings[$k]);
					unset($editData[$k]);
				}
			}
		}

		// META: get the XML and status meta then initialise
		$options['XMLmeta'] = TranslationsHelper::getXMLMeta($options['langPath'].DS.$options['lang'].'.xml');
		$statusMeta = TranslationsHelper::getINIstatus($refStrings, $editStrings);
		$editMeta = array_merge($options['XMLmeta'], $fileMeta, $statusMeta);
		$editMeta['filename'] = $options['filename'];

		// META: apply any user form values
		foreach($editMeta as $k=>$v) {
			$editMeta[$k] = JRequest::getVar($k,$v,'','string');
		}

		// META: require meta values
		foreach(array('version','author') as $v) {
			if(empty($editMeta[$v])) {
				$options['field_error_list'][$v] = JText::_($v);
			}
		}

		// ERRORS: report any errors and change the task
		if ((!empty($options['field_error_list']))&&($options['task']!='add')) {
			$app->enqueueMessage(sprintf(JText::_('Values Required'), implode(', ',$options['field_error_list'])));
			$options['task'] = 'edit';
		}

		// create a new file or save an existing file
		if (($options['task']=='apply')||($options['task']=='save')) {

			// ensure the file does not already exist when we are creating a new file
			if (($options['newprocess'])&&(JFile::exists($options['path_file']))) {
				// report error and set task flag
				$app->enqueueMessage(sprintf(JText::_('Language INI Exists'),$options['newfilename']));
				$options['task'] = 'edit';
			}

			// otherwise save the file
			else {
				// check the complete status
				// we set the complete value to the number of strings that are 'unchanged'
				// so that if the reference INI file should change the 'complete' flag is unset/broken
				$editMeta['complete'] = JRequest::getVar('complete', '', 'post', 'string');
				$editMeta['complete'] = ($editMeta['complete'] == 'COMPLETE') ? $editMeta['unchanged'] : 0;
				// build the header
				if ($editMeta['headertype']==1) {
					// @todo - should leave this up to the repo
					$saveContent = '# $'.'Id '.$options['filename'] . ' ' . $editMeta['version'] . ' ' . date('Y-m-d H:i:s') . ' ' . $editMeta['owner'] . ' ~' . $editMeta['complete'] . ' $';
				} else {
					$saveContent = '# version ' . $editMeta['version'] . ' ' . date('Y-m-d H:i:s') . ' ~' . $editMeta['complete'];
				}
				$saveContent .= "\n" . '# author ' . $editMeta['author'];
				$saveContent .= "\n" . '# copyright ' . $editMeta['copyright'];
				$saveContent .= "\n" . '# license ' . $editMeta['license'];
				$saveContent .= "\n\n" .  '# Note : All ini files need to be saved as UTF-8';
				$saveContent .= "\n\n";

				// process the $editData array to get the remaining content
				$changedStrings = array();
				$header = 0;
				foreach ($editData as $k=>$v) {
					// 1: add a blank line or comment
					// 2: add a key=value line (no need to addslashes on quote marks)
					if (!is_array($v)) {
						$saveContent .= $v . "\n";
					} else {
						// change newlines in the value
						$value = preg_replace('/(\r\n)|(\n\r)|(\n)/', '\n', $v['edit']);
						// change single-quotes or backticks in the value
						if ($options['backticks']>0) {
							$value = strtr($value, "'", '`');
						} else if ($options['backticks']<0) {
							$value = strtr($value, '`', "'");
						}
						// set back to $editData
						$editData[$k]['edit'] = $value;
						// add to file content
						$saveContent .= $k . '=' . $value . "\n";
						// if the string is in the selected language file
						if (isset($v['lang_file'])) {
							// and it has changed (via the user form)
							if ($v['lang_file'] != $v['edit']) {
								// log the change in a translation array
								$changedStrings[ "\n".$k.'='.$v['lang_file'] ] = "\n".$k.'='.$v['edit'];
							}
						}
					}
				}

				// if there is no reference Language File, automatically initialise/create one which is the same as the selected language file
				if ($options['refLangMissing']) {
					if (JFile::write($options['refLangPath'].DS.$options['refLangFile'], trim($saveContent))) {
						$app->enqueueMessage(sprintf(JText::_('Language INI Created'), $options['refLangFile']));
					}
				}

				// 1: write the selected language file and clear newprocess flag
				// 2: report failure
				if (JFile::write($options['path_file'], trim($saveContent))) {
					$app->enqueueMessage(sprintf(JText::_('Language INI '.(($options['newprocess'])?'Created':'Saved')),$options['clientName'],$options['filename']));
					$options['newprocess'] = 0;
				} else {
					$app->enqueueMessage(sprintf(JText::_('Could not write to file'),$options['path_file']));
				}

				// process changed strings globally across all the the ini files from the selected language directory
				if ((count($changedStrings)) && ($options['globalChanges'])) {
					$write = 0;
					$writeFiles = array();
					if ($files = JFolder::files($options['langPath'])) {
						foreach ($files as $file) {
							// skip non-INI files
							// skip this file
							// skip this file (unpublished)
							// skip checked out files
							if	(
								(strtolower(substr($file,-4)!='.ini'))
								|| ($file==$options['filename'])
								|| ($file=='xx.'.$options['filename'])
								|| (array_search($options['langPath'].DS.'chk.'.$file,$files))
								) {
								continue;
							}

							// otherwise grab the file content
							if ($content = file_get_contents($options['langPath'].DS.$file)) {
								// parse the changed strings
								$new_content = strtr($content, $changedStrings);
								// check for changes then write to the file
								if ($new_content != $content) {
									if (JFile::write($options['langPath'].DS.$file, trim($new_content))) {
										$writeFiles[$write++] = $file;
									}
								}
							}
						}
					}
					// report
					if ($write) {
						$app->enqueueMessage(sprintf(JText::_('Global String Change'), $write, implode('; ',$writeFiles)));
					}
				}
			}

		}

		// 1: checkin when we are saving (this will redirect also)
		// 2: call the html when we are editing or applying (and checkout existing files)
		if ($options['task'] == 'save') {
			$this->_multitask('checkin', $options['filename'], 'files', false);
		} else {
			$view = $this->getView($this->_name, 'html');
			$view->setLayout('edit');
			$view->assignRef('data', $editData);
			$view->assignRef('meta', $editMeta);
			$view->assignRef('options', $options);
			$view->display();
			if (!$options['newprocess']) {
				$this->_multitask('checkout', $options['filename'], false, false);
			}
		}
	}

	/**
	* Create Edit or Save an XML Language File
	* @uses HTML_localise::editINI 	To build and output the HTML when editing or applying changes
	*/
	function editXML()
	{
		// import file functions
		jimport('joomla.filesystem.file');

		// variables
		$app		= &JFactory::getApplication();
		$options =& $this->getOptions();
		$options['field_error_list'] = array();

		// new tasks set newprocess
		if ($options['task']=='addxml') {
			$options['newprocess'] = 1;
			$app->enqueueMessage(JText::_('Tag Desc'));

		}

		// when we are in the process of creating a new file
		// the client is passed by the 'addclient' field
		// the language tag is passed by the 'tag' field
		if ($options['newprocess']) {

			// get the client and set the client name and path
			$client = JRequest::getVar('newclient','','','string');
			if (($client=='A')||($client=='I')||($client=='S')){
				$options['client'] = $client;
				if ($options['client']=='A') {
					$options['basePath'] = JPATH_ADMINISTRATOR;
					$options['clientKey'] = 'administrator';
				} else if ($options['client']=='I') {
					$options['basePath'] = JPATH_INSTALLATION;
					$options['clientKey'] = 'installation';
				} else {
					$options['basePath'] = JPATH_SITE;
					$options['clientKey'] = 'site';
				}
				$options['clientName'] = JText::_($options['clientKey']);
			}

			// validate the language tag (split, check case, reassemble, on failure report error and change task)
			// report error and change task if we need to
			$tag_split = preg_split("/[^a-z]/i", JRequest::getVar('tag','','','string'));
			$tag = strtolower($tag_split[0]) . '-' . strtoupper($tag_split[1]);
			print_r($tag);
            if (!preg_match('/^[a-z]{2,3}-[A-Z]{2}$/',$tag)) {
				$options['field_error_list']['tag'] = JText::_('Tag Desc');
				if ($options['task']!='addxml') {
					$options['task'] = 'editxml';
				}
			}

			// set variables
			$options['client_lang'] = $options['client'].'_'.$tag;
			$options['lang'] = $tag;
			$options['langPath'] = $options['basePath'].DS.'language'.DS.$options['lang'];
			$options['filename'] = $tag.'.xml';
		}
		// otherwise we use the selected language XML file
		else {
			$options['filename'] = $options['lang'].'.xml';
		}

		// initialise the $editData array (with all the necessary entries)
		// any extra entries in the XML file will be retained but are not editable
		$editData = array(
			'tag' => '',
			'name' => '',
			'description' => '',
			'version' => '1.5.0',
			'creationDate' => date('Y-m-d'),
			'author' => '',
			'authorUrl' => '',
			'authorEmail' => '',
			'copyright' => '',
			'license' => 'http://www.gnu.org/copyleft/gpl.html GNU/GPL',
			'metadata' => array(
				'name' => '',
				'tag' => '',
				'rtl' => 0,
				'locale' => '',
				'winCodePage' => 'iso-8859-1',
				'backwardLang' => '',
				'pdfFontName' => 'vera'
			),
			'params' => '',
		);

		// load the XML file (if there is one, there won't be when creating a new language)
		// if a filename exists when we are creating a new file we will copy values from it
		$xml = & JFactory::getXMLParser('Simple');
		if (JFile::exists($options['langPath'].DS.$options['filename'])) {
			if ($xml->loadFile($options['langPath'].DS.$options['filename'])) {
				// go through each child of the xml root (using the XML file as master)
				// all the nodes in the XML file are parsed in lowercase
				// do it this way to ensure that we retain all the existing data in the XML file
				foreach($xml->document->children() as $node) {
					// main nodes
					if ($node->name()!='metadata') {
					   $editData[$node->name()] = $node->data();
					}
					// metadata nodes
					else {
						foreach ($node->children() as $subnode) {
							$editData['metadata'][$subnode->name()] = $subnode->data();
						}
					}
				}
				// copy any lowercase keys to mixed case keys
				// remove the lowercase key afterwards
				foreach ($editData as $k=>$v) {
				    if ($k!='metadata') {
				        $k_lc = strtolower($k);
    				    if ($k_lc != $k) {
    				        $editData[$k] = $editData[$k_lc];
    				        unset($editData[$k_lc]);
                        }
				    } else {
            			foreach ($editData['metadata'] as $k=>$v) {
        				    $k_lc = strtolower($k);
        				    if ($k_lc != $k) {
        				        $editData['metadata'][$k] = $editData['metadata'][$k_lc];
        				        unset($editData['metadata'][$k_lc]);
                            }
        	            }
                    }
				}
                // ensure that metadata and main body values match
				foreach ($editData['metadata'] as $k=>$v) {
        			if (isset($editData[$k])) {
						$editData[$k] = $v;
					}
				}
			}
		}
		// require a valid XML file (unless creating a new language)
		else if (!$options['newprocess']) {
			$app->enqueueMessage(sprintf(JText::_('Invalid XML File'),$options['langPath'].DS.$options['filename']));
			$app->redirect('index.php?option=com_localise');
		}

		// apply the user form data (only when not empty)
		foreach($editData as $k=>$v){
			// check the main nodes
			if (is_string($v)) $editData[$k] = JRequest::getVar($k,$v,'','string');
			// check the metadata nodes
			else if (is_array($v)) {
				foreach($v as $k2=>$v2)	$editData[$k][$k2] = JRequest::getVar($k2,$v2,'','string');
			}
		}

		// validate the language name values
		$editData['metadata']['pdfFontName'] = strtolower($editData['metadata']['pdfFontName']);
		$editData['metadata']['rtl'] = intval($editData['metadata']['rtl']);

		// validate required (non-blank)
		$required = array ('tag','name','version','creationDate','author','locale','winCodePage','backwardLang','pdfFontName');
		foreach ($required as $v) {
			if ((isset($editData[$v])) && (empty($editData[$v]))) {
				$options['field_error_list'][$v] = JText::_($v);
			} else if ((isset($editData['metadata'][$v])) && (empty($editData['metadata'][$v]))) {
				$options['field_error_list'][$v] = JText::_($v);
			}
		}
		// report any errors and change the task
		if (($options['field_error_list'])&&($options['task']!='addxml')) {
			$app->enqueueMessage(sprintf(JText::_('Values Required'), implode(', ',$options['field_error_list'])));
			$options['task'] = 'editxml';
		}

		// create a new file or save an existing file
		if (($options['task']=='savexml')||($options['task']=='applyxml')) {

			// ensure the file does not already exist when we are creating a new language
			if (($options['newprocess'])&&(JFile::exists($options['langPath'].DS.$options['filename']))) {
				// report error and set task flag
				$app->enqueueMessage(sprintf(JText::_('Language XML Exists'), $options['lang']));
				$options['task'] = 'editxml';
			}

			// otherwise build and save the file
			else {

				// build the file content
				$saveData = '<?xml version="1.0" encoding="utf-8"?>'."\n";
				$saveData .= '<metafile version="1.5"  client="' . $options['clientKey'] . '" >'."\n";
				foreach($editData as $k=>$v){
					if (is_string($v)) {
						$saveData .= "\t".'<'.$k.'>'.htmlspecialchars($v).'</'.$k.'>'."\n";
					}
					else if (is_array($v)) {
						$saveData .= "\t".'<'.$k.'>'."\n";
						foreach($v as $k2=>$v2){
							$saveData .= "\t\t".'<'.$k2.'>'.htmlspecialchars($v2).'</'.$k2.'>'."\n";
						}
						$saveData .= "\t".'</'.$k.'>'."\n";
					}
				}
				$saveData .= '</metafile>'."\n";

				// create the directory when we are creating a language
				// do this manually because JFile::write does not work on windows (
				if ($options['newprocess']) {
					jimport('joomla.filesystem.folder');
					if (!JFolder::create($options['langPath'])) {
						// report failure and set task flag
						$app->enqueueMessage(sprintf(JText::_('Folder Created'),$options['langPath']));
						$options['task'] = 'applyxml';
					}
				}

				// write the XML file (this will create a new file or overwrite an existing file)
				// 1: report new/existing file and clear new process flag
				// 2: report failure and set task flag if we can't write
				if (JFile::write($options['langPath'].DS.$options['filename'], $saveData)) {
					$msg = ($options['newprocess']) ? 'Language XML Created' : 'Language XML Saved' ;
					$app->enqueueMessage(sprintf(JText::_($msg),$options['clientName'],$options['lang']));
					$options['newprocess'] = 0;
				} else {
					$app->enqueueMessage(sprintf(JText::_('Could not write to file'),$options['langPath'].DS.$options['filename']));
					$options['task'] = 'applyxml';
				}
			}
		}

		// redirect or show HTML after saving
		if ($options['task'] == 'savexml') {
			$app->redirect('index.php?option=com_localise');
		} else {
			$view = $this->getView($this->_name, 'html');
			$view->setLayout('editxml');
			$view->assignRef('data', $editData);
			$view->assignRef('options', $options);
			$view->display();
		}
	}

	/**
	* Get the configuration options.
	* @return array	The Configuration in an array
	*/
	function &getOptions () {

		if (empty($this->_options)) {
			$this->_options = $this->_buildoptions();
		}
		return $this->_options;
	}

	/**
	* Make a language the default language for a client
	*/
	function setDefault()
	{
        // variables
		$app	= &JFactory::getApplication();
    	$params = JComponentHelper::getParams('com_languages');

        $options =& $this->getOptions();
        if ($options['client']=='A') {
            $client = 'administrator';
        } else if ($options['client']=='S') {
            $client = 'site';
        } else if ($options['client']=='I') {
            $client = 'installation';
        } else {
            return false;
        }
		$lang = $options['lang'];

    	// check for request forgeries.
    	$token = JUtility::getToken();
    	if (!JRequest::getInt($token, 0, 'post')) {
    		JError::raiseError(403, 'Request Forbidden');
    	}

    	// set variables
    	$params->set($client, $lang);
    	$table =& JTable::getInstance('component');
	    $table->loadByOption('com_languages');
    	$table->params = $params->toString();

    	// save the changes
        if (!$table->check()) {
    		JError::raiseWarning(500, $table->getError());
    		$write = false;
        } else if (!$table->store()) {
    		JError::raiseWarning(500, $table->getError());
    		$write = false;
    	} else {
            $write = true;
        }

		// Redirect on success/failure
		if ($write) {
			$app->redirect('index.php?option=com_localise', sprintf(JText::_('Default Language Saved'), JText::_($client), $options['lang']) );
		} else {
			$app->redirect('index.php?option=com_localise', JText::_('ERRORCONFIGWRITEABLE'));
		}
    }

	/**
	* Show a List of INI Translation Files for a given Client-Language
	*/
	function files()
	{
		// filesystem functions
		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		// variables
		$app		= &JFactory::getApplication();
		$options	= &$this->getOptions();
		$user		= &JFactory::getUser();
		$userid		= $user->get('id',0);

		// build client_lang select box
		foreach ($options['languages'] as $k=>$v) {
			$sel_lang[] = JHTML::_('select.option', $k, $v);
		}
		$lists['client_lang'] = JHTML::_('select.genericlist', $sel_lang, 'client_lang', 'class="inputbox" size="1" onchange="document.adminForm.limitstart.value=0;document.adminForm.submit();"', 'value', 'text', $options['client_lang']);

		// validate all the filters (specific to this view)
		$allowed = array(
			'client_lang'			=> '',
			'filter_search' 		=> '',
			'filter_state' 			=> '*|U|P',
			'filter_status' 		=> '*|NS|IP|C',
			'filter_order' 			=> 'name|status|strings|version|datetime|author',
			'filter_order_Dir' 		=> 'asc|desc',
			'limit' 				=> $app->getCfg('list_limit')
		);
		$filters = $this->_buildfilters($allowed, 'com_localise.files.');

		// copy to $options
		$options = array_merge($options, $filters);

		// copy to $lists
		$lists['order'] = $options['filter_order'];
		$lists['order_Dir'] = $options['filter_order_Dir'];

		// validate and build the filter_search box
		$options['dosearch'] = '';
		if ($options['filter_search']) {
			// 1: turn it into a case-insensitive regexp
			// 2: check and use a submitted regexp
			// 3: invalid regexp
			if ($options['filter_search']{0}!='/') {
				$options['dosearch'] = '/.*'.trim($options['filter_search'],'/').'.*/i';
			} else if (@preg_match($options['filter_search'],'') !== false) {
				$options['dosearch'] = $options['filter_search'];
			} else {
				$app->enqueueMessage(JText::_('Search') . ': ' . sprintf(JText::_('Invalid RegExp'), htmlentities($options['filter_search'])), 'error');
				$options['filter_search'] = '';
			}
		}
		$lists['search'] = '<input name="filter_search" id="filter_search" class="inputbox" "type="text" value="'.htmlspecialchars($options['filter_search'],ENT_QUOTES).'" onchange="this.form.submit();" size="15" />';

		// build the filter_state select box
		$extra = 'class="inputbox" size="1" onchange="document.adminForm.submit();"';
		$sel_state[] = JHTML::_('select.option',  '*', JText::_('Any State'));
		$sel_state[] = JHTML::_('select.option',  'P', JText::_('Published'));
		$sel_state[] = JHTML::_('select.option',  'U', JText::_('Not Published'));
		$lists['state'] = JHTML::_('select.genericlist',  $sel_state, 'filter_state', $extra, 'value', 'text', $options['filter_state']);

		// build the filter_status select box
		$sel_status[] = JHTML::_('select.option',  '*', JText::_('Any Status'));
		$sel_status[] = JHTML::_('select.option',  'NS', JText::_('Not Started'));
		$sel_status[] = JHTML::_('select.option',  'IP', JText::_('In Progress'));
		$sel_status[] = JHTML::_('select.option',  'C', JText::_('Complete'));
		if ($options['isReference']) {
			$options['filter_status'] = '*';
		}
		if ($options['lang'] == $options['refLang']) {
			$extra .= ' disabled';
		}
		$lists['status'] = JHTML::_('select.genericlist',  $sel_status, 'filter_status', $extra, 'value', 'text', $options['filter_status']);

		// create objects for loading data
		$refLangLoader = new JLanguage($options['refLang']);
		$LangLoader = ($options['lang'] == $options['refLang']) ? $refLangLoader : new JLanguage($options['lang']);

		// load all the the ini filenames (published or unpublished) from the reference directory
		// load the same from the selected language directory
		$refLangFiles = JFolder::files($options['refLangPath'] , '^(xx|'.$options['refLang'].')[.].*ini$');
		if ($options['isReference']) {
			$LangFiles = array_flip($refLangFiles);
		} else {
			$LangFiles = JFolder::files($options['langPath'] , '^(xx|'.$options['lang'].')[.].*ini$');
			$LangFiles = array_flip($LangFiles);
		}

		// build a composite filename list, keyed using the filename without language tag
		$allFiles = array();
		foreach ($refLangFiles as $v) {
			$k = preg_replace('/^(xx[.])*'.$options['refLang'].'[.]/','',$v);
			$allFiles[$k]['refLang'] = $v;
		}
		foreach ($LangFiles as $v=>$k) {
			$k = preg_replace('/^(xx[.])*'.$options['lang'].'[.]/','',$v);
			$allFiles[$k]['lang'] = $v;
		}

		// get default metadata for the selected language
		$xmlData = TranslationsHelper::getXMLMeta($options['langPath'].DS.$options['lang'].'.xml');

		// process the reference language INI files and compare them against the files for the selected language
		$rows = array ();
		$rowid = 1;
		foreach ($allFiles as $k=>$v)	{

			// get the content, bare filename, Meta and Strings from the reference language INI file
			// in some cases there may not be a reference language INI file
			if (isset($v['refLang'])) {
				$refContent = file($options['refLangPath'].DS.$v['refLang']);
                $refFileName = (substr($v['refLang'],0,3)=='xx.') ?  substr($v['refLang'],3) : $v['refLang'];
				$fileName = $options['lang'] . substr($refFileName,$options['refLangLen']);
				$refStrings = array();
				$refMeta  = TranslationsHelper::getINIMeta($refContent, $refStrings);
			} else {
				$refContent = array();
				$fileName = (substr($v['lang'],0,3)=='xx.') ?  substr($v['lang'],3) : $v['lang'];
				$refFileName = $options['refLang'] . substr($fileName,$options['langLen']);
				$refStrings = array();
				$refMeta  = array(
					'author' => '',
					'date' => '',
					'strings' => '',
					'time' => '',
					'version' => ''
				);
			}

			// initialise the row
			$row = new StdClass();
			$row->author 		= $refMeta['author'];
			$row->bom 			= 'UTF-8';
			$row->checkedout 	= 0;
			$row->changed 		= 0;
			$row->date 			= $refMeta['date'];
			$row->extra 		= 0;
			$row->filename 		= $fileName;
			$row->id 			= $rowid++;
			$row->name 			= substr($row->filename,($options['langLen']+1),-4);
			$row->refexists 	= intval(isset($v['refLang']));
			$row->reffilename 	= $refFileName;
			$row->refstrings	= $refMeta['strings'];
			$row->searchfound 	= 0;
			$row->status 		= 0;
			$row->strings 		= $refMeta['strings'];
			$row->time 			= $refMeta['time'];
			$row->unchanged		= 0;
			$row->unpub_filename = 'xx.'.$row->filename;
			$row->version 		= $refMeta['version'];

			// 1: file is published
			// 2: file is unpublished
			// 3: file does not exist
			if (JFile::exists($options['langPath'].DS.$row->filename)) {
				$row->exists 		= 1;
				$row->path_file 	= $options['langPath'].DS.$row->filename;
				$row->published 	= 1;
				$row->writable 		= is_writable($row->path_file);
			} else if (JFile::exists($options['langPath'].DS.$row->unpub_filename)) {
				$row->exists 		= 1;
				$row->path_file 	= $options['langPath'].DS.$row->unpub_filename;
				$row->published 	= 0;
				$row->writable 		= is_writable($row->path_file);
			} else {
				$row->author 		= '';
				$row->date	 		= '';
				$row->exists 		= 0;
				$row->path_file 	= $options['langPath'].DS.$row->unpub_filename;
				$row->published 	= 0;
				$row->status 		= 0;
				$row->version 		= '';
				$row->writable 		= 1;
			}

			// get the checkout status of the selected file
			if ($content = @file_get_contents($options['langPath'].DS.'chk.'.$row->filename)) {
				$row->checkedout = ((strpos($content,'#'.$userid.'#')) || (strpos($content,'#0#'))) ? 0 : 1;
			}

			// scan an existing language file
			if ((!$options['isReference']) && ($row->exists)) {
				$fileContent = file($row->path_file);
				$fileStrings = array();
				$fileMeta = TranslationsHelper::getINIMeta($fileContent, $fileStrings, $refStrings);
				if ($fileMeta['bom'] == 'UTF-8') {
					foreach ($fileMeta as $k=>$v) {
						$row->{$k} = $v;
					}
				} else {
					$row->bom = $fileMeta['bom'];
					$row->writable = 0;
				}
			} else {
				$fileContent = array();
				$fileStrings = array();
				$fileMeta = array();
			}

			// search the files
			// $refContent and $fileContent are arrays containing each line of the reference and translation file
			if ($options['dosearch']) {
				$row->searchfound_ref = preg_match_all($options['dosearch'], implode("\n",$refContent), $arr);
                if (! $options['isReference']) {
                    $row->searchfound_tran = preg_match_all($options['dosearch'], implode("\n",$fileContent), $arr);
                } else {
                    $row->searchfound_tran = $row->searchfound_ref;
                }
				$row->searchfound = $row->searchfound_ref + $row->searchfound_tran;
			}

			// set the datetime
			$row->datetime = $row->date.$row->time;

			// change the name
			if ($row->name == '') {
				$row->name = ' [core]';
			}

			// store the file
			$rows[$row->name] = $row;
		}


		// build the fileset totals and filter out rows we don't need/want
		$options['fileset-files'] 	= 0;
		$options['fileset-exists'] 	= 0;
		$options['fileset-published'] = 0;
		$options['fileset-refstrings'] = 0;
		$options['fileset-changed'] = 0;
		foreach($rows as $k=>$row) {
			// add to totals
			$options['fileset-files']++;
			$options['fileset-exists'] 		+= $row->exists;
			$options['fileset-published'] 	+= $row->published;
			$options['fileset-refstrings'] 	+= $row->refstrings;
			$options['fileset-changed'] 	+= $row->changed;

			// filter out searched items
			// filter out published or unpublished items
			// filter out status of items
			if 	(
				(($options['dosearch']) && ($row->searchfound == 0))
			||	(($options['filter_state']=='P') && ($row->published <> 1))
			||	(($options['filter_state']=='U') && ($row->published <> 0))
			||	(($options['filter_status']=='NS') && ($row->status > 0))
			||	(($options['filter_status']=='IP') && (($row->status <= 0)||($row->status >= 100)))
			||	(($options['filter_status']=='C') && ($row->status < 100))
				) {
				unset($rows[$k]);
			}
		}

		// set fileset status
		if ($options['fileset-changed'] == 0) {
			$options['fileset-status'] = 0;
		}
		if ($options['fileset-refstrings'] == $options['fileset-changed']) {
			$options['fileset-status'] = 100;
		} else {
			$options['fileset-status'] = floor(($options['fileset-changed']/$options['fileset-refstrings'])*100);
		}

		// build the pagination
		jimport('joomla.html.pagination');
		$pageNav = new JPagination(count($rows), $options['limitstart'], $options['limit'], 'index.php?option=com_localise&amp;task=files');

		// sort the $rows array
		$order_Int = (strtolower($lists['order_Dir'])=='desc') ? -1 : 1;
		JArrayHelper::sortObjects($rows, $lists['order'], $order_Int);

		// slice the array so we only show one page
		$rows = array_slice($rows, $pageNav->limitstart, $pageNav->limit);

		// call the html view
		$view = $this->getView($this->_name, 'html');
		$view->setLayout('files');
		$view->assignRef('data', $rows);
		$view->assignRef('options', $options);
		$view->assignRef('lists', $lists);
		$view->assignRef('pagenav', $pageNav);
		$view->display();
	}

	/**
	* Show a List of installed Languages (XML files)
	*/
	function languages()
	{
		// variables
		$app		= &JFactory::getApplication();
		$options =& $this->getOptions();

		// default languages
		$params = JComponentHelper::getParams('com_languages');
		$default['A'] = $params->get('administrator','en-GB');
		$default['I'] = $params->get('installation','en-GB');
		$default['S'] = $params->get('site','en-GB');

		// validate all the filters (specific to this view)
		// each filter key has a list of allowed values; the first is the default value
		// a blank value skips validation
		// the  key "limit" allows any integer
		$allowed = array(
			'filter_client' 	=> '*|' . implode('|', array_keys($options['clients'])),
			'filter_order' 		=> 'tag',
			'filter_order_Dir' 	=> 'asc|desc',
			'limit' 			=> $app->getCfg('list_limit')
		);
		$filters = $this->_buildfilters($allowed, 'com_localise.languages.');

		// copy to $options
		$options = array_merge($options, $filters);

		// copy to $lists
		$lists['order'] 	= $options['filter_order'];
		$lists['order_Dir'] = $options['filter_order_Dir'];

		// get the list of languages
		$rows = array();
		foreach ($options['languages'] as $k=>$v) {
			$row = new StdClass();
			$row->tag = substr($k,2);
			$row->client = strtoupper($k{0});
			$row->client_lang = $k;
			$row->filename = $row->tag . '.xml';

			// check filter
			if ($options['filter_client']!='*') {
				if ($row->client != $options['filter_client']) {
					continue;
				}
			}

			// check default status
			$row->isdefault = intval($default[$row->client]==$row->tag);

			// get the directory path
			if ($k{0}=='A') {
				$path = JPATH_ADMINISTRATOR;
				$row->client_name = JText::_('Administrator');
			} else if ($k{0}=='I') {
				$path = JPATH_INSTALLATION;
				$row->client_name = JText::_('Installation');
			} else {
				$path = JPATH_SITE;
				$row->client_name = JText::_('Site');
			}
			$path .= DS.'language'.DS.$row->tag;

			// count the number of INI files (published or unpublished)
			$row->files = count(JFolder::files($path, '(xx[.]|^)'.$row->tag.'.*ini$'));

			// load and add XML attributes
			// force the tag
		    $data = TranslationsHelper::getXMLMeta($path.DS.$row->filename);
			$data['tag'] = $row->tag;
 			foreach($data as $k2=>$v2) {
				$row->$k2 = $v2;
			}

			// add to rows
			$rows[] = $row;
		}

		// build the pagination
		jimport('joomla.html.pagination');
		$pageNav = new JPagination(count($rows), $options['limitstart'], $options['limit'], 'index.php?option=com_localise');

		// sort the $rows array
		$order_Int = (strtolower($lists['order_Dir'])=='desc') ? -1 : 1;
		JArrayHelper::sortObjects($rows, $lists['order'], $order_Int);

		// slice the array so we only show one page
		$rows = array_slice($rows, $pageNav->limitstart, $pageNav->limit);

		// call the html view
		$view = $this->getView($this->_name, 'html');
		$view->setLayout('languages');
		$view->assignRef('data', $rows);
		$view->assignRef('options', $options);
		$view->assignRef('lists', $lists);
		$view->assignRef('pagenav', $pageNav);
		$view->display();
	}

	/**
	* Package INI files into an installation zip file
	*/
	function package()
	{
		// variables
		$app		= &JFactory::getApplication();
		$options =& $this->getOptions();
		$files = array();

		// set the zip path
		// optionally change the tag if there is a [tag=xx-XX] in the path
		$zippath = '/' . $options['config']->get('zippath', 'tmp/[tag].[client].zip');
		$ziptag = $options['lang'];
		if (preg_match('/\[tag=([^\]]*)\]/',$zippath,$match)) {
			$zippath = str_replace($match[0],'[tag]',$zippath);
			if (preg_match('/^[a-z]{2}-[a-z]{2}$/i',$match[1])) {
				$ziptag = strtolower(substr($match[1],0,2)) . '-' . strtoupper(substr($match[1],-2));
				$app->enqueueMessage(sprintf(JText::_('ZIP Translate Tag'), $options['lang'], $ziptag));
			}
		}

		// process all the files in the selected language directory into an array ready to be packaged
		// translate the language tag if configured
		jimport('joomla.filesystem.file');
		foreach (JFolder::files($options['langPath']) as $k=>$filename) {
			// 1: grab the XML data and info
			// 2a: skip checkout marker files
			// 2b: grab the INI data into the files array
			if ($filename == $options['lang'].'.xml') {
				$xmlname = $filename;
				$xmldata = file_get_contents($options['langPath'].DS.$filename);
				$xmltime = filemtime($options['langPath'].DS.$filename);
				$xml = TranslationsHelper::getXMLMeta($options['langPath'].DS.$filename);
				$xml['client'] 	= $options['clientKey'];
				$xml['tag'] 	= $ziptag;
				$xmlname = str_replace($options['lang'],$ziptag,$xmlname);
				$xmldata = str_replace($options['lang'],$ziptag,$xmldata);
			} else if (substr($filename,-4)=='.ini') {
				if (substr($filename,0,4)=='chk.') {
					continue;
				} else {
					$k = (substr($filename,0,3)=='xx.') ? substr($filename,3) : $filename;
					$k = str_replace($options['lang'],$ziptag,$k);
					$files[$k]['name'] = $k;
					$files[$k]['data'] = file_get_contents($options['langPath'].DS.$filename);
					$files[$k]['time'] = filemtime($options['langPath'].DS.$filename);
				}
			}
		}

		// check we have XML data and files
		if (!isset($xml)) {
			JError::raiseNotice(500, sprintf(JText::_('ZIP No XML'), $options['clientKey'].'DS'.$options['lang'].'.xml' ));
		} else if (!count($files)) {
			JError::raiseNotice(500, sprintf(JText::_('ZIP No Files'), $options['clientKey']));
		} else {

			// sort the files
			ksort($files);

			// build the XML install file
			$install = '<?xml version="1.0" encoding="utf-8" ?>
<install version="1.5" client="' . $xml['client'] .'" type="language">
    <name>' . $xml['name'] .'</name>
    <tag>' . $xml['tag'] .'</tag>
    <version>' . $xml['version'] .'</version>
    <creationDate>' . $xml['creationDate'] .'</creationDate>
    <author>' . $xml['author'] .'</author>
    <authoremail>' . $xml['authorEmail'] .'</authoremail>
    <authorurl>' . $xml['authorUrl'] .'</authorurl>
    <copyright>' . $xml['copyright'] .'</copyright>
    <license>' . $xml['license'] .'</license>
    <description>' . $xml['description'] .'</description>
    <files>';
	foreach ($files as $k=>$v) {
		$install .= '
		<filename>' . $v['name'] . '</filename>';
	}
	$install .= '
		<filename file="meta">' . $xmlname . '</filename>
	</files>
	<params />
</install>';

			// finish the files array
			$files['xml']['name'] = $xmlname;
			$files['xml']['data'] = $xmldata;
			$files['xml']['date'] = $xmltime;
			$files['install']['name'] = 'install.xml';
			$files['install']['data'] = $install;
			$files['install']['date'] = time();

			// configure the package filename and type
			$type = substr($zippath, strrpos($zippath, '.') + 1);
			$zippath = str_replace('[client]', $xml['client'], $zippath);
			$zippath = str_replace('[tag]', $xml['tag'], $zippath);
			$ziproot = JPATH_ROOT.$zippath;
			$zipfile = substr($zippath, strrpos($zippath, DS) + 1);
			$ziplink = JURI::root() . $zipfile;

			// run the packager
			jimport('joomla.filesystem.archive');
			if (!$packager =& JARchive::getAdapter($type)) {
				JError::raiseWarning(500, sprintf(JText::_('ZIP Adapter Failure'), $type));
			} else if ($packager->create($ziproot, $files, array())) {
				$app->enqueueMessage(sprintf(JText::_('ZIP Create Success'), $ziplink, $zipfile));
			} else {
				JError::raiseNotice(500, sprintf(JText::_('ZIP Create Failure'), $ziplink));
			}

		}

		// Redirect to the default page
		$app->redirect('index.php?option=com_localise');
	}
}