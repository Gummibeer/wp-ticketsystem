<?php
/*
Plugin Name: Wordpress Ticketsystem
Description: Ticketsystem für Bug-Tracking und Anforderungsverwaltung.
Version: 1.0
Author: Tom Witkowski
*/

class wp_ticketsystem {
    public $plugin_dir;
    public $table_name;
    protected $table_create;

    public function __construct() {
        global $wpdb;
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        $this->plugin_dir = dirname(__FILE__).'/';
        $this->table_name = $wpdb->prefix . 'ticketsystem_tickets';

        if(is_admin()) {
            $this->table_create =	"CREATE TABLE ".$this->table_name." (
										id INT NOT NULL AUTO_INCREMENT,
										parent INT DEFAULT 0 NOT NULL,
										title TEXT DEFAULT '' COLLATE utf8_general_ci NOT NULL,
										content TEXT DEFAULT '' COLLATE utf8_general_ci NOT NULL,
										tickettype INT DEFAULT 0 NOT NULL,
										ticketstatus INT DEFAULT 0 NOT NULL,
										creationdate TIMESTAMP DEFAULT 0 NOT NULL,
										changedate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
										email VARCHAR(150) DEFAULT '' COLLATE utf8_general_ci NOT NULL,
										name VARCHAR(150) DEFAULT '' COLLATE utf8_general_ci NOT NULL,
										userid INT DEFAULT 0 NOT NULL,
										PRIMARY KEY (id)
									)
                                    COLLATE utf8_general_ci;";

            register_activation_hook( __FILE__, array( $this, 'initial_install' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );

            $this->load_dashboard();
            $this->load_options_page();
        }
        add_action( 'admin_bar_menu', array( $this, 'add_toolbar_link_options_page' ), 999 );
        $this->load_shortcode();
        $this->load_filter();
    }

    /**
     * Plugin installieren
     */
    public function initial_install() {
        dbDelta( $this->table_create );
    }

    /**
	 * Admin-Backend Scripts/Styles laden
	 */
    public function load_scripts() {
        wp_register_script('google-chart-api', 'https://www.google.com/jsapi');
        wp_enqueue_script('google-chart-api');
    }

    public function add_toolbar_link_options_page( $wp_admin_bar ) {
        $args = array(
            'id'    => 'wp_ticketsystem',
            'title' => 'Ticketsystem',
            'href'  => '?page=wp_ticketsystem_options_page',
            'parent' => false
        );
        $wp_admin_bar->add_node( $args );

        $args = array(
            'id'    => 'wp_ticketsystem_options_page_bug',
            'title' => 'Fehler',
            'href'  => '?page=wp_ticketsystem_options_page&tab=bug',
            'parent' => 'wp_ticketsystem'
        );
        $wp_admin_bar->add_node( $args );

        $args = array(
            'id'    => 'wp_ticketsystem_options_page_task',
            'title' => 'Aufgaben',
            'href'  => '?page=wp_ticketsystem_options_page&tab=task',
            'parent' => 'wp_ticketsystem'
        );
        $wp_admin_bar->add_node( $args );

        $args = array(
            'id'    => 'wp_ticketsystem_options_page_feature',
            'title' => 'Funktionen',
            'href'  => '?page=wp_ticketsystem_options_page&tab=feature',
            'parent' => 'wp_ticketsystem'
        );
        $wp_admin_bar->add_node( $args );
    }

    /**
     * load Dashboard-Class
     */
    public function load_dashboard() {
        require_once( $this->plugin_dir.'lib/dashboard_widgets.php' );
        add_action( 'wp_dashboard_setup', array( 'wp_ticketsystem_dashboard_widgets', 'ticketsystem_bug_add_dashboard_widget' ) );
        add_action( 'wp_dashboard_setup', array( 'wp_ticketsystem_dashboard_widgets', 'ticketsystem_task_add_dashboard_widget' ) );
        add_action( 'wp_dashboard_setup', array( 'wp_ticketsystem_dashboard_widgets', 'ticketsystem_feature_add_dashboard_widget' ) );
        add_action( 'wp_dashboard_setup', array( 'wp_ticketsystem_dashboard_widgets', 'ticketsystem_chart_add_dashboard_widget' ) );
    }

    /**
     * load Options-Page-Class
     */
    public function load_options_page() {
        require_once( $this->plugin_dir.'lib/options_page.php' );
        add_action( 'admin_init', array( 'wp_ticketsystem_options_page', 'ticketsystem_register_settings' ) );
        add_action('admin_menu', array( 'wp_ticketsystem_options_page', 'ticketsystem_add_options_page' ) );
    }

    /**
     * load Shortcodes-Class
     */
    public function load_shortcode() {
        require_once( $this->plugin_dir.'lib/form_shortcode.php' );
        add_shortcode( 'ticket_form', array( 'wp_ticketsystem_form_shortcode', 'ticket_form_func' ) );
        require_once( $this->plugin_dir.'lib/show_shortcode.php' );
        add_shortcode( 'ticket_show', array( 'wp_ticketsystem_show_shortcode', 'show_tickets_func' ) );
        require_once( $this->plugin_dir.'lib/single_shortcode.php' );
        add_shortcode( 'ticket_single', array( 'wp_ticketsystem_single_shortcode', 'single_ticket_func' ) );
    }

    /**
     * load Content-Filter
     */
    public function load_filter() {
        require_once( $this->plugin_dir.'lib/content_filter.php' );
        add_filter( 'the_content', array( 'wp_ticketsystem_filter_content', 'filter_content_func' ) );
        add_filter( 'bp_get_the_topic_post_content', array( 'wp_ticketsystem_filter_content', 'filter_content_func' ) );
    }
}

/**
 * Plugin initialisieren
 */
global $wp_ticketsystem;
$wp_ticketsystem = new wp_ticketsystem;