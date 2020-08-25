<div id="poll_container">
    <div id="lc_polls_from" class="question-of-the-day">
        <h2 class="lc-poll-heading"><?php _e('Question of the day'); ?></h2>
        <p class="lc-poll-question"><?php echo $viewData->question; ?></p>
        <form
                id="poll_"<?php echo $viewData->questionID;?>
                class="lc_poll_form"
                data-poll-id="<?php echo $viewData->questionID; ?>"
                data-prev-poll-id="<?php echo $viewData->prevPollId; ?>"
        >
            <?php
            foreach ($viewData->options as $id=>$option): ?>
                <div class="">
                    <label class="lc-polls-input-label" value="1" for="<?php echo $id; ?>">
                        <input type="<?php echo $viewData->answerType === 'multiple_choice' ? 'checkbox' : 'radio'; ?>" name="question_<?php echo $viewData->answerType === 'multiple_choice' ? $viewData->questionID . '[]' : $viewData->questionID; ?>" class="lc-polls-option-input <?php echo $viewData->answerType === 'multiple_choice' ? 'checkbox' : 'radio'; ?>" id="<?php echo $id; ?>">
                        <?php echo $option; ?>
                    </label>
                </div>
            <?php endforeach; ?>
            <div class="no-selection-error"><?php _e('Please select an option', 'lcpolls'); ?></div>
            <button
                    data-poll-id="<?php echo $viewData->questionID; ?>"
                    id="lc_poll_submit" type="submit"
                    data-prev-post-id="<?php echo $viewData->prevPollId ?>"
                    class="lc-polls-btn lc-polls-btn-primary">
                <?php _e('Submit Answer', 'lcpolls'); ?>
                <div class="lc-polls-loader loadingBtn"></div>
            </button>
        </form>

        <div class="lc-polls-footer-results results lc-polls-gray-text">
            <?php
            if($viewData->results):
                $answ = $viewData->results->total === 1 ? __('answer', 'lcpolls') : __('answers', 'lcpolls');
                echo sprintf(__('We received %s %s', 'lcpolls'), $viewData->results->total, $answ);
            else:
                _e('No answers for this question yet. Be the first one to answer?',  'lcpolls');
            endif;
            ?>
        </div>

        <div class="lc-polls-navigation">
            <button
                    data-nav-direction="newer"
                    data-newer-poll="<?php echo isset($viewData->newerPoll) ? $viewData->newerPoll : ''; ?>"
                    class="lc-left poll-nav">
                <i class="icon icon-arrow-left-circle"></i>
            </button>
            <button
                    data-nav-direction="older"
                    data-older-poll="<?php echo $viewData->prevPollId; ?>"
                    data-current-poll="<?php echo $viewData->questionID; ?>"
                    class="lc-right poll-nav">
                <i class="icon icon-arrow-right-circle"></i>
            </button>

            <div class="lc-polls-loader prev-next loadingBtn"></div>
        </div>
    </div>
</div>