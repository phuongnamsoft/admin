
export default class Toastr {
    constructor(PNS) {
        let _this = this;

        PNS.success = _this.success;
        PNS.error = _this.error;
        PNS.info = _this.info;
        PNS.warning = _this.warning;
    }

    success(message, title, options) {
        toastr.success(message, title, options);
    }

    error(message, title, options) {
        toastr.error(message, title, options);
    }

    info(message, title, options) {
        toastr.info(message, title, options);
    }

    warning(message, title, options) {
        toastr.warning(message, title, options);
    }
}
