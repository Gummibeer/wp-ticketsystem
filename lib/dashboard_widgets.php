<?php
class wp_ticketsystem_dashboard_widgets extends wp_ticketsystem {
    /**
     * Fehler-Tickets Dashbord Widget erstellen
     */
    public function ticketsystem_bug_add_dashboard_widget() {
        wp_add_dashboard_widget( 'ticketsystem_bug_add_dashboard_widget', 'offene Fehler Tickets', array( 'wp_ticketsystem_dashboard_widgets', 'ticketsystem_bug_add_dashboard_widget_function' ) );
    }
    public function ticketsystem_bug_add_dashboard_widget_function() {
        global $wpdb;
        global $wp_ticketsystem;

        $out = '';
        $sql = strval( 'SELECT id FROM '.$wp_ticketsystem->table_name.' WHERE tickettype = 0 AND ticketstatus < 5 AND parent = 0 ORDER BY creationdate ASC LIMIT 5' );
        $results = $wpdb->get_col( $sql );
        $out .= '<ul>';
        foreach($results as $id) {
            $sql = strval( 'SELECT * FROM '.$wp_ticketsystem->table_name.' WHERE id = "'.$id.'"' );
            $result = $wpdb->get_row( $sql );
            $out .= '<li>#'.$result->id.'&nbsp;-&nbsp;<strong>'.$result->title.'</strong>';
            $out .= '<br />';
            $out .= date('d.m.Y', strtotime($result->creationdate)).'&nbsp;|&nbsp;<a href="mailto:'.$result->email.'" title="'.$result->email.'">'.$result->name.'</a>';
            $out .= '</li>';
        }
        $out .= '</ul>';
        echo $out;
    }

    /**
     * Task-Tickets Dashbord Widget erstellen
     */
    public function ticketsystem_task_add_dashboard_widget() {
        wp_add_dashboard_widget( 'ticketsystem_task_add_dashboard_widget', 'offene Aufgaben Tickets', array( 'wp_ticketsystem_dashboard_widgets', 'ticketsystem_task_add_dashboard_widget_function' ) );
    }
    public function ticketsystem_task_add_dashboard_widget_function() {
        global $wpdb;
        global $wp_ticketsystem;

        $out = '';
        $sql = strval( 'SELECT id FROM '.$wp_ticketsystem->table_name.' WHERE tickettype = 1 AND ticketstatus < 5 AND parent = 0 ORDER BY creationdate ASC LIMIT 5' );
        $results = $wpdb->get_col( $sql );
        $out .= '<ul>';
        foreach($results as $id) {
            $sql = strval( 'SELECT * FROM '.$wp_ticketsystem->table_name.' WHERE id = "'.$id.'"' );
            $result = $wpdb->get_row( $sql );
            $out .= '<li>#'.$result->id.'&nbsp;-&nbsp;<strong>'.$result->title.'</strong>';
            $out .= '<br />';
            $out .= date('d.m.Y', strtotime($result->creationdate)).'&nbsp;|&nbsp;<a href="mailto:'.$result->email.'" title="'.$result->email.'">'.$result->name.'</a>';
            $out .= '</li>';
        }
        $out .= '</ul>';
        echo $out;
    }

    /**
     * Feature-Tickets Dashbord Widget erstellen
     */
    public function ticketsystem_feature_add_dashboard_widget() {
        wp_add_dashboard_widget( 'ticketsystem_feature_add_dashboard_widget', 'offene Funktion Tickets', array( 'wp_ticketsystem_dashboard_widgets', 'ticketsystem_feature_add_dashboard_widget_function' ) );
    }
    public function ticketsystem_feature_add_dashboard_widget_function() {
        global $wpdb;
        global $wp_ticketsystem;

        $out = '';
        $sql = strval( 'SELECT id FROM '.$wp_ticketsystem->table_name.' WHERE tickettype = 2 AND ticketstatus < 5 AND parent = 0 ORDER BY creationdate ASC LIMIT 5' );
        $results = $wpdb->get_col( $sql );
        $out .= '<ul>';
        foreach($results as $id) {
            $sql = strval( 'SELECT * FROM '.$wp_ticketsystem->table_name.' WHERE id = "'.$id.'"' );
            $result = $wpdb->get_row( $sql );
            $out .= '<li>#'.$result->id.'&nbsp;-&nbsp;<strong>'.$result->title.'</strong>';
            $out .= '<br />';
            $out .= date('d.m.Y', strtotime($result->creationdate)).'&nbsp;|&nbsp;<a href="mailto:'.$result->email.'" title="'.$result->email.'">'.$result->name.'</a>';
            $out .= '</li>';
        }
        $out .= '</ul>';
        echo $out;
    }

    /**
     * Chart-Tickets Dashbord Widget erstellen
     */
    public function ticketsystem_chart_add_dashboard_widget() {
        wp_add_dashboard_widget( 'ticketsystem_chart_add_dashboard_widget', 'Übersicht offene Tickets', array( 'wp_ticketsystem_dashboard_widgets', 'ticketsystem_chart_add_dashboard_widget_function' ) );
    }
    public function ticketsystem_chart_add_dashboard_widget_function() {
        global $wpdb;
        global $wp_ticketsystem;

        $bug_count = 0;
        $task_count = 0;
        $feature_count = 0;

        $out = '';
        $sql = strval( 'SELECT tickettype FROM '.$wp_ticketsystem->table_name.' WHERE ticketstatus < 5 AND parent = 0' );
        $results = $wpdb->get_col( $sql );
        $out .= '<div id="wp_ticketsystem_chart_chart"></div>';
        foreach($results as $type) {
            switch($type) {
                case 0:
                    $bug_count++;
                    break;
                case 1:
                    $task_count++;
                    break;
                case 2:
                    $feature_count++;
                    break;
            }
        }
        $out .= "<script type=\"text/javascript\">
                    jQuery(window).on('load', function() {
                        google.load('visualization', '1.0', {'packages':['corechart'], 'callback':drawChart});
                        function drawChart() {
                            var data = new google.visualization.DataTable();
                                data.addColumn('string', 'Typ');
                                data.addColumn('number', 'Anzahl');
                                data.addRows([
                                    ['Fehler', ".$bug_count."],
                                    ['Aufgaben', ".$task_count."],
                                    ['Funktionen', ".$feature_count."]
                                ]);

                            var options =   {
                                                'title':'Ticket Verhältnisübersicht',
                                                'width':380,
                                                'height':260,
                                                'colors': ['#7f0907', '#ffaa1d', '#1ca3f9']
                                            };
                            var chart = new google.visualization.PieChart(document.getElementById('wp_ticketsystem_chart_chart'));
                            chart.draw(data, options);
                        }
                    });
                </script>";
        echo $out;
    }
}