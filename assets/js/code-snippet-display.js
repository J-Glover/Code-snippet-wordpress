jQuery(document).ready(function($) {
    // Copy button functionality
    $('.copy-button').on('click', function() {
        const codeBlock = $(this).closest('.code-snippet-container').find('code');
        const textArea = document.createElement('textarea');
        textArea.value = codeBlock.text();
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        
        // Show feedback
        const originalText = $(this).text();
        $(this).text('Copied!');
        setTimeout(() => {
            $(this).text(originalText);
        }, 2000);
    });
    
    // Edit button functionality (you can customize this based on your needs)
    $('.edit-button').on('click', function() {
        const codeBlock = $(this).closest('.code-snippet-container').find('code');
        // Add your edit functionality here
        alert('Edit functionality can be customized based on your requirements');
    });
}); 