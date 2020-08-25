<?php

/**
 * LcPolls_Shortcodes
 *
 * Creating custom shortcode for QOTD
 *
 * @version 1.0
 * @author Nikola Nikoloski
 */

class LcPolls_Shortcodes
{
    private static $instance = null;

    /**
     * Returns a single instance of this class.
     */
    public static function getInstance ()
    {

        if ( null == self::$instance ) {
            self::$instance = new LcPolls_Shortcodes();
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

    public function lc_polls_question() {

        $args = [
            'post_type' => 'lc_polls',
            'post_status' => 'publish',
            'posts_per_page' => 50,
            'ignore_sticky_posts' => true,
            'order' => 'DESC',
            'meta_key' => 'lc_polls_start_date',
            'orderby'   => 'meta_value date',
            'meta_query' => array(
                array(
                    'key' => 'lc_polls_start_date',
                    'value' => date("Y-m-d"),
                    'compare' => '<=',
                    'type' => 'DATE'
                )
            ),
        ];

        $questions = new WP_Query($args);
        $query =  $questions->get_posts();
        $qotd =  $query[0];
        $qCommentsCount = $qotd->comment_count;
        $qMeta = unserialize(LcPolls::lcpolls_get_post_meta('lc_poll_answers', $qotd->ID));
        $qResults = unserialize(LcPolls::lcpolls_get_post_meta('lc_polls_statistics', $qotd->ID));
        $startDate = LcPolls::lcpolls_get_post_meta('lc_polls_start_date', $qotd->ID);
        $isAnswered = false;

        foreach($questions->posts as $key => $value) {
            if($value->ID == $qotd->ID){
                $prevPollId = isset($questions->posts[$key + 1]) ? $questions->posts[$key + 1]->ID : '';
                $newerPollId = isset($questions->posts[$key - 1]) ? $questions->posts[$key - 1]->ID: '';
                break;
            }
        }

        $commentArgs = array(
            'status'  => 'approve',
            'number'  => '5',
            'post_id' => $qotd->ID,
        );
        $comments = get_comments( $commentArgs );

        if(is_user_logged_in()) {
            $user = wp_get_current_user();
            $isAnswered = unserialize(LcPolls::lcpolls_get_user_meta('poll_' .$qotd->ID, $user->ID ));
        } else {
            if(isset($_COOKIE['poll_' . $qotd->ID])) {
                $isAnswered = (array)json_decode(html_entity_decode($_COOKIE['poll_' . $qotd->ID]));
            }
        }

        $data = (object)[
            'questionID'=> $qotd->ID,
            'answerType'=> $qMeta->answerType,
            'questionPrivacy'=> $qMeta->questionPrivacy,
            'question' => $qotd->post_title,
            'options'  => $qMeta->options,
            'results' => $qResults,
            'answeredData' => $isAnswered,
            'commentCount' => $qCommentsCount,
            'comments' => $comments,
            'startDate' => $startDate,
            'prevPollId' => $prevPollId,
            'newerPoll' => $newerPollId
        ];

        if($qMeta->questionPrivacy === 'private') {
            if(is_user_logged_in()) {
                return $isAnswered ? LcPolls::view('shortcodes/questions/answered-question', $data) : LcPolls::view('shortcodes/questions/question', $data);
            } else {
                return LcPolls::view('shortcodes/auth/login', $data);
            }
        }

        return $isAnswered ? LcPolls::view('shortcodes/questions/answered-question', $data) : LcPolls::view('shortcodes/questions/question', $data);
    }


    /**
     * Initializes WordPress hooks
     */
    public function init ()
    {
        add_shortcode('lc_polls_question', [$this, 'lc_polls_question']);
    }

}
