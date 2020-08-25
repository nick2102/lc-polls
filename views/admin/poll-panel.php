<div class="wrap">
    <div id="post-body">
        <div>
            <?php  if ($qMeta && count((array)$qMeta->options) > 0) : ?>
                <div class="trainee-field-set">
                    <label> <?php _e('Answer type', 'lcpolls'); ?></label>
                    <select id="questionType" name="mandatory">
                        <option <?php selected('single_choice', $qMeta->answerType); ?> value="single_choice"><?php _e('Single Choice', 'lcpolls'); ?></option>
                        <option <?php selected('multiple_choice', $qMeta->answerType); ?> value="multiple_choice"><?php _e('Multiple Choices', 'lcpolls'); ?></option>
                    </select>
                    <br>
                    <br>
                    <label> <?php _e('Public or poll for loged in users', 'lcpolls'); ?></label>
                    <select id="questionPrivacy" name="questionPrivacy">
                        <option <?php selected('private', $qMeta->questionPrivacy); ?> value="private"><?php _e('Private', 'lcpolls'); ?></option>
                        <option <?php selected('public', $qMeta->questionPrivacy); ?> value="public"><?php _e('Public', 'lcpolls'); ?></option>
                    </select>

                    <hr>
                    <a id="add_qotd_option"
                       data-tab=".questionnaire-structure-options-list"
                       data-count-options="2"
                       class="qotd-o-add button button-primary customize load-customize hide-if-no-customize">
                        +<?php _e('Add Option', 'lcpolls'); ?>
                    </a>
                    <div class="questionnaire-structure-options-list">

                        <?php foreach ($qMeta->options as $id=>$option): ?>
                            <div class="questionnaire-options">
                                <div class="trainee-reorder-slide option_li_sides">
                                    <span class="dashicons dashicons-move"></span>
                                </div>

                                <div style="width: 100%">
                                    <label> <?php _e('Option', 'lcpolls'); ?> </label>
                                    <input id="<?php echo $id; ?>" name="optionName" type="text" placeholder="" value="<?php echo $option; ?>">
                                </div>

                                <div class="lctrainee-delete-slide remove_field option_li_sides">
                                    <span class="dashicons dashicons-trash"></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="trainee-field-set">
                    <label> <?php _e('Answer type', 'lcpolls'); ?></label>
                    <select id="questionType" name="mandatory">
                        <option value="single_choice"><?php _e('Single Choice', 'lcpolls'); ?></option>
                        <option value="multiple_choice"><?php _e('Multiple Choices', 'lcpolls'); ?></option>
                    </select>
                    <br>
                    <br>
                    <label> <?php _e('Public or poll for loged in users', 'lcpolls'); ?></label>
                    <select id="questionPrivacy" name="questionPrivacy">
                        <option value="private"><?php _e('Private', 'lcpolls'); ?></option>
                        <option value="public"><?php _e('Public', 'lcpolls'); ?></option>
                    </select>

                    <hr>
                    <a id="add_qotd_option"
                       data-tab=".questionnaire-structure-options-list"
                       data-count-options="2"
                       class="qotd-o-add button button-primary customize load-customize hide-if-no-customize">
                        +<?php _e('Add Option', 'lcpolls'); ?>
                    </a>

                    <div class="questionnaire-structure-options-list">

                    </div>
                </div>
            <?php endif;?>
        </div>
    </div>
</div>

<style>
    /*CONTENT*/
    .tabLongTitle,
    .trainee-field-set {
        padding: 15px 50px 15px 50px;
        margin: 15px 0;
        border: 1px solid #cacaca;
        background: #fff;
        position: relative;
    }

    .trainee-field-set label {
        display: block;
        width: 100%;
        margin-bottom: 3px;
    }

    .tabLongTitle input[type="text"],
    .tabLongTitle textarea,
    .trainee-field-set input[type="text"] {
        width: 100%;
        border-radius: 0 !important;
        border: 1px solid #d2d2d2;
        box-shadow: none;
        margin-bottom: 10px;
    }

    .questionnaire-options {
        padding: 10px 50px 10px 50px;
        margin: 10px 0;
        display: flex;
        flex-wrap: wrap;
        border: 1px solid #cacaca;
        background: #fff;
        position: relative;
        max-width: 780px;
    }

    .questionnaire-options > div {
        padding: 0 10px ;
    }


    .lc_option_icon_box {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .lc_option_icon_box button {
        margin-left: 5px !important;
        display: block !important;
        margin-bottom: 12px !important;
    }

    .trainee-reorder-slide {
        left: 0;
        cursor: grab;
    }

    .lctrainee-delete-slide {
        right: 0;
        cursor: pointer;
    }

    .option_li_sides {
        display: flex;
        position: absolute;
        width: 30px;
        border-right: 1px solid #eee;
        top: 0;
        bottom: 0;
        align-items: center;
        justify-content: center;
        background: #f2f2f2;
        padding: 0 !important;
    }

    .addTabsWrapper {
        display: flex;
        align-items: center;
    }

    #addTabs {
        display: flex;
        align-items: center;
    }

    .addTabsWrapper {
        max-width: 500px;
        padding: 15px 10px;
        background: #f2f2f2;
        margin: 0 15px;
        min-height: 90px;
    }

    .addTabsWrapper label,
    .addTabsWrapper input {
        width: 100%;
        margin-bottom: 5px;
    }

    #shortcode {
        width: 70%;
        flex-direction: column;
        align-items: center;
        align-content: center;
    }

    /* Multi select */
    .ms-container {
        width: 100% !important;
    }
    .ms-container .ms-selectable, .ms-container .ms-selection {
        width: 49% !important;
    }

</style>

<script>
    //Global translations
    window.lcTranslations = {
        option: '<?php _e('Option', 'lcpolls'); ?>',
    }
    //Global functions
    function generateOptionsGroup() {
        var random = Math.random().toString(36).slice(-5);
        var group = '<div class="questionnaire-options">' +
            '           <div class="trainee-reorder-slide option_li_sides">' +
            '                <span class="dashicons dashicons-move"></span>' +
            '           </div>' +
            '           <div style="width: 100%">' +
            '             <label> '+ window.lcTranslations.option +' </label>' +
            '             <input id="option_'+ random +'" name="optionName" type="text" placeholder="">' +
            '           </div>' +
            '           <div class="lctrainee-delete-slide remove_field option_li_sides">' +
            '              <span class="dashicons dashicons-trash"></span>' +
            '           </div>'+
            '        </div>';

        return group;
    }

    function generateOptionsObject(type, questionPrivacy, form) {
        var inputs = form.find('input');
        var options = {};
        Object.keys(inputs).forEach(function(inputsKey) {
            if (Number.isInteger(parseInt(inputsKey))) {
                if(jQuery(inputs[inputsKey]).val().match(/^ *$/) === null ) {
                    var id = jQuery(inputs[inputsKey]).attr('id');
                    options[id] = jQuery(inputs[inputsKey]).val();
                }
            }
        });
        return {
            answerType: type,
            questionPrivacy: questionPrivacy,
            options: options
        };
    }

    jQuery(document).ready( function($) {
        $( "#lc_polls_start_date" ).datepicker({
            minDate: 0,
            dateFormat: "yy-mm-dd",
            altFormat: "yy-mm-dd"
        });
        //INIT SORTABLE
        $( ".questionnaire-structure-options-list" ).sortable({
            handle: ".trainee-reorder-slide",
        }).disableSelection();

        //Appned save button
        $('#publishing-action').prepend('<a style="width: 100%; text-align: center" class="button button-primary button-large lc-save-question-cpt">Save</a>');

        //ADD DELETE OPTIONS
        $('body').on('click', '.qotd-o-add', function (e) {
            e.preventDefault();
            var qCount = $(this).attr('data-count-options');
            var groupId = parseInt(qCount) + 1;
            var html = generateOptionsGroup();
            var tab = $(this).attr('data-tab');
            $(tab).append(html);

            $( ".questionnaire-structure-options-list" ).sortable({
                handle: ".trainee-reorder-slide",
            }).disableSelection();

            $(this).attr('data-count-options', groupId);
        });

        $('body').on('click', '.remove_field', function (e) {
            if(confirm("Delete this item?")){
                var fieldSet = $(this).parent();
                fieldSet.remove();
            }
        });

        //Save Question
        $('body').on('click', '.lc-save-question-cpt', function (e) {
            e.preventDefault();
            var form = $('.trainee-field-set');
            var questionType = $('#questionType').val();
            var questionPrivacy = $('#questionPrivacy').val();
            var jsonField = $('input[name="lc_poll_answers"]');

            var formData = generateOptionsObject(questionType, questionPrivacy, form);
            console.log(formData);
            jsonField.val(JSON.stringify(formData));
            $('#publish').trigger('click');
        });
    });
</script>