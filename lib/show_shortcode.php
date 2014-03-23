<?php
class wp_ticketsystem_show_shortcode extends wp_ticketsystem {
    public function show_tickets_func( $atts ) {
        global $wpdb;
        global $wp_ticketsystem;

        $types =    array(
            0 => '<span class="label label-danger"><i class="icon-bug"></i> Fehler</span>',
            1 => '<span class="label label-warning"><i class="icon-paw-pet"></i> Aufgabe</span>',
            2 => '<span class="label label-primary"><i class="icon-star"></i> Funktion</span>'
        );

        $status =   array(
            0 => 'offen',
            1 => 'gesichtet',
            2 => 'eingeplant',
            3 => 'in Bearbeitung',
            4 => 'wird geprüft',
            5 => 'abgeschlossen'
        );

        extract( shortcode_atts( array(
            'type' => 'all'
        ), $atts ) );
        switch($type) {
            case 'bug':
                $sql = strval( 'SELECT id FROM '.$wp_ticketsystem->table_name.' WHERE tickettype = 0 AND parent = 0 ORDER BY creationdate DESC' );
                break;
            case 'task':
                $sql = strval( 'SELECT id FROM '.$wp_ticketsystem->table_name.' WHERE tickettype = 1 AND parent = 0 ORDER BY creationdate DESC' );
                break;
            case 'feature':
                $sql = strval( 'SELECT id FROM '.$wp_ticketsystem->table_name.' WHERE tickettype = 2 AND parent = 0 ORDER BY creationdate DESC' );
                break;
            default:
                $sql = strval( 'SELECT id FROM '.$wp_ticketsystem->table_name.' WHERE ticketstatus < 5 AND parent = 0 ORDER BY creationdate DESC' );
                break;
        }

        $out = '';
        $out .= '<table class="table table-hover" cellspacing="0">';
        $out .= '<thead>
                    <tr>
                        <th>ID</th>
                        <th>Typ</th>
                        <th>Status</th>
                        <th>Titel & Beschreibung</th>
                        <th>erstellt</th>
                        <th>geändert</th>
                        <th><i class="icon-postalt"></i></th>
                    </tr>
                </thead>';
        $out .= '<tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Typ</th>
                        <th>Status</th>
                        <th>Titel & Beschreibung</th>
                        <th>erstellt</th>
                        <th>geändert</th>
                        <th><i class="icon-postalt"></i></th>
                    </tr>
                </tfoot>';

        $results = $wpdb->get_col( $sql );
        $out .= '<tbody>';
        foreach($results as $id) {
            $sql = strval( 'SELECT * FROM '.$wp_ticketsystem->table_name.' WHERE id = "'.$id.'"' );
            $result = $wpdb->get_row( $sql );

            $sql = strval( 'SELECT id FROM '.$wp_ticketsystem->table_name.' WHERE parent = '.$id );
            $parents = $wpdb->get_col( $sql );
            $parents = count($parents);

            $str_pos = strpos( $result->content, ' ', 200 ) ? strpos( $result->content, ' ', 200 ) : strlen( $result->content );
            $str_more = strpos( $result->content, ' ', 200 ) ? '[...]' : '';

            $out .= '<tr id="ticket-'.$result->id.'">
                        <td>#'.$result->id.'</td>
                        <td>'.$types[$result->tickettype].'</td>
                        <td>'.$status[$result->ticketstatus].'</td>
                        <td><a href="'.get_page_link( get_option('wp_ticketsystem_single_page') ).'&ticket='.$result->id.'" title="zum Ticket"><strong>'.$result->title.'</strong></a>'
                        .'<br />'.
                        str_replace( '\r\n', ' ', substr( $result->content, 0, $str_pos ) ).' '.$str_more.'</td>
                        <td>'.date('d.m.Y', strtotime($result->creationdate)).'</td>
                        <td>'.date('d.m.Y', strtotime($result->changedate)).'</td>
                        <td style="text-align:center;"><a href="'.get_page_link( get_option('wp_ticketsystem_single_page') ).'&ticket='.$result->id.'#comments" title="zu den Kommentaren" class="badge">'.$parents.'</a></td>
                    </tr>';
    }
        $out .= '</tbody>';

        $out .= '</table>';

        return $out;
    }
}