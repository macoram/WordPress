<?php 
/**
 * Plugin Name: Xylem Surveys
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Plugin for managing Xylem Surveys.
 * Version: 1.0
 * Author: Melissa Coram
 * Author URI: 
 * License: GPL2
 */

require_once('GoogleTranslate.php');
use GoogleTranslate\GoogleTranslate;

//Add Location, Departments, and Improvements categories
function themes_taxonomy() {  
    register_taxonomy(  
        'locations',  //The name of the taxonomy. Name should be in slug form (must not contain capital letters or spaces). 
        'surveys',        //post type name
        array(  
            'hierarchical' => true,  
            'label' => 'Locations',  //Display name
            'query_var' => true,
            'rewrite' => array(
                'slug' => 'locations', // This controls the base slug that will display before each term
                'with_front' => false // Don't display the category base before 
            )
        )  
    );  
    register_taxonomy(  
        'departments',  
        'surveys',        
        array(  
            'hierarchical' => true,  
            'label' => 'Departments', 
            'query_var' => true,
            'rewrite' => array(
                'slug' => 'departments',
                'with_front' => false 
            )
        )  
    );  
    register_taxonomy(  
        'improvements',  
        'surveys',        
        array(  
            'hierarchical' => true,  
            'label' => 'Improvement Categories',
            'query_var' => true,
            'rewrite' => array(
                'slug' => 'improvements', 
                'with_front' => false
            )
        )  
    );
}  
add_action( 'init', 'themes_taxonomy');

//Add Departments, Locations, and Improvement Categories to menu for editing
function add_taxonomies_to_menu() {
    //Add Locations to Menu
    add_menu_page('Locations','Locations','manage_categories','edit-tags.php?taxonomy=locations&post_type=surveys',null, 'dashicons-admin-site', 1); 
    //Add Departments to Survey Menu
    add_menu_page('Departments','Departments','manage_categories','edit-tags.php?taxonomy=departments&post_type=surveys',null, 'dashicons-groups', 1); 
    //Add Improvement Categories to Survey Menu
    add_menu_page('Improvement Categories','Improvement Categories','manage_categories','edit-tags.php?taxonomy=improvements&post_type=surveys',null, 'dashicons-list-view', 1);
}
add_action('admin_menu', 'add_taxonomies_to_menu');

//Open Session if one does not exist 
add_action('wp_loaded', 'myStartSession', 1);
function myStartSession() {
    if(!session_id()) {
        session_start();
    }
}

//Add plugin tables to database and custom user roles
function xs_install() {
     //Add surveys table
     global $wpdb;
     $table_name = $wpdb->prefix . 'surveys_table';
     $charset_collate = $wpdb->get_charset_collate();

     $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        sign_in_name varchar(50) NOT NULL,
        location varchar(50) NOT NULL,
        language varchar(10) DEFAULT 'en' NOT NULL,
        employee_id varchar(20) NOT NULL,
        name text NOT NULL,
        employee_photo text NOT NULL,
        department varchar(50) NOT NULL,
        survey_title text NOT NULL,
        survey_title_trans text DEFAULT '' NOT NULL,
        improvement_category varchar(100) NOT NULL,
        before_action text NOT NULL,
        before_action_trans text DEFAULT '' NOT NULL,
        before_picture text NOT NULL,
        action text NOT NULL,
        action_trans text DEFAULT '' NOT NULL,
        action_date date DEFAULT '0000-00-00' NOT NULL,
        after_action text NOT NULL,
        after_action_trans text DEFAULT '' NOT NULL,
        after_picture text NOT NULL,  
        status varchar(8) DEFAULT '' NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    //Add Survey admin role (able to manage all surveys and edit custom taxonomies)
    add_role('xs_admin', 
             __('Survey Administrator'),
             array('read' => true,
                   'manage_categories' => true,
                   'read_private_pages' => true,
                   'list_users' => true,
                   'create_users' => true,
                   'delete_users' => true,
                   'edit_users' => true));
    //Add Survey reviewer role (can only manage surveys for their location)
    add_role('xs_reviewer', 
             __('Survey Reviewer'),
             array('read' => true, 'read_private_pages' => true, 'list_users' => true, 'edit_users' => true, 'delete_users' => true)); 
    add_role('xs_user', 
             __('Survey Basic User'),
             array('read' => true, 'read_private_pages' => true)); 
 }
register_activation_hook( __FILE__, 'xs_install' );

//Display sign in form
function display_sign_in(array $errors = null){
    //Get saved fields if user is logged in
    $user = '';
    $name = '';
    $language = '';
    $location = '';
    $emp_id = '';
    
    if(is_user_logged_in()) {
        $user = wp_get_current_user();   
        $name = $user->data->display_name;
        $language = get_the_author_meta('language', $user->ID);
        $location = get_the_author_meta('location', $user->ID);
        $emp_id = get_the_author_meta('employee_id', $user->ID);
    }
    //Format list of Locations
    $locationlist = '<datalist id="locations" title="Locations to choose from">' . "\n";
    $locations = get_terms('locations', array('hide_empty' => false));
    foreach($locations as $l){
        $locationlist .= '<option value="' . $l->name . '">' . "\n";
    }
    $locationlist .= '</datalist>';
    
    $output = '<div id="form-area">' . "\n";
    $output .= '<h1>WELCOME</h1>' . "\n";
    if(is_user_logged_in()) {
        if(in_array('xs_user', $user->roles) && !(get_the_author_meta('approved', $user->ID))) {
            $output .= '<p class="text-center"><strong>Your registration is pending approval.</strong><br>Start a new survey below.</p>' . "\n";
        } else {
            $output .= '<p class="text-center"><a href="' . get_option('home') . '/approved-surveys/">View approved surveys</a><br>or start a new survey below.</p>' . "\n";
        }        
    } else {
        $output .= '<p class="text-center"><a href="' . wp_login_url() . '">Log In</a> to access your saved information or <br><a href="' . get_option('home') . '/register/">Sign Up</a> if you are not yet registered.</p>' . "\n";
    }
    $output .= '<form id="sign-in" enctype="multipart/form-data" action="">' . "\n";
    $output .= '<div id="form-errors" class="text-danger bg-warning"></div>' . "\n";
    $output .= '<div class="form-group">' . "\n";
    $output .= '<label>Language</label>' . "\n";
    $output .= '<div id="google_translate_element"></div>' . "\n";
    $output .= '<input type="hidden" id="saved_lang" name="saved_lang" value="' . $language . '">' . "\n";
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label>Your Name</label>'. "\n";
    $output .= '<input type="text" name="name" class="form-control input-lg" value="' . $name . '" required>'. "\n";
    $output .= '</div>'. "\n";
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label>Your Location</label>'. "\n";
    $output .= '<input list="locations" name="location" class="form-control input-lg" value="' . $location[0] . '" required>'. "\n";
    $output .= $locationlist;
    $output .= '</div>'. "\n";
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label>Employee ID</label>'. "\n";
    $output .= '<input type="text" name="employee_id" class="form-control input-lg" value="' . $emp_id . '" required>'. "\n";
    $output .= '</div>'. "\n";
    $output .= '<div class="form-group">'. "\n";
    $output .= '<a id="sign-in-submit" href="' . get_option('home') . '/survey/" class="form-control input-lg">Continue</a>'. "\n";
    $output .= '</div>'. "\n";
    $output .= '</form>' . "\n";
    $output .= '</div>'. "\n";
    return $output;
}

//Shortcode to display sign in form
function sign_in_form( $atts ){
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        $errors = process_sign_in();
        $survey_url = get_option('home') . '/survey/';
        if(empty($errors)){
           wp_redirect($survey_url, $status = 200);
        } else {
           return display_sign_in($errors);
        }
    } else {
        return display_sign_in();
    }
}
add_shortcode( 'sign_in_form', 'sign_in_form' );

//Process Sign In form
function process_sign_in() {
    global $_POST;
    global $_SESSION;
    $errors = array();
    
    if(!empty($_POST['language'])) {        
        $language = spam_scrubber($_POST['language']);
    } else {
        $language = 'en';
    }
    
    if(!empty($_POST['name'])) {        
        $name = spam_scrubber($_POST['name']);
    } else {
        $errors[] = 'Please enter your name.';
    }
    
    if(!empty($_POST['location'])) {        
        $location = spam_scrubber($_POST['location']);
    } else {
        $errors[] = 'Please enter your location.';
    }
    
    if(!empty($_POST['employee_id'])) {        
        $id = spam_scrubber($_POST['employee_id']);
    } else {
        $errors[] = 'Please enter your Employee ID.';
    }
    
    if(empty($errors)){
        $_SESSION["sign_in_name"] = $name;
        $_SESSION["location"] = $location;
        $_SESSION["employee_id"] = $id;
        $_SESSION["language"] = $language;
        echo 'Success';
        die();
    } else {
        foreach ($errors as $error) {
            echo '<p>'.$error.'</p>';
        }
        die();
    }
}

//Display survey form
function display_form(array $errors = null){
    global $_SESSION;
    $options = get_option('xsOptions');
    
    //Format list of Departments
    $departmentlist = '<datalist id="departments" title="Departments to choose from">' . "\n";
    $departments = get_terms('departments', array('hide_empty' => false));
    foreach($departments as $d){
        $departmentlist .= '<option value="' . $d->name . '">' . "\n";
    }
    $departmentlist .= '</datalist>';
    
    //Format list of Improvement Categories
    $improvementcategory = '';
    $improvements = get_terms('improvements', array('hide_empty' => false));
    foreach($improvements as $i){
        $improvementcategory .= '<div class="checkbox"><label>';
        $improvementcategory .= '<input type="checkbox" name="improvement_cat[]" value="' . $i->name . '">' . $i->name . "\n";
        $improvementcategory .= '</label></div>';
    }
    // Loading gif for images
    $imageloader = '<div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>' . "\n";
    
    //Output Form
    $output = '<div id="form-area">' . "\n";
    $output .= '<h2>Point Kaizen</h2>';
    $output .= '<p id="welcome-statement">'. ($options['xs_welcome_message'] ? $options['xs_welcome_message'] : 'Great work should be shared… please share your great work below! (note that all fields are required)') . '</p>';
    $output .= '<form id="survey-form" enctype="multipart/form-data" action="//' . $_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI] . '#form-area" method="POST" data-ajax="false">'. "\n";
    $output .= '<div id="form-errors" class="text-danger bg-warning"></div>'. "\n";
    //Name
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label>' . ($options['xs_name_label'] ? $options['xs_name_label'] : 'Name(s)') . '</label>'. "\n";
    $output .= '<input type="text" name="name" class="form-control input-lg" required>'. "\n";
    $output .= '</div>' . "\n";
    //Employee Photo
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label for="employee-photo">' . ($options['xs_employee_photo_label'] ? $options['xs_employee_photo_label'] : 'Employee Photo') . '<br/><span class="upload-btn">Upload Photo</span></label>'. "\n";
    $output .= '<input id="employee-photo" type="file" class="input-lg" name="employee_photo" value="Upload Photo" required>'. "\n";
    $output .= '<img class="preview-img" id="emp_photo" src="" data-rotate="0"/><span class="cancel-img glyphicon glyphicon-remove-circle"></span><span class="rotate-img glyphicon glyphicon-repeat"></span>'  . "\n";
    $output .= $imageloader;
    $output .= '</div>' . "\n";
    //Department
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label>' . ($options['xs_department_label'] ? $options['xs_department_label'] : 'Department') . '</label>'. "\n";
    $output .= '<input list="departments" name="department" class="form-control input-lg" required>'. "\n";
    $output .= $departmentlist;
    $output .= '</div>' . "\n";
    //Survey Title
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label>' . ($options['xs_title_label'] ? $options['xs_title_label'] : 'Title') . '</label>'. "\n";
    $output .= '<input type="text" name="survey_title" class="form-control input-lg" maxlength="100" required>'. "\n";
    $output .= '</div>' . "\n";
    //Improvement Category
    $output .= '<div class="form-group" id="accordion" role="tablist" aria-multiselectable="true">'. "\n";
    $output .= '<label>' . ($options['xs_improvement_category_label'] ? $options['xs_improvement_category_label'] : 'Improvement Category') . '<br/><span class="small">Check All That Apply</span></label>'. "\n";
    $output .= '<div class="accordion-panel form-control input-lg">'. "\n";
    $output .= '<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne"><span class="caret"></span></a>'. "\n";
    $output .= '<div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">'. "\n";
    $output .= $improvementcategory;
    $output .= '</div>' . "\n";
    $output .= '</div>' . "\n";
    $output .= '</div>' . "\n";
    //Before Action Taken
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label>' . ($options['xs_before_action_label'] ? $options['xs_before_action_label'] : 'Before Action Taken') . '</label>'. "\n";
    $output .= '<textarea class="form-control input-lg" name="before_action" maxlength="200"></textarea>'. "\n";
    $output .= '</div>' . "\n";
    //Picture Before Action Taken
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label for="before-photo">' . ($options['xs_before_photo_label'] ? $options['xs_before_photo_label'] : 'Picture Before Action Taken') . '<br/><span class="upload-btn">Upload Photo</span></label>'. "\n";
    $output .= '<input id="before-photo" type="file" class="input-lg" name="before_photo" value="Upload Photo">' . "\n";
    $output .= '<img class="preview-img" id="before_photo" src="" data-rotate="0"/><span class="cancel-img glyphicon glyphicon-remove-circle"></span><span class="rotate-img glyphicon glyphicon-repeat"></span>' . "\n";
    $output .= $imageloader;
    $output .= '</div>' . "\n";
    //Action Taken
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label>' . ($options['xs_action_label'] ? $options['xs_action_label'] : 'Action Taken') . '</label>'. "\n";
    $output .= '<textarea class="form-control input-lg" name="action_taken" maxlength="200"></textarea>'. "\n";
    $output .= '</div>' . "\n";
    //After Action Taken
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label>' . ($options['xs_after_action_label'] ? $options['xs_after_action_label'] : 'After Action Taken') . '</label>'. "\n";
    $output .= '<textarea class="form-control input-lg" name="after_action" maxlength="200"></textarea>'. "\n";
    $output .= '</div>' . "\n";
    //Picture After Action Taken
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label for="after-photo">' . ($options['xs_after_photo_label'] ? $options['xs_after_photo_label'] : 'Picture After Action Taken') . '<br/><span class="upload-btn">Upload Photo</span></label>'. "\n";
    $output .= '<input id="after-photo" type="file" class="input-lg" name="after_photo" value="Upload Photo">' . "\n";
    $output .= '<img class="preview-img" id="after_photo" src="" data-rotate="0"/><span class="cancel-img glyphicon glyphicon-remove-circle"></span><span class="rotate-img glyphicon glyphicon-repeat"></span>' . "\n";
    $output .= $imageloader;
    $output .= '</div>' . "\n";
    //Date
    $output .= '<div class="form-group">'. "\n";
    $output .= '<label>' . ($options['xs_date_label'] ? $options['xs_date_label'] : 'Date') . '</label>'. "\n";
    $output .= '<div class="input-group date" id="datetimepicker1">' . "\n";
    $output .= '<input type="text" name="survey_date" class="form-control input-lg" />'. "\n";
    $output .= '<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>' . "\n";
    $output .= '</div>' . "\n";
    $output .= '</div>' . "\n";
    //Fields from sign in page
    $output .= '<input type="hidden" name="sign_in_name" value="' . $_SESSION["sign_in_name"] . '"/>' . "\n";
    $output .= '<input type="hidden" name="location" value="' . $_SESSION["location"] . '"/>' . "\n";
    $output .= '<input type="hidden" name="employee_id" value="' . $_SESSION["employee_id"] . '"/>' . "\n";
    $output .= '<input type="hidden" name="user_language" value="' . $_SESSION["language"] . '"/>' . "\n";
    //Image file names
    $output .= '<input type="hidden" id="temp_emp" name="temp_emp" value=""/>' . "\n";
    $output .= '<input type="hidden" id="temp_before" name="temp_before" value=""/>' . "\n";
    $output .= '<input type="hidden" id="temp_after" name="temp_after" value=""/>' . "\n";
    //Form Buttons
    $output .= '<div class="form-group buttons">'. "\n";
    $output .= '<button type="reset" class="form-control input-lg">Cancel</button>' . "\n";
    $output .= '<button id="survey-submit" type="submit" class="form-control input-lg">Submit</button>'. "\n";
    $output .= '</div>' . "\n";
    $output .= '</form>'. "\n";
    $output .= '</div>'. "\n";
    $output .= '<a id="home-link" href="' . get_option('home') . '"></a>' . "\n";

    return $output;
}

//Display on successful form submit
function display_success(){
    $output = '<div id="form-area">' . "\n";
    $output .= '<h2 class="form-thank">Thank You<br/>for your submission!</h2>' . "\n";
    $output .= '<a href="' . get_option('home') . '"class="btn form-control input-lg">Start a New Survey</a>' . "\n";
    $output .= '</div>'. "\n";
    return $output;
}

//Display if sign in session variables are not set 
function direct_to_signin() {
    $output = '<div id="form-area">' . "\n";
    $output .= '<h2 class="form-thank">You are not currently signed in.<br/><a href="' . get_option('home') . '">Click here to sign in.</a></h2>' . "\n";
    $output .= '</div>'. "\n";
    return $output;
}

//Shortcode to display survey form or success message as appropriate
function survey_form($atts){
    global $_SESSION;
    
    if(empty($_SESSION['sign_in_name']) || empty($_SESSION['location']) || empty($_SESSION['employee_id'])){
        return direct_to_signin();
    }

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
add_shortcode( 'survey_form', 'survey_form' );

//Display editable survey form
function display_edit_form(array $errors = null){
    //Get Form Data
    global $wpdb;
    $options = get_option('xsOptions');
    
    $survey_id = $_GET['id'];
    $table_name = $wpdb->prefix . 'surveys_table';
    $survey_obj = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE id=' . $survey_id, ARRAY_A);

    if(!$survey_obj){
        if ($options['xs_error_msg']) {
            echo $options['xs_error_msg'];
        } else {
            $output = 'There was an error retrieving your information.  Please try again at another time. We apologize for the inconvenience.';
        }
    } else  {
        $survey = $survey_obj[0];
        
        $user = wp_get_current_user();
        if(in_array('xs_admin', $user->roles) || in_array('administrator', $user->roles)){
            $has_access = true;
        } else {
            $approved_locations = get_the_author_meta('location', $user->ID);
            if (in_array($survey['location'], $approved_locations)) {
                $has_access = true;
            } else {
                $has_access = false;
            }
        }
        if ($has_access) {
            //Format list of Departments
            $departmentlist = '<datalist id="departments" title="Departments to choose from">' . "\n";
            $departments = get_terms('departments', array('hide_empty' => false));
            foreach($departments as $d){
                $departmentlist .= '<option value="' . $d->name . '">' . "\n";
            }
            $departmentlist .= '</datalist>';

            //Format list of Improvement Categories
            $improvementcategory = '';
            $improvements = get_terms('improvements', array('hide_empty' => false));
            $checked_list = explode(', ', $survey['improvement_category']);
            foreach($improvements as $i){
                if (in_array($i->name, $checked_list)) {
                    $checked = 'checked';
                } else {
                    $checked = '';
                }
                $improvementcategory .= '<div class="checkbox"><label>';
                $improvementcategory .= '<input type="checkbox" name="improvement_cat[]" value="' . $i->name . '"' . $checked . '>' . $i->name . "\n";
                $improvementcategory .= '</label></div>';
            }

            //Format list of Locations
            $locationlist = '<datalist id="locations" title="Locations to choose from">' . "\n";
            $locations = get_terms('locations', array('hide_empty' => false));
            foreach($locations as $l){
                $locationlist .= '<option value="' . $l->name . '">' . "\n";
            }
            $locationlist .= '</datalist>';

            //Format list of languages 
            $languages = array('zh-CN'=>'Chinese (Simplified)', 
                              'zh-TW'=>'Chinese (Traditional)',
                              'cs'=>'Czech',
                              'nl'=>'Dutch',
                              'tl'=>'Filipino',
                              'fr'=>'French',
                              'de'=>'German',
                              'hu'=>'Hungarian',
                              'it'=>'Italian',
                              'pl'=>'Polish',
                              'es'=>'Spanish',
                              'sv'=>'Swedish',
                              'en'=>'English');
            $langlist = '<option></option>';
            foreach($languages as $abr => $lang) {
                if ($survey['language'] == $abr) {
                    $selected = 'selected';
                } else {
                    $selected = '';
                }
                $langlist .= '<option value="' . $abr . '" ' . $selected . ' >' . $lang . '</option>';
            }
            //Format Date 
            $date = date_create($survey['action_date']);
            $date = date_format($date, 'F d, Y'); 

            // Loading gif for images
            $imageloader = '<div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>' . "\n";
            
            //Output Form
            $output = '<div id="form-area" class="edit">' . "\n";
            $output .= '<h2>Point Kaizen</h2>';
            $output .= '<form id="survey-form" enctype="multipart/form-data" action="//' . $_SERVER[HTTP_HOST] . $_SERVER[REQUEST_URI] . '#form-area" method="POST">'. "\n";
            $output .= '<div id="form-errors" class="text-danger bg-warning"></div>'. "\n";
            //Fields from sign in page
            //Submitted By
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>Submitted By</label>'. "\n";
            $output .= '<input type="text" name="sign_in_name" class="form-control input-lg" value="' . $survey['sign_in_name'] . '" required>'. "\n";
            $output .= '</div>' . "\n";
            //Employee ID
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>Employee ID</label>'. "\n";
            $output .= '<input type="text" name="employee_id" class="form-control input-lg" value="' . $survey['employee_id'] . '" maxlength="100" required>'. "\n";
            $output .= '</div>' . "\n";
            //Location
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>Location</label>'. "\n";
            $output .= '<input list="locations" name="location" class="form-control input-lg" value="' . $survey['location'] . '" required>'. "\n";
            $output .= $locationlist;
            $output .= '</div>'. "\n";
            //Language
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>Language</label>'. "\n";
            $output .= '<select name="user_language" class="form-control input-lg" required>'. "\n";
            $output .= $langlist;
            $output .= '</select>';
            $output .= '</div>'. "\n";
            $output .= '<hr/>';
            //Name
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>' . ($options['xs_name_label'] ? $options['xs_name_label'] : 'Name(s)') . '</label>'. "\n";
            $output .= '<input type="text" name="name" class="form-control input-lg" value="' . esc_attr($survey['name']) . '" required>'. "\n";
            $output .= '</div>' . "\n";
            //Employee Photo
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label for="employee-photo">' . ($options['xs_employee_photo_label'] ? $options['xs_employee_photo_label'] : 'Employee Photo') . '<br/><span class="upload-btn">Upload Photo</span></label>'. "\n";
            $output .= '<input id="employee-photo" type="file" class="input-lg" name="employee_photo" value="Upload Photo">'. "\n";
            $output .= '<img class="preview-img" id="emp_photo" src="' . plugins_url() . '/Xylem-Survey/uploads/' . $survey['employee_photo'] . '" data-rotate="0"/><span class="cancel-img glyphicon glyphicon-remove-circle"></span><span class="rotate-img glyphicon glyphicon-repeat"></span>'  . "\n";
            $output .= $imageloader;
            $output .= '</div>' . "\n";
            //Department
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>' . ($options['xs_department_label'] ? $options['xs_department_label'] : 'Department') . '</label>'. "\n";
            $output .= '<input list="departments" name="department" class="form-control input-lg" value="' . $survey['department'] . '" required>'. "\n";
            $output .= $departmentlist;
            $output .= '</div>' . "\n";
            //Survey Title
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>' . ($options['xs_title_label'] ? $options['xs_title_label'] : 'Title') . '</label>'. "\n";
            $output .= '<input type="text" name="survey_title" class="form-control input-lg" value="' . $survey['survey_title'] . '" required>' . "\n";
            if (!empty($survey['survey_title_trans'])) {
                $output .= 'Translated Title: ' . $survey['survey_title_trans'];
            }
            $output .= '</div>' . "\n";
            //Improvement Category
            $output .= '<div class="form-group" id="accordion" role="tablist" aria-multiselectable="true">'. "\n";
            $output .= '<label>' . ($options['xs_improvement_category_label'] ? $options['xs_improvement_category_label'] : 'Improvement Category') . '<br/><span class="small">Check All That Apply</span></label>'. "\n";
            $output .= '<div class="accordion-panel form-control input-lg">'. "\n";
            $output .= '<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne"><span class="caret"></span></a>'. "\n";
            $output .= '<div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">'. "\n";
            $output .= $improvementcategory;
            $output .= '</div>' . "\n";
            $output .= '</div>' . "\n";
            $output .= '</div>' . "\n";
            //Before Action Taken
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>' . ($options['xs_before_action_label'] ? $options['xs_before_action_label'] : 'Before Action Taken') . '</label>'. "\n";
            $output .= '<textarea class="form-control input-lg" name="before_action" maxlength="200" required>' . stripslashes(html_entity_decode($survey['before_action'], ENT_QUOTES | ENT_HTML401)) . '</textarea>'. "\n";
            if (!empty($survey['before_action_trans'])) {
                if(strlen($survey['before_action_trans']) > 200) {
                    $output .= '<div class="text-danger">The length of this translated text may exceed the available space on the PDF. Please check before approval.</div>';
                }
                $output .= 'Before Action translation: ' . $survey['before_action_trans'];
            }
            $output .= '</div>' . "\n";
            //Picture Before Action Taken
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label for="before-photo">' . ($options['xs_before_photo_label'] ? $options['xs_before_photo_label'] : 'Picture Before Action Taken') . '<br/><span class="upload-btn">Upload Photo</span></label>'. "\n";
            $output .= '<input id="before-photo" type="file" class="input-lg" name="before_photo" value="Upload Photo">' . "\n";
            $output .= '<img class="preview-img" id="before_photo" src="' . plugins_url() . '/Xylem-Survey/uploads/' . $survey['before_picture'] . '" data-rotate="0"/><span class="cancel-img glyphicon glyphicon-remove-circle"></span><span class="rotate-img glyphicon glyphicon-repeat"></span>' . "\n";
            $output .= $imageloader;
            $output .= '</div>' . "\n";
            //Action Taken
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>' . ($options['xs_action_label'] ? $options['xs_action_label'] : 'Action Taken') . '</label>'. "\n";
            $output .= '<textarea class="form-control input-lg" name="action_taken" maxlength="200" required>' . stripslashes(html_entity_decode($survey['action'], ENT_QUOTES | ENT_HTML401)) . '</textarea>'. "\n";
            if (!empty($survey['action_trans'])) {
                if(strlen($survey['action_trans']) > 200) {
                    $output .= '<div class="text-danger">The length of this translated text may exceed the available space on the PDF. Please check before approval.</div>';
                }
                $output .= 'Action Taken translation: ' . $survey['action_trans'];
            }
            $output .= '</div>' . "\n";
            //After Action Taken
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>' . ($options['xs_after_action_label'] ? $options['xs_after_action_label'] : 'After Action Taken') . '</label>'. "\n";
            $output .= '<textarea class="form-control input-lg" name="after_action" maxlength="200" required>' . html_entity_decode($survey['after_action'], ENT_QUOTES | ENT_HTML401) . '</textarea>'. "\n";
            if (!empty($survey['after_action_trans'])) {
                if(strlen($survey['after_action_trans']) > 200) {
                    $output .= '<div class="text-danger">The length of this translated text may exceed the available space on the PDF. Please check before approval.</div>';
                }
                $output .= 'After Action translation: ' . $survey['after_action_trans'];
            }
            $output .= '</div>' . "\n";
            //Picture After Action Taken
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label for="after-photo">' . ($options['xs_after_photo_label'] ? $options['xs_after_photo_label'] : 'Picture After Action Taken') . '<br/><span class="upload-btn">Upload Photo</span></label>'. "\n";
            $output .= '<input id="after-photo" type="file" class="input-lg" name="after_photo" value="Upload Photo">' . "\n";
            $output .= '<img class="preview-img" id="after_photo" src="' . plugins_url() . '/Xylem-Survey/uploads/' . $survey['after_picture'] . '" data-rotate="0"/><span class="cancel-img glyphicon glyphicon-remove-circle"></span><span class="rotate-img glyphicon glyphicon-repeat"></span>' . "\n";
            $output .= $imageloader;
            $output .= '</div>' . "\n";
            //Date
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>' . ($options['xs_date_label'] ? $options['xs_date_label'] : 'Date') . '</label>'. "\n";
            $output .= '<div class="input-group date" id="datetimepicker1">' . "\n";
            $output .= '<input type="text" name="survey_date" class="form-control input-lg" value="' . $date . '"/>'. "\n";
            $output .= '<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>' . "\n";
            $output .= '</div>' . "\n";
            $output .= '</div>' . "\n";
            //Form ID 
            $output .= '<input type="hidden" name="survey_id" value="' . $survey_id . '"/>' . "\n";
            //Image file names
            $output .= '<input type="hidden" id="temp_emp" name="temp_emp" value=""/>' . "\n";
            $output .= '<input type="hidden" id="temp_before" name="temp_before" value=""/>' . "\n";
            $output .= '<input type="hidden" id="temp_after" name="temp_after" value=""/>' . "\n";
            //Form Buttons
            $output .= '<div class="form-group buttons">'. "\n";
            $output .= '<a href="' . admin_url('admin.php?page=survey-main') . '" type="reset" class="form-control input-lg">Cancel</a>' . "\n";
            $output .= '<button id="survey-submit" type="submit" class="form-control input-lg">Submit</button>'. "\n";
            $output .= '</div>' . "\n";
            $output .= '</form>'. "\n";
            $output .= '</div>'. "\n";
            $output .= '<a id="home-link" href="' . get_option('home') . '"></a>' . "\n";
        } else {
            $output =  'You do not have access to view this information.';
        }
    }
    return $output;
}

//Shortcode to display edit survey form
function edit_survey_form($atts){
    return display_edit_form();
}
add_shortcode( 'edit_survey_form', 'edit_survey_form' );

//Check form for errors - if clean, save to database and send email
function process_form() {
    global $_POST;
    global $_FILES;
    global $wpdb;

    $errors = array();
    $is_emp_photo = false;
    $is_before_photo = false;
    $is_after_photo = false;
    $emp_photo_unchanged = false;
    $before_photo_unchanged = false;
    $after_photo_unchanged = false;
    
    if(!empty($_POST['sign_in_name'])) {
        $sign_in_name = spam_scrubber($_POST['sign_in_name']);
    } else {
        $errors['Please enter the Submitted By name.'];
    }
    
    if(!empty($_POST['employee_id'])){
        $employee_id = spam_scrubber($_POST['employee_id']);
    } else {
        $errors['Please enter the Employee ID'];
    }
    
    if(!empty($_POST['location'])) {
        $location = spam_scrubber($_POST['location']);
    } else {
        $errors['Please enter the Location.'];
    }
    
    if(!empty($_POST['user_language']) && $_POST['user_language'] != 'undefined') {
        $language = spam_scrubber($_POST['user_language']);
    } else {
        $language = 'en';
    }
    
    if(!empty($_POST['name'])) {        
        $name = spam_scrubber($_POST['name']);
    } else {
        $errors[] = 'Please enter your name.';
    }
    
    if(!empty($_POST['temp_emp'])) {
        $is_emp_photo = true;
    } else {
        if (!empty($_POST['survey_id'])) {
            $emp_photo_unchanged = true;
        } else {
            $errors[] = 'Please upload an employee photo.';
        }
    }
    
    if(!empty($_POST['department'])) {        
        $department = spam_scrubber($_POST['department']);
    } else {
        $errors[] = 'Please enter a department.';
    }
    
    if(!empty($_POST['survey_title'])) {        
        $survey_title = spam_scrubber($_POST['survey_title']);
    } else {
        $errors[] = 'Please enter a survey title.';
    }

    if(!empty($_POST['improvement_cat'])) {        
        $improvement_cat = implode(', ', $_POST['improvement_cat']);
    } else {
        $errors[] = 'Please select at least one improvement category.';
    }

    if(!empty($_POST['before_action'])) {        
        $before_action = spam_scrubber($_POST['before_action']);
    } else {
        $errors[] = 'Please fill out the Before Action Taken field.';
    }
    
    if(!empty($_POST['temp_before'])) {
        $is_before_photo = true;
    } else {
        if (!empty($_POST['survey_id'])) {
            $before_photo_unchanged = true;
        } else {
            $errors[] = 'Please upload a Before Action Taken photo.';   
        }
    }
    
    if(!empty($_POST['survey_date'])) {        
        $survey_date = $_POST['survey_date'];
    } else {
        $errors[] = 'Please enter the date.';
    }
    
    if(!empty($_POST['action_taken'])) {        
        $action = spam_scrubber($_POST['action_taken']);
    } else {
        $errors[] = 'Please fill out the Action Taken field.';
    }
    
    if(!empty($_POST['after_action'])) {        
        $after_action = spam_scrubber($_POST['after_action']);
    } else {
        $errors[] = 'Please fill out the After Action Taken field.';
    }
    
    if(!empty($_POST['temp_after'])) {
        $is_after_photo = true;
    } else {
        if (!empty($_POST['survey_id'])) {
            $after_photo_unchanged = true;
        } else {
            $errors[] = 'Please upload an After Action Taken photo.';   
        }
    }
    
    if(!empty($_POST['survey_id'])) {
        $survey_id = spam_scrubber($_POST['survey_id']);
    } else {
        $survey_id = '';
    }

    if(empty($errors)){
        //Check for uploaded photos and store
        $temp_dir = plugin_dir_path(__FILE__) . 'temp/';
        if($is_emp_photo) {
            $emp_photo = $employee_id . 'emp' . microtime(true) . '.png';
            $emp_upload = plugin_dir_path(__FILE__) . 'uploads/' . $emp_photo;    
            $temp_file = $temp_dir . $_POST['temp_emp'] . '.png';
            rename($temp_file, $emp_upload);  
        } 
        if($is_before_photo) {
            $before_photo = $employee_id . 'before' . microtime(true) . '.png';
            $before_upload = plugin_dir_path(__FILE__) . 'uploads/' . $before_photo;
            rename($temp_dir . $_POST['temp_before'] . '.png', $before_upload); 
        }
        if($is_after_photo) {
            $after_photo = $employee_id . 'after' . microtime(true) . '.png';
            $after_upload = plugin_dir_path(__FILE__) . 'uploads/' . $after_photo;  
            rename($temp_dir . $_POST['temp_after'] . '.png', $after_upload);    
        } 
        
        //Format action date for database
        $action_date = DateTime::createFromFormat('M d, Y', $survey_date)->format('Y-m-d');
        
        //Translate fields
        if ($language != 'en') {
            $t = new GoogleTranslate();
            $survey_title_trans = $t->translate($language, 'en', $survey_title);
            $before_action_trans = $t->translate($language, 'en', $before_action);
            $action_trans = $t->translate($language, 'en', $action);
            $after_action_trans = $t->translate($language, 'en', $after_action);
        } else {
            $survey_title_trans = '';
            $before_action_trans = '';
            $action_trans = '';
            $after_action_trans = '';
        }
        
        $options = get_option('xsOptions');
        $table_name = $wpdb->prefix . 'surveys_table';
        
        if(!empty($survey_id)) {
            //Update existing survey
            $update_fields = array('sign_in_name' => $sign_in_name,
                'location' => $location,
                'language' => $language,
                'employee_id' => $employee_id,
                'name' => $name, 
                'department' => $department, 
                'survey_title' => $survey_title, 
                'survey_title_trans' => $survey_title_trans, 
                'improvement_category' => $improvement_cat, 
                'before_action' => htmlentities($before_action, ENT_QUOTES | ENT_HTML401), 
                'before_action_trans' => $before_action_trans, 
                'action' => htmlentities($action, ENT_QUOTES | ENT_HTML401),
                'action_trans' => $action_trans,
                'action_date' => $action_date,
                'after_action' => htmlentities($after_action),
                'after_action_trans' => $after_action_trans);
            if(!$emp_photo_unchanged) {                
                $update_fields['employee_photo'] = $emp_photo;
            }
            if(!$before_photo_unchanged) {
                $update_fields['before_picture'] = $before_photo;
            }
            if(!$after_photo_unchanged) {
                $update_fields['after_picture'] = $after_photo;
            }
            $call = $wpdb->update($table_name, $update_fields, array('id' => $survey_id));
            if (!call) {
                if ($options['xs_error_msg']) {
                    echo $options['xs_error_msg'];
                } else {
                    $call_error = $wpdb->last_error . "\n";
                    if ($wpdb->last_error !== '') {
			file_put_contents(plugin_dir_path(__FILE__) . 'logs.txt', $call_error . "\n", FILE_APPEND);
		    }
                    echo 'There was an error submitting your information.  Please try again at another time. We apologize for the inconvenience. <br/><br/>' . $call_error;
                }
            } else {
                if ($options['xs_edit_success_msg']) {
                    echo $options['xs_edit_success_msg'];
                } else {
                  echo 'The survey has been successfully updated!';  
                } 
            }
            die();
        } else {
            //Save new survey
            $call = $wpdb->insert($table_name, array( 
                'sign_in_name' => $sign_in_name,
                'location' => $location,
                'language' => $language,
                'employee_id' => $employee_id,
                'name' => $name, 
                'employee_photo' => $emp_photo, 
                'department' => $department, 
                'survey_title' => $survey_title,
                'survey_title_trans' => $survey_title_trans,
                'improvement_category' => $improvement_cat, 
                'before_action' => htmlentities($before_action), 
                'before_action_trans' => $before_action_trans,
                'before_picture' => $before_photo, 
                'action' => htmlentities($action),
                'action_trans' => $action_trans,
                'action_date' => $action_date,
                'after_action' => htmlentities($after_action),
                'after_action_trans' => $after_action_trans,
                'after_picture' => $after_photo)
            );

            if(!$call){
                if ($options['xs_error_msg']) {
                    echo $options['xs_error_msg'];
                } else {
		    $call_error = $wpdb->last_error . "\n";
                    if ($wpdb->last_error !== '') {
			file_put_contents(plugin_dir_path(__FILE__) . 'logs.txt', $call_error . "\n", FILE_APPEND);
		    }

                    echo 'There was an error submitting your information.  Please try again at another time. We apologize for the inconvenience. <br/><br/>' . $call_error;
                }
            } else  {
                send_mail($name, $emp_photo, $department, $survey_title, $improvement_cat, $before_action, $before_photo, $action, $action_date, $after_action, $after_photo, $location);
                if ($options['xs_sign_up_msg']) {
                    echo $options['xs_sign_up_msg'];
                } else {
                  echo 'Thank you for your submission!';  
                }  
            }             
            die();
        }
    } else {
        foreach ($errors as $error) {
            echo '<p>'.$error.'</p>';
        }
        die();
    }
}

//Spam scrubber
function spam_scrubber($value) {
	$very_bad = array('to:', 'cc:', 'bcc:', 'content-type:', 'mime-version:', 'multipart-mixed:', 'content-transfer-encoding:');
	foreach ($very_bad as $v) {
		if (stripos($value, $v) !== false) return '';
	}
	$value = str_replace(array( "\r", "\n", "%Oa", "%Od"), ' ', $value);
	return trim($value);
}

//Scale down image and rotate if necessary
function process_image() {
    $photo_data = base64_decode($_POST['photo']);
    $temp_dir = plugin_dir_path(__FILE__) . 'temp/';
    $temp_filepath = tempnam($temp_dir, 'xylem');
    $temp_fileinfo = pathinfo($temp_filepath);
    $temp_directory = $temp_fileinfo['dirname'];
    $temp_filename = $temp_fileinfo['filename'];
    if (!empty($_POST['filename']) || $_POST['filename'] != '') {
        unlink($temp_directory . '/' . $_POST['filename'] . '.png');
    }
    $tmp_image = file_put_contents($temp_directory . '/' . $temp_filename . '.png', $photo_data);
        
    unlink($temp_directory . '/' . $temp_fileinfo['basename']);
    echo $temp_fileinfo['filename'];
    die();
}

//Email sent on successful form submit
function send_mail($name, $emp_upload, $department, $survey_title, $improvement_category, $before_action, $before_upload, $action, $action_date, $after_action, $after_upload, $location){
    $options = get_option('xsOptions');
    global $wpdb;
    $table_name = $wpdb->prefix . 'surveys_table';
    $to = array();
    $reviewers = get_users(['role' => 'xs_reviewer']);
    foreach($reviewers as $reviewer) {
        $reviewerloc = get_the_author_meta('location', $reviewer->ID);
        if(in_array($location, $reviewerloc)) {
            $reviewerdata = get_userdata($reviewer->ID);
            $to[] = $reviewerdata->user_email;
        }
    }
    
    if(empty($to)) {
        $to = get_option('admin_email');
    }
    
    
    
    if ($options['xs_email_subject']) {
        $subject = $options['xs_email_subject'];
    } else {
        $subject = 'New Point Kaizen Submitted';    
    }
    
    $body = '';
    $body .= '<p><strong>Name:</strong> ' . $name . "</p>\r\n";
    $body .= '<p><strong>Employee Photo:</strong> <a href="' . plugins_url() . '/Xylem-Survey/uploads/' . $emp_upload . '">Click to view</a>' . "</p>\r\n";
    $body .= '<p><strong>Department:</strong> ' . $department . "</p>\r\n";
    $body .= '<p><strong>Survey Title:</strong> ' . $survey_title . "</p>\r\n";
    $body .= '<p><strong>Improvement Category:</strong> ' . $improvement_category . "</p>\r\n";
    $body .= '<p><strong>Before Action Taken:</strong> ' . $before_action . "</p>\r\n";
    $body .= '<p><strong>Picture Before Action Taken:</strong> <a href="' . plugins_url() . '/Xylem-Survey/uploads/' . $before_upload . '">Click to view</a>' . "</p>\r\n";
    $body .= '<p><strong>Action Taken:</strong> ' . $action . "</p>\r\n";
    $body .= '<p><strong>After Action Taken:</strong> ' . $after_action . "</p>\r\n";    
    $body .= '<p><strong>Picture After Action Taken:</strong> <a href="' . plugins_url() . '/Xylem-Survey/uploads/' . $after_upload . '">Click to view</a>' . "</p>\r\n";
    $body .= '<p><strong>Date:</strong> ' . $action_date . "</p>\r\n";
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail( $to, $subject, $body, $headers);
}

//Display all approved surveys
function approved_surveys($atts) {  
    //Check access for current user
    $user = wp_get_current_user();
    if(in_array('xs_admin', $user->roles) || in_array('administrator', $user->roles) || in_array('xs_reviewer', $user->roles) || (in_array('xs_user', $user->roles) && get_the_author_meta('approved', $user->id) == 1)){
        $has_access = true;
    } else {
        $has_access = false;
    }
    if ($has_access) {
        //Format list of Locations
        $locationlist = '<datalist id="locations" title="Locations to choose from">' . "\n";
        $locations = get_terms('locations', array('hide_empty' => false));
        foreach($locations as $l){
            $locationlist .= '<option value="' . $l->name . '">' . "\n";
        }
        $locationlist .= '</datalist>';
        //Format list of departments
        $departmentlist = '<datalist id="departments" title="Departments to choose from">' . "\n";
        $departments = get_terms('departments', array('hide_empty' => false));
        foreach($departments as $d){
            $departmentlist .= '<option value="' . $d->name . '">' . "\n";
        }
        $departmentlist .= '</datalist>';
        //Format list of Improvement Categories
        $improvementlist = '<datalist id="categories" title="Improvement Categories to choose from">';
        $improvements = get_terms('improvements', array('hide_empty' => false));
        foreach($improvements as $i){
            $improvementlist .= '<option value="' . $i->name . '">' . "\n";
        }
        $improvementlist .= '</datalist>';
        //Get Survey Data
        global $wpdb;
        $options = get_option('xsOptions');
        $table_name = $wpdb->prefix . 'surveys_table';
        $where = ' WHERE status = "Accepted"';
        if (!empty($_GET['location'])) {
            $location_query = spam_scrubber($_GET['location']);
            $where .= ' AND location = "' . $location_query . '"';
        }
        if (!empty($_GET['category'])) {
            $category_query = spam_scrubber($_GET['category']);
            $where .= ' AND improvement_category LIKE "%' . $category_query . '%"';
        }
        if (!empty($_GET['department'])) {
            $department_query = spam_scrubber($_GET['department']);
            $where .= ' AND department = "' . $department_query . '"';
        }
        if (!empty($_GET['start_date'])) {
            $start_query = DateTime::createFromFormat('M d, Y', spam_scrubber($_GET['start_date']))->format('Y-m-d');
        }
        if (!empty($_GET['end_date'])) {
            $end_query = DateTime::createFromFormat('M d, Y', spam_scrubber($_GET['end_date']))->format('Y-m-d');
        }
        if (!empty($start_query) && !empty($end_query)) {
            $where .= ' AND action_date >= "' . $start_query . '" AND action_date <= "' . $end_query . '"';
        } else if (!empty($start_query) && empty($end_query)) {
            $where .= ' AND action_date >= "' . $start_query . '"';
        } else if (empty($start_query) && !empty($end_query)) {
            $where .= ' AND action_date <= "' . $end_query . '"';
        }
        if (!empty($_GET['search'])) {
            $s_query = spam_scrubber($_GET['search']);
            $where .= ' AND (name LIKE "%' . $s_query . 
                    '%" OR action LIKE "%' . $s_query . 
                    '%" OR action_trans LIKE "%' . $s_query . 
                    '%" OR before_action LIKE "%' . $s_query . 
                    '%" OR before_action_trans LIKE "%' . $s_query . 
                    '%" OR after_action LIKE "%' . $s_query . 
                    '%" OR after_action_trans LIKE "%' . $s_query . 
                    '%" OR improvement_category LIKE "%' . $s_query . 
                    '%" OR survey_title LIKE "%' . $s_query . 
                    '%" OR survey_title_trans LIKE "%' . $s_query . '%")';
        }
                
        $surveys_obj = $wpdb->get_results('SELECT * FROM ' . $table_name . $where . ' ORDER BY location, action_date DESC', ARRAY_A);
            
        $location = '';
        $output = '<div class="approved-surveys row">';
        $output .= '<div class="col-xs-12">';
        $output .= '<h1>Point Kaizan</h1>';
        $output .= '<form class="form-inline" id="survey-search">';
        $output .= '<div class="form-group"><label for="startdate">Start Date:</label><div class="input-group date" id="startdate"><input type="text" name="start_date" class="form-control" value="' . $_REQUEST['start_date'] . '"/><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span></div></div>';
        $output .= '<div class="form-group"><label for="enddate">End Date:</label><div class="input-group date" id="enddate"><input type="text" name="end_date" class="form-control" value="' . $_REQUEST['end_date'] . '"/><span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span></div></div>';
        $output .= '<div class="form-group"><label for="location">Location: </label><input list="locations" name="location" class="form-control" value="' . $_REQUEST['location'] . '">' . $locationlist . '</div>';
        $output .= '<div class="form-group"><label for="department">Department: </label><input list="departments" name="department" class="form-control" value="' . $_REQUEST['department'] . '">' . $departmentlist . '</div>';
        $output .= '<div class="form-group"><label for="category">Improvement Category: </label><input list="categories" name="category" class="form-control" value="' . $_REQUEST['category'] . '">' . $improvementlist . '</div>';
        $output .= '<div class="form-group"><label for="search">Search: </label><input type="search" id="search" name="search" class="form-control" value="' . $_REQUEST['search'] . '"></div>';
        $output .= '<div class="form-group"><label>&emsp;</label><input type="submit" class="form-control" value="Search"></div>';
        $output .= '</form>';
        $output .= '</div>';
        $output .= '<div class="col-xs-12">';
        $output .= '<div>';

        if($surveys_obj){
            foreach($surveys_obj as $index => $survey) {
                $date = date_create($survey['action_date']);
                if ($survey['location'] !== $location) {
                    $location = $survey['location'];
                    $output .= '</div>' . "\n";
                    $output .= '<h2><a role="button" data-toggle="collapse" href="#collapse-' . $index . '" aria-expanded="true" aria-controls="collapse-' . $index . '">' . $location . '</a></h2>' . "\n";
                    $output .= '<div class="collapse in" id="collapse-' . $index . '" aria-expanded="true">' . "\n";
                }
                $output .= '<div class="survey-item">' . "\n";
                $output .= '<h3><a href="' . get_option('home') . '/view-survey?id=' . $survey['id'] . '">' . $survey['survey_title'] . '</a></h3>' . "\n";
                $output .= '<p class="small">' . date_format($date, 'F d, Y') . ' &mdash; ' . $survey['improvement_category'] . '</p>' . "\n";
                $output .= '</div>' . "\n";
            }
        } else {
            if ($options['xs_error_msg']) {
                $output .= '<p>' . $options['xs_error_msg'] . '</p></div>';
            } else {
                $output .= '<p style="margin-top: 50px;"><strong>There are no surveys that meet your search criteria.</strong></p></div>';
            }

        }
        $output .= '</div>';
        $output .= '</div>';
        
    } else {
        $output = '<p class="text-center" style="margin-top: 50px;">You do not have access to view this information.</p><p class="text-center"><a href="'. wp_login_url() . '"><strong>Log In</strong></a></p>';
    }
    return $output;
}
add_shortcode('approved_surveys', 'approved_surveys');

//Display single approved survey
function view_survey($atts) {
    //Get Form Data
    global $wpdb;
    $options = get_option('xsOptions');
    
    $survey_id = $_GET['id'];
    $table_name = $wpdb->prefix . 'surveys_table';
    $survey_obj = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE id=' . $survey_id, ARRAY_A);

    if(!$survey_obj){
        if ($options['xs_error_msg']) {
            echo $options['xs_error_msg'];
        } else {
            $output = 'There was an error retrieving your information.  Please try again at another time. We apologize for the inconvenience.';
        }
    } else  {
        $survey = $survey_obj[0];
        
        $user = $user = wp_get_current_user();
        if(in_array('xs_admin', $user->roles) || in_array('administrator', $user->roles) || in_array('xs_reviewer', $user->roles) || (in_array('xs_user', $user->roles) && get_the_author_meta('approved', $user->id) == 1)){
            $has_access = true;
        } else {
            $has_access = false;
        }
        if ($has_access) {
            
            //Format list of Improvement Categories
            $improvementcategory = '';
            $improvements = get_terms('improvements', array('hide_empty' => false));
            $checked_list = explode(', ', $survey['improvement_category']);
            foreach($improvements as $i){
                if (in_array($i->name, $checked_list)) {
                    $checked = '&#9745;';
                } else {
                    $checked = '&#9744;';
                }
                $improvementcategory .= '<p id="categories">';
                $improvementcategory .= $checked . '&emsp;' . $i->name . "\n";
                $improvementcategory .= '</p>';
            }

            //Format Date 
            $date = date_create($survey['action_date']);
            $date = date_format($date, 'F d, Y'); 

            //Output Form
            $output = '<div id="form-area" class="view-only">' . "\n";
            $output .= '<h2>Point Kaizen</h2>';

            //Location
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>Location:&emsp;</label>'. "\n";
            $output .= '<p>' . $survey['location'] . '</p>'. "\n";
            $output .= '</div>'. "\n";

            //Name
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>' . ($options['xs_name_label'] ? $options['xs_name_label'] : 'Name(s)') . ':&emsp;</label>'. "\n";
            $output .= '<p>' . esc_attr($survey['name']) . '</p>'. "\n";
            $output .= '</div>' . "\n";
            
            //Employee Photo
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label for="employee-photo">' . ($options['xs_employee_photo_label'] ? $options['xs_employee_photo_label'] : 'Employee Photo') . ':&emsp;</label>'. "\n";
            $output .= '<img class="preview-img" id="emp_photo" src="' . plugins_url() . '/Xylem-Survey/uploads/' . $survey['employee_photo'] . '"/>' . "\n";
            $output .= '</div>' . "\n";
            
            //Department
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>' . ($options['xs_department_label'] ? $options['xs_department_label'] : 'Department') . ':&emsp;</label>'. "\n";
            $output .= '<p>' . $survey['department'] . '</p>'. "\n";
            $output .= '</div>' . "\n";
            
            //Survey Title
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>' . ($options['xs_title_label'] ? $options['xs_title_label'] : 'Title') . ':&emsp;</label>'. "\n";
            $output .= '<p>' . $survey['survey_title'] . '</p>' . "\n";
            if (!empty($survey['survey_title_trans'])) {
                $output .= '<p class="small"><strong>Translated Title:</strong> ' . $survey['survey_title_trans'] . '</p>' . "\n";
            }
            $output .= '</div>' . "\n";
            
            //Improvement Category
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>' . ($options['xs_improvement_category_label'] ? $options['xs_improvement_category_label'] : 'Improvement Category') . ':</label>'. "\n";
            $output .= $improvementcategory;
            $output .= '</div>' . "\n";

            //Before Action Taken
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>' . ($options['xs_before_action_label'] ? $options['xs_before_action_label'] : 'Before Action Taken') . ':&emsp;</label>'. "\n";
            $output .= '<p>' . stripslashes(html_entity_decode($survey['before_action'], ENT_QUOTES | ENT_HTML401)) . '</p>'. "\n";
            if (!empty($survey['before_action_trans'])) {
                $output .= '<p class="small"><strong>Before Action translation:</strong> ' . $survey['before_action_trans'] . '</p>' . "\n";
            }
            $output .= '</div>' . "\n";
            
            //Picture Before Action Taken
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label for="before-photo">' . ($options['xs_before_photo_label'] ? $options['xs_before_photo_label'] : 'Picture Before Action Taken') . ':</label>'. "\n";
            $output .= '<img class="preview-img" id="before_photo" src="' . plugins_url() . '/Xylem-Survey/uploads/' . $survey['before_picture'] . '"/>' . "\n";
            $output .= '</div>' . "\n";
            
            //Action Taken
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>' . ($options['xs_action_label'] ? $options['xs_action_label'] : 'Action Taken') . ':&emsp;</label>'. "\n";
            $output .= '<p>' . stripslashes(html_entity_decode($survey['action'], ENT_QUOTES | ENT_HTML401)) . '</p>'. "\n";
            if (!empty($survey['action_trans'])) {
                $output .= '<p class="small"><strong>Action Taken translation:</strong> ' . $survey['action_trans'] . '</p>' . "\n";
            }
            $output .= '</div>' . "\n";
            
            //After Action Taken
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>' . ($options['xs_after_action_label'] ? $options['xs_after_action_label'] : 'After Action Taken') . ':&emsp;</label>'. "\n";
            $output .= '<p>' . html_entity_decode($survey['after_action'], ENT_QUOTES | ENT_HTML401) . '</p>'. "\n";
            if (!empty($survey['after_action_trans'])) {
                $output .= '<p class="small"><strong>After Action translation:</strong> ' . $survey['after_action_trans'] . '</p>' . "\n";
            }
            $output .= '</div>' . "\n";
            
            //Picture After Action Taken
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label for="after-photo">' . ($options['xs_after_photo_label'] ? $options['xs_after_photo_label'] : 'Picture After Action Taken') . ':</label>'. "\n";
            $output .= '<img class="preview-img" id="after_photo" src="' . plugins_url() . '/Xylem-Survey/uploads/' . $survey['after_picture'] . '"/>' . "\n";
            $output .= '</div>' . "\n";
            
            //Date
            $output .= '<div class="form-group">'. "\n";
            $output .= '<label>' . ($options['xs_date_label'] ? $options['xs_date_label'] : 'Date') . ':&emsp;</label>'. "\n";
            $output .= '<p>' . $date . '</p>'. "\n";
            $output .= '</div>' . "\n";
            
            $output .= '<input type="hidden" name="user_language" value="' . $survey['language'] . '"/>' . "\n";

            $output .= '<div class="form-group buttons">'. "\n";
            $output .= '<a href="' . get_option('home') . '/approved-surveys/" type="reset" class="form-control input-lg">Back to Surveys</a>' . "\n";
            $output .= '<a href="' . plugins_url() . '/Xylem-Survey/survey-pdf.php?survey_id=' . $survey['id'] . '" class="form-control input-lg" target="_blank">View PDF <span class="dashicons dashicons-format-aside"></span></a' . "\n";
            $output .= '</div>' . "\n";

            $output .= '</div>'. "\n";
        } else {
            $output =  'You do not have access to view this information.';
        }
    }
    return $output;
}
add_shortcode('view_survey', 'view_survey');

//Add scripts and processing functions to wp queue
function survey_script_enqueuer() {
    wp_register_script( 'survey_form_script', plugins_url() . '/Xylem-Survey/surveyform.js', array('jquery'), '1.0' );
    //fallback for browsers that don't support Datalist
    wp_register_script( 'datalist_polyfill', plugins_url() . '/Xylem-Survey/datalist-polyfill.min.js', array('jquery') );
    wp_localize_script( 'survey_form_script', 'SurveyAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));    
    wp_enqueue_script('jquery');
    wp_enqueue_script('bootstrap', get_template_directory_uri() .'/js/bootstrap.min.js', array('jquery'), null, true);
    wp_enqueue_script('moment', get_template_directory_uri() .'/js/vendor/moment.min.js', null, null, false);
    wp_enqueue_script('datepicker', get_template_directory_uri() .'/js/vendor/bootstrap-datetimepicker.min.js', null, null, false);
    wp_enqueue_script('main', get_template_directory_uri() .'/js/main.js', array('jquery'), 0.4, true);
    wp_enqueue_style('admin_styles' , get_template_directory_uri().'/admin-style.css');
    wp_enqueue_script('datalist_polyfill');
    wp_enqueue_script('survey_form_script');
}
add_action( 'init', 'survey_script_enqueuer' );
add_action('wp_ajax_process_form', 'process_form');
add_action('wp_ajax_nopriv_process_form', 'process_form');
add_action('wp_ajax_process_sign_in', 'process_sign_in');
add_action('wp_ajax_nopriv_process_sign_in', 'process_sign_in');
add_action('wp_ajax_process_image', 'process_image');
add_action('wp_ajax_nopriv_process_image', 'process_image');


// ===== Admin Functions ==== //

//Survey Table Page
require_once('survey-table.php');

//Survey Settings Page
require_once('survey-settings.php');

//Xylem Users Management
require_once('xylem-users.php');
