<?php
/**
 * Plugin Name: Code Snippet Display
 * Plugin URI: 
 * Description: A plugin to display beautiful code snippets with syntax highlighting
 * Version: 1.0.0
 * Author: James Glover
 * License: GPL v2 or later
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

// Enqueue necessary styles and scripts
function csd_enqueue_assets() {
    // Prism.js for syntax highlighting
    wp_enqueue_style('prism-css', plugins_url('assets/css/prism.css', __FILE__));
    wp_enqueue_script('prism-js', plugins_url('assets/js/prism.js', __FILE__), array(), '1.0', true);
    
    // Add bash language support explicitly
    wp_enqueue_script('prism-bash', plugins_url('assets/js/prism-bash.js', __FILE__), array('prism-js'), '1.0', true);
    
    // Custom styles and scripts
    wp_enqueue_style('code-snippet-display', plugins_url('assets/css/code-snippet-display.css', __FILE__));
    wp_enqueue_script('code-snippet-display', plugins_url('assets/js/code-snippet-display.js', __FILE__), array('jquery', 'prism-js'), '1.0', true);
    
    // Initialize Prism
    wp_add_inline_script('prism-js', 'Prism.highlightAll();');
}
add_action('wp_enqueue_scripts', 'csd_enqueue_assets');

// Enqueue Google Fonts
function csd_enqueue_google_fonts() {
    $font_family = get_option('csd_font_family');
    
    // Only load Google Fonts if using one of these options
    if (strpos($font_family, 'Source Code Pro') !== false) {
        wp_enqueue_style('google-font-source-code-pro', 'https://fonts.googleapis.com/css2?family=Source+Code+Pro:wght@400;500;600&display=swap');
    } elseif (strpos($font_family, 'Fira Code') !== false) {
        wp_enqueue_style('google-font-fira-code', 'https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&display=swap');
    } elseif (strpos($font_family, 'JetBrains Mono') !== false) {
        wp_enqueue_style('google-font-jetbrains-mono', 'https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600&display=swap');
    }
}
add_action('wp_enqueue_scripts', 'csd_enqueue_google_fonts');
add_action('admin_enqueue_scripts', 'csd_enqueue_google_fonts');

// Register shortcode
function csd_code_snippet_shortcode($atts, $content = null) {
    $atts = shortcode_atts(array(
        'language' => 'bash',
        'title' => ''
    ), $atts);
    
    $content = trim($content);
    
    $output = '<div class="code-snippet-container">';
    $output .= '<div class="code-snippet-header">';
    if ($atts['title']) {
        $output .= '<span class="code-snippet-title">' . esc_html($atts['title']) . '</span>';
    }
    $output .= '<div class="code-snippet-actions">';
    $output .= '<button class="copy-button" title="Copy code">Copy</button>';
    $output .= '</div></div>';
    $output .= '<pre class="line-numbers"><code class="language-' . esc_attr($atts['language']) . '">';
    $output .= esc_html($content);
    $output .= '</code></pre>';
    $output .= '</div>';
    
    return $output;
}
add_shortcode('code-snippet', 'csd_code_snippet_shortcode');

// Add admin menu
function csd_add_admin_menu() {
    add_menu_page(
        'Code Snippet Display Settings', // Page title
        'Code Snippet', // Menu title
        'manage_options', // Capability
        'code-snippet-display', // Menu slug
        'csd_settings_page', // Function to display the page
        'dashicons-code-standards', // Icon
        100 // Position
    );
}
add_action('admin_menu', 'csd_add_admin_menu');

// Register additional settings
function csd_register_settings() {
    register_setting('csd_settings_group', 'csd_font_family');
    register_setting('csd_settings_group', 'csd_background_color', array(
        'default' => '#f5f5f5' // Light grey default
    ));
    register_setting('csd_settings_group', 'csd_header_color', array(
        'default' => '#2d2d2d' // Dark grey default
    ));
    
    // Set default font if not set
    if (!get_option('csd_font_family')) {
        update_option('csd_font_family', 'Monaco, Consolas, "Andale Mono", "DejaVu Sans Mono", monospace');
    }
}
add_action('admin_init', 'csd_register_settings');

// Modify the settings page
function csd_settings_page() {
    // Get saved colors or use defaults
    $background_color = get_option('csd_background_color', '#f5f5f5');
    $header_color = get_option('csd_header_color', '#2d2d2d');
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('csd_settings_group');
            do_settings_sections('code-snippet-display');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Background Color</th>
                    <td>
                        <input type="text" 
                               name="csd_background_color" 
                               value="<?php echo esc_attr($background_color); ?>" 
                               class="color-picker" 
                               data-default-color="#f5f5f5" />
                        <p class="description">Choose the background color for code snippets</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Header Color</th>
                    <td>
                        <input type="text" 
                               name="csd_header_color" 
                               value="<?php echo esc_attr($header_color); ?>" 
                               class="color-picker" 
                               data-default-color="#2d2d2d" />
                        <p class="description">Choose the header banner color for code snippets</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Code Font Family</th>
                    <td>
                        <select name="csd_font_family">
                            <option value="Monaco, Consolas, 'Andale Mono', 'DejaVu Sans Mono', monospace" 
                                <?php selected(get_option('csd_font_family'), "Monaco, Consolas, 'Andale Mono', 'DejaVu Sans Mono', monospace"); ?>>
                                Monaco (Default)
                            </option>
                            <option value="'Source Code Pro', monospace" 
                                <?php selected(get_option('csd_font_family'), "'Source Code Pro', monospace"); ?>>
                                Source Code Pro
                            </option>
                            <option value="'Fira Code', monospace" 
                                <?php selected(get_option('csd_font_family'), "'Fira Code', monospace"); ?>>
                                Fira Code
                            </option>
                            <option value="'JetBrains Mono', monospace" 
                                <?php selected(get_option('csd_font_family'), "'JetBrains Mono', monospace"); ?>>
                                JetBrains Mono
                            </option>
                        </select>
                        <p class="description">Select the font family for your code snippets.</p>
                        <div style="margin-top: 20px;">
                            <p><strong>Example Usage:</strong></p>
                            <pre style="background: #f5f5f5; padding: 15px; border-radius: 4px;">[code-snippet language="bash" title="bash"]
cd /var/www/html
sudo curl -O https://wordpress.org/latest.tar.gz
sudo tar -xvzf latest.tar.gz
[/code-snippet]</pre>
                        </div>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Enqueue color picker assets
function csd_admin_enqueue_scripts($hook) {
    if ('toplevel_page_code-snippet-display' !== $hook) {
        return;
    }
    
    // Add the color picker css file
    wp_enqueue_style('wp-color-picker');
    
    // Include our custom jQuery script with WordPress Color Picker dependency
    wp_enqueue_script('custom-script-handle', plugins_url('assets/js/color-picker.js', __FILE__), array('wp-color-picker'), false, true);
}
add_action('admin_enqueue_scripts', 'csd_admin_enqueue_scripts');

// Add custom CSS based on settings
function csd_add_custom_css() {
    $font_family = get_option('csd_font_family', 'Monaco, Consolas, "Andale Mono", "DejaVu Sans Mono", monospace');
    $background_color = get_option('csd_background_color', '#f5f5f5');
    $header_color = get_option('csd_header_color', '#2d2d2d');
    
    $custom_css = "
        .code-snippet-container pre,
        .code-snippet-container code {
            font-family: {$font_family} !important;
        }
        .code-snippet-container {
            background-color: {$background_color} !important;
        }
        .code-snippet-header {
            background-color: {$header_color} !important;
        }
    ";
    wp_add_inline_style('code-snippet-display', $custom_css);
}
add_action('wp_enqueue_scripts', 'csd_add_custom_css'); 