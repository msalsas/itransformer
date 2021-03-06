const $ = require('jquery');

$(function() {
    var fileUpload = $("#fileupload")[0];

    if (fileUpload) {
        fileUpload.reset();
    }

    var submitInput = $("input[type=submit]");
    if (submitInput.length > 0) {
        submitInput.button();
    }

	var tam_max = 3145728;
	var file = $('input[type=file]');

    if (file.length > 0) {
        file.button();
        file.css({
            overflow: "hidden"

        });

        file.bind('change', function (e) {
            e.preventDefault();
            var fileOk;
            if (typeof this.files === "undefined") fileOk = file;
            else fileOk = this.files[0];
            if (!fileOk) {
                return;
            }
            if (fileOk.size > tam_max || file.val().match('.+\.(jpe?g|png|wbmp|gif|JPE?G|PNG|WBMP|GIF)$') == null) {
                var alerta = '';


                if (fileOk.size > tam_max) {
                    alerta += '\nFile size: ' + this.files[0].size + ' Bytes. Too large. MAX: ' + tam_max + ' Bytes. \n';
                }

                if (file.val().match('.+\.(jpe?g|png|wbmp|gif|JPE?G|PNG|WBMP|GIF)$') == null) {
                    alerta += '\nFormat not accepted. Accepted formats: jpg, jpeg, png, wbmp and gif.';
                }


                window.scrollTo(0, 0);
                var respuesta = '<div id="todo"></div><div id="botones"><div id="error">' + alerta + '</div><input value="OK" id="volver" style="width: 20%" onclick="jQuery(\'#todo\').remove(); jQuery(\'#botones\').remove();"></div>';

                $('#agrupar').after(respuesta);
                $("input").button();
                $("#botones").css({
                    position: "absolute",
                    top: "2%",
                    left: "15%",
                    width: "60%",
                    marginTop: "0",
                    marginLeft: "0",
                    fontSize: "1.5em",
                    border: "1px solid #999999",
                    backgroundColor: "rgba(200,200,200,0.5)",
                    padding: "2%"
                })
                $("#todo").css({
                    position: "absolute",
                    top: "0",
                    left: "0",
                    width: "100%",
                    height: "150%",
                    backgroundColor: "rgba(20,20,20,0.5)"
                })
                $("button").css({fontSize: "1.2em", cursor: "pointer"})

                $("#fileupload")[0].reset();
            }
            else {
                var httpRequest;
                if (window.XMLHttpRequest) {
                    //El explorador implementa la interfaz de forma nativa
                    httpRequest = new XMLHttpRequest();
                }
                else if (window.ActiveXObject) {
                    //El explorador permite crear objetos ActiveX
                    try {
                        httpRequest = new ActiveXObject("MSXML2.XMLHTTP");
                    } catch (e) {
                        try {
                            httpRequest = new ActiveXObject("Microsoft.XMLHTTP");
                        } catch (e) {
                        }
                    }
                }
                if (!httpRequest) {
                    $("#fileupload").submit();
                } else {
                    var bar = $('.bar');
                    var percent = $('.percent');
                    var status = $('#status');

                    $('#fileupload').ajaxSubmit({
                        beforeSend: function () {
                            status.empty();
                            var percentVal = '0%';
                            bar.width(percentVal)
                            percent.html(percentVal);
                            $(".in_file").hide();
                            $(".progress").after('<div class="wait">Wait...</div>')
                        },
                        uploadProgress: function (event, position, total, percentComplete) {
                            var percentVal = percentComplete + '%';
                            bar.width(percentVal)
                            percent.html(percentVal);
                        },
                        success: function () {
                            var percentVal = '100%';
                            bar.width(percentVal)
                            percent.html(percentVal);
                        },
                        complete: function (xhr) {
                            $('#agrupar').after(xhr.responseText);
                            $('#img_cargada').show()
                            $("input").button();
                            $(".wait").remove();
                            window.scrollTo(0, 0);
                        }
                    });

                }
            }

        });
    }
				
});
