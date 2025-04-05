<?php
/**
 * Plugin Name: UPSC Notes - WhatsApp & Mail Buttons
 * Description: Adds a WhatsApp and Mail button to each blog post with dynamic post URL and parent category in the WhatsApp message.
 * Version: 1.0
 * Author: Ajay Upadhyay
 */

// 1. Admin Settings Page
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

// 2. Register settings
function custom_button_register_settings() {
    register_setting('custom_button_settings_group', 'custom_button_title');
    register_setting('custom_button_settings_group', 'custom_whatsapp_number');
    register_setting('custom_button_settings_group', 'custom_mail_link');
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
        'custom_mail_link',
        'Mail Button Link',
        'custom_mail_link_callback',
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

// 3. Settings Field Callbacks
function custom_button_title_callback() {
    $title = get_option('custom_button_title', 'Get Notes');
    echo '<input type="text" name="custom_button_title" value="' . esc_attr($title) . '" style="width:100%;">';
}

function custom_whatsapp_number_callback() {
    $number = get_option('custom_whatsapp_number', '');
    echo '<input type="text" name="custom_whatsapp_number" value="' . esc_attr($number) . '" style="width:100%;">';
    echo '<p class="description">Include country code. Example: 919999999999</p>';
}

function custom_mail_link_callback() {
    $link = get_option('custom_mail_link', '');
    echo '<input type="url" name="custom_mail_link" value="' . esc_url($link) . '" style="width:100%;">';
}

function custom_mail_title_callback() {
    $title = get_option('custom_mail_title', 'Email Us');
    echo '<input type="text" name="custom_mail_title" value="' . esc_attr($title) . '" style="width:100%;">';
}

// 4. Inject buttons into post content
function insert_custom_button_into_content( $content ) {
    if ( is_single() && get_post_type() === 'post' ) {
        global $post;

        $post_url = get_permalink($post->ID);
        $whatsapp_title = get_option('custom_button_title', 'Get Notes');
        $whatsapp_number = get_option('custom_whatsapp_number', '');
        $mail_link = get_option('custom_mail_link');
        $mail_title = get_option('custom_mail_title', 'Email Us');

        // Get top-level parent category
        $categories = get_the_category($post->ID);
        $parent_category = '';

        if (!empty($categories)) {
            $category = $categories[0];
            while ($category->category_parent != 0) {
                $category = get_category($category->category_parent);
            }
            $parent_category = $category->name;
        }

        // WhatsApp message
        $message = rawurlencode("Hi, I'm interested in the PDF for this post:\n\n🔗 URL: $post_url\n📚 Category: $parent_category");
        $whatsapp_link = !empty($whatsapp_number) ? "https://wa.me/{$whatsapp_number}?text={$message}" : '';

        $button_html = '<div class="custom-post-buttons" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">';

        if ( $whatsapp_link ) {
            $button_html .= '<a href="' . esc_url($whatsapp_link) . '" class="custom-button whatsapp" target="_blank" style="flex:1; text-align: center; padding: 10px; background-color: #25D366; color: white; text-decoration: none; border-radius: 5px;">';
            $button_html .= '<i class="fab fa-whatsapp"></i> ' . esc_html($whatsapp_title);
            $button_html .= '</a>';
        }

        if ( $mail_link ) {
            $button_html .= '<a href="' . esc_url($mail_link) . '" class="custom-button mail" target="_blank" style="flex:1; text-align: center; padding: 10px; background-color: #0073aa; color: white; text-decoration: none; border-radius: 5px;">';
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
