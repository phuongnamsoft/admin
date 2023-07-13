
/*=========================================================================================
  File Name: app.js
  Description: PNS Admin JS脚本.
  ----------------------------------------------------------------------------------------
  Item Name: PNS Admin
  Author: Jqh
  Author URL: https://github.com/jqhph
==========================================================================================*/

import PNS from './PNS'

import NProgress from './NProgress/NProgress.min'
import Ajax from './extensions/Ajax'
import Toastr from './extensions/Toastr'
import SweetAlert2 from './extensions/SweetAlert2'
import RowSelector from './extensions/RowSelector'
import Grid from './extensions/Grid'
import Form from './extensions/Form'
import DialogForm from './extensions/DialogForm'
import Loading from './extensions/Loading'
import AssetsLoader from './extensions/AssetsLoader'
import Slider from './extensions/Slider'
import Color from './extensions/Color'
import Validator from './extensions/Validator'
import DarkMode from './extensions/DarkMode'

import Menu from './bootstrappers/Menu'
import Footer from './bootstrappers/Footer'
import Pjax from './bootstrappers/Pjax'
import DataActions from './bootstrappers/DataActions'

let win = window,
    $ = jQuery;

// 扩展PNS对象
function extend (PNS) {
    // ajax处理相关扩展函数
    new Ajax(PNS);
    // Toastr简化使用函数
    new Toastr(PNS);
    // SweetAlert2简化使用函数
    new SweetAlert2(PNS);
    // Grid相关功能函数
    new Grid(PNS);
    // loading效果
    new Loading(PNS);
    // 静态资源加载器
    new AssetsLoader(PNS);
    // 颜色管理
    new Color(PNS);
    // 表单验证器
    new Validator(PNS);
    // 黑色主题切换
    new DarkMode(PNS);

    // 加载进度条
    PNS.NP = NProgress;

    // 行选择器
    PNS.RowSelector = function (options) {
        return new RowSelector(options)
    };

    // ajax表单提交
    PNS.Form = function (options) {
        return new Form(options)
    };

    // 弹窗表单
    PNS.DialogForm = function (options) {
        return new DialogForm(PNS, options);
    };

    // 滑动面板
    PNS.Slider = function (options) {
        return new Slider(PNS, options)
    };
}

// 初始化
function listen(PNS) {
    // 只初始化一次
    PNS.booting(() => {
        PNS.NP.configure({parent: '.app-content'});

        // layer弹窗设置
        layer.config({maxmin: true, moveOut: true, shade: false});

        //////////////////////////////////////////////////////////

        // 菜单点击选中效果
        new Menu(PNS);
        // 返回顶部按钮
        new Footer(PNS);
        // data-action 动作绑定(包括删除、批量删除等操作)
        new DataActions(PNS);
    });

    // 每个请求都初始化
    PNS.bootingEveryRequest(() => {
        // ajax全局设置
        $.ajaxSetup({
            cache: true,
            error: PNS.handleAjaxError,
            headers: {
                'X-CSRF-TOKEN': PNS.token
            }
        });
        // pjax初始化功能
        new Pjax(PNS);
    });
}

function prepare(PNS) {
    extend(PNS);
    listen(PNS);

    return PNS;
}

/**
 * @returns {PNS}
 */
win.CreatePNS = function(config) {
    return prepare(new PNS(config));
};
