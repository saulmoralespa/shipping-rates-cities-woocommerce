(function($){
    $('#shipping_rates_cities_wc_sr_upload').on('change', function(e) {
        e.preventDefault();

        let form = $(this).parents('form');

        let shipping_rates_cities_wc_sr_excel = form.find('input[name=shipping_rates_cities_wc_sr_excel]').val();

        let ext = e.target.value.split(".").pop().toLowerCase();

        if (ext !== 'xls'){
            Swal.fire(
                'Error tipo de archivo',
                'El nombre del excel debe tener la extensión .xsl',
                'warning'
            );
            return;
        }

        let xsl = e.target.files[0];

        if (xsl.size < 30000){
            Swal.fire(
                'Error',
                'Esta subiendo el archivo incompleto o vacío, verifique',
                'warning'
            );
            return;
        }

        let fd = new FormData();
        fd.append('shipping_rates_cities_wc_sr_xsl', xsl);
        fd.append('action', 'shipping_rates_cities_wc_sr_db');
        fd.append('shipping_rates_cities_wc_sr_excel', shipping_rates_cities_wc_sr_excel);
        upload(fd);
    });

    function upload(fd){
        $.ajax({
            data: fd,
            type: 'POST',
            contentType: false,
            processData: false,
            url: ajaxurl,
            dataType: "json",
            beforeSend : () => {
                Swal.fire({
                    title: 'Subiendo información',
                    onOpen: () => {
                        Swal.showLoading()
                    },
                    allowOutsideClick: false
                });
            },
            success: (r) => {
                if (r.status){
                    Swal.fire({
                        title: '',
                        text: 'Información guardada exitosamente',
                        type: 'success',
                        showConfirmButton: false
                    });
                    window.location.reload();
                }else{
                    Swal.fire(
                        'Error',
                        r.message,
                        'error'
                    );
                }
            }
        });
    }
})(jQuery);