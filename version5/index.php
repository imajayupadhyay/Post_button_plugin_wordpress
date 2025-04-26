<?php
/**
 * Plugin Name: UPSC Notes - WhatsApp & Mail Buttons
 * Description: Adds WhatsApp and Mail buttons to blog posts, with subject and category extracted from post URL. Now Mail button can trigger a popup via class.
 * Version: 1.3
 * Author: Ajay Upadhyay
 */

// 1. Admin Menu Page
function custom_button_settings_page() {
    add_menu_page(
        'Button Settings',
        'Button Settings',
        'manage_options',
        'custom-button-settings',
        'custom_button_settings_callback',
        'dashicons-admin-generic',
        20
    );
}
add_action('admin_menu', 'custom_button_settings_page');

// Settings Page Output
function custom_button_settings_callback() {
    ?>
    <div class="wrap">
        <h1>Button Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('custom_button_settings_group');
            do_settings_sections('custom-button-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// 2. Register Settings
function custom_button_register_settings() {
    register_setting('custom_button_settings_group', 'custom_button_title');
    register_setting('custom_button_settings_group', 'custom_whatsapp_number');
    register_setting('custom_button_settings_group', 'custom_mail_class');
    register_setting('custom_button_settings_group', 'custom_mail_title');

    add_settings_section(
        'custom_button_section',
        'Custom Button Settings',
        null,
        'custom-button-settings'
    );

    add_settings_field(
        'custom_button_title',
        'WhatsApp Button Title',
        'custom_button_title_callback',
        'custom-button-settings',
        'custom_button_section'
    );

    add_settings_field(
        'custom_whatsapp_number',
        'WhatsApp Number (with country code)',
        'custom_whatsapp_number_callback',
        'custom-button-settings',
        'custom_button_section'
    );

    add_settings_field(
        'custom_mail_class',
        'Mail Button Class',
        'custom_mail_class_callback',
        'custom-button-settings',
        'custom_button_section'
    );

    add_settings_field(
        'custom_mail_title',
        'Mail Button Title',
        'custom_mail_title_callback',
        'custom-button-settings',
        'custom_button_section'
    );
}
add_action('admin_init', 'custom_button_register_settings');

// 3. Setting Field Inputs
function custom_button_title_callback() {
    $title = get_option('custom_button_title', 'Get Notes');
    echo '<input type="text" name="custom_button_title" value="' . esc_attr($title) . '" style="width:100%;">';
}

function custom_whatsapp_number_callback() {
    $number = get_option('custom_whatsapp_number', '');
    echo '<input type="text" name="custom_whatsapp_number" value="' . esc_attr($number) . '" style="width:100%;">';
    echo '<p class="description">Include country code. Example: 919999999999</p>';
}

function custom_mail_class_callback() {
    $class = get_option('custom_mail_class', '');
    echo '<input type="text" name="custom_mail_class" value="' . esc_attr($class) . '" style="width:100%;">';
    echo '<p class="description">Enter the popup trigger class (example: my-popup-class)</p>';
}

function custom_mail_title_callback() {
    $title = get_option('custom_mail_title', 'Email Us');
    echo '<input type="text" name="custom_mail_title" value="' . esc_attr($title) . '" style="width:100%;">';
}

// 4. Inject Buttons into Post Content
function insert_custom_button_into_content( $content ) {
    if ( is_single() && get_post_type() === 'post' ) {
        global $post;

        $post_url = get_permalink($post->ID);
        $whatsapp_title = get_option('custom_button_title', 'Get Notes');
        $whatsapp_number = get_option('custom_whatsapp_number', '');
        $mail_class = get_option('custom_mail_class');
        $mail_title = get_option('custom_mail_title', 'Email Us');

        // Extract category and subject from URL segments
        $url_path = parse_url($post_url, PHP_URL_PATH);
        $segments = array_filter(explode('/', trim($url_path, '/')));
        $segments = array_values($segments);

        $segment_count = count($segments);
        $subject = '';
        $category = '';

        if ($segment_count >= 3) {
            $subject_slug = $segments[$segment_count - 2];
            $category_slug = $segments[$segment_count - 3];

            $subject = ucwords(str_replace('-', ' ', sanitize_title($subject_slug)));
            $category = ucwords(str_replace('-', ' ', sanitize_title($category_slug)));
        }

        // Build WhatsApp message
        $message = rawurlencode("Hi, I'm interested in the PDF for this post:\n\n*📚 Category:* $category\n*📖 Subject:* $subject");
        $whatsapp_link = !empty($whatsapp_number) ? "https://wa.me/{$whatsapp_number}?text={$message}" : '';

        // Build HTML buttons
        $button_html = '<div class="custom-post-buttons" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">';

        if ( $whatsapp_link ) {
            $button_html .= '<a href="' . esc_url($whatsapp_link) . '" class="custom-button whatsapp" target="_blank" style="flex:1; text-align: center; padding: 10px; background-color: #25D366; color: white; text-decoration: none; border-radius: 5px;">';
            $button_html .= '<i class="fab fa-whatsapp"></i> ' . esc_html($whatsapp_title);
            $button_html .= '</a>';
        }

        if ( $mail_class ) {
            $button_html .= '<a href="javascript:void(0);" class="custom-button mail ' . esc_attr($mail_class) . '" style="flex:1; text-align: center; padding: 10px; background-color: #0073aa; color: white; text-decoration: none; border-radius: 5px;">';
            $button_html .= '<i class="fas fa-envelope"></i> ' . esc_html($mail_title);
            $button_html .= '</a>';
        }

        $button_html .= '</div>';

        return $button_html . $content;
    }

    return $content;
}
add_filter('the_content', 'insert_custom_button_into_content');

// 5. Load Font Awesome
function enqueue_font_awesome() {
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css');
}
add_action('wp_enqueue_scripts', 'enqueue_font_awesome');
?>
