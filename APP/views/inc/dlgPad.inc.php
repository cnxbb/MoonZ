<style>
    .dlg-content {
        display:none;
    }
    #PADDLG .modal-header {
        border-bottom:1px solid #dee3f1;
        line-height:0px;
        display:flex;
    }
    #PADDLG .modal-title {
        position:relative;
        flex-Grow:1;
    }
    #PADDLG .close {
        opacity:1;
        padding:0;
        margin:0;
    }
    #PADDLG .modal-title::before {
        content: '';
        position: absolute;
        display: block;
        background-color: #41dc9e;
        height: 100%;
        left:0px;
        top:0px;
    }
    #PADDLG .modal-footer {
        padding:0;
        margin:0;
        position:relative;
        border-top:1px solid #dee3f1;
        display:flex;
        overflow:hidden;
    }

    #PADDLG #PADDLG-btn-cancel {
        color:#999999;
        text-align:center;
    }
    #PADDLG #PADDLG-btn-confirm {
        color:#fff;
        background-color:#25c887;
        text-align:center;
        font-weight:bold;
    }
    #PADDLG #PADDLG-btn-ok {
        color:#000;
        background-color:#fff;
        text-align:center;
        letter-spacing:0px;
    }

    #PADDLG #xtip-icon {
        background-position:center bottom;
        background-repeat:no-repeat;
    }
    #PADDLG .xtip-icon-done {
        background-image:url(/images/pad/tipicon_done.png);
    }
    #PADDLG .xtip-icon-warning {
        background-image:url(/images/pad/tipicon_warning.png);
    }
    #PADDLG .xtip-icon-lock {
        background-image:url(/images/pad/tipicon_lock.png);
    }
    #PADDLG .xtip-icon-question {
        background-image:url(/images/pad/tipicon_question.png);
    }
    #PADDLG .xtip-icon-process {
        background-image:url(/images/pad/tipicon_3dprocess.gif);
        background-repeat:no-repeat;
        background-position:center center;
    }
    #PADDLG .xtip-icon-clock {
        background-image:url(/images/pad/tipicon_clock.png);
    }

    #PADDLG #xtitle {
        color:#333333;
        line-height:37px;
        background-color:#fff;
        border:none;
        padding:0px;
        margin:0px;
        text-align:center;
        font-family:"Microsoft YaHei", "微软雅黑";
        padding-top:8px;
        white-space:pre-wrap;
    }
    #PADDLG #xsubtitle {
        color:#b8b8b8;
        font-size:13px;
        line-height:25px;
        background-color:#fff;
        border:none;
        padding:0px;
        margin:0px;
        text-align:center;
        font-family:"Microsoft YaHei", "微软雅黑";
        padding-top:8px;
    }
    .modal-dialog {
        position:absolute;
        left:50%;
        top:50%;

    }
    .modal.in .modal-dialog {
        margin:0;
        transform: translate(-50%,-50%);
    }
</style>
<script language="JavaScript">
    /*
    cfg {
        tip : {
            icon: 'warning' | 'lock'
            iconclass : 图标栏自定义CSS类名 如果有icon优先使用icon忽略iconclass
            title : 标题文字
            subtitle : 副标题文字
        },
        dlg : {
            id : 'xxx',
            title : '标题文字',
        },
        btns : [
            'cancel', 'confirm', 'ok',
        ],
        title_btconfirm : 确定按钮文字   默认'确定'
        title_btncancel : 取消按钮文字 默认'取消'
        title_btnok: 按钮文字
        width: '??px',
        onok : function 点击确定时的回调
        oncancel : function 点击取消时的回调
        oninit : function( dlg ) 用于初始化对话框数据在显示前调用 dlg参数为对话框的jQuery对象

    }
    */
    window.dlgPad = {
        modal : function( cfg ) {
            $("#PADDLG .modal-header").show();
            if( cfg && cfg.dlg && cfg.dlg.id ) {
                var content = $("#" + cfg.dlg.id).clone();
                $("#PADDLG-Content").html('');
                $("#PADDLG-Content").append( content );
                content.show();
                $("#PADDLG-Content").show();

                if( cfg.dlg.title && cfg.dlg.title.length > 0 ) {
                    $("#PADDLG .modal-header").show();
                    $("#PADDLG .modal-title").text( cfg.dlg.title );
                } else {
                    $("#PADDLG .modal-header").hide();
                }
            } else {
                $("#PADDLG-Content").hide();
            }

            if( cfg && cfg.tip ) {
                $("#TIP-Content").show();
                $("#PADDLG .modal-header").hide();
                if( typeof( cfg.tip.icon ) != 'undefined' && cfg.tip.icon.length > 0 ) {
                    $("#PADDLG #xtip-icon").attr('class', 'xtip-icon-' + cfg.tip.icon );
                    $("#PADDLG #xtip-icon").show();
                } else {
                    $("#PADDLG #xtip-icon").hide();
                }

                if( typeof( cfg.tip.title ) != 'undefined' && cfg.tip.title.length > 0 ) {
                    $("#PADDLG #xtitle").html( cfg.tip.title );
                    $("#PADDLG #xtitle").show();
                } else {
                    $("#PADDLG #xtitle").hide();
                }

                if( typeof( cfg.tip.subtitle ) != 'undefined' && cfg.tip.subtitle.length > 0 ) {
                    $("#PADDLG #xsubtitle").html( cfg.tip.subtitle );
                    $("#PADDLG #xsubtitle").show();
                } else {
                    $("#PADDLG #xsubtitle").hide();
                }
            } else {
                $("#TIP-Content").hide();
            }

            $("#PADDLG #PADDLG-btn-cancel").show();
            $("#PADDLG #PADDLG-btn-confirm").show();
            $("#PADDLG #PADDLG-btn-ok").hide();
            if( cfg && cfg.btns && $.isArray( cfg.btns ) ) {
                if( $.inArray("ok", cfg.btns) != -1 ) {
                    $("#PADDLG #PADDLG-btn-cancel").hide();
                    $("#PADDLG #PADDLG-btn-confirm").hide();
                    $("#PADDLG #PADDLG-btn-ok").show();
                }
            }
            if( cfg && cfg.width ) {
                $("#PADDLG .modal-dialog").css('width', cfg.width );
            }



            $("#PADDLG #PADDLG-btn-cancel").unbind('click');
            $("#PADDLG #PADDLG-btn-confirm").unbind('click');
            $("#PADDLG #PADDLG-btn-ok").unbind('click');

            $("#PADDLG #PADDLG-btn-cancel").bind('click', ( function( _cfg ) {
                return function() {
                    $("#PADDLG").modal('hide');
                    if( typeof( _cfg.oncancel ) == 'function' ) {
                        _cfg.oncancel();
                    }
                }
            }( cfg ) ) );

            $("#PADDLG #PADDLG-btn-confirm").bind('click', ( function( _cfg ) {
                return function() {
                    if( typeof( _cfg.onok ) == 'function' ) {
                        _cfg.onok();

                    } else {
                        $("#PADDLG").modal('hide');
                    }
                }
            }( cfg ) ) );

            if( cfg && cfg.oninit && typeof( cfg.oninit ) == 'function' ) {
                cfg.oninit( $('#PADDLG') );
            }
            $("#PADDLG").modal();
        },

        modals : function( cfg ) {
            var dlg = $("#PADDLG").clone(true,true);
            //dlg.find(".modal-header").show();
            if( cfg && cfg.dlg && cfg.dlg.id ) {
                var content = $("#" + cfg.dlg.id).clone();
                dlg.find("#PADDLG-Content").html('');
                dlg.find("#PADDLG-Content").append( content );
                content.show();
                dlg.find("#PADDLG-Content").show();

                if( cfg.dlg.title ) {
                    dlg.find(".modal-title").text( cfg.dlg.title );
                }
            } else {
                dlg.find("#PADDLG-Content").hide();
            }

            if( cfg && cfg.tip ) {
                dlg.find("#TIP-Content").show();
                dlg.find(".modal-header").hide();
                if( typeof( cfg.tip.icon ) != 'undefined' && cfg.tip.icon.length > 0 ) {
                    dlg.find("#xtip-icon").attr('class', 'xtip-icon-' + cfg.tip.icon );
                    dlg.find("#xtip-icon").show();
                } else {
                    dlg.find("#xtip-icon").hide();
                }

                if( typeof( cfg.tip.title ) != 'undefined' && cfg.tip.title.length > 0 ) {
                    dlg.find("#xtitle").html( cfg.tip.title );
                    dlg.find("#xtitle").show();
                } else {
                    dlg.find("#xtitle").hide();
                }

                if( typeof( cfg.tip.subtitle ) != 'undefined' && cfg.tip.subtitle.length > 0 ) {
                    dlg.find("#xsubtitle").html( cfg.tip.subtitle );
                    dlg.find("#xsubtitle").show();
                } else {
                    dlg.find("#xsubtitle").hide();
                }
            } else {
                dlg.find("#TIP-Content").hide();
            }

            dlg.find("#PADDLG-btn-cancel").show();
            dlg.find("#PADDLG-btn-confirm").show();
            dlg.find("#PADDLG-btn-ok").hide();
            if( cfg && cfg.btns && $.isArray( cfg.btns ) ) {
                if( $.inArray("ok", cfg.btns) != -1 ) {
                    dlg.find("#PADDLG-btn-cancel").hide();
                    dlg.find("#PADDLG-btn-confirm").hide();
                    dlg.find("#PADDLG-btn-ok").show();
                }
            }
            if( typeof( cfg.title_btncancel ) != 'undefined' && cfg.title_btncancel.length > 0 ) {
                dlg.find("#PADDLG-btn-cancel").text( cfg.title_btncancel );
            } else {
                dlg.find("#PADDLG-btn-cancel").text( '取消' );
            }
            if( typeof( cfg.title_btconfirm ) != 'undefined' && cfg.title_btconfirm.length > 0 ) {
                dlg.find("#PADDLG-btn-confirm").text( cfg.title_btconfirm );
            } else {
                dlg.find("#PADDLG-btn-confirm").text( '确认' );
            }
            if( typeof( cfg.title_btnok ) != 'undefined' && cfg.title_btnok.length > 0 ) {
                dlg.find("#PADDLG-btn-ok").text( cfg.title_btnok );
            } else {
                dlg.find("#PADDLG-btn-ok").text( '关闭' );
            }

            if( cfg && cfg.width ) {
                dlg.find(".modal-dialog").css('width', cfg.width );
            }

            dlg.find("#PADDLG-btn-cancel").unbind('click');
            dlg.find("#PADDLG-btn-confirm").unbind('click');
            dlg.find("#PADDLG-btn-ok").unbind('click');
            dlg.find(".modal-header .close").unbind('click');

            dlg.find(".modal-header .close").bind('click', function( _cfg, _dlg ) {
                return function() {
                    _dlg.modal('hide');
                    _dlg.remove();
                    if( typeof( _cfg.oncancel ) == 'function' ) {
                        _cfg.oncancel( _dlg );
                    }
                };
            }( cfg, dlg ) );

            dlg.find("#PADDLG-btn-cancel").bind('click', ( function( _cfg, _dlg ) {
                return function() {
                    _dlg.modal('hide');
                    _dlg.remove();
                    if( typeof( _cfg.oncancel ) == 'function' ) {
                        _cfg.oncancel( _dlg );
                    }
                }
            }( cfg, dlg ) ) );

            dlg.find("#PADDLG-btn-confirm").bind('click', ( function( _cfg, _dlg ) {
                return function() {
                    if( typeof( _cfg.onok ) == 'function' ) {
                        if( _cfg.onok( _dlg ) === true ) {
                            _dlg.modal('hide');
                            _dlg.remove();
                        }
                    } else {
                        _dlg.modal('hide');
                        _dlg.remove();
                    }
                }
            }( cfg, dlg ) ) );

            dlg.find("#PADDLG-btn-ok").bind('click', ( function( _cfg, _dlg ) {
                return function() {
                    if( typeof( _cfg.onok ) == 'function' ) {
                        if( _cfg.onok( _dlg ) === true ) {
                            _dlg.modal('hide');
                            _dlg.remove();
                        }
                    } else {
                        _dlg.modal('hide');
                        _dlg.remove();
                    }
                }
            }( cfg, dlg ) ) );

            if( cfg && cfg.oninit && typeof( cfg.oninit ) == 'function' ) {
                cfg.oninit( dlg );
            }
            dlg.modal();
            return dlg;
        }
    };
</script>

<div class="modal" id="PADDLG" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static" style="display:none;" >
    <div class="modal-dialog" >
        <div class="modal-content" br=10>
            <div class="modal-header">
                <div class="modal-title">对话框标题</div>
                <button type="button" class="close" rh=20 data-dismiss="modal">&times;</button>
            </div>
            <div id="TIP-Content" style="overflow:auto;">
                <div pb=27>
                    <div id="xtip-icon" rh=114></div>
                    <pre id="xtitle" fs=20></pre>
                    <pre id="xsubtitle"></pre>
                </div>
            </div>
            <div id="PADDLG-Content"></div>
            <div class="modal-footer">
                <div id="PADDLG-btn-cancel" style="width:50%;">取消</div>
                <div id="PADDLG-btn-confirm" style="width:50%;">确认</div>
                <div id="PADDLG-btn-ok" style="width:100%;">知道了</div>
            </div>
        </div>
    </div>
</div>

<img class="css-magic" data-name="#PADDLG .modal-header" data-pl=18 data-pt=20 data-pr=18 data-pb=20 />
<img class="css-magic" data-name="#PADDLG .modal-title" data-pl=16 data-fs=18 data-height=20 data-lh=20 />
<img class="css-magic" data-name="#PADDLG .modal-title::before" data-width=4 data-height=20  />
<img class="css-magic" data-name="#PADDLG #PADDLG-btn-cancel, #PADDLG #PADDLG-btn-confirm, #PADDLG #PADDLG-btn-ok" data-height=60 data-lh=60 data-fs=20 data-ls=6 />
<img class="css-magic" data-name="#PADDLG .modal-footer" data-bblr=10 data-bbrr=10 />
<img class="css-magic" data-name="#PADDLG .xtip-icon-warning" data-bgsize-w=56 data-bgsize-h=50 />
<img class="css-magic" data-name="#PADDLG .xtip-icon-done" data-bgsize-w=56 data-bgsize-h=60 />
<img class="css-magic" data-name="#PADDLG .xtip-icon-lock" data-bgsize-w=43 data-bgsize-h=59 />
<img class="css-magic" data-name="#PADDLG .xtip-icon-process" data-bgsize-w=120 data-bgsize-h=60 />
<img class="css-magic" data-name="#PADDLG .xtip-icon-clock" data-bgsize-w=57 data-bgsize-h=57 />
<img class="css-magic" data-name="#PADDLG .xtip-icon-question" data-bgsize-w=54 data-bgsize-h=55 />
