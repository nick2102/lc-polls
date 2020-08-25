<div id="poll_container">
    <div id="lc_polls_from" class="question-of-the-day">
        <h2 class="lc-poll-heading"><?php _e('Question of the day'); ?></h2>
        <p class="lc-poll-question"><?php echo $viewData->question; ?></p>
        <form
            id="poll_"<?php echo $viewData->questionID;?>
            data-poll-is-next = "isNext"
            class="lc_poll_form"
            data-poll-id="<?php echo $viewData->questionID; ?>"
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
            <hr>
            <div class="statistics-footer">
                <div>
                    <button
                            data-poll-id="<?php echo $viewData->questionID; ?>"
                            data-poll-is-next="isNext"
                            id="lc_poll_submit" type="submit"
                            class="lc-polls-btn lc-polls-btn-primary">
                        <?php _e('Answer and Join Discussion', 'lcpolls'); ?>
                        <div class="lc-polls-loader loadingBtn"></div>
                    </button>

                    <div class="lc-polls-footer-results results lc-polls-gray-text">
                        <?php
                        if($viewData->results):
                            $answ = $viewData->results->total === 1 ? __('answer', 'lcpolls') : __('answers', 'lcpolls');
                            echo sprintf(__('We received %s %s and %s comments', 'lcpolls'), $viewData->results->total, $answ, $viewData->comments_count);
                        else:
                            _e('No answers for this question yet. Be the first one to answer?',  'lcpolls');
                        endif;
                        ?>
                    </div>

                </div>
                <div>
                    <?php if($viewData->prevPollId != ''): ?>
                        <button id="lc-polls-next-q"
                                data-nav-direction="older"
                                data-older-poll="<?php echo $viewData->prevPollId; ?>"
                                data-current-poll="<?php echo $viewData->questionID; ?>"
                                data-current-question-date="<?php echo $viewData->startDate; ?>"
                                class="lc-polls-btn"><?php _e('Next Question', 'lcpolls') ?>
                            <img src="<?php echo LCPOLLS_PLUGIN_URL; ?>/assets/images/angle-right.svg" alt="">
                            <div class="lc-polls-loader loadingBtn"></div>
                        </button>
                        <p class="prev-question-title"><?php echo get_the_title($viewData->prevPollId); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>