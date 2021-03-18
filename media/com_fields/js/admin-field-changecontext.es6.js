/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

Joomla.fieldsChangeContext = context => {
  'use strict';

  const regex = new RegExp('([?;&])context[^&;]*[;&]?');
  const url = window.location.href;
  const query = url.replace(regex, '$1').replace(/&$/, ''); // eslint-disable-next-line

  window.location.href = (query.length > 2 ? query + '&' : '?') + (context ? 'context=' + context : '');
};