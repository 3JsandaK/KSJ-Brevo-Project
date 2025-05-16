document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('brevo-contact-form');
  const message = document.getElementById('brevo-message');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const data = {
      email: form.email.value,
      attributes: {
        FIRSTNAME: form.firstname.value,
        LASTNAME: form.lastname.value
      }
    };

    try {
      const res = await fetch(BrevoData.apiUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });

      const json = await res.json();
      if (json.code === 201 || json.code === 204) {
        message.innerHTML = "✅ You're subscribed!";
        form.reset();
      } else {
        message.innerHTML = "❌ " + (json.body?.message || "Something went wrong.");
      }
    } catch (err) {
      message.innerHTML = "❌ Network error.";
    }
  });
});
jQuery(document).ready(function($) {
    $('#brevo-test-connection').click(function() {
        $.post(ajaxurl, {
            action: 'brevo_test_connection',
            security: '<?php echo wp_create_nonce("brevo_test_nonce"); ?>',
        }, function(response) {
            if (response.success) {
                $('#brevo-test-result').html("<div style='color: green;'>" + response.data + "</div>");
            } else {
                $('#brevo-test-result').html("<div style='color: red;'>" + response.data + "</div>");
            }
        });
    });
});
