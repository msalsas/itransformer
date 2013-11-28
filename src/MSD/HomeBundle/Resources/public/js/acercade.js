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
	animar()
});

function animar() {
	var parr = $("article").children()
	var thisparr = Array(2)
	parr.each(function(index){
		thisparr[index] = $(this)
		//console.log(thisparr[0])
		
			thisparr[index].animate({
			marginTop: "1%"
			}, (index+1)*6000, "easeOutElastic")	
			
			.animate({
			marginTop: "40%"
			}, (index+1)*4000, "easeInQuint")
			.queue(function(sig){
                    animar();
                    sig();
            })
	})	
}