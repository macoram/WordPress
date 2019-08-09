<?php

//Include MPDF
ini_set("memory_limit","200M");
include("mpdf60/mpdf.php");
//define( 'SHORTINIT', true );
require_once( '../../../wp-load.php' );

//Set new PDF document (charset, size)
$mpdf = new mPDF('utf-8', 'A4-L'); 

//Import theme CSS
$css = file_get_contents('../../themes/xylem/style.css');

global $wpdb;
$survey_ids = $_GET['survey_id'];
$id_list = explode(',', $survey_ids);
$pages = array();
foreach($id_list as $survey_id) {
    $table_name = $wpdb->prefix . 'surveys_table';
    $survey_obj = $wpdb->get_results('SELECT * FROM ' . $table_name . ' WHERE id=' . $survey_id, ARRAY_A);
    $survey = $survey_obj[0];
    $improvementcategory = '';
    $improvements = get_terms('improvements', array('hide_empty' => false, 'orderby' => 'ID'));
    $improvementslist = array();

    $checked_list = explode(', ', $survey['improvement_category']);
    foreach($improvements as $i){
        if (in_array($i->name, $checked_list)) {
            $checked = '&#9745;';
        } else {
            $checked = '&#9744;';
        }
        $improvementcategory .= '<div>' . "\n";
        $improvementcategory .= $checked . '&nbsp;' . $i->name . "\n";
        $improvementcategory .= '</div>' . "\n";
    }

    //Format translated fields 
    if (!empty($survey['survey_title_trans'])) {
        $survey_title = $survey['survey_title'] . '/' . $survey['survey_title_trans'];
    } else {
        $survey_title = $survey['survey_title'];
    }
    if (!empty($survey['before_action_trans'])) {
        $before_action = stripslashes(html_entity_decode($survey['before_action'], ENT_QUOTES | ENT_HTML401)) . '<br/><br/>' . $survey['before_action_trans'];
    } else {
        $before_action = stripslashes(html_entity_decode($survey['before_action'], ENT_QUOTES | ENT_HTML401));
    }
    if (!empty($survey['action_trans'])) {
        $action_taken = stripslashes(html_entity_decode($survey['action'], ENT_QUOTES | ENT_HTML401)) . '<br/><br/>' . $survey['action_trans'];
    } else {
        $action_taken = stripslashes(html_entity_decode($survey['action'], ENT_QUOTES | ENT_HTML401));
    }
    if (!empty($survey['after_action_trans'])) {
        $after_action = stripslashes(html_entity_decode($survey['after_action'], ENT_QUOTES | ENT_HTML401)) . '<br/><br/>' . $survey['after_action_trans'];
    } else {
        $after_action = stripslashes(html_entity_decode($survey['after_action'], ENT_QUOTES | ENT_HTML401));
    }
    //==============================================================

    $html = '<style>' . $css . '</style>' . "/n";
    $html .= '<body class="pdf" style="margin-bottom: 60px;">' . "\n";
    $html .= '<table class="table" height: 1000px;>' . "\n";
    //dummy row to simulate 12 column layout
    $html .= '<tr><td style="width: 8.3333%;"></td><td style="width: 8.3333%;"></td><td style="width: 8.3333%;"></td><td style="width: 8.3333%;"></td><td style="width: 8.3333%;"></td><td style="width: 8.3333%;"></td><td style="width: 8.3333%;"></td><td style="width: 8.3333%;"></td><td style="width: 8.3333%;"></td><td style="width: 8.3333%;"></td><td style="width: 8.3333%;"></td><td style="width: 8.3333%;"></td></tr>' . "\n";
    //Improvement Category
    $html .= '<tr>' . "\n";
    $html .= '<td colspan="4" style="color: black;">' . "\n"; 
    $html .= $improvementcategory . "\n";
    $html .= '</td>' . "\n";
    //Location
    $html .= '<td colspan="4" align="center">' . "\n";
    $html .= '<span style="font-size: 32px;">Point Kaizen</span><br/>' . "\n";
    $html .= '<span style="font-size: 18px;">from</span><br/>' . "\n";
    $html .= '<span style="font-size: 24px; font-style: italic;">' . $survey['location'] . '</span>' . "\n";
    $html .= '</td>' . "\n";
    //Employee Photo
    $html .= '<td id="emp-pic" colspan="4" align="right">' . "\n";
    $html .= '<img src="' . plugins_url() . '/Xylem-Survey/uploads/'. $survey['employee_photo'] . '" style="max-height: 110px; width: auto; max-width: 330px;">' . "\n";
    $html .= '</td>' . "\n";
    $html .= '</tr>' . "\n";
    //Survey Title
    $html .= '<tr id="title" style="border: 1px solid #000;">' . "\n";
    $html .= '<td colspan="12" align="center" style="font-size: 24px; color: black; background: #0185AE; padding: 10px 0px;">' . $survey_title . '</td>' . "\n";
    $html .= '</tr>' . "\n";
    //Department
    $html .= '<tr style="border: 1px solid #000; border-top: none;">' . "\n";
    $html .= '<td colspan="4" align="center" style="font-size: 18px; background: #0185AE; color: white; padding: 10px 0px;">' . "\n";
    $html .= 'Department: ' . $survey['department'] . "\n";
    $html .= '</td>' . "\n";
    //Name(s)
    $html .= '<td colspan="4" align="center" style="font-size: 18px; background: #0185AE; color: white; padding: 10px 0px; border-left: 1px solid #000; border-right: 1px solid #000;">' . "\n";
    $html .= 'Name(s): ' . $survey['name'] . "\n";
    $html .= '</td>' . "\n";
    //Date
    $html .= '<td colspan="4" align="center" style="font-size: 18px; background: #0185AE; color: white; padding: 10px 0px;">' . "\n";
    $html .= 'Date: ' . $survey['action_date'] . "\n";
    $html .= '</td>' . "\n";
    $html .= '</tr>' . "\n";
    //Action Headers
    $html .= '<tr style="border: 1px solid #000;  border-top: none;">' . "\n";
    $html .= '<td colspan="4" align="center" style="font-size: 18px; background: #0185AE; color: white; padding: 10px 0px;">Before</td>' . "\n";
    $html .= '<td colspan="4" align="center" style="font-size: 18px; background: #0185AE; color: white; padding: 10px 0px; border-left: 1px solid #000; border-right: 1px solid #000;">Action</td>' . "\n";
    $html .= '<td colspan="4" align="center" style="font-size: 18px; background: #0185AE; color: white; padding: 10px 0px;">After</td>' . "\n";
    $html .= '</tr>' . "\n";
    //Before Action
    $html .= '<tr style="border: 1px solid #000;  border-top: none;">' . "\n";
    $html .= '<td colspan="4" style="font-size: 14px; color: black; padding: 10px; vertical-align: top; height: 200px;">' . "\n";
    $html .= '<div style="width: 100%; height: 100%; overflow: hidden;">' . $before_action . '</div>' . "\n";
    $html .= '</td>' . "\n";
    //Action Taken
    $html .= '<td colspan="4" style="font-size: 14px; color: black; padding: 10px; border-left: 1px solid #000; border-right: 1px solid #000; vertical-align: top; height: 200px;">' . "\n";
    $html .= '<div style="width: 100%; height: 100%; overflow: hidden;">' . $action_taken . '</div>' . "\n";
    $html .= '</td>' . "\n";
    //After Action
    $html .= '<td colspan="4" style="font-size: 14px; color: black; padding: 10px; vertical-align: top; height: 200px;">' . "\n";
    $html .= '<div style="width: 100%; height: 100%; overflow: hidden;">' . $after_action . '</div>' . "\n";
    $html .= '</td>' . "\n";
    $html .= '</tr>' . "\n";
    //Before Photo
    $html .= '<tr style="border: 1px solid #000;  border-top: none;">' . "\n";
    $html .= '<td colspan="6" align="center" style="font-size: 14px; color: black; padding: 10px;">' . "\n";
    $html .= '<img src="uploads/' . $survey['before_picture'] . '" style="max-height: 200px; width: auto; max-width: 500px">' . "\n";
    $html .= '</td>' . "\n";
    //After Photo
    $html .= '<td colspan="6" align="center" style="font-size: 14px; color: black; padding: 10px; border-left: 1px solid #000;">' . "\n";
    $html .= '<img src="uploads/' . $survey['after_picture'] . '" style="max-height: 200px; width: auto; max-width: 500px;">' . "\n";
    $html .= '</td>' . "\n";
    $html .= '</tr>' . "\n";
    $html .= '</table><br style="page-break-after: always;"/>' . "\n";
    $html .= '<div style="width: 100%; position: fixed; bottom: 0px; margin-bottom: -20px; left: -20px; margin-right: -40px;"><img src="wave.png" style="opacity: 0.3; width: 100%; height: 100px;"></div>' . "\n";
    $html .= '<div style="width: 100%; position: fixed; bottom: 0px; right: 0px; z-index:100;"><img src="iloveci.png" style="height: 60px; width: auto;"></div>' . "\n";
    $html .= '<div style="width: 100%; position: fixed; bottom: 0px; right: 0px; z-index:100;"><img src="xylem.png" style="height: 30px; width: auto; float: right;"></div>' . "\n";
    $html .= '</body>' . "\n";
    $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
    $pages[] = $html;
}


//==============================================================
if ($_REQUEST['html']) { echo $html; exit; }
if ($_REQUEST['source']) { 
	$file = __FILE__;
	header("Content-Type: text/plain");
	header("Content-Length: ". filesize($file));
	header("Content-Disposition: attachment; filename='".$file."'");
	readfile($file);
	exit; 
}

//==============================================================
foreach ($pages as $index => $page){
    if ($index > 0) {
        $mpdf->AddPage();
    }
    $mpdf->WriteHTML($page);
}
//var_dump($pages);

// OUTPUT
$mpdf->Output(); exit;


//==============================================================
//==============================================================
//==============================================================
//==============================================================


?>
