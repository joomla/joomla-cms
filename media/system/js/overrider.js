// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-2.0/JG/trunk/media/joomgallery/js/admin.js $
// $Id: admin.js 3383 2011-10-07 20:30:32Z erftralle $
/****************************************************************************************\
**   JoomGallery  2                                                                     **
**   By: JoomGallery::ProjectTeam                                                       **
**   Copyright (C) 2008 - 2011  JoomGallery::ProjectTeam                                **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                            **
**   Released under GNU GPL Public License                                              **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look                       **
**   at administrator/components/com_joomgallery/LICENSE.TXT                            **
\****************************************************************************************/

/**
 * Overwrite function for Joomla.submitbutton.
 * Submits the form. 
 *  
 * @param   string  pressbutton The button pressed
 * @return  void
 */

Joomla.overrider = {
  states : {
    refreshing:false,
    counter:0
  }
};

Joomla.overrider.refreshCache = function()
{
		var req = new Request.JSON({
			method: 'post',
			url: 'index.php?option=com_languages&task=strings.refresh&format=json',
			onRequest: function() {
				this.states.refreshing = true;
				document.id('refresh-status').reveal();
			}.bind(this),
			onSuccess: function(r) {
				if (r.messages) {
					Joomla.renderMessages(r.messages);
				}
				document.id('refresh-status').dissolve();
				this.states.refreshing = false;
			}.bind(this),
			onFailure: function(xhr) {
				var r = JSON.decode(xhr.responseText);
				if (r) {
					alert(r.message);
				}
			}.bind(this)
		});
		req.post();                     
};

Joomla.overrider.searchStrings = function(searchstring, more)
{
	if(this.states.refreshing)
	{
		return;
	}
		var req = new Request.JSON({
			method: 'post',
			url: 'index.php?option=com_languages&task=strings.search&format=json',
			onRequest: function() {
        if(more)
        {
          document.id('more-results').addClass('jg_spinner');
        }
        else
        {
          document.id('more-results').set('style', 'display:none;');
          var children = $$('#results-container div');
          children.destroy();
          document.id('results-container').addClass('jg_spinner').reveal();
        }
			}.bind(this),
			onSuccess: function(r) {
				if (r.messages) {
					Joomla.renderMessages(r.messages);
				}
				if(r.data)
				{
          if(r.data.results)
          {
            this.insertResults(r.data.results);
          }
          if(r.data.more)
          {
            this.states.more = r.data.more;
            document.id('more-results').reveal();
          }
          else
          {
            document.id('more-results').set('style', 'display:none;');
          }
				}
        document.id('results-container').removeClass('jg_spinner');
        document.id('more-results').removeClass('jg_spinner');
			}.bind(this),
			onFailure: function(xhr) {
        document.id('results-container').removeClass('jg_spinner');
        document.id('more-results').removeClass('jg_spinner');
				var r = JSON.decode(xhr.responseText);
				if (r) {
					alert(r.message);
				}
			}.bind(this)
		});
		req.post('searchstring=' + searchstring + '&more=' + more);         
};

Joomla.overrider.insertResults = function(results)
{
  this.states.counter = this.states.counter + 1;

	var results_div = new Element('div', {
		id: 'language-results' + this.states.counter,
		style: 'display:none;'
	});
	Array.each(results, function (item, index) {
    var div = new Element('div', {
      class:    'result row' + index%2,
			onclick:  'Joomla.overrider.selectString(' + this.states.counter + index + ');',
    });
		var key = new Element('div', {
			id:       'override_key' + this.states.counter + index,
      class:    'result-key',
			html:     item.constant
		});
		key.inject(div);
		var string = new Element('div', {
			id:       'override_string' + this.states.counter + index,
      class:    'result-string',
			html:     item.string
		});
    string.inject(div);
		div.inject(results_div);
	}, this);
	results_div.inject(document.id('more-results'), 'before');
	document.id('language-results' + this.states.counter).reveal();
};

Joomla.overrider.selectString = function(id)
{
	document.id('jform_key').value = document.id('override_key' + id).get('html');
	document.id('jform_override').value = document.id('override_string' + id).get('html');
  new Fx.Scroll(window).toTop();
};

Joomla.overrider.edit = function(key)
{
  var newKey = new Element('input', {
    type:   'text',
    id:     'new-key[' + key + ']',
    class:  'inputbox',
    style:  'display:none',
    size:   80,
    value:  document.id('key[' + key + ']').get('html')
  });

  var newString = new Element('textarea', {
    id:     'new-string[' + key + ']',
    class:  'inputbox',
    style:  'display:none;width:92%;',
    cols:   60,
    rows:   5,
    value:  document.id('string[' + key + ']').get('html')
  });

  newKey.inject('key[' + key + ']', 'after');
  newString.inject('string[' + key + ']', 'after');
  document.id('key[' + key + ']').dissolve();
  document.id('string[' + key + ']').dissolve();
  document.id('new-key[' + key + ']').reveal();
  document.id('new-string[' + key + ']').reveal();

  var a = new Element('a', {
    id:     'save-button[' + key + ']',
    class:  'saveorder',
    title:  'Save Order',
    href:   'javascript:Joomla.overrider.save(\'' + key + '\');'
  });

  a.inject('new-string[' + key + ']', 'before');
};

Joomla.overrider.save = function(key)
{
    document.id('adminForm').task.value = 'override.save';

		var req = new Request.JSON({
			method: 'post',
			url: 'index.php?option=com_languages&format=json',
			onRequest: function() {
        document.id('save-button[' + key + ']').removeClass('saveorder');
        document.id('save-button[' + key + ']').addClass('save-spinner');
			}.bind(this),
			onSuccess: function(r) {
				if (r.messages) {
					Joomla.renderMessages(r.messages);
				}
				if(r.success)
				{
          document.id('key[' + key + ']').set('html', document.id('new-key[' + key + ']').value);
          document.id('string[' + key + ']').set('html', document.id('new-string[' + key + ']').value);
          document.id('save-button[' + key + ']').nix(true);
          document.id('new-key[' + key + ']').nix(true);
          document.id('new-string[' + key + ']').nix(true);
          document.id('key[' + key + ']').reveal();
          document.id('string[' + key + ']').reveal();
				}
        else
        {
          new Fx.Scroll(window).toTop();
        }
			}.bind(this),
			onFailure: function(xhr) {alert(xhr);
          document.id('save-button[' + key + ']').removeClass('save-spinner');
				var r = JSON.decode(xhr.responseText);
				if (r) {
					alert(r.message);
				}
			}.bind(this)
		});
		req.post(document.id('adminForm').toQueryString() + '&jform[key]=' + encodeURIComponent(document.id('new-key[' + key + ']').value) + '&jform[override]=' + encodeURIComponent(document.id('new-string[' + key + ']').value) + '&id=' + encodeURIComponent(document.id('key[' + key + ']').get('html')));         
};