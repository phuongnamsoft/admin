import Dropdown from "../../../adminlte/js/Dropdown";

let $document = $(document);

let defaultActions = {
    // 刷新按钮
    refresh (action, PNS) {
        $document.on('click', action, function () {
            PNS.reload($(this).data('url'));
        });
    },
    // 删除按钮初始化
    delete (action, PNS) {
        let lang = PNS.lang;

        $document.on('click', action, function() {
            let url = $(this).data('url'),
                redirect = $(this).data('redirect'),
                msg = $(this).data('message');

            PNS.confirm(lang.delete_confirm, msg, function () {
                PNS.NP.start();
                $.delete({
                    url: url,
                    success: function (response) {
                        PNS.NP.done();

                        response.data.detail = msg;

                        if (redirect && ! response.data.then) {
                            response.data.then = {action: 'redirect', value: redirect}
                        }

                        PNS.handleJsonResponse(response);
                    }
                });
            });
        });
    },
    // 批量删除按钮初始化
    'batch-delete' (action, PNS) {
        $document.on('click', action, function() {
            let url = $(this).data('url'),
                name = $(this).data('name'),
                redirect = $(this).data('redirect'),
                keys = PNS.grid.selected(name),
                lang = PNS.lang;

            if (! keys.length) {
                return;
            }
            let msg = 'ID - ' + keys.join(', ');

            PNS.confirm(lang.delete_confirm, msg, function () {
                PNS.NP.start();
                $.delete({
                    url: url + '/' + keys.join(','),
                    success: function (response) {
                        PNS.NP.done();

                        if (redirect && ! response.data.then) {
                            response.data.then = {action: 'redirect', value: redirect}
                        }

                        PNS.handleJsonResponse(response);
                    }
                });
            });
        });
    },

    // 图片预览
    'preview-img' (action, PNS) {
        $document.on('click', action, function () {
            return PNS.helpers.previewImage($(this).attr('src'));
        });
    },

    'popover' (action, PNS) {
        PNS.onPjaxComplete(function () {
            $('.popover').remove();
        }, false);

        $document.on('click', action, function () {
            $(this).popover()
        });
    },

    'box-actions' () {
        $document.on('click', '.box [data-action="collapse"]', function (e) {
            e.preventDefault();

            $(this).find('i').toggleClass('icon-minus icon-plus');

            $(this).closest('.box').find('.box-body').first().collapse("toggle");
        });

        // Close box
        $document.on('click', '.box [data-action="remove"]', function () {
            $(this).closest(".box").removeClass().slideUp("fast");
        });
    },

    dropdown () {
        function hide() {
            $('.dropdown-menu').removeClass('show')
        }
        $document.off('click', document, hide)
        $document.on('click', hide);

        function toggle(event) {
            var $this = $(this);

            $('.dropdown-menu').each(function () {
                if ($this.next()[0] !== this) {
                    $(this).removeClass('show');
                }
            });

            $this.Dropdown('toggleSubmenu')
        }

        function fix(event) {
            event.preventDefault()
            event.stopPropagation()

            let $this = $(this);

            setTimeout(function() {
                $this.Dropdown('fixPosition')
            }, 1)
        }

        let selector = '[data-toggle="dropdown"]';

        $document.off('click',selector).on('click', selector, toggle).on('click', selector, fix);
    },
};

export default class DataActions {
    constructor(PNS) {
        let actions = $.extend(defaultActions, PNS.actions()),
            name;

        for (name in actions) {
            actions[name](`[data-action="${name}"]`, PNS);
        }
    }
}
