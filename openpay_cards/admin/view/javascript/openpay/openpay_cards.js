$(document).ready(function(){
    var country = $('#country').val();
    console.log('openpay_cards.js', country);
    showOrHideElements(country);

    function showOrHideElements(country) {        
        if (country === 'CO') {            
            jQuery("#input-iva").closest(".form-group").show();
            jQuery("#payment_openpay_cards_installments").closest(".form-group").show();
            
            jQuery("#select-charge-type").closest(".form-group").hide();
            jQuery("#capture").closest(".form-group").hide();
            jQuery("#use_card_points").closest(".form-group").hide();
            jQuery("#months_interest_free").closest(".form-group").hide();             
        } else if (country === 'MX') {            
            jQuery("#input-iva").closest(".form-group").hide();  
            jQuery("#payment_openpay_cards_installments").closest(".form-group").hide();
                          
            jQuery("#select-charge-type").closest(".form-group").show();
            jQuery("#capture").closest(".form-group").show();
            jQuery("#use_card_points").closest(".form-group").show();
            jQuery("#months_interest_free").closest(".form-group").show();                    
        }
    }

    jQuery('#country').change(function () {
        var country = jQuery(this).val();      

        showOrHideElements(country)
    });

    if($("input[name='payment_openpay_cards_mode']").length){
        is_sandbox();

        $("input[name='payment_openpay_cards_mode']").on("change", function(e){
            is_sandbox();
        });
    }

    function is_sandbox(){
        sandbox  = $("input[name='payment_openpay_cards_mode']:checked").val();
        if(sandbox == 1){
            jQuery("input[name*='live']").parent().parent().hide();
            jQuery("input[name*='test']").parent().parent().show();
        }else{
            jQuery("input[name*='test']").parent().parent().hide();
            jQuery("input[name*='live']").parent().parent().show();
        }
    }
});