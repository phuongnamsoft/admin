
export default class Translator{
    constructor(PNS, lang) {
        this.dcat = PNS;
        this.lang = lang;

        for (let i in lang) {
            if (! PNS.helpers.isset(this, i)) {
                this[i] = lang[i];
            }
        }
    }

    /**
     * 翻译
     *
     * @example
     *      this.trans('name')
     *      this.trans('selected_options', {':num': 18}) // :num options selected
     *
     * @param {string} label
     * @param {object} replace
     * @returns {*}
     */
    trans(label, replace) {
        let _this = this,
            helpers = _this.dcat.helpers;

        if (typeof _this.lang !== 'object') {
            return label;
        }

        var text = helpers.get(_this.lang, label), i;
        if (! helpers.isset(text)) {
            return label;
        }

        if (! replace) {
            return text;
        }

        for (i in replace) {
            text = helpers.replace(text, ':'+i, replace[i]);
        }

        return text;
    }
}
