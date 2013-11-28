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

$("#img_cargada").attr("display","none")	
	//********************************************************
	//************* Seleccionar formulario *******************
	//********************************************************
	
	$("#basico").on("click", function(){
		$("#formulario_filtros").hide()
		$("#formulario_efectos").hide()
		$("#formulario_basico").show()
		$("#basico").css("background-color","#ffffff")
		$("#filtros").css("background-color","#cccccc")
		$("#efectos").css("background-color","#cccccc")
	})
	$("#filtros").on("click", function(){
		$("#formulario_basico").hide()
		$("#formulario_efectos").hide()
		$("#formulario_filtros").show()
		$("#filtros").css("background-color","#ffffff")
		$("#basico").css("background-color","#cccccc")
		$("#efectos").css("background-color","#cccccc")
	})
	$("#efectos").on("click", function(){
		$("#formulario_filtros").hide()
		$("#formulario_basico").hide()
		$("#formulario_efectos").show()
		$("#efectos").css("background-color","#ffffff")
		$("#filtros").css("background-color","#cccccc")
		$("#basico").css("background-color","#cccccc")
		
	})

	//**********************************************************
	//************* Pasar datos por AJAX ***********************
	//**********************************************************
	
	formularioAjax($("#form_basico"));	
	formularioAjax($("#form_filtros"));
	formularioAjax($("#form_efectos"));
	

	//************** Ir a la imagen anterior ***************************
	
	var enlace = $(".atras");
	enlace.on("click", function(e){	
	
		e.preventDefault();
		$("#error_ajax").text("");
		var offsetImg = $("#columna").offset(); 
		$(document).scrollTop(offsetImg.top);
		var nombre = $("#img_cargada").attr("src")
			var img_cargando = $("#img_cargando");
			var img_cargada = $("#img_cargada");
			img_cargada.data("height",$("#img_cargada").attr("height"))
			img_cargando.css("margin-top",($("#img_cargada").data("height")-40)/2);
			img_cargando.show()
			//ocultar imagen
			img_cargada.hide(500);
			objAjax = $.ajax ({
			type: "POST",
			url: enlace.attr("formaction"),
			success: function(res){
				$("#imagen").html(res);
				var img_cargada = $("#img_cargada");
				var res = redimensionarImagen();
				var alto_orig = res[1]
				var ancho_orig = res[0]
				var nuevaRuta = img_cargada.attr("src")
				rangosRecorte(ancho_orig,alto_orig)
				},
			error: function(objAjax,estado,excepcion){
				$("#error_ajax").text("Error: Try again").css("color","red");
				},
			timeout: 10000,
			cache: false
			})
			objAjax.always(function(){
				img_cargando.hide()
			})
	});

	$("#form_borrar").on("submit", function(e){
			$("#img_cargada").effect("explode",500)			
	})
});

/**
 * ********************************************************************
/********************** TODO ****************************************
**********************************************************************/

$(function() {
	
	var res = redimensionarImagen();
	var alto_orig = res[1]
	var ancho_orig = res[0]	
	$(".mostrar-ocultar").mostrarOcultar();
	$(".mostrar-ocultar").on("change", function(){
		$(this).mostrarOcultar()
		});
	
	$("input[type=number]").each(function(index, elem) {
		var input = $(this);
		input.attr("title","MIN: " + input.attr("min") + " , MAX: " + input.attr("max"))		
	})
	$("input[type=submit], button, input[type=button]").button();		
	$("input[type=number]").spinner({
	stop: function() {
			if($(this).attr("id") == "dimensionesX" || $(this).attr("id") == "dimensionesY") {
				mantenerProporciones($(this).attr("id"), $("#img_cargada").data("ancho_orig"), $("#img_cargada").data("alto_orig"))	
			}
		}
	})	
	$("#proporciones").on("change",function(){	
		mantenerProporciones("dimensionesX", $("#img_cargada").data("ancho_orig"), $("#img_cargada").data("alto_orig"))
	})
	
	$("input[type=number]").tooltip();
	
	//Canvas para recorte
	

	$("#recortar_izq, #recortar_der, #recortar_arr, #recortar_aba").spinner({
	start: function() {
		img=$("img_cargada")
		
		$("#lienzo").offset({ left:$("#img").offset().left+margen_izquierdo_canvas, top: $("#img").offset().top });
		iniciarCanvas();
		lienzo.clearRect(0,0,$("#lienzo").attr("width"),$("#lienzo").attr("height"))
			
			},
	stop: function() {	
				var izquierda = $("#recortar_izq").val()*$("#img_cargada").attr("width")/$("#img_cargada").data("ancho_orig");
				var derecha = $("#recortar_der").val()*$("#img_cargada").attr("width")/$("#img_cargada").data("ancho_orig");
				var arriba = $("#recortar_arr").val()*$("#img_cargada").attr("height")/$("#img_cargada").data("alto_orig");
				var abajo = $("#recortar_aba").val()*$("#img_cargada").attr("height")/$("#img_cargada").data("alto_orig");
				lienzo.strokeRect(izquierda, arriba, $("#lienzo").attr("width")-izquierda-derecha, $("#lienzo").attr("height")-abajo-arriba)
			}
	});
});	

/***********************************************************************
 * ******************** Calcular valor divisor de **********************
 * ******************** la matriz de convolución ***********************
 * *********************************************************************
*/

$(function() {

	$("#val_convolucion_matriz, #divisor_conv_auto").on('keyup click', function(){
		if($("#divisor_conv_auto").is(":checked")) {
			var divisor = calcularDivConvol();
			$("#num_convolucion_divisor").val(divisor)
		}
	})
})

function recalcularAlto(alto_orig,ancho_orig) {
	var nuevo_alto = Math.round(alto_orig * $("#dimensionesX").val() / ancho_orig);
	return nuevo_alto
}
function recalcularAncho(alto_orig,ancho_orig) {
	var nuevo_ancho = Math.round(ancho_orig * $("#dimensionesY").val() / alto_orig);
	return nuevo_ancho
}
function redimensionarImagen() {
	
//redimensionar imagen a ancho x proporción
	var ancho = $("#img").css("width")
	var ancho = parseInt(ancho)
	var img = $("#img_cargada")
	var res = new Array(2)
	res[0] = img.attr("width")
	res[1] = img.attr("height")
	if(res[0]>ancho) {
		var alto = res[1]/(res[0]/ancho)
		img.attr("width", ancho)
		img.attr("height", alto)
		margen_izquierdo_canvas = 0;
	} else if (res[0]<ancho) {
		margen_izquierdo_canvas = Math.round((ancho-res[0])/2); 
	} else margen_izquierdo_canvas = 0;

	//mostrar imagen
	img.show(500);	
	
	$("#dimensionesX").val(res[0])
	$("#dimensionesY").val(res[1])
	
	$("#lienzo").attr("width",img.attr("width")).attr("height",img.attr("height"))
	
	
	return res
}

function formularioAjax(formulario) {
	
	//Aplicar formulario 
	formulario.on("submit", function(e){
	
		e.preventDefault();
		$("#error_ajax").text("");
		var offsetImg = $("#columna").offset(); 
		$(document).scrollTop(offsetImg.top);
		var img_cargando = $("#img_cargando");
		var img_cargada = $("#img_cargada");
		img_cargada.data("height",img_cargada.attr("height"))
		img_cargando.css("margin","(img_cargada.data('height')-40)/2 auto")
		img_cargando.show()
		//ocultar imagen
		img_cargada.hide(500);
		objAjax = $.ajax ({
		type: "POST",
		url: formulario.attr("action"),
		data: formulario.serialize(),
		success: function(res){
			$("#imagen").html(res);
			var img_cargada = $("#img_cargada");
			var res = redimensionarImagen();
			var alto_orig = res[1]
			var ancho_orig = res[0]
			$("#dimensionesX").val(ancho_orig)
			mantenerProporciones("dimensionesX", ancho_orig, alto_orig)	
			var nuevaRuta = img_cargada.attr("src")
			$("input[type=checkbox]").attr('checked', false);
			$(".mostrar-ocultar").mostrarOcultar();
			rangosRecorte(ancho_orig,alto_orig)
			$("#recortar_izq, #recortar_der, #recortar_arr, #recortar_aba").val(0);
			},
		error: function(objAjax,estado,excepcion){
			$("#error_ajax").text("Error. Try again").css("color","red");
			},
		timeout: 50000,
		cache: false
		})
		objAjax.always(function(){
			img_cargando.hide()
			
		})
	});
} 

function mantenerProporciones(idInputCheck, ancho_orig, alto_orig){

	if(idInputCheck=="dimensionesX" || idInputCheck=="dimensionesY") {
		var dimensionesX = $("#dimensionesX");
		var dimensionesY = $("#dimensionesY");	
		if($("#proporciones").is(":checked")) {
			if(idInputCheck=="dimensionesX") {
				var nuevo_alto = recalcularAlto(alto_orig,ancho_orig);
				dimensionesY.val(nuevo_alto);
			} else {
				var nuevo_ancho = recalcularAncho(alto_orig,ancho_orig);
				dimensionesX.val(nuevo_ancho);
			}
		}
	}
}

function rangosRecorte(nuevo_ancho, nuevo_alto) {
		
	$("#recortar_izq, #recortar_der").attr("max",nuevo_ancho).attr("title","MIN: " + 0 + " , Rango máximo: " + nuevo_ancho)
	$("#recortar_arr, #recortar_aba").attr("max",nuevo_alto).attr("title","MIN: " + 0 + " , Rango máximo: " + nuevo_alto)
}


function iniciarCanvas() {
		var elemento = document.getElementById('lienzo');
		lienzo = elemento.getContext('2d');
		//lienzo.globalCompositeOperation="copy";
}

function calcularDivConvol() {

var matriz = new Array(
	[$("#num_convolucion_matriz_0").val(),$("#num_convolucion_matriz_1").val(),$("#num_convolucion_matriz_2").val()],
	[$("#num_convolucion_matriz_3").val(),$("#num_convolucion_matriz_4").val(),$("#num_convolucion_matriz_5").val()],
	[$("#num_convolucion_matriz_6").val(),$("#num_convolucion_matriz_7").val(),$("#num_convolucion_matriz_8").val()]
)
	divisor = array_sum(array_map('array_sum', matriz));
	if(divisor == 0){
		divisor = 1;
	}
	return divisor
}

function array_sum (array) {
  // http://kevin.vanzonneveld.net
  // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   bugfixed by: Nate
  // +   bugfixed by: Gilbert
  // +   improved by: David Pilia (http://www.beteck.it/)
  // +   improved by: Brett Zamir (http://brett-zamir.me)
  // *     example 1: array_sum([4, 9, 182.6]);
  // *     returns 1: 195.6
  // *     example 2: total = []; index = 0.1; for (y=0; y < 12; y++){total[y] = y + index;}
  // *     example 2: array_sum(total);
  // *     returns 2: 67.2
  var key, sum = 0;

  if (array && typeof array === 'object' && array.change_key_case) { // Duck-type check for our own array()-created PHPJS_Array
    return array.sum.apply(array, Array.prototype.slice.call(arguments, 0));
  }

  // input sanitation
  if (typeof array !== 'object') {
    return null;
  }

  for (key in array) {
    if (!isNaN(parseFloat(array[key]))) {
      sum += parseFloat(array[key]);
    }
  }

  return sum;
}

function array_map (callback) {
  // http://kevin.vanzonneveld.net
  // +   original by: Andrea Giammarchi (http://webreflection.blogspot.com)
  // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // +   improved by: Brett Zamir (http://brett-zamir.me)
  // %        note 1: Takes a function as an argument, not a function's name
  // %        note 2: If the callback is a string, it can only work if the function name is in the global context
  // *     example 1: array_map( function (a){return (a * a * a)}, [1, 2, 3, 4, 5] );
  // *     returns 1: [ 1, 8, 27, 64, 125 ]
  var argc = arguments.length,
    argv = arguments;
  var j = argv[1].length,
    i = 0,
    k = 1,
    m = 0;
  var tmp = [],
    tmp_ar = [];

  while (i < j) {
    while (k < argc) {
      tmp[m++] = argv[k++][i];
    }

    m = 0;
    k = 1;

    if (callback) {
      if (typeof callback === 'string') {
        callback = this.window[callback];
      }
      tmp_ar[i++] = callback.apply(null, tmp);
    } else {
      tmp_ar[i++] = tmp;
    }

    tmp = [];
  }

  return tmp_ar;
}


function goBack()
  {
  
  window.history.back()
  }
  
