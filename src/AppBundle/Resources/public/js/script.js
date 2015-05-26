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
      processResults: function (data/*, page*/) { // parse the results into the format expected by Select2.
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
    escapeMarkup: function (markup) {
      return markup;
    },
    templateResult: function (result/*, container, query, escapeMarkup*/) {
      var markup = [];
      markup.push(result.text);
      return markup.join("");
    },
    templateSelection: function (result/*, container, query, escapeMarkup*/) {
      var markup = [];
      markup.push(result.text);
      return markup.join("");
    }
  });

  searchStr.find('select[name=q]').on('select2:select', function (e) {
    document.location.href = e.params.data.id;
  });

  setTimeout(function () {
    $('.select2-choice.select2-default').removeClass('select2-choice').removeClass('select2-default');
  }, 100);

  if (haveLocalStorage()) {
    new Library().run()
  }

});

function haveLocalStorage() {
  return typeof(localStorage) !== 'undefined';
}

String.prototype.hashCode = function () {
  var hash = 0, i, chr, len;
  if (this.length == 0) return hash;
  for (i = 0, len = this.length; i < len; i++) {
    chr = this.charCodeAt(i);
    hash = ((hash << 5) - hash) + chr;
    hash |= 0; // Convert to 32bit integer
  }
  return hash;
};

function Library() {
  this.books = {};
}

Library.prototype.run = function () {
  this.load().checkPage().showBtn();

  return this;
};

Library.prototype.showBtn = function () {
  if (!$.isEmptyObject(this.books)) {
    var btn = $('#book-read');
    btn.on('click', function (e) {
      e.preventDefault();
    });
    btn.removeClass('hide').popover({
      animation: true,
      html: true,
      placement: 'bottom',
      template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content"></div></div>',
      title: 'Последнее прочитаное: ',
      content: this.getListOfBooks(),
      trigger: 'click'
    });

  }

  return this;
};

Library.prototype.getListOfBooks = function () {
  var s = '';
  var books = [];
  for (var key in this.books) {
    if (this.books.hasOwnProperty(key)) {
      books.push(this.books[key]);
    }
  }

  books.sort(function (a, b) {
    return a.time < b.time;
  });

  for (var i = 0; i < books.length; i++) {
    var book = books[i];
    s += '<a href="' + book.url + '">' + book.author + '. "' + book.name + '"</a>';
  }

  return s;
};

Library.prototype.checkPage = function () {
  if ($('[itemtype="http://schema.org/Article"]').length === 0)
    return this;

  var bookName = $('h1[itemprop="name"]').html().trim();
  var authorName = $('span[itemprop="name"]').html().trim();

  var key = String(authorName + bookName).hashCode();

  if (this.books[key] === undefined) {
    this.books[key] = {
      author: authorName,
      name: bookName,
      url: document.location.href,
      time: new Date().getTime()
    };
    this.save();
  } else {
    if (this.books[key].url !== document.location.href) {
      this.books[key].url = document.location.href;
      this.books[key].time = new Date().getTime();
      this.save();
    }
  }

  return this;
};

Library.prototype.load = function () {
  this.books = JSON.parse(localStorage.getItem('books')) || {};

  return this;
};

Library.prototype.save = function () {
  if (Object.keys(this.books).length > 5) {
    var firstKey = null;
    var firstTm = new Date().getTime();
    for (var key in this.books) {
      if (this.books.hasOwnProperty(key)) {
        var book = this.books[key];

        if (firstTm > book.time) {
          firstTm = book.time;
          firstKey = key;
        }
      }
    }
    if (firstKey !== null) {
      delete this.books[firstKey];
    }
  }

  localStorage.setItem('books', JSON.stringify(this.books));

  return this;
};

