<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

//Functions for Survey List table
class Xylem_Survey extends WP_List_Table {
	public function __construct() {
		parent::__construct([
			'singular' => __('Survey', 'sp'), //singular name of the listed records
			'plural'   => __('Surveys', 'sp'), //plural name of the listed records
			'ajax'     =>    true,//should this table support ajax?
            'all_items' => array()
		] );
	}

    //set table classes 
    function get_table_classes() {
        return array('striped', 'table', 'table-responsive', 'widefat', $this->_args['plural']);
    }
    
    //retrieve records from database
    public static function get_records($per_page = 10, $page_number = 1, $searcharray = NULL) {
        global $wpdb;

        if($searcharray) {
            if(!empty($searcharray['s'])) {
                $search = $searcharray['s'];  
            } else {
                $search = NULL;
            }
            if(!empty($searcharray['locationfilter'])) {
                $locationfilter = $searcharray['locationfilter'];  
            } else {
                $locationfilter = NULL;
            }
            if(!empty($searcharray['startdate'])) {
                $startdate = $searcharray['startdate'];  
            } else {
                $startdate = NULL;
            }
            if(!empty($searcharray['enddate'])) {
                $enddate = $searcharray['enddate'];  
            } else {
                $enddate = NULL;
            }
        } else {
            $search = NULL;
            $locationfilter = NULL;
        }
        
        //Get List of Locations
        $locations = get_terms('locations', array('hide_empty' => false));
        //Get Locations assigned to user
        $default = array();
        $user = wp_get_current_user();
        $location = wp_parse_args(get_the_author_meta('location', $user->ID), $default); 
        //If user is admin allow all locations to be returned, otherwise limit to locations assign to user
        if(in_array('xs_admin', $user->roles) || in_array('administrator', $user->roles)) {
            $limit_location = '';
        } else {
            $limit_location = ' location IN (';
            for($x = 0; $x < count($location); $x++) {
                $limit_location .= '"' . $location[$x] . '"';
                if($x < count($location) - 1) {
                    $limit_location .= ',';
                }
            }
            $limit_location .= ')';
        }
        $sql = "SELECT * FROM {$wpdb->prefix}surveys_table";
    
        if(!empty($search) || !empty($locationfilter) || !empty($startdate) || !empty($enddate)) {
            $sql .= " WHERE ";
            if(!empty($locationfilter)) {
                $sql .= "location='" . $locationfilter . "'";
            }
            if(!empty($startdate) && !empty($enddate)) {
                if(!empty($locationfilter)) {
                    $sql .= " AND ";   
                }
                $sql .= "action_date >= '" . $startdate . "' AND action_date <= '" . $enddate . "'";
            } else if(!empty($startdate) && empty($enddate)) {
                $sql .= "action_date >= '" . $startdate . "'";
            } else if(empty($startdate) && !empty($enddate)) {
                $sql .= "action_date <= '" . $enddate . "'";
            }
            if (!empty($search)) {
                if (!empty($startdate) || !empty($enddate) || !empty($locationfilter)) {
                    $sql .= " AND ";
                }
                $sql .= "(name LIKE '%" . $search . 
                    "%' OR department LIKE '%" . $search . 
                    "%' OR improvement_category LIKE '%" . $search . 
                    "%' OR status LIKE '%" . $search . 
                    "%' OR survey_title LIKE '%" . $search . "%')";
            }
            if(!empty($limit_location)) {
                $sql .= ' HAVING' . $limit_location;
            }
        } else {
            if(!empty($limit_location)) {
                $sql .= ' WHERE' . $limit_location;
            }
        }
        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        } else {
            $sql .= ' ORDER BY action_date DESC';
        }

        $result = $wpdb->get_results( $sql, 'ARRAY_A' );
        return $result;
    }

    //delete a record
    public static function delete_survey($id) {
        global $wpdb;

        $wpdb->delete(
            "{$wpdb->prefix}surveys_table",
            [ 'id' => $id ],
            [ '%d' ]
        );
    }
    
    //Mark a survey as 'Accepted'
    public static function accept_survey($id) {
        global $wpdb;

        $wpdb->update(
            "{$wpdb->prefix}surveys_table", 
            ['status' => 'Accepted'],
            [ 'id' => $id ],
            [ '%s' ],
            [ '%d' ]
        );
    }
    
    //Mark a survey as 'Rejected'
    public static function reject_survey($id) {
        global $wpdb;

        $wpdb->update(
            "{$wpdb->prefix}surveys_table", 
            ['status' => 'Rejected'],
            [ 'id' => $id ],
            [ '%s' ],
            [ '%d' ]
        );
    }
    
    //name column
    function column_name($item) {
      // create a nonce
      $delete_nonce = wp_create_nonce('delete_survey');
      $accept_nonce = wp_create_nonce('accept_survey');
      $reject_nonce = wp_create_nonce('reject_survey');
      $title = '<strong>' . $item['name'] . '</strong>';

      $actions = [
        'view_details' => sprintf('<a href="' . get_option('home') . '/edit-survey?id=' . absint($item['id']) . '">View Details</a>'),
        'accept' => sprintf('<a href="?page=%s&action=%s&survey=%s&_wpnonce=%s">Accept</a>', esc_attr($_REQUEST['page']), 'accept', absint($item['id']), $accept_nonce),
        'reject' => sprintf('<a href="?page=%s&action=%s&survey=%s&_wpnonce=%s">Reject</a>', esc_attr($_REQUEST['page']), 'reject', absint($item['id']), $reject_nonce),
        'delete' => sprintf('<a href="?page=%s&action=%s&survey=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['id']), $delete_nonce)
      ];

      return $title . $this->row_actions($actions);
    }

    //employee photo column
    function column_employee_photo($item) {
      $link = '<a href="' . plugins_url() . '/Xylem-Survey/uploads/' . $item['employee_photo'] . '" target="_blank"><span class="dashicons dashicons-format-image"></a>';
      return $link;
    }

    //before photo column
    function column_before_picture($item) {
      $link = '<a href="' . plugins_url() . '/Xylem-Survey/uploads/' . $item['before_picture'] . '" target="_blank"><span class="dashicons dashicons-format-image"></a>';
      return $link;
    }

    //after photo column
    function column_after_picture($item) {
      $link = '<a href="' . plugins_url() . '/Xylem-Survey/uploads/' . $item['after_picture'] . '" target="_blank"><span class="dashicons dashicons-format-image"></a>';
      return $link;
    } 
    
    //survey date column
    function column_action_date($item) {
      return date('M d, Y', strtotime($item['action_date']));
    }

    //default function for columns for which no method exists
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'department':
            case 'survey_title':
            case 'improvement_category':
            case 'before_action':
            case 'action':
            case 'sign_in_name':
            case 'location':
            case 'employee_id':
            case 'status':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    //returns checkbox for bulk operations
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="survey-bulk-action[]" value="%s" />', $item['id']
        );
    }  

    function column_pdf($item) {

        $survey_id = $item['id'];
        $output = '<a href="' . plugins_url() . '/Xylem-Survey/survey-pdf.php?survey_id=' . $survey_id . '" target="_blank"><span class="dashicons dashicons-format-aside"></span></a>';
        return $output;
    }
    //returns array of columns
    function get_columns() {
        $columns = [
            'cb'      => '<input type="checkbox" />',
            'name'    => __( 'Name', 'sp' ),
            'department'    => __( 'Department', 'sp' ),
            'survey_title' => __('Survey Title', 'sp'),
            'improvement_category' => __('Improvement Category', 'sp'),
            'action_date' => __('Date', 'sp'),            
            'location' => __('Location', 'sp'),
            'status' => __('Status', 'sp'),
            'pdf' => __('View PDF', 'sp')
        ];

        return $columns;
    }

    //make columns sortable 
    public function get_sortable_columns() {
        $sortable_columns = array(
            'name' => array('name', true),
            'department' => array('department', true),
            'location' => array('location', false),
            'status' => array('status', false),
            'action_date' => array('action_date', false)
        );

        return $sortable_columns;
    }

    //returns bulk actions
    public function get_bulk_actions() {
        $actions = [
            'survey-bulk-accept' => 'Accept',
            'survey-bulk-reject' => 'Reject',
            'survey-bulk-delete' => 'Delete',
            'survey-bulk-pdf' => 'Send to PDF'
        ];

        return $actions;
    }
    
    //data query and filter, sorting, and pagination
    public function prepare_items($search = NULL) {
        $per_page     = $this->get_items_per_page( 'surveys_per_page', 10 );
        $current_page = $this->get_pagenum();
        $this->_column_headers = array( 
             $this->get_columns(),		// columns
             array(),			// hidden
             $this->get_sortable_columns(),	// sortable
        );
        
        $this->_column_headers = $this->get_column_info();
        
        //process bulk action
        $this->process_bulk_action();
        
        //$total_items  = self::record_count($search);
        $surveys = self::get_records($per_page, $current_page, $search);
        $total_items  = count($surveys);
        $this->_args['all_items'] = array();
        foreach($surveys as $index => $item) {
            $this->_args['all_items'][] = $item['id'];
        }
        
        $surveys = array_slice($surveys,(($current_page-1)*$per_page),$per_page);
        $this->set_pagination_args([
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page, //WE have to determine how many items to show on a page
            'total_pages' => ceil( $total_items / $per_page )
        ]);

        $this->items = $surveys;
    }
    
    //perform accept, reject, or delete actions
    public function process_bulk_action() {
        //when delete action is used
        if ('delete' === $this->current_action()) {
            //verify the nonce
            $nonce = esc_attr($_REQUEST['_wpnonce']);
            if (! wp_verify_nonce($nonce, 'delete_survey')) {
                die('Not authorized.');
            }
            else {
                self::delete_survey(absint($_GET['survey']));
            }
        }

        //when bulk delete action is used
        if ((isset($_POST['action']) && $_POST['action'] == 'survey-bulk-delete')
        || (isset($_POST['action2']) && $_POST['action2'] == 'survey-bulk-delete')) {
            $delete_ids = esc_sql($_POST['survey-bulk-action']);

            // loop over the array of record IDs and delete them
            foreach ($delete_ids as $id) {
                self::delete_survey($id);
            }
        }
        
        //when accept action is used
        if ('accept' === $this->current_action()) {
            //verify the nonce
            $nonce = esc_attr($_REQUEST['_wpnonce']);
            if (! wp_verify_nonce($nonce, 'accept_survey')) {
                die('Not authorized.');
            }
            else {
                self::accept_survey(absint($_GET['survey']));
            }
        }

        //when bulk accept action is used
        if ((isset($_POST['action']) && $_POST['action'] == 'survey-bulk-accept')
        || (isset($_POST['action2']) && $_POST['action2'] == 'survey-bulk-accept')) {
            $accept_ids = esc_sql($_POST['survey-bulk-action']);

            // loop over the array of record IDs and delete them
            foreach ($accept_ids as $id) {
                self::accept_survey($id);
            }
        }
        
        //when reject action is used
        if ('reject' === $this->current_action()) {
            //verify the nonce
            $nonce = esc_attr($_REQUEST['_wpnonce']);
            if (! wp_verify_nonce($nonce, 'reject_survey')) {
                die('Not authorized.');
            }
            else {
                self::reject_survey(absint($_GET['survey']));
            }
        }

        //when bulk delete action is used
        if ((isset($_POST['action']) && $_POST['action'] == 'survey-bulk-reject')
        || (isset($_POST['action2']) && $_POST['action2'] == 'survey-bulk-reject')) {
            $reject_ids = esc_sql($_POST['survey-bulk-action']);
            // loop over the array of record IDs and delete them
            foreach ($reject_ids as $id) {
                self::reject_survey($id);
            }
        }
        
        //when bulk pdf action is used
        if ((isset($_POST['action']) && $_POST['action'] == 'survey-bulk-pdf')
        || (isset($_POST['action2']) && $_POST['action2'] == 'survey-bulk-pdf')) {
            
        }
    }
    
    //overrides default pagination - added in query parameters to pagination links
    function pagination( $which ) {
        if ( empty( $this->_pagination_args ) ) {
            return;
        }

        $total_items = $this->_pagination_args['total_items'];
        $total_pages = $this->_pagination_args['total_pages'];
        $infinite_scroll = false;
        if ( isset( $this->_pagination_args['infinite_scroll'] ) ) {
            $infinite_scroll = $this->_pagination_args['infinite_scroll'];
        }

        if ( 'top' === $which && $total_pages > 1 ) {
            $this->screen->render_screen_reader_content( 'heading_pagination' );
        }

        $output = '<span class="displaying-num">' . sprintf( _n( '%s item', '%s items', $total_items ), number_format_i18n( $total_items ) ) . '</span>';

        $current = $this->get_pagenum();
        $removable_query_args = wp_removable_query_args();

        $current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

        $current_url = remove_query_arg( $removable_query_args, $current_url );
        
        //====== ADD QUERY PARAMETERS TO PAGINATION LINK URLS ======
        if($_POST['s']) {
            $current_url = esc_url_raw(add_query_arg('s', $_POST['s'], $current_url));
        }
        if($_POST['locationfilter']) {
            $current_url = esc_url_raw(add_query_arg('locationfilter', $_POST['locationfilter'], $current_url));
        }
        if($_POST['startdate']) {
            $current_url = esc_url_raw(add_query_arg('startdate', $_POST['startdate'], $current_url));
        }
        if($_POST['enddate']) {
            $current_url = esc_url_raw(add_query_arg('enddate', $_POST['enddate'], $current_url));
        }
        
        $page_links = array();

        $total_pages_before = '<span class="paging-input">';
        $total_pages_after  = '</span></span>';

        $disable_first = $disable_last = $disable_prev = $disable_next = false;

        if ( $current == 1 ) {
            $disable_first = true;
            $disable_prev = true;
        }
        if ( $current == 2 ) {
            $disable_first = true;
        }
        if ( $current == $total_pages ) {
            $disable_last = true;
            $disable_next = true;
        }
        if ( $current == $total_pages - 1 ) {
            $disable_last = true;
        }

        if ( $disable_first ) {
            $page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&laquo;</span>';
        } else {
            $page_links[] = sprintf( "<a class='first-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( remove_query_arg( 'paged', $current_url ) ),
                __( 'First page' ),
                '&laquo;'
            );
        }

        if ( $disable_prev ) {
            $page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&lsaquo;</span>';
        } else {
            $page_links[] = sprintf( "<a class='prev-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
                __( 'Previous page' ),
                '&lsaquo;'
            );
        }

        if ( 'bottom' === $which ) {
            $html_current_page  = $current;
            $total_pages_before = '<span class="screen-reader-text">' . __( 'Current Page' ) . '</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">';
        } else {
            $html_current_page = sprintf( "%s<input class='current-page' id='current-page-selector' type='text' name='paged' value='%s' size='%d' aria-describedby='table-paging' /><span class='tablenav-paging-text'>",
                '<label for="current-page-selector" class="screen-reader-text">' . __( 'Current Page' ) . '</label>',
                $current,
                strlen( $total_pages )
            );
        }
        $html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $total_pages ) );
        $page_links[] = $total_pages_before . sprintf( _x( '%1$s of %2$s', 'paging' ), $html_current_page, $html_total_pages ) . $total_pages_after;

        if ( $disable_next ) {
            $page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&rsaquo;</span>';
        } else {
            $page_links[] = sprintf( "<a class='next-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( add_query_arg( 'paged', min( $total_pages, $current+1 ), $current_url ) ),
                __( 'Next page' ),
                '&rsaquo;'
            );
        }

        if ( $disable_last ) {
            $page_links[] = '<span class="tablenav-pages-navspan" aria-hidden="true">&raquo;</span>';
        } else {
            $page_links[] = sprintf( "<a class='last-page' href='%s'><span class='screen-reader-text'>%s</span><span aria-hidden='true'>%s</span></a>",
                esc_url( add_query_arg( 'paged', $total_pages, $current_url ) ),
                __( 'Last page' ),
                '&raquo;'
            );
        }

        $pagination_links_class = 'pagination-links';
        if ( ! empty( $infinite_scroll ) ) {
            $pagination_links_class .= ' hide-if-js';
        }
        $output .= "\n<span class='$pagination_links_class'>" . join( "\n", $page_links ) . '</span>';

        if ( $total_pages ) {
            $page_class = $total_pages < 2 ? ' one-page' : '';
        } else {
            $page_class = ' no-pages';
        }
        $this->_pagination = "<div class='tablenav-pages{$page_class}'>$output</div>";

        echo $this->_pagination;
    }
    
    function extra_tablenav($which) {
        if ($which === 'top') {
            $output = '';
            if ((isset($_POST['action']) && $_POST['action'] == 'survey-bulk-pdf') || (isset($_POST['action2']) && $_POST['action2'] == 'survey-bulk-pdf')) {
                $pdf_ids = esc_sql($_POST['survey-bulk-action']);
                // loop over the array of record IDs and add to list
                $pdflist = '';
                foreach ($pdf_ids as $index => $id) {
                    if ($index > 0) {
                        $pdflist .= ',';
                    }
                    $pdflist .= $id;
                }
                $output .= '<div class="alignleft actions"><a href="' . plugins_url() . '/Xylem-Survey/survey-pdf.php?survey_id=' . $pdflist . '" class="button action generated-pdf" target="_blank">View Selected as PDF</span></a></div>';
            }
            $filteredlist = '';
            foreach($this->_args['all_items'] as $index => $item) {
                if ($index > 0) {
                    $filteredlist .= ',';
                }
                $filteredlist .= $item;
            }
            $output .= '<div class="alignleft actions"><a href="' . plugins_url() . '/Xylem-Survey/survey-pdf.php?survey_id=' . $filteredlist . '" class="button action" target="_blank">View All as PDF</span></a></div>';
            echo $output;
        }
    }
    
    //overrides default column header output - added in query parameters to sort links
    function print_column_headers( $with_id = true ) {
        list( $columns, $hidden, $sortable, $primary ) = $this->get_column_info();

        $current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
        $current_url = remove_query_arg( 'paged', $current_url );

        if ( isset( $_GET['orderby'] ) ) {
            $current_orderby = $_GET['orderby'];
        } else {
            $current_orderby = '';
        }

        if ( isset( $_GET['order'] ) && 'desc' === $_GET['order'] ) {
            $current_order = 'desc';
        } else {
            $current_order = 'asc';
        }

        if ( ! empty( $columns['cb'] ) ) {
            static $cb_counter = 1;
            $columns['cb'] = '<label class="screen-reader-text" for="cb-select-all-' . $cb_counter . '">' . __( 'Select All' ) . '</label>'
                . '<input id="cb-select-all-' . $cb_counter . '" type="checkbox" />';
            $cb_counter++;
        }

        foreach ( $columns as $column_key => $column_display_name ) {
            $class = array( 'manage-column', "column-$column_key" );

            if ( in_array( $column_key, $hidden ) ) {
                $class[] = 'hidden';
            }

            if ( 'cb' === $column_key )
                $class[] = 'check-column';
            elseif ( in_array( $column_key, array( 'posts', 'comments', 'links' ) ) )
                $class[] = 'num';

            if ( $column_key === $primary ) {
                $class[] = 'column-primary';
            }
            
            if ( isset( $sortable[$column_key] ) ) {
                list( $orderby, $desc_first ) = $sortable[$column_key];

                if ( $current_orderby === $orderby ) {
                    $order = 'asc' === $current_order ? 'desc' : 'asc';
                    $class[] = 'sorted';
                    $class[] = $current_order;
                } else {
                    $order = $desc_first ? 'desc' : 'asc';
                    $class[] = 'sortable';
                    $class[] = $desc_first ? 'asc' : 'desc';
                }

                 //====== ADD QUERY PARAMETERS TO PAGINATION LINK URLS ======
                    if($_POST['s']) {
                        $current_url = esc_url_raw(add_query_arg('s', $_POST['s'], $current_url));
                    }
                    if($_POST['locationfilter']) {
                        $current_url = esc_url_raw(add_query_arg('locationfilter', $_POST['locationfilter'], $current_url));
                    }
                    if($_POST['startdate']) {
                        $current_url = esc_url_raw(add_query_arg('startdate', $_POST['startdate'], $current_url));
                    }
                    if($_POST['enddate']) {
                        $current_url = esc_url_raw(add_query_arg('enddate', $_POST['enddate'], $current_url));
                    }

                $column_display_name = '<a href="' . esc_url( add_query_arg( compact( 'orderby', 'order' ), $current_url ) ) . '"><span>' . $column_display_name . '</span><span class="sorting-indicator"></span></a>';
            }

            $tag = ( 'cb' === $column_key ) ? 'td' : 'th';
            $scope = ( 'th' === $tag ) ? 'scope="col"' : '';
            $id = $with_id ? "id='$column_key'" : '';

            if ( !empty( $class ) )
                $class = "class='" . join( ' ', $class ) . "'";

            echo "<$tag $scope $id $class>$column_display_name</$tag>";
        }
    }
}

class Xylem_Survey_Plugin {
	static $instance;

    // job seekers WP_List_Table object
	public $surveys_obj;

	// class constructor
	public function __construct() {
		add_filter('set-screen-option', [__CLASS__, 'set_screen'], 10, 3);
		add_action('admin_menu', [$this, 'plugin_menu']);
	}

    public static function set_screen($status, $option, $value) {
	   return $value;
    }
    
    public function plugin_menu() {
        $hook = add_menu_page( 
            'Survey List',
            'Survey List',
            'read',
            'survey-main',
            [$this, 'manage_surveys_page'],
            'dashicons-clipboard',
            0
        );
        add_action("load-$hook", [$this, 'screen_option']);
    }

    public function screen_option() {
        $option = 'per_page';
        $args   = [
            'label'   => 'Surveys per page',
            'default' => 10,
            'option'  => 'surveys_per_page'
        ];

        add_screen_option($option, $args);
        $this->surveys_obj = new Xylem_Survey();
    }

    public function manage_surveys_page() {
        global $wpdb;
        $user = wp_get_current_user();
        if(in_array('administrator', $user->roles) || in_array('xs_admin', $user->roles) || in_array('xs_reviewer', $user->roles)) {
            $queryarray = array();
            if(isset($_POST['s']) || isset($_POST['locationfilter']) || isset($_POST['startdate']) || isset($_POST['enddate'])){
                if(isset($_POST['s'])) {
                    $queryarray['s'] = $_POST['s'];
                }
                if(isset($_POST['locationfilter'])){
                    $queryarray['locationfilter'] = $_POST['locationfilter'];
                } 
                if(isset($_POST['startdate'])) {
                    if(!empty($_POST['startdate'])) {
                        $start_date = DateTime::createFromFormat('M d, Y', $_POST['startdate'])->format('Y-m-d');
                        $queryarray['startdate'] = $start_date;
                    } 

                }
                if(isset($_POST['enddate'])) {
                    if(!empty($_POST['enddate'])) {
                        $end_date = DateTime::createFromFormat('M d, Y', $_POST['enddate'])->format('Y-m-d');
                        $queryarray['enddate'] = $end_date;
                    }                
                }
                $current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
                $parse_url = parse_url($current_url);
                $_SERVER['REQUEST_URI'] = $parse_url['path'] . '?page=survey-main';
            } else {
                if(isset($_REQUEST['s']) || isset($_REQUEST['locationfilter']) || isset($_REQUEST['startdate']) || isset($_REQUEST['enddate'])){    
                    if(isset($_REQUEST['s'])) {
                        $queryarray['s'] = $_REQUEST['s'];
                    } 
                    if(isset($_REQUEST['locationfilter'])){
                        $queryarray['locationfilter'] = $_REQUEST['locationfilter'];
                    }
                    if(isset($_REQUEST['startdate'])) {
                        if(!empty($_REQUEST['startdate'])) {
                            $start_date = DateTime::createFromFormat('M d, Y', $_REQUEST['startdate'])->format('Y-m-d');
                        } else {
                            $start_date = '';
                        }
                        $queryarray['startdate'] = $start_date;
                    }
                    if(isset($_REQUEST['enddate'])) {
                        if(!empty($_REQUEST['enddate'])) {
                            $end_date = DateTime::createFromFormat('M d, Y', $_REQUEST['enddate'])->format('Y-m-d');
                        } else {
                            $end_date = '';
                        }
                        $queryarray['enddate'] = $end_date;
                    }
                }
            }         

            if(!empty($queryarray)) {
                $this->surveys_obj->prepare_items($queryarray);
            } else {
                $this->surveys_obj->prepare_items();
            }


            $location = wp_parse_args(get_the_author_meta('location', $user->ID), $default);
            $locationlist = '<option ' . ($queryarray['locationfilter'] ? '' : 'selected') . '></option>' . "\n";
            $locations = get_terms('locations', array('hide_empty' => false));
            foreach($locations as $l){
                if(in_array('xs_admin', $user->roles) || in_array('administrator', $user->roles)) {
                    $locationlist .= '<option value="' . $l->name . '" ' . ($queryarray['locationfilter'] == $l->name ? 'selected' : '') . '>' . $l->name . "</option>" .  "\n";
                } else {
                    if(in_array($l->name, $location)) {
                        $locationlist .= '<option value="' . $l->name . '" ' . ($queryarray['locationfilter'] == $l->name ? 'selected' : '') . '>' . $l->name . "</option>" .  "\n";
                    }
                }
            }

            ?>

            <div id="survey-main-table" class="wrap">
                <h2>Surveys</h2>             
                <div id="poststuff"> 
                    <div id="post-body" class="metabox-holder">
                        <div id="post-body-content">
                            <div class="meta-box-sortables ui-sortable">
                                <form method="post">
                                    <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />

                                    <?php $this->surveys_obj->search_box('Search', 'search-surveys');?> 
                                    <p class="search-box">
                                        <label for="locationfilter">Location: </label>
                                        <select id="locationfilter" name="locationfilter">
                                            <?php echo $locationlist; ?>
                                        </select>
                                    </p>
                                    <div class="input-group date" id="enddate" style="float: right;">
                                        <input type="text" name="enddate" class="form-control" value="<?php echo $_REQUEST['enddate'] ?>" placeholder="End Date"/>
                                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                                    </div>
                                    <div class="input-group date" id="startdate" style="float: right;">
                                        <input type="text" name="startdate" class="form-control" value="<?php echo $_REQUEST['startdate'] ?>" placeholder="Start Date"/>
                                        <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
                                    </div>
                                    <?php $this->surveys_obj->display(); ?>
                                </form>
                            </div>
                        </div>
                    </div>
                    <br class="clear">
                </div>
            </div>
            <?php
        } else {
            ?>
            <p>Your access to this area has not yet been approved.</p>
            <?php
        }
    }

    //Singleton instance
    public static function get_instance() {
        if (! isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}

add_action('plugins_loaded', function () {
	Xylem_Survey_Plugin::get_instance();
});