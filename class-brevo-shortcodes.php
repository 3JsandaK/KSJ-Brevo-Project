<?php
if (!defined('ABSPATH')) exit;

class Brevo_Shortcodes {

    public function __construct() {
        // Register the shortcode. Users can insert [ksj_brevo] anywhere.
        add_shortcode('ksj_brevo', array($this, 'render_shortcode'));
    }
    
    /**
     * Renders the contact form with dynamic attributes.
     *
     * Default attributes are pulled from the settings option (comma separated).
     * If the shortcode is used with attributes, these values override the defaults.
     *
     * Example usage:
     * [ksj_brevo email="user@example.com" first_name="Alice" last_name="Smith" phone="1234567890"]
     */
    public function render_shortcode($atts, $content = null) {
        // Retrieve default attributes from settings (or fall back if not set)
        $admin_default_attrs = get_option('ksj_brevo_default_attributes', 'email,first_name,last_name');
        $default_attrs = array();
        foreach (explode(',', $admin_default_attrs) as $att) {
            $att = trim($att);
            if (!empty($att)) {
                $default_attrs[$att] = '';
            }
        }
        // Merge defaults with attributes provided to the shortcode
        $atts = shortcode_atts($default_attrs, $atts, 'ksj_brevo');
        
        $feedback = '';
        // Process form submission if POSTed
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ksj_brevo_nonce'])) {
            if (!wp_verify_nonce($_POST['ksj_brevo_nonce'], 'ksj_brevo_form')) {
                $feedback = '<div class="ksj-brevo-error">Security check failed. Please try again.</div>';
            } else {
                $post_data = array();
                $errors = array();
                
                // Validate and sanitize each attribute
                foreach ($atts as $key => $default_value) {
                    $value = isset($_POST[$key]) ? sanitize_text_field($_POST[$key]) : '';
                    $post_data[$key] = $value;
                    
                    // Simple example: mark email as required and validate format
                    if ($key == 'email') {
                        if (empty($value)) {
                            $errors[] = "Email is required.";
                        } elseif (!is_email($value)) {
                            $errors[] = "The email address is not valid.";
                        }
                    }
                }
                
                if (!empty($errors)) {
                    $feedback = '<div class="ksj-brevo-error">' . implode('<br>', $errors) . '</div>';
                } else {
                    // Process the form data (e.g., perform API calls or store in the database)
                    // For demonstration, we simply show a success message.
                    $feedback = '<div class="ksj-brevo-success">Your information has been submitted successfully!</div>';
                }
            }
        }
        
        // Build and output the form with feedback if applicable
        $output = $feedback;
        $output .= '<form method="post" action="">';
        $output .= wp_nonce_field('ksj_brevo_form', 'ksj_brevo_nonce', true, false);
        
        // Generate an input field for each attribute
        foreach ($atts as $key => $value) {
            $label_text = ucfirst(str_replace('_', ' ', $key));
            // Preserve the submitted value if present
            $submitted_value = isset($_POST[$key]) ? sanitize_text_field($_POST[$key]) : esc_attr($value);
            $output .= '<p>';
            $output .= '<label for="' . esc_attr($key) . '">' . esc_html($label_text) . '</label><br />';
            $output .= '<input type="text" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . $submitted_value . '" placeholder="' . esc_attr($label_text) . '">';
            $output .= '</p>';
        }
        $output .= '<p><input type="submit" value="Submit"></p>';
        $output .= '</form>';
        
        return $output;
    }
}
