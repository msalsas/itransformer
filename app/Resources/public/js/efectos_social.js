/*
 * Itransformer.es is an online application to transform images
Copyright (C) 2013  Manolo Salsas

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as
published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

Contact: manolez@gmail.com - http://msalsas.com
* */



$(function(){

/* Set image carrete size */

	function set_src() {
	  var window_width = $(window).width();
	  if (window_width < 1147) {
	      $("#img_cabecera2 img").attr('width', 80).attr('src', "/public/img/carrete_mvl.png");
	      $("#img_cabecera2").css({"top":"140px","left": "5px"});      
	  } else {
		  $("#img_cabecera2 img").attr('width', 300).attr('src', "/public/img/carrete.png");
	      $("#img_cabecera2").css({"top":"15px","left": "44%"});
	  }
	}
	

	   set_src();

	
	$(window).resize(function() {
	    set_src();
	});

/* ************************* */


	$("#FLink, #TLink, #GLink").on('mouseenter',function(){
		
		$(this).animate({"margin-right": "5px",
						 "opacity": "0.4"
			}, 500, "easeOutBounce");	
		});
	$("#FLink, #TLink, #GLink").on('mouseleave',function(){
		$(this).animate({"margin-right": "0px",
						 "opacity": "1"
			}, 500, "easeOutBounce");
		});

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
