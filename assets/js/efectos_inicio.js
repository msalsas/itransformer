const $ = require('jquery');

$(function(){ 
	
	$("#form").show(500);

	animarRojoInput();

    function animarRojoInput() {
        var input = $("input[type='file']")

        input.animate({
            "borderColor": "#FF0000"
        }, 1500)

        .animate({
            "borderColor": "#FFFFFF"
        }, 1500)
        .queue(function(next){
                animarRojoInput();
                next();
        });
    }
});
