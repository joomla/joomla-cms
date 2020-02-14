/**
* @package Helix3 Framework
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/
jQuery(function($) {

  $('.post-formats input').on('click', function(){
    checkFormate();
  });

  function checkFormate(){

    var formate = $('.post-formats input:checked').attr('value');
    
    if(typeof formate != 'undefined'){

      $('#jform_attribs_gallery, #jform_attribs_audio, #jform_attribs_audio, #jform_attribs_video, #jform_attribs_link_title, #jform_attribs_link_url, #jform_attribs_quote_text, #jform_attribs_quote_author, #jform_attribs_post_status').closest('.control-group').hide();

      if( formate=='video' ) {
        $('#jform_attribs_video').closest('.control-group').show();
      } else if( formate=='gallery' ) {
        $('#jform_attribs_gallery').closest('.control-group').show();
      } else if( formate=='audio' ) {
        $('#jform_attribs_audio').closest('.control-group').show();
      } else if( formate=='link' ) {
        $('#jform_attribs_link_title').closest('.control-group').show();
        $('#jform_attribs_link_url').closest('.control-group').show();
      } else if( formate=='quote' ) {
        $('#jform_attribs_quote_text').closest('.control-group').show();
        $('#jform_attribs_quote_author').closest('.control-group').show();
      } else if( formate=='status' ) {
        $('#jform_attribs_post_status').closest('.control-group').show();
      }

    }
  }

  $(document).ready(function(){
    checkFormate();
  });

});
