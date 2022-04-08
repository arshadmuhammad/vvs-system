// available_qty
jQuery(document).ready(function () {
    jQuery('.product_id').change(function() {
        var product_id = jQuery( this ).val();
        jQuery.get(
            'product/qty?product_id='+product_id,
            function (data, status){
                $('#available_qty').html(data);
            }
        );
    })

})
