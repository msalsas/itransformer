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
	$("<br>").insertAfter("label")
	$("<br>").insertAfter("input[type=text]", "input[type=password]")
	$("<br>").insertBefore("input[type=submit]")
	$("<br><br>").insertAfter("input[type=submit]")
	$("input[type=submit], button").button()
	$(".error, article li").css({color: "red", "font-family": "Sans-serif"})
	$("li").each(function(indice,valor){
		if($(this).text()=='') {
		$(this).remove()
		}
	})
});
