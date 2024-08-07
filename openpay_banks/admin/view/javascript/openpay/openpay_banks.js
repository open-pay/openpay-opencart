$(document).ready(function(){
    console.log('openpay_banks.js');

    var banks_mode =$("input[name='payment_openpay_banks_mode']").length
    console.log(banks_mode)

    if($("input[name='payment_openpay_banks_mode']").length){
        is_sandbox();

        $("input[name='payment_openpay_banks_mode']").on("change", function(e){
            is_sandbox();
        });
    }

    function is_sandbox(){
        sandbox  = $("input[name='payment_openpay_banks_mode']:checked").val();
        if(sandbox == 1){
            jQuery("input[name*='live']").parent().parent().hide();
            jQuery("input[name*='test']").parent().parent().show();
        }else{
            jQuery("input[name*='test']").parent().parent().hide();
            jQuery("input[name*='live']").parent().parent().show();
        }
    }
});