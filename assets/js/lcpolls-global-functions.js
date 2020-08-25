function generatePollAnswer(inputs) {
    var answers = {};
    Object.keys(inputs).forEach(function (key) {
        if(Number.isInteger(parseInt(key))){
            var input = $(inputs[key]);
            answers[input.attr('id')] = input.prop('checked') ? 1 : 0;
        }
    })
    return answers;
}