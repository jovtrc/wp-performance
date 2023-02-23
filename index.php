<?php

/*
Plugin Name: WP Performance
Plugin URI: https://github.com/jovtrc/wp-performance
Description: A simple WordPress plugin that optimizes the performance of the website via CSS, HTML and JavaScript changes.
Author: João Carvalho
Author URI: https://joaocarvalho.cc
Version: 0.1
*/

// Load Plugin
require_once('Classes/Performance.php');
new Performance();