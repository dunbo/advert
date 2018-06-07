$(function(){
	$('.menu_title').bind('click',function(){
		$(this).parent().toggleClass('selected')
		$(this).siblings('.menu_sub').toggle();
	})
	$('#dropdown1 .dropdown-menu li').bind('click',function(){
		$(this).parents('.dropdown').find('i').html($(this).html())
	})
	//单选按钮
	$('.label_group').bind('click',function(){
		$(this).parent().find('.label_group').removeClass('label_selected');
		if(!$(this).hasClass('label_selected')){
			$(this).addClass('label_selected');
			$(this).find('input').attr('checked','checked');
		}
	})
})

/*dd*/
function switch_group(obj, onoff) {
	if( onoff == 1) {
		$(obj).addClass('switch_checked');
		$(obj).find('input').prop('checked','checked');
		$(obj).find('input').attr('checked','checked');
		$(obj).find('span').html('开');
	}else {
		$(obj).removeClass('switch_checked');
		$(obj).find('input').removeProp('checked');
		$(obj).find('input').removeAttr('checked');
		$(obj).find('span').html('关');
	}
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


