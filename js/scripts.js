$(document).ready(function() {
            if (typeof Html5Qrcode === 'undefined') {
                console.error('Html5Qrcode library is not loaded. Please check if the library URL is correct.');
                return;
            }

            function startQrScanner(elementId, inputElementId) {
                const html5QrCode = new Html5Qrcode(elementId);
                const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                    $(inputElementId).val(decodedText);
                    html5QrCode.stop().then(() => {
                        $('#' + elementId).hide();
                        $('#modal-text').text(decodedText);
                        $('#modal').show();
                    }).catch(err => {
                        console.error("Error stopping qr code scanner.", err);
                    });
                };
                const config = { fps: 10, qrbox: 250 };
                html5QrCode.start({ facingMode: "environment" }, config, qrCodeSuccessCallback).catch(err => {
                    console.error("Error starting qr code scanner.", err);
                });
            }

            $('#escanear_codigo').click(function() {
                $('#qr-reader-codigo').show();
                console.log("Escanear Código clicked");
                startQrScanner("qr-reader-codigo", "#codigo");
            });

            $('#escanear_buscar').click(function() {
                $('#qr-reader-buscar').show();
                console.log("Escanear Buscar clicked");
                startQrScanner("qr-reader-buscar", "#buscar");
            });

            $('#mostrar_lista').click(function() {
                $('#formulario').hide();
                $('.tools').show();
            });

            $('#mostrar_formulario').click(function() {
                $('.tools').hide();
                $('#formulario').show();
            });

            // Mostrar la lista por defecto
            $('#formulario').hide();
            $('.tools').show();

            // Modal
            var modal = document.getElementById("modal");
            var span = document.getElementsByClassName("close")[0];

            span.onclick = function() {
                modal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
        });