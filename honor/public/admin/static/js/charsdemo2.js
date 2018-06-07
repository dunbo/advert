var myChart = echarts.init(document.getElementById('chart_ad'));
				option = {
				    legend: {
				        data:['收益','点击量']
				    },
				    toolbox: {
				        show : true,
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
				            data : ['周一','周二','周三','周四','周五','周六','周日']
				        }
				    ],
				    yAxis : [
				        {
				        	name:'收益',
				            type : 'value',
				            axisLabel : {
				                formatter: '{value}'
				            }
				        },
				        {
				        	name:'点击量',
				            type : 'value',
				            axisLabel : {
				                formatter: '{value}'
				            }
				        }
				    ],
				    series : [
				        {
				            name:'收益',
				            type:'line',
				            data:[131, 115, 165, 135, 12, 13, 10]
				        },
				        {
				            name:'点击量',
				            type:'line',
				            data:[178, 488, 234, 334, 12, 454, -35]
				        }
				    ]
				};
				myChart.setOption(option);