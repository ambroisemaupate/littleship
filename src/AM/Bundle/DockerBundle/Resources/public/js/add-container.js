/**
 * Add container JS scripts
 *
 */
$(document).ready(function() {
    var $addDeleteTypes = $('.add-delete-form-type');

    if($addDeleteTypes.length){
        $addDeleteTypes.each(function(index, el) {
            var $type = $(el);
            var $cont = $type.parent();
            var envCount = 0;

            $cont.append('<a href="#" class="add-type-button btn btn-default">Add</a>');
            var $addBtn = $cont.find('.add-type-button');
            $addBtn.on('click', function (e) {
                e.preventDefault();
                // grab the prototype template
                var newWidget = $type.attr('data-prototype');
                // replace the "__name__" used in the id and name of the prototype
                // with a number that's unique to your emails
                // end name attribute looks like name="contact[emails][2]"
                newWidget = newWidget.replace(/__name__label__/g, envCount);
                newWidget = newWidget.replace(/__name__/g, envCount);
                envCount++;

                // create a new list element and add it to the list
                var newLi = $('<li></li>').html(newWidget);
                newLi.appendTo($type);
            });
        });
    }
});