<?php
/**
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The HTML Joomla Core Site Configuration View
 *
 * @package		Joomla.Installation
 * @since		1.6
 */
class JInstallationViewSite extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 */
	public function display($tpl = null)
	{
		$state = $this->get('State');
		$form  = $this->get('Form');
		$sample_installed = $form->getValue('sample_installed', null, 0);

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->state = $state;
		$this->form  = $form;
		$this->sample_installed = $sample_installed;

		parent::display($tpl);
	}
        
        public function validateInput () {
            jimport('joomla.filesystem.file');
            jimport('joomla.image.image');
            
            $app = JFactory::getApplication();
            
            $fm  = JRequest::getVar('jform', NULL, 'post');
            
            if (!empty($fm)) {
                $site_name          = $fm['site_name'];
                $site_metadesc      = $fm['site_metadesc'];
                $site_metakeys      = $fm['site_metakeys'];
                $site_offline       = $fm['site_offline'];
                $admin_email        = $fm['admin_email'];
                $admin_user         = $fm['admin_user'];
                $admin_password     = $fm['admin_password'];
                $admin_password2    = $fm['admin_password2'];
            } else {
                $site_name          = NULL;
                $site_metadesc      = NULL;
                $site_metakeys      = NULL;
                $site_offline       = NULL;
                $admin_email        = NULL;
                $admin_user         = NULL;
                $admin_password     = NULL;
                $admin_password2    = NULL;
            }
            
            $files              = JRequest::getVar( 'jform', NULL, 'files' );
            
            $msg    = "Usted debe corregir y completar los siguientes datos antes de continuar:<br />";
            $disc   = true;
            
            if (empty($site_name)) {
                $msg .= "- Debe ingresar un nombre para el sitio.<br />";
                $disc   &= false;
            }
            
            if (empty($admin_email)) {
                $msg .= "- Debe ingresar el correo del administrador.<br />";
                $disc   &= false;
            }
            
            if (empty($admin_user)) {
                $msg .= "- Debe ingresar un nombre de usuario.<br />";
                $disc   &= false;
            }
            
            if (empty($admin_password)) {
                $msg .= "- Debe ingresar una contraseña.<br />";
                $disc   &= false;
            }
            
            if (empty($admin_password2)) {
                $msg .= "- Debe confirmar la contraseña.<br />";
                $disc   &= false;
            }
            
            if ($admin_password != $admin_password2) {
                $msg .= "- Las contraseñas no coinciden.<br />";
                $disc   &= false;
            }
            
            if ($disc) {
                if (!empty($files['tmp_name']['site_logo'])) {
                    // Set the path to the file
                    $file = $files['tmp_name']['site_logo'];

                    // Instantiate our JImage object
                    $image = new JImage($file);

                    // Get the file's properties
                    $properties = $image->getImageFileProperties($file);

                    // Resize the file as a new object
                    $logo1 = $image->resize('200px', '74px', true);
                    $logo3 = $image->resize('250px', '30px', true);

                    // Determine the MIME of the original file to get the proper type for output
                    $mime = $properties->mime;

                    if ($mime == 'image/jpeg')
                    {
                        $type = IMAGETYPE_JPEG;
                    }
                    elseif ($mime == 'image/png')
                    {
                        $type = IMAGETYPE_PNG;
                    }
                    elseif ($mime == 'image/gif')
                    {
                        $type = IMAGETYPE_GIF;
                    }

                    // Store the resized image to a new file
                    $logo1->toFile(JPATH_ROOT.DS.'images'.DS.'logos'.DS.'jokte-logo-front.png', $type);
                    $logo3->toFile(JPATH_ROOT.DS.'administrator'.DS.'templates'.DS.'storkantu'.DS.'images'.DS.'logo.png', $type);
                }
                
                $site_name          = $fm['site_name'];
                $site_metadesc      = $fm['site_metadesc'];
                $site_metakeys      = $fm['site_metakeys'];
                $site_offline       = $fm['site_offline'];
                $admin_email        = $fm['admin_email'];
                $admin_user         = $fm['admin_user'];
                $admin_password     = $fm['admin_password'];
                $admin_password2    = $fm['admin_password2'];
                
                $fmData  = '&jform[site_name]=' . $site_name;
                $fmData .= '&jform[site_metadesc]=' . $site_metadesc;
                $fmData .= '&jform[site_metakeys]=' . $site_metakeys;
                $fmData .= '&jform[site_offline]=' . $site_offline;
                $fmData .= '&jform[admin_email]=' . $admin_email;
                $fmData .= '&jform[admin_user]=' . $admin_user;
                $fmData .= '&jform[admin_password]=' . $admin_password;
                $fmData .= '&jform[admin_password2]=' . $admin_password2;
                
                JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
                
                $app->redirect('?view=remove&task=setup.saveconfig' . $fmData . "&$tkn=1");
                
            } elseif (!empty($fm)) {
                
                JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
                $app->redirect('?view=site', $msg, 'warning');
            }
        }
}