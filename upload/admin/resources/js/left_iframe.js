	$(document).ready(function(){
		$("#main-nav ul li a").click(function (){
			var s = $(this).attr("rel") + '&rd=' + Math.random();
			var prabd = window.parent.document.getElementById("rightIframe");
			$(prabd).attr("src", s);
		});
	});

	(function(){
		try{
			var db = Math.max(document.body.offsetHeight,document.body.clientHeight);
			var prarentH = Math.max(window.parent.document.documentElement.offsetHeight,window.parent.document.documentElement.clientHeight);
			var prabd = window.parent.document.getElementById("leftIframe");
			var r_iframe = window.parent.document.getElementById("rightIframe");
			var rdom =Math.random();//»º´æÎÊÌâ
			
			if(prarentH > db){
				prabd.style.height =  prarentH+rdom+'px';
				r_iframe.style.height =  prarentH+rdom+'px';
			}else{
				prabd.style.height = db+'px';
				r_iframe.style.height = db+'px';
			}
		}catch(e){}
	})()