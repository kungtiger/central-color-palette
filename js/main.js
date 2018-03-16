(function ($, jekyll) {
    var requestVar = function (key) {
        var query = window.location.search.substring(1);
        var vars = query.split('&');

        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split('=');

            if (pair[0] === key) {
                return pair[1];
            }
        }
        return null;
    };
    $(function () {
        var $body = $(document.body);
        $('#menu').on('click', function () {
            $body.toggleClass('nav-open');
        });

        var current_url = window.location.href.replace(jekyll.url, '');
        var $nav_item = $('#sidebar a[href="' + current_url + '"]').eq(0);
        if ($nav_item.length) {
            var nav_item = $nav_item[0];
            if (typeof nav_item.scrollIntoView == 'function') {
                nav_item.scrollIntoView();
            }
        }
    });
})(jQuery, jekyll);