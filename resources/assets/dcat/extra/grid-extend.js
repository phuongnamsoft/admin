
import Helper from './Grid/Helper'
import Tree from './Grid/Tree'
import Orderable from './Grid/Orderable'
import AsyncTable from './Grid/AsyncTable'

(function (w, $) {
    let PNS = w.PNS,
        h = new Helper();

    // 树形表格
    PNS.grid.Tree = function (opts) {
        return new Tree(h, opts);
    };

    // 列表行可排序
    PNS.grid.Orderable = function (opts) {
        return new Orderable(h, opts);
    };

    // 异步表格
    PNS.grid.AsyncTable =function (opts) {
        return new AsyncTable(opts)
    }
})(window, jQuery);