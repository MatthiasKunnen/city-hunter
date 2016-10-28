
$(function () {
    $(".nav-option").click(function () {
        var th = $(this);

        if (th.attr("data-collapse") === "collapsed") {
            var visibleItems = $(".nav-option[data-collapse='visible']");
            visibleItems.attr("data-collapse", "collapsed").siblings(".nav-content").attr("data-collapse", "collapsed").slideUp();
            visibleItems.children("i.nav-option-button").removeClass("fa-angle-down").addClass("fa-angle-up");
            th.attr("data-collapse", "visible");
            th.siblings(".nav-content").attr("data-collapse", "visible").slideDown();
            th.children("i.nav-option-button").removeClass("fa-angle-up").addClass("fa-angle-down");
        }
    });

    $(".notifications > li").each(function (index, item) {
        var $item = $(item),
            liHeight = $item.height(),
            $icon = $($item.children().get(0)),
            iconHeight = $icon.height();
        $icon.css("margin-top", Math.round(liHeight / 2 - iconHeight / 2));
    })
});
