<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no" name="viewport"/>
<title>登录</title>
<link rel="stylesheet" href="/js/bootstrap/css/bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="/css/managerpage.css" />
<style>
    * {
        box-sizing:border-box;
        font-family: "Microsoft YaHei", "微软雅黑";
        background-repeat:no-repeat;
    }
    html {
        width:100%;
        height:100%;
    }
    body {
        background-image:url(/images/manager/loginbg.png);
        background-position:center center;
        background-repeat:no-repeat;
    }
    #mask {
        position:fixed;
        left:0px;
        right:0px;
        top:0px;
        bottom:0px;
        background-color:rgba(0,0,0,.8);
        display:flex;
        justify-content:center;
        align-items:center;
    }
    #inner {
        width:1200px;
        height:610px;
        background-color:rgba(255,255,255,1);
        background-image:url(/images/manager/loginleft.png);
        background-position:left center;
        background-repeat:no-repeat;
        padding-left:670px;

    }
    #login-part {
        width:530px;
    }
    #login-t {
        font-size:40px;
        color:#12ab6e;
        padding:77px 0 0 79px;
    }
    #login-u {
        width:299px;
        height:48px;
        border:1px solid #eeeeee;
        border-radius:4px;
        margin-left:121px;
        margin-top:74px;
        background-image:url(/images/manager/loginicon1.png);
        background-position:14px 13px;
        padding-left:39px;
    }

    #login-p {
        width:299px;
        height:48px;
        border:1px solid #eeeeee;
        border-radius:4px;
        margin-left:121px;
        margin-top:17px;
        background-image:url(/images/manager/loginicon2.png);
        background-position:14px 13px;
        padding-left:40px;
    }
    #login-r {
        width:299px;
        height:48px;
        margin-left:121px;
        margin-top:17px;
        display:flex;
        align-items:center;
    }
    #login-r input{
        border:1px solid;
        outline:none;
        cursor:pointer;
    }
    #login-r label {
        color:#999999;
        font-size:14px;
        cursor:pointer;
    }
    #login-btn {
        margin-left:121px;
        margin-top:30px;
        width:299px;
        height:48px;
        line-height:48px;
        color:#fff;
        background-color:#25c887;
        border-radius:4px;
        text-align:center;
        font-size:20px;
        cursor:pointer;
    }

    #login-u input, #login-p input {
        outline:none;
        border:none;
        padding:0px;
        height:46px;
        line-height:46px;
        font-size:16px;
        width:250px;
    }
    .noselect {
        -moz-user-select: none;
        -webkit-user-select: none;
        -ms-user-select: none;
        -khtml-user-select: none;
        user-select: none;
    }
</style>
<script type="text/javascript" src="/js/bootstrap/js/jquery.min.js"></script>
<script type="text/javascript" src="/js/bootstrap/js/bootstrap.js"></script>
<script type="text/javascript" src="/js/bootstrap.modal.vcenter.js"></script>
<script type="text/javascript" src="/js/xajax.js"></script>
<script language="JavaScript">
    $(document).ready( function() {
        $("body").show();
        $("#login-btn").click( function() {
            var pd = {
                'login' : {
                    'uname' : $("#uname").val(),
                    'upass' : $("#upass").val(),
                    'remember' : $("#remember:checked").length,
                }
            };
            xajax( '/api/', JSON.stringify(pd), function( ret ) {
                window.location.href = '/';
            }, function( err ) {
                xtip( xtip.ErrParam( err ) );
            } );
        } );
    });
</script>

</head>
<body>
    <div id="mask">
        <div id="inner">
            <div id="login-part">
                <div id="login-t" class="noselect">混凝土智能振捣系统</div>
                <div id="login-u">
                    <input type="text" id="uname" placeholder="请输入手机号" value="">
                </div>
                <div id="login-p">
                    <input type="password" id="upass" placeholder="请输入密码" value="">
                </div>
                <div id="login-r" class="noselect">
                    <input type="checkbox" id="remember" value="1" style="width:16px;height:16px;border-radius:1px;padding:0;margin:0;">
                    <label style="padding:0 4px;margin:0;" for="remember">在此设备上保持登录</label>
                </div>
                <div id="login-btn" class="noselect">登录</div>
            </div>
        </div>
    </div>
    <?php require_once 'inc/xtip.inc.php'; ?>
</body>
</html>