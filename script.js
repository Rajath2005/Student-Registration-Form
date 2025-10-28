$(document).ready(function() {
    // Form validation
    $('#registrationForm input, #registrationForm select, #registrationForm textarea').on('blur', function() {
        validateField($(this));
    });

    // Phone number validation
    $('#phone').on('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Form submission
    $('#registrationForm').on('submit', function(e) {
        e.preventDefault();
        
        let isValid = true;
        $(this).find('input, select, textarea').each(function() {
            if (!validateField($(this))) {
                isValid = false;
            }
        });

        if (isValid) {
            submitForm();
        }
    });
});

function validateField($field) {
    let isValid = true;
    let errorMsg = '';

    // Remove previous error
    $field.removeClass('error');
    $field.next('.error-message').remove();

    // Required field check
    if ($field.prop('required') && !$field.val()) {
        isValid = false;
        errorMsg = 'This field is required';
    }

    // Email validation
    if ($field.attr('type') === 'email' && $field.val()) {
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test($field.val())) {
            isValid = false;
            errorMsg = 'Please enter a valid email address';
        }
    }

    // Phone validation
    if ($field.attr('id') === 'phone' && $field.val()) {
        if ($field.val().length !== 10) {
            isValid = false;
            errorMsg = 'Phone number must be 10 digits';
        }
    }

    if (!isValid) {
        $field.addClass('error');
        $field.after('<span class="error-message">' + errorMsg + '</span>');
    }

    return isValid;
}

function submitForm() {
    let formData = $('#registrationForm').serialize();
    
    // Disable submit button to prevent double submission
    $('.submit-btn').prop('disabled', true).text('Submitting...');
    
    $.ajax({
        url: 'process.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            // Re-enable submit button
            $('.submit-btn').prop('disabled', false).text('Submit Application');
            
            // Check if there's an error in response
            if (response.error) {
                alert('Error: ' + response.error);
            } else {
                displayResult(response);
            }
        },
        error: function(xhr, status, error) {
            // Re-enable submit button
            $('.submit-btn').prop('disabled', false).text('Submit Application');
            
            console.log('XHR Status:', xhr.status);
            console.log('Response Text:', xhr.responseText);
            console.log('Error:', error);
            
            // Try to parse response as JSON
            try {
                let errorResponse = JSON.parse(xhr.responseText);
                if (errorResponse.error) {
                    alert('Error: ' + errorResponse.error);
                } else {
                    alert('An error occurred. Please check console for details.');
                }
            } catch(e) {
                // Response is not JSON, show generic error
                alert('An error occurred. Please try again. Check browser console (F12) for details.');
                console.error('Server returned non-JSON response:', xhr.responseText);
            }
        }
    });
}

function displayResult(data) {
    let html = '<div class="success-icon">✓</div>';
    html += '<div class="result-card">';
    
    for (let key in data) {
        // Skip internal fields
        if (key === 'registrationId') continue;
        
        let label = key.replace(/([A-Z])/g, ' $1').trim();
        label = label.charAt(0).toUpperCase() + label.slice(1);
        
        html += '<div class="result-item">';
        html += '<div class="result-label">' + label + ':</div>';
        html += '<div class="result-value">' + escapeHtml(data[key]) + '</div>';
        html += '</div>';
    }
    
    html += '</div>';
    
    $('#resultContent').html(html);
    $('#formSection').fadeOut(300, function() {
        $('#resultSection').fadeIn(300);
    });
}

function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    let map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
}
