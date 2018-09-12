<?php
/*
  Plugin Name: Donation
  Plugin URI:
  Plugin Description: Donate points to users paid by Waves-based tokens
  Plugin Version: 1.0
  Plugin Date: 2018-08-27
  Plugin Author: Abdullah Daud
  Plugin Author URI: https://github.com/chelahmy
  Plugin License: https://github.com/chelahmy
  Plugin Minimum Question2Answer Version: 1.5
  Plugin Update Check URI:
*/

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}
	
qa_register_plugin_layer(
	'qa-donation-layer.php', // PHP file containing layer class 
	'Donation Layer' // human-readable name of layer
);

qa_register_plugin_module(
	'page', // type of module
	'qa-donate-to.php', // PHP file containing module class
	'qa_donate_to_page', // name of module class
	'Donate To Page' // human-readable name of module
);

qa_register_plugin_module(
	'page', // type of module
	'qa-donation-point-rates.php', // PHP file containing module class
	'qa_donation_point_rates_page', // name of module class
	'Donate Point Rates Page' // human-readable name of module
);

qa_register_plugin_phrases(
    'qa-donation-lang-*.php', // pattern for language files
    'plugin_donation_desc' // prefix to retrieve phrases
);


function render_donate_button($handle)
{
	return '<a href="' . qa_path('donate-to/' . $handle) . '">' . qa_lang_html('plugin_donation_desc/donate') . '</a>';
}

