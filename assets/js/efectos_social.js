const $ = require('jquery');

$(function(){

/* Set image carrete size */

	function set_src() {
	  var window_width = $(window).width();
	  if (window_width < 1147) {
	      $("#img_cabecera2").css({"display":"none"});    
	  } else {
		  $("#img_cabecera2 img").attr('width', "300").attr('src', "/img/carrete.png").attr('alt', "logo").attr('height','auto');
	      $("#img_cabecera2").css({"top":"15px","left": "44%","display":"block"});
	  }
	}
	

	   set_src();

	
	$(window).resize(function() {
	    set_src();
	});

/* ************************* */

	$("#buttonCookies").on("click", function() {
			$(this, "#ini_mess").fadeOut();
			$("#ini_mess").fadeOut();
		})
	$("#languages a").each(function(){
			switch($(this).text()) {
					case "es":
					$(this).html('<span style="color:#CC2222;">E</span><span style="color:#FCBC75;">S</span>');
					break;
					
					case "en":
					$(this).html('<span style="color:#CC2222;">E</span><span style="color:blue;">N</span>');
					break;
					
					case "fr":
					$(this).html('<span style="color:white;">F</span><span style="color:#5194DB;">R</span>');
					break;
					
					case "de":
					$(this).html('<span style="color:#FCBC75;">D</span><span style="color:black;">E</span>');
					break;
				}
		}); 

});
