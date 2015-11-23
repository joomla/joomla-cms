<?php
/**
 * @version     $Id: badwordfilter.php 1.6 2011-03-11 Rob Haag rhaag71 at g mail dot com $
 * @copyright   Copyright (C) 2011 All rights reserved.
 * @license     GNU/GPL, see LICENSE.php
 */


// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class plgContentBadWordFilter extends JPlugin {
/**
 * Constructor
 *
 * For php4 compatability we must not use the __constructor as a constructor for plugins
 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
 * This causes problems with cross-referencing necessary for the observer design pattern.
 *
 * @param object $subject The object to observe
 * @param object $params  The object that holds the plugin parameters
 * @since 1.5
 */
  function plgContentBadWordFilter( &$subject, $config ) {
    parent::__construct( $subject, $config );
  }

  // needed the new 'context' arg and onPrepareContent is now onContentPrepare (in 1.6)
  public function onContentPrepare($context, &$row, &$params, $page = 0) {
    if (is_object($row)) {
        $text = &$row->text;
    }
    else {
      $text = &$row;
    }
    //global $mainframe;

    // Plugin helper no longer need in 1.6, parameter object now available automatically (in 1.6)
    $allow_exceptions = $this->params->get('allow_exceptions', '1');

    if ($allow_exceptions == '1') {
      if (JString::strpos($text, '{no_badwordfilter}') !== false) {
        $text = str_replace('{no_badwordfilter}', '', $text);
        return true;
      }
    }

    $badwords = $this->params->def('bad_words', 'porn,sex');
    $html_out = $this->params->def('html_out', '<s>BAD WORD</s>');


    $badwords_array = explode(',', $badwords);


    foreach($badwords_array as $badword) {
      $text = JString::str_ireplace($badword, $html_out, $text);
    }

    return true;
  }

}
