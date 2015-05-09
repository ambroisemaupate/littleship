/**
 * Add container JS scripts
 *
 */
var removeTemplate = '<a href="#" class="remove-item btn btn-danger"><i class="glyphicon glyphicon-trash"></i></a>';

$(document).ready(function() {
    var $addDeleteTypes = $('.add-delete-form-type');

    if($addDeleteTypes.length){
        $addDeleteTypes.each(function(index, el) {
            var $type = $(el);
            var $cont = $type.parent();
            var envCount = 0;

            $cont.append('<a href="#" class="add-type-button btn btn-default"><i class="glyphicon glyphicon-plus"></i></a>');
            var $addBtn = $cont.find('.add-type-button');

            $type.find('.form-group').each(function(index, el) {
                var $group = $(el);
                $group.append(removeTemplate);
                $group.find('.remove-item').on('click', function (event) {
                    event.preventDefault();
                    $group = $(event.currentTarget).parent();
                    $group.remove();
                });
            });

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
                //
                // create a new list element and add it to the list
                //var newLi = $('<li></li>').html(newWidget);
                var newLi = $(newWidget);

                newLi.append(removeTemplate);
                newLi.appendTo($type);

                newLi.find('.remove-item').on('click', $.proxy(deleteGroup, this, newLi));
            });
        });
    }


    var deleteGroup = function($element, event) {
        var _this = this;
        event.preventDefault();
        $element.remove();

    };
});