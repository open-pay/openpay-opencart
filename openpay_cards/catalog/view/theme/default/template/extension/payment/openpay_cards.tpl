<link rel="stylesheet" type="text/css" href="/catalog/view/theme/default/stylesheet/openpay_cards.css">

<form action="<?php echo $action ?>" method="POST" id="payment-form" class="form-horizontal">

    <div class="content" id="payment">
        <div class="row" id="header">
            <div class="col-sm-6">
                <h2><?php echo $text_credit_card; ?></h2>
            </div>
        </div>

        <div class="row mb20 mt20">
            <div class="col-md-6">
                <h3>Tarjetas de crédito</h3>
                <div class="row">
                <?php for($i=1;$i<=4;$i++): ?>
                    <div class="col-md-2">
                        <img src="/catalog/view/theme/default/image/credit_cards/<?php echo sprintf('%02d', $i) ?>.png" alt="Tarjetas" class="tiendas">
                    </div>
                <?php endfor; ?>
                </div>
            </div>
            <div class="col-md-6">
                <h3>Tarjetas de débito</h3>
                <div class="row">
                <?php for($i=1;$i<=6;$i++): ?>
                    <div class="col-md-2">
                        <img src="/catalog/view/theme/default/image/debit_cards/<?php echo sprintf('%02d', $i) ?>.png" alt="Tarjetas" class="tiendas">
                    </div>
                <?php endfor; ?>
                </div>
            </div>
        </div>

        <div id="msgBox" role="alert"><i></i><span style="margin-left:10px;"></span></div>

        <div class="form-group">
            <label class="col-sm-2 control-label" for='cc-owner'>
                <?php echo $entry_cc_owner; ?>
            </label>
            <div class="col-sm-4">
                <input type="text" id="cc-owner" class="form-control" data-openpay-card="holder_name" autocomplete="off" >
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label" for='cc-number'>
                <?php echo $entry_cc_number; ?>
            </label>
            <div class="col-sm-4">
                <input type="text" id="cc-number" class="form-control" data-openpay-card="card_number" autocomplete="off" >
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="cc-month">
                <?php echo $entry_cc_expire_date; ?>
            </label>
            <div class="col-sm-2">
                <select id="cc-month" data-openpay-card="expiration_month" class="form-control">
                    <?php foreach ($months as $month) : ?>
                    <option value="<?php echo $month['value']; ?>"><?php echo $month['text']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-sm-2">
                <select id="cc-year" data-openpay-card="expiration_year" class="form-control">
                    <?php foreach ($year_expire as $year) : ?>
                    <option value="<?php echo $year['value']; ?>"><?php echo $year['text']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label" for="cc-cvv">
                <?php echo $entry_cc_cvv2; ?>
            </label>
            <div class="col-sm-2">
                <input id="cc-cvv" type="text" autocomplete="off" data-openpay-card="cvv2" class="form-control" >
            </div>
            <div class="col-sm-2">
                <img data-toggle="popover" data-content="<?php echo $help_cvc_back; ?>" src="/catalog/view/theme/default/image/cvc_back.png" alt="Tarjetas" class="cvv" style="cursor:pointer;">
                <img data-toggle="popover" data-content="<?php echo $help_cvc_front; ?>" src="/catalog/view/theme/default/image/cvc_front.png" alt="Tarjetas" class="cvv" style="cursor:pointer;">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-4 col-sm-offset-2">
                <button type="submit" class="btn btn-primary" id="button-confirm" data-loading-text="Processing"><?php echo $button_confirm; ?></button>
            </div>
        </div>
    </div>
</form>

<script>
'use strict';

    $(document).ready(function(){
        
        $('[data-toggle="popover"]').popover({
            trigger: 'hover',
            'placement': 'top'
        });

        if( typeof OpenPay == 'undefined' ){
            var openpay_library = "https://openpay.s3.amazonaws.com/openpay.v1.min.js";
            var openpay_anti_fraud = "https://openpay.s3.amazonaws.com/openpay-data.v1.min.js";
            createScriptTag(openpay_library);
            createScriptTag(openpay_anti_fraud);
        }

        function createScriptTag(url){
            var script = document.createElement( 'script');
            var head = document.getElementsByTagName( 'head' )[ 0 ];
            script.src = url;
            head.appendChild( script );
            return;
        }
        
        function setApiKey(){
            OpenPay.setId('<?php echo $merchant_id; ?>');
            OpenPay.setApiKey('<?php echo $public_key; ?>');
            OpenPay.setSandboxMode(<?php echo $test_mode; ?>);
            OpenPay.deviceData.setup("payment-form", "device_session_id");
    }


        $('#payment-form').submit(function(event) {
            /* Prevent the form from submitting with the default action */
            event.preventDefault();
            //return false;
            $("#button-confirm").button( 'loading' );
            addMsg( '<?php echo $text_wait; ?>' , 'warning' );
            setApiKey();
            console.log("submit!!!");
            OpenPay.token.extractFormAndCreate('payment-form', success_callbak, error_callbak);
            return false;
    });


        function addMsg( msg , type ){
            var $msgBox = $( '#msgBox' );

            $msgBox[ 0 ].className =  'alert alert-' + type;
            $msgBox.find( 'span' ).text( msg );

            var className = '';
            switch( type ){
                    case 'danger' :
                            className = 'fa-lg fa fa-exclamation-triangle';
                    break;
                    case 'warning' :
                            className = 'fa fa-cog fa-spin urgent-2x';
                    break;
                    case 'success' :
                            className = 'fa-2x fa fa-check';
                    break;
            }
            $msgBox.find( 'i' )[ 0 ].className = className;
    }


        var success_callbak = function(response) {
            var token_id = response.data.id;
            var $form = $('#payment-form'), $msgBox = $( '#msgBox' );
            $msgBox.find( 'img' ).remove();
            console.log(token_id);
            $('#payment-form').append('<input type="hidden" name="token" value="' + escape(token_id) + '" />');

            $.ajax({
                url: '<?php echo $action ?>',
                type: 'post',
                data: $('#payment-form').serialize(),
                dataType: 'json',
                success: function (json, textStatus) {
                    if( json.error ){
                        addMsg( json.error , 'danger' );
                        $form.find('button').button( 'reset' )
                    }else if( json.success ){
                        $form.find('button').attr( 'disabled' , 'disabled' );
                        addMsg( '<?php echo $text_success_payment; ?>' , 'success' );
                        setTimeout( function(){ document.location.assign( json.success ) } , 2000 );
                    }else{
                        addMsg( '<?php echo $error_error; ?>' , 'danger' );
                    }
                }
            });

        };


        var error_callbak = function(response) {
            var $form = $('#payment-form');            
            var msg = "";
            switch (response.data.error_code) {
                case 1000:
                    msg = "Servicio no disponible.";
                    break;

                case 1001:
                    msg = "Los campos no tienen el formato correcto, o la petición no tiene campos que son requeridos.";
                    break;

                case 1004:
                    msg = "Servicio no disponible.";
                    break;

                case 1005:
                    msg = "Servicio no disponible.";
                    break;

                case 2004:
                    msg = "El dígito verificador del número de tarjeta es inválido de acuerdo al algoritmo Luhn.";
                    break;

                case 2005:
                    msg = "La fecha de expiración de la tarjeta es anterior a la fecha actual.";
                    break;

                case 2006:
                    msg = "El código de seguridad de la tarjeta (CVV2) no fue proporcionado.";
                    break;

                default: //Demás errores 400
                    msg = "La petición no pudo ser procesada.";
                    break;
            }

            addMsg( 'ERROR ' + response.data.error_code + '. ' + msg , 'danger' )
            $form.find('button').button( 'reset' );

        };
    });

</script>