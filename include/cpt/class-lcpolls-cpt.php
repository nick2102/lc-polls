<?php
/**
 * LcPolls_Cpt
 *
 * Creating custom post types
 *
 * @version 1.0
 * @author Nikola Nikoloski
 */

class LcPolls_Cpt
{
    private static $instance = null;

    /**
     * Returns a single instance of this class.
     */
    public static function getInstance ()
    {

        if ( null == self::$instance ) {
            self::$instance = new LcPolls_Cpt();
        }

        return self::$instance;
    }

    /**
     * class constructor
     */
    public function __construct ()
    {
        // plugin initialization
        $this->init();
    }

    /**
     * Register Custom Post Type
     */
    public function lc_polls_cpt()
    {

        $labels = [
            'name'                  => _x( 'LC Polls', 'Post Type General Name', 'lcpolls' ),
            'singular_name'         => _x( 'LC Poll', 'Post Type Singular Name', 'lcpolls' ),
            'menu_name'             => __( 'LC Polls', 'lcpolls' ),
            'name_admin_bar'        => __( 'LC Polls', 'lcpolls' ),
            'archives'              => __( 'Polls Archives', 'lcpolls' ),
            'attributes'            => __( 'Poll Attributes', 'lcpolls' ),
            'parent_item_colon'     => __( 'Poll Parent Item:', 'lcpolls' ),
            'all_items'             => __( 'All Polls', 'lcpolls' ),
            'add_new_item'          => __( 'Add New Poll', 'lcpolls' ),
            'add_new'               => __( 'Add New Poll', 'lcpolls' ),
            'new_item'              => __( 'New Poll', 'lcpolls' ),
            'edit_item'             => __( 'Edit Poll', 'lcpolls' ),
            'update_item'           => __( 'Update Poll', 'lcpolls' ),
            'view_item'             => __( 'View Poll', 'lcpolls' ),
            'view_items'            => __( 'View Poll', 'lcpolls' ),
            'search_items'          => __( 'Search Poll', 'lcpolls' ),
            'not_found'             => __( 'Not found', 'lcpolls' ),
            'not_found_in_trash'    => __( 'Not found in Trash', 'lcpolls' ),
            'featured_image'        => __( 'Featured Image', 'lcpolls' ),
            'set_featured_image'    => __( 'Set featured image', 'lcpolls' ),
            'remove_featured_image' => __( 'Remove featured image', 'lcpolls' ),
            'use_featured_image'    => __( 'Use as featured image', 'lcpolls' ),
            'insert_into_item'      => __( 'Insert into item', 'lcpolls' ),
            'uploaded_to_this_item' => __( 'Uploaded to this Poll', 'lcpolls' ),
            'items_list'            => __( 'Poll list', 'lcpolls' ),
            'items_list_navigation' => __( 'Polls list navigation', 'lcpolls' ),
            'filter_items_list'     => __( 'Filter Poll list', 'lcpolls' ),
        ];

        $args = array(
            'label'                 => __( 'LC Polls', 'lcpolls' ),
            'description'           => __( 'LC Polls Posts', 'lcpolls' ),
            'labels'                => $labels,
            'supports'              => array( 'title', 'comments' ),
            'taxonomies'            => array( '' ),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-chart-bar',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => true,
            'capability_type'       => 'post',
            'show_in_rest'          => true,
            'rest_base'             => 'lc_polls',
        );
        register_post_type( 'lc_polls', $args );

    }

    /**
     * Change default title placeholder for cpt
     */

    public function lc_title_place_holder($title , $post){

        if( $post->post_type == 'lc_polls' ){
            $company_title = __('Enter Your Question Here', 'lcpolls');
            return $company_title;
        }
        return $title;
    }

    /**
     * Get the next post id
     * @param $id
     * @return int
     */
    public static function getNextPostId($id)
    {
        global $post;
        $oldGlobal = $post;
        $post = get_post( $id );
        $next_post = get_next_post();
        $post = $oldGlobal;
        if ( '' == $next_post )
            return 0;
        return $next_post->ID;
    }

    /**
     * Get the prev post id
     * @param $id
     * @return int
     */
    public static function getPreviousPostId($id)
    {
        // Get all pages under this section
        $post = get_post($id);
        $get_pages_query = 'order=desc&post_type=lc_polls&meta_key=lc_polls_start_date&orderby=meta_value';
        $get_pages = get_posts($get_pages_query);
        $next_post = get_next_post();
        var_dump($next_post);
//        var_dump($get_pages);
        $prev_page_id = '';

        // Count results
        $page_count = count($get_pages);

        for($p=0; $p < $page_count; $p++) {
            // get the array key for our entry
            if ($id == $get_pages[$p]->ID) break;
        }

        // assign our next & previous keys
        $prev_key = $p-1;
        $last_key = $page_count-1;

        // if there isn't a value assigned for the previous key, go all the way to the end
        if (isset($get_pages[$prev_key])) {
            $prev_page_id = $get_pages[$prev_key]->ID;
        }

        return $prev_page_id;
    }

    /**
     * Initializes WordPress hooks
     */
    public function init ()
    {
        add_action( 'init', [$this, 'lc_polls_cpt'], 0 );
        add_filter('enter_title_here', [$this, 'lc_title_place_holder' ] , 20 , 2 );
    }

}
