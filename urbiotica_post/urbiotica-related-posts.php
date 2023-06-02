<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://digitalgroup.es/
 * @since             1.0.0
 * @package           URBIOTICA_post
 *
 * @wordpress-plugin
 * Plugin Name:       URBIOTICA - Related Posts 
 * Plugin URI:        https://www.digitalgroup.es/
 * Description:       Muestra artículos relacionados al final de cada publicación
 * Version:           1.0.0
 * Author:            Digital Group
 * Author URI:        https://digitalgroup.es/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       urbiotica-post
 * Domain Path:       
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'URBIOTICA_POST_VERSION', '1.0.0' );

function display_related_posts($content) {
    if (is_single()) {
        $original_content = $content;
        $content = '';
        $content .= $original_content;

        ob_start();
        show_related_posts();
        $related_posts_content = ob_get_clean(); 

        if ($related_posts_content) {
            $content .= $related_posts_content;
        }
    }
    echo $content;
}
// add_filter('tha_content_after', 'display_related_posts');
// Agregar el hook de post relacionados
add_action('urbiotica_add_related_post', 'display_related_posts', 10);


// Función para mostrar los artículos relacionados al final del post
function show_related_posts() {
    // Obtener la categoría actual del post
    $categories = get_the_category();
    if (empty($categories)) {
        return; // Salir si no hay categorías
    }
    $category = $categories[0]; // Suponiendo que solo tiene una categoría asignada

    // Consultar artículos relacionados de la misma categoría
    $related_args = array(
        'post_type' => 'post',
        'posts_per_page' => 2, // Máximo de 2 artículos relacionados
        'category__in' => array($category->term_id),
        'post__not_in' => array(get_the_ID()),
        'orderby' => 'rand'
    );
    $related_query = new WP_Query($related_args);

    // Mostrar artículos relacionados
    if ($related_query->have_posts()) {
        echo '<section class="post-related-block">';
        ?>
            <h3 class="post-related__title"> <?php echo __('More about ', 'urbiotica-post') . $category->name ?></h3>
        <?php
        // echo '<h3 class="post-related__title">Más sobre ' . $category->name . '</h3>';
        echo '<ul class="post-related__list">';
        $related_posts_count = 0;
        while ($related_query->have_posts() && $related_posts_count < 2) {
            $related_query->the_post();
            echo '<li>';
            ?>
            <article id="post-<?php the_ID();?>" <?php post_class("post-card") ?>>
            <?php
            echo '<a href="' . get_permalink() . '">';
            echo '<div class="post-card__image">';
            $thumbnail_url = get_the_post_thumbnail_url();
            if (empty($thumbnail_url)) {
                $thumbnail_url = 'https://urbiotica.com/wp-content/uploads/2023/05/URBIOTICA.jpg';
            }
            echo '<img src="' . $thumbnail_url . '" alt="' . get_the_title() . '">';
            echo '</div>';
            echo '<div class="post-card__content">';
            echo '<h4 class="post-card__title">' . get_the_title() . '</h4>';
            echo '<div class="post-card__excerpt">';
            echo '<p>' . get_the_excerpt() . '</p>';
            echo '</div>';
            echo '</div>';
            echo '</a>';
            echo '</article>';
            echo '</li>';

            $related_posts_count++;
        }

        echo '</ul>';
        echo '</section>';

        wp_reset_postdata();
    }
}

// Cargar archivos CSS personalizados
function urbiotica_related_post_assets() {
    wp_enqueue_style( 'urbiotica_relatedpost', plugins_url( '/css/relatedpost.css', __FILE__ ) );
}
add_action( 'wp_enqueue_scripts', 'urbiotica_related_post_assets' );

?>