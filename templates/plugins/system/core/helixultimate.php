<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined('_JEXEC') or die ();

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filter.filteroutput');

use Joomla\Utilities\ArrayHelper;

class HelixUltimate
{
    public $params;

    private $doc;

    public $app;

    public $input;

    public $template;

    public $template_folder_url;

    private $in_positions = array();

    public $loadFeature = array();

    public function __construct()
    {
        $this->app      = JFactory::getApplication();
        $this->input    = $this->app->input;
        $this->doc      = JFactory::getDocument();
        $this->template = $this->app->getTemplate(true);
        $this->params   = $this->template->params;
        $this->get_template_uri();
    }

    public function bodyClass($class = '')
    {
        $menu            = $this->app->getMenu()->getActive();
        $bodyClass       = 'site helix-ultimate ' . htmlspecialchars(str_replace('_', '-', $this->input->get('option', '', 'STRING')));
        $bodyClass      .= ' view-' . htmlspecialchars($this->input->get('view', '', 'STRING'));
        $bodyClass      .= ' layout-' . htmlspecialchars($this->input->get('layout', 'default', 'STRING'));
        $bodyClass      .= ' task-' . htmlspecialchars($this->input->get('task', 'none', 'STRING'));
        $bodyClass      .= ' itemid-' . (int) $this->input->get('Itemid', '', 'INT');
        $bodyClass      .= ($this->doc->language) ? ' ' . $this->doc->language : '';
        $bodyClass      .= ($this->doc->direction) ? ' ' . $this->doc->direction : '';
        $bodyClass      .= ($this->params->get('sticky_header')) ? ' sticky-header' : '';
        $bodyClass      .= ($this->params->get('boxed_layout', 0)) ? ' layout-boxed' : ' layout-fluid';
        $bodyClass      .= ' offcanvas-init offcanvs-position-' . $this->params->get('offcanvas_position', 'right');
        
        if (isset($menu) && $menu)
        {
            if ($menu->params->get('pageclass_sfx'))
            {
                $bodyClass .= ' ' . $menu->params->get('pageclass_sfx');
            }
        }
        $bodyClass      .= (!empty($class)) ? ' ' . $class : '';

        return $bodyClass;
    }

    public function head()
    {

        $doc = JFactory::getDocument();

        $view = $this->input->get('view', '', 'STRING');
        $layout = $this->input->get('layout', 'default', 'STRING');

        JHtml::_('jquery.framework');
        JHtml::_('bootstrap.framework');
        unset($doc->_scripts[JURI::base(true) . '/media/jui/js/bootstrap.min.js']);
        unset($doc->_scripts[JURI::base(true) . '/media/jui/js/bootstrap-tooltip-extended.min.js']);

        $webfonts = array();

        if ($this->params->get('enable_body_font'))
        {
            $webfonts['body'] = $this->params->get('body_font');
        }

        if ($this->params->get('enable_h1_font'))
        {
            $webfonts['h1'] = $this->params->get('h1_font');
        }

        if ($this->params->get('enable_h2_font'))
        {
            $webfonts['h2'] = $this->params->get('h2_font');
        }

        if ($this->params->get('enable_h3_font'))
        {
            $webfonts['h3'] = $this->params->get('h3_font');
        }

        if ($this->params->get('enable_h4_font'))
        {
            $webfonts['h4'] = $this->params->get('h4_font');
        }

        if ($this->params->get('enable_h5_font'))
        {
            $webfonts['h5'] = $this->params->get('h5_font');
        }

        if ($this->params->get('enable_h6_font'))
        {
            $webfonts['h6'] = $this->params->get('h6_font');
        }

        if ($this->params->get('enable_navigation_font'))
        {
            $webfonts['.sp-megamenu-parent > li > a, .sp-megamenu-parent > li > span, .sp-megamenu-parent .sp-dropdown li.sp-menu-item > a'] = $this->params->get('navigation_font');
        }

        if ($this->params->get('enable_custom_font') && $this->params->get('custom_font_selectors'))
        {
            $webfonts[$this->params->get('custom_font_selectors')] = $this->params->get('custom_font');
        }

        // Favicon
        if ($favicon = $this->params->get('favicon'))
        {
            $doc->addFavicon(JURI::base(true) . '/' . $favicon);
        }
        else
        {
            $doc->addFavicon($this->template_folder_url . '/images/favicon.ico');
        }

        $this->addGoogleFont($webfonts);

        $doc->addScriptdeclaration('template="'. $this->template->template .'";');

        echo '<jdoc:include type="head" />';

        $this->add_css('bootstrap.min.css');

        if($view == 'form' && $layout == 'edit')
        {
            $doc->addStylesheet( \JURI::root(true) . '\plugins/system/helixultimate/assets/css/frontend-edit.css');
        }
        
        $this->add_js('popper.min.js, bootstrap.min.js');
    }

    public function add_css($css_files = '', $options = array(), $attribs = array())
    {
        $files = array(
            'resource' => $css_files,
            'options'  => $options,
            'attribs'  => $attribs
        );

        $this->put_css_js_file($files,'css');
    }

    public function add_js($js_files = '', $options = array(), $attribs = array())
    {
        $files = array(
            'resource' => $js_files,
            'options'  => $options,
            'attribs'  => $attribs
        );

        $this->put_css_js_file($files,'js');
    }


    private function put_css_js_file($files = array(), $file_type = '')
    {
        $files_folder_path = \JPATH_THEMES . '/' . $this->template->template . '/'. $file_type .'/';
        $file_list = explode(',',$files['resource']);

        foreach( $file_list as $file )
        {
            if (empty($file)) continue;
            $file = trim($file);
            $file_path = $files_folder_path . $file;

            if (\JFile::exists($file_path))
            {
                $file_url = \JURI::base(true) . '/templates/' . $this->template->template . '/'. $file_type .'/' . $file;
            }
            else if (\JFile::exists($file))
            {
                $file_url = $file;
            }
            else
            {
                continue;
            }

            if($file_type == 'js')
            {
                $this->doc->addScript($file_url, $files['options'], $files['attribs']);
            }
            else
            {
                $this->doc->addStyleSheet($file_url, $files['options'], $files['attribs']);
            }
        }
    }

    private function get_template_uri()
    {
        $this->template_folder_url = \JURI::base(true) . '/templates/' . $this->template->template;
    }

    private function include_features()
    {
        $folder_path     = JPATH_THEMES . '/' . $this->template->template . '/features';

        if (JFolder::exists($folder_path))
        {
            $files = JFolder::files($folder_path, '.php');

            if (count($files))
            {
                foreach ($files as $key => $file)
                {
                    include_once $folder_path . '/' . $file;

                    $file_name = JFile::stripExt($file);
                    $class = 'HelixUltimateFeature' . ucfirst($file_name);
                    $feature_obj = new $class($this->params);
                    $position = $feature_obj->position;
                    $load_pos = (isset($feature_obj->load_pos) && $feature_obj->load_pos) ? $feature_obj->load_pos : '';

                    $this->in_positions[] = $position;
                    if (!empty($position))
                    {
                        $this->loadFeature[$position][$key]['feature'] = $feature_obj->renderFeature();
                        $this->loadFeature[$position][$key]['load_pos'] = $load_pos;
                    }
                }
            }
        }
    }

    public function render_layout()
    {
        $this->add_css('custom.css');
        $this->add_js('custom.js');
        $this->include_features();

        $layout = ($this->params->get('layout'))? $this->params->get('layout') : [];
        
        if(!empty($layout))
        {
            $rows   = json_decode($layout);
        }
        else
        {
            $layout_file = JPATH_SITE . '/templates/' . $this->template->template . '/options.json';
            if (!JFile::exists($layout_file))
            {
                die('Default Layout file is not exists! Please goto to template manager and create a new layout first.');
            }
            $layout_data = json_decode(JFile::read($layout_file));
            $rows = json_decode($layout_data->layout);
        }

        $output = $this->get_recursive_layout($rows);

        echo $output;
    }

    private function get_recursive_layout($rows = array())
    {
        if(empty($rows) || !is_array($rows))
        {
            return;
        }

        $option      = $this->app->input->getCmd('option', '');
        $view        = $this->app->input->getCmd('view', '');
        $pagebuilder = false;
        $output = '';

        if ($option == 'com_sppagebuilder')
        {
            $pagebuilder = true;
        }

        $themepath      = JPATH_THEMES . '/' . $this->template->template;
        $carea_file     = $themepath . '/html/layouts/helixultimate/frontend/conponentarea.php';
        $module_file    = $themepath . '/html/layouts/helixultimate/frontend/modules.php';
        $lyt_thm_path   = $themepath . '/html/layouts/helixultimate/';

        $layout_path_carea  = (file_exists($carea_file)) ? $lyt_thm_path : JPATH_ROOT .'/plugins/system/helixultimate/layouts';
        $layout_path_module = (file_exists($module_file)) ? $lyt_thm_path : JPATH_ROOT .'/plugins/system/helixultimate/layouts';

        foreach ($rows as $key => $row)
        {
            $modified_row = $this->get_current_row($row);
            $columns = $modified_row->attr;

            if ($columns)
            {
                $componentArea = false;
                
                if (isset($modified_row->has_component) && $modified_row->has_component)
                {
                    $componentArea = true;
                }

                $fluidrow = false;
                if (isset($modified_row->settings->fluidrow) && $modified_row->settings->fluidrow)
                {
                    $fluidrow = $modified_row->settings->fluidrow;
                }

                $id = (isset($modified_row->settings->name) && $modified_row->settings->name) ? 'sp-' . JFilterOutput::stringURLSafe($modified_row->settings->name) : 'sp-section-' . ($key + 1);
                $row_class = $this->build_row_class($modified_row->settings);
                $this->add_row_styles($modified_row->settings, $id);
                $sematic = (isset($modified_row->settings->name) && $modified_row->settings->name) ? strtolower($modified_row->settings->name) : 'section';

                switch ($sematic) {
                    case "header":
                        $sematic = 'header';
                        break;

                    case "footer":
                        $sematic = 'footer';
                        break;

                    default:
                        $sematic = 'section';
                        break;
                }

                $data = array(
                    'sematic' 			=> $sematic,
                    'id' 				=> $id,
                    'row_class' 		=> $row_class,
                    'componentArea' 	=> $componentArea,
                    'pagebuilder' 		=> $pagebuilder,
                    'fluidrow' 			=> $fluidrow,
                    'rowColumns' 		=> $columns,
                    'loadFeature'       => $this->loadFeature
                );

                $layout_path  = JPATH_ROOT .'/plugins/system/helixultimate/layouts';
                $getLayout = new JLayoutFile('frontend.generate', $layout_path );
                $output .= $getLayout->render($data);
            }
        }

        return $output;
    }

    private function get_current_row($row)
    {
        $inactive_col   = 0; //absence span
        $has_component  = false;

        foreach ($row->attr as $key => &$column)
        {
            $column->settings->disable_modules = isset($column->settings->name) ? $this->disable_details_page_modules( $column->settings->name ) : false;

            if (!$column->settings->column_type)
            {
                if (!$this->count_modules($column->settings->name))
                {
                    $inactive_col += $column->settings->grid_size;
                    unset($row->attr[$key]);
                }
                if( $column->settings->disable_modules && $this->count_modules($column->settings->name) ){
                    $inactive_col += $column->settings->grid_size;
                    unset($row->attr[$key]);
                }
            }
            else
            {
                $row->has_component = true;
                $has_component = true;
            }
        }

        foreach ($row->attr as &$column)
        {
            $options = $column->settings;
            $col_grid_size = $options->grid_size;
            if (!$has_component && end($row->attr) === $column)
            {
                $col_grid_size = $col_grid_size + $inactive_col;
            }

            if ($options->column_type)
            {
                $col_grid_size = $col_grid_size + $inactive_col;
                $className = 'col-lg-' . $col_grid_size;
            }
            else
            {
                $className = 'col-lg-' . $col_grid_size;
            }

            if(isset($options->xl_col) && $options->xl_col)
            {
                $className = $className . ' col-xl-' . $options->xl_col;
            }

            if(isset($options->md_col) && $options->md_col)
            {
                $className = 'col-md-' . $options->md_col . ' ' . $className;
            }

            if(isset($options->sm_col) && $options->sm_col)
            {
                $className = 'col-sm-' . $options->sm_col . ' ' . $className;
            }

            if(isset($options->xs_col) && $options->xs_col)
            {
                $className = 'col-' . $options->xs_col . ' ' . $className;
            }

            $device_class = $this->get_device_class($options);
            $column->settings->className = $className . ' ' . $device_class;
        }

        return $row;
    }

    private function add_row_styles($options, $id)
    {
        $row_css = '';

        if (isset($options->background_image) && $options->background_image)
        {
            $row_css .= 'background-image:url("' . JURI::base(true) . '/' . $options->background_image . '");';
            if (isset($options->background_repeat) && $options->background_repeat)
            {
                $row_css .= 'background-repeat:' . $options->background_repeat . ';';
            }

            if (isset($options->background_size) && $options->background_size)
            {
                $row_css .= 'background-size:' . $options->background_size . ';';
            }

            if (isset($options->background_attachment) && $options->background_attachment)
            {
                $row_css .= 'background-attachment:' . $options->background_attachment . ';';
            }

            if (isset($options->background_position) && $options->background_position)
            {
                $row_css .= 'background-position:' . $options->background_position . ';';
            }
        }

        if (isset($options->background_color) && $options->background_color)
        {
            $row_css .= 'background-color:' . $options->background_color . ';';
        }

        if (isset($options->color) && $options->color)
        {
            $row_css .= 'color:' . $options->color . ';';
        }

        if (isset($options->padding) && $options->padding)
        {
            $row_css .= 'padding:' . $options->padding . ';';
        }

        if (isset($options->margin) && $options->margin)
        {
            $row_css .= 'margin:' . $options->margin . ';';
        }

        if ($row_css)
        {
            $this->doc->addStyledeclaration('#' . $id . '{ ' . $row_css . ' }');
        }


        if (isset($options->link_color) && $options->link_color)
        {
            $this->doc->addStyledeclaration('#' . $id . ' a{color:' . $options->link_color . ';}');
        }

        if (isset($options->link_hover_color) && $options->link_hover_color) {
            $this->doc->addStyledeclaration('#' . $id . ' a:hover{color:' . $options->link_hover_color . ';}');
        }
    }

    private function build_row_class($options)
    {
        $row_class = '';
        if (isset($options->custom_class) && $options->custom_class)
        {
            $row_class .= $options->custom_class;
        }

        $device_class = $this->get_device_class($options);
        if($device_class)
        {
            $row_class .= ' '.$device_class;
        }

        if($row_class)
        {
            $row_class = 'class="' . $row_class . '"';
        }
        return $row_class;
    }


    private function get_device_class($options)
    {
        $device_class = '';

        if (isset($options->hide_on_phone) && $options->hide_on_phone)
        {
            $device_class = 'd-none d-sm-block';
        }

        if (isset($options->hide_on_large_phone) && $options->hide_on_large_phone)
        {
            $device_class = $this->reshape_device_class('sm', $device_class);
            $device_class .= ' d-sm-none d-md-block';
        }

        if (isset($options->hide_on_tablet) && $options->hide_on_tablet)
        {
            $device_class = $this->reshape_device_class('md', $device_class);
            $device_class .= ' d-md-none d-lg-block';
        }

        if (isset($options->hide_on_small_desktop) && $options->hide_on_small_desktop)
        {
            $device_class = $this->reshape_device_class('lg', $device_class);
            $device_class .= ' d-lg-none d-xl-block';
        }

        if (isset($options->hide_on_desktop) && $options->hide_on_desktop)
        {
            $device_class = $this->reshape_device_class('xl', $device_class);
            $device_class .= ' d-xl-none';
        }

        return $device_class;
    }

    private function reshape_device_class($device = '', $class)
    {
        $search = 'd-'.$device.'-block';
        $class = str_replace($search,'',$class);
        $class = trim($class,' ');

        return $class;
    }

    public function count_modules($position)
    {
        return ($this->doc->countModules($position) or $this->has_feature($position));
    }

    /**
     * Disable module only from article page
     * Type: @feature
     */
    private function disable_details_page_modules( $position ){
        $article_and_disable = ($this->app->input->get('view') == 'article' && $this->params->get('disable_module'));
        $match_positions = $position == 'left' || $position == 'right';

        return ($article_and_disable && $match_positions);
    }

    private function has_feature($position)
    {
        if (in_array($position, $this->in_positions))
        {
            return true;
        }
        return false;
    }

    public function after_body()
    {
        if ($this->params->get('compress_css'))
        {
            $this->compress_css();
        }

        if ($this->params->get('compress_js'))
        {
            $this->compress_js($this->params->get('exclude_js'));
        }

        if ($before_body = $this->params->get('before_body'))
        {
            echo $before_body . "\n";
        }
    }

    public function scssInit()
    {
        include_once __DIR__ . '/classes/scss/Base/Range.php';
        include_once __DIR__ . '/classes/scss/Block.php';
        include_once __DIR__ . '/classes/scss/Colors.php';
        include_once __DIR__ . '/classes/scss/Compiler.php';
        include_once __DIR__ . '/classes/scss/Compiler/Environment.php';
        include_once __DIR__ . '/classes/scss/Exception/CompilerException.php';
        include_once __DIR__ . '/classes/scss/Exception/ParserException.php';
        include_once __DIR__ . '/classes/scss/Exception/ServerException.php';
        include_once __DIR__ . '/classes/scss/Formatter.php';
        include_once __DIR__ . '/classes/scss/Formatter/Compact.php';
        include_once __DIR__ . '/classes/scss/Formatter/Compressed.php';
        include_once __DIR__ . '/classes/scss/Formatter/Crunched.php';
        include_once __DIR__ . '/classes/scss/Formatter/Debug.php';
        include_once __DIR__ . '/classes/scss/Formatter/Expanded.php';
        include_once __DIR__ . '/classes/scss/Formatter/Nested.php';
        include_once __DIR__ . '/classes/scss/Formatter/OutputBlock.php';
        include_once __DIR__ . '/classes/scss/Node.php';
        include_once __DIR__ . '/classes/scss/Node/Number.php';
        include_once __DIR__ . '/classes/scss/Parser.php';
        include_once __DIR__ . '/classes/scss/Type.php';
        include_once __DIR__ . '/classes/scss/Util.php';
        include_once __DIR__ . '/classes/scss/Version.php';

        return new Leafo\ScssPhp\Compiler();
    }

    public function add_scss($scss, $vars = array(), $css = '')
    {
        $scss = JFile::stripExt($scss);

        if(!empty($css))
        {
            $css = JFile::stripExt($css) . '.css';
        }
        else
        {
            $css = $scss . '.css';
        }

        if($this->params->get('scssoption'))
        {
            $needsCompile = $this->needScssCompile($scss, $vars);
            if ($needsCompile)
            {
                $scssInit = $this->scssInit();
                $template  = JFactory::getApplication()->getTemplate();
                $scss_path = JPATH_THEMES . '/' . $template . '/scss';
                $css_path = JPATH_THEMES . '/' . $template . '/css';

                if (file_exists($scss_path . '/'. $scss . '.scss'))
                {
                    $out = $css_path . '/' . $css;
                    $scssInit->setFormatter('Leafo\ScssPhp\Formatter\Expanded');
                    $scssInit->setImportPaths($scss_path);
                    if(count($vars))
                    {
                        $scssInit->setVariables($vars);
                    }
                    $compiledCss = $scssInit->compile('@import "'. $scss .'.scss"');
                    JFile::write($out, $compiledCss);

                    $cache_path = \JPATH_CACHE . '/com_templates/templates/' . $template . '/' . $scss . '.scss.cache';
                    $scssCache = array();
                    $scssCache['imports'] = $scssInit->getParsedFiles();
                    $scssCache['vars'] = $scssInit->getVariables();
                    JFile::write($cache_path, json_encode($scssCache));
                }
            }
        }

        $this->add_css($css);
    }

    private function needScssCompile($scss, $existvars = array())
    {
        $cache_path = JPATH_CACHE . '/com_templates/templates/' . $this->template->template . '/' . $scss . '.scss.cache';
        $return = false;

        if (file_exists($cache_path))
        {
            $cache_file = json_decode(file_get_contents($cache_path));
            $imports = (isset($cache_file->imports) && $cache_file->imports) ? $cache_file->imports : array();
            $vars = (isset($cache_file->vars) && $cache_file->vars) ? (array) $cache_file->vars : array();

            if (array_diff($vars, $existvars))
            {
                $return = true;
            }

            if ($imports)
            {
                foreach ($imports as $import => $mtime)
                {
                    if (file_exists($import))
                    {
                        $existmtime = filemtime($import);
                        if ($existmtime != $mtime)
                        {
                            $return = true;
                        }
                    }
                    else
                    {
                        $return = true;
                    }
                }
            }
            else
            {
                $return = true;
            }
        }
        else
        {
            $return = true;
        }

        return $return;
    }

    public function addGoogleFont($fonts)
    {
        $doc = \JFactory::getDocument();

        $systemFonts = array(
            'Arial',
            'Tahoma',
            'Verdana',
            'Helvetica',
            'Times New Roman',
            'Trebuchet MS',
            'Georgia'
        );

        if (is_array($fonts))
        {
            foreach ($fonts as $key => $font)
            {
                $font = json_decode($font);

                if (!in_array($font->fontFamily, $systemFonts))
                {
                    $fontUrl = '//fonts.googleapis.com/css?family='. $font->fontFamily .':100,100i,300,300i,400,400i,500,500i,700,700i,900,900i';
                
                    if (isset($font->fontSubset) && $font->fontSubset)
                    {
                        $fontUrl .= '&amp;subset=' . $font->fontSubset;
                    }

                    $doc->addStylesheet($fontUrl);
                }
                
                $fontCSS = $key . "{";
                $fontCSS .= "font-family: '" . $font->fontFamily . "', sans-serif;";

                if (isset($font->fontSize) && $font->fontSize)
                {
                    $fontCSS .= 'font-size: ' . $font->fontSize . 'px;';
                }
                
                if (isset($font->fontWeight) && $font->fontWeight)
                {
                    $fontCSS .= 'font-weight: ' . $font->fontWeight . ';';
                }

                if (isset($font->fontStyle) && $font->fontStyle)
                {
                    $fontCSS .= 'font-style: ' . $font->fontStyle . ';';
                }

                $fontCSS .= "}\n";

                if (isset($font->fontSize_sm) && $font->fontSize_sm){
                    $fontCSS .= '@media (min-width:768px) and (max-width:991px){';
                    $fontCSS .= $key . "{";
                    $fontCSS .= 'font-size: ' . $font->fontSize_sm . 'px;';
                    $fontCSS .= "}\n}\n";
                }


                if (isset($font->fontSize_xs) && $font->fontSize_xs){
                    $fontCSS .= '@media (max-width:767px){';
                    $fontCSS .= $key . "{";
                    $fontCSS .= 'font-size: ' . $font->fontSize_xs . 'px;';
                    $fontCSS .= "}\n}\n";
                }
                $doc->addStyledeclaration($fontCSS);
            }
        }
    }

    //Exclude js and return others js
    private function exclude_js($key, $excludes)
    {
        $match = false;
        if ($excludes)
        {
            $excludes = explode(',', $excludes);
            foreach ($excludes as $exclude)
            {
                if (JFile::getName($key) == trim($exclude))
                {
                    $match = true;
                }
            }
        }

        return $match;
    }

    //function to compress js files
    public function compress_js($excludes = '')
    {
        require_once(__DIR__ . '/classes/Minifier.php');

        $app       = JFactory::getApplication();
        $cachetime = $app->get('cachetime', 15);

        $all_scripts  = $this->doc->_scripts;
        $cache_path   = JPATH_CACHE . '/com_templates/templates/' . $this->template->template;
        $scripts      = array();
        $root_url     = JURI::root(true);
        $minifiedCode = '';
        $md5sum       = '';

        //Check all local scripts
        foreach ($all_scripts as $key => $value)
        {
            $js_file = str_replace($root_url, JPATH_ROOT, $key);

            if (strpos($js_file, JPATH_ROOT) === false)
            {
                $js_file = JPATH_ROOT . $key;
            }

            if (JFile::exists($js_file))
            {
                if (!$this->exclude_js($key, $excludes))
                {
                    $scripts[] = $key;
                    $md5sum .= md5($key);
                    $compressed = \JShrink\Minifier::minify(JFile::read($js_file), array('flaggedComments' => false));
                    $minifiedCode .= "/*------ " . JFile::getName($js_file) . " ------*/\n" . $compressed . "\n\n"; //add file name to compressed JS

                    unset($this->doc->_scripts[$key]); //Remove sripts
                }
            }
        }

        //Compress All scripts
        if ($minifiedCode)
        {
            if (!JFolder::exists($cache_path))
            {
                JFolder::create($cache_path, 0755);
            }
            else
            {
                $file = $cache_path . '/' . md5($md5sum) . '.js';

                if (!JFile::exists($file))
                {
                    JFile::write($file, $minifiedCode);
                }
                else
                {
                    if (filesize($file) == 0 || ((filemtime($file) + $cachetime * 60) < time()))
                    {
                        JFile::write($file, $minifiedCode);
                    }
                }
                $this->doc->addScript(JURI::base(true) . '/cache/com_templates/templates/' . $this->template->template . '/' . md5($md5sum) . '.js');
            }
        }

        return;
    }

    public function getHeaderStyle()
    {
        $pre_header = $this->params->get('predefined_header');
        $header_style = $this->params->get('header_style');
        if (!$pre_header || !$header_style)
        {
            return;
        }

        $options = new stdClass;
        $options->template = $this->template;
        $options->params = $this->params;
        $template = $options->template->template;

        $tmpl_file_location = JPATH_ROOT .'/templates/'. $template .'/headers';

        if(JFile::exists($tmpl_file_location . '/'. $header_style . '/header.php')){
            $getLayout = new JLayoutFile($header_style.'.header', $tmpl_file_location );
            return $getLayout->render($options);
        }
    }

    public function minifyCss($css_code){
        // Remove comments
        $css_code = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css_code);
        
        // Remove space after colons
        $css_code = str_replace(': ', ':', $css_code);
    
        // Remove whitespace
        $css_code = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css_code);
    
        // Remove Empty Selectors without any properties
        $css_code = preg_replace('/(?:(?:[^\r\n{}]+)\s?{[\s]*})/', '', $css_code);
    
        // Remove Empty Media Selectors without any properties or selector
        $css_code = preg_replace('/@media\s?\((?:[^\r\n,{}]+)\s?{[\s]*}/', '', $css_code);
    
        return $css_code;
    }

    //Compress CSS files
    public function compress_css()
    {
        $app             = \JFactory::getApplication();
        $cachetime       = $app->get('cachetime', 15);
        $all_stylesheets = $this->doc->_styleSheets;
        $cache_path      = \JPATH_CACHE . '/com_templates/templates/' . $this->template->template;
        $stylesheets     = array();
        $root_url        = \JURI::root(true);
        $minifiedCode    = '';
        $md5sum          = '';

        //Check all local stylesheets
        foreach ($all_stylesheets as $key => $value)
        {
            $css_file = str_replace($root_url, \JPATH_ROOT, $key);

            if (strpos($css_file, \JPATH_ROOT) === false)
            {
                $css_file = \JPATH_ROOT . $key;
            }

            global $absolute_url;
            $absolute_url = $key;            

            if (\JFile::exists($css_file))
            {
                $stylesheets[] = $key;
                $md5sum .= md5($key);
                $compressed = $this->minifyCss(file_get_contents($css_file));

                $fixUrl = preg_replace_callback('/url\(([^\):]*)\)/', function ($matches){

                        global $absolute_url;

                        $url = str_replace(array('"', '\''), '', $matches[1]);
                        if(preg_match('/\.(jpg|png|jpeg|mp4|gif|JPEG|JPG|PNG|GIF)$/', $url)) {
                            return "url('$url')";
                        }
                        $base = dirname($absolute_url);
                        while (preg_match('/^\.\.\//', $url))
                        {
                            $base = dirname($base);
                            $url  = substr($url, 3);
                        }
                        $url = $base . '/' . $url;

                        return "url('$url')";
                    }, $compressed);

                $minifiedCode .= "/*------ " . basename($css_file) . " ------*/\n" . $fixUrl . "\n\n"; //add file name to compressed css

                unset($this->doc->_styleSheets[$key]); //Remove stylesheets
            }
        }

        //Compress All stylesheets
        if ($minifiedCode)
        {
            if (!\JFolder::exists($cache_path))
            {
                \JFolder::create($cache_path, 0755);
            }
            else
            {
                $file = $cache_path . '/' . md5($md5sum) . '.css';

                if (!\JFile::exists($file))
                {
                    \JFile::write($file, $minifiedCode);
                }
                else
                {
                    if (filesize($file) == 0 || ((filemtime($file) + $cachetime * 60) < time()))
                    {
                        \JFile::write($file, $minifiedCode);
                    }
                }
                $this->doc->addStylesheet(\JURI::base(true) . '/cache/com_templates/templates/' . $this->template->template . '/' . md5($md5sum) . '.css');
            }
        }

        return;
    }

    public static function getRelatedArticles($params){
        $user   = JFactory::getUser();
		$userId = $user->get('id');
		$guest  = $user->get('guest');
		$groups = $user->getAuthorisedViewLevels();
        $authorised = JAccess::getAuthorisedViewLevels($userId);
        $db = JFactory::getDbo();
        $app = JFactory::getApplication();
        $nullDate = $db->quote($db->getNullDate());
        $nowDate  = $db->quote(JFactory::getDate()->toSql());
        $item_id = $params['item_id'];
        $maximum = isset($params['maximum']) ? (int) $params['maximum'] : 5;
        $maximum = $maximum < 1 ? 5 : $maximum;
        $catId = isset($params['catId']) ? (int) $params['catId'] : null;
        $tagids = [];
        if( isset($params['itemTags']) && count($params['itemTags']) ){
            $itemTags = $params['itemTags'];
            foreach( $itemTags as $tag ){
                array_push($tagids, $tag->id );
            }
        }
        
        // Category filter
        $catItemIds = $tagItemIds = $itemIds = [];
		if ( $catId !== null ) {
            $catQuery = $db->getQuery(true)
                ->clear()
                ->select('id')
                ->from($db->quoteName('#__content'))
                ->where($db->quoteName('catid'). " = " .$catId)
                ->setLimit($maximum+1);
                $db->setQuery($catQuery);
                $catItemIds = $db->loadColumn();
		}

		// tags filter
		if (is_array($tagids) && count($tagids)) {
			$tagId = implode(',', ArrayHelper::toInteger($tagids));
			if ($tagId) {
                $subQuery = $db->getQuery(true)
                    ->clear()
					->select('DISTINCT content_item_id as id')
					->from($db->quoteName('#__contentitem_tag_map'))
					->where('tag_id IN (' . $tagId . ')')
					->where('type_alias = ' . $db->quote('com_content.article'));
                $db->setQuery($subQuery);
                $tagItemIds = $db->loadColumn();
			}
        }
        
        $itemIds = array_unique(array_merge($catItemIds, $tagItemIds));
        
        if( count($itemIds) < 1 ){
            return [];
        }
        $itemIds = implode(',', ArrayHelper::toInteger($itemIds));
        $query = $db->getQuery(true);

        $query->clear()
            ->select('a.*')
            ->select('a.alias as slug')
            ->from($db->quoteName('#__content', 'a'))
            ->select($db->quoteName('b.alias', 'category_alias'))
            ->select($db->quoteName('b.title', 'category'))
            ->select($db->quoteName('b.access', 'category_access'))
            ->select($db->quoteName('u.name', 'author'))
            ->join('LEFT', $db->quoteName('#__categories', 'b') . ' ON (' . $db->quoteName('a.catid') . ' = ' . $db->quoteName('b.id') . ')')
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON (' . $db->quoteName('a.created_by') . ' = ' . $db->quoteName('u.id') . ')')
            ->where($db->quoteName('a.access')." IN (" . implode( ',', $authorised ) . ")")
            ->where('a.id IN (' . $itemIds . ')')
            ->where('a.id != ' . (int) $item_id);
        // Language filter
        if ($app->getLanguageFilter()) {
            $query->where('a.language IN (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
        }
        $query->where('(a.publish_down = ' . $nullDate . ' OR a.publish_down >= ' . $nowDate . ')');
        $query->where($db->quoteName('a.state') . ' = ' . $db->quote(1));
        $query->order($db->quoteName('a.created') . ' DESC')
		->setLimit($maximum);
        $db->setQuery($query);
        $items = $db->loadObjectList();
        foreach( $items as &$item ){
            $item->slug    	= $item->id . ':' . $item->slug;
            $item->catslug 	= $item->catid . ':' . $item->category_alias;
            $item->params = JComponentHelper::getParams('com_content');
            $access = (isset($item->access) && $item->access) ? $item->access : true;
            
            if ($access) {
                $item->params->set('access-view', true);
			} else {
				if ($item->catid == 0 || $item->category_access === null) {
					$item->params->set('access-view', in_array($item->access, $groups));
				} else {
					$item->params->set('access-view', in_array($item->access, $groups) && in_array($item->category_access, $groups));
				}
			}

        }
        return $items;
    }
}
