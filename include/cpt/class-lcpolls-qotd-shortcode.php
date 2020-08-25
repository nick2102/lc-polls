<?php

/**
 * LcPolls_Qotd_Shortcode
 *
 * Creating custom shortcode for QOTD
 *
 * @version 1.0
 * @author Nikola Nikoloski
 */

class LcPolls_Qotd_Shortcode
{
    private static $instance = null;

    /**
     * Returns a single instance of this class.
     */
    public static function getInstance ()
    {

        if ( null == self::$instance ) {
            self::$instance = new LcPolls_Qotd_Shortcode();
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

    public function question_of_the_day() {

        $args = [
            'post_type' => 'qotd',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'ignore_sticky_posts' => true,
            'order' => 'DESC',
            'orderby' => 'date'
        ];

        $questions = new WP_Query($args);
        $qotd = $questions->get_posts()[0];
        $qMeta = unserialize(get_post_meta($qotd->ID, 'lc_qotd_answers', true));

        $data = (object)[
            'questionID'=> $qotd->ID,
            'answerType'=> $qMeta->answerType,
            'question' => $qotd->post_title,
            'options'  => $qMeta->options
        ];

        ob_start();
        include LCSTARTER_THEME_PATH . '/admin/views/question.php';

        return ob_get_clean();
    }


    /**
     * Initializes WordPress hooks
     */
    public function init ()
    {
        add_shortcode('question_of_the_day', [$this, 'question_of_the_day']);
    }

}
