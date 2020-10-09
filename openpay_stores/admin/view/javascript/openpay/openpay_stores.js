$(document).ready(function(){
    var country = $('#stores_country').val();
    console.log('openpay_stores.js', country);
    showOrHideElements(country);

    function showOrHideElements(country) {        
        if (country === 'CO') {            
            jQuery("#input-iva").closest(".form-group").show();

        } else if (country === 'MX') {            
            jQuery("#input-iva").closest(".form-group").hide();                 
        }
    }

    jQuery('#stores_country').change(function () {
        var country = jQuery(this).val();      

        showOrHideElements(country)
    });

    if($("input[name='payment_openpay_stores_mode']").length){
        is_sandbox();

        $("input[name='payment_openpay_stores_mode']").on("change", function(e){
            is_sandbox();
        });
    }

    function is_sandbox(){
        sandbox  = $("input[name='payment_openpay_stores_mode']:checked").val();
        if(sandbox == 1){
            jQuery("input[name*='live']").parent().parent().hide();
            jQuery("input[name*='test']").parent().parent().show();
        }else{
            jQuery("input[name*='test']").parent().parent().hide();
            jQuery("input[name*='live']").parent().parent().show();
        }
    }
});