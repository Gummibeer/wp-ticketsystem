<?php
class wp_ticketsystem_filter_content extends wp_ticketsystem {
    public function filter_content_func( $content ) {
        global $wpdb;
        global $wp_ticketsystem;
        global $topic_template;

        if( !empty($topic_template->post->post_text) ) {
            $content = $topic_template->post->post_text;
        }

        preg_match_all( '/@#([0-9]+)/', $content, $tlinks );
        if( count($tlinks[1]) > 0 ) {
            foreach( $tlinks[1] as $tid ) {
                $tid = esc_sql( esc_attr( $tid ) ) * 1;

                $sql = strval( 'SELECT * FROM '.$wp_ticketsystem->table_name.' WHERE id = "'.$tid.'" AND parent = 0' );
                $link = $wpdb->get_row( $sql );
                if( $link ) {
                    $content = preg_replace( '/@#'.$link->id.'([^0-9]?)/', '<a href="'.get_page_link( get_option('wp_ticketsystem_single_page') ).'&ticket='.$link->id.'" title="zum Ticket #'.$link->id.'">Ticket #'.$link->id.'</a>$1', $content );
                } else {
                    $content = preg_replace( '/@#'.$tid.'([^0-9]?)/', '$1', $content );
                }
            }
        }

        return $content;
    }
}