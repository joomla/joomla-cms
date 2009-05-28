TinyBrowser 1.41 - A TinyMCE file browser (C) 2008  Bryn Jones
(author website - http://www.lunarvis.com)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.


TinyBrowser Features:
=====================

- Integrates as a custom file browser within TinyMCE for image, media and 'all'
  file types, or can work in stand-alone mode

- Adobe Flash based file uploader, supporting multiple file selection and upload
  with file type and size filtering (permission based)

- Browse files with a list view or as thumbnails (images only), with optional
  pagination

- List view has image thumbnail on hover

- File display order customisable e.g. by name, type, size, date

- Find function to filter results by search string 

- Display detailed file information such as type, size and dimensions (images
  only)

- File deletion facility (permission based)

- File edit facility (permission based) - rename, resize and rotate (last two
  images only)
  
- Folder browsing and creation

- File storage location user definable for each file type

- Optional session control

- Multi-lingual support with language definition files

- Many user definable settings, all from central configuration file


TinyBrowser Background
======================

I created TinyBrowser as I couldn't find the right TinyMCE file browser for my
needs, particularly the ability to select and upload multiple files in an easy
way.


Version Notes
=============

TinyBrowser 1.41 - released 05/05/2009
--------------------------------------
New Features:
Added folder argument to tinyBrowserPopUp function, so that a sub-folder can be
selected when using TinyBrowser in standalone mode.
Added error logging - errors are output to error.log in tinybrowser directory.
Added Spanish, Croatian, Slovakian, Latvian, Czech, Italian, Hungarian, Swedish
and Russian language files (thanks to all who contributed).

Changes:
Prohibited files are no longer browseable (if the paths defined contained files
of this type).
Only defined file extensions of a file type are browseable and editable.
Changed stripos function to strpos to ensure PHP4 compatability.
Updated createfolder function to work recursively with PHP4.
Remember current folder after upload.
Changed filename clean regex to be less strict.

Bug Fixes:
Fixed transparency support for gif and png (thanks to Dirk Bohl).
Added 'no-cache' meta tag to ensure latest page is served by web browser.
Added another fix for no style issue with TinyMCE integration.
Security fix for browse type when set to non-existant value.
Security fix for malicious setting of folder get variable to relative values.

TinyBrowser 1.40 - released N/A
--------------------------------------
New Features:
Added multiple folder support with a separate Folders tab to allow folder
creation, renaming and deletion.
Added Move action to Edit tab, to allow moving of files between folders.
Added Danish language file.
Added 'delayprocess' to config file - this can be set if server response is
delayed, preventing uploaded files being processed correctly.
Added width and height config values for the TinyBrowser pop up window size.
Added 'cleanfilename' flag to config file, to remove disallowed characters
from filenames on upload (set to true by default).
  
Changes:
Added recursive flag to createfolder function, to allow TinyBrowser to 
create full upload path.
Changed default window size to 770px x 480px.
Minor code optimisation.
Prohibited files are now not browseable (if the paths defined contained files of
this type).
Now only defined file extensions of a file type are browseable and editable.
Changed stripos function call to strpos to ensure PHP4 compatability.
Updated createfolder function to work recursively with PHP4.
Remember current folder after upload.

Bug Fixes:
Fixed minor bug causing elementid to be lost after file upload in
stand-alone mode.
Fixed stand-alone javascript selectURL function bug that prevented 
TinyBrowser window close on selection. 
Fixed bug in Flash upload that prevented folder permission error reporting.
Fixed bug in image resize and rotate functions that converted all types
to jpeg.

TinyBrowser 1.33 - released 23/09/2008
--------------------------------------
New Features:
Added German, Finnish, Traditional and Simplified Chinese language files.
Added session control workaround for upload_file.php (called by Flash).

Changes:
Flash uploader has been modified to display the file type and also to fix
strange progress bar behaviour when uploading multiple files. As the
upload process is not concurrent, I have removed the individual progress
bars and replaced them with one (progress is still per file however).
When the Upload button is pressed all the buttons now disappear.

Bug Fixes:
Fixed minor bug that affected css layout after file upload.
Fixed bug introduced in 1.32 that prevented automatic image resize on
upload.
Added 'HTTP/1.1 200' response to upload_file.php script, to address
Flash bug in some Mac setups.
Added check for valid images in Browse and Edit tabs - non-image files
are still listed but error producing image properties code is bypassed.
	

TinyBrowser 1.32 - released 17/09/2008
--------------------------------------
New Features:
None.

Changes:
The upload path is now passed to the Flash upload widget, for better
compatibility with Firefox and Opera when you set your paths using session
variables (this is due to a Flash bug that loses session data).
Files are no longer uploaded to a temporary directory, so there's now no
folder creation during the upload process. Instead, file name extensions
are suffixed with an underscore until processed. (This renders them
useless until verification).
The thumbs directory has been changed to '_thumbs'.

Bug Fixes:
Fixed security hole - previously, it was possible to directly
submit files to the upload_file.php script. Due to the Flash session bug,
normal session control does not work. Instead, a hash string check has been
added to the upload_file.php script and the installation instructions
amended.
   
TinyBrowser 1.31 - released 16/09/2008
--------------------------------------
New Features:
None.

Changes:
Added prohibited files logic to the file_upload.php script (previously
located only in file_process.php script).
Changed duplicate file handling behaviour - TinyBrowser now discards
files with the same name.

Bug Fixes:
None.

TinyBrowser 1.30 - released 12/09/2008
--------------------------------------
New Features:
'Stand-alone' mode, for use without TinyMCE.
Optional and configurable pagination, to break results down into pages.
New configuration option, $tinybrowser['link'], allows the passback link to
be different to the upload path.
Added automatic creation of upload directories (with definable chmod for Unix
servers).
Added alert messages for errors and actions.
Multi-lingual support - credit to Francis Rebouças 
(francisreboucas[at]gmail[dot]com) for idea, design, implementation and 
Portugese language file.
Experimental support for FCKeditor.

Changes:
Rationalised TinyBrowser file and folder names and structure (brought in line
with TinyMCE plugin specification).
Revised documentation.
After file upload user is now directed back to upload tab instead of the
browse tab.
   
Bug Fixes:
Fixed security hole - prohibited file extensions not obeyed due to error in
inherited code. 
Fixed small issue with edit / delete permissions (not consistently hiding
edit tab).

TinyBrowser 1.20 - released 20/08/2008
--------------------------------------
New Features:
Added thumbnail on hover for detail view (images only). Note: not working in
IE6.
Added Edit tab - allows file rename, resize and rotate (last two for images
only). Also moved delete action to here.
Added configurable automatic image resize on upload.

Changes:
Moved file thumbnail generation to upload process.
Changed table css to match Flash upload.
Improved thumbnail view layout.
Removed the form select elements for sort by and type, and made the table
column headers clickable.

Bug Fixes:
Changed default $tinymce['docroot'] value to
rtrim($_SERVER['DOCUMENT_ROOT'],'/') - this fixes an issue with server setups
that return a value with a '/' suffix.
Removed .htaccess file after various bug reports - doesn't appear to be
required for majority of server setups.
Fixed silly bug with thumbnail urls that could prevent generation and viewing
under some server setups.
Fixed various other minor bugs and tidied code.

TinyBrowser 1.10 
----------------
Adjusted layout of file upload.
Added facility to limit permitted file upload size (separate values for each
file type).
Amended installation instructions for clarity.
Tested as working in Opera 9.

TinyBrowser 1.00
----------------
Tested in Firefox 2 and 3, Internet Explorer 6 and 7 and Safari 3.
Requires Adobe Flash Player 9.


Requirements
============

Adobe Flash Player 8 +
PHP enabled server

Supported browsers:
Internet Explorer 6 +
Firefox 2 +
Safari 3 +
Opera 9 +
Google Chrome


Language Definition Files
=========================

English (en)
Chinese Simplified (zh-cn)
Chinese Traditional (zh-tw)
Croatian (hr)
Czech (cs)
Danish (da)
Dutch (nl)
Finnish (fi)
French (fr)
German (de)
Hungarian (hu)
Italian (it)
Latvian (lv)
Polish (pl)
Portuguese (pt)
Russian (ru)
Slovak (sk)
Spanish (es)
Swedish (sv)



Known Issues
============

None.


Troubleshooting
===============

If you receive a 403, 406 or 412 status error on uploading files, please create
an .htaccess file in your tinybrowser directory with the following contents:

SecFilterEngine Off
SecFilterScanPOST Off

If you use Linux and the Squid proxy, you need to add a "ignore_expect_100 on"
flag to the squid config file to avoid a 417 status error.


TinyBrowser Installation Method 1
=================================

The standard TinyBrowser installation, this integrates TinyBrowser as a custom
file browseer with TinyMCE.

1) Copy the tinybrowser folder and contents to your TinyMCE plugins directory.

2) Place the following javascript link after the link to TinyMCE (tiny_mce.js):

   <script type="text/javascript"
   src="/tiny_mce/plugins/tinybrowser/tb_tinymce.js.php"></script>

   ***NOTE:*** The above link assumes TinyMCE is installed in your website root
   directory, you will need to amend the link to your specific setup!

3) Add this line to your TinyMCE init:

   file_browser_callback : "tinyBrowser"

4) Edit the TinyBrowser configuration file (config_tinybrowser.php). The most
   important settings are the file paths (these will be automatically created on
   your server by TinyBrowser if they do not exist) and also the 'obfuscate'
   property, which should be set to a random value.

   ***NOTE:*** If your server is Unix-based. you may wish to modify the 
   $tinybrowser['unixpermissions'] config value, which decides permissions.

5) All done! Now you will see a browse button in the TinyMCE dialog windows for
   plugins like image, media and link - just click this button and TinyBrowser
   will appear.


TinyBrowser Installation Method 2
=================================

This installation allows TinyBrowser to be used in 'stand-alone' mode, for
integration with any web application.

1) Copy the tinybrowser folder and contents to your server.

2) Place the following javascript link within the <head> tag on the page you
   require TinyBrowser:

   <script language="javascript" type="text/javascript"
   src="/tinybrowser/tb_standalone.js.php"></script>

   ***NOTE:*** The above link assumes TinyBrowser is installed in your website
   root directory, you will need to amend the link to your specific setup!

3) Edit the TinyBrowser configuration file (config_tinybrowser.php). The most
   important settings are the file paths (these will be automatically created on
   your server by TinyBrowser if they do not exist) and also the 'obfuscate'
   property, which should be set to a random value.

   ***NOTE:*** If your server is Unix-based. you may wish to modify the
   $tinybrowser['unixpermissions'] config value, which decides permissions.

4) To launch TinyBrowser use the following javascript function:

   tinyBrowserPopUp('type','elementid');

   'type' can contain 'image', 'media' or 'file' - corresponding to the type of
   file you want TinyBrowser to manage.

   'elementid' is the id of the page element you want populate with the file url
   TinyBrowser returns - this is generally a form text input. If you want to
   immediately display the image then create an <img> tag with the same element
   id, only suffixed with img - e.g. elementidimg.

   
TinyBrowser Installation Method 3
=================================

This installation method integrates TinyBrowser as a custom file browser with
FCKeditor.

1) Copy the tinybrowser folder and contents to your server.

2) Edit your fckconfig.js file as follows (replace existing lines).

   To enable TinyBrowser for files:
   FCKConfig.LinkBrowserURL = '/yourtinybrowserurl/tinybrowser.php?type=file';

   To enable TinyBrowser for images:
   FCKConfig.ImageBrowserURL = '/yourtinybrowserurl/tinybrowser.php?type=image';

   To enable TinyBrowser for Flash:
   FCKConfig.FlashBrowserURL = '/yourtinybrowserurl/tinybrowser.php?type=media';

   If you wish to disable the default FCKeditor file uploads (recommended), set
   the following:
   FCKConfig.LinkUpload = false;
   FCKConfig.ImageUpload = false;
   FCKConfig.FlashUpload = false;

3) Edit the TinyBrowser configuration file (config_tinybrowser.php).

   Change the $tinybrowser['integration'] line:
   $tinybrowser['integration'] = 'fckeditor';

   The other most important settings are the file paths (these will be
   automatically created on your server by TinyBrowser if they do not exist) and
   the 'obfuscate' property, which should be set to a random value.

   ***NOTE:*** If your server is Unix-based. you may wish to modify the
   $tinybrowser['unixpermissions'] config value, which decides permissions.

4) All done! Now when you click the Browse Server button in the FCKeditor dialog
   windows for image, Flash and link, TinyBrowser will appear instead of the
   standard FCKeditor file browser.


Contact
=======

Please notify me by email bryn[at]lunarvis[dot]com if you notice any bugs or
have ideas for new features.

-----------------------------
File Last Modified 05/05/2009