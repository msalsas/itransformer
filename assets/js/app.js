/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/jquery-ui-1.10.3.custom.min.css');
require('../css/app.css');
require('../css/options.css');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
const $ = jQuery = require('jquery');
const ajaxSubmit = require('jquery-form');
console.log('Hello Webpack Encore! Edit me in assets/js/app.js');


require('./jquery-ui-1.10.3.custom.min.js');
require('./jquery.easing.min.js');
require('./plugins.js');
require('./imageUpload.js');
require('./efectos_inicio.js');
require('./adj_img.js');
require('./procesar_img.js');
require('./efectos_social.js');