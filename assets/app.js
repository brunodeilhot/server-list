import './bootstrap.js';
import './styles/app.css';
import htmx from 'htmx.org';

window.htmx = htmx;

window.dismissFlashMessage = function(element) {
    element.parentElement.classList.add('hidden');
};
