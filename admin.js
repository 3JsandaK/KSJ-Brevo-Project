jQuery(document).ready(function($) {
    $('#brevo-refresh-attributes').click(function() {
        $.post(ksjBrevoData.ajax_url, {
            action: 'brevo_fetch_attributes',
            security: ksjBrevoData.nonce,
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
