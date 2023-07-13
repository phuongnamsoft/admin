
export default class Validator {
    constructor(PNS) {
        PNS.validator = this;
    }

    // 注册自定义验证器
    extend(rule, callback, message) {
        let DEFAULTS = $.fn.validator.Constructor.DEFAULTS;

        DEFAULTS.custom[rule] = callback;
        DEFAULTS.errors[rule] = message || null;
    }
}