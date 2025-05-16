<?php
if (!defined('ABSPATH')) exit;

class Brevo_Settings {

    public function __construct() {
         add_action('admin_menu', array($this, 'add_plugin_page'));
         add_action('admin_init', array($this, 'page_init'));
         add_action('wp_ajax_brevo_fetch_attributes', array($this, 'fetch_attributes'));
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

              <script>
              jQuery(document).ready(function($) {
                  $('#brevo-refresh-attributes').click(function() {
                      $.post(ajaxurl, {
                          action: 'brevo_fetch_attributes',
                          security: '<?php echo wp_create_nonce("brevo_fetch_nonce"); ?>',
                      }, function(response) {
                          if (response.success) {
                              $('#brevo-attribute-results').html("<div style='color: green;'>" + response.data.message + "</div>");
                              $('#brevo-attribute-list').html(response.data.attributes_html);
                          } else {
                              $('#brevo-attribute-results').html("<div style='color: red;'>" + response.data.message + "</div>");
                          }
                      }).fail(function() {
                          $('#brevo-attribute-results').html("<div style='color: red;'>AJAX call failed. Check console logs.</div>");
                      });
                  });
              });
              </script>
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

    public function sanitize_attribute_array($input) {
         return array_map('sanitize_text_field', (array)$input);
    }

    public function selected_attributes_callback() {
         echo '<div id="brevo-attribute-list">';
         $attributes = get_option('ksj_brevo_selected_attributes', array());
         foreach ($attributes as $attribute) {
             echo "<input type='checkbox' name='ksj_brevo_selected_attributes[]' value='{$attribute}' checked /> {$attribute}<br>";
         }
         echo '</div>';
    }

    public function fetch_attributes() {
        check_ajax_referer('brevo_fetch_nonce', 'security');
        $api_key = get_option('ksj_brevo_api_key', '');

        if (empty($api_key)) {
            wp_send_json_error(array('message' => 'API Key is missing.'));
        }

        $response = wp_remote_get("https://api.brevo.com/v3/attributes", array(
            'headers' => array('api-key' => $api_key)
        ));

        if (is_wp_error($response)) {
            error_log('Brevo Attributes API Error: ' . $response->get_error_message());
            wp_send_json_error(array('message' => 'API request failed: ' . $response->get_error_message()));
        }

        $body = wp_remote_retrieve_body($response);
        error_log('Brevo Attributes API Raw Response: ' . $body);

        $data = json_decode($body, true);
        if (!isset($data['attributes']) || empty($data['attributes'])) {
            error_log('Brevo API returned no attributes.');
            wp_send_json_error(array('message' => 'No attributes found.'));
        }

        $attributes_html = '';
        foreach ($data['attributes'] as $attribute) {
            $attributes_html .= "<input type='checkbox' name='ksj_brevo_selected_attributes[]' value='{$attribute['name']}' /> {$attribute['name']}<br>";
        }

        wp_send_json_success(array('message' => 'Attributes successfully fetched!', 'attributes_html' => $attributes_html));
    }
}
