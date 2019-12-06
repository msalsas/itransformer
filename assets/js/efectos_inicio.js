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

const $ = require('jquery');

$(function(){ 
	
	$("#form").show(500)
	
		
	//Animar input adjuntar imagen:
	

	animarRojoInput()


function animarRojoInput() {
	var input = $("input[type='file']")
		
			input.animate({
			"borderColor": "#FF0000"
			}, 1500)	
			
			.animate({
			"borderColor": "#FFFFFF"
			}, 1500)
			.queue(function(sig){
                    animarRojoInput();
                    sig();
            })
		
}

	
});
