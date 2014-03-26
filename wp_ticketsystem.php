<?php
/**
 * Plugin Name: Wordpress Ticketsystem
 * Description: Ticketsystem für Bug-Tracking und Anforderungsverwaltung.
 * Version: 0.5
 * Author: Tom Witkowski <tomwitkowski@ymail.com>
 * Author URI: http://gummibeer.github.io/wp-ticketsystem
 * License: GPL2
 */

/**
 * Copyright 2014  Tom Witkowski  (email : tomwitkowski@ymail.com)
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class wp_ticketsystem {
    private $wp_basepath;
    private $plugin_file;
    private $plugin_dir;
    private $plugin_url;
    private $plugin_name;
    private $plugin_slug;
    private $plugin_version;
    private $table_name;
    private $table_create;
    private $predef_tickettypes;
    private $predef_ticketstatus;

    public $admin_notice_out;
    public $admin_notice_class;

    public function __construct() {
        /* var-Setup - start */
        global $wpdb;

        $this->plugin_name = 'Ticketsystem';
        $this->plugin_slug = 'wp_ticketsystem';
        $this->plugin_version = '0.5.0';

        $this->wp_basepath = ABSPATH;
        $this->plugin_file = __FILE__;
        $this->plugin_dir = dirname($this->plugin_file).'/';
        $this->plugin_url = plugins_url().'/'.$this->plugin_slug.'/';

        $this->table_name = $wpdb->prefix . $this->plugin_slug . '_tickets';
        $this->table_create =	"CREATE TABLE ".$this->table_name." (
                                    id INT NOT NULL AUTO_INCREMENT,
                                    parent INT DEFAULT 0 NOT NULL,
                                    title TEXT DEFAULT '' COLLATE utf8_general_ci NOT NULL,
                                    content TEXT DEFAULT '' COLLATE utf8_general_ci NOT NULL,
                                    type INT DEFAULT 0 NOT NULL,
                                    status INT DEFAULT 0 NOT NULL,
                                    creationdate TIMESTAMP DEFAULT 0 NOT NULL,
                                    changedate TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                    email VARCHAR(150) DEFAULT '' COLLATE utf8_general_ci NOT NULL,
                                    name VARCHAR(150) DEFAULT '' COLLATE utf8_general_ci NOT NULL,
                                    userid INT DEFAULT 0 NOT NULL,
                                    PRIMARY KEY (id)
                                )
                                COLLATE utf8_general_ci;";

        $this->predef_tickettypes = array(
                                        0 =>    array(
                                                    'name' => 'Fehler',
                                                    'slug' => 'bug',
                                                    'icon' => 'icon-bug',
                                                    'color' => '#ff0000',
                                                    'active' => true,
                                                    'description' => 'Probleme welche die erwarte Funktionalität behindern.',
                                                ),
                                        1 =>	array(
                                                    'name' => 'Aufgabe',
                                                    'slug' => 'task',
                                                    'icon' => 'icon-pet-paw',
                                                    'color' => '#ffff00',
                                                    'active' => true,
                                                    'description' => 'Dinge die getan werden sollten aber weder den Spielablauf behindern noch wirklich neue Funktionen benötigen.',
                                                ),
                                        2 =>	array(
                                                    'name' => 'Funktion',
                                                    'slug' => 'feature',
                                                    'icon' => 'icon-star',
                                                    'color' => '#0000ff',
                                                    'active' => true,
                                                    'description' => 'Ideen welche neue Möglichkeiten für den Spieler schaffen.',
                                                ),
                                        3 =>	array(
                                                    'name' => 'Sicherheit',
                                                    'slug' => 'security',
                                                    'icon' => 'icon-securityalt-shieldalt',
                                                    'color' => '#cccccc',
                                                    'active' => true,
                                                    'description' => 'Probleme mit existenten Sicherheitsvorkehrungen oder Ideen um das System sicherer zu machen.',
                                                ),
                                    );
        $this->predef_ticketstatus =    array(
                                            0 => 'offen',           /* offen */
                                            1 => 'gesichtet',       /* offen */
                                            2 => 'eingeplant',      /* offen */
                                            3 => 'in Bearbeitung',  /* in Bearbeitung */
                                            4 => 'wird geprüft',    /* in Bearbeitung */
                                            5 => 'abgeschlossen'    /* geschlossen */
                                        );
        /* var-Setup - end */

        require_once( $this->wp_basepath . 'wp-admin/includes/upgrade.php' );

        register_activation_hook( __FILE__, array( $this, 'install_plugin' ) );
        add_action( 'plugins_loaded', array( $this, 'update_plugin' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_scripts' ) );
        add_action( 'admin_bar_menu', array( $this, 'add_toolbar_link' ), 999 );

        $this->load_dashboard_widgets();
        $this->load_options_page();
    } /* __construct() */

    public function install_plugin() {
        dbDelta( $this->table_create );
        update_option( $this->plugin_slug.'_version', $this->plugin_version );
        update_option( $this->plugin_slug.'_types', $this->serialize_data( $this->predef_tickettypes ) );
    }

    public function update_plugin() {
        if ( get_option($this->plugin_slug.'_version') != $this->plugin_version ) {
            $this->install_plugin();
        }
    }

    public function load_admin_scripts() {
        wp_register_script( 'google-chart-api', 'https://www.google.com/jsapi' );
        wp_enqueue_script( 'google-chart-api' );

        wp_register_style( $this->plugin_slug, $this->plugin_url.'css/'.$this->plugin_slug.'.css' );
        wp_enqueue_style( $this->plugin_slug );
    }



    /**
     * Wordpress-Extensions
     */
    /* Toolbar-Link */
    public function add_toolbar_link( $wp_admin_bar ) {
        $args = array(
            'id'    => $this->plugin_slug.'_admin_page',
            'title' => $this->plugin_name,
            'href'  => '?page='.$this->plugin_slug.'_admin_page',
            'parent' => false
        );
        $wp_admin_bar->add_node( $args );

        $args = array(
            'id'    => $this->plugin_slug.'_admin_page_settings',
            'title' => 'Einstellungen',
            'href'  => '?page='.$this->plugin_slug.'_admin_page&tab=settings',
            'parent' => $this->plugin_slug.'_admin_page'
        );
        $wp_admin_bar->add_node( $args );

        $args = array(
            'id'    => $this->plugin_slug.'_admin_page_open',
            'title' => 'offene Tickets',
            'href'  => '?page='.$this->plugin_slug.'_admin_page&tab=open',
            'parent' => $this->plugin_slug.'_admin_page'
        );
        $wp_admin_bar->add_node( $args );

        $args = array(
            'id'    => $this->plugin_slug.'_admin_page_work',
            'title' => 'Tickets in Bearbeitung',
            'href'  => '?page='.$this->plugin_slug.'_admin_page&tab=work',
            'parent' => $this->plugin_slug.'_admin_page'
        );
        $wp_admin_bar->add_node( $args );

        $args = array(
            'id'    => $this->plugin_slug.'_admin_page_closed',
            'title' => 'geschlossene Tickets',
            'href'  => '?page='.$this->plugin_slug.'_admin_page&tab=closed',
            'parent' => $this->plugin_slug.'_admin_page'
        );
        $wp_admin_bar->add_node( $args );
    }

    /* Dashboard-Widget */
    public function load_dashboard_widgets() {
        add_action( 'wp_dashboard_setup', array( $this, 'add_chart_dashboard_widget' ) );
    }

    public function add_chart_dashboard_widget() {
        wp_add_dashboard_widget( $this->plugin_slug.'chart_dashboard_widget', 'Übersicht offene Tickets', array( $this, 'display_chart_dashboard_widget' ) );
    }
    public function display_chart_dashboard_widget() {
        global $wpdb;

        $out = '';

        $sql = strval( 'SELECT type FROM '.$this->table_name.' WHERE status < 5 AND parent = 0' );
        $results = $wpdb->get_col( $sql );
        $types = $this->unserialize_data( get_option($this->plugin_slug.'_types') );
        $counter = array();
        foreach($results as $type) {
            $counter[$type] = $counter[$type] + 1;
        }

        $out .= '<div id="'.$this->plugin_slug.'_chart"></div>';
        $out .= "<script type=\"text/javascript\">
                    jQuery(window).on('load', function() {
                        google.load('visualization', '1.0', {'packages':['corechart'], 'callback':drawChart});
                        function drawChart() {
                            var data = new google.visualization.DataTable();
                                data.addColumn('string', 'Typ');
                                data.addColumn('number', 'Anzahl');
                                data.addRows([";
        foreach($counter as $id => $count) {
            $out .= "['".$types[$id]['name']."', ".$count."],";
        }
        $out .=                 "]);

                            var options =   {
                                                'title':'Ticket Verhältnisübersicht',
                                                'width':380,
                                                'height':260,";
        $out .= "'colors': [";
        foreach($counter as $id => $count) {
            $out .= "'".$types[$id]['color']."', ";
        }
        $out .= "]";
        $out .=                            "};
                            var chart = new google.visualization.PieChart(document.getElementById('".$this->plugin_slug."_chart'));
                            chart.draw(data, options);
                        }
                    });
                </script>";

        echo $out;
    }

    /* Options-Page */
    public function load_options_page() {
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_menu', array( $this, 'add_options_page' ) );
    }

    public function register_settings() {
        register_setting( $this->plugin_slug.'_settings_group', $this->plugin_slug.'_single_page' );
        register_setting( $this->plugin_slug.'_settings_group', $this->plugin_slug.'_show_name' );
        register_setting( $this->plugin_slug.'_settings_group', $this->plugin_slug.'_show_email' );
        register_setting( $this->plugin_slug.'_settings_group', $this->plugin_slug.'_show_comments' );
        register_setting( $this->plugin_slug.'_settings_group', $this->plugin_slug.'_tickets_per_page' );
    }

    public function add_options_page() {
        add_menu_page( 'Ticketsystem', 'Ticketsystem', 'administrator', $this->plugin_slug.'_options_page', array( $this, 'display_options_page' ) );
    }

    public function display_options_page() {
        global $wpdb;

        $tab = $_GET['tab'] ? $_GET['tab'] : 'settings';
        $tid = $_GET['tid'] ? esc_sql( esc_attr( $_GET['tid'] ) ) * 1 : false;
?>

<div id="<?php echo $this->plugin_slug.'_wrap'; ?>" class="wrap">
    <h2>Ticketsystem</h2>

    <h2 class="nav-tab-wrapper">
        <a class="nav-tab<?php echo $tab == 'settings' ? ' nav-tab-active' : ''; ?>" href="?page=<?php echo $this->plugin_slug.'_options_page'; ?>&tab=settings">Einstellungen</a>
        <a class="nav-tab<?php echo $tab == 'open' ? ' nav-tab-active' : ''; ?>" href="?page=<?php echo $this->plugin_slug.'_options_page'; ?>&tab=open">offene Tickets</a>
        <a class="nav-tab<?php echo $tab == 'work' ? ' nav-tab-active' : ''; ?>" href="?page=<?php echo $this->plugin_slug.'_options_page'; ?>&tab=work">Tickets in Bearbeitung</a>
        <a class="nav-tab<?php echo $tab == 'closed' ? ' nav-tab-active' : ''; ?>" href="?page=<?php echo $this->plugin_slug.'_options_page'; ?>&tab=closed">geschlossene Tickets</a>
        <span class="nav-tab <?php echo $tab == 'edit' ? ' nav-tab-active' : ''; ?>">Ticket bearbeiten</span>
    </h2>

    <?php if( $tab == 'settings' ) : ?>
    <div class="postbox-container" style="width:100%;">
        <div class="metabox-holder">
            <div class="meta-box-sortables ui-sortable">
                <div class="postbox">
                    <h3 class="hndle">
                        <span>Einstellungen</span>
                    </h3>
                    <div class="inside">
                        <?php
                        if($_GET['settings-updated']) {
                            echo $this->return_admin_notice( '<strong>Erfolg:</strong> Änderungen erfolgreich übernommen.', 'updated' );
                        }
                        ?>

                        <form action="options.php" method="post" name="options">
                            <?php
                            settings_fields( $this->plugin_slug.'_settings_group' );
                            do_settings_sections( $this->plugin_slug.'_settings_group' );
                            ?>
                            <fieldset>
                                <div class="row">
                                    <div class="col col-3">
                                        <label for="<?php echo $this->plugin_slug.'_single_page'; ?>">Seite für Ticketeinzelansicht</label>
                                    </div>
                                    <div class="col col-9">
                                        <select name="<?php echo $this->plugin_slug.'_single_page'; ?>">
                                            <?php
                                            $pages = get_pages();
                                            foreach ( $pages as $page ) {
                                                $selected = $page->ID == get_option($this->plugin_slug.'_single_page') ? 'selected' : '';
                                                $option = '<option value="'.$page->ID.'" '.$selected.'>';
                                                $option .= $page->post_title;
                                                $option .= '</option>';
                                                echo $option;
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col col-3">
                                        <label for="<?php echo $this->plugin_slug.'_show_name'; ?>">veröffentliche Name in Tickets</label>
                                    </div>
                                    <div class="col col-3">
                                        <input type="checkbox" name="<?php echo $this->plugin_slug.'_show_name'; ?>" id="<?php echo $this->plugin_slug.'_show_name'; ?>" <?php echo get_option($this->plugin_slug.'_show_name') == 'on' ? 'checked' : '' ?> />
                                    </div>

                                    <div class="col col-3">
                                        <label for="<?php echo $this->plugin_slug.'_show_email'; ?>">veröffentliche E-Mail in Tickets</label>
                                    </div>
                                    <div class="col col-3">
                                        <input type="checkbox" name="<?php echo $this->plugin_slug.'_show_email'; ?>" id="<?php echo $this->plugin_slug.'_show_email'; ?>" <?php echo get_option($this->plugin_slug.'_show_email') == 'on' ? 'checked' : '' ?> />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col col-3">
                                        <label for="<?php echo $this->plugin_slug.'_show_comments'; ?>">zeige Ticketkommentare</label>
                                    </div>
                                    <div class="col col-3">
                                        <input type="checkbox" name="<?php echo $this->plugin_slug.'_show_comments'; ?>" id="<?php echo $this->plugin_slug.'_show_comments'; ?>" <?php echo get_option($this->plugin_slug.'_show_comments') == 'on' ? 'checked' : '' ?> />
                                    </div>
                                    <div class="col col-3">
                                        <label for="<?php echo $this->plugin_slug.'_tickets_per_page'; ?>">zeige {x} Tickets</label>
                                    </div>
                                    <div class="col col-3">
                                        <input type="number" name="<?php echo $this->plugin_slug.'_tickets_per_page'; ?>" id="<?php echo $this->plugin_slug.'_tickets_per_page'; ?>" placeholder="25" value="<?php echo get_option($this->plugin_slug.'_tickets_per_page'); ?>" />
                                    </div>
                                </div>
                            </fieldset>
                            <p class="submit">
                                <input id="submit" class="button button-primary" type="submit" value="Einstellungen speichern" name="submit" />
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="postbox-container" style="width:100%;">
        <div class="metabox-holder">
            <div class="meta-box-sortables ui-sortable">
                <div class="postbox">
                    <h3 class="hndle">
                        <span>Tickettypen</span>
                    </h3>

                    <div class="inside">
                    <?php
                    if( !empty($_POST[$this->plugin_slug]) ) {
                        $post = $_POST[$this->plugin_slug];
                        $post['id'] = $post['id']-1;
                        $existingtypes = $this->unserialize_data( get_option($this->plugin_slug.'_types') );

                        $data = array();
                        $post_error = false;

                        if( !empty($post['name']) ) {
                            $data['name'] = $this->cleanup_data( $post['name'], 'name' );
                            $data['slug'] = $this->cleanup_data( str_replace( array( 'ä', 'ö', 'ü', 'ß', '-' ), array( 'ae', 'oe', 'ue', 'ss', '_' ), $data['name'] ), 'slug' );
                        } else {
                            $post_error = true;
                        }

                        if( !empty($post['color']) ) {
                            $data['color'] = $this->cleanup_data( $post['color'], 'hexcode' );
                        } else {
                            $post_error = true;
                        }

                        if( !empty($post['description']) ) {
                            $data['description'] = $this->cleanup_data( $post['description'], 'text' );
                        } else {
                            $post_error = true;
                        }

                        if(!$post_error) {
                            $data['active'] = $post['active'] == 'on' ? true : false;
                            $data['icon'] = $this->cleanup_data( $post['icon'], 'cssclass' );

                            if($post['id'] > -1) {
                                $existingtypes[$post['id']] = $data;
                            } else {
                                array_push( $existingtypes, $data );
                            }

                            update_option( $this->plugin_slug.'_types', $this->serialize_data( $existingtypes ) );

                            echo $this->return_admin_notice( '<strong>Erfolg:</strong> Änderungen erfolgreich übernommen.', 'updated' );
                        } else {
                            echo $this->return_admin_notice( '<strong>Fehler:</strong> Leider waren deine Eingaben unvollständig - bitte alle benötigten Felder ausfüllen.', 'error' );
                        }
                    }

                    if(is_int($tid)) {
                        $tid = $tid-1;
                        $edittype = $this->unserialize_data( get_option($this->plugin_slug.'_types') )[$tid];
                    } elseif($tid === false) {
                        $tid = -1;
                    }
                    ?>

                        <form action="" method="post" name="options">
                            <fieldset>
                                <div class="row">
                                    <div class="col col-3">
                                        <label for="name">Tickettyp Name *</label>
                                    </div>
                                    <div class="col col-3">
                                        <input type="text" placeholder="Lorem" name="<?php echo $this->plugin_slug; ?>[name]" id="name" value="<?php echo $edittype['name']; ?>" />
                                    </div>
                                    <div class="col col-3">
                                        <label for="active">Tickettyp aktiv?</label>
                                    </div>
                                    <div class="col col-3">
                                        <input type="checkbox" name="<?php echo $this->plugin_slug; ?>[active]" id="active" <?php echo $edittype['active'] ? 'checked' : ''; ?> />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col col-3">
                                        <label for="color">Tickettyp Farbe (hex-Farbcode) *</label>
                                    </div>
                                    <div class="col col-3">
                                        <input type="text" placeholder="#ff0000" name="<?php echo $this->plugin_slug; ?>[color]" id="color" value="<?php echo $edittype['color']; ?>" onchange="change_color_picker()" style="border-right: 15px solid <?php echo $edittype['color']; ?>" />
                                    </div>
                                    <script type="text/javascript">
                                        function change_color_picker() {
                                            var field = document.getElementById('color');
                                            var value = field.value;
                                            var color = value.match(/(#[0-9abcdef]{6})/i);
                                            if(color != null) {
                                                color = color[1];
                                            } else {
                                                color = 'transparent';
                                            }
                                            field.style.borderRight = '15px solid ' + color;
                                        }
                                    </script>

                                    <div class="col col-3">
                                        <label for="icon">Tickettyp Icon (css-Klasse)</label>
                                    </div>
                                    <div class="col col-3">
                                        <input type="text" placeholder="icon-bug" name="<?php echo $this->plugin_slug; ?>[icon]" id="icon" value="<?php echo $edittype['icon']; ?>" />
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col col-3">
                                        <label for="description">Tickettyp Beschreibung *</label>
                                    </div>
                                    <div class="col col-9">
                                        <textarea name="<?php echo $this->plugin_slug; ?>[description]" id="description" placeholder="Lorem Ipsum dolor sit amet."><?php echo $edittype['description']; ?></textarea>
                                    </div>
                                </div>

                                <input type="hidden" name="<?php echo $this->plugin_slug; ?>[id]" value="<?php echo $tid+1; ?>" />
                            </fieldset>

                            <div class="col col-md-12">
                                <strong>Tickettypen bearbeiten:</strong>&nbsp;
                                <?php
                                $types = $this->unserialize_data( get_option($this->plugin_slug.'_types') );
                                $typesout = '';
                                $seperator = ' | ';
                                foreach( $types as $id => $type ) {
                                    $link = '?page='.$this->plugin_slug.'_options_page&tab=settings&tid='.($id+1);
                                    $class = $type['active'] ? 'active' : 'inactive';
                                    $typesout .= '<a href="'.$link.'" title="'.$type['name'].' bearbeiten" class="'.$class.'">#'.$id.' - '.$type['name'].'</a>';
                                    $typesout .= $seperator;
                                }
                                $typesout = trim($typesout, $seperator);
                                echo $typesout;
                                ?>
                            </div>
                            <p class="submit">
                                <input id="submit" class="button button-primary" type="submit" value="Tickettypen speichern" name="submit" />
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><!-- Einstellungen -->

    <!-- Tickets mit Status 0, 1, 2 -->
    <?php if( $tab == 'open' ) : ?>
    <div class="postbox-container" style="width:100%;">
        <div class="metabox-holder">
            <div class="meta-box-sortables ui-sortable">
                <div class="postbox">
                    <h3 class="hndle">
                        <span>offene Tickets</span>
                    </h3>
                    <div class="inside">

                        <table class="wp-list-table widefat tickets" cellspacing="0">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th colspan="2">Typ</th>
                                <th>Status</th>
                                <th>Titel &amp; Beschreibung</th>
                                <th>Nutzer</th>
                                <th>erstellt</th>
                                <th><span class="comment-grey-bubble"></span></th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>ID</th>
                                <th colspan="2">Typ</th>
                                <th>Status</th>
                                <th>Titel &amp; Beschreibung</th>
                                <th>Nutzer</th>
                                <th>erstellt</th>
                                <th><span class="comment-grey-bubble"></span></th>
                            </tr>
                            </tfoot>

                            <tbody>
                            <?php
                            $sql = strval( 'SELECT id FROM '.$this->table_name.' WHERE status < 3 AND parent = 0 ORDER BY creationdate ASC' );
                            $results = $wpdb->get_col( $sql );
                            foreach($results as $id) {
                                $sql = strval( 'SELECT * FROM '.$this->table_name.' WHERE id = "'.$id.'"' );
                                $result = $wpdb->get_row( $sql );

                                $sql = strval( 'SELECT id FROM '.$this->table_name.' WHERE parent = '.$id );
                                $parents = $wpdb->get_col( $sql );
                                $parents = count($parents);

                                $user_temp = '';
                                if($result->userid > 0) {
                                    $user_info = get_userdata($result->userid);
                                    $user_temp .= '<br />';
                                    $user_temp .= $user_info->user_login;
                                }

                                $types = $this->unserialize_data( get_option($this->plugin_slug.'_types') );
                            ?>
                                <tr>
                                    <td>#<?php echo $result->id; ?></td>
                                    <td><span style="display:block;border-radius:50%;height:12px;width:12px;background:<?php echo $types[$result->type]['color']; ?>;"></span></td>
                                    <td><?php echo $types[$result->type]['name']; ?></td>
                                    <td><?php echo $this->predef_ticketstatus[$result->status]; ?></td>
                                    <td><a href="?page=<?php echo $this->plugin_slug.'_options_page'; ?>&tab=edit&tid=<?php echo $result->id; ?>" title="Ticket bearbeiten"><strong><?php echo $result->title; ?></strong></a>
                                        <br />
                                        <?php echo $this->shorten_string( $result->content, 150 ); ?></td>
                                    <td>
                                        <a href="mailto:<?php echo $result->email; ?>" title="<?php echo $result->email; ?>"><?php echo $result->name; ?></a>
                                        <?php echo $user_temp; ?>
                                    </td>
                                    <td style="max-width:75px;"><?php echo date('d.m.Y H:i:s', strtotime($result->creationdate)); ?></td>
                                    <td><a class="post-com-count" title="0" href="#"><span class="comment-count"><?php echo $parents; ?></span></a></td>
                                </tr>
                            <?php
                            }
                            ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><!-- offene Tickets -->

    <!-- Tickets mit Status 3, 4 -->
    <?php if( $tab == 'work' ) : ?>
    <div class="postbox-container" style="width:100%;">
        <div class="metabox-holder">
            <div class="meta-box-sortables ui-sortable">
                <div class="postbox">
                    <h3 class="hndle">
                        <span>Tickets in Bearbeitung</span>
                    </h3>
                    <div class="inside">

                        <table class="wp-list-table widefat tickets" cellspacing="0">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th colspan="2">Typ</th>
                                <th>Status</th>
                                <th>Titel &amp; Beschreibung</th>
                                <th>Nutzer</th>
                                <th>erstellt</th>
                                <th><span class="comment-grey-bubble"></span></th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>ID</th>
                                <th colspan="2">Typ</th>
                                <th>Status</th>
                                <th>Titel &amp; Beschreibung</th>
                                <th>Nutzer</th>
                                <th>erstellt</th>
                                <th><span class="comment-grey-bubble"></span></th>
                            </tr>
                            </tfoot>

                            <tbody>
                            <?php
                            $sql = strval( 'SELECT id FROM '.$this->table_name.' WHERE (status = 3 OR status = 4) AND parent = 0 ORDER BY creationdate ASC' );
                            $results = $wpdb->get_col( $sql );
                            foreach($results as $id) {
                                $sql = strval( 'SELECT * FROM '.$this->table_name.' WHERE id = "'.$id.'"' );
                                $result = $wpdb->get_row( $sql );

                                $sql = strval( 'SELECT id FROM '.$this->table_name.' WHERE parent = '.$id );
                                $parents = $wpdb->get_col( $sql );
                                $parents = count($parents);

                                $user_temp = '';
                                if($result->userid > 0) {
                                    $user_info = get_userdata($result->userid);
                                    $user_temp .= '<br />';
                                    $user_temp .= $user_info->user_login;
                                }

                                $types = $this->unserialize_data( get_option($this->plugin_slug.'_types') );
                                ?>
                                <tr>
                                    <td>#<?php echo $result->id; ?></td>
                                    <td><span style="display:block;border-radius:50%;height:12px;width:12px;background:<?php echo $types[$result->type]['color']; ?>;"></span></td>
                                    <td><?php echo $types[$result->type]['name']; ?></td>
                                    <td><?php echo $this->predef_ticketstatus[$result->status]; ?></td>
                                    <td><a href="?page=<?php echo $this->plugin_slug.'_options_page'; ?>&tab=edit&tid=<?php echo $result->id; ?>" title="Ticket bearbeiten"><strong><?php echo $result->title; ?></strong></a>
                                        <br />
                                        <?php echo $this->shorten_string( $result->content, 150 ); ?></td>
                                    <td>
                                        <a href="mailto:<?php echo $result->email; ?>" title="<?php echo $result->email; ?>"><?php echo $result->name; ?></a>
                                        <?php echo $user_temp; ?>
                                    </td>
                                    <td style="max-width:75px;"><?php echo date('d.m.Y H:i:s', strtotime($result->creationdate)); ?></td>
                                    <td><a class="post-com-count" title="0" href="#"><span class="comment-count"><?php echo $parents; ?></span></a></td>
                                </tr>
                            <?php
                            }
                            ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><!-- Tickets in Bearbeitung -->

    <!-- Tickets mit Status 5 -->
    <?php if( $tab == 'closed' ) : ?>
    <div class="postbox-container" style="width:100%;">
        <div class="metabox-holder">
            <div class="meta-box-sortables ui-sortable">
                <div class="postbox">
                    <h3 class="hndle">
                        <span>geschlossene Tickets</span>
                    </h3>
                    <div class="inside">

                        <table class="wp-list-table widefat tickets" cellspacing="0">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th colspan="2">Typ</th>
                                <th>Status</th>
                                <th>Titel &amp; Beschreibung</th>
                                <th>Nutzer</th>
                                <th>erstellt</th>
                                <th><span class="comment-grey-bubble"></span></th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th>ID</th>
                                <th colspan="2">Typ</th>
                                <th>Status</th>
                                <th>Titel &amp; Beschreibung</th>
                                <th>Nutzer</th>
                                <th>erstellt</th>
                                <th><span class="comment-grey-bubble"></span></th>
                            </tr>
                            </tfoot>

                            <tbody>
                            <?php
                            $sql = strval( 'SELECT id FROM '.$this->table_name.' WHERE status = 5 AND parent = 0 ORDER BY creationdate ASC' );
                            $results = $wpdb->get_col( $sql );
                            foreach($results as $id) {
                                $sql = strval( 'SELECT * FROM '.$this->table_name.' WHERE id = "'.$id.'"' );
                                $result = $wpdb->get_row( $sql );

                                $sql = strval( 'SELECT id FROM '.$this->table_name.' WHERE parent = '.$id );
                                $parents = $wpdb->get_col( $sql );
                                $parents = count($parents);

                                $user_temp = '';
                                if($result->userid > 0) {
                                    $user_info = get_userdata($result->userid);
                                    $user_temp .= '<br />';
                                    $user_temp .= $user_info->user_login;
                                }

                                $types = $this->unserialize_data( get_option($this->plugin_slug.'_types') );
                                ?>
                                <tr>
                                    <td>#<?php echo $result->id; ?></td>
                                    <td><span style="display:block;border-radius:50%;height:12px;width:12px;background:<?php echo $types[$result->type]['color']; ?>;"></span></td>
                                    <td><?php echo $types[$result->type]['name']; ?></td>
                                    <td><?php echo $this->predef_ticketstatus[$result->status]; ?></td>
                                    <td><a href="?page=<?php echo $this->plugin_slug.'_options_page'; ?>&tab=edit&tid=<?php echo $result->id; ?>" title="Ticket bearbeiten"><strong><?php echo $result->title; ?></strong></a>
                                        <br />
                                        <?php echo $this->shorten_string( $result->content, 150 ); ?></td>
                                    <td>
                                        <a href="mailto:<?php echo $result->email; ?>" title="<?php echo $result->email; ?>"><?php echo $result->name; ?></a>
                                        <?php echo $user_temp; ?>
                                    </td>
                                    <td style="max-width:75px;"><?php echo date('d.m.Y H:i:s', strtotime($result->creationdate)); ?></td>
                                    <td><a class="post-com-count" title="0" href="#"><span class="comment-count"><?php echo $parents; ?></span></a></td>
                                </tr>
                            <?php
                            }
                            ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?><!-- geschlossene Tickets -->

    <!-- einzelnes Ticket bearbeiten -->
    <?php if( $tab == 'edit' ) : ?>

    <?php
    if( !empty($_POST[$this->plugin_slug]) ) {
        $post = $_POST[$this->plugin_slug];

        $data = array();
        $post_error = false;

        if( isset($post['id']) ) {
            $data['id'] = $this->cleanup_data( $post['id'], 'int' );
        } else {
            $post_error = true;
        }

        if( !empty($post['title']) ) {
            $data['title'] = $this->cleanup_data( $post['title'], 'text' );
        } else {
            $post_error = true;
        }

        if( !empty($post['content']) ) {
            $data['content'] = $this->cleanup_data( $post['content'], 'text' );
        } else {
            $post_error = true;
        }

        if( isset($post['type']) ) {
            $data['type'] = $this->cleanup_data( $post['type'], 'int' );
        } else {
            $post_error = true;
        }

        if( isset($post['status']) ) {
            $data['status'] = $this->cleanup_data( $post['status'], 'int' );
        } else {
            $post_error = true;
        }

        if(!$post_error) {
            $success = $wpdb->update( $this->table_name, array( 'title' => $data['title'], 'content' => $data['content'], 'type' => $data['type'], 'status' => $data['status'] ), array( 'id' => $data['id'] ), array( '%s', '%s', '%d', '%d' ), array( '%d' ) );

            if($success !== false) {
                echo $this->return_admin_notice( '<strong>Erfolg:</strong> Änderungen erfolgreich übernommen.', 'updated' );
            } else {
                echo $this->return_admin_notice( '<strong>Fehler:</strong> Leider ist ein Fehler aufgetreten.', 'error' );
            }
        } else {
            echo $this->return_admin_notice( '<strong>Fehler:</strong> Leider waren deine Eingaben unvollständig - bitte alle benötigten Felder ausfüllen.', 'error' );
        }
    }
    ?>

    <?php
    $sql = strval( 'SELECT * FROM '.$this->table_name.' WHERE id = "'.$tid.'"' );
    $result = $wpdb->get_row( $sql );
    $types = $this->unserialize_data( get_option($this->plugin_slug.'_types') );

    $sql = strval( 'SELECT id FROM '.$this->table_name.' WHERE parent = '.$tid );
    $comments = $wpdb->get_col( $sql );
    $comments_count = count($parents);

    $user_info = get_userdata($result->userid);
    ?>

    <form action="" method="post">
        <div style="width:65%;margin-right:5%;float:left;">
            <div class="postbox-container" style="width:100%;">
                <div class="metabox-holder">
                    <div class="meta-box-sortables ui-sortable">
                        <div class="postbox">
                            <h3 class="hndle">
                                <span>Ticket bearbeiten</span>
                            </h3>
                            <div class="inside">

                                <div class="row">
                                    <div class="col-12">
                                        <label for="<?php echo $this->plugin_slug; ?>[title]">Titel</label>
                                        <input type="text" name="<?php echo $this->plugin_slug; ?>[title]" id="<?php echo $this->plugin_slug; ?>[title]" value="<?php echo $result->title; ?>" />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-12">
                                        <label for="<?php echo $this->plugin_slug; ?>[content]">Beschreibung</label>
                                        <textarea type="text" name="<?php echo $this->plugin_slug; ?>[content]" id="<?php echo $this->plugin_slug; ?>[content]" rows="10"><?php echo $result->content; ?></textarea>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="postbox-container" style="width:100%;">
                <div class="metabox-holder">
                    <div class="meta-box-sortables ui-sortable">
                        <div class="postbox">
                            <h3 class="hndle">
                                <span>Kommentare</span>
                            </h3>
                            <div class="inside">

                                <ul>
                                    <?php
                                    $comments_out = '';
                                    foreach($comments as $id) {
                                        $sql = strval( 'SELECT * FROM '.$this->table_name.' WHERE id = "'.$id.'"' );
                                        $comment = $wpdb->get_row( $sql );

                                        $comments_out .= '<li>';
                                        $comments_out .= '#'.$id.' - '.$comment->name.' ('.date( 'd.m.Y', strtotime($comment->creationdate) ).')';
                                        $comments_out .= ' | <a href="?page='.$this->plugin_slug.'_options_page&tab=edit&tid='.$result->id.'" title="Kommentar bearbeiten">bearbeiten</a>';
                                        $comments_out .= '<br />';
                                        $comments_out .= $this->shorten_string( $comment->content, 100 );
                                        $comments_out .= '</li>';
                                    }
                                    echo $comments_out;
                                    ?>
                                </ul>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div style="width:30%;float:left;">
            <div class="postbox-container" style="width:100%;">
                <div class="metabox-holder">
                    <div class="meta-box-sortables ui-sortable">
                        <div class="postbox">
                            <h3 class="hndle">
                                <span>Ticketinfos</span>
                            </h3>
                            <div class="inside">

                                <p>
                                    <strong>TicketID:</strong> #<?php echo $result->id; ?>
                                    <input type="hidden" name="<?php echo $this->plugin_slug; ?>[id]" value="<?php echo $result->id; ?>" />
                                </p>
                                <p>
                                    <strong>Tickettyp:</strong>
                                    <select name="<?php echo $this->plugin_slug; ?>[type]">
                                    <?php
                                    foreach( $types as $id => $type ) {
                                        $selected = $id == $result->type ? 'selected' : '';
                                        echo '<option value="'.$id.'" '.$selected.'>'.$type['name'].'</option>';
                                    }
                                    ?>
                                    </select>
                                </p>
                                <p>
                                    <strong>Ticketstatus:</strong>
                                    <select name="<?php echo $this->plugin_slug; ?>[status]">
                                        <?php
                                        foreach( $this->predef_ticketstatus as $id => $status ) {
                                            $selected = $id == $result->status ? 'selected' : '';
                                            echo '<option value="'.$id.'" '.$selected.'>'.$status.'</option>';
                                        }
                                        ?>
                                    </select>
                                </p>
                                <p>
                                    <strong>erstellt am:</strong> <?php echo date( 'd.m.Y', strtotime($result->creationdate) ); ?>
                                </p>
                                <p>
                                    <strong>zuletzt bearbeitet am:</strong> <?php echo date( 'd.m.Y', strtotime($result->changedate) ); ?>
                                </p>
                                <p>
                                    <strong>Kommentare:</strong> <?php echo $comments_count; ?> <a href="<?php echo get_page_link( get_option('wp_ticketsystem_single_page') ).'&ticket='.$result->id; ?>#comments">anzeigen</a>
                                </p>
                                <a href="<?php echo get_page_link( get_option('wp_ticketsystem_single_page') ).'&ticket='.$result->id; ?>" class="button" target="_blank">Ticket anschauen</a>

                                <input id="submit" class="button button-primary" type="submit" value="Ticket speichern" name="submit" style="float:right;" />
    </form>

                                <hr />
                                <p>
                                    <strong>Duplikat von:</strong>
                                </p>
                                <form action="" method="post">
                                    <input type="text" name="<?php echo $this->plugin_slug; ?>_original" placeholder="Original Ticket-ID" value="" />
                                    <input type="hidden" name="<?php echo $this->plugin_slug; ?>_duplicate" value="<?php echo $result->id; ?>" />
                                    <input id="submit" class="button" type="submit" value="Duplikate zusammenführen" name="submit" />
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="postbox-container" style="width:100%;">
                <div class="metabox-holder">
                    <div class="meta-box-sortables ui-sortable">
                        <div class="postbox">
                            <h3 class="hndle">
                                <span>Ticketersteller</span>
                            </h3>
                            <div class="inside">

                                <p>
                                    <strong>angegebene Daten</strong>
                                    <br />
                                    <strong>Name:</strong> <?php echo $result->name; ?>
                                    <br />
                                    <strong>E-Mail:</strong> <a href="mailto:<?php echo $result->email; ?>"><?php echo $result->email; ?></a>
                                </p>

                                <p>
                                    <strong>Wordpress Nutzerdaten</strong>
                                    <br />
                                    <strong>Nutzer-ID:</strong> <?php echo $user_info->ID; ?>
                                    <br />
                                    <strong>Nutzer-Rolle(n):</strong> <?php echo implode(', ', $user_info->roles); ?>
                                    <br />
                                    <strong>Nickname:</strong> <?php echo $user_info->user_login; ?>
                                    <br />
                                    <strong>Name:</strong> <?php echo $user_info->user_firstname.' '.$user_info->user_lastname; ?>
                                    <br />
                                    <strong>E-Mail:</strong> <a href="mailto:<?php echo $user_info->user_email; ?>"><?php echo $user_info->user_email; ?></a>
                                </p>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><!-- Ticket bearbeiten -->
<?php }



    /**
     * Output-Handling
     */
    private function return_admin_notice( $text, $class ) {
        $out = '';
        $out .= '<div class="'.$class.'"><p>';
        $out .= $text;
        $out .= '</p></div>';
        return $out;
    }

    private function shorten_string( $text, $length ) {
        $str_pos = strpos( $text, ' ', $length ) ? strpos( $text, ' ', $length ) : strlen( $text );
        $str_more = strpos( $text, ' ', $length ) ? '[...]' : '';
        return str_replace( '\r\n', ' ', substr( $text, 0, $str_pos ) ).' '.$str_more;
    }



    /**
     * Data-Handling
     */
    private function serialize_data( $datas ) {
        $serialized = array();
        foreach( $datas as $id => $data ) {
            $serialized[$id] = maybe_serialize($data);
        }
        $serialized = maybe_serialize($serialized);
        return $serialized;
    }

    private function unserialize_data( $datas ) {
        $unserialized = maybe_unserialize($datas);
        foreach( $unserialized as $id => $data ) {
            $unserialized[$id] = maybe_unserialize($data);
        }
        return $unserialized;
    }

    private function cleanup_data( $data, $type ) {
        $temp = '';
        switch($type) {
            case 'name':
                $temp = trim( preg_replace( '/[^a-zäöüß\-]/i', '', $data ) );
                break;
            case 'slug':
                $temp = trim( preg_replace( '/[^a-z_]/i', '', $data ) );
                break;
            case 'cssclass':
                $temp = trim( preg_replace( '/[^a-z0-9\-_]/i', '', $data ) );
                break;
            case 'hexcode':
                preg_match( '/(#[0-9abcdef]{6})/i', $data, $temp );
                $temp = $temp[1];
                break;
            case 'text':
                $temp = trim( preg_replace( '/[^a-z0-9äöüß\-_,&:;. \(\)]/i', '', $data ) );
                break;
            case 'int':
                $temp = trim( preg_replace( '/[^0-9]/i', '', $data ) ) * 1;
                break;
            default:
                $temp = false;
                break;
        }
        return esc_sql( esc_attr( $temp ) );
    }
} /* wp_ticketsystem */

/* Plugin initialisieren */
$wp_ticketsystem = new wp_ticketsystem;