<?php
if (!defined('ABSPATH')) exit;

class Brevo_Settings {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
        add_action('wp_ajax_brevo_fetch_attributes', array($this, 'fetch_attributes'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function enqueue_admin_scripts() {
        wp_enqueue_script('ksj-brevo-admin', plugin_dir_url(__FILE__) . 'js/admin.js', array('jquery'), '1.8', true);
        wp_localize_script('ksj-brevo-admin', 'ksjBrevoData', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('brevo_fetch_nonce')
        ));
    }

    public function add_plugin_page() {
        add_options_page(
            'KSJ Brevo Integration Settings', 
            'KSJ Brevo Integration',
            'manage_options',
            'ksj-brevo-integration-admin',
            array($this, 'create_admin_page')
        );
    }

    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h1>KSJ Brevo Integration Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('ksj_brevo_option_group');
                do_settings_sections('ksj-brevo-integration-admin');
                submit_button();
                ?>
            </form>

            <button id="brevo-refresh-attributes" class="button-secondary">Refresh Attributes from Brevo</button>
            <div id="brevo-attribute-results"></div>
            <div id="brevo-attribute-list">
                <?php
                $attributes = get_option('ksj_brevo_selected_attributes', array());
                foreach ($attributes as $attribute) {
                    echo "<input type='checkbox' name='ksj_brevo_selected_attributes[]' value='{$attribute}' checked /> {$attribute}<br>";
                }
                ?>
            </div>
        </div>
        <?php
    }

    public function page_init() {
        register_setting('ksj_brevo_option_group', 'ksj_brevo_api_key', array('sanitize_callback' => 'sanitize_text_field'));
        register_setting('ksj_brevo_option_group', 'ksj_brevo_selected_attributes', array('sanitize_callback' => array($this, 'sanitize_attribute_array')));

        add_settings_section('setting_section_id', 'Brevo Integration Settings', array($this, 'print_section_info'), 'ksj-brevo-integration-admin');
        add_settings_field('api_key', 'Brevo API Key', array($this, 'api_key_callback'), 'ksj-brevo-integration-admin', 'setting_section_id');
        add_settings_field('selected_attributes', 'Select Attributes to Display', array($this, 'selected_attributes_callback'), 'ksj-brevo-integration-admin', 'setting_section_id');
    }

    public function print_section_info() {
        echo 'Configure your Brevo API key and select which attributes should appear on the frontend.';
    }

    public function api_key_callback() {
        printf('<input type="text" id="ksj_brevo_api_key" name="ksj_brevo_api_key" value="%s" size="50" />', esc_attr(get_option('ksj_brevo_api_key', '')));
    }

    public function selected_attributes_callback() {
        echo ''; // Intentionally left blank since the list is printed in the admin page
    }

    public function sanitize_attribute_array($input) {
        return array_map('sanitize_text_field', (array)$input);
    }

    public function fetch_attributes() {
        check_ajax_referer('brevo_fetch_nonce', 'security');
        $api_key = get_option('ksj_brevo_api_key', '');

        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'API Key is missing.'));
        }

        $response = wp_remote_get("https://api.brevo.com/v3/contacts/attributes", array(
            'headers' => array('api-key' => $api_key)
        ));

        if (is_wp_error($response)) {
            error_log('Brevo API Error: ' . $response->get_error_message());
            wp_send_json_error(array('message' => 'API request failed: ' . $response->get_error_message()));
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!isset($data['attributes']) || empty($data['attributes'])) {
            wp_send_json_error(array('message' => 'No attributes found.'));
        }

        $attributes_html = '';
        foreach ($data['attributes'] as $attribute) {
            $name = esc_html($attribute['name']);
            $attributes_html .= "<input type='checkbox' name='ksj_brevo_selected_attributes[]' value='{$name}' /> {$name}<br>";
        }

        wp_send_json_success(array(
            'message' => 'Attributes successfully fetched!',
            'attributes_html' => $attributes_html
        ));
    }
}

