(function ($) {
    $.fn.tabs = function (props) {
        let $tabs = $(this);
        return $tabs.each(function () {
            let $this = $(this),
                $content = $this.find("[data-content]")

            $content.filter(":not(.active)").hide();
            let $tabTitles = $this.find(`[data-target]`);

            $tabTitles.click(function () {
                let $tab = $(this),
                    target = $tab.data("target"),
                    $tabContent = $this.find(`[data-content="${target}"]`);

                if (!$tabContent.length) return false;

                $content.hide();
                $tabContent.show();

                $tabTitles.removeClass("active");
                $content.removeClass("active");

                $tab.addClass("active");
                $tabContent.addClass("active");
            });

        });
    }
})($)
