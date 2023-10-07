<?php  //CODE BY ZMZ
// 数字键盘
?>

<style>
    #numKeyboard {
        width:302px;
        box-sizing:border-box;
        position:absolute;
        display:none;
        left:50%;
        top:50%;
        transform:translateX(-50%) translateY(-50%);
        z-index:2999;
        background-color:#fff;
    }
    #numKeyboard .num {
        bor-sizing:border-box;
        width:100px;
        height:100px;
        font-size:40px;
        line-height:100px;
        text-align:center;
        border:1px solid #ccc;
    }
</style>

<div id="numKeyboard" style="width:300px;box-sizing:border-box;">
    <div style="width:100%;display:flex;">
        <div class="num">7</div>
        <div class="num">8</div>
        <div class="num">9</div>
    </div>
    <div style="width:100%;display:flex;">
        <div class="num">4</div>
        <div class="num">5</div>
        <div class="num">6</div>
    </div>
    <div style="width:100%;display:flex;">
        <div class="num">1</div>
        <div class="num">2</div>
        <div class="num">3</div>
    </div>
    <div style="width:100%;display:flex;">
        <div class="num">0</div>
        <div class="num" data-t="dot">.</div>
        <div class="num glyphicon glyphicon-arrow-left" data-t="bs" style="overflow:hidden;position:static;"></div>
    </div>
</div>

<script language="JavaScript">
    $(document).ready(function() {
        $("#numKeyboard .num").on('tap', function( e ) {
            var el = $(e.target).closest('.num');
            el.css('background-color','#DDD');
            setTimeout( ( function( _el ) {
                return function() {
                    _el.css('background-color','#fff' );
                    $("#numKeyboard").data('IEL').focus();
                    var dt = el.attr('data-t'),
                        start = $("#numKeyboard").data('IEL').get(0).selectionStart,
                        end = $("#numKeyboard").data('IEL').get(0).selectionEnd,
                        oldv = $("#numKeyboard").data('IEL').val(),
                        arr = oldv.split('');

                    if( dt && dt == 'dot' ) { //.
                        if( $("#numKeyboard").data('IEL').val().length < 1 ||
                            $("#numKeyboard").data('IEL').val().indexOf('.') != -1 ) {
                            return;
                        }
                    }
                    if( dt && dt == 'bs' ) { //Backspace
                        if( start == end ) {
                            if( start == 0 ) {
                                return;
                            }
                            arr.splice(start-1,1);
                            $("#numKeyboard").data('IEL').val( arr.join('') );
                            $("#numKeyboard").data('IEL').get(0).selectionStart = $("#numKeyboard").data('IEL').get(0).selectionEnd  = start-1;
                        } else {
                            arr.splice(start, end-start);
                            $("#numKeyboard").data('IEL').val( arr.join('') );
                            $("#numKeyboard").data('IEL').get(0).selectionStart = $("#numKeyboard").data('IEL').get(0).selectionEnd  = start;
                        }
                        return;
                    }
                    if( start == end ) {
                        if( start == oldv.length ) {
                            $("#numKeyboard").data('IEL').val( $("#numKeyboard").data('IEL').val() + el.text() );
                        }else {
                            arr.splice(start,0, el.text() );
                            $("#numKeyboard").data('IEL').val( arr.join('') );
                            $("#numKeyboard").data('IEL').get(0).selectionStart = $("#numKeyboard").data('IEL').get(0).selectionEnd  = start+el.text().length;
                        }
                    } else {
                        arr.splice(start, end-start, el.text() );
                        $("#numKeyboard").data('IEL').val( arr.join('') );
                        $("#numKeyboard").data('IEL').get(0).selectionStart = $("#numKeyboard").data('IEL').get(0).selectionEnd  = start+el.text().length;
                    }

                }
            }(el) ), 100 );


        } );
    } );
</script>