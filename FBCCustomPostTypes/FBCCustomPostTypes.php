<?php 
/**
 * Plugin Name: FBCCustomPostTypes
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Plugin for FBC custom post types.
 * Version: 1.0
 * Author: Melissa Coram
 * Author URI: http://URI_Of_The_Plugin_Author
 * License: GPL2
 */
 
 function create_posttypes() {
	$labels1 = array(
				'name' => __( 'Teachings' ),
                		'singular_name' => __( 'Teaching' ),
				'all_items' => __( 'All Teachings' ),
				'view_item' => __( 'View Teaching' ),
				'add_new_item' => __( 'Add New Teaching' ),
				'edit_item' => __( 'Edit Teaching' ),
				'update_item' => __( 'Update Teaching' ),
				'search_items' => __( 'Search Teachings' ),
				'not_found' => __( 'Not Found' ),
				'not_found_in_trash' => __( 'Not Found in Trash' ),
			);
	$args1 = array(
				'label' => 'Teachings',
				'description' => 'Directory of Teachings',
				'labels' => $labels1,
				'public' => true,
				'query_var' => true,
				'has_archive' => false,
				'rewrite' => array('slug'=> 'teachings', 'with_front' => true),
				'hierarchical' => false,
                'taxonomies' => array('post_tag'),
				'show_ui' => true,
				'show_in_menu' => true,
				'show_in_rest' => true,
				'show_in_nav_menus' => true,
				'show_in_admin_bar' => true,
				'menu_position' => 5,
				'can_export' => true,
				'exclude_from_search' => false,
				'publicly_queryable' => true,
				'capability_type' => 'post',
				'supports' => array('title','thumbnail', 'revisions', 'excerpt', 'editor')
			);
	register_post_type( 'teachings', $args1 );
     
    $labels2 = array(
				'name' => __( 'Books' ),
                		'singular_name' => __( 'Book' ),
				'all_items' => __( 'All Books' ),
				'view_item' => __( 'View Book' ),
				'add_new_item' => __( 'Add New Book' ),
				'edit_item' => __( 'Edit Book' ),
				'update_item' => __( 'Update Book' ),
				'search_items' => __( 'Search Books' ),
				'not_found' => __( 'Not Found' ),
				'not_found_in_trash' => __( 'Not Found in Trash' ),
			);
	$args2 = array(
				'label' => 'Books',
				'description' => 'Directory of Books',
				'labels' => $labels2,
				'public' => true,
				'query_var' => true,
				'has_archive' => false,
				'rewrite' => array('slug'=> 'books', 'with_front' => true),
				'hierarchical' => false,
                'taxonomies' => array('post_tag'),
				'show_ui' => true,
				'show_in_menu' => true,
				'show_in_rest' => true,
				'show_in_nav_menus' => true,
				'show_in_admin_bar' => true,
				'menu_position' => 5,
				'can_export' => true,
				'exclude_from_search' => false,
				'publicly_queryable' => true,
				'capability_type' => 'post',
				'supports' => array('title','thumbnail', 'revisions', 'excerpt', 'editor')
			);
	register_post_type( 'books', $args2 );
     
    $labels3 = array(
				'name' => __( 'Study Guides' ),
                		'singular_name' => __( 'Study Guide' ),
				'all_items' => __( 'All Study Guides' ),
				'view_item' => __( 'View Study Guide' ),
				'add_new_item' => __( 'Add New Study Guide' ),
				'edit_item' => __( 'Edit Study Guide' ),
				'update_item' => __( 'Update Study Guide' ),
				'search_items' => __( 'Search Study Guides' ),
				'not_found' => __( 'Not Found' ),
				'not_found_in_trash' => __( 'Not Found in Trash' ),
			);
	$args3 = array(
				'label' => 'Study Guide',
				'description' => 'Directory of Study Guides',
				'labels' => $labels3,
				'public' => true,
				'query_var' => true,
				'has_archive' => false,
				'rewrite' => array('slug'=> 'guides', 'with_front' => true),
				'hierarchical' => false,
                'taxonomies' => array('post_tag'),
				'show_ui' => true,
				'show_in_menu' => true,
				'show_in_rest' => true,
				'show_in_nav_menus' => true,
				'show_in_admin_bar' => true,
				'menu_position' => 5,
				'can_export' => true,
				'exclude_from_search' => false,
				'publicly_queryable' => true,
				'capability_type' => 'post',
				'supports' => array('title','thumbnail', 'revisions', 'excerpt', 'editor')
			);
	register_post_type( 'study_guides', $args3 );
    
    $labels4 = array(
				'name' => __( 'Donation Pages' ),
                		'singular_name' => __( 'Donation Page' ),
				'all_items' => __( 'All Donation Pages' ),
				'view_item' => __( 'View Donation Page' ),
				'add_new_item' => __( 'Add New Donation Page' ),
				'edit_item' => __( 'Edit Donation Page' ),
				'update_item' => __( 'Update Donation Page' ),
				'search_items' => __( 'Search Donation Pages' ),
				'not_found' => __( 'Not Found' ),
				'not_found_in_trash' => __( 'Not Found in Trash' ),
			);
	$args4 = array(
				'label' => 'Donation Page',
				'description' => 'Directory of Donation Pages',
				'labels' => $labels4,
				'public' => true,
				'query_var' => true,
				'has_archive' => false,
				//'rewrite' => array('slug'=> 'donate', 'with_front' => true),
				'rewrite' => false,
				'hierarchical' => false,
                'taxonomies' => array('post_tag'),
				'show_ui' => true,
				'show_in_menu' => true,
				'show_in_rest' => true,
				'show_in_nav_menus' => true,
				'show_in_admin_bar' => true,
				'menu_position' => 5,
				'can_export' => true,
				'exclude_from_search' => true,
				'publicly_queryable' => true,
				'capability_type' => 'post',
				'supports' => array('title','thumbnail', 'revisions', 'excerpt', 'editor')
			);
	register_post_type( 'donation_page', $args4 );
    
    //custom rewrites for Donation pages
    add_rewrite_rule( '[^/]+/attachment/([^/]+)/?$', 'index.php?attachment=$matches[1]', 'bottom');
    add_rewrite_rule( '[^/]+/attachment/([^/]+)/trackback/?$', 'index.php?attachment=$matches[1]&tb=1', 'bottom');
    add_rewrite_rule( '[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$', 'index.php?attachment=$matches[1]&feed=$matches[2]', 'bottom');
    add_rewrite_rule( '[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$', 'index.php?attachment=$matches[1]&feed=$matches[2]', 'bottom');
    add_rewrite_rule( '[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$', 'index.php?attachment=$matches[1]&cpage=$matches[2]', 'bottom');
    add_rewrite_rule( '[^/]+/attachment/([^/]+)/embed/?$', 'index.php?attachment=$matches[1]&embed=true', 'bottom');
    add_rewrite_rule( '([^/]+)/embed/?$', 'index.php?donation_page=$matches[1]&embed=true', 'bottom');
    add_rewrite_rule( '([^/]+)/trackback/?$', 'index.php?donation_page=$matches[1]&tb=1', 'bottom');
    add_rewrite_rule( '([^/]+)/page/?([0-9]{1,})/?$', 'index.php?donation_page=$matches[1]&paged=$matches[2]', 'bottom');
    add_rewrite_rule( '([^/]+)/comment-page-([0-9]{1,})/?$', 'index.php?donation_page=$matches[1]&cpage=$matches[2]', 'bottom');
    add_rewrite_rule( '([^/]+)(?:/([0-9]+))?/?$', 'index.php?donation_page=$matches[1]', 'bottom');
    add_rewrite_rule( '[^/]+/([^/]+)/?$', 'index.php?attachment=$matches[1]', 'bottom');
    add_rewrite_rule( '[^/]+/([^/]+)/trackback/?$', 'index.php?attachment=$matches[1]&tb=1', 'bottom');
    add_rewrite_rule( '[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$', 'index.php?attachment=$matches[1]&feed=$matches[2]', 'bottom');
    add_rewrite_rule( '[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$', 'index.php?attachment=$matches[1]&feed=$matches[2]', 'bottom');
    add_rewrite_rule( '[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$', 'index.php?attachment=$matches[1]&cpage=$matches[2]', 'bottom');
    add_rewrite_rule( '[^/]+/([^/]+)/embed/?$', 'index.php?attachment=$matches[1]&embed=true', 'bottom');
}

// hook into the 'init' action
add_action( 'init', 'create_posttypes', 0 );

function custom_post_type_permalinks( $post_link, $post, $leavename ) {
    if ( isset( $post->post_type ) && 'donation_page' == $post->post_type ) {
        $post_link = home_url( $post->post_name );
    }

    return $post_link;
}

add_filter( 'post_type_link', 'custom_post_type_permalinks', 10, 3 );

function prevent_slug_duplicates( $slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug ) {
    $check_post_types = array(
        'post',
        'page',
        'donation_page'
    );

    if ( ! in_array( $post_type, $check_post_types ) ) {
        return $slug;
    }

    if ( 'donation_page' == $post_type ) {
        // Saving a custom_post_type post, check for duplicates in POST or PAGE post types
        $post_match = get_page_by_path( $slug, 'OBJECT', 'post' );
        $page_match = get_page_by_path( $slug, 'OBJECT', 'page' );

        if ( $post_match || $page_match ) {
            $slug .= '-duplicate';
        }
    } else {
        // Saving a POST or PAGE, check for duplicates in custom_post_type post type
        $custom_post_type_match = get_page_by_path( $slug, 'OBJECT', 'donation_page' );

        if ( $custom_post_type_match ) {
            $slug .= '-duplicate';
        }
    }

    return $slug;
}
add_filter( 'wp_unique_post_slug', 'prevent_slug_duplicates', 10, 6 );

// add custom post type to main query 
add_action( 'pre_get_posts', 'add_custom_postTypes_to_query' );

function add_custom_postTypes_to_query( $query ) {
  if ( is_home() && $query->is_main_query() )
    $query->set( 'post_type', array( 'post', 'page', 'teachings', 'books', 'study_guides' ) );
  return $query;
}

//add shortcodes to display book fields
function show_book_pdf($atts) {
    $pdf = get_field('book_pdf');
        
    $html = do_shortcode('[pdf-embedder url="' . $pdf['url'] . '"]');
    echo $html;
}
add_shortcode('show_book_pdf', 'show_book_pdf');

function show_book_image($atts) {
    $image = get_field('book_image');
    if(get_field('image_border')){
        $border = ' book-border';
    } else {
        $border = '';
    }
    
    $html = '<img src="' . $image['sizes']['medium_large'] . '" alt="' . $image['alt'] . '" class="book-img' . $border . '">';
    echo $html;
}
add_shortcode('show_book_image', 'show_book_image');

function show_book_description($atts) {
    $description = '<p class="description">' . get_field('book_description') . '</p>';
    $pdf = get_field('book_pdf');
    $book_link = '<p>This page contains full, unabridged work authored by Dr. Lester Sumrall. Please feel free to use the reader provided, or you may directly download the publication – both versions offer access to the full text. We hope you enjoy reading the works of Dr. Sumrall. To purchase a hard copy of this teaching visit <a href="https://www.leseapublishing.com" target="_blank">www.leseapublishing.com</a> to order.</p>';
    if($pdf) {
        $book_link .= '<a class="book-download" href="' . $pdf['url'] . '" target="_blank">Download Book PDF</a>';
    }
    echo $description . $book_link;
}
add_shortcode('show_book_description', 'show_book_description');

function show_book_buttons($atts) {
    $a = shortcode_atts(array('link'=>true),$atts);
    $video = get_field('video_teaching');
    $study_guide = get_field('study_guide');
    $html = '<div class="study-buttons">';

    if(!empty($video)) {
        $html .= '<a href="' . $video . '" class="teachings-buttons">View Related Teaching</a>';
    } else {
        $html .= '<span class="teachings-buttons not-visible">No Related Teaching</span>'; 
    }
    if(!empty($study_guide)) {
        $html .= '<a href="' . $study_guide . '" class="teachings-buttons">View Study Guide</a>';
    } else {
        $html .= '<span class="teachings-buttons not-visible">No Study Guide</span>';
    }
    
    $html .= '</div>';
    echo $html;
}
add_shortcode('show_book_buttons', 'show_book_buttons');

function show_featured_books($atts) {
    $books = get_field('featured_books');
    $html = '<div id="books-carousel" class="owl-carousel blog-carousel-shortcode dt-owl-carousel-call blog-carousel-shortcode-id-9729fd19b9fb966f9a6518a876cef72b classic-layout-list content-bg-on scale-img enable-bg-rollover dt-arrow-border-on dt-arrow-hover-border-on bullets-small-dot-stroke reposition-arrows disable-arrows-hover-bg owl-loaded owl-drag" data-scroll-mode="1" data-col-num="1" data-wide-col-num="1" data-laptop-col="1" data-h-tablet-columns-num="1" data-v-tablet-columns-num="1" data-phone-columns-num="1" data-auto-height="true" data-col-gap="30" data-speed="600" data-autoplay="false" data-autoplay_speed="6000" data-arrows="true" data-bullet="false" data-next-icon="icon-ar-018-r" data-prev-icon="icon-ar-018-l">';
    foreach($books as $book) {
        $id = $book->ID;
        $image = get_field('book_image', $id);
        $title = $book->post_title;

        $html .= '<div class="item">';
        $html .= '<a href="' . get_the_permalink($id) . '"><img src="' . $image['sizes']['medium'] . '" alt="' . $image['alt'] . '" class="book-img"></a>';
        $html .= '<h5>' . $title . '</h5>';
        $html .= '</div>';
    }
    $html .= '</div>';
    echo $html;
}
add_shortcode('show_featured_books', 'show_featured_books');

function show_books_list($atts) {
    $books = new WP_Query(array('post_type'=>'books', 'posts_per_page'=>-1));
    $html = '<div id="books-list">';
    while ($books->have_posts()) : $books->the_post();
        $image = get_field('book_image');
        $description = get_field('book_description');
        $study_guide = get_field('study_guide');
        $video_teaching = get_field('video_teaching');
        if(empty($study_guide)) {
            $study_class = "not-visible";
        } else {
            $study_class = "";
        }
        if(empty($video_teaching)) {
            $video_class = "not-visible";
        } else {
            $video_class = "";
        }
        $html .= '<a href="' . get_the_permalink() . '">';
        $html .= '<div class="list-item">';
        $html .= '<div class="item-img"><img src="' . $image['sizes']['medium'] . '" alt="' . $image['alt'] . '" class="book-img"></div>';
        $html .= '<div class="item-body">';
        $html .= '<h3>' . get_the_title() . '</h3>';
        $html .= '<p>' . $description . '</p>';
        $html .= '</div>';
        $html .= '<div class="item-buttons">';
        $html .= '<span class="teachings-buttons ' . $video_class . '">Video Teaching Available</span>';
        $html .= '<span class="teachings-buttons ' . $study_class . '">Study Guide Available</span>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</a>';
    endwhile;
    echo $html;
    wp_reset_postdata();
}
add_shortcode('show_books_list', 'show_books_list');

//add shortcodes to display video fields
function show_playlist_description($atts) {
    $description = get_field('playlist_description');
    
    echo '<p class="description">' . $description . ' To purchase a hard copy of this teaching visit <a href="https://www.leseapublishing.com" target="_blank">www.leseapublishing.com</a> to order.</p>';
}
add_shortcode('show_playlist_description', 'show_playlist_description');

function show_teachings_study_guide($atts) {
    $a = shortcode_atts(array('link'=>true),$atts);
    
    $study_guide = get_field('teachings_study_guide');
    $related_book = get_field('related_book');
    $html = '<div class="study-buttons">';

    if(!empty($related_book)) {
        $html .= '<a href="' . $related_book . '" class="teachings-buttons">View Related Book</a>';
    } else {
        $html .= '<span class="teachings-buttons not-visible">No Related Book</span>'; 
    }
    if(!empty($study_guide)) {
        $html .= '<a href="' . $study_guide . '" class="teachings-buttons">View Study Guide</a>';  
    } else {
        $html .= '<span class="teachings-buttons not-visible">No Study Guide</span>';
    }
    
    $html .= '</div>';
    echo $html;
}
add_shortcode('show_teachings_study_guide', 'show_teachings_study_guide');

function show_video_playlist($atts) {
    $url = get_field('playlist_url');
    
    if(get_field('single_video')) {
        $html = do_shortcode('[embedyt]' . $url . '&iv_load_policy=3&modestbranding=0[/embedyt]'); 
    } else {
        $html = do_shortcode('[embedyt]' . $url . '&layout=gallery[/embedyt]');
    }
    
    echo $html;
}
add_shortcode('show_video_playlist', 'show_video_playlist');

function show_featured_teachings($atts) {
    $teachings = get_field('featured_teachings');
    $html = '<div id="teachings-carousel" class="owl-carousel blog-carousel-shortcode dt-owl-carousel-call blog-carousel-shortcode-id-9729fd19b9fb966f9a6518a876cef72b classic-layout-list content-bg-on scale-img enable-bg-rollover dt-arrow-border-on dt-arrow-hover-border-on bullets-small-dot-stroke reposition-arrows disable-arrows-hover-bg owl-loaded owl-drag" data-scroll-mode="1" data-col-num="3" data-wide-col-num="3" data-laptop-col="3" data-h-tablet-columns-num="1" data-v-tablet-columns-num="1" data-phone-columns-num="1" data-auto-height="true" data-col-gap="30" data-speed="600" data-autoplay="false" data-autoplay_speed="6000" data-arrows="true" data-bullet="false" data-next-icon="icon-ar-018-r" data-prev-icon="icon-ar-018-l">';
    foreach($teachings as $teaching) {
        $id = $teaching->ID;
        $image = get_field('video_placeholder_image', $id);
        $title = $teaching->post_title;
        
        $html .= '<div class="item">';
        $html .= '<a href="' . get_the_permalink($id) . '"><img src="' . $image['sizes']['large'] . '" alt="' . $image['alt'] . '" class="teaching-img"></a>';
        $html .= '<h5>' . $title . '</h5>';
        $html .= '</div>';
    }
    $html .= '</div>';
    echo $html;
}
add_shortcode('show_featured_teachings', 'show_featured_teachings');

function show_teachings_list($atts) {
    $teachings = new WP_Query(array('post_type'=>'teachings', 'posts_per_page'=>-1));
    $html = '<div id="teachings-list">';
    while ($teachings->have_posts()) : $teachings->the_post();
        $image = get_field('video_placeholder_image');
        $description = get_field('playlist_description');
        $study_guide = get_field('teachings_study_guide');
        if(empty($study_guide)) {
            $class = "not-visible";
        } else {
            $class = "";
        }
        $html .= '<a href="' . get_the_permalink() . '">';
        $html .= '<div class="list-item">';
        $html .= '<div class="item-img"><img src="' . $image['sizes']['medium'] . '" alt="' . $image['alt'] . '" class="teaching-img"></div>';
        $html .= '<div class="item-body">';
        $html .= '<h3>' . get_the_title() . '</h3>';
        $html .= '<p>' . $description . '</p>';
        $html .= '</div>';
        $html .= '<div class="item-buttons">';
        $html .= '<span class="teachings-buttons '  . $class . '">Study Guide Available</span>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</a>';
    endwhile;
    echo $html;
    wp_reset_postdata();
}
add_shortcode('show_teachings_list', 'show_teachings_list');

//add shortcodes to display study guide fields
function show_study_guide_pdf($atts) {
    $pdf = get_field('study_guide_pdf');
    $html = do_shortcode('[pdf-embedder url="' . $pdf['url'] . '"]');
    echo $html;
}
add_shortcode('show_study_guide_pdf', 'show_study_guide_pdf');

function show_study_guide_description($atts) {
    $description = get_field('study_guide_description');
    echo '<p class="description">' . $description . '</p>';
    echo '<p>This page contains full, unabridged work authored by Dr. Lester Sumrall. Please feel free to use the reader provided, or you may directly download the publication – both versions offer access to the full text. We hope you enjoy reading the works of Dr. Sumrall. To purchase a hard copy of this teaching visit <a href="https://www.leseapublishing.com" target="_blank">www.leseapublishing.com</a> to order.</p>';
    $pdf = get_field('study_guide_pdf');
    if($pdf) {
        echo '<a class="book-download" href="' . $pdf['url'] . '" target="_blank">Download Study Guide</a>';
    }
    do_shortcode('[show_study_guide_buttons]');
}
add_shortcode('show_study_guide_description', 'show_study_guide_description');

function show_study_guide_buttons($atts) {
    $a = shortcode_atts(array('link'=>true),$atts);
    
    $related_teaching = get_field('sg_related_teaching');
    $related_book = get_field('sg_related_book');
    $html = '<div class="study-buttons">';
    
    if(!empty($related_book)) {
        $html .= '<a href="' . $related_book . '" class="teachings-buttons">View Related Book</a>';
    } else {
        $html .= '<span class="teachings-buttons not-visible">No Related Book</span>'; 
    }
    if(!empty($related_teaching)) {
        $html .= '<a href="' . $related_teaching . '" class="teachings-buttons">View Related 
            Teaching</a>';           
    } else {
        $html .= '<span class="teachings-buttons not-visible">No Related 
            Teaching</span>'; 
    }
    
    $html .= '</div>';
    echo $html;
}
add_shortcode('show_study_guide_buttons', 'show_study_guide_buttons');

//add search functions
function get_search_option_list() {
    $posts = new WP_Query(array('post_type'=>array('books', 'teachings', 'study_guides'), 'posts_per_page'=>-1, 'order'=>'ASC', 'orderby'=>'title'));
    $options = '';
    $icon = '';
    while ($posts->have_posts()) : $posts->the_post();
        $id = get_the_ID();
        if(get_post_type($id) == 'teachings') {
            $icon = '&#xf03d ';
        } elseif(get_post_type($id) == 'books') {
            $icon = '&#xf02d ';
        } elseif(get_post_type($id) == 'study_guides') {
            $icon = '&#xf0ca ';
        }
        $options .= '<a href="' . get_the_permalink() . '">' . $icon . get_the_title() . '</a>';
    endwhile;
    wp_reset_postdata();
    return $options;
}

function home_search_form() {
    ob_start(); 
    ?>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <form class="search-home" action="<?php echo home_url('/'); ?>" role="search" method="get">
        <div class="form-group">
            <input type="search" placeholder="Keyword" name="s" class="form-control" autocomplete="off">
            <input type="submit" value="SEARCH">
        </div>
        <div id="topic-label" class="form-group" style="font-family: 'FontAwesome', 'Titillium Web';">
            <label for="page-input-box">Topic <span style="float: right;">&#xf0d7;</span></label>
            <input type="checkbox" class="form-control" id="page-input-box">
            <div id="page-options">                
                <?php echo get_search_option_list(); ?>
            </div>
        </div>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('home_search_form', 'home_search_form');

function inner_search_form() {
    ob_start(); 
    ?>
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"/>
    <form class="search-inner" action="<?php echo home_url('/'); ?>" role="search" method="get">
        <div class="form-group">
            <input type="search" placeholder="Keyword" name="s" class="form-control" autocomplete="off">
            <input type="submit" value="SEARCH">
        </div>
        <div id="topic-label" class="form-group" style="font-family: 'FontAwesome', 'Titillium Web';">
            <label for="page-input-box-inner">Topic <span style="float: right;">&#xf0d7;</span></label>
            <input type="checkbox" class="form-control" id="page-input-box-inner">
            <div id="page-options">                
                <?php echo get_search_option_list(); ?>
            </div>
        </div>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('inner_search_form', 'inner_search_form');

//show donation form 
function show_donation_form(){
    if(get_field('donation_form')) {
        echo get_field('donation_form');
    }
}
add_shortcode('show_donation_form', 'show_donation_form');
?>