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
const ajaxSubmit = require('jquery-form');

$(function() {
    var form = $('form[name="image"]')[0];

    if (form) {
        form.reset();
        var $form = $(form);

        var tam_max = 3145728;
        var $fileInput = $form.find('input[type="file"]');
        var _self = this;
        $fileInput.bind('change', function (e) {
            e.preventDefault();
            var files = this.files;

            var $file = files === "undefined" ? $fileInput : files[0];
            console.log($file);
            if (!isValid($file, $fileInput)) {
                submit($form);
                showError($file, $fileInput);
            } else {
                submit($form);
            }

            function submit($form) {
                var httpRequest;
                if (window.XMLHttpRequest) {
                    httpRequest = new XMLHttpRequest();
                } else if (window.ActiveXObject) {
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
                    $form.submit();
                } else {
                    var bar = $('.bar');
                    var percent = $('.percent');
                    var status = $('#status');

                    $form.ajaxSubmit({
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
                            $(".wait").remove();
                            window.scrollTo(0, 0);
                        }
                    });

                }
            }

            function isValid($file, $fileInput) {
                return $file.size <= tam_max && $fileInput.val().match('.+\\.(jpe?g|png|wbmp|gif|JPE?G|PNG|WBMP|GIF)$');
            }

            function showError($file, $fileInput) {
                var error = '';

                if ($file.size > tam_max) {
                    error += '\nFile size: ' + $fileInput.size + ' Bytes. Too large. MAX: ' + tam_max + ' Bytes. \n';
                }

                //if($file.val().match('.+\\.(jpe?g|png|wbmp|gif|JPE?G|PNG|WBMP|GIF)$')==null){
                error += '\nFormat not accepted. Accepted formats: jpg, jpeg, png, wbmp and gif.';
                //}

                alert(error)
                window.scrollTo(0, 0);
                var htmlError = '<div id="todo"></div><div id="botones"><div id="error">' + error + '</div><input value="OK" id="volver" style="width: 20%" onclick="$(\'#todo\').remove(); $(\'#botones\').remove();"></div>';

                $('#agrupar').after(htmlError);
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

                form.reset();
            }

        });
    }

});
