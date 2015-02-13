$(document).ready(function () {
  var searchStr = $('#search-str');
  searchStr.find('input[name=q]').select2({
    //placeholder: "Select a State",
    //allowClear: true,
    minimumInputLength: 2,
    ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
      url: searchStr.attr('action'),
      dataType: 'json',
      quietMillis: 250,
      data: function (term, page) {
        return {q: term};
      },
      results: function (data, page) { // parse the results into the format expected by Select2.
        // since we are using custom formatting functions we do not need to alter the remote JSON data
        return {
          results: data.map(function (el) {
            return {
              text: (el.doc_type == 'authors') ? ('Автор: ' + el.result) : ('Книга: ' + el.result),
              id: el.path
            }
          })
        }
      },
      cache: true
    },
    formatResult: function (result, container, query, escapeMarkup) {
      var markup = [];
      markup.push(result.text);
      return markup.join("");
    },
    formatSelection: function (result, container, query, escapeMarkup) {
      var markup = [];
      markup.push(result.text);
      return markup.join("");
    }
  });
  searchStr.find('input[name=q]').on('select2-selecting', function (e) {
    document.location.href = e.object.id;
  });

  setTimeout(function() {
    $('.select2-choice.select2-default').removeClass('select2-choice').removeClass('select2-default');
  }, 100)
});