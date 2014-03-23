<?php
class wp_ticketsystem_single_shortcode extends wp_ticketsystem {
    public function single_ticket_func() {
        global $wpdb;
        global $wp_ticketsystem;
        global $current_user;
        get_currentuserinfo();

        $types =    array(
                        0 => 'fehler',
                        1 => 'aufgabe',
                        2 => 'funktion'
                    );

        $status =   array(
                        0 => '<span class="label label-default">offen</span>',
                        1 => '<span class="label label-default">gesichtet</span>',
                        2 => '<span class="label label-default">eingeplant</span>',
                        3 => '<span class="label label-warning">in Bearbeitung</span>',
                        4 => '<span class="label label-warning">wird geprüft</span>',
                        5 => '<span class="label label-success">abgeschlossen</span>'
                    );

        $ticket_id = esc_sql( esc_attr( $_GET['ticket'] ) ) * 1;

        $sql = strval( 'SELECT * FROM '.$wp_ticketsystem->table_name.' WHERE id = "'.$ticket_id.'"' );
        $result = $wpdb->get_row( $sql );

        $sql = strval( 'SELECT id FROM '.$wp_ticketsystem->table_name.' WHERE parent = '.$ticket_id );
        $parents = $wpdb->get_col( $sql );
        $parents = count($parents);

        $out = '';

        $out .= '<article class="panel panel-default">';
        $out .= '<div class="panel-heading">';
        $out .= '<h2 style="display: inline-block;margin-right: 10px;">#'.$result->id.'&nbsp;-&nbsp;'.$result->title.'</h2>';
        $out .= do_shortcode( '[label type="'.$types[$result->tickettype].'"/]' );
        $out .= $status[$result->ticketstatus];
        $out .= '<div class="clearfix"></div>';
        $out .= '<ul class="meta-infos clearfix">';
        $out .= '<li><strong>erstellt am:</strong>&nbsp;'.date('d.m.Y', strtotime($result->creationdate)).'</li>';
        $out .= '<li><strong>erstellt von:</strong>&nbsp;'.$result->name.'</li>';
        $out .= '<li><strong>zuletzt bearbeitet am:</strong>&nbsp;'.date('d.m.Y', strtotime($result->changedate)).'</li>';
        $out .= '<li><strong>Kommentare:</strong>&nbsp;<a href="#comments" title="zu den Kommentaren">'.$parents.'</a></li>';
        $out .= '</ul>';
        $out .= '</div>';
        $out .= '<div class="panel-body">';

        preg_match_all( '/@#([0-9]+)/', $result->content, $tlinks );
        if( count($tlinks[1]) > 0 ) {
            foreach( $tlinks[1] as $tid ) {
                $tid = esc_sql( esc_attr( $tid ) ) * 1;

                $sql = strval( 'SELECT * FROM '.$wp_ticketsystem->table_name.' WHERE id = "'.$tid.'" AND parent = 0' );
                $link = $wpdb->get_row( $sql );
                if( $link ) {
                    $result->content = preg_replace( '/@#'.$link->id.'([^0-9])/', '<a href="'.get_page_link( get_option('wp_ticketsystem_single_page') ).'&ticket='.$link->id.'" title="zum Ticket #'.$link->id.'">Ticket #'.$link->id.'</a>$1', $result->content );
                } else {
                    $result->content = preg_replace( '/@#'.$tid.'([^0-9])/', '$1', $result->content );
                }
            }
        }

        $out .= '<p>'.str_replace( '\r\n', '<br />', str_replace( '\r\n\r\n', '</p><p>', $result->content ) ).'</p>';
        $out .= '</div>';
        $out .= '</article>';

        $out .= '<h3>neuen Kommentar schreiben</h3>';
        if( !empty($_POST['wp_ticket']) ) {
            $wp_ticket = $_POST['wp_ticket'];

            $wp_ticket['name'] = esc_sql( esc_attr( $wp_ticket['name'] ) );
            $wp_ticket['email'] = esc_sql( esc_attr( $wp_ticket['email'] ) );
            $wp_ticket['title'] = esc_sql( esc_attr( $wp_ticket['title'] ) );
            $wp_ticket['content'] = esc_sql( esc_textarea( $wp_ticket['content'] ) );
            $wp_ticket['userid'] = $current_user->ID * 1;

            $wp_ticket['error'] = false;
            $wp_ticket['out'] = '';
            $wp_ticket['error_miss'] = array();
            if( empty($wp_ticket['name']) ) {
                $wp_ticket['error'] = true;
                array_push($wp_ticket['error_miss'], 'Name');
            }
            if( empty($wp_ticket['email']) ) {
                $wp_ticket['error'] = true;
                array_push($wp_ticket['error_miss'], 'E-Mail');
            }
            if( empty($wp_ticket['title']) ) {
                $wp_ticket['error'] = true;
                array_push($wp_ticket['error_miss'], 'Betreff');
            }
            if( empty($wp_ticket['content']) ) {
                $wp_ticket['error'] = true;
                array_push($wp_ticket['error_miss'], 'Kommentar');
            }
            $wp_ticket['error_miss'] = implode( ', ', $wp_ticket['error_miss']);
            if($wp_ticket['error_miss'] != '') {
                $wp_ticket['out'] .=  '<div class="alert alert-danger alert-dismissable">
                                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                                <strong>Fehler:</strong>
                                                fehlende Formular-Eingaben: '.$wp_ticket['error_miss'].', prüfe noch einmal deine Eingaben
                                            </div>';
            }

            if( !filter_var($wp_ticket['email'], FILTER_VALIDATE_EMAIL) ) {
                $wp_ticket['error'] = true;
                $wp_ticket['out'] .=  '<div class="alert alert-danger alert-dismissable">
                                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                                <strong>Fehler:</strong>
                                                leider hast du keine gültige E-Mail-Adresse eingegeben, prüfe noch einmal deine Eingabe
                                            </div>';
            }

            if( !$wp_ticket['error'] ) {
                $wp_ticket['success'] = $wpdb->insert(
                    $wp_ticketsystem->table_name,
                    array(
                        'title' => $wp_ticket['title'],
                        'content' => $wp_ticket['content'],
                        'email' => $wp_ticket['email'],
                        'name' => $wp_ticket['name'],
                        'parent' => $ticket_id,
                        'tickettype' => $result->tickettype,
                        'ticketstatus' => 0,
                        'userid' => $wp_ticket['userid'],
                        'creationdate' => date('Y-m-d H:i:s', (time() + 3600))
                    ),
                    array(
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%d',
                        '%d',
                        '%d',
                        '%d',
                        '%s'
                    )
                );

                if($wp_ticket['success']) {
                    $wp_ticket['out'] .=  '<div class="alert alert-success alert-dismissable">
                                                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                                <strong>Erfolg:</strong>
                                                dein Kommentar wurde erfolgreich übermittelt.</a>
                                            </div>';
                }
            }
            $out .= $wp_ticket['out'];
        }

        $out .= '<form action="" method="post">';
        $out .= '<div class="form-group">
                        <div class="input-group">
                            <label class="input-group-addon" for="name">Name</label>
                            <input type="text" class="form-control" name="wp_ticket[name]" id="name" value="'.$current_user->display_name.'" />
                        </div>
                    </div>';
        $out .= '<div class="form-group">
                        <div class="input-group">
                            <label class="input-group-addon" for="email">E-Mail</label>
                            <input type="text" class="form-control" name="wp_ticket[email]" id="email" value="'.$current_user->user_email.'" />
                        </div>
                    </div>';
        $out .= '<div class="form-group">
                        <div class="input-group">
                            <label class="input-group-addon" for="title">Betreff</label>
                            <input type="text" class="form-control" name="wp_ticket[title]" id="title" value="'.$wp_ticket['title'].'" />
                        </div>
                    </div>';
        $out .= '<div class="form-group">
                        <div class="input-group">
                            <label class="input-group-addon" for="content">Kommentar</label>
                            <textarea class="form-control" name="wp_ticket[content]" id="content" rows="5">'.$wp_ticket['content'].'</textarea>
                        </div>
                    </div>';

        $out .= '<button type="submit" class="btn btn-success pull-right">Kommentar absenden</button>';
        $out .= '</form>';

        if($parents > 0) {
            $out .= '<h3 id="comments">Kommentare</h3>';

            $sql = strval( 'SELECT id FROM '.$wp_ticketsystem->table_name.' WHERE parent = "'.$ticket_id.'"' );
            $results = $wpdb->get_col( $sql );
            foreach( $results as $id ) {
                $sql = strval( 'SELECT * FROM '.$wp_ticketsystem->table_name.' WHERE id = "'.$id.'"' );
                $result = $wpdb->get_row( $sql );

                $out .= '<article class="panel panel-default">';
                $out .= '<div class="panel-heading">';
                $out .= '<h4>'.$result->title.'</h4>';
                $out .= '<ul class="meta-infos clearfix">';
                $out .= '<li><strong>erstellt am:</strong>&nbsp;'.date('d.m.Y', strtotime($result->creationdate)).'</li>';
                $out .= '<li><strong>erstellt von:</strong>&nbsp;'.$result->name.'</li>';
                $out .= '</ul>';
                $out .= '</div>';
                $out .= '<div class="panel-body">';

                preg_match_all( '/@#([0-9]+)/', $result->content, $tlinks );
                if( count($tlinks[1]) > 0 ) {
                    foreach( $tlinks[1] as $tid ) {
                        $tid = esc_sql( esc_attr( $tid ) ) * 1;

                        $sql = strval( 'SELECT * FROM '.$wp_ticketsystem->table_name.' WHERE id = "'.$tid.'" AND parent = 0' );
                        $link = $wpdb->get_row( $sql );
                        if( $link ) {
                            $result->content = preg_replace( '/@#'.$link->id.'([^0-9])/', '<a href="'.get_page_link( get_option('wp_ticketsystem_single_page') ).'&ticket='.$link->id.'" title="zum Ticket #'.$link->id.'">Ticket #'.$link->id.'</a>$1', $result->content );
                        } else {
                            $result->content = preg_replace( '/@#'.$tid.'([^0-9])/', '$1', $result->content );
                        }
                    }
                }

                $out .= '<p>'.str_replace( '\r\n', '<br />', str_replace( '\r\n\r\n', '</p><p>', $result->content ) ).'</p>';

                $out .= '</div>';
                $out .= '</article>';
            }
        }

        echo $out;
    }
}