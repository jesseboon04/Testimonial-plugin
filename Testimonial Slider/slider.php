<?php
/*
Plugin Name: Testimonial Slider Plugin 
Description: Een eenvoudige testimonial slider plug-in waarbij je gemakkelijk via het dashboard nieuwe testimonials kan aanmaken. Voeg deze  shortcode [testimonial_slider] toe op de pagina om testimoials op te halen.
Version: 1.0
Author: Jesse Boon
*/

// Stap 1: Voegt een custom post toe
function create_testimonial_post_type() {
    $labels = array(
        'name' => 'Testimonials',
        'singular_name' => 'Testimonial',
        'add_new' => 'Nieuw testimonial toevoegen',
        'add_new_item' => 'Nieuw testimonial toevoegen',
        'edit_item' => 'Testimonial bewerken',
        'new_item' => 'Nieuw testimonial',
        'view_item' => 'Testimonial bekijken',
        'search_items' => 'Testimonials zoeken',
        'not_found' => 'Geen testimonials gevonden',
        'not_found_in_trash' => 'Geen testimonials gevonden in de prullenbak',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'supports' => array('title', 'editor', 'thumbnail'),
        'menu_icon' => 'dashicons-testimonial',
        'has_archive' => true,
    );

    register_post_type('testimonial', $args);
}
add_action('init', 'create_testimonial_post_type');

// Stap 2: Maak een shortcode om de testimonials weer te geven
function testimonial_slider_shortcode() {
    ob_start(); 

    
    $query = new WP_Query(array(
        'post_type' => 'testimonial',
        'posts_per_page' => -1, // Haal alle testimonials op
    ));

    //  HTML van de container
    echo '<section class="testimonials">';
    echo '<div class="container-testimonial">';
    echo '<div class="section-header">';
    echo '<h2 class="title">Wat zeggen onze bezoekers</h2>';
    echo '</div>';
    echo '<div class="testimonial-content">';
    echo '<div class="swiper testimonial-slider js-testimonial-slider">';
    echo '<div class="swiper-wrapper">';

    
    while ($query->have_posts()) {
        $query->the_post();

        // Haal de inhoud van het testimonial op
        $title = get_the_title();
        $content = get_the_content();
        $thumbnail = get_the_post_thumbnail_url();
        $rating = get_post_meta(get_the_ID(), 'testimonial_rating', true);
        $job = get_post_meta(get_the_ID(), 'testimonial_job', true);

        // HTML kaartje testimonial
        echo '<div class="swiper-slide testimonial-item">';
        echo '<div class="info">';
        echo '<img src="' . esc_url($thumbnail) . '" alt="' . esc_attr($title) . '">';
        echo '<div class="text-box">';
        echo '<h3 class="name">' . esc_html($title) . '</h3>';
        echo '<span class="job">' . esc_html($job) . '</span>';
        echo '</div>';
        echo '</div>';
        echo '<p>' . esc_html($content) . '</p>';
        echo '<div class="rating">';

        // Voeg de beoordeling toe (1 tot en met 5)
        for ($i = 0; $i < $rating; $i++) {
            echo '<i class="fa fa-star"></i>';
        }

        echo '</div>'; 
        echo '</div>'; // Sluit de testimonial slide 
    }

    // Einde van de testimonials slider en HTML
    echo '</div>'; 
    echo '</div>'; 
    echo '<div class="swiper-pagination js-testimonial-pagination"></div>';
    echo '</div>'; 
    echo '</div>'; 
    echo '</section>'; 

    // Voer Swiper JS-script uit
    echo '<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>';
    echo '<script>';
    echo 'const swiper = new Swiper(".js-testimonial-slider", {';
    echo '    grabCursor: true,';
    echo '    spaceBetween: 30,';
    echo '    pagination: {';
    echo '        el: ".js-testimonial-pagination",';
    echo '        clickable: true,';
    echo '    },';
    echo '    breakpoints: {';
    echo '        767: {';
    echo '            slidesPerView: 2,';
    echo '        },';
    echo '    },';
// 	echo '    autoplay: {';
// 	echo '        delay: 5000, // Auto-play interval in milliseconds (5 seconds)';
// 	echo '        disableOnInteraction: false, // Enable/disable autoplay on slide interaction';
// 	echo '    },';
    echo '});';
    echo '</script>';

    wp_reset_postdata(); // Herstel de globale postdata

    return ob_get_clean(); // Haal de output op
}

//  de shortcode
add_shortcode('testimonial_slider', 'testimonial_slider_shortcode');

//  Voeg aangepaste velden toe voor de testimonials
function testimonial_custom_fields() {
    // Voeg een meta box toe voor de rating
    add_meta_box(
        'testimonial_rating',
        'Rating',
        'testimonial_rating_meta_box_callback',
        'testimonial',
        'side',
        'default'
    );

    // Voeg een meta box toe voor de functie/job
    add_meta_box(
        'testimonial_job',
        'Job',
        'testimonial_job_meta_box_callback',
        'testimonial',
        'side',
        'default'
    );
}


function testimonial_rating_meta_box_callback($post) {
    // Haal de huidige rating op
    $rating = get_post_meta($post->ID, 'testimonial_rating', true);

    // HTML voor de rating-meta box
    echo '<label for="testimonial_rating">Rating (1-5):</label>';
    echo '<input type="number" id="testimonial_rating" name="testimonial_rating" min="1" max="5" value="' . esc_attr($rating) . '">';
}


function testimonial_job_meta_box_callback($post) {
    // Haal de huidige job op
    $job = get_post_meta($post->ID, 'testimonial_job', true);

    // HTML voor de job-meta box
    echo '<label for="testimonial_job">Job:</label>';
    echo '<input type="text" id="testimonial_job" name="testimonial_job" value="' . esc_attr($job) . '">';
}

// Bewaar de aangepaste velden bij het opslaan van een testimonial
function save_testimonial_custom_fields($post_id) {
    // Controleer of dit een testimonial is en de gebruiker de juiste rechten heeft
    if (isset($_POST['testimonial_rating']) && current_user_can('edit_post', $post_id)) {
        $rating = intval($_POST['testimonial_rating']);
        update_post_meta($post_id, 'testimonial_rating', $rating);
    }

    if (isset($_POST['testimonial_job']) && current_user_can('edit_post', $post_id)) {
        $job = sanitize_text_field($_POST['testimonial_job']);
        update_post_meta($post_id, 'testimonial_job', $job);
    }
}

// Voeg acties toe om aangepaste velden toe te voegen en op te slaan
add_action('add_meta_boxes', 'testimonial_custom_fields');
add_action('save_post', 'save_testimonial_custom_fields');

// Voeg de CSS-bestanden toe 
function enqueue_testimonial_slider_styles() {
    // Voeg de externe CSS-bestanden 
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css', [], null);
    wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', [], null);
    
    // Voeg het CSS-bestand van de plug-in toe
    wp_enqueue_style('testimonial-slider-styles', plugin_dir_url(__FILE__) . 'css/style.css', [], '1.0');
}

// Voeg de CSS-bestanden toe bij het initiëren van de plug-in
add_action('wp_enqueue_scripts', 'enqueue_testimonial_slider_styles');
?>
