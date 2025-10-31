<?php
/**
 * Plugin Name:       Custom Schema Box Generator
 * Description:       Adds a meta box to all post types for inserting custom schema (JSON-LD) structured data into the page head.
 * Version:           2.3.0
 * Author:            FARAZFRANK
 * Author URI:        https://wpfrank.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       custom-schema-box-generator
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue admin styles and scripts.
 *
 * @param string $hook The current admin page hook.
 */
function csg_enqueue_admin_assets( $hook ) {
    // Only load on our settings page
    if ( 'settings_page_csgbxgen-settings' !== $hook ) {
        return;
    }

    // Enqueue CSS
    wp_enqueue_style(
        'csg-admin-styles',
        plugin_dir_url( __FILE__ ) . 'assets/css/admin-styles.css',
        array(),
        '2.2.0'
    );

    // Enqueue JavaScript
    wp_enqueue_script(
        'csg-admin-scripts',
        plugin_dir_url( __FILE__ ) . 'assets/js/admin-scripts.js',
        array( 'jquery' ),
        '2.2.0',
        true
    );
}
add_action( 'admin_enqueue_scripts', 'csg_enqueue_admin_assets' );

/**
 * Add settings page to admin menu.
 */
function csg_add_settings_page() {
    add_options_page(
        __( 'Custom Schema Box Generator', 'custom-schema-box-generator' ), // Page title
        __( 'Schema Box Generator', 'custom-schema-box-generator' ), // Menu title
        'manage_options', // Capability
        'csgbxgen-settings', // Menu slug
        'csg_render_settings_page' // Callback function
    );
}
add_action( 'admin_menu', 'csg_add_settings_page' );

/**
 * Register settings.
 */
function csg_register_settings() {
    register_setting(
        'csg_settings_group', // Option group
        'csg_enabled_post_types', // Option name
        array(
            'type' => 'array',
            'sanitize_callback' => 'csg_sanitize_post_types',
            'default' => array(),
        )
    );

    register_setting(
        'csg_settings_group', // Option group
        'csg_enabled_pages', // Option name
        array(
            'type' => 'array',
            'sanitize_callback' => 'csg_sanitize_enabled_items',
            'default' => array(),
        )
    );

    register_setting(
        'csg_settings_group', // Option group
        'csg_enabled_posts', // Option name
        array(
            'type' => 'array',
            'sanitize_callback' => 'csg_sanitize_enabled_items',
            'default' => array(),
        )
    );

    register_setting(
        'csg_settings_group', // Option group
        'csg_enabled_cpt_items', // Option name
        array(
            'type' => 'array',
            'sanitize_callback' => 'csg_sanitize_enabled_items',
            'default' => array(),
        )
    );

    register_setting(
        'csg_settings_group', // Option group
        'csg_meta_box_type', // Option name
        array(
            'type' => 'array',
            'sanitize_callback' => 'csg_sanitize_meta_box_type',
            'default' => array(),
        )
    );

    register_setting(
        'csg_settings_group', // Option group
        'csg_dynamic_schema', // Option name
        array(
            'type' => 'array',
            'sanitize_callback' => 'csg_sanitize_dynamic_schema',
            'default' => array(),
        )
    );
}
add_action( 'admin_init', 'csg_register_settings' );

/**
 * Sanitize the post types array.
 *
 * @param array $input The input array.
 * @return array The sanitized array.
 */
function csg_sanitize_post_types( $input ) {
    if ( ! is_array( $input ) ) {
        return array();
    }

    $sanitized = array();
    foreach ( $input as $post_type => $enabled ) {
        $sanitized[ sanitize_key( $post_type ) ] = ( $enabled === '1' ) ? '1' : '0';
    }

    return $sanitized;
}

/**
 * Sanitize the enabled items array (pages, posts, CPT items).
 *
 * @param array $input The input array.
 * @return array The sanitized array.
 */
function csg_sanitize_enabled_items( $input ) {
    if ( ! is_array( $input ) ) {
        return array();
    }

    $sanitized = array();
    foreach ( $input as $item_id => $enabled ) {
        $sanitized[ absint( $item_id ) ] = ( $enabled === '1' ) ? '1' : '0';
    }

    return $sanitized;
}

/**
 * Sanitize the meta box type array.
 *
 * @param array $input The input array.
 * @return array The sanitized array.
 */
function csg_sanitize_meta_box_type( $input ) {
    if ( ! is_array( $input ) ) {
        return array();
    }

    $sanitized = array();
    foreach ( $input as $post_type => $type ) {
        $sanitized[ sanitize_key( $post_type ) ] = in_array( $type, array( 'individual', 'dynamic' ), true ) ? $type : 'individual';
    }

    return $sanitized;
}

/**
 * Sanitize the dynamic schema array.
 *
 * @param array $input The input array.
 * @return array The sanitized array.
 */
function csg_sanitize_dynamic_schema( $input ) {
    if ( ! is_array( $input ) ) {
        return array();
    }

    $sanitized = array();
    foreach ( $input as $post_type => $schema ) {
        $sanitized[ sanitize_key( $post_type ) ] = wp_kses_post( $schema );
    }

    return $sanitized;
}

/**
 * Render the settings page.
 */
function csg_render_settings_page() {
    // Check user capabilities
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Get current tab
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only tab selection
    $current_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'settings';

    // Get current settings
    $enabled_post_types = get_option( 'csg_enabled_post_types', array() );
    $enabled_pages = get_option( 'csg_enabled_pages', array() );
    $enabled_posts = get_option( 'csg_enabled_posts', array() );
    $enabled_cpt_items = get_option( 'csg_enabled_cpt_items', array() );
    $meta_box_types = get_option( 'csg_meta_box_type', array() );
    $dynamic_schemas = get_option( 'csg_dynamic_schema', array() );

    // Get Pages
    $pages = array();
    $page_obj = get_post_type_object( 'page' );
    if ( $page_obj ) {
        $pages[] = $page_obj;
    }

    // Get Posts
    $posts = array();
    $post_obj = get_post_type_object( 'post' );
    if ( $post_obj ) {
        $posts[] = $post_obj;
    }

    // Get Custom Post Types (non-built-in only)
    $custom_post_types = get_post_types( array( '_builtin' => false ), 'objects' );

    // Get all pages
    $all_pages = get_pages( array( 'number' => 500 ) );

    // Get all posts
    $all_posts = get_posts( array( 'numberposts' => 500, 'post_status' => 'any' ) );

    // Get all CPT items
    $all_cpt_items = array();
    foreach ( $custom_post_types as $cpt ) {
        $cpt_posts = get_posts( array(
            'post_type' => $cpt->name,
            'numberposts' => 500,
            'post_status' => 'any'
        ) );
        // Always add to array, even if empty
        $all_cpt_items[ $cpt->name ] = array(
            'label' => $cpt->labels->name,
            'items' => $cpt_posts
        );
    }

    ?>
    <div class="wrap csg-settings-wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

        <!-- Tab Navigation -->
        <h2 class="nav-tab-wrapper">
            <a href="?page=csgbxgen-settings&tab=settings" class="nav-tab <?php echo 'settings' === $current_tab ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Settings', 'custom-schema-box-generator' ); ?>
            </a>
            <a href="?page=csgbxgen-settings&tab=templates" class="nav-tab <?php echo 'templates' === $current_tab ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'Schema Templates', 'custom-schema-box-generator' ); ?>
            </a>
            <a href="?page=csgbxgen-settings&tab=how-to-use" class="nav-tab <?php echo 'how-to-use' === $current_tab ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'How to Use', 'custom-schema-box-generator' ); ?>
            </a>
            <a href="?page=csgbxgen-settings&tab=faq" class="nav-tab <?php echo 'faq' === $current_tab ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( 'FAQ', 'custom-schema-box-generator' ); ?>
            </a>
            <a href="?page=csgbxgen-settings&tab=donate" class="nav-tab <?php echo 'donate' === $current_tab ? 'nav-tab-active' : ''; ?>">
                <?php esc_html_e( '❤️ Donate', 'custom-schema-box-generator' ); ?>
            </a>
        </h2>

        <?php
        // Display content based on current tab
        switch ( $current_tab ) {
            case 'templates':
                csg_render_templates_tab();
                break;
            case 'how-to-use':
                csg_render_how_to_use_tab();
                break;
            case 'faq':
                csg_render_faq_tab();
                break;
            case 'donate':
                csg_render_donate_tab();
                break;
            case 'settings':
            default:
                csg_render_settings_tab( $pages, $posts, $custom_post_types, $all_pages, $all_posts, $all_cpt_items, $enabled_post_types, $enabled_pages, $enabled_posts, $enabled_cpt_items, $meta_box_types, $dynamic_schemas );
                break;
        }
        ?>
    </div>
    <?php
}

/**
 * Render the main settings tab.
 *
 * @param array $pages Page post types.
 * @param array $posts Post post types.
 * @param array $custom_post_types Custom post types.
 * @param array $all_pages All pages.
 * @param array $all_posts All posts.
 * @param array $all_cpt_items All CPT items.
 * @param array $enabled_post_types Enabled post types.
 * @param array $enabled_pages Enabled pages.
 * @param array $enabled_posts Enabled posts.
 * @param array $enabled_cpt_items Enabled CPT items.
 * @param array $meta_box_types Meta box types (individual/dynamic).
 * @param array $dynamic_schemas Dynamic schema templates.
 */
function csg_render_settings_tab( $pages, $posts, $custom_post_types, $all_pages, $all_posts, $all_cpt_items, $enabled_post_types, $enabled_pages, $enabled_posts, $enabled_cpt_items, $meta_box_types, $dynamic_schemas ) {
    ?>

        <?php
        // Debug info - show detected post types (can be removed later)
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only debug display, no data processing
        $show_debug = current_user_can( 'manage_options' ) && isset( $_GET['debug'] ) && sanitize_text_field( wp_unslash( $_GET['debug'] ) ) === '1';
        if ( $show_debug ) :
        ?>
            <div class="csg-debug-box">
                <h3>🔍 Debug Information</h3>

                <div class="csg-debug-section">
                    <p><strong>Built-in Post Types:</strong></p>
                    <ul>
                        <li><strong>page</strong> - Pages <?php echo ! empty( $pages ) ? '✓ Detected' : '✗ Not Found'; ?></li>
                        <li><strong>post</strong> - Posts <?php echo ! empty( $posts ) ? '✓ Detected' : '✗ Not Found'; ?></li>
                    </ul>
                </div>

                <div class="csg-debug-section">
                    <p><strong>Custom Post Types Detected: <?php echo count( $custom_post_types ); ?></strong></p>
                    <?php if ( ! empty( $custom_post_types ) ) : ?>
                        <ul>
                            <?php foreach ( $custom_post_types as $cpt_slug => $cpt_object ) : ?>
                                <li>
                                    <strong><?php echo esc_html( $cpt_object->labels->name ); ?></strong>
                                    (Slug: <code><?php echo esc_html( $cpt_slug ); ?></code>)
                                    <span class="csg-debug-success">✓ Custom Post Type</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else : ?>
                        <p class="csg-debug-error">❌ No custom post types are currently registered.</p>
                        <p>
                            To register custom post types, use plugins like <strong>Custom Post Type UI</strong>, <strong>ACF</strong>,
                            or add custom code to your theme's functions.php file.
                        </p>
                    <?php endif; ?>
                </div>

                <div class="csg-debug-note">
                    <p>
                        <strong>ℹ️ Note:</strong> This debug information helps identify which post types are detected by the plugin.
                        You can hide this by removing <code>?debug=1</code> from the URL.
                    </p>
                </div>
            </div>
        <?php endif; ?>

        <form method="post" action="options.php">
            <?php
            settings_fields( 'csg_settings_group' );
            do_settings_sections( 'csg_settings_group' );
            ?>

            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <td>
                            <h2><?php esc_html_e( 'Enable/Disable Schema Meta Box', 'custom-schema-box-generator' ); ?></h2>
                            <p class="description csg-settings-description">
                                <?php esc_html_e( 'Select which post types should have the Custom Schema meta box available.', 'custom-schema-box-generator' ); ?>
                                <?php
                                // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only debug link, no data processing
                                if ( ! isset( $_GET['debug'] ) ) :
                                ?>
                                    <a href="<?php echo esc_url( add_query_arg( 'debug', '1' ) ); ?>" class="csg-debug-link">[<?php esc_html_e( 'Show Debug Info', 'custom-schema-box-generator' ); ?>]</a>
                                <?php endif; ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            

                            <?php if ( ! empty( $pages ) ) : ?>
                                <fieldset class="csg-post-type-section">
                                    <legend><?php esc_html_e( 'Pages', 'custom-schema-box-generator' ); ?></legend>
                                    <?php foreach ( $pages as $post_type ) : ?>
                                        <?php
                                        $is_enabled = isset( $enabled_post_types[ $post_type->name ] ) && $enabled_post_types[ $post_type->name ] === '1';
                                        $current_type = isset( $meta_box_types[ $post_type->name ] ) ? $meta_box_types[ $post_type->name ] : 'individual';
                                        $current_schema = isset( $dynamic_schemas[ $post_type->name ] ) ? $dynamic_schemas[ $post_type->name ] : '';
                                        ?>
                                        <div class="csg-enable-box">
                                            <label class="csg-enable-label">
                                                <?php echo esc_html( $post_type->labels->singular_name ); ?>
                                            </label>
                                            <div class="csg-enable-radio-group">
                                                <label class="csg-radio-label">
                                                    <input type="radio"
                                                           name="csg_enabled_post_types[<?php echo esc_attr( $post_type->name ); ?>]"
                                                           value="1"
                                                           class="csg-enable-toggle"
                                                           data-post-type="<?php echo esc_attr( $post_type->name ); ?>"
                                                           <?php checked( $is_enabled, true ); ?> />
                                                    <span class="csg-radio-yes"><?php esc_html_e( 'Yes', 'custom-schema-box-generator' ); ?></span>
                                                </label>
                                                <label class="csg-radio-label">
                                                    <input type="radio"
                                                           name="csg_enabled_post_types[<?php echo esc_attr( $post_type->name ); ?>]"
                                                           value="0"
                                                           class="csg-enable-toggle"
                                                           data-post-type="<?php echo esc_attr( $post_type->name ); ?>"
                                                           <?php checked( $is_enabled, false ); ?> />
                                                    <span class="csg-radio-no"><?php esc_html_e( 'No', 'custom-schema-box-generator' ); ?></span>
                                                </label>
                                            </div>
                                        </div>

                                        <?php if ( $is_enabled ) : ?>
                                            <div class="csg-meta-box-type-container" data-post-type="<?php echo esc_attr( $post_type->name ); ?>">
                                                <div class="csg-meta-box-type-selector">
                                                    <label><?php esc_html_e( 'Meta Box Type:', 'custom-schema-box-generator' ); ?></label>
                                                    <div class="csg-meta-box-type-radio-group">
                                                        <label class="csg-meta-box-type-label">
                                                            <input type="radio"
                                                                   id="csg_meta_box_type_individual_<?php echo esc_attr( $post_type->name ); ?>"
                                                                   name="csg_meta_box_type[<?php echo esc_attr( $post_type->name ); ?>]"
                                                                   value="individual"
                                                                   class="csg-meta-box-type-radio"
                                                                   data-post-type="<?php echo esc_attr( $post_type->name ); ?>"
                                                                   <?php checked( $current_type, 'individual' ); ?> />
                                                            <span><?php esc_html_e( 'Individual', 'custom-schema-box-generator' ); ?></span>
                                                        </label>

                                                        <label class="csg-meta-box-type-label">
                                                            <input type="radio"
                                                                   id="csg_meta_box_type_dynamic_<?php echo esc_attr( $post_type->name ); ?>"
                                                                   name="csg_meta_box_type[<?php echo esc_attr( $post_type->name ); ?>]"
                                                                   value="dynamic"
                                                                   class="csg-meta-box-type-radio"
                                                                   data-post-type="<?php echo esc_attr( $post_type->name ); ?>"
                                                                   <?php checked( $current_type, 'dynamic' ); ?> />
                                                            <span><?php esc_html_e( 'Dynamic', 'custom-schema-box-generator' ); ?></span>
                                                        </label>
                                                    </div>
                                                    <div class="csg-mode-description">
                                                        <span class="csg-individual-desc" style="<?php echo $current_type === 'dynamic' ? 'display:none;' : ''; ?>">
                                                            <?php esc_html_e( 'Add unique schema to each page individually. Select specific pages below.', 'custom-schema-box-generator' ); ?>
                                                        </span>
                                                        <span class="csg-dynamic-desc" style="<?php echo $current_type === 'individual' ? 'display:none;' : ''; ?>">
                                                            <?php esc_html_e( 'Use one common schema template for all pages. Placeholders will auto-populate with page data.', 'custom-schema-box-generator' ); ?>
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="csg-individual-list" data-post-type="<?php echo esc_attr( $post_type->name ); ?>" style="<?php echo $current_type === 'dynamic' ? 'display:none;' : ''; ?>">
                                                    <?php if ( ! empty( $all_pages ) ) : ?>
                                                        <div class="csg-items-list-container">
                                                            <div class="csg-items-list-header">
                                                                <span><?php esc_html_e( 'Select Individual Pages:', 'custom-schema-box-generator' ); ?></span>
                                                                <div class="csg-select-all-container">
                                                                    <button type="button" class="csg-select-all-btn"><?php esc_html_e( 'Select All', 'custom-schema-box-generator' ); ?></button>
                                                                    <button type="button" class="csg-deselect-all-btn"><?php esc_html_e( 'Deselect All', 'custom-schema-box-generator' ); ?></button>
                                                                </div>
                                                            </div>
                                                            <div class="csg-items-list">
                                                                <?php foreach ( $all_pages as $page ) : ?>
                                                                    <?php
                                                                    $page_enabled = isset( $enabled_pages[ $page->ID ] ) && $enabled_pages[ $page->ID ] === '1';
                                                                    $page_title = ! empty( $page->post_title ) ? $page->post_title : __( '(no title)', 'custom-schema-box-generator' );
                                                                    ?>
                                                                    <label class="csg-item-checkbox">
                                                                        <input type="checkbox"
                                                                               name="csg_enabled_pages[<?php echo esc_attr( $page->ID ); ?>]"
                                                                               value="1"
                                                                               <?php checked( $page_enabled, true ); ?> />
                                                                        <?php echo esc_html( $page_title ); ?>
                                                                        <span class="csg-item-id">(ID: <?php echo esc_html( $page->ID ); ?>)</span>
                                                                    </label>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="csg-dynamic-schema" data-post-type="<?php echo esc_attr( $post_type->name ); ?>" style="<?php echo $current_type === 'individual' ? 'display:none;' : ''; ?>">
                                                    <label for="csg_dynamic_schema_<?php echo esc_attr( $post_type->name ); ?>">
                                                        <?php esc_html_e( 'Common Schema for All Pages', 'custom-schema-box-generator' ); ?>
                                                    </label>
                                                    <textarea name="csg_dynamic_schema[<?php echo esc_attr( $post_type->name ); ?>]"
                                                              id="csg_dynamic_schema_<?php echo esc_attr( $post_type->name ); ?>"
                                                              rows="12"
                                                              placeholder='<?php echo esc_attr( '{\n  "@context": "https://schema.org",\n  "@type": "Article",\n  "headline": "{{post_title}}",\n  "author": {\n    "@type": "Person",\n    "name": "{{author_name}}"\n  },\n  "publisher": {\n    "@type": "Organization",\n    "name": "{{site_name}}"\n  },\n  "datePublished": "{{post_date}}",\n  "image": "{{featured_image}}"\n}' ); ?>'><?php echo esc_textarea( $current_schema ); ?></textarea>
                                                    <div class="csg-dynamic-schema-help">
                                                        <strong><?php esc_html_e( 'Tip:', 'custom-schema-box-generator' ); ?></strong> <?php esc_html_e( 'Use placeholders to automatically populate data. This schema will apply to all pages of this type.', 'custom-schema-box-generator' ); ?>
                                                    </div>
                                                    <div class="csg-placeholder-preview">
                                                        <h4><?php esc_html_e( 'Available Placeholders', 'custom-schema-box-generator' ); ?></h4>
                                                        <ul class="csg-placeholder-list">
                                                            <li>{{post_title}}</li>
                                                            <li>{{post_excerpt}}</li>
                                                            <li>{{post_date}}</li>
                                                            <li>{{post_modified}}</li>
                                                            <li>{{author_name}}</li>
                                                            <li>{{author_url}}</li>
                                                            <li>{{featured_image}}</li>
                                                            <li>{{post_url}}</li>
                                                            <li>{{site_name}}</li>
                                                            <li>{{site_url}}</li>
                                                            <li>{{site_description}}</li>
                                                            <li>{{site_logo}}</li>
                                                            <li>{{post_category}}</li>
                                                            <li>{{post_category_first}}</li>
                                                            <li>{{post_tags}}</li>
                                                            <li>{{post_id}}</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </fieldset>
                            <?php endif; ?>

                            <?php if ( ! empty( $posts ) ) : ?>
                                <fieldset class="csg-post-type-section">
                                    <legend><?php esc_html_e( 'Posts', 'custom-schema-box-generator' ); ?></legend>
                                    <?php foreach ( $posts as $post_type ) : ?>
                                        <?php
                                        $is_enabled = isset( $enabled_post_types[ $post_type->name ] ) && $enabled_post_types[ $post_type->name ] === '1';
                                        $current_type = isset( $meta_box_types[ $post_type->name ] ) ? $meta_box_types[ $post_type->name ] : 'individual';
                                        $current_schema = isset( $dynamic_schemas[ $post_type->name ] ) ? $dynamic_schemas[ $post_type->name ] : '';
                                        ?>
                                        <div class="csg-enable-box">
                                            <label class="csg-enable-label">
                                                <?php echo esc_html( $post_type->labels->singular_name ); ?>
                                            </label>
                                            <div class="csg-enable-radio-group">
                                                <label class="csg-radio-label">
                                                    <input type="radio"
                                                           name="csg_enabled_post_types[<?php echo esc_attr( $post_type->name ); ?>]"
                                                           value="1"
                                                           class="csg-enable-toggle"
                                                           data-post-type="<?php echo esc_attr( $post_type->name ); ?>"
                                                           <?php checked( $is_enabled, true ); ?> />
                                                    <span class="csg-radio-yes"><?php esc_html_e( 'Yes', 'custom-schema-box-generator' ); ?></span>
                                                </label>
                                                <label class="csg-radio-label">
                                                    <input type="radio"
                                                           name="csg_enabled_post_types[<?php echo esc_attr( $post_type->name ); ?>]"
                                                           value="0"
                                                           class="csg-enable-toggle"
                                                           data-post-type="<?php echo esc_attr( $post_type->name ); ?>"
                                                           <?php checked( $is_enabled, false ); ?> />
                                                    <span class="csg-radio-no"><?php esc_html_e( 'No', 'custom-schema-box-generator' ); ?></span>
                                                </label>
                                            </div>
                                        </div>

                                        <?php if ( $is_enabled ) : ?>
                                            <div class="csg-meta-box-type-container" data-post-type="<?php echo esc_attr( $post_type->name ); ?>">
                                                <div class="csg-meta-box-type-selector">
                                                    <label><?php esc_html_e( 'Meta Box Type:', 'custom-schema-box-generator' ); ?></label>
                                                    <div class="csg-meta-box-type-radio-group">
                                                        <label class="csg-meta-box-type-label">
                                                            <input type="radio"
                                                                   id="csg_meta_box_type_individual_<?php echo esc_attr( $post_type->name ); ?>"
                                                                   name="csg_meta_box_type[<?php echo esc_attr( $post_type->name ); ?>]"
                                                                   value="individual"
                                                                   class="csg-meta-box-type-radio"
                                                                   data-post-type="<?php echo esc_attr( $post_type->name ); ?>"
                                                                   <?php checked( $current_type, 'individual' ); ?> />
                                                            <span><?php esc_html_e( 'Individual', 'custom-schema-box-generator' ); ?></span>
                                                        </label>

                                                        <label class="csg-meta-box-type-label">
                                                            <input type="radio"
                                                                   id="csg_meta_box_type_dynamic_<?php echo esc_attr( $post_type->name ); ?>"
                                                                   name="csg_meta_box_type[<?php echo esc_attr( $post_type->name ); ?>]"
                                                                   value="dynamic"
                                                                   class="csg-meta-box-type-radio"
                                                                   data-post-type="<?php echo esc_attr( $post_type->name ); ?>"
                                                                   <?php checked( $current_type, 'dynamic' ); ?> />
                                                            <span><?php esc_html_e( 'Dynamic', 'custom-schema-box-generator' ); ?></span>
                                                        </label>
                                                    </div>
                                                    <div class="csg-mode-description">
                                                        <span class="csg-individual-desc" style="<?php echo $current_type === 'dynamic' ? 'display:none;' : ''; ?>">
                                                            <?php esc_html_e( 'Add unique schema to each post individually. Select specific posts below.', 'custom-schema-box-generator' ); ?>
                                                        </span>
                                                        <span class="csg-dynamic-desc" style="<?php echo $current_type === 'individual' ? 'display:none;' : ''; ?>">
                                                            <?php esc_html_e( 'Use one common schema template for all posts. Placeholders will auto-populate with post data.', 'custom-schema-box-generator' ); ?>
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="csg-individual-list" data-post-type="<?php echo esc_attr( $post_type->name ); ?>" style="<?php echo $current_type === 'dynamic' ? 'display:none;' : ''; ?>">
                                                    <?php if ( ! empty( $all_posts ) ) : ?>
                                                        <div class="csg-items-list-container">
                                                            <div class="csg-items-list-header">
                                                                <span><?php esc_html_e( 'Select Individual Posts:', 'custom-schema-box-generator' ); ?></span>
                                                                <div class="csg-select-all-container">
                                                                    <button type="button" class="csg-select-all-btn"><?php esc_html_e( 'Select All', 'custom-schema-box-generator' ); ?></button>
                                                                    <button type="button" class="csg-deselect-all-btn"><?php esc_html_e( 'Deselect All', 'custom-schema-box-generator' ); ?></button>
                                                                </div>
                                                            </div>
                                                            <div class="csg-items-list">
                                                                <?php foreach ( $all_posts as $single_post ) : ?>
                                                                    <?php
                                                                    $post_enabled = isset( $enabled_posts[ $single_post->ID ] ) && $enabled_posts[ $single_post->ID ] === '1';
                                                                    $post_title = ! empty( $single_post->post_title ) ? $single_post->post_title : __( '(no title)', 'custom-schema-box-generator' );
                                                                    ?>
                                                                    <label class="csg-item-checkbox">
                                                                        <input type="checkbox"
                                                                               name="csg_enabled_posts[<?php echo esc_attr( $single_post->ID ); ?>]"
                                                                               value="1"
                                                                               <?php checked( $post_enabled, true ); ?> />
                                                                        <?php echo esc_html( $post_title ); ?>
                                                                        <span class="csg-item-id">(ID: <?php echo esc_html( $single_post->ID ); ?>)</span>
                                                                    </label>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="csg-dynamic-schema" data-post-type="<?php echo esc_attr( $post_type->name ); ?>" style="<?php echo $current_type === 'individual' ? 'display:none;' : ''; ?>">
                                                    <div class="csg-dynamic-schema-container">
                                                        <label for="csg_dynamic_schema_<?php echo esc_attr( $post_type->name ); ?>">
                                                            <strong><?php esc_html_e( 'Common Schema for All Posts:', 'custom-schema-box-generator' ); ?></strong>
                                                        </label>
                                                        <p class="description">
                                                            <?php esc_html_e( 'Enter JSON-LD schema code. Use placeholders like {{post_title}}, {{site_name}}, {{post_category}}, etc.', 'custom-schema-box-generator' ); ?>
                                                        </p>
                                                        <textarea name="csg_dynamic_schema[<?php echo esc_attr( $post_type->name ); ?>]"
                                                                  id="csg_dynamic_schema_<?php echo esc_attr( $post_type->name ); ?>"
                                                                  rows="10"
                                                                  class="large-text code"
                                                                  style="width:100%; font-family: monospace;"><?php echo esc_textarea( $current_schema ); ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </fieldset>
                            <?php endif; ?>

                            <?php if ( ! empty( $custom_post_types ) ) : ?>
                                <?php foreach ( $custom_post_types as $post_type ) : ?>
                                    <fieldset class="csg-post-type-section">
                                        <legend>
                                            <?php echo esc_html( $post_type->labels->name ); ?> <span class="csg-label-note">(Custom Post Type)</span>
                                        </legend>

                                        <?php
                                        $is_enabled = isset( $enabled_post_types[ $post_type->name ] ) && $enabled_post_types[ $post_type->name ] === '1';
                                        $current_type = isset( $meta_box_types[ $post_type->name ] ) ? $meta_box_types[ $post_type->name ] : 'individual';
                                        $current_schema = isset( $dynamic_schemas[ $post_type->name ] ) ? $dynamic_schemas[ $post_type->name ] : '';
                                        ?>
                                        <div class="csg-enable-box">
                                            <label class="csg-enable-label">
                                                Enable for <?php echo esc_html( $post_type->labels->singular_name ); ?>
                                            </label>
                                            <div class="csg-enable-radio-group">
                                                <label class="csg-radio-label">
                                                    <input type="radio"
                                                           name="csg_enabled_post_types[<?php echo esc_attr( $post_type->name ); ?>]"
                                                           value="1"
                                                           class="csg-enable-toggle"
                                                           data-post-type="<?php echo esc_attr( $post_type->name ); ?>"
                                                           <?php checked( $is_enabled, true ); ?> />
                                                    <span class="csg-radio-yes">Yes</span>
                                                </label>
                                                <label class="csg-radio-label">
                                                    <input type="radio"
                                                           name="csg_enabled_post_types[<?php echo esc_attr( $post_type->name ); ?>]"
                                                           value="0"
                                                           class="csg-enable-toggle"
                                                           data-post-type="<?php echo esc_attr( $post_type->name ); ?>"
                                                           <?php checked( $is_enabled, false ); ?> />
                                                    <span class="csg-radio-no">No</span>
                                                </label>
                                            </div>
                                        </div>

                                        <?php if ( $is_enabled ) : ?>
                                            <div class="csg-meta-box-type-container" data-post-type="<?php echo esc_attr( $post_type->name ); ?>">
                                                <div class="csg-meta-box-type-selector">
                                                    <label><?php esc_html_e( 'Meta Box Type:', 'custom-schema-box-generator' ); ?></label>
                                                    <div class="csg-meta-box-type-radio-group">
                                                        <label class="csg-meta-box-type-label">
                                                            <input type="radio"
                                                                   id="csg_meta_box_type_individual_<?php echo esc_attr( $post_type->name ); ?>"
                                                                   name="csg_meta_box_type[<?php echo esc_attr( $post_type->name ); ?>]"
                                                                   value="individual"
                                                                   class="csg-meta-box-type-radio"
                                                                   data-post-type="<?php echo esc_attr( $post_type->name ); ?>"
                                                                   <?php checked( $current_type, 'individual' ); ?> />
                                                            <span><?php esc_html_e( 'Individual', 'custom-schema-box-generator' ); ?></span>
                                                        </label>

                                                        <label class="csg-meta-box-type-label">
                                                            <input type="radio"
                                                                   id="csg_meta_box_type_dynamic_<?php echo esc_attr( $post_type->name ); ?>"
                                                                   name="csg_meta_box_type[<?php echo esc_attr( $post_type->name ); ?>]"
                                                                   value="dynamic"
                                                                   class="csg-meta-box-type-radio"
                                                                   data-post-type="<?php echo esc_attr( $post_type->name ); ?>"
                                                                   <?php checked( $current_type, 'dynamic' ); ?> />
                                                            <span><?php esc_html_e( 'Dynamic', 'custom-schema-box-generator' ); ?></span>
                                                        </label>
                                                    </div>
                                                    <div class="csg-mode-description">
                                                        <span class="csg-individual-desc" style="<?php echo $current_type === 'dynamic' ? 'display:none;' : ''; ?>">
                                                            <?php
                                                            /* translators: %s: Post type name */
                                                            printf( esc_html__( 'Add unique schema to each %s individually. Select specific items below.', 'custom-schema-box-generator' ), esc_html( strtolower( $post_type->labels->singular_name ) ) );
                                                            ?>
                                                        </span>
                                                        <span class="csg-dynamic-desc" style="<?php echo $current_type === 'individual' ? 'display:none;' : ''; ?>">
                                                            <?php
                                                            /* translators: %s: Post type name */
                                                            printf( esc_html__( 'Use one common schema template for all %s. Placeholders will auto-populate with data.', 'custom-schema-box-generator' ), esc_html( strtolower( $post_type->labels->name ) ) );
                                                            ?>
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="csg-individual-list" data-post-type="<?php echo esc_attr( $post_type->name ); ?>" style="<?php echo $current_type === 'dynamic' ? 'display:none;' : ''; ?>">
                                                    <?php if ( isset( $all_cpt_items[ $post_type->name ] ) && ! empty( $all_cpt_items[ $post_type->name ]['items'] ) ) : ?>
                                                        <div class="csg-items-list-container">
                                                            <div class="csg-items-list-header">
                                                                <span>Select Individual <?php echo esc_html( $all_cpt_items[ $post_type->name ]['label'] ); ?>:</span>
                                                                <div class="csg-select-all-container">
                                                                    <button type="button" class="csg-select-all-btn">Select All</button>
                                                                    <button type="button" class="csg-deselect-all-btn">Deselect All</button>
                                                                </div>
                                                            </div>
                                                            <div class="csg-items-list">
                                                                <?php foreach ( $all_cpt_items[ $post_type->name ]['items'] as $cpt_item ) : ?>
                                                                    <?php
                                                                    $cpt_item_enabled = isset( $enabled_cpt_items[ $cpt_item->ID ] ) && $enabled_cpt_items[ $cpt_item->ID ] === '1';
                                                                    $cpt_item_title = ! empty( $cpt_item->post_title ) ? $cpt_item->post_title : __( '(no title)', 'custom-schema-box-generator' );
                                                                    ?>
                                                                    <label class="csg-item-checkbox">
                                                                        <input type="checkbox"
                                                                               name="csg_enabled_cpt_items[<?php echo esc_attr( $cpt_item->ID ); ?>]"
                                                                               value="1"
                                                                               <?php checked( $cpt_item_enabled, true ); ?> />
                                                                        <?php echo esc_html( $cpt_item_title ); ?>
                                                                        <span class="csg-item-id">(ID: <?php echo esc_html( $cpt_item->ID ); ?>)</span>
                                                                    </label>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    <?php else : ?>
                                                        <div class="csg-warning-box">
                                                            <p>
                                                                <strong>Note:</strong> No <?php echo esc_html( strtolower( $post_type->labels->name ) ); ?> found. Create some <?php echo esc_html( strtolower( $post_type->labels->name ) ); ?> first.
                                                            </p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="csg-dynamic-schema" data-post-type="<?php echo esc_attr( $post_type->name ); ?>" style="<?php echo $current_type === 'individual' ? 'display:none;' : ''; ?>">
                                                    <div class="csg-dynamic-schema-container">
                                                        <label for="csg_dynamic_schema_<?php echo esc_attr( $post_type->name ); ?>">
                                                            <strong>
                                                                <?php
                                                                /* translators: %s: Post type name */
                                                                printf( esc_html__( 'Common Schema for All %s:', 'custom-schema-box-generator' ), esc_html( $post_type->labels->name ) );
                                                                ?>
                                                            </strong>
                                                        </label>
                                                        <p class="description">
                                                            <?php esc_html_e( 'Enter JSON-LD schema code. Use placeholders like {{post_title}}, {{site_name}}, {{post_category}}, etc.', 'custom-schema-box-generator' ); ?>
                                                        </p>
                                                        <textarea name="csg_dynamic_schema[<?php echo esc_attr( $post_type->name ); ?>]"
                                                                  id="csg_dynamic_schema_<?php echo esc_attr( $post_type->name ); ?>"
                                                                  rows="10"
                                                                  class="large-text code"
                                                                  style="width:100%; font-family: monospace;"><?php echo esc_textarea( $current_schema ); ?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </fieldset>
                                <?php endforeach; ?>
                            <?php else : ?>
                                <fieldset class="csg-post-type-section">
                                    <legend>
                                        Custom Post Types
                                    </legend>
                                    <div class="csg-info-box">
                                        <p>
                                            <strong>ℹ️ No Custom Post Types Found</strong><br>
                                            <span>Your WordPress site doesn't have any custom post types registered yet. Custom post types can be created using plugins like ACF, CPT UI, or custom code.</span>
                                        </p>
                                    </div>
                                </fieldset>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button( __( 'Save Settings', 'custom-schema-box-generator' ) ); ?>
        </form>
    <?php
}

/**
 * Render the Schema Templates tab.
 */
function csg_render_templates_tab() {
    ?>
    <div class="csg-templates-container">
        <h2>Schema Templates Library</h2>
        <p class="description">Click on any template to copy the JSON code and paste it into your post/page schema field.</p>

        <div class="csg-templates-grid">
            <?php
            $templates = csg_get_schema_templates();
            foreach ( $templates as $template_id => $template ) :
            ?>
                <div class="csg-template-card">
                    <div class="csg-template-header">
                        <h3><?php echo esc_html( $template['name'] ); ?></h3>
                        <span class="csg-template-type"><?php echo esc_html( $template['type'] ); ?></span>
                    </div>
                    <p class="csg-template-description"><?php echo esc_html( $template['description'] ); ?></p>
                    <div class="csg-template-actions">
                        <button type="button" class="button button-primary csg-copy-template" data-template-id="<?php echo esc_attr( $template_id ); ?>">
                            Copy Template
                        </button>
                        <button type="button" class="button csg-view-template" data-template-id="<?php echo esc_attr( $template_id ); ?>">
                            View Code
                        </button>
                    </div>
                    <pre class="csg-template-code" id="template-<?php echo esc_attr( $template_id ); ?>" style="display:none;"><?php echo esc_html( wp_json_encode( $template['schema'], JSON_PRETTY_PRINT ) ); ?></pre>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Template Preview Modal -->
        <div id="csg-template-modal" class="csg-modal" style="display:none;">
            <div class="csg-modal-content">
                <span class="csg-modal-close">&times;</span>
                <h2 id="csg-modal-title">Template Code</h2>
                <pre id="csg-modal-code"></pre>
                <button type="button" class="button button-primary csg-modal-copy">Copy to Clipboard</button>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Render the How to Use tab.
 */
function csg_render_how_to_use_tab() {
    ?>
    <div class="csg-how-to-use">
        <h2>How to Use Custom Schema Box Generator</h2>

        <div class="csg-guide-section">
            <h3>📋 Step 1: Enable Schema for Post Types</h3>
            <ol>
                <li>Go to <strong>Settings → Schema Generator → Settings</strong> tab</li>
                <li>Find the post type you want to add schema to (Pages, Posts, or Custom Post Types)</li>
                <li>Select <strong>"Yes"</strong> to enable the schema meta box for that post type</li>
                <li>Check the individual items where you want to add schema</li>
                <li>Click <strong>"Save Settings"</strong></li>
            </ol>
            <div class="csg-guide-image">
                <p><em>💡 Tip: Use "Select All" button to quickly enable schema for all items in a post type.</em></p>
            </div>
        </div>

        <div class="csg-guide-section">
            <h3>✏️ Step 2: Add Schema to Your Content</h3>
            <ol>
                <li>Edit any Page, Post, or Custom Post Type where you enabled schema</li>
                <li>Scroll down to find the <strong>"Custom Schema (JSON-LD)"</strong> meta box</li>
                <li>Enter your JSON-LD schema code in the text area</li>
                <li>Click <strong>"Update"</strong> or <strong>"Publish"</strong> to save</li>
            </ol>
            <div class="csg-guide-example">
                <h4>Example Schema Code:</h4>
                <pre>{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "{{post_title}}",
  "author": {
    "@type": "Person",
    "name": "{{author_name}}"
  },
  "datePublished": "{{post_date}}",
  "image": "{{featured_image}}"
}</pre>
            </div>
        </div>

        <div class="csg-guide-section">
            <h3>📚 Step 3: Use Schema Templates (Recommended)</h3>
            <ol>
                <li>Go to <strong>Settings → Schema Generator → Schema Templates</strong> tab</li>
                <li>Browse available templates (Article, Product, FAQ, etc.)</li>
                <li>Click <strong>"Copy Template"</strong> on the template you want</li>
                <li>Go to your post/page editor</li>
                <li>Paste the template into the Custom Schema meta box</li>
                <li>Customize the values to match your content</li>
                <li>Save your post/page</li>
            </ol>
            <div class="csg-guide-image">
                <p><em>💡 Tip: Templates include all required fields and proper structure, reducing errors.</em></p>
            </div>
        </div>

        <div class="csg-guide-section">
            <h3>🔍 Step 4: Test Your Schema</h3>
            <ol>
                <li>Save and publish your post/page with schema</li>
                <li>Visit the page on your website</li>
                <li>Right-click and select <strong>"View Page Source"</strong></li>
                <li>Search for <code>&lt;script type="application/ld+json"&gt;</code></li>
                <li>Verify your schema appears in the HTML</li>
                <li>Test with <a href="https://search.google.com/test/rich-results" target="_blank">Google Rich Results Test</a></li>
            </ol>
        </div>

        <div class="csg-guide-section">
            <h3>⚙️ Step 5: Using Dynamic Placeholders</h3>
            <p>You can use placeholders that automatically populate with post data:</p>
            <p><em><strong>Note:</strong> The "Example Output" column shows sample data for illustration purposes only. Actual values will be dynamically generated from your WordPress site content.</em></p>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Placeholder</th>
                        <th>Description</th>
                        <th>Example Output</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>{{post_title}}</code></td>
                        <td>Post/Page Title</td>
                        <td>My Awesome Article</td>
                    </tr>
                    <tr>
                        <td><code>{{post_excerpt}}</code></td>
                        <td>Post Excerpt</td>
                        <td>This is a brief summary...</td>
                    </tr>
                    <tr>
                        <td><code>{{post_date}}</code></td>
                        <td>Publication Date (ISO 8601)</td>
                        <td>2025-01-15T10:30:00+00:00</td>
                    </tr>
                    <tr>
                        <td><code>{{post_modified}}</code></td>
                        <td>Last Modified Date (ISO 8601)</td>
                        <td>2025-01-20T14:30:00+00:00</td>
                    </tr>
                    <tr>
                        <td><code>{{author_name}}</code></td>
                        <td>Author Display Name</td>
                        <td>John Doe</td>
                    </tr>
                    <tr>
                        <td><code>{{author_url}}</code></td>
                        <td>Author Archive URL</td>
                        <td>https://example.com/author/john-doe/</td>
                    </tr>
                    <tr>
                        <td><code>{{featured_image}}</code></td>
                        <td>Featured Image URL</td>
                        <td>https://example.com/image.jpg</td>
                    </tr>
                    <tr>
                        <td><code>{{post_url}}</code></td>
                        <td>Post Permalink</td>
                        <td>https://example.com/my-post/</td>
                    </tr>
                    <tr>
                        <td><code>{{site_name}}</code></td>
                        <td>Website Name</td>
                        <td>My Awesome Website</td>
                    </tr>
                    <tr>
                        <td><code>{{site_url}}</code></td>
                        <td>Website URL</td>
                        <td>https://example.com</td>
                    </tr>
                    <tr>
                        <td><code>{{site_description}}</code></td>
                        <td>Website Tagline</td>
                        <td>Just another WordPress site</td>
                    </tr>
                    <tr>
                        <td><code>{{post_category}}</code></td>
                        <td>Post Categories (comma-separated)</td>
                        <td>Technology, WordPress, SEO</td>
                    </tr>
                    <tr>
                        <td><code>{{post_category_first}}</code></td>
                        <td>First Post Category</td>
                        <td>Technology</td>
                    </tr>
                    <tr>
                        <td><code>{{post_tags}}</code></td>
                        <td>Post Tags (comma-separated)</td>
                        <td>tutorial, guide, tips</td>
                    </tr>
                    <tr>
                        <td><code>{{post_id}}</code></td>
                        <td>Post ID</td>
                        <td>123</td>
                    </tr>
                </tbody>
            </table>
            <div class="csg-guide-example">
                <h4>Example with Placeholders:</h4>
                <pre>{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": "{{post_title}}",
  "author": {
    "@type": "Person",
    "name": "{{author_name}}",
    "url": "{{author_url}}"
  },
  "publisher": {
    "@type": "Organization",
    "name": "{{site_name}}",
    "url": "{{site_url}}"
  },
  "datePublished": "{{post_date}}",
  "dateModified": "{{post_modified}}",
  "image": "{{featured_image}}",
  "url": "{{post_url}}",
  "articleSection": "{{post_category_first}}",
  "keywords": "{{post_tags}}"
}</pre>
                <p><em>💡 The placeholders will be automatically replaced with actual values when the page loads.</em></p>
            </div>
        </div>

        <div class="csg-guide-section">
            <h3>🔄 Step 6: Individual vs Dynamic Schema Mode</h3>
            <p>Choose how you want to apply schema to your post types:</p>

            <h4><strong>Individual Mode</strong> (Default)</h4>
            <ul>
                <li>Add unique schema to each post/page individually</li>
                <li>Best for content with different schema types</li>
                <li>More control but requires manual setup for each item</li>
                <li>Select specific posts/pages where schema should appear</li>
            </ul>

            <h4><strong>Dynamic Mode</strong> (Recommended for Large Sites)</h4>
            <ul>
                <li>Use one common schema template for all posts of a type</li>
                <li>Automatically applies to all posts/pages of that type</li>
                <li>Uses placeholders to populate data dynamically</li>
                <li>Perfect for blogs with many posts using the same schema structure</li>
                <li>Saves time - no need to add schema to each post individually</li>
            </ul>

            <div class="csg-guide-example">
                <h4>Example: Dynamic Schema for Blog Posts</h4>
                <p>Set "Posts" to <strong>Dynamic Mode</strong> and add this schema:</p>
                <pre>{
  "@context": "https://schema.org",
  "@type": "BlogPosting",
  "headline": "{{post_title}}",
  "description": "{{post_excerpt}}",
  "author": {
    "@type": "Person",
    "name": "{{author_name}}"
  },
  "publisher": {
    "@type": "Organization",
    "name": "{{site_name}}"
  },
  "datePublished": "{{post_date}}",
  "dateModified": "{{post_modified}}",
  "image": "{{featured_image}}",
  "articleSection": "{{post_category_first}}"
}</pre>
                <p><em>💡 This schema will automatically apply to ALL your blog posts with their respective data!</em></p>
            </div>
        </div>

        <div class="csg-guide-section csg-guide-tips">
            <h3>💡 Pro Tips</h3>
            <ul>
                <li><strong>Validate Your Schema:</strong> Always test with Google's Rich Results Test tool</li>
                <li><strong>Use Templates:</strong> Start with templates to avoid syntax errors</li>
                <li><strong>Keep It Simple:</strong> Only add schema types relevant to your content</li>
                <li><strong>Update Regularly:</strong> Keep dates and information current</li>
                <li><strong>Check Console:</strong> Use browser console to check for JSON errors</li>
                <li><strong>Backup First:</strong> Save a copy of working schemas before making changes</li>
            </ul>
        </div>

        <div class="csg-guide-section csg-guide-resources">
            <h3>📖 Helpful Resources</h3>
            <ul>
                <li><a href="https://schema.org/" target="_blank">Schema.org Official Documentation</a></li>
                <li><a href="https://search.google.com/test/rich-results" target="_blank">Google Rich Results Test</a></li>
                <li><a href="https://developers.google.com/search/docs/appearance/structured-data/intro-structured-data" target="_blank">Google Structured Data Guide</a></li>
                <li><a href="https://validator.schema.org/" target="_blank">Schema.org Validator</a></li>
            </ul>
        </div>
    </div>
    <?php
}

/**
 * Render the FAQ tab.
 */
function csg_render_faq_tab() {
    ?>
    <div class="csg-faq">
        <h2>Frequently Asked Questions</h2>

        <div class="csg-faq-item">
            <h3 class="csg-faq-question">❓ What is Schema Markup?</h3>
            <div class="csg-faq-answer">
                <p>Schema markup (also known as structured data) is code that helps search engines understand your content better. It provides additional context about your pages, which can lead to rich results in search engines like Google.</p>
                <p><strong>Benefits:</strong></p>
                <ul>
                    <li>Enhanced search results with rich snippets</li>
                    <li>Better click-through rates</li>
                    <li>Improved SEO performance</li>
                    <li>More visibility in search results</li>
                </ul>
            </div>
        </div>

        <div class="csg-faq-item">
            <h3 class="csg-faq-question">❓ What is JSON-LD?</h3>
            <div class="csg-faq-answer">
                <p>JSON-LD (JavaScript Object Notation for Linked Data) is the recommended format by Google for adding structured data to web pages. It's easy to implement and doesn't interfere with your page's HTML.</p>
                <p>This plugin uses JSON-LD format to add schema markup to your WordPress site.</p>
            </div>
        </div>

        <div class="csg-faq-item">
            <h3 class="csg-faq-question">❓ Do I need to add schema to every page?</h3>
            <div class="csg-faq-answer">
                <p>No, you only need to add schema to pages where it makes sense. For example:</p>
                <ul>
                    <li><strong>Article schema</strong> for blog posts</li>
                    <li><strong>Product schema</strong> for product pages</li>
                    <li><strong>FAQ schema</strong> for FAQ pages</li>
                    <li><strong>Local Business schema</strong> for contact/about pages</li>
                </ul>
                <p>Focus on your most important pages first.</p>
            </div>
        </div>

        <div class="csg-faq-item">
            <h3 class="csg-faq-question">❓ How do I know if my schema is working?</h3>
            <div class="csg-faq-answer">
                <p>Follow these steps to verify:</p>
                <ol>
                    <li>Visit your page in a browser</li>
                    <li>Right-click and select "View Page Source"</li>
                    <li>Search for <code>&lt;script type="application/ld+json"&gt;</code></li>
                    <li>Your schema should appear in the HTML</li>
                    <li>Test with <a href="https://search.google.com/test/rich-results" target="_blank">Google Rich Results Test</a></li>
                </ol>
            </div>
        </div>

        <div class="csg-faq-item">
            <h3 class="csg-faq-question">❓ Can I use multiple schemas on one page?</h3>
            <div class="csg-faq-answer">
                <p>Yes! You can add multiple schema types to a single page. For example, an article page might have:</p>
                <ul>
                    <li>Article schema for the main content</li>
                    <li>BreadcrumbList schema for navigation</li>
                    <li>Organization schema for your company</li>
                </ul>
                <p>Simply include multiple schema objects in your JSON-LD code, or use an array format.</p>
            </div>
        </div>

        <div class="csg-faq-item">
            <h3 class="csg-faq-question">❓ What's the difference between Individual and Dynamic mode?</h3>
            <div class="csg-faq-answer">
                <p><strong>Individual Mode:</strong></p>
                <ul>
                    <li>Add unique schema to each post/page separately</li>
                    <li>Best when each post needs different schema types</li>
                    <li>More control but requires manual setup</li>
                    <li>You select which specific posts/pages get schema</li>
                </ul>
                <p><strong>Dynamic Mode:</strong></p>
                <ul>
                    <li>One schema template applies to all posts of that type</li>
                    <li>Perfect for blogs with many posts using the same structure</li>
                    <li>Uses placeholders to auto-populate data</li>
                    <li>Saves time - no need to add schema to each post</li>
                    <li>Recommended for sites with 50+ posts</li>
                </ul>
            </div>
        </div>

        <div class="csg-faq-item">
            <h3 class="csg-faq-question">❓ What are the placeholders like {{post_title}}?</h3>
            <div class="csg-faq-answer">
                <p>Placeholders are dynamic variables that automatically populate with your post/page data. Instead of manually entering the same information, use placeholders:</p>
                <ul>
                    <li><code>{{post_title}}</code> - Automatically uses your post title</li>
                    <li><code>{{author_name}}</code> - Automatically uses the author's name</li>
                    <li><code>{{post_date}}</code> - Automatically uses the publication date</li>
                    <li><code>{{featured_image}}</code> - Automatically uses the featured image URL</li>
                    <li><code>{{site_name}}</code> - Automatically uses your website name</li>
                    <li><code>{{post_category}}</code> - Automatically uses post categories</li>
                    <li><code>{{post_tags}}</code> - Automatically uses post tags</li>
                </ul>
                <p>This saves time and keeps your schema updated automatically! See the "How to Use" tab for a complete list of available placeholders.</p>
            </div>
        </div>

        <div class="csg-faq-item">
            <h3 class="csg-faq-question">❓ I'm getting JSON errors. What should I do?</h3>
            <div class="csg-faq-answer">
                <p>Common JSON errors and solutions:</p>
                <ul>
                    <li><strong>Missing comma:</strong> Make sure all properties except the last one have a comma</li>
                    <li><strong>Extra comma:</strong> Remove commas after the last property in an object</li>
                    <li><strong>Missing quotes:</strong> All property names and string values need double quotes</li>
                    <li><strong>Unclosed brackets:</strong> Every <code>{</code> needs a matching <code>}</code></li>
                </ul>
                <p><strong>Tip:</strong> Use the Schema Templates to start with valid JSON structure!</p>
            </div>
        </div>

        <div class="csg-faq-item">
            <h3 class="csg-faq-question">❓ Will this plugin slow down my website?</h3>
            <div class="csg-faq-answer">
                <p>No! The plugin is very lightweight and only adds a small JSON-LD script to your pages. This has minimal impact on page load time and can actually improve your SEO performance.</p>
            </div>
        </div>

        <div class="csg-faq-item">
            <h3 class="csg-faq-question">❓ Can I use this with other SEO plugins?</h3>
            <div class="csg-faq-answer">
                <p>Yes! This plugin works alongside other SEO plugins like Yoast SEO, Rank Math, or All in One SEO. However, be careful not to add duplicate schema markup. If your SEO plugin already adds schema for certain content types, you may want to disable it there to avoid conflicts.</p>
            </div>
        </div>

        <div class="csg-faq-item">
            <h3 class="csg-faq-question">❓ How long does it take for Google to show rich results?</h3>
            <div class="csg-faq-answer">
                <p>After adding schema markup:</p>
                <ol>
                    <li>Google needs to crawl your page (can take days to weeks)</li>
                    <li>Your schema must be valid and error-free</li>
                    <li>Your content must meet Google's quality guidelines</li>
                    <li>Rich results are not guaranteed, even with valid schema</li>
                </ol>
                <p>Be patient and focus on creating quality content with proper schema markup.</p>
            </div>
        </div>

        <div class="csg-faq-item">
            <h3 class="csg-faq-question">❓ Where can I find schema examples?</h3>
            <div class="csg-faq-answer">
                <p>Great resources for schema examples:</p>
                <ul>
                    <li><strong>This Plugin:</strong> Check the "Schema Templates" tab for ready-to-use templates</li>
                    <li><a href="https://schema.org/" target="_blank">Schema.org</a> - Official documentation with examples</li>
                    <li><a href="https://developers.google.com/search/docs/appearance/structured-data/search-gallery" target="_blank">Google Search Gallery</a> - Examples for different content types</li>
                    <li><a href="https://jsonld.com/examples/" target="_blank">JSON-LD Examples</a> - Community examples</li>
                </ul>
            </div>
        </div>

        <div class="csg-faq-item">
            <h3 class="csg-faq-question">❓ Can I customize the meta box title or position?</h3>
            <div class="csg-faq-answer">
                <p>Currently, the meta box appears in the normal position on the edit screen. If you need to customize it, you can modify the plugin code or contact support for assistance.</p>
            </div>
        </div>

        <div class="csg-faq-item">
            <h3 class="csg-faq-question">❓ Is this plugin compatible with Gutenberg?</h3>
            <div class="csg-faq-answer">
                <p>Yes! The plugin works with both the Classic Editor and Gutenberg (Block Editor). The schema meta box appears below the editor in both cases.</p>
            </div>
        </div>

        <div class="csg-faq-section csg-faq-support">
            <h3>🆘 Still Need Help?</h3>
            <p>If you have questions not covered here:</p>
            <ul>
                <li>Check the <strong>"How to Use"</strong> tab for detailed instructions</li>
                <li>Review the <strong>"Schema Templates"</strong> for examples</li>
                <li>Test your schema with <a href="https://search.google.com/test/rich-results" target="_blank">Google Rich Results Test</a></li>
                <li>Visit <a href="https://schema.org/" target="_blank">Schema.org</a> for official documentation</li>
            </ul>
        </div>
    </div>
    <?php
}

/**
 * Render the Donate tab.
 */
function csg_render_donate_tab() {
    ?>
    <div class="csg-donate">
        <div class="csg-donate-hero">
            <div class="csg-donate-icon">☕</div>
            <h2>Support Custom Schema Box Generator</h2>
            <p class="csg-donate-tagline">Help keep this plugin free and actively maintained!</p>
        </div>

        <div class="csg-donate-content">
            <div class="csg-donate-main">
                <div class="csg-donate-section">
                    <h3>👋 Hello There!</h3>
                    <p>Thank you for using <strong>Custom Schema Box Generator</strong>! I'm thrilled that this plugin is helping you improve your website's SEO and search engine visibility.</p>
                    <p>This plugin is completely <strong>free</strong> and <strong>open-source</strong>, and I'm committed to keeping it that way. However, developing and maintaining quality WordPress plugins takes considerable time and effort.</p>
                </div>

                <div class="csg-donate-section csg-donate-features">
                    <h3>✨ What You Get (For Free!)</h3>
                    <ul>
                        <li>✅ <strong>20+ Schema Templates</strong> - Ready-to-use JSON-LD templates</li>
                        <li>✅ <strong>Dynamic Placeholders</strong> - Auto-populate with post data</li>
                        <li>✅ <strong>Granular Control</strong> - Enable/disable per post type and item</li>
                        <li>✅ <strong>Comprehensive Guide</strong> - Step-by-step instructions</li>
                        <li>✅ <strong>FAQ Section</strong> - Answers to common questions</li>
                        <li>✅ <strong>Regular Updates</strong> - Bug fixes and new features</li>
                        <li>✅ <strong>WordPress Standards</strong> - Clean, secure code</li>
                        <li>✅ <strong>No Ads or Upsells</strong> - Pure functionality</li>
                    </ul>
                </div>

                <div class="csg-donate-section csg-donate-why">
                    <h3>💝 Why Donate?</h3>
                    <p>Your donation helps me:</p>
                    <ul>
                        <li>☕ <strong>Stay Motivated</strong> - Coffee fuels coding sessions!</li>
                        <li>🔧 <strong>Maintain the Plugin</strong> - Regular updates and bug fixes</li>
                        <li>✨ <strong>Add New Features</strong> - Visual builder, validation, and more</li>
                        <li>📚 <strong>Improve Documentation</strong> - Better guides and examples</li>
                        <li>🆘 <strong>Provide Support</strong> - Help users with issues</li>
                        <li>🌐 <strong>Keep It Free</strong> - No premium versions or paywalls</li>
                    </ul>
                </div>

                <div class="csg-donate-cta">
                    <h3>☕ Buy Me a Cup of Coffee</h3>
                    <p>If this plugin has saved you time or helped improve your SEO, consider buying me a coffee! Even a small donation makes a big difference and shows your appreciation.</p>

                    <div class="csg-donate-buttons">
                        <a href="https://paypal.me/buymecupofcoffee?locale.x=en_GB&country.x=IN" target="_blank" class="csg-donate-button csg-donate-button-primary">
                            <span class="csg-donate-button-icon">☕</span>
                            <span class="csg-donate-button-text">
                                <strong>Buy Me a Coffee</strong>
                                <small>via PayPal</small>
                            </span>
                        </a>
                    </div>

                    <p class="csg-donate-note">
                        <em>💡 Tip: Any amount is appreciated! Whether it's $5 for a coffee or $10 for lunch, your support means the world to me.</em>
                    </p>
                </div>

                <div class="csg-donate-section csg-donate-alternatives">
                    <h3>🌟 Other Ways to Support</h3>
                    <p>Not able to donate? No problem! Here are other ways you can help:</p>
                    <ul>
                        <li>⭐ <strong>Rate the Plugin</strong> - Leave a 5-star review on WordPress.org</li>
                        <li>📢 <strong>Spread the Word</strong> - Tell others about this plugin</li>
                        <li>🐛 <strong>Report Bugs</strong> - Help improve the plugin by reporting issues</li>
                        <li>💡 <strong>Suggest Features</strong> - Share your ideas for improvements</li>
                        <li>📝 <strong>Write a Blog Post</strong> - Share your experience using the plugin</li>
                        <li>🔗 <strong>Share on Social Media</strong> - Help others discover this tool</li>
                    </ul>
                </div>

                <div class="csg-donate-section csg-donate-thanks">
                    <h3>🙏 Thank You!</h3>
                    <p>Whether you donate or not, thank you for using <strong>Custom Schema Box Generator</strong>. Your support—in any form—keeps me motivated to continue developing and improving this plugin.</p>
                    <p>If you have any questions, suggestions, or just want to say hi, feel free to reach out!</p>
                    <p><strong>Happy Schema Building! 🚀</strong></p>
                </div>
            </div>

            <div class="csg-donate-sidebar">
                <div class="csg-donate-stats">
                    <h4>📊 Plugin Stats</h4>
                    <div class="csg-stat-item">
                        <span class="csg-stat-number">20+</span>
                        <span class="csg-stat-label">Schema Templates</span>
                    </div>
                    <div class="csg-stat-item">
                        <span class="csg-stat-number">6</span>
                        <span class="csg-stat-label">Dynamic Placeholders</span>
                    </div>
                    <div class="csg-stat-item">
                        <span class="csg-stat-number">100%</span>
                        <span class="csg-stat-label">Free & Open Source</span>
                    </div>
                    <div class="csg-stat-item">
                        <span class="csg-stat-number">0</span>
                        <span class="csg-stat-label">Ads or Upsells</span>
                    </div>
                </div>

                <div class="csg-donate-testimonial">
                    <h4>💬 What Users Say</h4>
                    <blockquote>
                        <p>"This plugin is exactly what I needed! Simple, powerful, and completely free. The schema templates saved me hours of work."</p>
                        <cite>— Happy User</cite>
                    </blockquote>
                </div>

                <div class="csg-donate-quick-donate">
                    <h4>⚡ Quick Donate</h4>
                    <p>Show your appreciation with a quick donation:</p>
                    <a href="https://paypal.me/buymecupofcoffee?locale.x=en_GB&country.x=IN" target="_blank" class="csg-donate-button csg-donate-button-secondary">
                        ☕ Donate Now
                    </a>
                </div>

                <div class="csg-donate-social">
                    <h4>🌐 Stay Connected</h4>
                    <p>Follow for updates and new features!</p>
                    <div class="csg-social-links">
                        <a href="https://twitter.com/WPFrank2" target="_blank" class="csg-social-link" title="Twitter">🐦</a>
                        <a href="https://www.facebook.com/wpfrankfaraz/" class="csg-social-link" target="_blank" title="Facebook">📘</a>
                        <a href="https://wpfrank.com/" class="csg-social-link" target="_blank" title="Website">🌐</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Get schema templates.
 *
 * @return array Array of schema templates.
 */
function csg_get_schema_templates() {
    return array(
        'article' => array(
            'name' => 'Article',
            'type' => 'Article',
            'description' => 'For blog posts, news articles, and editorial content.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'Article',
                'headline' => '{{post_title}}',
                'description' => '{{post_excerpt}}',
                'image' => '{{featured_image}}',
                'author' => array(
                    '@type' => 'Person',
                    'name' => '{{author_name}}',
                    'url' => '{{author_url}}',
                ),
                'publisher' => array(
                    '@type' => 'Organization',
                    'name' => '{{site_name}}',
                    'logo' => array(
                        '@type' => 'ImageObject',
                        'url' => '{{site_logo}}',
                    ),
                ),
                'datePublished' => '{{post_date}}',
                'dateModified' => '{{post_modified}}',
                'mainEntityOfPage' => array(
                    '@type' => 'WebPage',
                    '@id' => '{{post_url}}',
                ),
            ),
        ),
        'product' => array(
            'name' => 'Product',
            'type' => 'Product',
            'description' => 'For e-commerce product pages with pricing and availability.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'Product',
                'name' => '{{post_title}}',
                'image' => '{{featured_image}}',
                'description' => '{{post_excerpt}}',
                'brand' => array(
                    '@type' => 'Brand',
                    'name' => 'Brand Name',
                ),
                'offers' => array(
                    '@type' => 'Offer',
                    'url' => '{{post_url}}',
                    'priceCurrency' => 'USD',
                    'price' => '99.99',
                    'availability' => 'https://schema.org/InStock',
                ),
            ),
        ),
        'local_business' => array(
            'name' => 'Local Business',
            'type' => 'LocalBusiness',
            'description' => 'For local businesses with physical location and contact info.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'LocalBusiness',
                'name' => 'Business Name',
                'image' => '{{featured_image}}',
                'address' => array(
                    '@type' => 'PostalAddress',
                    'streetAddress' => '123 Main Street',
                    'addressLocality' => 'City',
                    'addressRegion' => 'State',
                    'postalCode' => '12345',
                    'addressCountry' => 'US',
                ),
                'telephone' => '+1-555-555-5555',
                'url' => '{{post_url}}',
                'openingHours' => 'Mo-Fr 09:00-17:00',
            ),
        ),
        'faq' => array(
            'name' => 'FAQ',
            'type' => 'FAQPage',
            'description' => 'For FAQ pages with questions and answers.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => array(
                    array(
                        '@type' => 'Question',
                        'name' => 'What is your return policy?',
                        'acceptedAnswer' => array(
                            '@type' => 'Answer',
                            'text' => 'We offer a 30-day return policy on all products.',
                        ),
                    ),
                    array(
                        '@type' => 'Question',
                        'name' => 'How long does shipping take?',
                        'acceptedAnswer' => array(
                            '@type' => 'Answer',
                            'text' => 'Standard shipping takes 5-7 business days.',
                        ),
                    ),
                ),
            ),
        ),
        'howto' => array(
            'name' => 'How-To',
            'type' => 'HowTo',
            'description' => 'For step-by-step guides and tutorials.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'HowTo',
                'name' => '{{post_title}}',
                'description' => '{{post_excerpt}}',
                'image' => '{{featured_image}}',
                'totalTime' => 'PT30M',
                'step' => array(
                    array(
                        '@type' => 'HowToStep',
                        'name' => 'Step 1',
                        'text' => 'Description of step 1',
                        'image' => '{{featured_image}}',
                    ),
                    array(
                        '@type' => 'HowToStep',
                        'name' => 'Step 2',
                        'text' => 'Description of step 2',
                        'image' => '{{featured_image}}',
                    ),
                ),
            ),
        ),
        'recipe' => array(
            'name' => 'Recipe',
            'type' => 'Recipe',
            'description' => 'For cooking recipes with ingredients and instructions.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'Recipe',
                'name' => '{{post_title}}',
                'image' => '{{featured_image}}',
                'author' => array(
                    '@type' => 'Person',
                    'name' => '{{author_name}}',
                    'url' => '{{author_url}}',
                ),
                'datePublished' => '{{post_date}}',
                'dateModified' => '{{post_modified}}',
                'description' => '{{post_excerpt}}',
                'prepTime' => 'PT20M',
                'cookTime' => 'PT30M',
                'totalTime' => 'PT50M',
                'recipeYield' => '4 servings',
                'recipeCategory' => '{{post_category_first}}',
                'keywords' => '{{post_tags}}',
                'recipeIngredient' => array(
                    '2 cups flour',
                    '1 cup sugar',
                    '3 eggs',
                ),
                'recipeInstructions' => array(
                    array(
                        '@type' => 'HowToStep',
                        'text' => 'Mix flour and sugar',
                    ),
                    array(
                        '@type' => 'HowToStep',
                        'text' => 'Add eggs and mix well',
                    ),
                ),
            ),
        ),
        'event' => array(
            'name' => 'Event',
            'type' => 'Event',
            'description' => 'For events, conferences, and webinars.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'Event',
                'name' => '{{post_title}}',
                'description' => '{{post_excerpt}}',
                'image' => '{{featured_image}}',
                'startDate' => '2025-12-01T19:00:00',
                'endDate' => '2025-12-01T22:00:00',
                'location' => array(
                    '@type' => 'Place',
                    'name' => 'Event Venue',
                    'address' => array(
                        '@type' => 'PostalAddress',
                        'streetAddress' => '123 Event Street',
                        'addressLocality' => 'City',
                        'addressRegion' => 'State',
                        'postalCode' => '12345',
                        'addressCountry' => 'US',
                    ),
                ),
                'offers' => array(
                    '@type' => 'Offer',
                    'url' => '{{post_url}}',
                    'price' => '50.00',
                    'priceCurrency' => 'USD',
                    'availability' => 'https://schema.org/InStock',
                ),
            ),
        ),
        'video' => array(
            'name' => 'Video',
            'type' => 'VideoObject',
            'description' => 'For video content with duration and thumbnail.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'VideoObject',
                'name' => '{{post_title}}',
                'description' => '{{post_excerpt}}',
                'thumbnailUrl' => '{{featured_image}}',
                'uploadDate' => '{{post_date}}',
                'duration' => 'PT5M30S',
                'contentUrl' => 'YOUR_VIDEO_URL',
                'embedUrl' => 'YOUR_EMBED_URL',
            ),
        ),
        'organization' => array(
            'name' => 'Organization',
            'type' => 'Organization',
            'description' => 'For company/organization information.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'Organization',
                'name' => '{{site_name}}',
                'url' => '{{site_url}}',
                'logo' => '{{site_logo}}',
                'description' => '{{site_description}}',
                'contactPoint' => array(
                    '@type' => 'ContactPoint',
                    'telephone' => '+1-555-555-5555',
                    'contactType' => 'Customer Service',
                ),
                'sameAs' => array(
                    'https://facebook.com/yourpage',
                    'https://twitter.com/yourhandle',
                    'https://linkedin.com/company/yourcompany',
                ),
            ),
        ),
        'breadcrumb' => array(
            'name' => 'Breadcrumb',
            'type' => 'BreadcrumbList',
            'description' => 'For navigation breadcrumbs.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => array(
                    array(
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => 'Home',
                        'item' => '{{site_url}}',
                    ),
                    array(
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => 'Category',
                        'item' => 'YOUR_CATEGORY_URL',
                    ),
                    array(
                        '@type' => 'ListItem',
                        'position' => 3,
                        'name' => '{{post_title}}',
                        'item' => '{{post_url}}',
                    ),
                ),
            ),
        ),
        'review' => array(
            'name' => 'Review',
            'type' => 'Review',
            'description' => 'For product or service reviews with ratings.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'Review',
                'itemReviewed' => array(
                    '@type' => 'Product',
                    'name' => '{{post_title}}',
                    'image' => '{{featured_image}}',
                ),
                'reviewRating' => array(
                    '@type' => 'Rating',
                    'ratingValue' => '4.5',
                    'bestRating' => '5',
                    'worstRating' => '1',
                ),
                'author' => array(
                    '@type' => 'Person',
                    'name' => '{{author_name}}',
                    'url' => '{{author_url}}',
                ),
                'reviewBody' => '{{post_excerpt}}',
                'datePublished' => '{{post_date}}',
                'publisher' => array(
                    '@type' => 'Organization',
                    'name' => '{{site_name}}',
                ),
            ),
        ),
        'course' => array(
            'name' => 'Course',
            'type' => 'Course',
            'description' => 'For online courses and educational content.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'Course',
                'name' => '{{post_title}}',
                'description' => '{{post_excerpt}}',
                'provider' => array(
                    '@type' => 'Organization',
                    'name' => '{{site_name}}',
                    'sameAs' => '{{site_url}}',
                ),
                'offers' => array(
                    '@type' => 'Offer',
                    'category' => 'Paid',
                    'price' => '99.00',
                    'priceCurrency' => 'USD',
                ),
                'hasCourseInstance' => array(
                    '@type' => 'CourseInstance',
                    'courseMode' => 'online',
                    'courseWorkload' => 'PT10H',
                ),
            ),
        ),
        'job_posting' => array(
            'name' => 'Job Posting',
            'type' => 'JobPosting',
            'description' => 'For job listings and career opportunities.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'JobPosting',
                'title' => '{{post_title}}',
                'description' => '{{post_excerpt}}',
                'datePosted' => '{{post_date}}',
                'hiringOrganization' => array(
                    '@type' => 'Organization',
                    'name' => '{{site_name}}',
                    'sameAs' => '{{site_url}}',
                    'logo' => '{{site_logo}}',
                ),
                'jobLocation' => array(
                    '@type' => 'Place',
                    'address' => array(
                        '@type' => 'PostalAddress',
                        'streetAddress' => '123 Main Street',
                        'addressLocality' => 'City',
                        'addressRegion' => 'State',
                        'postalCode' => '12345',
                        'addressCountry' => 'US',
                    ),
                ),
                'employmentType' => 'FULL_TIME',
                'baseSalary' => array(
                    '@type' => 'MonetaryAmount',
                    'currency' => 'USD',
                    'value' => array(
                        '@type' => 'QuantitativeValue',
                        'value' => 50000,
                        'unitText' => 'YEAR',
                    ),
                ),
            ),
        ),
        'book' => array(
            'name' => 'Book',
            'type' => 'Book',
            'description' => 'For books, ebooks, and publications.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'Book',
                'name' => '{{post_title}}',
                'author' => array(
                    '@type' => 'Person',
                    'name' => '{{author_name}}',
                ),
                'image' => '{{featured_image}}',
                'description' => '{{post_excerpt}}',
                'isbn' => '978-3-16-148410-0',
                'numberOfPages' => 250,
                'publisher' => array(
                    '@type' => 'Organization',
                    'name' => 'Publisher Name',
                ),
                'datePublished' => '{{post_date}}',
                'bookFormat' => 'https://schema.org/Paperback',
            ),
        ),
        'software' => array(
            'name' => 'Software Application',
            'type' => 'SoftwareApplication',
            'description' => 'For software, apps, and digital tools.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'SoftwareApplication',
                'name' => '{{post_title}}',
                'description' => '{{post_excerpt}}',
                'image' => '{{featured_image}}',
                'operatingSystem' => 'Windows, macOS, Linux',
                'applicationCategory' => 'BusinessApplication',
                'offers' => array(
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'USD',
                ),
                'aggregateRating' => array(
                    '@type' => 'AggregateRating',
                    'ratingValue' => '4.5',
                    'ratingCount' => '100',
                ),
            ),
        ),
        'restaurant' => array(
            'name' => 'Restaurant',
            'type' => 'Restaurant',
            'description' => 'For restaurants, cafes, and dining establishments.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'Restaurant',
                'name' => '{{post_title}}',
                'image' => '{{featured_image}}',
                'description' => '{{post_excerpt}}',
                'address' => array(
                    '@type' => 'PostalAddress',
                    'streetAddress' => '123 Restaurant Street',
                    'addressLocality' => 'City',
                    'addressRegion' => 'State',
                    'postalCode' => '12345',
                    'addressCountry' => 'US',
                ),
                'telephone' => '+1-555-555-5555',
                'servesCuisine' => 'Italian',
                'priceRange' => '$$',
                'openingHours' => 'Mo-Su 11:00-22:00',
                'acceptsReservations' => 'True',
            ),
        ),
        'music' => array(
            'name' => 'Music Recording',
            'type' => 'MusicRecording',
            'description' => 'For songs, albums, and music content.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'MusicRecording',
                'name' => '{{post_title}}',
                'description' => '{{post_excerpt}}',
                'image' => '{{featured_image}}',
                'byArtist' => array(
                    '@type' => 'MusicGroup',
                    'name' => 'Artist Name',
                ),
                'inAlbum' => array(
                    '@type' => 'MusicAlbum',
                    'name' => 'Album Name',
                ),
                'duration' => 'PT3M30S',
                'datePublished' => '{{post_date}}',
            ),
        ),
        'podcast' => array(
            'name' => 'Podcast',
            'type' => 'PodcastEpisode',
            'description' => 'For podcast episodes and audio content.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'PodcastEpisode',
                'name' => '{{post_title}}',
                'description' => '{{post_excerpt}}',
                'url' => '{{post_url}}',
                'datePublished' => '{{post_date}}',
                'partOfSeries' => array(
                    '@type' => 'PodcastSeries',
                    'name' => 'YOUR_PODCAST_SERIES_NAME',
                    'url' => 'YOUR_PODCAST_URL',
                ),
                'associatedMedia' => array(
                    '@type' => 'MediaObject',
                    'contentUrl' => 'YOUR_EPISODE_MP3_URL',
                    'duration' => 'PT45M',
                ),
            ),
        ),
        'news_article' => array(
            'name' => 'News Article',
            'type' => 'NewsArticle',
            'description' => 'For news articles and press releases.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'NewsArticle',
                'headline' => '{{post_title}}',
                'description' => '{{post_excerpt}}',
                'image' => '{{featured_image}}',
                'datePublished' => '{{post_date}}',
                'dateModified' => '{{post_date}}',
                'author' => array(
                    '@type' => 'Person',
                    'name' => '{{author_name}}',
                ),
                'publisher' => array(
                    '@type' => 'Organization',
                    'name' => '{{site_name}}',
                    'logo' => array(
                        '@type' => 'ImageObject',
                        'url' => '{{site_logo}}',
                    ),
                ),
                'articleSection' => 'News',
            ),
        ),
        'medical' => array(
            'name' => 'Medical Condition',
            'type' => 'MedicalCondition',
            'description' => 'For medical conditions and health information.',
            'schema' => array(
                '@context' => 'https://schema.org',
                '@type' => 'MedicalCondition',
                'name' => '{{post_title}}',
                'description' => '{{post_excerpt}}',
                'possibleTreatment' => array(
                    '@type' => 'MedicalTherapy',
                    'name' => 'Treatment Name',
                ),
                'riskFactor' => array(
                    '@type' => 'MedicalRiskFactor',
                    'name' => 'Risk Factor',
                ),
            ),
        ),
    );
}

/**
 * Check if meta box should be shown for current post.
 *
 * @param int $post_id The post ID.
 * @param string $post_type The post type.
 * @return bool Whether to show the meta box.
 */
function csg_should_show_meta_box( $post_id, $post_type ) {
    // Get enabled post types from settings
    $enabled_post_types = get_option( 'csg_enabled_post_types', array() );

    // If post type is not enabled, don't show
    if ( ! isset( $enabled_post_types[ $post_type ] ) || $enabled_post_types[ $post_type ] !== '1' ) {
        return false;
    }

    // Check individual item settings
    if ( $post_type === 'page' ) {
        $enabled_pages = get_option( 'csg_enabled_pages', array() );
        // If no pages are specifically enabled, show for all pages
        if ( empty( $enabled_pages ) ) {
            return true;
        }
        // Check if this specific page is enabled
        return isset( $enabled_pages[ $post_id ] ) && $enabled_pages[ $post_id ] === '1';
    } elseif ( $post_type === 'post' ) {
        $enabled_posts = get_option( 'csg_enabled_posts', array() );
        // If no posts are specifically enabled, show for all posts
        if ( empty( $enabled_posts ) ) {
            return true;
        }
        // Check if this specific post is enabled
        return isset( $enabled_posts[ $post_id ] ) && $enabled_posts[ $post_id ] === '1';
    } else {
        // Custom post type
        $enabled_cpt_items = get_option( 'csg_enabled_cpt_items', array() );
        // If no CPT items are specifically enabled, show for all CPT items
        if ( empty( $enabled_cpt_items ) ) {
            return true;
        }
        // Check if this specific CPT item is enabled
        return isset( $enabled_cpt_items[ $post_id ] ) && $enabled_cpt_items[ $post_id ] === '1';
    }
}

/**
 * Replace placeholders in schema with actual post data.
 *
 * @param string $schema The schema string with placeholders.
 * @param int    $post_id The post ID.
 * @return string The schema with replaced placeholders.
 */
function csg_replace_schema_placeholders( $schema, $post_id ) {
    $post = get_post( $post_id );
    if ( ! $post ) {
        return $schema;
    }

    // Get post data
    $post_title = get_the_title( $post_id );
    $post_excerpt = has_excerpt( $post_id ) ? get_the_excerpt( $post_id ) : wp_trim_words( $post->post_content, 55, '...' );
    $post_date = get_the_date( 'c', $post_id ); // ISO 8601 format
    $post_modified = get_the_modified_date( 'c', $post_id );
    $post_url = get_permalink( $post_id );

    // Get author data
    $author_id = $post->post_author;
    $author_name = get_the_author_meta( 'display_name', $author_id );
    $author_url = get_author_posts_url( $author_id );

    // Get featured image
    $featured_image = '';
    if ( has_post_thumbnail( $post_id ) ) {
        $featured_image = get_the_post_thumbnail_url( $post_id, 'full' );
    }

    // Get site data
    $site_name = get_bloginfo( 'name' );
    $site_url = get_bloginfo( 'url' );
    $site_description = get_bloginfo( 'description' );

    // Get site logo
    $site_logo = '';
    $custom_logo_id = get_theme_mod( 'custom_logo' );
    if ( $custom_logo_id ) {
        $site_logo = wp_get_attachment_image_url( $custom_logo_id, 'full' );
    }

    // Get categories (for posts)
    $categories = array();
    $category_names = array();
    $post_categories = get_the_category( $post_id );
    if ( ! empty( $post_categories ) ) {
        foreach ( $post_categories as $cat ) {
            $categories[] = $cat->name;
            $category_names[] = $cat->name;
        }
    }
    $post_category = ! empty( $category_names ) ? implode( ', ', $category_names ) : '';
    $post_category_first = ! empty( $category_names ) ? $category_names[0] : '';

    // Get tags
    $tags = array();
    $post_tags = get_the_tags( $post_id );
    if ( ! empty( $post_tags ) ) {
        foreach ( $post_tags as $tag ) {
            $tags[] = $tag->name;
        }
    }
    $post_tags_string = ! empty( $tags ) ? implode( ', ', $tags ) : '';

    // Create replacement array
    $replacements = array(
        '{{post_title}}'         => esc_html( $post_title ),
        '{{post_excerpt}}'       => esc_html( $post_excerpt ),
        '{{post_date}}'          => $post_date,
        '{{post_modified}}'      => $post_modified,
        '{{post_url}}'           => esc_url( $post_url ),
        '{{author_name}}'        => esc_html( $author_name ),
        '{{author_url}}'         => esc_url( $author_url ),
        '{{featured_image}}'     => esc_url( $featured_image ),
        '{{site_name}}'          => esc_html( $site_name ),
        '{{site_url}}'           => esc_url( $site_url ),
        '{{site_description}}'   => esc_html( $site_description ),
        '{{site_logo}}'          => esc_url( $site_logo ),
        '{{post_category}}'      => esc_html( $post_category ),
        '{{post_category_first}}' => esc_html( $post_category_first ),
        '{{post_tags}}'          => esc_html( $post_tags_string ),
        '{{post_id}}'            => $post_id,
    );

    // Replace placeholders
    $schema = str_replace( array_keys( $replacements ), array_values( $replacements ), $schema );

    return $schema;
}

/**
 * Adds the meta box to the editor screen.
 */
function csg_add_custom_meta_box( $post_type, $post ) {
    // Get enabled post types from settings
    $enabled_post_types = get_option( 'csg_enabled_post_types', array() );

    // If no settings saved yet, show on all public post types by default
    if ( empty( $enabled_post_types ) ) {
        $post_types = get_post_types( array( 'public' => true ) );
        $show_for_all = true;
    } else {
        // Only get enabled post types
        $post_types = array();
        foreach ( $enabled_post_types as $pt => $enabled ) {
            if ( $enabled === '1' ) {
                $post_types[] = $pt;
            }
        }
        $show_for_all = false;
    }

    // Check if current post type is in the enabled list
    if ( ! in_array( $post_type, $post_types, true ) ) {
        return;
    }

    // Check meta box type (individual or dynamic)
    $meta_box_types = get_option( 'csg_meta_box_type', array() );
    $current_type = isset( $meta_box_types[ $post_type ] ) ? $meta_box_types[ $post_type ] : 'individual';

    // If dynamic mode, don't show meta box (schema will be applied automatically)
    if ( $current_type === 'dynamic' ) {
        return;
    }

    // Check if meta box should be shown for this specific post
    if ( ! $show_for_all && ! csg_should_show_meta_box( $post->ID, $post_type ) ) {
        return;
    }

    add_meta_box(
        'csg_meta_box', // Unique ID
        __( 'Custom Schema (JSON-LD)', 'custom-schema-box-generator' ), // Box title
        'csg_meta_box_html', // Content callback, must be of type callable
        $post_type, // Post type
        'advanced', // Context
        'default' // Priority
    );
}
add_action( 'add_meta_boxes', 'csg_add_custom_meta_box', 10, 2 );

/**
 * Renders the HTML for the meta box.
 *
 * @param WP_Post $post The post object.
 */
function csg_meta_box_html( $post ) {
    $value = get_post_meta( $post->ID, '_csg_schema_data', true );
    
    // Add a nonce field for security
    wp_nonce_field( 'csg_save_meta_box_data', 'csg_meta_box_nonce' );
    ?>
    <label for="csg_schema_field" style="font-weight: bold; margin-bottom: 5px; display: block;">Schema JSON-LD Script:</label>
    <textarea name="csg_schema_field" id="csg_schema_field" rows="10" style="width:100%;"><?php echo esc_textarea( $value ); ?></textarea>
    <p class="description">
        Enter your valid JSON-LD structured data here. It will be wrapped in <code>&lt;script type="application/ld+json"&gt;</code> tags automatically.
    </p>
    <?php
}

/**
 * Saves the custom meta box data when a post is saved.
 *
 * @param int $post_id The post ID.
 */
function csg_save_meta_box_data( $post_id ) {
    // Check if our nonce is set.
    if ( ! isset( $_POST['csg_meta_box_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    $nonce = isset( $_POST['csg_meta_box_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['csg_meta_box_nonce'] ) ) : '';
    if ( ! wp_verify_nonce( $nonce, 'csg_save_meta_box_data' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }
    } else {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    // Make sure the field is set.
    if ( ! isset( $_POST['csg_schema_field'] ) ) {
        return;
    }

    // Unslash and sanitize user input.
    // Use wp_kses_post to allow valid JSON characters while stripping harmful scripts
    $schema_data = wp_kses_post( wp_unslash( $_POST['csg_schema_field'] ) );

    // Validate that it's valid JSON before saving
    $decoded = json_decode( $schema_data );
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        // If invalid JSON, don't save it
        return;
    }

    // Update the meta field in the database.
    update_post_meta( $post_id, '_csg_schema_data', $schema_data );
}
add_action( 'save_post', 'csg_save_meta_box_data' );

/**
 * Injects the schema script into the website's head on singular pages.
 */
function csg_inject_schema_in_head() {
    // Only output on single posts, pages, or custom post types
    if ( is_singular() ) {
        $post_id = get_the_ID();
        $post_type = get_post_type( $post_id );

        // Check if schema should be shown for this post
        $enabled_post_types = get_option( 'csg_enabled_post_types', array() );

        // If no settings saved yet, show on all (backward compatibility)
        $show_schema = empty( $enabled_post_types );

        // If post type is enabled, check individual item settings
        if ( ! $show_schema && isset( $enabled_post_types[ $post_type ] ) && $enabled_post_types[ $post_type ] === '1' ) {
            $show_schema = csg_should_show_meta_box( $post_id, $post_type );
        }

        // If schema should not be shown, return early
        if ( ! $show_schema ) {
            return;
        }

        // Check meta box type (individual or dynamic)
        $meta_box_types = get_option( 'csg_meta_box_type', array() );
        $current_type = isset( $meta_box_types[ $post_type ] ) ? $meta_box_types[ $post_type ] : 'individual';

        $schema_data = '';

        if ( $current_type === 'dynamic' ) {
            // Get dynamic schema for this post type
            $dynamic_schemas = get_option( 'csg_dynamic_schema', array() );
            if ( isset( $dynamic_schemas[ $post_type ] ) && ! empty( $dynamic_schemas[ $post_type ] ) ) {
                $schema_data = $dynamic_schemas[ $post_type ];
                // Replace placeholders with actual post data
                $schema_data = csg_replace_schema_placeholders( $schema_data, $post_id );
            }
        } else {
            // Get individual schema from post meta
            $schema_data = get_post_meta( $post_id, '_csg_schema_data', true );
            // Replace placeholders in individual schema too
            if ( ! empty( $schema_data ) ) {
                $schema_data = csg_replace_schema_placeholders( $schema_data, $post_id );
            }
        }

        // If we have data, output it in a script tag
        if ( ! empty( $schema_data ) ) {
            // Validate that it's valid JSON before outputting
            $decoded = json_decode( $schema_data );
            if ( json_last_error() === JSON_ERROR_NONE ) {
                // Re-encode to ensure it's properly escaped
                echo '<script type="application/ld+json">' . wp_json_encode( $decoded ) . '</script>' . "\n";
            }
        }
    }
}
add_action( 'wp_head', 'csg_inject_schema_in_head' );