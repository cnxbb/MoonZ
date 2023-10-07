<?php
//进度条
//------------------------------------------------------------------------------------
?>
<style>
    #progressbar {
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
        -khtml-user-select: none;
        user-select: none;
        z-index:5999;
        border:1px solid #ccc;
        border-radius:8px;
        background-color:#f5f5f5;
        color:#333;
        display:none;
    }

    .progress-line {
        width: 288px;
        height: 20px;
        line-height: 20px;
        background-color: #fff;
        color: #333;
        position: absolute;
        text-align: center;
        top: 0px;
        left: 0px;
        font-size: 14px;
    }

    .progress-line-out {
        height: 20px;
        line-height: 20px;
        font-size: 14px;
        position: absolute;
        overflow: hidden;
        width: 0%;
    }

    .progress-line-in {
        background-color: #0074e8;
        color: #fff;
    }
</style>
<div id="progressbar" class="progressbar" style="position:fixed;left: 50%;top: 50%;transform: translate(-50%,-50%); width:300px;height:auto;z-index:5999;-moz-user-select:none;-webkit-user-select:none;-ms-user-select:none;-khtml-user-select:none;user-select:none;border:1px solid #ccc;border-radius:8px;background-color:#f5f5f5;color:#333;display:none;">
    <div style="padding:6px;">
        <div style="font-size:14px;text-align:center" class="progressbar-title">正在上传···</div>
        <div id="progress-all" style="background:#fff;border:1px solid #c0c0c0;height:20px;border-radius:6px;overflow:hidden;margin-top:7px;position:relative;width:288px;">
            <div class="progress-line"><span class="progress-val"></span></div>
            <div class="progress-line-out">
                <div class="progress-line progress-line-in"><span class="progress-val"></span></div>
            </div>
        </div>
        <center style="padding-top:10px;">
            <button class="btn btn-warning" id="btncancelupload" style="padding:2px 12px;"
            onclick="javascript:if( window.hupload ) {window.hupload.data('jqxhr').abort();window.hupload = null;}">终止上传</button>
        </center>
    </div>
</div>