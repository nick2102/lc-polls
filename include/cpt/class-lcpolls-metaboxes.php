<?php

/**
 * LcPolls_Metaboxes
 *
 * Creating custom post types Metaboxes
 *
 * @version 1.0
 * @author Nikola Nikoloski
 */

class LcPolls_Metaboxes
{
    private static $instance = null;

    /**
     * Returns a single instance of this class.
     */
    public static function getInstance ()
    {

        if ( null == self::$instance ) {
            self::$instance = new LcPolls_Metaboxes();
        }

        return self::$instance;
    }

    /**
     * class constructor
     */
    public function __construct ()
    {
        // class initialization
        $this->init();
    }



    /**
     * Initializes WordPress hooks
     */
    public function init ()
    {
        LcPolls_Question_Metaboxes::getInstance();
    }

}
