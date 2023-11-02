<?php

// Plugin Name: Fluent Analytics
// Plugin URI: https://github.com/fluentapp/wordpress-plugin
// Description: This plugin adds a JavaScript script to the header of your website to track website traffic.
// Version: 1.0.3
// Author: fluent App
// Author URI: https://fluentapp.io
// License: MIT
// Load the plugin.

require 'page.php';

// Get the plugin settings.
$settings = get_option('fluent_analytics_option_name');

// Get the URL of the external JS script.
$fluentapp_js_script_domain = @$settings['domain_0'];

// Get the enabled flag
$fluentapp_js_script_enabled = @$settings['enabled'];

function fluentapp_js_script_load() {
    // Add the external JS script to the front-end.
    global $fluentapp_js_script_enabled;
    if ($fluentapp_js_script_enabled) {
        wp_enqueue_script(
                'fluentanalytics',
                'https://app.fluentapp.io/fluentanalytics.js',
                array(),
                '1.0.3',
                array(
                    'strategy' => 'defer',
                    'in_footer' => false
                )
        );
    }
}

function add_data_to_script($tag, $handle, $src) {
    global $fluentapp_js_script_domain;
    if ('fluentanalytics' === $handle) {
        $tag = str_replace('src=', 'data-api="https://api.fluentapp.io/event" data-domain="' . $fluentapp_js_script_domain . '" src=', $tag);
    }
    return $tag;
}

add_filter('script_loader_tag', 'add_data_to_script', 10, 3);
add_action('wp_enqueue_scripts', 'fluentapp_js_script_load');

