<?php

//Add Location and Language fields to user profiles
function add_fields_to_profile($user) {
    
    //Get List of Locations
    $locations = get_terms('locations', array('hide_empty' => false));
    //Get Locations assigned to user
    $default = array();
    $location = wp_parse_args(get_the_author_meta('location', $user->ID), $default); 
    $user_language = wp_parse_args(get_the_author_meta('language', $user->ID), 'en'); 
    //Format Locations List 
    $location_options = '';
    foreach($locations as $l) {
        if(in_array($l->name, $location)) {
            $selected = 'selected';
        } else {
            $selected = '';
        }
        $location_options .= '<option value="' . $l->name . '" ' . $selected . '>' . $l->name . '</option>' . "\n";
    }
    
    //Only allow editing if user role allows
    $activeuser = wp_get_current_user();
    if (in_array('xs_admin', $activeuser->roles) || (in_array('administrator', $activeuser->roles))) {
        $disabled = '';
    } else {
        $disabled = 'disabled';
    }
    ?>
    <h2>Surveys Location</h2>
    <table class="form-table">
        <tr>
            <th><label for="location">Location</label></th>
            <td>
                <select id="location" name="location[]" multiple="multiple" <?php print($disabled); ?>>
                <?php print($location_options); ?>
                </select>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'add_fields_to_profile');
add_action('edit_user_profile', 'add_fields_to_profile');

//Function to save location updates in user profile
function xs_save_profile_fields($user_id) {
    $user = wp_get_current_user();
    if (!current_user_can('edit_user', $user_id) || in_array('xs_reviewer', $user->roles)) {
   	 return false;
    }

    if (empty($_POST['location'])) {
   	 return false;
    }

    update_user_meta($user_id, 'location', $_POST['location']);
}
add_action('personal_options_update', 'xs_save_profile_fields');
add_action('edit_user_profile_update', 'xs_save_profile_fields');

//Add Approve Action to User Menu
function user_approve_action($actions, $user_object) {
    unset($actions['view']);
    if('user-approve' === $_GET['action']) {
        $id = absint($_GET['user']);
        if($user_object->ID == $id) {
            update_user_meta($id, 'approved', true);
            send_approval_mail($user_object->user_email);
        }
    }
    $activeuser = wp_get_current_user();
    if(!(get_the_author_meta('approved', $user_object->ID) == 1) && in_array('xs_user', $user_object->roles) && (in_array('xs_admin', $activeuser->roles) || (in_array('administrator', $activeuser->roles) || in_array('xs_reviewer', $activeuser->roles)))) {
	   $actions['approve_user'] = "<a href='" . admin_url( "users.php?action=user-approve&amp;user=$user_object->ID") . "'>" . __( 'Approve User', 'cgc_ub' ) . "</a>";
    }
	return $actions;
}
add_filter('user_row_actions', 'user_approve_action', 10, 2);


function limit_users_function($query) {
    $userlistids = $GLOBALS['useridlist'];
    $activeuser = wp_get_current_user();
    if(in_array('xs_reviewer', $activeuser->roles)) {
        if(!empty($userlistids)) {
           $query->query_vars['include'] = $userlistids;
        }
    }
}
add_action('pre_get_users', 'limit_users_function');

//Limits user edit access for reviewer role to users in their location
function limit_reviewers_function() {
    $GLOBALS['useridlist'] = array();
    $userlistids = $GLOBALS['useridlist'];
    $activeuser = wp_get_current_user();
    if (isset($activeuser->roles) && is_array($activeuser->roles) && in_array('xs_reviewer', $activeuser->roles)) {
        $adminlocation = get_the_author_meta('location', $activeuser->ID);
        $user_list = new WP_User_Query(['role__in' => array('xs_user'), 'meta_key' => 'location', 'meta_compare' => 'EXISTS', 'include' => $userlistids]);
        foreach($user_list->get_results() as $user) {
            $location = get_the_author_meta('location', $user->data->ID);
            foreach($location as $l) {
                if (in_array($l, $adminlocation)) {
                    $userlistids[] = $user->ID;
                }
            }
        }
        if (empty($userlistids)) {
            $userlistids = array('xxxxxxxxxxxxxxx');
        }
    }
    $GLOBALS['useridlist'] = $userlistids;
}
add_action('admin_bar_init', 'limit_reviewers_function');
//Remove other roles from edit user menu so that reviewers cannot change user's role
function remove_higher_levels($all_roles) {
    $activeuser = wp_get_current_user();
    if(in_array('xs_reviewer', $activeuser->roles)) {
        foreach ( $all_roles as $name => $role ) {

            if ($name != 'xs_user') {
                unset($all_roles[$name]);
            }
        }
    }
    return $all_roles;
}
add_filter('editable_roles', 'remove_higher_levels');

//Add column to flag users that need to be approved
function modify_user_columns($column) {
    $column = array(
        'cb' => '<input type="checkbox" />',
        'approved' => '&emsp;',
        'username' => 'Username',
        'name' => 'Name',
        'email' => 'Email',
        'role' => 'Role'
    );
    return $column;
}
add_filter('manage_users_columns','modify_user_columns');
 
//add content to your new custom column
function modify_user_column_content($val,$column_name,$user_id) {
    $user = get_userdata($user_id);
    switch ($column_name) {
        case 'approved':
            if((get_the_author_meta('approved', $user_id) != 1) && in_array('xs_user', $user->roles)) {
                return '<span class="glyphicon glyphicon-exclamation-sign" title="Needs Approval"></span>';   
            }
            break;
        default:
    }
    return $return;
}
add_filter('manage_users_custom_column','modify_user_column_content',10, 10);

//Remove unnecessary dashboard links for custom users
function remove_menus() {
    $activeuser = wp_get_current_user();
    if (in_array('xs_reviewer', $activeuser->roles) || in_array('xs_user', $activeuser->roles)) {
        remove_menu_page('index.php');
    }
    if (in_array('xs_admin', $activeuser->roles)) {
        remove_menu_page('index.php');
        remove_menu_page('edit-tags.php?taxonomy=category');
        remove_submenu_page('edit.php', 'edit.php?taxonomy=category');
        remove_menu_page('edit.php');
    }
}
add_action('admin_menu', 'remove_menus');

//Direct custom users to survey on back-end login and builds access data
function xs_login_redirect($redirect_to, $request, $user) {
	//is there a user to check?
	if (isset($user->roles) && is_array($user->roles)) {
		//check for admins
		if (in_array('xs_admin', $user->roles) || in_array('xs_reviewer', $user->roles)) {
			//redirect to survey list	
            return admin_url('admin.php?page=survey-main');
		} else if (in_array('xs_user', $user->roles)) {
            //redirect to home page
            show_admin_bar(false);
            return get_option('home');
		} else {
            //direct to normal page
			return $redirect_to;
        }
	} else {
		return $redirect_to;
	}
}
add_filter('login_redirect', 'xs_login_redirect', 10, 3);
//Hide admin bar for xs_user role
function xs_hide_admin_bar() {
    $activeuser = wp_get_current_user();
    if (in_array('xs_user', $activeuser->roles)) {
        show_admin_bar(false);
    }
}
add_action('set_current_user', 'xs_hide_admin_bar');
//Redirect wp-admin to survey page for custom users
function dashboard_redirect(){
    $activeuser = wp_get_current_user();
    // is there a user ?
    if(is_array($activeuser->roles)) {
        // check, whether user has the author role:
        if(in_array('xs_admin', $activeuser->roles) || in_array('xs_reviewer', $activeuser->roles)) {
             wp_redirect(admin_url('admin.php?page=survey-main'));
             exit;
        } else if(in_array('xs_user', $activeuser->roles)) {
            wp_redirect(get_option('home') . '/approved-surveys/');
            exit;
        }
    }
}
add_action('load-index.php','dashboard_redirect');

function registration_form($password, $email, $first_name, $last_name, $language, $location, $emp_id) { 
    //Format list of Locations
    $locationlist = '<datalist id="locations" title="Locations to choose from">' . "\n";
    $locations = get_terms('locations', array('hide_empty' => false));
    foreach($locations as $l){
        $locationlist .= '<option value="' . $l->name . '">' . "\n";
    }
    $locationlist .= '</datalist>';

    $output = '
    <div id="form-area">
    <h1>New User Registration</h1>
    <form id="registration" action="' . $_SERVER['REQUEST_URI'] . '" method="post">
    <div id="form-errors" class="text-danger bg-warning"></div>
    <div class="form-group">
    <label for="language">Language <strong>*</strong></label>
    <div id="google_translate_element"></div>
    </div>
        
    <div class="form-group">
    <label for="location">Location <strong>*</strong></label>
    <input list="locations" name="location" class="form-control input-lg" value="' . ( isset( $_POST['location']) ? $location : null ) . '" required>
    ' . $locationlist . ' 
    </div>
             
    <div class="form-group">
    <label for="email">Email <strong>*</strong></label>
    <input type="text" name="email" class="form-control input-lg" value="' . ( isset( $_POST['email']) ? $email : null ) . '">
    </div>
        
    <div class="form-group">
    <label for="password">Password <strong>*</strong></label>
    <input type="password" name="password"  class="form-control input-lg" value="' . ( isset( $_POST['password'] ) ? $password : null ) . '">
    </div>
     
    <div class="form-group">
    <label for="fname">First Name <strong>*</strong></label>
    <input type="text" name="fname"  class="form-control input-lg" value="' . ( isset( $_POST['fname']) ? $first_name : null ) . '">
    </div>
     
    <div class="form-group">
    <label for="lname">Last Name <strong>*</strong></label>
    <input type="text" name="lname"  class="form-control input-lg" value="' . ( isset( $_POST['lname']) ? $last_name : null ) . '">
    </div>
    
    <div class="form-group">
    <label for="empid">Employee ID <strong>*</strong></label>
    <input type="text" name="empid"  class="form-control input-lg" value="' . ( isset( $_POST['empid']) ? $emp_id : null ) . '">
    </div>
    <input id="register-submit" type="submit" name="submit_registration" class="form-control input-lg" value="Register"/>
    </form>
    </div>
    <a id="home-link" href="' . get_option('home') . '"></a>
    ';
    echo $output;
}
function process_registration_form() {
        $errors = array();
        if(!empty($_POST['language'])) {        
            $language = spam_scrubber($_POST['language']);
        } else {
            $language = 'en';
        }

        if(!empty($_POST['fname'])) {        
            $first_name = spam_scrubber($_POST['fname']);
        } else {
            $errors[] = 'Please enter your first name.';
        }

        if(!empty($_POST['lname'])) {        
            $last_name = spam_scrubber($_POST['lname']);
        } else {
            $errors[] = 'Please enter your last name.';
        }

        if(!empty($_POST['email'])) {        
            $email = spam_scrubber($_POST['email']);
        } else {
            $errors[] = 'Please enter your email address.';
        }
        
        if(!empty($_POST['password'])) {        
            $password = spam_scrubber($_POST['password']);
        } else {
            $errors[] = 'Please enter a password.';
        }
        
        if(!empty($_POST['location'])) {        
            $location = spam_scrubber($_POST['location']);
        } else {
            $errors[] = 'Please choose your location.';
        }
        
        if(!empty($_POST['empid'])) {        
            $emp_id = spam_scrubber($_POST['empid']);
        } else {
            $errors[] = 'Please enter your employee ID.';
        }
        
        if(empty($errors)){
            // sanitize user form input
            $username   =   sanitize_user( $_POST['email'] );
            $password   =   esc_attr( $_POST['password'] );
            $email      =   sanitize_email( $_POST['email'] );
            $first_name =   sanitize_text_field( $_POST['fname'] );
            $last_name  =   sanitize_text_field( $_POST['lname'] );
            $language =   sanitize_text_field( $_POST['language'] );
            $location =   sanitize_text_field( $_POST['location'] );
            $emp_id =   sanitize_text_field( $_POST['empid'] );
            // call @function complete_registration to create the user
            // only when no WP_error is found
            $userdata = array(
            'user_login' => $username,
            'user_email' => $email,
            'user_pass' => $password,
            'user_url' => '',
            'first_name' => $first_name,
            'last_name' => $last_name,
            'nickname' => '',
            'description' => '',
            'role' => 'xs_user'
            );
            $user = wp_insert_user($userdata);
            if ($user->errors) {
                foreach ($user->errors as $error) {
                    foreach($error as $message) {
                        echo '<p>'.$message.'</p>';
                    
                    }
                }
            } else {
                send_registration_mail($email, $first_name, $last_name, $location);
                update_user_meta($user, 'location', [0=>$location]);
                update_user_meta($user, 'language', $language);
                update_user_meta($user, 'employee_id', $emp_id);
                echo 'Success';
            }
            die();
        } else {
            foreach ($errors as $error) {
                echo '<p>'.$error.'</p>';
            }
            die();
        }

}
    
function xylem_register_user() {
    ob_start();
    registration_form(
        $password,
        $email,
        $first_name,
        $last_name,
        $language,
        $location,
        $emp_id
        );
    return ob_get_clean();
}

add_shortcode( 'xylem-registration', 'xylem_register_user' );
add_action('wp_ajax_process_registration_form', 'process_registration_form');
add_action('wp_ajax_nopriv_process_registration_form', 'process_registration_form');

//Email sent on successful registration
function send_registration_mail($email, $first_name, $last_name, $location){
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
        $subject = 'New Point Kaizen User Registration';    
    }
    
    $body = '';
    $body .= '<p><strong>Name:</strong> ' . $first_name . " " . $last_name . "</p>\r\n";
    $body .= '<p><strong>Email address:</strong> ' . $email . "</p>\r\n";
    $body .= '<p><strong>Location:</strong> ' . $location . "</p>\r\n";
    $body .= '<p>Please <a href="' . wp_login_url() . '">log in</a> to approve or deny this registration.</p>';
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail( $to, $subject, $body, $headers);
}

//Email sent on user approval
function send_approval_mail($email){
    $to = $email;
    
    if ($options['xs_email_subject']) {
        $subject = $options['xs_email_subject'];
    } else {
        $subject = 'Point Kaizen User Registration Approved';    
    }
    
    $body = '';
    $body .= "<p>Your registration at Point Kaizen has been approved.</p>\r\n";
    $body .= '<p>You may now <a href="' . wp_login_url() . '">log in</a> to view approved surveys.</p>';
    
    $headers = array('Content-Type: text/html; charset=UTF-8');
    wp_mail( $to, $subject, $body, $headers);
}