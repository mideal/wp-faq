<?php
/**
 * @author  Mideal
 * @package Mideal Faq
 * @version 1.0
 */
/*
Plugin Name: Mideal Faq
Plugin URI: http://mideal.ru/
Description: Faq.
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

//добавим основную библиотеку jQuery
wp_enqueue_script('jquery');
// --------------------Подключение стилий-----------------------------
wp_enqueue_style( 'mideal-faq-style', plugins_url( '/css/style.css', __FILE__ ),false,'1.0','all');


// --------------------Подключение скриптов-----------------------------
wp_enqueue_script( 'mideal-faq-base', plugins_url( '/js/base.js', __FILE__ ), array( 'jquery' ),1.0,true );


// --------------------Админ панель-----------------------------
// Hook for adding admin menus
add_action('admin_menu', 'mideal_faq_add_pages');

// action function for above hook
function mideal_faq_add_pages() {
    // Add a new top-level menu (ill-advised):
    add_menu_page('Mideal Faq', 'Mideal Faq', 8, __FILE__, 'mideal_faq_page');
}

// mt_toplevel_page() displays the page content for mideal filter menu
function mideal_faq_page() { 
    echo "<h2>Вопросы и ответы</h2>";
}



//------------------------------- Таблица в базе данных--------------------------------------------
// global $jal_db_version;
// $jal_db_version = '1.0';

// function jal_install() {
//     global $wpdb;
//     global $jal_db_version;

//     $table_name = $wpdb->prefix . 'mideal_faq';
    
//     $charset_collate = $wpdb->get_charset_collate();

//     $sql = "CREATE TABLE $table_name (
//         id mediumint(9) NOT NULL AUTO_INCREMENT,
//         time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
//         name tinytext NOT NULL,
//         text text NOT NULL,
//         url varchar(55) DEFAULT '' NOT NULL,
//         PRIMARY KEY  (id)
//     ) $charset_collate;";

//     require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
//     dbDelta( $sql );

//     add_option( 'jal_db_version', $jal_db_version );
// }

// function jal_install_data() {
//     global $wpdb;
    
//     $welcome_name = 'Mr. WordPress';
//     $welcome_text = 'Congratulations, you just completed the installation!';
    
//     $table_name = $wpdb->prefix . 'liveshoutbox';
    
//     $wpdb->insert( 
//         $table_name, 
//         array( 
//             'time' => current_time( 'mysql' ), 
//             'name' => $welcome_name, 
//             'text' => $welcome_text, 
//         ) 
//     );
// }




// register_activation_hook( __FILE__, 'jal_install' );
// register_activation_hook( __FILE__, 'jal_install_data' );



//------------------------------- Новый тип записи--------------------------------------------


add_action( 'init', 'create_mideal_faq' );

function create_mideal_faq() {
    register_post_type( 'mideal_faq',
        array(
            'labels' => array(
            'name'               => 'Вопрос', // основное название для типа записи
            'singular_name'      => 'Вопрос', // название для одной записи этого типа
            'add_new'            => 'Добавить вопрос', // для добавления новой записи
            'add_new_item'       => 'Добавление вопроса', // заголовка у вновь создаваемой записи в админ-панели.
            'edit_item'          => 'Редактирование вопроса', // для редактирования типа записи
            'new_item'           => 'Новый вопрос', // текст новой записи
            'view_item'          => 'Смотреть вопрос', // для просмотра записи этого типа.
            'search_items'       => 'Искать вопрос', // для поиска по этим типам записи
            'not_found'          => 'Не найдено', // если в результате поиска ничего не было найдено
            'not_found_in_trash' => 'Не найдено в корзине', // если не было найдено в корзине
            'parent_item_colon'  => '', // для родителей (у древовидных типов)
            'menu_name'          => 'Вопрос ответ', // название меню
            ),
            'public' => true,
            'menu_position' => 15,
            'supports' => array( 'title', 'editor' ),
            //'taxonomies' => array( '' ),
           // 'menu_icon' => plugins_url( 'images/image.png', __FILE__ ),
            //'has_archive' => false
        )
    );
}


// ------------------------ Колонка ответы в админ панели ----------------

add_filter( 'manage_mideal_faq_posts_columns', 'set_custom_edit_faq_columns' );
add_action( 'manage_mideal_faq_posts_custom_column' , 'custom_faq_column', 10, 2 );

function set_custom_edit_faq_columns($columns) {

    $num = 2; // после какой по счету колонки вставлять новые

    $new_columns = array(
        'faq_ansver' => 'Ответ',
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
add_filter('manage_edit-mideal_faq_sortable_columns', 'add_views_sortable_column');
function add_views_sortable_column($sortable_columns){
    $sortable_columns['faq_ansver'] = 'Ответ';
    return $sortable_columns;
}

//------------------------------- Дополнительные поля в вопросах--------------------------------------------

// подключаем функцию активации мета блока
add_action('add_meta_boxes', 'mideal_faq_add_fields');

function mideal_faq_add_fields() {
    add_meta_box( 'mideal_faq_fields', 'Ответ на вопрос', 'mideal_faq_add_field_func', 'mideal_faq', 'normal', 'high'  );
}

// код блока
function mideal_faq_add_field_func($faq_item){
    $faq_ansver = get_post_meta( $faq_item->ID, 'mideal_faq_ansver', true );
    $faq_email = get_post_meta( $faq_item->ID, 'mideal_faq_email', true );
    wp_editor($faq_ansver,'faq_add_ansver',array('textarea_name' => 'mideal_faq_ansver'));
    echo '<br>';
    echo '<br>';
    echo 'Email пользователя: <input type="text" name="mideal_faq_email" value="'.$faq_email.'" size="25" placeholder="Email пользователя"/>';
    wp_nonce_field( plugin_basename(__FILE__), 'mideal_faq_noncename' );
}




// включаем обновление полей при сохранении
add_action('save_post', 'mideal_faq_update');

/* Сохраняем данные, при сохранении поста */
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

    // Обновляем данные в базе данных.
    update_post_meta( $post_id, 'mideal_faq_ansver', $my_data );
    update_post_meta( $post_id, 'mideal_faq_email', $my_data2 );
}

// ----------------------------Права на управление плагином ------------------------
function mideal_faq_permission($roles){
    $allowed_roles = array('editor', 'administrator');
    if( array_intersect($allowed_roles, $roles ) ) {
        return true;
    } else {
        return false;
    }
}
//------------------------------- Шорткоды--------------------------------------------
add_shortcode('mideal-faq', 'mideal_faq_list');

function mideal_faq_list() {
echo "<h2>Список вопросов</h2>";

$user = wp_get_current_user();
$user_faq_admin = mideal_faq_permission($user->roles);



if($user_faq_admin=='true') {
    $post_status = 'any';
} else {
    $post_status = 'publish';
}


$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
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
    foreach ($faq_array->posts as $key => $post) {
        //echo '<pre>';
        //print_r();
        //echo '</pre>';
        echo "<li class='media";
        if($post->post_status!="publish"){echo " no-published";}
        echo "' data-id='".$post->ID."'>";
        echo "<div class='media-body'>";
        echo "<h4 class='media-heading'>".$post->post_title."</h4>";
        echo "<p>".$post->post_date."</p>";
        echo "<p>".$post->post_content."</p>";
        if($user_faq_admin=='true'){
            echo '<a class="btn btn-default" href="'.get_edit_post_link($post->ID).'">Ответить</a>';
            echo '<a href="#" class="btn btn-default mideal-faq-delete-post" data-id="'.$post->ID.'">Удалить</a>';
            if($post->post_status == 'publish'){
                echo '<a class="btn btn-default mideal-faq-publish-post" data-status="'.$post->post_status.'" data-id="'.$post->ID.'" href="#">Снять с публикации</a>';
            } else {
                echo '<a class="btn btn-default mideal-faq-publish-post" data-status="'.$post->post_status.'" data-id="'.$post->ID.'" href="#">Опубликовать</a>';
            }
        }
        echo "<div class='media-body'>";
        echo "<h4 class='media-heading'> Ital-bags </h4>";
        echo "<p>".get_post_meta( $post->ID, 'mideal_faq_ansver', true )."</p>";
        echo "</div>";

        echo "</div>";
        echo "</li>";
    }
} else {
    echo "Нет вопросов";
}
echo "</ul>";

//------------------------ Пагинация ----------------------
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
    if($pages){
        $pages = str_replace( '/page/1/', '', $pages );
        echo '<ul class="pagination">';
        foreach ($pages as $i => $page) {
            if ($paged == 1 && $i == 0) {
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



// ------------------- Добавление нововго поста форма----------------

add_shortcode('mideal-faq-form', 'mideal_faq_form');

function mideal_faq_form() {

echo "<h2>Форма</h2>";
echo '<form class="form-horizontal" /wp-admin/admin-ajax.php id="form-mideal-faq">';

echo '<div class="form-group">';
echo '<span class="col-sm-2 control-label">Имя<span class="red">*</span>:</span>';
echo '<div class="col-sm-10">';
echo '<input type="text" name="mideal_faq_name" class="form-control" placeholder="Имя">';
echo '</div>';
echo '</div>';
echo '<div class="form-group">';
echo '<span class="col-sm-2 control-label">E-mail<span class="red">*</span>:</span>';
echo '<div class="col-sm-10">';
echo '<input type="text" name="mideal_faq_email" class="form-control" placeholder="Ваш E-mail адрес">';
echo '</div>';
echo '</div>';
echo '<div class="form-group">';
echo '<span class="col-sm-2 control-label">Вопрос<span class="red">*</span>:</span>';
echo '<div class="col-sm-10">';
echo '<textarea type="text" name="mideal_faq_question" class="form-control" placeholder="Текст вопроса"></textarea>';
echo '</div>';
echo '</div>';
echo '<div class="form-group sent-group">';
echo '<div class="col-sm-2 control-label">&nbsp;</div>';
echo '<div class="col-sm-10">';
echo '<div class="message-error-sent"></div>';
echo '<input class="btn btn-default sent-mideal-faq" type="submit" value="Задать вопрос">';
echo '</div>';
echo '</div>';

echo '</form>';
}





// ------------------- Добавление поддржки ajax----------------

add_action( 'wp_enqueue_scripts', 'myajax_data', 99 );
function myajax_data(){

    wp_localize_script('mideal-faq-base', 'myajax', 
        array(
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('myajax-nonce')
        )
    );  

}


// ------------------- Добавление нововго поста ajax----------------
if( defined('DOING_AJAX') && DOING_AJAX ) {
    add_action('wp_ajax_mideal_faq_add', 'mideal_faq_add_callback');
    add_action('wp_ajax_nopriv_mideal_faq_add', 'mideal_faq_add_callback');
}

function mideal_faq_add_callback() {
    $nonce = $_POST['nonce'];

    // проверяем nonce код, если проверка не пройдена прерываем обработку
    if ( ! wp_verify_nonce( $nonce, 'myajax-nonce' ) ){
        die ( 'Stop!');
    }

    // Создаем массив данных новой записи
    $post_data = array(
        'post_title'    => wp_strip_all_tags( $_POST['mideal_faq_name'] ),
        'post_content'  => $_POST['mideal_faq_question'],
        'post_status'   => 'pending',
        'post_type'  => 'mideal_faq'
    );

    //Вставляем запись в базу данных
    $post_id = wp_insert_post( $post_data );
    if($post_id){
        update_post_meta( $post_id, 'mideal_faq_email', $_POST['mideal_faq_email'] );
    
        //отправляем оповещние о новом вопросе на email
        $friends = 'midealf@gmail.com, smith.mariia@gmail.com';
        wp_mail( $friends, "Вопрос:", $_POST['mideal_faq_question'] );
    }
    wp_die();
}




// ------------------- Удаление поста ajax----------------

if( defined('DOING_AJAX') && DOING_AJAX ) {
    add_action('wp_ajax_mideal_faq_delete', 'mideal_faq_delete_callback');
}

function mideal_faq_delete_callback() {
    $nonce = $_POST['nonce'];
    $user = wp_get_current_user();
    $user_faq_admin = mideal_faq_permission($user->roles);
    // проверяем nonce код, если проверка не пройдена прерываем обработку
    if ( ! wp_verify_nonce( $nonce, 'myajax-nonce' ) ){
        die ( 'Stop!');
    }

     if ( $user_faq_admin!='true' ) {
         die ('Stop!');
     }

    wp_delete_post($_POST['ID'] );
    wp_die();
}

// ------------------- Публикация поста ajax----------------

if( defined('DOING_AJAX') && DOING_AJAX ) {
    add_action('wp_ajax_mideal_faq_publish', 'mideal_faq_publish_callback');
}

function mideal_faq_publish_callback() {
    $nonce = $_POST['nonce'];
    $user = wp_get_current_user();
    $user_faq_admin = mideal_faq_permission($user->roles);
    // проверяем nonce код, если проверка не пройдена прерываем обработку
    if ( ! wp_verify_nonce( $nonce, 'myajax-nonce' ) ){
        die ( 'Stop!');
    }

    if ( $user_faq_admin!='true' ) {
        die ('Stop!');
    }

    print_r($_POST);
    if( $_POST['post_status'] != 'publish'){
        wp_publish_post( $_POST['ID'] );
    } else {
        $post_data = array(
            'ID'    => $_POST['ID'],
            'post_content'  => $_POST['mideal_faq_question'],
            'post_status'   => 'pending',
            'post_type'  => 'mideal_faq'
        );
        wp_update_post( $post_data );
    }

    wp_die();
}

