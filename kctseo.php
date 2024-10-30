<?php
 /*
Plugin Name: Kctseo: Backup full
Description: Easily search, export, and import options from the WordPress wp_options table using specific keywords
Author: KCT
Version: 1.0.1
Author URI: https://aiautotool.com
License: GPL2
*/
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}


if ( ! defined( 'WPINC' ) )
{
    die;
}

define( 'KCTSEO_VERSION', '1.0.1' );

define( 'KCTSEO_SLUG', 'kctseo' );
define( 'KCTSEO_NAME', plugin_basename( __FILE__ ) );
define( 'KCTSEO_URL', plugins_url( '', __FILE__ ) );
define( 'KCTSEO_DIR', dirname( __FILE__ ) );
function custom_upload_mimes($mimes) {
    $mimes['json'] = 'application/json';
    return $mimes;
}
add_filter('upload_mimes', 'custom_upload_mimes');

class Kctseo_tool_vkct {

    public function __construct() {
        add_action( 'admin_menu', [ $this, 'kctseo_add_admin_menu' ] );
        add_action( 'admin_head', [ $this, 'kctseo_enqueue_scripts' ] );
        add_action( 'wp_ajax_kctseo_export_options', [ $this, 'kctseo_export_options' ] );
        add_action( 'wp_ajax_kctseo_import_options', [ $this, 'kctseo_import_options' ] );
        add_action( 'wp_ajax_kctseo_zip_plugin', [ $this, 'kctseo_zip_plugin' ] );
        add_filter( 'admin_body_class', [ $this, 'kctseo_add_body_class' ] );
        add_filter( 'wp_ajax_zip_theme', [ $this, 'kctseo_zip_theme' ] );

    }

    public function kctseo_add_body_class( $classes ) {
        
        $screen = get_current_screen();
        if ( isset($_GET['page']) && ( $_GET['page'] == 'custom-option-manager' || $_GET['page'] == 'com-plugin-exporter' ) ) {
            $classes .= ' aikct-export';
        }
        return $classes;
    }
 
    public function kctseo_add_admin_menu() {
        add_menu_page(
            ' VKCT Tool Expport Option',
            ' <i class="wp-menu-image dashicons-before dashicons-download"></i> VKCT Tool ',
            'manage_options',
            KCTSEO_SLUG,
            [ $this, 'option_manager_page' ],
            '',
            6
        );

        add_submenu_page(
            KCTSEO_SLUG,
            ' Plugin Zip',
            '<i class="wp-menu-image dashicons-before dashicons-download"></i> Plugin Zip ',
            'manage_options',
            'com-plugin-exporter',
            [ $this, 'plugin_exporter_page' ]
        );
        add_submenu_page(
           KCTSEO_SLUG, 
            __('Theme Exporter', 'kctseo'), 
            __('<i class="wp-menu-image dashicons-before dashicons-download"></i> Theme Exporter', 'kctseo'), 
            'manage_options', 
            'vkct-theme-exporter', 

            [ $this, 'vkct_theme_exporter_page' ]
        );
    }
    function vkct_theme_exporter_page() {
    
        $themes = wp_get_themes();
        ?>
        <div class="wrap kctseo-fl-wrap">
            <form id="export-theme-form" class="kctseo-fl-settings-form" method="post">
            <div class="kctseo-fl-grid-container kctseo-fl-grid-x kctseo-fl-grid-margin-x">
               
                <div class="kctseo-fl-cell kctseo-fl-small-12">
                    <div class="kctseo-fl-white-box">
                         <h3 class="kctseo-fl-dm-sans kctseo-fl-medium-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-globe kctseo-fl-feather"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg><?php esc_html_e('Theme Exporter', 'kctseo'); ?></h3>
                       <p class="kctseo-fl-dm-sans kctseo-fl-section-p"><?php esc_html_e('VKCT Theme Export', 'kctseo'); ?>.</p>

                       <table class="form-table" role="presentation"><tbody>
                        <tr>
                            <th scope="row"> <label class="kctseo-fl-required-label" for="theme-select"><?php esc_html_e('Select Theme to Zip:', 'kctseo'); ?></label>
                            <small><?php esc_html_e('Select a theme and press the button to zip the theme.', 'kctseo'); ?></small></th>
                        <td>   
                        <div class="kctseo-fl-form-field">
     
                <select id="theme-select" class="kctseo-fl-language-selector kctseo-fl-post-creator_post-type" name="theme">
                                <?php foreach ( $themes as $theme_slug => $theme_info ) : ?>
                                    <option value="<?php echo esc_attr( $theme_slug ); ?>">
                                        <?php echo esc_html( $theme_info->get('Name') ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
        <p class="kctseo-fl-form-field-desc"><em>Select the language you want to create your posts in.</em></p>
    </div>
</td></tr></tbody></table>

<p class="submit"><button type="button" class="kctseo-fl-button kctseo-fl-dm-sans kctseo-fl-transitions-2s" id="zip-theme-btn">
                                <i class="wp-menu-image dashicons-before dashicons-download"></i><?php esc_html_e('Create ZIP and Download', 'kctseo'); ?>
                            </button></p>

                        
                           
                         
                        

                        <div id="zip-theme-status" style="margin-top: 10px;"></div>
                    </div>
                </div>
            </div>
            </form>
        </div>
        
        <?php
    }

  
    public function option_manager_page() {
        ?>
        <div class="wrap kctseo-fl-wrap">
    <h1><?php esc_html_e( 'Custom Option Manager', 'kctseo' ); ?></h1>
    <form id="option-form" class="kctseo-fl-settings-form" method="post">
        <div class="kctseo-fl-grid-container kctseo-fl-grid-x kctseo-fl-grid-margin-x">
            
            <div class="kctseo-fl-cell kctseo-fl-small-12">
                <div class="kctseo-fl-white-box">
                    <h3 class="kctseo-fl-dm-sans kctseo-fl-medium-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-globe kctseo-fl-feather">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg><?php esc_html_e('Option Manager', 'kctseo'); ?>
                    </h3>
                    <p class="kctseo-fl-dm-sans kctseo-fl-section-p"><?php esc_html_e( 'Manage and operate options in WP_Options.', 'kctseo' ); ?></p>

                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label class="kctseo-fl-required-label" for="search_keyword"><?php esc_html_e( 'Enter search keyword:', 'kctseo' ); ?></label>
                                    <small><?php esc_html_e( 'Enter keyword to search in options.', 'kctseo' ); ?></small>
                                </th>
                                <td>
                                    <div class="kctseo-fl-form-field">
                                        <input type="text" id="search_keyword" class="kctseo-fl-post-creator_post-type" name="search_keyword" placeholder="<?php esc_attr_e( 'Enter search keyword', 'kctseo' ); ?>">
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="submit">
                        <button type="button" class="kctseo-fl-button kctseo-fl-dm-sans kctseo-fl-transitions-2s" id="export_btn">
                            <i class="wp-menu-image dashicons-before dashicons-upload"></i><?php esc_html_e( 'Export', 'kctseo' ); ?>
                        </button>
                        <button type="button" class="kctseo-fl-button kctseo-fl-dm-sans kctseo-fl-transitions-2s" id="import_btn">
                            <i class="wp-menu-image dashicons-before dashicons-download"></i><?php esc_html_e( 'Import', 'kctseo' ); ?>
                        </button>
                        <input type="file" id="import_file" style="display:none">
                    </p>

                    <div id="loading" class="kctseo-fl-dm-sans" style="display:none; margin-top:10px;">
                        <?php esc_html_e( 'Processing...', 'kctseo' ); ?>
                    </div>
                    <div id="response" class="kctseo-fl-dm-sans" style="margin-top: 20px;"></div>
                </div>
            </div>

            <div class="kctseo-fl-cell kctseo-fl-small-12">
                <!-- Optional: Add additional notices or content here -->
            </div>
        </div>
    </form>
</div>


        <?php
    }

    
    public function plugin_exporter_page() {
        $plugins = get_plugins();
        ?>
        <div class="wrap kctseo-fl-wrap">
    <div class="kctseo-fl-grid-container kctseo-fl-grid-x kctseo-fl-grid-margin-x">
        

        <div class="kctseo-fl-cell kctseo-fl-small-12">
            <div class="kctseo-fl-white-box">
                <h3 class="kctseo-fl-dm-sans kctseo-fl-medium-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-globe kctseo-fl-feather">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="2" y1="12" x2="22" y2="12"></line>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg><?php esc_html_e('Zip Plugins', 'kctseo'); ?>
                    </h3>
                    <p class="kctseo-fl-dm-sans kctseo-fl-section-p"><?php esc_html_e( 'Manage and operate options in WP_Options.', 'kctseo' ); ?></p>
                <form id="export-plugin-form" class="kctseo-fl-settings-form" method="post">
                    <table class="form-table" role="presentation">
                        <tbody>
                            <tr>
                                <th scope="row">
                                    <label class="kctseo-fl-required-label" for="plugin-select"><?php esc_html_e( 'Select Plugin to Zip:', 'kctseo' ); ?></label>
                                    <small><?php esc_html_e( 'Select a plugin and press the button to zip the plugin.', 'kctseo' ); ?></small>
                                </th>
                                <td>
                                    <div class="kctseo-fl-form-field">
                                        <select id="plugin-select" class="kctseo-fl-post-creator_post-type" name="plugin">
                                            <?php foreach ( $plugins as $plugin_path => $plugin_info ) : ?>
                                                <option value="<?php echo esc_attr( $plugin_path ); ?>">
                                                    <?php echo esc_html( $plugin_info['Name'] ); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <p class="submit">
                        <button type="button" class="kctseo-fl-button kctseo-fl-dm-sans kctseo-fl-transitions-2s" id="zip-plugin-btn">
                            <i class="wp-menu-image dashicons-before dashicons-download"></i><?php esc_html_e( 'Create ZIP and Download', 'kctseo' ); ?>
                        </button>
                    </p>

                    <div id="zip-status" class="kctseo-fl-dm-sans" style="margin-top: 10px;"></div>
                </form>
            </div>
        </div>

        <div class="kctseo-fl-remember kctseo-fl-cell kctseo-fl-small-12">
            
        </div>
    </div>
</div>


         <script>
        
    </script>
        <?php
    }

  
    public function kctseo_enqueue_scripts() {
       wp_enqueue_script( 'com-ajax-script', plugin_dir_url( __FILE__ ) . 'js/kcttool.js', array('jquery'), KCTSEO_VERSION, true );
        wp_localize_script( 'com-ajax-script', 'kctseo_js', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'com_nonce' )
        ));

         wp_enqueue_style( 'kctseo', plugin_dir_url( __FILE__ ) . 'css/kcttool.css', array(), KCTSEO_VERSION, 'all' );
    }

    
    public function kctseo_export_options() {
        check_ajax_referer( 'com_nonce', 'nonce' );

        
        if ( isset( $_POST['keyword'] ) ) {
            $keyword = sanitize_text_field( wp_unslash( $_POST['keyword'] ) );
       
            global $wpdb;

            $options = $wpdb->get_results( $wpdb->prepare(
                "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s", '%' . $wpdb->esc_like( $keyword ) . '%'
            ), ARRAY_A );

            if ( ! empty( $options ) ) {
                header('Content-Type: application/json');
                header('Content-Disposition: attachment; filename="options_export.json"');
                wp_send_json_success( $options );
                wp_die();
            } else {
                wp_send_json_error( 'Not found option with key.' );
            }
        } else {
            wp_send_json_error( 'Keyword parameter is missing.' );
        }
    }


    
    public function kctseo_import_options() {
    check_ajax_referer('com_nonce', 'nonce');

    if (isset($_FILES['file'])) {
        $uploadedfile = $_FILES['file'];
        $uploadedfile['name'] = sanitize_file_name($uploadedfile['name']);
        $uploadedfile['tmp_name'] = sanitize_file_name($uploadedfile['tmp_name']);
        $uploadedfile['error'] = intval($uploadedfile['error']);
        $uploadedfile['size'] = intval($uploadedfile['size']);
        if ($uploadedfile['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error('File upload error.');
            return;
        }

        $upload_overrides = [
            'test_form' => false,
            'mimes'     => ['json' => 'application/json']
        ];

        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            $file_path = $movefile['file'];
            $file_data = file_get_contents($file_path);
            if ($file_data === false) {
                wp_send_json_error('Error reading the file.');
                return;
            }

            $options = json_decode($file_data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                foreach ($options as $option) {
                    $option = array_map('sanitize_text_field', (array) $option);
                    update_option($option['option_name'], maybe_unserialize($option['option_value']));
                }
                wp_send_json_success($options);
            } else {
                wp_send_json_error('Error parsing JSON data.');
            }
        } else {
            wp_send_json_error(isset($movefile['error']) ? $movefile['error'] : 'File upload error.');
        }
    } else {
        wp_send_json_error('No file provided for import.');
    }
}




    
    public function kctseo_zip_plugin() {
        check_ajax_referer( 'com_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'kctseo' ) ) );
        }

        if ( isset( $_POST['plugin'] ) ) {
                $plugin = sanitize_text_field( wp_unslash($_POST['plugin'] ));
                $plugin_dir = WP_PLUGIN_DIR . '/' . dirname( $plugin );
                $plugin_slug = basename( $plugin_dir );
                $upload_dir = wp_upload_dir();
                $zip_path = $upload_dir['basedir'] . '/' . $plugin_slug . '.zip';

                if ( ! is_dir( $plugin_dir ) ) {
                    wp_send_json_error( array( 'message' => __( 'Plugin does not exist.', 'kctseo' ) ) );
                }

                if (file_exists($zip_path)) {
                    wp_delete_file($zip_path); 
                }

                $zip = new ZipArchive();
                if ( $zip->open( $zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE ) === TRUE ) {
                    $files = new RecursiveIteratorIterator(
                        new RecursiveDirectoryIterator( $plugin_dir ),
                        RecursiveIteratorIterator::LEAVES_ONLY
                    );

                    foreach ( $files as $name => $file ) {
                        if ( ! $file->isDir() ) {
                            $file_path = $file->getRealPath();
                            $relative_path = substr( $file_path, strlen( $plugin_dir ) + 1 );
                            $zip->addFile( $file_path, $relative_path );
                        }
                    }

                    $zip->close();

                    $zip_url = $upload_dir['baseurl'] . '/' . $plugin_slug . '.zip';
                    wp_send_json_success( array( 'url' => $zip_url ) );
                } else {
                    wp_send_json_error( array( 'message' => __( 'Unable to create ZIP file.', 'kctseo' ) ) );
                }
            } else {
                wp_send_json_error( array( 'message' => __( 'Plugin not found.', 'kctseo' ) ) );
            }

    }


    public function kctseo_zip_theme() {
        check_ajax_referer('com_nonce', 'nonce');

        if ( ! current_user_can('manage_options') ) {
            wp_send_json_error(array('message' => __('Permission denied', 'kctseo')));
        }

        if ( ! isset($_POST['theme']) || empty($_POST['theme']) ) {
            wp_send_json_error(array('message' => __('Theme parameter is missing or empty', 'kctseo')));
        }

        $theme_slug = sanitize_text_field(wp_unslash($_POST['theme']));
        $theme = wp_get_theme($theme_slug);

        if ( ! $theme->exists() ) {
            wp_send_json_error(array('message' => __('Theme not found', 'kctseo')));
        }

        $theme_dir = get_theme_root() . '/' . $theme_slug;
        $zip_file = WP_CONTENT_DIR . '/uploads/' . $theme_slug . '.zip';
        if (file_exists($zip_file)) {
                    wp_delete_file($zip_file); 
                }
        $zip = new ZipArchive();
        if ( $zip->open( $zip_file, ZipArchive::CREATE ) === TRUE ) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($theme_dir),
                RecursiveIteratorIterator::LEAVES_ONLY
            );
            foreach ($files as $name => $file) {
                if (!$file->isDir()) {
                    $file_path = $file->getRealPath();
                    $relative_path = substr($file_path, strlen($theme_dir) + 1);
                    $zip->addFile($file_path, $relative_path);
                }
            }
            $zip->close();
            $zip_url = content_url('uploads/' . $theme_slug . '.zip');
            wp_send_json_success(array('message' => __('Theme successfully zipped', 'kctseo'), 'zip_url' => $zip_url));
        } else {
            wp_send_json_error(array('message' => __('Failed to create ZIP file', 'kctseo')));
        }
    }


}

new Kctseo_tool_vkct();
