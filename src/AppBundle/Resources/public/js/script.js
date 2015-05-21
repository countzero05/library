$(document).ready(function () {
  var searchStr = $('#search-str');
  searchStr.find('select[name=q]').select2({
    placeholder: "Поиск по автору или названию книги",
    minimumInputLength: 2,
    ajax: { // instead of writing the function to execute the request we use Select2's convenient helper
      url: searchStr.attr('action'),
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return {q: params.term};
      },
      processResults: function (data, page) { // parse the results into the format expected by Select2.
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
    escapeMarkup: function (markup) { return markup; },
    templateResult: function (result, container, query, escapeMarkup) {
      var markup = [];
      markup.push(result.text);
      return markup.join("");
    },
    templateSelection: function (result, container, query, escapeMarkup) {
      var markup = [];
      markup.push(result.text);
      return markup.join("");
    }
  });

  searchStr.find('select[name=q]').on('select2:select', function (e) {
    document.location.href = e.params.data.id;
  });

  setTimeout(function() {
    $('.select2-choice.select2-default').removeClass('select2-choice').removeClass('select2-default');
  }, 100)

});