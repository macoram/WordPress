<?php 

//Settings Page Functions

//Adds Settings Page
function xs_add_admin_menu() { 
	add_menu_page('Survey Form Settings', 'Survey Form Settings', 'manage_categories', 'survey-form-settings', 'xs_options_page', 'dashicons-admin-generic', 2);
}
add_action('admin_menu', 'xs_add_admin_menu');
add_action('admin_init', 'xs_settings_init');

//Fills Settings Page Content and adds settings to options table
function xs_settings_init() {
	add_settings_section(
		'xs_options_section', 
		__( 'Survey Form Settings', 'wordpress' ), 
		'xs_settings_section_callback', 
		'xsOptions'
	);

    add_settings_field( 
		'xs_welcome_message', 
		__( 'Welcome message at the top of the form', 'wordpress' ), 
		'xs_welcome_message_render', 
		'xsOptions', 
		'xs_options_section'
	);
	add_settings_field( 
		'xs_name_label', 
		__( 'Label for Name field', 'wordpress' ), 
		'xs_name_label_render', 
		'xsOptions', 
		'xs_options_section' 
	);
	add_settings_field( 
		'xs_employee_photo_label', 
		__( 'Label for Employee Photo', 'wordpress' ), 
		'xs_employee_photo_label_render', 
		'xsOptions', 
		'xs_options_section' 
	);    
    add_settings_field( 
		'xs_department_label', 
		__( 'Label for Department', 'wordpress' ), 
		'xs_department_label_render', 
		'xsOptions', 
		'xs_options_section' 
	);
	add_settings_field( 
		'xs_title_label', 
		__( 'Label for Survey Title', 'wordpress' ), 
		'xs_title_label_render', 
		'xsOptions', 
		'xs_options_section' 
	);
    add_settings_field( 
		'xs_improvement_category_label', 
		__( 'Label for Improvement Category', 'wordpress' ), 
		'xs_improvement_category_label_render', 
		'xsOptions', 
		'xs_options_section' 
	);
    add_settings_field( 
		'xs_before_action_label', 
		__( 'Label for Before Action', 'wordpress' ), 
		'xs_before_action_label_render', 
		'xsOptions', 
		'xs_options_section' 
	);
    add_settings_field( 
		'xs_before_photo_label', 
		__( 'Label for Before Photo', 'wordpress' ), 
		'xs_before_photo_label_render', 
		'xsOptions', 
		'xs_options_section' 
	);
    add_settings_field( 
		'xs_action_label', 
		__( 'Label for Action Taken', 'wordpress' ), 
		'xs_action_label_render', 
		'xsOptions', 
		'xs_options_section' 
	);
    add_settings_field( 
		'xs_after_action_label', 
		__( 'Label for After Action', 'wordpress' ), 
		'xs_after_action_label_render', 
		'xsOptions', 
		'xs_options_section' 
	);
    add_settings_field( 
		'xs_after_photo_label', 
		__( 'Label for After Photo', 'wordpress' ), 
		'xs_after_photo_label_render', 
		'xsOptions', 
		'xs_options_section' 
	);
    add_settings_field( 
		'xs_date_label', 
		__( 'Label for Date', 'wordpress' ), 
		'xs_date_label_render', 
		'xsOptions', 
		'xs_options_section' 
	);
    register_setting('xsOptions', 'xsOptions');
}

//Functions to display settings form fields
function xs_welcome_message_render() {
	$options = get_option('xsOptions');
	?>
    <input type='text' name='xsOptions[xs_welcome_message]' size='100' value='<?php echo $options['xs_welcome_message'] ? $options['xs_welcome_message'] : 'Great work should be shared… please share your great work below! (note that all fields are required)' ?>'>
	<?php
}

function xs_name_label_render() {
	$options = get_option('xsOptions');
	?>
    <input type='text' name='xsOptions[xs_name_label]' size='100' value='<?php echo $options['xs_name_label'] ? $options['xs_name_label'] : 'Name(s)'; ?>'>
	<?php
}

function xs_employee_photo_label_render() {
	$options = get_option('xsOptions');
	?>
    <input type='text' name='xsOptions[xs_employee_photo_label]' size='100' value='<?php echo $options['xs_employee_photo_label'] ? $options['xs_employee_photo_label'] : 'Employee Photo'; ?>'>
	<?php
}

function xs_department_label_render() {
	$options = get_option('xsOptions');
	?>
    <input type='text' name='xsOptions[xs_department_label]' size='100' value='<?php echo $options['xs_department_label'] ? $options['xs_department_label'] : 'Department' ?>'>
	<?php
}

function xs_title_label_render() {
	$options = get_option('xsOptions');
	?>
    <input type='text' name='xsOptions[xs_title_label]' size='100' value='<?php echo $options['xs_title_label'] ? $options['xs_title_label'] : 'Title'; ?>'>
	<?php
}
function xs_improvement_category_label_render() {
	$options = get_option('xsOptions');
	?>
    <input type='text' name='xsOptions[xs_improvement_category_label]' size='100' value='<?php echo $options['xs_improvement_category_label'] ? $options['xs_improvement_category_label'] : 'Improvement Category'; ?>'>
	<?php
}
function xs_before_action_label_render() {
	$options = get_option('xsOptions');
	?>
    <input type='text' name='xsOptions[xs_before_action_label]' size='100' value='<?php echo $options['xs_before_action_label'] ? $options['xs_before_action_label'] : 'Before Action Taken'; ?>'>
	<?php
}
function xs_before_photo_label_render() {
	$options = get_option('xsOptions');
	?>
    <input type='text' name='xsOptions[xs_before_photo_label]' size='100' value='<?php echo $options['xs_before_photo_label'] ? $options['xs_before_photo_label'] : 'Picture Before Action Taken'; ?>'>
	<?php
}
function xs_action_label_render() {
	$options = get_option('xsOptions');
	?>
    <input type='text' name='xsOptions[xs_action_label]' size='100' value='<?php echo $options['xs_action_label'] ? $options['xs_action_label'] : 'Action Taken'; ?>'>
	<?php
}
function xs_after_action_label_render() {
	$options = get_option('xsOptions');
	?>
    <input type='text' name='xsOptions[xs_after_action_label]' size='100' value='<?php echo $options['xs_after_action_label'] ? $options['xs_after_action_label'] : 'After Action Taken'; ?>'>
	<?php
}
function xs_after_photo_label_render() {
	$options = get_option('xsOptions');
	?>
    <input type='text' name='xsOptions[xs_after_photo_label]' size='100' value='<?php echo $options['xs_after_photo_label'] ? $options['xs_after_photo_label'] : 'Picture After Action Taken'; ?>'>
	<?php
}
function xs_date_label_render() {
	$options = get_option('xsOptions');
	?>
    <input type='text' name='xsOptions[xs_date_label]' size='100' value='<?php echo $options['xs_date_label'] ? $options['xs_date_label'] : 'Date'; ?>'>
	<?php
}

function xs_settings_section_callback() {
	echo __( 'Override default survey form settings.', 'wordpress' );
}

function xs_options_page() {
	?>
	<form action='options.php' method='post'>
		<?php
		settings_fields( 'xsOptions' );
		do_settings_sections( 'xsOptions' );
		submit_button();
		?>
	</form>
	<?php
}
