(function($) {
    // Add Color Picker to all inputs that have 'color-picker' class
    $(function() {
        $('.color-picker').wpColorPicker({
            change: function(event, ui) {
                // Update the preview in real-time
                updatePreview();
            }
        });
    });

    function updatePreview() {
        var backgroundColor = $('input[name="csd_background_color"]').val();
        var headerColor = $('input[name="csd_header_color"]').val();
        
        // Update the preview container
        $('#preview-container').css('background-color', backgroundColor);
        $('#preview-header').css('background-color', headerColor);
    }
})(jQuery); 