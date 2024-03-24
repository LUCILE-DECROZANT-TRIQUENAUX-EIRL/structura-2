/*
 * Main JavaScript file
 *
 * Included in templates/base.html.twig
 */

// main CSS file, included in templates/base.html.twig too
import './styles/app.scss';

// include libraries
const $ = require('jquery');
require('@popperjs/core'); // tooltips
const bootstrap = require('bootstrap'); // bootstrap 5
require ('./javascripts/NiceAdmin/main'); // NiceAdmin


// enable popper tooltips (bootstrap tooltips)
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
