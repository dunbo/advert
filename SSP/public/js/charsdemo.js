$(function(){
	var attr = [0];
	var data = [{title:'收益',data:[131, 115, 165, 135, 12, 13, 10,200,143,110,89,233,434,232]},{title:'点击量',data:[178, 488, 234, 334, 12, 454, -35,40,211,123,453,565,676,454]},{title:'点击率',data:[1331, 2115, 1365, 1835, 123, 133,1330,3434,2323,2442,899,2323,1239,700]},{title:'曝光量',data:[23231, 11115, 2365, 18335, 10243, 13043, 13430,8900,23242,4242,12032,34344,23232,12123]}];
	//默认显示第一个数据
	char(attr)
	$('#chart_lenge li').click(function(){
		var index = $(this).attr('data-index');
		var $obj = $(this).find('input');
		if(typeof($obj.attr("checked"))=="undefined"){
			$obj.prop('checked',true);
			$obj.attr('checked',true);
			$(this).addClass('selected');
			attr.push(index);
			if(attr.length>2){
				var indexN = attr[0];
				$('#chart_lenge li').eq(indexN).find('input').removeAttr('checked');
				$('#chart_lenge li').eq(indexN).removeClass('selected');
				attr.splice(0, 1);
			}
		}
		else{
			if(attr.length==2){
				$obj.removeProp('checked');
				$obj.removeAttr('checked');
				$(this).removeClass('selected');
				for(var i =0; i<attr.length; i++){
					if(attr[i]==index){
						attr.del(i);
						attr = attr.del(i);
					}
				}
			
			}	
		}
		char(attr)
	})
})
//删除数组某个值
Array.prototype.del = function t(n) {									
	if (n < 0){
		return this;}
	else {
		return this.slice(0, n).concat(this.slice(n + 1, this.length));
	}
}
//echars绘制图表
function char(attr){
	var myChart = echarts.init(document.getElementById('chart_ad'));
	var data = [{title:'收益',data:[131, 115, 165, 135, 12, 13, 10,200,143,110,89,233,434,232]},{title:'点击量',data:[178, 488, 234, 334, 12, 454, -35,40,211,123,453,565,676,454]},{title:'点击率',data:[1331, 2115, 1365, 1835, 123, 133,1330,3434,2323,2442,899,2323,1239,700]},{title:'曝光量',data:[23231, 11115, 2365, 18335, 10243, 13043, 13430,8900,23242,4242,12032,34344,23232,12123]}];
	if(attr.length ==1){
		var index = attr[0];
		option = {
			tooltip : {   //设置提示框
		        trigger: 'axis'
		    },
		    grid:{    //设置图表左右上下边距
                x:80,
                y:50,
                x2:80,
                y2:60
            },
			legend: {  
				data:[data[index].title],
				show:false
			},
			toolbox: {
				show : false,
				feature : {
				    mark : {show: true},
				    dataView : {show: true, readOnly: false},
				    magicType : {show: true, type: ['line', 'bar']},
				    restore : {show: true},
				    saveAsImage : {show: true}
				},
			},
			calculable : true,
			xAxis : [
				{
				    type : 'category',
				    boundaryGap : false,
				    data : ['2017-08-15','2017-08-18','2017-08-21','2017-08-24','2017-08-27','2017-08-30','2017-09-03','2017-09-06','2017-09-09','2017-09-12','2017-09-15','2017-09-16','2017-09-17','2017-09-18'],
				     axisLabel :{  
					    interval:0  //x轴间隔多少个类别画栅格
					} 
				}
			],
		    yAxis : [
				{
				    name:data[index].title,
				    type : 'value'
				}
			],
			series : [
				{
				    name:data[index].title,
				    type:'line',
				    data:data[index].data
				}
			]
		};
		myChart.setOption(option,true);
	}
	if(attr.length==2){
		var index1 = attr[0];
		var index2 = attr[1];
		option = {
			tooltip : {
		        trigger: 'axis'
		    },
			legend: {
				data:[data[index1].title,data[index2].title],
				show:false
			},
			toolbox: {
				show : false,
				feature : {
				    mark : {show: true},
				    dataView : {show: true, readOnly: false},
				    magicType : {show: true, type: ['line', 'bar']},
				    restore : {show: true},
				    saveAsImage : {show: true}
				}
			},
			calculable : true,
			xAxis : [
				{
				    type : 'category',
				    boundaryGap : false,
				     data : ['2017-08-15','2017-08-18','2017-08-21','2017-08-24','2017-08-27','2017-08-30','2017-09-03','2017-09-06','2017-09-09','2017-09-12','2017-09-15','2017-09-16','2017-09-17','2017-09-18'],
				     axisLabel :{  
					    interval:0   
					} 
				}
			],
		    yAxis : [
				{
				    name:data[index1].title,
				    type : 'value'
				},
				{
					name:data[index2].title,
				    type : 'value'
				}
			],
			series : [
				{
				    name:data[index1].title,
				    type:'line',
				    data:data[index1].data,
				    yAxisIndex:0
				},
				{
					name:data[index2].title,
				    type:'line',
				    data:data[index2].data,
				    yAxisIndex:1
				}
			]
		};
		myChart.setOption(option);
	}
}