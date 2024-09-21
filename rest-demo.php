<?php
/**
 * Plugin Name: Rest Demo
 * Description: A simple plugin to demonstrate WP REST API Development
 * Version: 1.0
 * Author: Hasin Hayder
 * Author URI: https://hasin.me
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: rest-demo
 * Domain Path: /languages
 */

class Rest_Demo {
    function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    function register_routes() {
        register_rest_route('rest-demo/v1', '/hello', [
            'methods' => 'GET',
            'callback' => [$this, 'say_hello']
        ]);

        register_rest_route('rest-demo/v1', '/posts', [
            'methods' => 'GET',
            'callback' => [$this, 'get_posts']
        ]);

        register_rest_route('rest-demo/v1', '/posts/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'get_post']
        ]);

        register_rest_route('rest-demo/v1', '/qs', [
            'methods' => 'GET',
            'callback' => [$this, 'get_query_string']
        ]);

        register_rest_route('rest-demo/v1', '/invoice/(?P<id>\d+)/item/(?P<item_id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'invoice_item']
        ]);

        register_rest_route('rest-demo/v1', '/greet/(?P<name>[a-zA-Z0-9-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'greet']
        ]);

        register_rest_route('rest-demo/v1', '/person', [
            'methods' => 'POST',
            'callback' => [$this, 'process_person']
        ]);

        //create a contact form endpoint with GET and POST support
        register_rest_route('rest-demo/v1', '/contact', [
            'methods' => ['GET', 'POST'],
            'callback' => [$this, 'process_contact']
        ]);

        register_rest_route('rest-demo/v1', '/me', [
            'methods' => 'GET',
            'callback' => [$this, 'get_me']
        ]);

        register_rest_route('rest-demo/v1', '/check_permission', [
            'methods' => 'GET',
            'callback' => [$this, 'check_permission'],
            'permission_callback' => function(){
                return current_user_can('manage_options');
            }
        ]);

        //posts endpoint to create post
        register_rest_route('rest-demo/v1', '/posts', [
            'methods' => 'POST',
            'callback' => [$this, 'create_post'],
            'permission_callback' => function(){
                return current_user_can('publish_posts');
            }
        ]);

        //my_posts route to return posts created by the current user
        register_rest_route('rest-demo/v1', '/my_posts', [
            'methods' => 'GET',
            'callback' => [$this, 'get_my_posts'],
            'permission_callback' => function(){
                return is_user_logged_in();
            }
        ]);

    }

    function get_my_posts() {
        $user_id = get_current_user_id();
        $posts = get_posts([
            'post_type' => 'post',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'author' => $user_id
        ]);

        return new WP_REST_Response($posts, 200);
    }

    function create_post($request) {
        $title = $request['title'];
        $content = $request['content'];
        $post = [
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'publish',
            'post_author' => get_current_user_id()
        ];
        $post_id = wp_insert_post($post);
        if ($post_id) {
            return new WP_REST_Response('Post Created', 200);
        } else {
            return new WP_Error('error', 'Post Not Created', ['status' => 500]);
        }
    }

    function check_permission() {
        // if (!current_user_can('manage_options')) {
        //     return new WP_Error('error', 'Unauthorized', ['status' => 401]);
        // }
        return new WP_REST_Response('You can Manage Options', 200);
    }



    function get_me() {
        $user_id = get_current_user_id();
        $user_name = get_user_meta($user_id, 'nickname', true);
        if ($user_id == 0) {
            return new WP_Error('error', 'Unauthorized', ['status' => 401]);
        }
        $user = [
            'id' => $user_id,
            'name' => $user_name
        ];
        return new WP_REST_Response($user, 200);
    }

    function process_contact($request) {
        $method = $request->get_method();
        //if GET return a form
    }

    function process_person($request) {
        $name = $request['name'];
        $email = $request['email'];
        $response = [
            'name' => $name,
            'email' => $email
        ];
        return new WP_REST_Response($response, 200);
    }

    function greet($request) {
        $name = $request['name'];
        $response = [
            'message' => 'Hello ' . $name,
        ];
        return new WP_REST_Response($response, 200);
    }

    function invoice_item($data) {
        $invoice_id = $data['id'];
        $item_id = $data['item_id'];
        $response = [
            'invoice_id' => $invoice_id,
            'item_id' => $item_id
        ];
        return new WP_REST_Response($response, 200);
    }

    function get_query_string($request) {
        $query_string_parameters = $request->get_params();
        // $page_number = $request->get_param('page');
        // if(!$page_number){
        //     $page_number = 1;
        // }
        return new WP_REST_Response($query_string_parameters, 200);
    }

    function get_post($data) {
        $post_id = $data['id'];
        $post = get_post($post_id);
        if (!$post) {
            return new WP_Error('error', 'Post Not Found', ['status' => 404]);
        }
        return new WP_REST_Response($post, 200);
    }

    function get_posts() {
        $posts = get_posts([
            'post_type' => 'post',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ]);

        return new WP_REST_Response($posts, 200);
    }

    function say_hello() {
        // return 'Hello World';
        // return new WP_REST_Response('Hello World', 200);
        $response = [
            'message' => 'Hello World',
        ];
        return new WP_REST_Response($response, 200);
    }
}

new Rest_Demo();
