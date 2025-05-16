<?php

class Brevo_API {
    public static function add_contact($email, $attributes = [], $listIds = [2]) {
        $api_key = get_option('brevo_api_key', '');
        if (!$api_key) {
            return [
                'code' => 400,
                'body' => ['message' => 'Missing Brevo API key in settings.']
            ];
        }

        $response = wp_remote_post('https://api.brevo.com/v3/contacts', [
            'headers' => [
                'api-key' => $api_key,
                'Content-Type' => 'application/json'
            ],
            'body' => json_encode([
                'email' => $email,
                'attributes' => $attributes,
                'listIds' => $listIds,
                'updateEnabled' => true
            ])
        ]);

        return [
            'code' => wp_remote_retrieve_response_code($response),
            'body' => json_decode(wp_remote_retrieve_body($response), true)
        ];
    }
}
