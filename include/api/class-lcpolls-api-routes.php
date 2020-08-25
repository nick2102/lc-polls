<?php
/**
 * LcPolls_Api_Routes
 *
 * Initiate custom wordpress api routes
 *
 * @version 1.0
 * @author Nikola Nikoloski
 */

class LcPolls_Api_Routes
{
    private static $instance = null;
    const METHOD = 'methods';
    const CALLBACK = 'callback';
    const PERMISSION_CALLBACK = 'permission_callback';

    /**
     * Returns a single instance of this class.
     */
    public static function getInstance()
    {

        if (null == self::$instance) {
            self::$instance = new LcPolls_Api_Routes();
        }

        return self::$instance;
    }

    /**
     * @var string Api namespace
     */
    protected $namespace = LCPOLLS_PLUGIN_API_NAMESPACE;


    /**
     * class constructor
     */
    public function __construct()
    {
        // plugin initialization
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes()
    {
        /**********  GET Routes  **********/

        // Load more commets
        register_rest_route($this->namespace, '/load-more-comments', array(
            self::METHOD => WP_REST_Server::READABLE,
            self::CALLBACK => [ new LcPolls_Api_Controller, 'loadMoreComments' ],
        ));

        register_rest_route($this->namespace, '/get-old-question', array(
            self::METHOD => WP_REST_Server::READABLE,
            self::CALLBACK => [ new LcPolls_Api_Controller, 'getOlderQuestion' ],
        ));

        register_rest_route($this->namespace, '/nav-questions', array(
            self::METHOD => WP_REST_Server::READABLE,
            self::CALLBACK => [ new LcPolls_Api_Controller, 'navQuestions' ],
        ));



        /**********  POST Routes  **********/

        // Save polls
        register_rest_route($this->namespace, '/send-poll-answer', array(
            self::METHOD => WP_REST_Server::CREATABLE,
            self::CALLBACK => [ new LcPolls_Api_Controller, 'send_poll_answer' ],
        ));

        //view results
        register_rest_route($this->namespace, '/view-results', array(
            self::METHOD => WP_REST_Server::CREATABLE,
            self::CALLBACK => [ new LcPolls_Api_Controller, 'viewSavedStatistics' ],
        ));

        //Save Comment
        register_rest_route($this->namespace, '/post-question-comment', array(
            self::METHOD => WP_REST_Server::CREATABLE,
            self::CALLBACK => [ new LcPolls_Api_Controller, 'postQuestionComment' ],
        ));
    }
}