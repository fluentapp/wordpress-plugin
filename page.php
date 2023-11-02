<?php

class FluentAnalytics {

    private $fluent_analytics_options;

    public function __construct() {
        add_action('admin_menu', array($this, 'fluent_analytics_add_plugin_page'));
        add_action('admin_init', array($this, 'fluent_analytics_page_init'));
        add_action('init', array($this, 'check_plugin_version'));
    }

    function check_plugin_version() {
        // this will run your default option setup if the version does not exist
        if (!defined('IFRAME_REQUEST') &&  get_option( 'fluent_plugin_version' ) == null) {
            update_option('fluent_plugin_version', '1.0.3');
            $this->setup_options();
        }
    }

    function setup_options() {
        $this->fluent_analytics_options = get_option('fluent_analytics_option_name');
        $this->fluent_analytics_options['enabled'] = '1';
        update_option('fluent_analytics_option_name', $this->fluent_analytics_options);
    }

    public function fluent_analytics_add_plugin_page() {
        add_menu_page(
                'Fluent Analytics', // page_title
                'Fluent Analytics', // menu_title
                'manage_options', // capability
                'fluent-analytics', // menu_slug
                array($this, 'fluent_analytics_create_admin_page'), // function
                'dashicons-admin-generic', // icon_url
                80 // position
        );
    }

    public function fluent_analytics_create_admin_page() {
        $this->fluent_analytics_options = get_option('fluent_analytics_option_name');
        ?>

        <div class="wrap">
            <h2>Fluent Analytics</h2>

            <?php settings_errors(); ?>
            <div>
                This plugin will automatically add Fluent Analytics script to your website header. Make sure to add the value of the domain below.
            </div>
            <form method="post" action="options.php">
                <?php
                settings_fields('fluent_analytics_option_group');
                do_settings_sections('fluent-analytics-admin');
                ?>
                <div>Enter the domain name of your website, please make sure that you 
                    have created it in Fluent Analytics. Click on this <a href="http://app.fluentapp.io/manage-sites" target="_blank"> link </a> to check 
                    the list of sites you have added</div>
                <?php
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function fluent_analytics_page_init() {
        register_setting(
                'fluent_analytics_option_group', // option_group
                'fluent_analytics_option_name', // option_name
                array($this, 'fluent_analytics_sanitize') // sanitize_callback
        );

        add_settings_section(
                'fluent_analytics_setting_section', // id
                'Settings', // title
                array($this, 'fluent_analytics_section_info'), // callback
                'fluent-analytics-admin' // page
        );

        // Domain
        add_settings_field(
                'domain_0', // id
                'Domain', // title
                array($this, 'domain_0_callback'), // callback
                'fluent-analytics-admin', // page
                'fluent_analytics_setting_section' // section
        );

        // Enabled
        add_settings_field(
                'enabled', // id
                'Enabled', // title
                array($this, 'enabled_callback'), // callback
                'fluent-analytics-admin', // page
                'fluent_analytics_setting_section' // section
        );
    }

    public function fluent_analytics_sanitize($input) {
        $sanitary_values = array();

        if (isset($input['domain_0'])) {
            $domain = sanitize_text_field($input['domain_0']);

            // Validate the FQDN format
            if (preg_match('/^(?:(?=[a-z0-9-]{1,63}\.)(xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}[a-z]{2,63}$/i', $domain)) {
                $sanitary_values['domain_0'] = $domain;
            } else {
                // Invalid FQDN format, display an error
                add_settings_error(
                        'fluent_analytics_option_name',
                        'invalid_domain',
                        'Invalid domain format. Please enter a valid Fully Qualified Domain Name (FQDN) like example.com',
                        'error'
                );
            }
        }

        // Sanitize enabled checkbox
        if (isset($input['enabled'])) {
            $sanitary_values['enabled'] = sanitize_text_field($input['enabled']);
        }

        return $sanitary_values;
    }

    public function fluent_analytics_section_info() {
        
    }

    public function domain_0_callback() {
        printf(
                '<input class="regular-text" type="text" name="fluent_analytics_option_name[domain_0]" id="domain_0" value="%s">',
                isset($this->fluent_analytics_options['domain_0']) ? esc_attr($this->fluent_analytics_options['domain_0']) : ''
        );
    }

    public function enabled_callback() {
        $enabled = isset($this->fluent_analytics_options['enabled']) ? $this->fluent_analytics_options['enabled'] : '';
        ?>
        <label for="enabled">
            <input type="checkbox" name="fluent_analytics_option_name[enabled]" id="enabled" <?php checked('1', $enabled); ?> value="1">
            Enable Fluent Analytics
        </label>
        <?php
    }
}

if (is_admin())
    $fluent_analytics = new FluentAnalytics();

/* 
 * Retrieve this value with:
 * $fluent_analytics_options = get_option( 'fluent_analytics_option_name' ); // Array of All Options
 * $domain_0 = $fluent_analytics_options['domain_0']; // Domain
 */