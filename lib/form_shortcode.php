<?php
class wp_ticketsystem_form_shortcode extends wp_ticketsystem {
    public function ticket_form_func() {
        global $wpdb;
        global $wp_ticketsystem;
        global $current_user;
        get_currentuserinfo();

        $out = '';

        if( !empty($_POST['wp_ticket']) ) {
            $wp_ticket = $_POST['wp_ticket'];

            $wp_ticket['name'] = esc_sql( esc_attr( $wp_ticket['name'] ) );
            $wp_ticket['email'] = esc_sql( esc_attr( $wp_ticket['email'] ) );
            $wp_ticket['type'] = esc_sql( esc_attr( $wp_ticket['type'] ) ) * 1;
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
            if( empty($wp_ticket['type'] || $wp_ticket['type'] == 0) ) {
                $wp_ticket['error'] = true;
                array_push($wp_ticket['error_miss'], 'Ticket-Typ');
            }
            if( empty($wp_ticket['title']) ) {
                $wp_ticket['error'] = true;
                array_push($wp_ticket['error_miss'], 'Ticket-Titel');
            }
            if( empty($wp_ticket['content']) ) {
                $wp_ticket['error'] = true;
                array_push($wp_ticket['error_miss'], 'Ticket-Beschreibung');
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
                        'parent' => 0,
                        'tickettype' => $wp_ticket['type'],
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
                                                dein Ticket wurde erfolgreich übermittelt. Ticket-ID: <a href="#ticket-'.$wpdb->insert_id.'" title="zum Ticket">#'.$wpdb->insert_id.'</a>
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
                            <label class="input-group-addon" for="type">Typ</label>
                            <select name="wp_ticket[type]" id="type" class="form-control">
                                <option value="0">Fehler</option>
                                <option value="1">Aufgabe</option>
                                <option value="2">Funktion</option>
                            </select>
                        </div>
                    </div>';
            $out .= '<div class="form-group">
                        <div class="input-group">
                            <label class="input-group-addon" for="title">Betreff</label>
                            <input type="text" class="form-control" name="wp_ticket[title]" id="title" value="" />
                        </div>
                    </div>';
            $out .= '<div class="form-group">
                        <div class="input-group">
                            <label class="input-group-addon" for="content">Beschreibung</label>
                            <textarea class="form-control" name="wp_ticket[content]" id="content" rows="5"></textarea>
                        </div>
                    </div>';

            $out .= '<button type="submit" class="btn btn-success pull-right">Ticket absenden</button>';
        $out .= '</form>';

        return $out;
    }
}