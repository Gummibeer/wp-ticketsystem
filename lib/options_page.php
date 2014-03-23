<?php
class wp_ticketsystem_options_page extends wp_ticketsystem {
    public function ticketsystem_register_settings() {
        register_setting( 'wp_ticketsystem_settings_group', 'wp_ticketsystem_single_page' );
    }

    public function ticketsystem_add_options_page() {
        add_menu_page( 'Ticketsystem Übersicht', 'Ticketsystem', 'administrator', 'wp_ticketsystem_options_page', array( 'wp_ticketsystem_options_page', 'ticketsystem_add_options_page_display' ) );
    }

    public function ticketsystem_add_options_page_display() {
        global $wpdb;
        global $wp_ticketsystem;

        $types =    array(
                        0 => '<span style="color:#7f0907;text-transform:uppercase;font-weight:700;">Fehler</span>',
                        1 => '<span style="color:#ffaa1d;text-transform:uppercase;font-weight:700;">Aufgabe</span>',
                        2 => '<span style="color:#1ca3f9;text-transform:uppercase;font-weight:700;">Funktion</span>'
                    );

        $status =   array(
                        0 => 'offen',
                        1 => 'gesichtet',
                        2 => 'eingeplant',
                        3 => 'in Bearbeitung',
                        4 => 'wird geprüft',
                        5 => 'abgeschlossen'
                    );
?>
<div class="wrap">
    <h2>Ticketsystem Übersicht</h2>
    <form action="options.php" method="post" name="options">
        <?php
        settings_fields( 'wp_ticketsystem_settings_group' );
        do_settings_sections( 'wp_ticketsystem_settings_group' );
        ?>
        <label for="wp_ticketsystem_single_page">Seite für Ticketeinzelansicht</label>
        <select name="wp_ticketsystem_single_page">
            <?php
            $pages = get_pages();
            foreach ( $pages as $page ) {
                $option = '<option value="'.$page->ID.'">';
                $option .= $page->post_title;
                $option .= '</option>';
                echo $option;
            }
            ?>
        </select>
        <?php submit_button(); ?>
    </form>

    <h2 class="nav-tab-wrapper">
        <a class='nav-tab' href='?page=wp_ticketsystem_options_page&tab=start'>Übersicht</a>
        <a class='nav-tab' href='?page=wp_ticketsystem_options_page&tab=bug'>Fehler</a>
        <a class='nav-tab' href='?page=wp_ticketsystem_options_page&tab=task'>Aufgaben</a>
        <a class='nav-tab' href='?page=wp_ticketsystem_options_page&tab=feature'>Funktionen</a>
    </h2>

    <table class="wp-list-table widefat tickets" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Typ</th>
                <th>Status</th>
                <th>Titel</th>
                <th>Beschreibung</th>
                <th>Nutzer</th>
                <th>erstellt</th>
                <th>geändert</th>
                <th><span class="comment-grey-bubble"></span></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <th>ID</th>
                <th>Typ</th>
                <th>Status</th>
                <th>Titel</th>
                <th>Beschreibung</th>
                <th>Nutzer</th>
                <th>erstellt</th>
                <th>geändert</th>
                <th><span class="comment-grey-bubble"></span></th>
            </tr>
        </tfoot>

        <tbody>
<?php
switch($_GET['tab']) {
    case 'bug':
        $sql = strval( 'SELECT id FROM '.$wp_ticketsystem->table_name.' WHERE tickettype = 0 AND parent = 0 ORDER BY creationdate ASC' );
        break;
    case 'task':
        $sql = strval( 'SELECT id FROM '.$wp_ticketsystem->table_name.' WHERE tickettype = 1 AND parent = 0 ORDER BY creationdate ASC' );
        break;
    case 'feature':
        $sql = strval( 'SELECT id FROM '.$wp_ticketsystem->table_name.' WHERE tickettype = 2 AND parent = 0 ORDER BY creationdate ASC' );
        break;
    default:
        $sql = strval( 'SELECT id FROM '.$wp_ticketsystem->table_name.' WHERE ticketstatus < 5 AND parent = 0 ORDER BY creationdate ASC LIMIT 25' );
        break;
}
$results = $wpdb->get_col( $sql );
foreach($results as $id) {
    $sql = strval( 'SELECT * FROM '.$wp_ticketsystem->table_name.' WHERE id = "'.$id.'"' );
    $result = $wpdb->get_row( $sql );

    $sql = strval( 'SELECT id FROM '.$wp_ticketsystem->table_name.' WHERE parent = '.$id );
    $parents = $wpdb->get_col( $sql );
    $parents = count($parents);

    if($result->ticketstatus == 5) {
        $tr_style = 'background:#b1b21b;';
    } else {
        $tr_style = '';
    }

    $user_temp = '';
    if($result->userid > 0) {
        $user_info = get_userdata($result->userid);
        $user_temp .= '<br />';
        $user_temp .= $user_info->ID.'&nbsp;'.$user_info->user_login;
    }
?>
    <tr style="<?php echo $tr_style; ?>">
        <td>#<?php echo $result->id; ?></td>
        <td><?php echo $types[$result->tickettype]; ?></td>
        <td><?php echo $status[$result->ticketstatus]; ?></td>
        <td><?php echo $result->title; ?></td>
        <td style="max-width:900px;"><?php echo str_replace( '\r\n', '<br />', $result->content ); ?></td>
        <td>
            <a href="mailto:<?php echo $result->email; ?>" title="<?php echo $result->email; ?>"><?php echo $result->name; ?></a>
            <?php echo $user_temp; ?>
        </td>
        <td style="max-width:75px;"><?php echo date('d.m.Y', strtotime($result->creationdate)); ?> <?php echo date('H:i:s', strtotime($result->creationdate)); ?></td>
        <td style="max-width:75px;"><?php echo date('d.m.Y', strtotime($result->changedate)); ?> <?php echo date('H:i:s', strtotime($result->changedate)); ?></td>
        <td><a class="post-com-count" title="0" href="#"><span class="comment-count"><?php echo $parents; ?></span></a></td>
    </tr>
<?php
}
?>

        </tbody>
    </table>

</div>

<?php
    }
}