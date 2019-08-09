<?php 
/**
 * Plugin Name: Jobs Post Type
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Plugin for Jobs custom post types.
 * Version: 1.0
 * Author: Melissa Coram
 * Author URI: http://URI_Of_The_Plugin_Author
 * License: GPL2
 */

 

//adds table to database

function themes_taxonomy() {  
    register_taxonomy(  
        'jobs_categories',  //The name of the taxonomy. Name should be in slug form (must not contain capital letters or spaces). 
        'jobs',        //post type name
        array(  
            'hierarchical' => true,  
            'label' => 'Job Type',  //Display name
            'query_var' => true,
            'rewrite' => array(
                'slug' => 'jobs', // This controls the base slug that will display before each term
                'with_front' => false // Don't display the category base before 
            )
        )  
    );  
}  

add_action( 'init', 'themes_taxonomy');

function jpt_install() {
     global $wpdb;
     $table_name = $wpdb->prefix . 'jobs_table';
     $charset_collate = $wpdb->get_charset_collate();

     $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        first_name tinytext NOT NULL,
        last_name tinytext NOT NULL,
        address text NOT NULL,
        phone tinytext NOT NULL,
        resume varchar(100) DEFAULT '' NOT NULL,
        comments text,
        category varchar(30) NOT NULL,
        email varchar(55) NOT NULL,
        time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,        
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );

    $post = array(
          'comment_status' => 'closed',
          'ping_status' =>  'closed' ,
          'post_author' => 1,
          'post_date' => date('Y-m-d H:i:s'),
          'post_name' => 'jobs-unsubscribe',
          'post_status' => 'publish' ,
          'post_title' => 'Unsubscribe',
          'post_type' => 'page',
          'post_content' => '[jobs_unsubscribe]'
    );  

    //insert page and save the id
    $newvalue = wp_insert_post( $post, false );
    //save the id in the database
    update_option( 'hclpage', $newvalue );
 }
register_activation_hook( __FILE__, 'jpt_install' );

function create_posttypes() {

	$labels1 = array(
				'name' => __( 'Jobs' ),
        		'singular_name' => __( 'Job' ),
				'all_items' => __( 'All Jobs' ),
				'view_item' => __( 'View Jobs' ),
				'add_new_item' => __( 'Add New Job' ),
				'edit_item' => __( 'Edit Job' ),
				'update_item' => __( 'Update Job' ),
				'search_items' => __( 'Search Jobs' ),
				'not_found' => __( 'Not Found' ),
				'not_found_in_trash' => __( 'Not Found in Trash' ),
			);

	$args1 = array(
				'label' => 'Jobs',
				'description' => 'Jobs available',
				'labels' => $labels1,
				'public' => true,
				'query_var' => true,
				'has_archive' => true,
				'rewrite' => array('slug'=> 'jobs', 'with_front' => false),
				'hierarchical' => false,
				'show_ui' => true,
				'show_in_menu' => true,
				'show_in_rest' => false,
				'show_in_nav_menus' => false,
				'show_in_admin_bar' => true,
				'menu_position' => 5,
				'can_export' => true,
				'exclude_from_search' => false,
				'publicly_queryable' => true,
				'capability_type' => 'post',
				'supports' => array('title', 'revisions', 'excerpt', 'editor')
			);

    register_post_type( 'jobs', $args1 );
}

// hook into the 'init' action
add_action( 'init', 'create_posttypes', 0 );


//unsubscribe form shortcode 
function jobs_unsubscribe_shortcode($atts) {
    global $_GET;

    if($_GET['email'] == NULL){
        $output = '<div id="form-area">' . "\n";
        $output .= '<div class="row">'. "\n";
        $output .= '<div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">'. "\n";
        $output .= '<div class="form-wrapper">'. "\n";
        $output .= '<form id="unsub-form" enctype="multipart/form-data" action="//' . $_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI] . 'method="GET">';
        $output .= '<div class="form-group text-center">' . "\n";
        $output .= '<div id="form-errors"><p>Enter your email here to unsubscribe from the jobs mailing list.</p></div>' . "\n";
        $output .= '<input type="email" name="email" class="form-control" required/><br/>' . "\n";
        $output .= '<button type="submit" class="btn btn-default">Unsubscribe</button>' . "\n";
        $output .= '</div>' . "\n";
        $output .= '</form>' . "\n";
        $output .= '</div>' . "\n";
        $output .= '</div>' . "\n";
        $output .= '</div>' . "\n";
        $output .= '</div>'. "\n";
    } else {
        $output = '<div class="form-wrapper"><p>';
        $output .= process_unsub_form();
        $output .= '</p></div>';
    }
    return $output;
}
add_shortcode( 'jobs_unsubscribe', 'jobs_unsubscribe_shortcode' );

//unsubscribe function
function process_unsub_form() {
    global $_POST;
    global $wpdb;
    $errors = '';
    $options = get_option('jptOptions');
    
    if ($options['jpt_error_msg']) {
        $errors_msg = $options['jpt_error_msg'];
    } else {
        $error_msg = 'There was an error submitting your information.  Please try again at another time. We apologize for the inconvenience.';
    }
    if ($options['jpt_unsub_msg']) {
        $success_msg = $options['jpt_unsub_msg'];
    } else {
        $success_msg = 'This email address has been removed from the job notification mailing list.';
    }
    
    if (!empty($_POST['email'])) {
        $email = $_POST['email'];
        $method = 'post';
    } else if (!empty($_GET['email'])) {
        $email = $_GET['email'];
        $method = 'get';
    } else {
        $errors = 'Please enter a valid email address.';
    }  

    if($errors == ''){
        $table_name = $wpdb->prefix . 'jobs_table';
        $call = $wpdb->delete($table_name, array('email' => $email));
        if(!$call){
            if ($method == 'post') {
                echo $error_msg;
                die();
            } else if ($method == 'get') {
                return $error_msg;
            }
        } else  {
           if ($method == 'post') {
                echo $success_msg;
                die();
            } else if ($method == 'get') {
                return $success_msg;
            }
        }
    } else {
        if ($method == 'post') {
            echo '<p>'.$errors.'</p>';
            die();
        } else if ($method == 'get') {
            return'<p>'.$errors.'</p>';
        } 
    }    
}

function jobs_shortcode( $atts ){
	$output = '<hr />';
    $jobs = get_posts(array('post_type'  => 'jobs'));
    if($jobs){
        foreach ( $jobs as $post ) {
            setup_postdata( $post );
            $output .= '<h3>' . get_the_title($post->ID) . '</h3>' . "\n";
            $output .= get_the_content() . "\n";
            $output .= '<hr />' . "\n";
        }
        wp_reset_postdata();
    }
    return $output;
}
add_shortcode( 'show_jobs', 'jobs_shortcode' );

function display_success(){
    $output = '<div id="form-area">' . "\n";
    $output .= '<div class="row">'. "\n";
    $output .= '<div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">'. "\n";
    $output .= '<div class="form-wrapper">'. "\n";
    $output .= '<p class="form-thank">Thank you for your interest! You will be notified of job postings you may be interested in at the email you provided.</p>' . "\n";
    $output .= '</div>' . "\n";
    $output .= '</div>' . "\n";
    $output .= '</div>' . "\n";
    $output .= '</div>'. "\n";
    return $output;
}

function display_form(array $errors = null){
    $output = '<div id="form-area">' . "\n";
    $output .= '<form id="jobs-form" enctype="multipart/form-data" action="//' . $_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI] . '#form-area" method="POST">'. "\n";
    $output .= '<div class="row">'. "\n";
    $output .= '<div class="col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">'. "\n";
    $output .= '<div class="form-wrapper">'. "\n";
    $output .= '<div id="form-errors" class="text-danger bg-warning"></div>'. "\n";
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label>First Name</label>'. "\n";
    $output .= '<input type="text" name="firstname" class="form-control" required>'. "\n";
    $output .= '</div>' . "\n";
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label>Last Name</label>'. "\n";
    $output .= '<input type="text" name="lastname" class="form-control" required>'. "\n";
    $output .= '</div>' . "\n";
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label>Address</label>'. "\n";
    $output .= '<input type="text" name="address" class="form-control" >'. "\n";
    $output .= '</div>' . "\n";
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label>Email Address</label>'. "\n";
    $output .= '<input type="email" name="email" class="form-control" required>'. "\n";
    $output .= '</div>' . "\n";
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label>Phone Number</label>'. "\n";
    $output .= '<input type="tel" name="phone" class="form-control">'. "\n";
    $output .= '</div>' . "\n";
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label>Interested In</label>'. "\n";
    $output .= '<select name="category" class="form-control" required>'. "\n";
    $output .= '<option value="All">All</option>'. "\n";
    $terms = get_terms('jobs_categories', array('hide_empty' => false));
    foreach($terms as $t){
        $output .= '<option value="' . $t->name . '">' . $t->name . '</option>'. "\n";
    }
    $output .= '</select>' . "\n";
    $output .= '</div>' . "\n";
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label>Resume</label>'. "\n";
    $output .= '<input type="file" name="resume" class="form-control" accept="application/msword, application/pdf">'. "\n";
    $output .= '</div>' . "\n";
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label>Additional Comments</label>'. "\n";
    $output .= '<textarea class="form-control" name="comments" rows="3"></textarea>'. "\n";
    $output .= '</div>' . "\n";
    $output .= '<div class="form-group text-center">'. "\n";
    $output .= '<button type="submit" class="btn btn-default">Submit</button>'. "\n";
    $output .= '</div>' . "\n";
    $output .= '</div>' . "\n";
    $output .= '</div>' . "\n";
    $output .= '</div>' . "\n";
    $output .= '</form>'. "\n";
    $output .= '</div>'. "\n";

    return $output;
}

function jobs_signup( $atts ){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $errors = process_form();
        if(empty($errors)){
           return display_success();
        } else {
           return display_form($errors);
        }
    } else {
        return display_form();
    }
}
add_shortcode( 'jobs_signup', 'jobs_signup' );

function send_mail($firstname, $lastname, $address, $phone, $resume, $comments, $category, $email){
    $options = get_option('jptOptions');
    if ($options['jpt_admin_email']) {
        $to = $options['jpt_admin_email'];
    } else {
        $to = get_option('admin_email');
    } if ($options['jpt_email_subject']) {
        $subject = $options['jpt_email_subject'];
    } else {
        $subject = 'New Employment Interest Submitted';    }
    
    $body = '';
    $body .= '<p><strong>Name:</strong> ' . $lastname . ', ' . $firstname . "</p>\r\n";
    $body .= '<p><strong>Address:</strong> ' . $address .  "</p>\r\n";
    $body .= '<p><strong>Phone:</strong> ' . $phone . "</p>\r\n";
    $body .= '<p><strong>Email:</strong> ' . $email . "</p>\r\n";
    $body .= '<p><strong>Interested In:</strong> ' . $category . "</p>\r\n";
    $body .= '<p><strong>Additional Comments:</strong> ' . $comments . "</p>\r\n";
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail( $to, $subject, $body, $headers, $resume );
}

function spam_scrubber($value) {
	$very_bad = array('to:', 'cc:', 'bcc:', 'content-type:', 'mime-version:', 'multipart-mixed:', 'content-transfer-encoding:');
	foreach ($very_bad as $v) {
		if (stripos($value, $v) !== false) return '';
	}
	$value = str_replace(array( "\r", "\n", "%Oa", "%Od"), ' ', $value);
	return trim($value);
}

//function to save form data to database
function process_form() {
    global $_POST;
    global $_FILES;
    global $wpdb;

    $errors = array();
    $date = new DateTime();
    $valphone = '/^([\(]{1}[0-9]{3}[\)]{1}[\.| |\-]{0,1}|^[0-9]{3}[\.|\-| ]?)?[0-9]{3}(\.|\-| )?[0-9]{4}$/';
	$valemail = '/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*\.(([0-9]{1,3})|([a-zA-Z]{2,3})|(aero|coop|info|museum|name))$/';

    if(!empty($_POST['firstname'])) {        
        $firstname = spam_scrubber($_POST['firstname']);
    } else {
        $errors[] = 'Please enter a first name.';
    }

    if(!empty($_POST['lastname'])) {        
        $lastname = spam_scrubber($_POST['lastname']);
    } else {
        $errors[] = 'Please enter a last name.';
    }

    if(preg_match($valemail, $_POST['email'])) {        
        $email = $_POST['email'];
    } else {
        $errors[] = 'Please enter a valid email address.';
    }

    if(!empty($_POST['phone'])) {    
        if(preg_match($valphone, $_POST['phone']))
            $phone = spam_scrubber($_POST['phone']);
        else
            $errors[] = 'Please enter a valid phone number.';
    } else {
        $phone = 'N/A';
    }
    
    if(!empty($_FILES['resume']) && !($_FILES['resume']['name'] === '') && empty($errors)) {
        $today = $date->format('Ymd');
        $resume = $firstname . $lastname . $today . basename($_FILES['resume']['name']);
        $uploadpath = plugin_dir_path(__FILE__) . 'uploads/' . $resume;
        move_uploaded_file($_FILES['resume']['tmp_name'], $uploadpath);     
    } else {
        $resume = 'N/A';
        $uploadpath = '';
    }
    
    if(!empty($_POST['comments'])) {        
        $comments = spam_scrubber($_POST['comments']);
    } else {     
        $comments = '';
    }

    if(!empty($_POST['category'])) {        
        $category = spam_scrubber($_POST['category']);
    } else {
        $category = 'N/A';
    }

    if(!empty($_POST['address'])) {        
        $address = spam_scrubber($_POST['address']);
    } else {
        $address = 'N/A';
    }
    
    if(empty($errors)){
        $options = get_option('jptOptions');
        $time = $date->format('Y-m-d H:i:s');
        $table_name = $wpdb->prefix . 'jobs_table';
        $call = $wpdb->insert($table_name, array( 
            'first_name' => $firstname, 
            'last_name' => $lastname, 
            'address' => $address, 
            'phone' => $phone, 
            'resume' => $resume, 
            'comments' => $comments, 
            'category' => $category, 
            'email' => $email,
            'time' => $time)
        );
        if(!$call){
            if ($options['jpt_error_msg']) {
                echo $options['jpt_error_msg'];
            } else {
                echo 'There was an error submitting your information.  Please try again at another time. We apologize for the inconvenience.';
            }
        } else  {
            send_mail($firstname, $lastname, $address, $phone, $uploadpath, $comments, $category, $email);
            if ($options['jpt_sign_up_msg']) {
                echo $options['jpt_sign_up_msg'];
            } else {
              echo 'Thank you for your interest! You will be notified of job postings you may be interested in at the email you provided.';  
            }  
        }              
        die();
    } else {
        foreach ($errors as $error) {
            echo '<p>'.$error.'</p>';
        }
        die();
    }
}

//add ajax script to wp queue
add_action( 'init', 'job_script_enqueuer' );

function job_script_enqueuer() {
   wp_register_script( 'job_form_script', plugins_url() . '/Jobs/jobsform.js', array('jquery') );
   wp_localize_script( 'job_form_script', 'JobAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));    

   wp_enqueue_script('jquery');
   wp_enqueue_script('job_form_script');
}

add_action('wp_ajax_process_form', 'process_form');
add_action('wp_ajax_nopriv_process_form', 'process_form');
add_action('wp_ajax_process_unsub_form', 'process_unsub_form');
add_action('wp_ajax_nopriv_process_unsub_form', 'process_unsub_form');

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Job_Seekers extends WP_List_Table {
	public function __construct() {
		parent::__construct([
			'singular' => __('Job Seeker', 'sp'), //singular name of the listed records
			'plural'   => __('Job Seekers', 'sp'), //plural name of the listed records
			'ajax'     =>    true//should this table support ajax?
		] );
	}

    //retrieve records from database
    public static function get_records($per_page = 10, $page_number = 1, $search = NULL) {
        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}jobs_table";
        if ($search != NULL) {
            $sql .= " WHERE first_name LIKE '%" . $search . 
                    "%' OR last_name LIKE '%" . $search . 
                    "%' OR email LIKE '%" . $search . 
                    "%' OR category LIKE '%" . $search . 
                    "%' OR phone LIKE '%" . $search . "%'";
        }
        if ( ! empty( $_REQUEST['orderby'] ) ) {
            $sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
            $sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
        } else {
            $sql .= ' ORDER BY time DESC';
        }
        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
        $result = $wpdb->get_results( $sql, 'ARRAY_A' );
        return $result;
    }

    //delete a record
    public static function delete_seeker($id) {
        global $wpdb;

        $wpdb->delete(
            "{$wpdb->prefix}jobs_table",
            [ 'id' => $id ],
            [ '%d' ]
        );
    }

    //return the number of records
    public static function record_count() {
        global $wpdb;
        $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}jobs_table";
        return $wpdb->get_var( $sql );
    }

    //name column
    function column_name($item) {
      // create a nonce
      $delete_nonce = wp_create_nonce('delete_seeker');
      $title = '<strong>' . $item['last_name'] . ', ' . $item['first_name'] . '</strong>';

      $actions = [
        'delete' => sprintf('<a href="?post_type=jobs&page=%s&action=%s&job_seeker=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['id']), $delete_nonce)
      ];

      return $title . $this->row_actions($actions);
    }

    //resume column
    function column_resume($item) {
      if ($item['resume'] === 'N/A' || $item['resume'] === '') {
          $link = 'N/A';
      } else {
          $link = '<a href="' . plugins_url() . '/Jobs/uploads/' . $item['resume'] . '" target="_blank"><span class="dashicons dashicons-media-default"></a>';
      }      
      return $link;
    }

     //resume column
    function column_email($item) {
      $link = '<a href="mailto:' . $item['email'] . '">' . $item['email'] . '</a>';
      return $link;
    }

    function column_date($item) {
      return date('M j, Y', strtotime($item['time']));
    }

    //default function for columns for which no method exists
    public function column_default($item, $column_name) {
        switch ($column_name) {
            case 'address':
            case 'first_name':
            case 'phone':
            case 'category':
            case 'resume':
            case 'comments':
                return $item[$column_name];
            default:
                return print_r($item, true); //Show the whole array for troubleshooting purposes
        }
    }

    //returns checkbox for bulk operations
    function column_cb( $item ) {
        return sprintf(
            '<input type="checkbox" name="seeker-bulk-delete[]" value="%s" />', $item['id']
        );
    }  

    //returns array of columns
    function get_columns() {
        $columns = [
            'cb'      => '<input type="checkbox" />',
            'name'    => __( 'Name', 'sp' ),
            'address' => __( 'Address', 'sp' ),
            'phone'    => __( 'Phone Number', 'sp' ),
            'email' => __('Email', 'sp'),
            'category' => __('Category', 'sp'),
            'resume' => __('Resume', 'sp'),
            'date' => __('Date', 'sp')            
        ];

        return $columns;
    }

    //make columns sortable 
    public function get_sortable_columns() {
        $sortable_columns = array(
            'name' => array('last_name', true),
            'category' => array('email', true),
            'date' => array('date', false)
        );

        return $sortable_columns;
    }

    //returns bulk actions
    public function get_bulk_actions() {
        $actions = [
            'seeker-bulk-delete' => 'Delete'
        ];

        return $actions;
    }
    
    //data query and filter, sorting, and pagination
    public function prepare_items($search = NULL) {
        $this->_column_headers = $this->get_column_info();
        //process bulk action
        $this->process_bulk_action();
        $per_page     = $this->get_items_per_page( 'customers_per_page', 10 );
        $current_page = $this->get_pagenum();
        $total_items  = self::record_count();

        $this->set_pagination_args([
            'total_items' => $total_items, //WE have to calculate the total number of items
            'per_page'    => $per_page //WE have to determine how many items to show on a page
        ]);

        $this->items = self::get_records($per_page, $current_page, $search);
    }

    public function process_bulk_action() {
        //when delete action is used
        if ('delete' === $this->current_action()) {
            //verify the nonce
            $nonce = esc_attr($_REQUEST['_wpnonce']);
            if (! wp_verify_nonce($nonce, 'delete_seeker')) {
                die('Not authorized.');
            }
            else {
                self::delete_seeker(absint($_GET['job_seeker']));
            }
        }

        //when bulk delete action is used
        if ((isset($_POST['action']) && $_POST['action'] == 'seeker-bulk-delete')
        || (isset($_POST['action2']) && $_POST['action2'] == 'seeker-bulk-delete')) {
            $delete_ids = esc_sql($_POST['seeker-bulk-delete']);

            // loop over the array of record IDs and delete them
            foreach ($delete_ids as $id) {
                self::delete_seeker($id);
            }
        }
    }
}

class Job_Seekers_Plugin {
	static $instance;

    // job seekers WP_List_Table object
	public $seekers_obj;

	// class constructor
	public function __construct() {
		add_filter('set-screen-option', [__CLASS__, 'set_screen'], 10, 3);
		add_action('admin_menu', [$this, 'plugin_menu']);
	}

    public static function set_screen($status, $option, $value) {
	   return $value;
    }

    public function plugin_menu() {
        $hook = add_submenu_page(
            'edit.php?post_type=jobs',
            'Job Seekers',
            'Job Seekers List',
            'manage_options',
            'wp_list_table_class',
            [ $this, 'plugin_settings_page' ]
        );

        add_action("load-$hook", [$this, 'screen_option']);
    }

    public function screen_option() {
        $option = 'per_page';
        $args   = [
            'label'   => 'Job Seekers',
            'default' => 10,
            'option'  => 'seekers_per_page'
        ];

        add_screen_option($option, $args);
        $this->seekers_obj = new Job_Seekers();
    }

    public function plugin_settings_page() {
        if( isset($_POST['s']) ){
            $this->seekers_obj->prepare_items($_POST['s']);
        } else {
            $this->seekers_obj->prepare_items();
        }
        ?>
        <div class="wrap">
            <h2>Job Seekers</h2>             
            <div id="poststuff"> 
                <div id="post-body" class="metabox-holder columns-2">
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <form method="post">
                                <input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
                                <?php $this->seekers_obj->search_box('Search', 'search-job-seekers');                                
                                $this->seekers_obj->display(); ?>
                            </form>
                        </div>
                    </div>
                </div>
                <br class="clear">
            </div>
        </div>
        <?php
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
	Job_Seekers_Plugin::get_instance();
});

function send_notification($email, $title, $term, $description){
    $to = $email;
    $subject = 'New Job Posting at Briarcliff Healthcare and Rehabilitation';
    $body = '';
    $body .= '<p><strong>Job Title:</strong> ' . $title . "</p>\r\n";
    $body .= '<p><strong>Description:</strong> ' . $description .  "</p>\r\n";
    $body .= '<p><strong>Category:</strong> ' . $term . "</p>\r\n";
    $body .= '<br/><br/><p><small>To stop receiving job notifications from this sender, <a href="';
    $body .= get_permalink(get_page_by_path('jobs-unsubscribe')) . '?email=' . $email . '">click here</a>.'; 
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail( $to, $subject, $body, $headers );
}

add_action( 'save_post', 'updateCheck', 10, 3 );

function updateCheck($post_ID, $post, $update) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
	   return;
    }
    if ($post->post_status != 'publish' || get_post_type($post_ID) != 'jobs') {
	   return;
    }
    
    global $wpdb;
    $terms = wp_get_post_terms($post_ID, 'jobs_categories');
    $thePost = get_post($post_ID);
    $sql = "SELECT * FROM {$wpdb->prefix}jobs_table WHERE category = 'All'";
    foreach($terms as $t){
        $sql .= " OR category = '$t->name'";
    }
    $users = $wpdb->get_results($sql);
    foreach($users as $u){
        send_notification($u->email, $thePost->post_title, $terms[0]->name, do_shortcode($thePost->post_content));
    }
}


//Settings Page Functions

//Adds Settings Page
function jpt_add_admin_menu() { 
	add_submenu_page('edit.php?post_type=jobs', 'Job Form Settings', 'Job Form Settings', 'manage_options', 'jobs_post_type', 'jpt_options_page');
}
add_action('admin_menu', 'jpt_add_admin_menu');
add_action('admin_init', 'jpt_settings_init');

//Fills Settings Page Content and adds settings to options table
function jpt_settings_init() {
	add_settings_section(
		'jpt_options_section', 
		__( 'Job Form Settings', 'wordpress' ), 
		'jpt_settings_section_callback', 
		'jptOptions'
	);

	add_settings_field( 
		'jpt_admin_email', 
		__( 'Email address for form submission', 'wordpress' ), 
		'jpt_admin_email_render', 
		'jptOptions', 
		'jpt_options_section'
	);
    
    add_settings_field( 
		'jpt_email_subject', 
		__( 'Subject line for form submission email', 'wordpress' ), 
		'jpt_email_subject_render', 
		'jptOptions', 
		'jpt_options_section'
	);

	add_settings_field( 
		'jpt_sign_up_msg', 
		__( 'Successful sign up message', 'wordpress' ), 
		'jpt_sign_up_msg_render', 
		'jptOptions', 
		'jpt_options_section' 
	);

	add_settings_field( 
		'jpt_error_msg', 
		__( 'Error message', 'wordpress' ), 
		'jpt_error_msg_render', 
		'jptOptions', 
		'jpt_options_section' 
	);
    
    add_settings_field( 
		'jpt_notify_subject', 
		__( 'Subject line for new job notification email', 'wordpress' ), 
		'jpt_notify_subject_render', 
		'jptOptions', 
		'jpt_options_section' 
	);

	add_settings_field( 
		'jpt_unsub_msg', 
		__( 'Successful unsubscribe message', 'wordpress' ), 
		'jpt_unsub_msg_render', 
		'jptOptions', 
		'jpt_options_section' 
	);
    register_setting('jptOptions', 'jptOptions');
}

//Functions to display settings form fields
function jpt_admin_email_render() {
	$options = get_option('jptOptions');
	?>
	<input type='email' size='100' name='jptOptions[jpt_admin_email]' value='<?php echo $options['jpt_admin_email'] ? $options['jpt_admin_email'] : get_option('admin_email'); ?>'>
	<?php
}

function jpt_email_subject_render() {
	$options = get_option('jptOptions');
	?>
    <input type='text' name='jptOptions[jpt_email_subject]' size='100' value='<?php echo $options['jpt_email_subject'] ? $options['jpt_email_subject'] : 'New Employment Interest Submitted' ?>'>
	<?php
}

function jpt_sign_up_msg_render() {
	$options = get_option('jptOptions');
	?>
    <textarea name='jptOptions[jpt_sign_up_msg]' cols='102' rows='5'><?php echo $options['jpt_sign_up_msg'] ? $options['jpt_sign_up_msg'] : 'Thank you for your interest! You will be notified of job postings you may be interested in at the email you provided.'; ?></textarea>
	<?php
}

function jpt_error_msg_render() {
	$options = get_option('jptOptions');
	?>
<textarea name='jptOptions[jpt_error_msg]' cols='102' rows='5'><?php echo $options['error_msg'] ? $options['error_msg'] : 'There was an error submitting your information.  Please try again at another time. We apologize for the inconvenience.'; ?></textarea>
	<?php
}

function jpt_notify_subject_render() {
	$options = get_option('jptOptions');
	?>
    <input type='text' name='jptOptions[jpt_notify_subject]' size='100' value='<?php echo $options['jpt_notify_subject'] ? $options['jpt_notify_subject'] : 'New Job Posting at ' . get_option('blogname') ?>'>
	<?php
}

function jpt_unsub_msg_render() {
	$options = get_option('jptOptions');
	?>
<textarea name='jptOptions[jpt_unsub_msg]' cols='102' rows='5'><?php echo $options['jpt_unsub_msg'] ? $options['jpt_unsub_msg'] : 'This email address has been removed from the job notification mailing list.'; ?></textarea>
	<?php
}

function jpt_settings_section_callback() {
	echo __( 'Override default job form settings.', 'wordpress' );
}

function jpt_options_page() {
	?>
	<form action='options.php' method='post'>
		<?php
		settings_fields( 'jptOptions' );
		do_settings_sections( 'jptOptions' );
		submit_button();
		?>
	</form>
	<?php
}