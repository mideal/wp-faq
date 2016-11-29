<?php
/**
 * @author  Mideal
 * @package Mideal Faq
 * @version 1.0
 */
/*
Plugin Name: Mideal Faq
Plugin URI: http://mideal.ru/wordpress-plugin/mideal-faq/
Description: Ajax, bootstrap Faq.
Author: Mideal
Version: 1.0
Author URI: http://mideal.ru/
*/
/*  Copyright 2016  Mideal  (email: midealf@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if (!defined('ABSPATH'))
{
    exit;
}

//wp_enqueue_style( 'mideal-faq-style-test', plugins_url( '/bootstrap/css/bootstrap.min.css', __FILE__ ),false,'1.0','all' );

// --------------------add style plugin-----------------------------
wp_enqueue_style( 'mideal-faq-style', plugins_url( '/css/style.css', __FILE__ ),false,'1.0','all' );
wp_enqueue_style( 'mideal-faq-bootstrap', plugins_url( '/css/bootstrap.css', __FILE__ ),false,'1.0','all' );

// --------------------add script plugin, check jquery-----------------------------
wp_enqueue_script( 'mideal-faq-base', plugins_url( '/js/base.js', __FILE__ ), array( 'jquery' ),1.0,true );

// --------------------Admin panel-----------------------------
add_action( 'admin_menu', 'mideal_faq_create_menu' );

function mideal_faq_create_menu() {
    add_options_page( 'Mideal Faq', 'Mideal Faq', 8, __FILE__, 'mideal_faq_settings_page' );
    add_action( 'admin_init', 'register_mideal_faq_settings' );
}

function register_mideal_faq_settings() {
    register_setting( 'mideal-faq-settings-group', 'mideal_faq_setting_email' );
}

function mideal_faq_settings_page() {
?>
<div class="wrap">
<h1>Mideal Faq</h1>

<form method="post" action="options.php">
    <?php settings_fields( 'mideal-faq-settings-group' ); ?>
    <?php do_settings_sections( 'mideal-faq-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row"><?php _e( "The email address for notifications about new question", "mideal-faq" );?></th>
        <td><input type="text" name="mideal_faq_setting_email" value="<?php echo esc_attr( get_option( 'mideal_faq_setting_email',get_option( 'admin_email' )) ); ?>" /></td>
        </tr>
    </table>
    <?php submit_button(); ?>
</form>
</div>
<?php }



add_action( 'plugins_loaded', 'mideal_faq_init' );
function mideal_faq_init(){


    // --------------------------------- Add support ajax----------------------------------
    wp_localize_script('mideal-faq-base', 'myajax', 
        array(
            'url' => admin_url( 'admin-ajax.php' ),
            'nonce' => wp_create_nonce( 'myajax-nonce' )
        )
    );  


    // ------------------------------------ Add translate------------------------------------
    load_plugin_textdomain( 'mideal-faq', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    
    // ------------------------------- Add script translate---------------------------------
    $translation_array = array( 
     'errorajax' => __( 'Unfortunately, an error occurred. Try again later please', "mideal-faq" ),
     'okajax' => __( 'Thank you for your question. It will appear after moderation', "mideal-faq" ),
     'publish' => __("Publish", "mideal-faq"),
     'unpublish' => __("Unpublish", "mideal-faq"),
    );
    wp_localize_script( 'mideal-faq-base', 'mideal_faq_l10n', $translation_array );
}



//------------------------------- Новый тип записи--------------------------------------------


add_action( 'init', 'create_mideal_faq' );

function create_mideal_faq() {
    register_post_type( 'mideal_faq',
        array(
            'labels' => array(
            'name'               => __("Question", "mideal-faq"),
            'singular_name'      => __("Question", "mideal-faq"),
            'add_new'            => __("Add question", "mideal-faq"),
            'add_new_item'       => __("Add question", "mideal-faq"),
            'edit_item'          => __("Edit question", "mideal-faq"),
            'new_item'           => __("New question", "mideal-faq"),
            'menu_name'          => __("FAQ", "mideal-faq"),
            ),
            'public' => true,
            'menu_position' => 15,
            'supports' => array( 'title', 'editor' ),
           // 'menu_icon' => plugins_url( 'images/image.png', __FILE__ ),
        )
    );
}

// ------------------------ Колонка ответы в админ панели ----------------

add_filter( 'manage_mideal_faq_posts_columns', 'set_custom_edit_faq_columns' );
add_action( 'manage_mideal_faq_posts_custom_column' , 'custom_faq_column', 10, 2 );

function set_custom_edit_faq_columns( $columns ) {

    $num = 2; // после какой по счету колонки вставлять новые

    $new_columns = array(
        'faq_ansver' => __("Ansver", "mideal-faq"),
    );
    return array_slice( $columns, 0, 2 ) + $new_columns + array_slice( $columns, $num );
}

function custom_faq_column( $column, $post_id ) {
    switch ( $column ) {

        case 'faq_ansver' :
            echo get_post_meta( $post_id, 'mideal_faq_ansver', true );
            break;
    }
}

// добавляем возможность сортировать колонку
add_filter( 'manage_edit-mideal_faq_sortable_columns', 'add_views_sortable_column' );
function add_views_sortable_column( $sortable_columns ){
    $sortable_columns['faq_ansver'] = __( "Ansver", "mideal-faq" );
    return $sortable_columns;
}

//------------------------------- add custom fields in FAQ--------------------------------------------


add_action( 'add_meta_boxes', 'mideal_faq_add_fields' );

function mideal_faq_add_fields() {
    add_meta_box( 'mideal_faq_fields', __("Ansver a question", "mideal-faq"), 'mideal_faq_add_field_func', 'mideal_faq', 'normal', 'high'  );
}


function mideal_faq_add_field_func( $faq_item ){
    $faq_ansver = get_post_meta( $faq_item->ID, 'mideal_faq_ansver', true );
    $faq_email = get_post_meta( $faq_item->ID, 'mideal_faq_email', true );
    wp_editor( $faq_ansver,'faq_add_ansver', array( 'textarea_name' => 'mideal_faq_ansver' ));
    echo '<br />';
    echo '<br />';
    echo __( "User Email", "mideal-faq" ).': <input type="text" name="mideal_faq_email" value="'.$faq_email.'" size="25" />';
    wp_nonce_field( plugin_basename(__FILE__), 'mideal_faq_noncename' );
}




// update fields after save
add_action( 'save_post', 'mideal_faq_update' );

function mideal_faq_update( $post_id ){

    if ( ! wp_verify_nonce( $_POST['mideal_faq_noncename'], plugin_basename(__FILE__) ) ) return $post_id;; 
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post_id; // если это автосохранение
    if ( 'page' == $_POST['post_type'] && ! current_user_can( 'edit_page', $post_id ) ) {
          return $post_id;
    } elseif( ! current_user_can( 'edit_post', $post_id ) ) {
        return $post_id;
    }
    if ( ! isset( $_POST['mideal_faq_ansver'] ) ) return $post_id;
    
    $my_data = sanitize_text_field( $_POST['mideal_faq_ansver'] );
    $my_data2 = sanitize_text_field( $_POST['mideal_faq_email'] );

    update_post_meta( $post_id, 'mideal_faq_ansver', $my_data );
    update_post_meta( $post_id, 'mideal_faq_email', $my_data2 );
}

// ---------------------------------Permission --------------------------------------
function mideal_faq_permission( $roles ) {
    $allowed_roles = array( 'editor', 'administrator' );
    if( array_intersect($allowed_roles, $roles ) ) {
        return true;
    } else {
        return false;
    }
}
//------------------------------- Shortcode--------------------------------------------
add_shortcode('mideal-faq', 'mideal_faq_list');

function mideal_faq_list() {
    echo '<h2>'.__("List a question", "mideal-faq").'</h2>';

    $user = wp_get_current_user();
    $user_faq_admin = mideal_faq_permission($user->roles);



    if($user_faq_admin=='true') {
        $post_status = 'any';
    } else {
        $post_status = 'publish';
    }


    $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
    $args = array(
        'posts_per_page' => 5,
        'paged' => $paged, 
        'post_type' => 'mideal_faq',
        'orderby' => 'date',
        'order'   => 'DESC',
        'post_status' => $post_status
    );

    $faq_array = new WP_Query( $args );

    echo '<ul id="mideal-faq-list" class="media-list">';
    if ( $faq_array->have_posts() ) {
        foreach ( $faq_array->posts as $key => $post ) {
            echo "<li class='media-list-item";
            if( $post->post_status!="publish" ){
                echo " no-published";
            }
            echo "' data-id='".$post->ID."'>";

           
            echo "<div class='faq-header'><div class='faq-name'>".$post->post_title."</div><div class='faq-date'>".$post->post_date."</div></div>";
            echo "<div class='faq-question'>";
            $user_email = get_post_meta( $post->ID, 'mideal_faq_email', true );
            $url_default_avatar = urlencode(plugins_url( 'img/avatar-default.png', __FILE__ ));
            $user_avatar_url = 'https://www.gravatar.com/avatar/'.md5( strtolower( trim( $user_email ) ) ).'?d='.$url_default_avatar.'&s=40';
            echo "<img class='media-object chat-avatar' src='".$user_avatar_url."' alt='avatar'>";
            echo "<div class='chat-text'>".$post->post_content."</div>";
            echo "</div>";
            $ansver_text = get_post_meta( $post->ID, 'mideal_faq_ansver', true );
            if ($ansver_text) {
                echo "<div class='faq-ansver'>";
                echo "<div class='faq-header'>".__("Ansver", "mideal-faq")."</div>";
                echo "<div class='clearfix'></div>";
                echo "<img class='media-object chat-avatar' src='".plugins_url( 'img/avatar-default.png', __FILE__ )."' alt='avatar'>";
                echo "<div class='chat-text'>".$ansver_text."</div>";
                echo "</div>";
            }
            if( 'true' == $user_faq_admin ){
                echo '<div class="mideal-faq-admin-btn">';
                if( $ansver_text ) {
                    $text_btn_reply = __( "Edit", "mideal-faq" );
                } else {
                    $text_btn_reply = __( "Reply", "mideal-faq" );
                }
                echo '<a class="btn btn-xs btn-success" href="'.get_edit_post_link($post->ID).'">'.$text_btn_reply.'</a>';
                if($post->post_status == 'publish'){
                    echo '<a class="btn btn-default btn-xs mideal-faq-publish-post" data-status="'.$post->post_status.'" data-id="'.$post->ID.'" href="#">'.__("Unpublish", "mideal-faq").'</a>';
                } else {
                    echo '<a class="btn btn-default btn-xs mideal-faq-publish-post" data-status="'.$post->post_status.'" data-id="'.$post->ID.'" href="#">'.__("Publish", "mideal-faq").'</a>';
                }
                echo '<a href="#" class="btn btn-xs btn-danger mideal-faq-delete-post" data-id="'.$post->ID.'">'.__( "Delete", "mideal-faq" ).'</a>';
                echo '</div>';
            }
            echo "<hr>";
            echo "</li>";
        }
    } else {
        echo "<li class='media'>".__( "No question", "mideal-faq" )."</li>";
    }


    //------------------------ Pagination ----------------------
    $big = 999999999;
    $pages = paginate_links(array(
        'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
        'format' => '',
        'current' => $paged,
        'total' => $faq_array->max_num_pages,
        'type' => 'array',
        'prev_next' => true,
        'prev_text' => '<',
        'next_text' => '>',
            ));
    if( $pages ){
        $pages = str_replace( '/page/1/', '', $pages );
        echo '<ul class="pagination">';
        foreach ( $pages as $i => $page ) {
            if ( $paged == 1 && $i == 0 ) {
                echo "<li class='active'>$page</li>";
            } else {
                if ($paged != 1 && $paged == $i) {
                    echo "<li class='active'>$page</li>";
                } else {
                    echo "<li>$page</li>";
                }
            }
        }
        echo '</ul>';
    }
    wp_reset_postdata();
}

    echo "</ul>";

// ------------------- add new question----------------

add_shortcode( 'mideal-faq-form', 'mideal_faq_form' );

function mideal_faq_form() {

    echo "<h2>".__( 'Add question', 'mideal-faq' )."</h2>";
    echo '<form id="form-mideal-faq">';

    echo '<div class="form-group">';
    echo '<label>'.__("Name", "mideal-faq").'<span class="red">*</span>:</label>';
    echo '<input type="text" name="mideal_faq_name" class="form-control" placeholder="'.__("Name", "mideal-faq").'">';
    echo '</div>';
    echo '<div class="form-group">';
    echo '<label>'.__("E-mail", "mideal-faq").'<span class="red">*</span>:</label>';
    echo '<input type="text" name="mideal_faq_email" class="form-control" placeholder="'.__("Your E-mail", "mideal-faq").'">';
    echo '</div>';
    echo '<div class="form-group">';
    echo '<label>'.__("Question", "mideal-faq").'<span class="red">*</span>:</label>';
    echo '<textarea name="mideal_faq_question" class="form-control" placeholder="'.__("Your question", "mideal-faq").'"></textarea>';
    echo '</div>';
    echo '<div class="form-group sent-group">';
    echo '<div class="message-error-sent"></div>';
    echo '<input class="btn btn-primary sent-mideal-faq" type="submit" value="'.__("Ask a question", "mideal-faq").'">';
    echo '</div>';

    echo '</form>';

}


// ------------------- Add post ajax----------------
if( defined('DOING_AJAX') && DOING_AJAX ) {
    add_action('wp_ajax_mideal_faq_add', 'mideal_faq_add_callback');
    add_action('wp_ajax_nopriv_mideal_faq_add', 'mideal_faq_add_callback');
}

function mideal_faq_add_callback() {
    $nonce = $_POST['nonce'];

    if ( ! wp_verify_nonce( $nonce, 'myajax-nonce' ) ){
        die ( 'Stop!');
    }

    $post_data = array(
        'post_title'    => wp_strip_all_tags( $_POST['mideal_faq_name'] ),
        'post_content'  => $_POST['mideal_faq_question'],
        'post_status'   => 'pending',
        'post_type'  => 'mideal_faq'
    );

    $post_id = wp_insert_post( $post_data );
    if( $post_id ){
        update_post_meta( $post_id, 'mideal_faq_email', $_POST['mideal_faq_email'] );



        //sent notification on email
        $sendto   = get_option('mideal_faq_setting_email',get_option('admin_email'));
        $subject  = __("New question on site", "mideal-faq");

         $headers = "MIME-Version: 1.0\r\n";
         $headers .= "Content-Type: text/html;charset=utf-8 \r\n";

        $username  = nl2br($_POST['mideal_faq_name']);
        $usermail = $_POST['mideal_faq_email'];
        $faq_content  = nl2br($_POST['mideal_faq_question']);
        $msg  = "<html><body style='font-family:Arial,sans-serif;'>";
        $msg .= "<h2 style='font-weight:bold;border-bottom:1px dotted #ccc;'>".__('New question on site', 'mideal-faq')." ".get_option('blogname').":</h2>\r\n";
        $msg .= "<p><strong>".__('Name', 'mideal-faq').":</strong> ".$username."</p>\r\n";
        $msg .= "<p><strong>".__('E-mail', 'mideal-faq').":</strong> ".$usermail."</p>\r\n";
        $msg .= "<p><strong>".__('Question', 'mideal-faq').":</strong> ".$faq_content."</p>\r\n";
        $msg .= "<p><strong><a href='".get_edit_post_link($post_id)."'>".__('Reply', 'mideal-faq')."</a></strong></p>\r\n";
        $msg .= "</body></html>";

        wp_mail( $sendto, $subject, $msg, $headers );

    }
    wp_die();
}


// ------------------- Delete post ajax----------------

if( defined('DOING_AJAX') && DOING_AJAX ) {
    add_action('wp_ajax_mideal_faq_delete', 'mideal_faq_delete_callback');
}

function mideal_faq_delete_callback() {
    $nonce = $_POST['nonce'];
    $user = wp_get_current_user();
    $user_faq_admin = mideal_faq_permission($user->roles);

    if ( ! wp_verify_nonce( $nonce, 'myajax-nonce' ) ){
        die ( 'Stop!');
    }

     if ( $user_faq_admin!='true' ) {
         die ('Stop!');
     }

    wp_delete_post($_POST['ID'] );
    wp_die();
}

// ------------------- Publish post ajax----------------

if( defined('DOING_AJAX') && DOING_AJAX ) {
    add_action('wp_ajax_mideal_faq_publish', 'mideal_faq_publish_callback');
}

function mideal_faq_publish_callback() {
    $nonce = $_POST['nonce'];
    $user = wp_get_current_user();
    $user_faq_admin = mideal_faq_permission($user->roles);

    if ( ! wp_verify_nonce( $nonce, 'myajax-nonce' ) ){
        die ( 'Stop!');
    }

    if ( $user_faq_admin!='true' ) {
        die ('Stop!');
    }

    if( $_POST['post_status'] != 'publish'){
        wp_publish_post( $_POST['ID'] );
    } else {
        $post_data = array(
            'ID'    => $_POST['ID'],
            'post_status'   => 'pending',
            'post_type'  => 'mideal_faq'
        );
        wp_update_post( $post_data );
    }

    wp_die();
}
