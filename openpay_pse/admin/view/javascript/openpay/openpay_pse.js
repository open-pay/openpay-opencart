$(document).ready(function(){
    console.log('openpay_pse.js');

    if($("input[name='payment_openpay_pse_mode']").length){
        is_sandbox();

        $("input[name='payment_openpay_pse_mode']").on("change", function(e){
            is_sandbox();
        });
    }

    function is_sandbox(){
        sandbox  = $("input[name='payment_openpay_pse_mode']:checked").val();
        if(sandbox == 1){
            jQuery("input[name*='live']").parent().parent().hide();
            jQuery("input[name*='test']").parent().parent().show();
        }else{
            jQuery("input[name*='test']").parent().parent().hide();
            jQuery("input[name*='live']").parent().parent().show();
        }
    }
});