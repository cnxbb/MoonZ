<style>
    #XTIP #xtip-icon {
        height:60px;
        background-position:center bottom;
        background-repeat:no-repeat;
    }
    #XTIP .xtip-icon-warning {
        background-image:url(/images/manager/tipicon_warning.png);
        background-size:56px 50px;
    }
    #XTIP .xtip-icon-lock {
        background-image:url(/images/manager/tipicon_lock.png);
        background-size:43px 59px;
    }
    #XTIP .xtip-icon-process {
        background-image:url(/images/manager/tipicon_3dprocess.gif);
        background-repeat:no-repeat;
        background-size:120px 60px;
        background-position:center center;
    }

    #XTIP #xtitle {
        color:#333333;
        font-size:20px;
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
    #XTIP #xsubtitle {
        color:#b8b8b8;
        font-size:13px;
        line-height:25px;
        background-color:#fff;
        border:none;
        padding:0px;
        margin:0px;
        text-align:center;
        font-family:"Microsoft YaHei", "微软雅黑";
        padding-top:20px;
    }
    #XTIP button {
        height:30px;
        width:72px;
        box-sizing:border-box;
        padding:0px;
        margin:0px;
        outline:none;
        font-size:16px;
        font-family:"Microsoft YaHei", "微软雅黑";
    }
    #XTIP .btn-primary {
        background-color:#2bc084;
        color:#fff;
    }
    #XTIP .btn-cancel {
        border:1px solid #c9d1e9;
        color:#333333;
        background-color:#ffffff;
    }
</style>
<script language="JavaScript">
    /*
    cfg {
        icon: 'warning' | 'lock'
        iconclass : 图标栏自定义CSS类名 如果有icon优先使用icon忽略iconclass
        title : 标题文字
        subtitle : 副标题文字
        hideok : 如果该值存在 “确定”按钮不显示
        hidecancel : 如果该值存在 “取消”按钮不显示
        onok : function 点击确定时的回调
        oncancel : function 点击取消时的回调
        title_btnok : 确定按钮文字   默认'确定'
        title_btncancel : 取消按钮文字 默认'取消'
    }
    */
    window.xtip = function( cfg ) {
        this.cfg = cfg;
        if( typeof( cfg ) !== 'undefined' && typeof( cfg.icon ) !== 'undefined' ) {
            if( cfg.icon.length > 0 ) {
                $("#XTIP #xtip-icon").attr('class', 'xtip-icon-' + cfg.icon );
                $("#XTIP #xtip-icon").show();
            } else {
                $("#XTIP #xtip-icon").hide();
            }
        } else {
            if( typeof( cfg.iconclass ) !== 'undefined' && cfg.iconclass.length > 0 ) {
                $("#XTIP #xtip-icon").attr('class', cfg.iconclass );
                $("#XTIP #xtip-icon").show();
            } else {
                $("#XTIP #xtip-icon").hide();
            }
        }

        if( typeof( cfg ) !== 'undefined' && typeof( cfg.title ) !== 'undefined' && cfg.title.length > 0 ) {
            $("#XTIP #xtitle").html( cfg.title );
            $("#XTIP #xtitle").show();
        } else {
            $("#XTIP #xtitle").hide();
        }

        if( typeof( cfg ) !== 'undefined' && typeof( cfg.subtitle ) !== 'undefined' && cfg.subtitle.length > 0 ) {
            $("#XTIP #xsubtitle").html( cfg.subtitle );
            $("#XTIP #xsubtitle").show();
        } else {
            $("#XTIP #xsubtitle").hide();
        }

        if( typeof( cfg ) !== 'undefined' && typeof( cfg.title_btnok ) !== 'undefined' ) {
            $("#XTIP #XTIP-btnConfirm").text( cfg.title_btnok );
        } else {
            $("#XTIP #XTIP-btnConfirm").text( '确定' );
        }

        if( typeof( cfg ) !== 'undefined' && typeof( cfg.title_btncancel ) !== 'undefined' ) {
            $("#XTIP #XTIP-btnCancel").text( cfg.title_btncancel );
        } else {
            $("#XTIP #XTIP-btnCancel").text( '取消' );
        }

        $("#XTIP #XTIP-btnConfirm").unbind('click');
        $("#XTIP #XTIP-btnConfirm").bind('click', ( function( _self ) {
            return function() {
                $("#XTIP").modal('hide');
                if( typeof( _self.cfg.onok ) == 'function' ) {
                    _self.cfg.onok();
                }
            }
        }( this ) ) );

        $("#XTIP #XTIP-btnCancel").unbind('click');
        $("#XTIP #XTIP-btnCancel").bind('click', ( function( _self ) {
            return function() {
                $("#XTIP").modal('hide');
                if( typeof( _self.cfg.oncancel ) == 'function' ) {
                    _self.cfg.oncancel();
                }
            }
        }( this ) ) );

        if( typeof( cfg ) !== 'undefined' && typeof( cfg.hideok ) !== 'undefined' ) {
            $("#XTIP-btnConfirm").hide();
        } else {
            $("#XTIP-btnConfirm").show();
        }

        if( typeof( cfg ) !== 'undefined' && typeof( cfg.hidecancel ) !== 'undefined' ) {
            $("#XTIP-btnCancel").hide();
        } else {
            $("#XTIP-btnCancel").show();
        }

        if( typeof( cfg ) !== 'undefined' && typeof( cfg.hidecancel ) !== 'undefined' &&
            typeof( cfg ) !== 'undefined' && typeof( cfg.hideok ) !== 'undefined' ) {
            $("#XTIP .modal-footer").hide();
        } else {
            $("#XTIP .modal-footer").show();
        }
        $("#XTIP").modal();
    }

    window.xtip.ErrParam = function( _err, _title = '操作失败' ) {
        return {
            icon: 'warning',
            title : ( typeof( _err ) !== 'undefined' && _err && typeof( _err.errmsg ) != 'undefined' ) ? _err.errmsg : _title,
            hideok : true,
            title_btncancel : '关闭',
        };
    };
    window.xtip.Close = function() {
        $("#XTIP").modal('hide');
    };
    window.xtip.Title = function( text ) {
        $("#XTIP #xtitle").html( text );
    };
    window.xtip.SubTitle = function( text ) {
        $("#XTIP #xsubtitle").html( text );
    };
</script>
<div class="modal" id="XTIP" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-keyboard="false" data-backdrop="static" style="display:none;" >
    <div class="modal-dialog" style="width:410px;">
        <div class="modal-content">
            <div class="modal-body" style="overflow:auto;">
                <div style="padding:15px 0px 13px 0;">
                    <div id="xtip-icon"></div>
                    <pre id="xtitle"></pre>
                    <pre id="xsubtitle"></pre>
                </div>

            </div>
            <div class="modal-footer" style="padding:13px 0 17px 0;">
                <center>
                    <button id="XTIP-btnConfirm" type="button" class="btn btn-primary">确定</button><button id="XTIP-btnCancel" type="button" class="btn btn-cancel" data-dismiss="modal" style="margin-left:10px;">取消</button>
                </center>
            </div>
        </div>
    </div>
</div>