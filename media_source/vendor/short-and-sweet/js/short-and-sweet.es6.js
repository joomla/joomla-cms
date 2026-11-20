import shortAndSweet from 'short-and-sweet/dist/short-and-sweet.module.js';

shortAndSweet('textarea.charcount,input.charcount', { counterClassName: 'small text-muted' });

/** Repeatable */
document.addEventListener('joomla:updated', (event) => {
  event.target.querySelectorAll('textarea.charcount,input.charcount')
    .forEach((el) => shortAndSweet(el, { counterClassName: 'small text-muted' }));
});
