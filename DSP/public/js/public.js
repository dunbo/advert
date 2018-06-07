$(function(){
	$('.menu_title').bind('click',function(){
		$(this).parent().toggleClass('selected')
		$(this).siblings('.menu_sub').toggle();
	})
	$('#dropdown1 .dropdown-menu li').bind('click',function(){
		$(this).parents('.dropdown').find('i').html($(this).html())
	})
	/*switch 开关*/
	// $('.switch_group').bind('click',function(){
	// 	if(!$(this).hasClass('switch_checked')){
	// 		$(this).addClass('switch_checked');
	// 		$(this).find('input').prop('checked','checked');
	// 		$(this).find('input').attr('checked','checked');
	// 		$(this).find('span').html('开');
	// 	}
	// 	else{
	// 		$(this).removeClass('switch_checked');
	// 		$(this).find('input').removeProp('checked');
	// 		$(this).find('input').removeAttr('checked');
	// 		$(this).find('span').html('关');
	// 		$('#modal_confirm').modal('show');
	// 	}
	// })
	//block 1选择标题不全选  2选定标题整组移动
	var block1 = new Block('#block1',1);
	var block2 = new Block('#block2',2);
	/*block search*/
	$('#block1 .search_input1').bind('input',function(){
		search_block1('#block1',this)
	})
	$('#block1 .search_input2').bind('input',function(){
		search_block2('#block1',this)
	})
	$('#block2 .search_input1').bind('input',function(){
		search_block1('#block2',this)
	})
	$('#block2 .search_input2').bind('input',function(){
		search_block2('#block2',this)
	})
})
function Block(obj,order){
	this.blockSelNum =0;
	this.blockNum = 0;
	this.totalNum = $(obj+' .select_table .ms_list_sub li').length;
	this.hover(obj);
	this.selectLeft(obj);
	this.selectRight(obj);
	if(order == 2){
		this.selTitleLeft(obj);
		this.selTitleRight(obj);
	}
	this.selDeleAll(obj);
	this.selAddAll(obj);
}
Block.prototype.hover = function(obj){
	$(obj+' .ms_list_sub li').hover(function(){
		$(this).addClass('ms_hover');
	},function(){
		$(this).removeClass('ms_hover');
	});
}
Block.prototype.selectLeft = function(obj){
	var _this = this;
	$(obj+' .select_table .ms_list_sub li').each(function(index){
		this.order = index;
		$(this).bind('click',function(){
			$(obj+ ' #select_sel option').eq(this.order).attr('selected','selected');
			$(obj+ ' #select_sel option').eq(this.order).parent().attr('selected','selected');
			$(this).hide();
			var diy_none = true;
			$(this).parent().find('li').each(function(){
				if($(this).css('display') != 'none')
				{diy_none = false;}
			})
			if(diy_none==true){
				$(this).parent().siblings('h3').hide()
			}
			$(obj+ ' .ms_selection .ms_list_sub li').eq(this.order).parent().siblings('h3').show();
			$(obj+ ' .ms_selection .ms_list_sub li').eq(this.order).show();
			$(obj+ ' .ms_selection .ms_list_sub li').eq(this.order).addClass('selected');
			_this.selNum(obj);
		})
	})
}
Block.prototype.selectRight = function(obj){
	var _this = this;
	$(obj+' .ms_selection .ms_list_sub li').each(function(index){
		this.order = index;
		$(this).bind('click',function(){
			$(obj+ ' #select_sel option').eq(this.order).removeAttr('selected');
			$(this).hide();
			var diy_none = true;
			$(this).parent().find('li').each(function(){
				if($(this).css('display') != 'none')
				{diy_none = false;}
			})
			if(diy_none==true){
				$(this).parent().siblings('h3').hide();
				$(obj+ ' #select_sel option').eq(this.order).parent().removeAttr('selected');
			}
			$(obj+' .select_table .ms_list_sub li').eq(this.order).parent().siblings('h3').show();
			$(obj+' .select_table .ms_list_sub li').eq(this.order).show();
			$(obj+' .ms_selection .ms_list_sub li').eq(this.order).removeClass('selected');
			_this.selNum(obj);
		})
	})
}
Block.prototype.selTitleLeft = function(obj){
	var _this = this;
	$(obj+' .select_table .ms_list_mainli h3').each(function(index){
		this.order = index;
		$(this).bind('click',function(){
			$(this).hide();
			$(this).siblings('.ms_list_sub').find('li').hide();
			var selGrNum = $(this).siblings('.ms_list_sub').find('li').length;
			$(obj+' .ms_selection .ms_list_mainli').eq(this.order).find('h3').show();
			$(obj+' .ms_selection .ms_list_mainli').eq(this.order).find('li').show();
			$(obj+' .ms_selection .ms_list_mainli').eq(this.order).find('li').addClass('selected');
			$(obj+' #select_sel optgroup').eq(this.order).find('option').attr('selected','selected');
			$(obj+' #select_sel optgroup').eq(this.order).attr('selected','selected');
			_this.selNum(obj);
		})
	})
}
Block.prototype.selTitleRight = function(obj){
	var _this = this;
	$(obj+' .ms_selection .ms_list_mainli h3').each(function(index){
		this.order = index;
		$(this).bind('click',function(){
			$(this).hide();
			$(this).siblings('.ms_list_sub').find('li').hide();
			var selGrNum = $(this).siblings('.ms_list_sub').find('li').length;
			$(obj+' .select_table .ms_list_mainli').eq(this.order).find('h3').show();
			$(obj+' .select_table .ms_list_mainli').eq(this.order).find('li').show();
			$(obj+' .ms_selection .ms_list_mainli').eq(this.order).find('li').removeClass('selected');
			$(obj+' #select_sel optgroup').eq(this.order).find('option').removeAttr('selected');
			$(obj+' #select_sel optgroup').eq(this.order).removeAttr('selected');
			_this.selNum(obj);
		})
	})
}
Block.prototype.selDeleAll = function(obj){
	var _this = this;
	$(obj+' .select_delete').bind('click',function(){
		$(obj+' .ms_selection').find('h3').hide();
		$(obj+' .ms_selection .ms_list_sub').find('li').hide();
		$(obj+' .select_table').find('h3').show();
		$(obj+' .select_table .ms_list_sub').find('li').show();
		_this.blockSelNum = 0;
		$(obj+' .ms_selection .select_table_head span').html('选择行业('+_this.blockSelNum+')');
		_this.blockNum = $(obj+' .select_table .ms_list_sub li').length;
		$(obj+' .select_table .select_table_head span').html('选择行业('+_this.blockNum+')');
		$(obj+' #select_sel option').each(function(){
			$(this).removeAttr('selected');
			$(this).parent().removeAttr('selected');
		})
		$(obj+' .ms_selection .ms_list_sub li').each(function(){
			$(this).removeClass('selected');
		})
	})
}
Block.prototype.selAddAll = function(obj){
	var _this = this;
	$(obj+' .select_add_all').bind('click',function(){
		$(obj+' .ms_selection').find('h3').show();
		$(obj+' .ms_selection .ms_list_sub').find('li').show();
		$(obj+' .select_table').find('h3').hide();
		$(obj+' .select_table .ms_list_sub').find('li').hide();
		_this.blockSelNum = $(obj+' .select_table .ms_list_sub li').length;
		$(obj+' .ms_selection .select_table_head span').html('选择行业('+_this.blockSelNum+')');
		_this.blockNum = 0;
		$(obj+' .select_table .select_table_head span').html('选择行业('+_this.blockNum+')');
		$(obj+' #select_sel option').each(function(){
			$(this).attr('selected','selected');
			$(this).parent().attr('selected','selected');
		})
		$(obj+' .ms_selection .ms_list_sub li').each(function(){
			$(this).addClass('selected');
		})
	})
}
Block.prototype.selNum = function(obj){
	var _this = this;
	this.blockSelNum = 0;
	$(obj+' #select_sel option').each(function(){
		if($(this).attr('selected')=='selected'){
			_this.blockSelNum ++;
		}
	})
	this.blockNum = this.totalNum - this.blockSelNum;
	$(obj+' .ms_selection .select_table_head span').html('选择行业('+this.blockSelNum+')');
	$(obj+' .select_table .select_table_head span').html('选择行业('+this.blockNum+')');
}
/*block search*/
function search_block1(obj,input){
	var value = $(input).val();
	if(value!=""){
		var $li = $(input).parents('.select_table').find('.ms_list_sub li:contains('+value.trim()+')');
		if($li.length==0){
			$(input).parents('.select_table').find('.ms_list_sub li').hide();
		}
		else{
			$(input).parents('.select_table').find('.ms_list_sub li').hide();
			$li.show();
		}
	}
	else{
		$(input).parents('.select_table').find('.ms_list_sub li').show();
	}
	$(obj+' #select_sel option').each(function(index){
		if($(this).attr('selected')=='selected'){
			$(obj+' .select_table .ms_list_sub li').eq(index).hide()
		}
	})
}
function search_block2(obj,input){
	var value = $(input).val();
	var selNum = 0;
	$(obj+' #select_sel option').each(function(index){
		if($(this).attr('selected')=='selected'){
			selNum++
		}
	})
	if(value!=""&&selNum>0){
		var $li = $(input).parents('.ms_selection').find('.ms_list_sub li.selected:contains('+value.trim()+')');
		if($li.length==0){
			$(input).parents('.ms_selection').find('.ms_list_sub li').hide();
		}
		else{
			$(input).parents('.ms_selection').find('.ms_list_sub li').hide();
			$li.show()
		}
	}
	else{
		$(input).parents('.ms_selection').find('.ms_list_sub li').hide();
		$(obj+' #select_sel option').each(function(index){
			if($(this).attr('selected')=='selected'){
				$(obj+' .ms_selection .ms_list_sub li').eq(index).show()
			}
		})
	}
}
String.prototype.trim = function() { 
	return this.replace(/(^\s*)|(\s*$)/g, ''); 
};


function is_url(str_url) {// 验证url
    var str_url = str_url.toLowerCase();
    var strRegex = "^((http|ftp|https)://)(([a-zA-Z0-9\._-]+\.[a-zA-Z]{2,6})|([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}))(:[0-9]{1,4})*(/[a-zA-Z0-9\&%_\./-~-]*)?";
	var re = new RegExp(strRegex);
	return re.test(str_url);
}

function trim(str){
　	return str.replace(/(^\s*)|(\s*$)/g, "");
}
