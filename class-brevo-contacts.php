<?php
if (!defined('ABSPATH')) exit;

class Brevo_Contacts {

    private $api_endpoint = 'https://api.brevo.com/v3/contacts';

    public function __construct() {
        add_action('init', array($this, 'process_contact_submission'));
    }

    public function process_contact_submission() {
        if ( isset($_POST['ksj_brevo_nonce']) && wp_verify_nonce($_POST['ksj_brevo_nonce'], 'ksj_brevo_form') ) {

            $api_key = get_option('ksj_brevo_api_key', '');
            if (empty($api_key)) {
                error_log('Brevo_Contacts: API Key is missing.');
                return;
            }

            $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
            if (empty($email) || !is_email($email)) {
                error_log('Brevo_Contacts: Invalid or missing email.');
                return;
            }
            
            // Gather selected attributes
            $attributes = array();
            foreach ($_POST as $key => $value) {
                if (in_array($key, array('ksj_brevo_nonce', 'email', 'listIds'))) {
                    continue;
                }
                $attributes[strtoupper($key)] = sanitize_text_field($value);
            }

            // Capture selected lists
            $listIds = isset($_POST['listIds']) ? array_map('intval', $_POST['listIds']) : array();

            // Prepare the API payload
            $body = array(
                'email'         => $email,
                'attributes'    => $attributes,
                'listIds'       => $listIds,
                'updateEnabled' => true
            );

            // Send API request
            $response = wp_remote_post($this->api_endpoint, array(
                'headers' => array(
                    'api-key'      => $api_key,
                    'Content-Type' => 'application/json'
                ),
                'body'    => json_encode($body),
                'timeout' => 15,
            ));

            if (is_wp_error($response)) {
                error_log('Brevo_Contacts WP_Error: ' . $response->get_error_message());
                return;
            }

            $response_code = wp_remote_retrieve_response_code($response);
            $response_body = json_decode(wp_remote_retrieve_body($response), true);

            if ($response_code == 201 || $response_code == 200) {
                error_log('Brevo_Contacts: Contact successfully added. Response: ' . print_r($response_body, true));
            } else {
                error_log('Brevo_Contacts: API Error (' . $response_code . ') - ' . print_r($response_body, true));
            }
        }
    }
}
