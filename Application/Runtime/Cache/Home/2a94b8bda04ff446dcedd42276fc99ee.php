<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no, width=device-width">
	<meta charset="UTF-8">
	<link rel="stylesheet" href="http://cache.amap.com/lbs/static/main1119.css"/>
	<link rel="stylesheet" href="/Wechat/Public/css/main.css" />
    <script src="http://cache.amap.com/lbs/static/es5.min.js"></script>
    <script src="http://webapi.amap.com/maps?v=1.3&key=572120c292a2280d130e364e2cc8510f"></script>
    <script type="text/javascript" src="http://cache.amap.com/lbs/static/addToolbar.js"></script>
    <link rel="stylesheet" href="/Wechat/Public/css/button.css">
    <link rel="stylesheet" href="/Wechat/Public/css/AMap.CloudDataSearchRender1120.css">
    <script type="text/javascript" src="/Wechat/Public/js/AMap.CloudDataSearchRender.js"></script>
	<title>Main</title>
</head>
<body>
	<div class="main">
		<div class="menu">
			<ul class="bar">
				<li>广外官网</li>
				<li>建筑介绍</li>
				<li>微信服务</li>
				<li><img src="/Wechat/Public/pics/classify.png" alt="" id="classifyIcon"></li>
			</ul>
		</div>
		<div class="transfer">
			<div class="button" id="travelN">跳转到北校 .</div>
			<div class="button" id="travelS">跳转到南校 .</div>
		</div>
		<div id="tip"></div>
		<div><img src="/Wechat/Public/pics/search.png" alt="" id="startSearch"></div>
		<div id="classify">
			<ul>
			    <li id="dom"><a href="#">宿舍</a></li>
			    <li id="classroom"><a href="#">教室</a></li>
			    <li id="firstPlace"><a href="#">新生签到</a></li>
          <li id="canteen"><a href="#">饭堂</a></li>
          <li id="other"><a href="#">其余设施</a></li>
			</ul>
		</div>
		<div id="feedback"></div>
		<div id="welcome"><p>欢迎来到广东外语外贸大学</p><i id="num">3</i></div>
		<div id="container"></div>
		<div class="plugin"><button id="range">显示/隐藏校区范围</button></div>
	</div>
	<span id="status">0</span>
	<span id="searchStatus">0</span>
  <input type="hidden" id="area" value="s">
</body>
</html>










<script>

    var map = new AMap.Map('container', {
    	zooms: [13,18],
        resizeEnable: true,
        zoom:16,
        center: [113.397259, 23.062343]
    });

    //定位插件
    AMap.plugin('AMap.Geolocation',function(){
    	var geolocation = new AMap.Geolocation({
    		enableHighAccuracy:true,
    		timeout:10000,
    		convert: true
    	});
    	map.addControl(geolocation);
        geolocation.watchPosition();
        AMap.event.addListener(geolocation, 'error', onError);
      	//解析定位错误信息
    	function onError(data) {
    		if(data.info) {
        	var tip = document.getElementById('tip');
        	tip.style.display="block";
        	tip.innerHTML='定位失败';
    	}
    }


    }); //定位插件


    //云图载入插件
    AMap.plugin('AMap.CloudDataLayer', function() {
         var layerOptions = {
            query: {},
            clickable: true
        };
        var cloudDataLayer = new AMap.CloudDataLayer('573aa0f4305a2a57d8ce2103', layerOptions);
            // cloudDataLayer.setMap(map);

	    AMap.event.addListener(cloudDataLayer,'click', function(result) {
	        var clouddata = result.data;
	        var infoWindow = new AMap.InfoWindow({  //云图marker信息窗口
	            content: "<h3><font face='微软雅黑'color='#36F'><p  class='close'>" + 
	                clouddata._name +"</p></br><p class='close'><点击地图任意处关闭></p>", 
	            size: new AMap.Size(250, 0),
	            autoMove: true,
	            offset: new AMap.Pixel(0, -25)
	        });
	        infoWindow.open(map, clouddata._location);
	    });



    	//分类展示云图数据
	    AMap.event.addListener(map,'click',function(){
	    	map.clearInfoWindow();
	    	document.getElementById('classify').style.display='none';
		});

	    window.search;
      var searchObj = [];
      searchObj[0] = document.getElementById('dom');
      searchObj[1] = document.getElementById('classroom');
      searchObj[2] = document.getElementById('firstPlace');
      searchObj[3] = document.getElementById('canteen');
      searchObj[4] = document.getElementById('other');
	    AMap.event.addDomListener(searchObj,'click',function(){
        var label;
        switch(searchObj.id)
        {
          case "dom": label = "宿舍"; break;
          case "classroom": label = "教学楼"; break;
          case "firstPlace": label = "签到"; break;
          case "canteen": label = "饭堂"; break;
          case "other": label = "公共"; break;
        }
		    	var searchDom = {
		    	map:map,
		    	panel:'feedback',
		    	keywords:label,
		    	pageSize:4,
		    	orderBy:'_id:ASC'
		    	};
		    AMap.service(["AMap.CloudDataSearch"], function() {
          var area = document.getElementById('area');
            if (area.value == "s") 
            {
		          search = new AMap.CloudDataSearch('573aa0f4305a2a57d8ce2103',searchDom);
		    	    search.searchNearBy([113.397259, 23.062343] , 10000, function(){});
            }else if (area.value == "n")
            {
              search = new AMap.CloudDataSearch('573aa0f4305a2a57d8ce2103',searchDom);
              search.searchNearBy([113.292429, 23.200438] , 10000, function(){});
            } 
		      });
		    document.getElementById('feedback').style.display='block';
		    document.getElementById('classify').style.display='none';
		    document.getElementById('searchStatus').innerHTML=1;
		    document.getElementById('classifyIcon').src="pics/clean.png";
	    	});


	});  //云图载入插件


	    
	    AMap.event.addDomListener(document.getElementById('classifyIcon'),'click',function(){
	    	var classify = document.getElementById('classify');
	    	var searchStatus = document.getElementById('searchStatus');
	    	if (searchStatus.innerHTML == 0) {
	    		classify.style.display='block';
	    		classify.style.webkitAnimationName='fadeIn';
	    	}else{
	    		search.clear();
	    		document.getElementById('searchStatus').innerHTML = 0;
	    		document.getElementById('classifyIcon').src = "pics/classify.png";
	    		document.getElementById('feedback').style.display = "none";
	    	}
	    });





    //南北校跳转函数
    AMap.event.addDomListener(document.getElementById('travelN'), 'click', function() {
        // 设置缩放级别和中心点
        map.setZoomAndCenter(16, [113.292429, 23.200438]);
        document.getElementById('area').value = "n";
    });
    AMap.event.addDomListener(document.getElementById('travelS'), 'click', function() {
        // 设置缩放级别和中心点
        map.setZoomAndCenter(16, [113.397259, 23.062343]);
        document.getElementById('area').value = "s";
    });

    //加载南校图层
    var Sbound = new AMap.Bounds([113.394500,23.056373],[113.40450,23.071602]);
    var ImageLayerOptions = {
    	bounds:Sbound,
    	url:"pics/southBound.jpg",
    	opacity:0.5,
    	zIndex:11
    }
   	var southBound = new AMap.ImageLayer(ImageLayerOptions);
   	southBound.setMap(map);
   	southBound.hide();
   	var listener = AMap.event.addDomListener(document.getElementById('range'),'click',function(){
   		var status = document.getElementById('status');
   		if (status.innerHTML==0) 
   		{
   			southBound.show();
   			status.innerHTML = 1;
   		}else{
   			southBound.hide();
   			status.innerHTML = 0;
   		}
   	});//加载南校图层

   	//welcome倒计时
   	var id = setInterval("countDown()",1000);
   	var num = document.getElementById('num');
   	function countDown(){
   		var next = parseInt(num.innerHTML);
   		num.innerHTML = next-1;
   	}

   	function hideWelcome(){
   		document.getElementById('welcome').style.display='none';
   		clearInterval(id);
   		delete num;
   		delete id;
   	}

   	setTimeout("hideWelcome()", 3000);
   	//welcome倒计时
</script>