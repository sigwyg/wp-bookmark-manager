<?php
/*
Plugin Name: WP Bookmark Manager
Description: You can manage "link-manager.php". Duplicate link, Export to CSV file, and import from CSV format for each links.
Version: 1.0.0
Author: Yasuo Fukuda
Author URI: http://archiva.jp/
Text Domain: wp-bookmark-manager
Domain Path: /languages
License: GPLv2 or later
 */

define( 'WBM_VERSION', '1.0.0' );

/**
 * Initialize
 */
function init_wp_bookmark_manager() {
    $wp_bookmark_manager = new WP_Bookmark_Manager();
}
add_action( 'plugins_loaded', 'init_wp_bookmark_manager' );

class WP_Bookmark_Manager {
    public function __construct() {
        // 他言語化
        load_plugin_textdomain( 'wp-bookmark-manager', false, basename( dirname( __FILE__ ) ) . '/languages/' );

        // 管理メニューに追加するフック
        add_action( 'admin_menu', array( &$this, 'admin_menu', ) );

        // css, js
        add_action( 'admin_print_styles', array( &$this, 'add_admin_enqueue_style', ) );
    }

    /**
     * プラグインのメインページ
     */
    public function show_options_clone() {
        require_once dirname(__FILE__).'/admin-clone/index.php';
    }
    public function show_options_export() {
        require_once dirname(__FILE__).'/admin-export/index.php';
    }
    public function show_options_import() {
        require_once dirname(__FILE__).'/admin-import/index.php';
    }

    /**
     * メニューを表示
     */
    public function admin_menu() {
        add_submenu_page(
            'link-manager.php',
            __( 'Clone Link', 'wp-bookmark-manager' ),
            __( 'Clone Link', 'wp-bookmark-manager' ),
            'level_7',
            'wp-bookmark-manager-clone',
            array( &$this, 'show_options_clone', )
        );
        add_submenu_page(
            'link-manager.php',
            __( 'Bookmark Import', 'wp-bookmark-manager' ),
            __( 'Bookmark Import', 'wp-bookmark-manager' ),
            'level_7',
            'wp-bookmark-manager-import',
            array( &$this, 'show_options_import', )
        );
        add_submenu_page(
            'link-manager.php',
            __( 'Bookmark Export', 'wp-bookmark-manager' ),
            __( 'Bookmark Export', 'wp-bookmark-manager' ),
            'level_7',
            'wp-bookmark-manager-export',
            array( &$this, 'show_options_export', )
        );
    }

    /**
     * 管理画面JS/CSS追加
     */
    public function add_admin_enqueue_style() {
        if (  isset($_REQUEST["page"]) && 0 === strncmp($_REQUEST["page"], 'wp-bookmark-manager-', 20) ) {
            wp_enqueue_style( "wbm_css", plugin_dir_url( __FILE__ ).'assets/style.css' );
        }
    }
}
