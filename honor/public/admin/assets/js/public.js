$(document).ready(function(){
    (function(){
        var alertTips = $.trim($('#alert-tips').html());
        if(typeof alertTips != 'undefined' && alertTips != null && alertTips){
            var classname = $('#alert-tips div').attr('class');
            switch(classname){
                case 'errorMessage':
                    $('#alert-tips div').removeClass(classname).addClass('alert').addClass('alert-danger')
                    break;
                case 'successMessage':
                    $('#alert-tips div').removeClass(classname).addClass('alert').addClass('alert-success');
                    break;
                case 'noticeMessage':
                    $('#alert-tips div').removeClass(classname).addClass('alert').addClass('alert-info')
                    break;
                case 'warningMessage':
                    $('#alert-tips div').removeClass(classname).addClass('alert').addClass('alert-warning')
                    break;
                default:
                    $('#alert-tips div').removeClass(classname).addClass('alert').addClass('alert-info')
                    break;
            }
             $('#alert-tips').slideToggle(0).delay(3000).slideToggle(300);
        }
    })();
});


function tips_message(message, level){
    var str = '';
    switch(level){
        case 'error':
            str = '<div class="alert alert-danger">' + message + '</div>';
            break;
        case 'success':
            str = '<div class="alert alert-success">' + message + '</div>';
            break;
        case 'notice':
            str = '<div class="alert alert-info">' + message + '</div>';
            break;
        case 'warning':
            str = '<div class="alert alert-warning">' + message + '</div>';
            break;
        default:
            str = '<div class="alert alert-danger">' + message + '</div>';
            break;
    }
    $('#alert-tips').html(str);
    $('#alert-tips').slideToggle('fast').delay(3000).slideToggle(300);
}

function min_length(str, len) {
    var cur_len = strlen( trim(str) );
    if( cur_len < len ) {
        return false;
    }
    return true;
}

function max_length(str, len) {
    var cur_len = strlen( trim(str) );
    if( cur_len > len ) {
        return false;
    }
    return true;
}

function strlen(str) {
  var realLength = 0, len = str.length, charCode = -1;
  for (var i = 0; i < len; i++) {
    charCode = str.charCodeAt(i);
    if (charCode >= 0 && charCode <= 128) realLength += 1;
    else realLength += 2;
  }
  return realLength;
}

function is_url(str_url) {// 验证url
    var str_url = str_url.toLowerCase();
    var strRegex = "^((http|ftp|https)://)(([a-zA-Z0-9\._-]+\.[a-zA-Z]{2,6})|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,4})*(/[a-zA-Z0-9\&%_\./-~-]*)?";
    var re = new RegExp(strRegex);
    return re.test(str_url);
}


function trim(str){
　   return str.replace(/(^\s*)|(\s*$)/g, "");
}


//confirm初始化
var Common = {
    confirm:function(params){
        var model = $("#modal_confirm");
        model.find(".title").html(params.title)
        $("#modal_confirm_btn").click()
        model.find(".ok").one("click",function(){
            params.operate(true)
        })
        model.find(".cancel").one("click",function(){
            params.operate(false)
        })
    }
}

function confirm_jump(title, url){
    Common.confirm({
          title: title,
          operate: function (reselt) {
              if (reselt) {
                window.location.href=url
              } 
          }
      })
}

$('#page_dropdown .dropdown-menu li').bind('click',function(){
        $(this).parents('.dropup').find('i').html($(this).html())
    })


