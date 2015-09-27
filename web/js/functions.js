function doDisplayCollapse(element) {
    $(element).collapse('toggle');
}

function remove() {
    if(selected_id !== undefined) {
        var remove_id = selected_id;
        selected_id = undefined;
        $('#myModal').modal('hide');
        redirect('/delete/'+remove_id);
    }
}

var selected_id;

$( document ).ready(function() {
    $('#myModal').on('show.bs.modal', function (event) {
      var button = $(event.relatedTarget);
      selected_id = button.data('id');
    });
});
