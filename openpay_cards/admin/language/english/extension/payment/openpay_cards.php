<?php
// Heading
$_['heading_title']             = 'Openpay Cards';

//Tabs
$_[ 'tab_api' ] = 'API Settings';
$_[ 'tab_general' ] = 'General Settings';
$_[ 'tab_status' ] = 'Status';

// Text
$_[ 'text_payment' ]              = 'Payment';
$_[ 'text_success' ]              = 'Success: You have modified Openpay Cards account details!';
$_[ 'text_openpay_cards' ] = '<a href="http://www.openpay.mx/" target="_blank"><img src="view/image/payment/openpay-logo.png" alt="Openpay" title="Openpay" style="border: 1px solid #EEEEEE;" /></a>';
$_[ 'text_test' ]                 = 'Test';
$_[ 'text_live' ]                 = 'Live';
$_[ 'text_authorization' ]        = 'Authorization';
$_[ 'text_charge' ]               = 'Charge';
$_[ 'text_title' ]           = 'Openpay Tarjetas';
$_[ 'text_credit_card' ]     = 'Pago con tarjeta de crédito/débito';
$_[ 'text_wait' ]            = 'Please wait!';
$_[ 'text_test_mode' ] = 'Test Mode';
$_[ 'text_debug_mode' ] = 'Debug Mode';
$_[ 'text_yes' ] = 'Yes';
$_[ 'text_no '] = 'No';
$_[ 'text_default_payment_mode' ] = 'Default Payment Mode';
$_[ 'text_one_step_mode' ] = 'One Step';
$_[ 'text_two_step_mode' ] = 'Two Step';
$_[ 'text_wait_page_load' ] = 'Please wait till page will be fully loaded';
$_[ 'text_success_payment' ] = 'Payment was  successful';
$_[ 'text_openpay_header' ] = 'Openpay Payment Details';
$_[ 'text_charge_id' ] = 'Charge ID';
$_[ 'text_amount' ] = 'Amount';
$_[ 'text_capture' ] = 'Capture';
$_[ 'text_capturing' ] = 'Capturing';
$_[ 'text_processing' ] = 'Processing';
$_[ 'text_refund' ] = 'Refunds';
$_[ 'text_captured' ] = 'Payment was captured';
$_[ 'text_capture_success' ] = 'Payment was captured';
$_[ 'text_refund_success' ] = 'Charge was refunded';
$_[ 'text_date' ] = 'Date';
$_[ 'text_type' ] = 'Type';
$_[ 'text_amount' ] = 'Amount';
$_[ 'text_description' ] = 'Description';
$_[ 'text_initiator' ] = 'Initiator';
$_[ 'text_transaction' ] = 'Transactions';
$_[ 'text_status' ] = 'Status';
$_[ 'text_refund' ] = 'Refund';
$_[ 'text_amount_refunded' ] = 'Refunded';
$_[ 'text_refunded' ] = 'Charge was refunded';
$_[ 'text_charge_refunded' ] = 'Charge was refunded';
$_[ 'text_form' ] = 'Settings';

// Entry
$_[ 'entry_test_merchant_id' ]              = 'Test Merchant ID';
$_[ 'entry_live_merchant_id' ]              = 'Live Merchant ID';
$_[ 'entry_test_secret_key' ]              = 'Test Secret Key';
$_[ 'entry_test_public_key' ]                 = 'Test Public Key';
$_[ 'entry_live_secret_key' ]              = 'Live Secret Key';
$_[ 'entry_live_public_key' ]                 = 'Live Public Key';
$_[ 'entry_mode' ]                = 'Transaction Mode';
$_[ 'entry_method' ]              = 'Transaction Method';
$_[ 'entry_total' ]               = 'Total';
$_[ 'entry_order_status' ]        = 'Order Status';
$_[ 'entry_cc_owner' ]       = 'Nombre del tarjetahabiente';
$_[ 'entry_cc_number' ]      = 'Número de tarjeta';
$_[ 'entry_cc_expire_date' ] = 'Fecha de expiración';
$_[ 'entry_cc_cvv2' ]        = 'Código CVV';
$_[ 'entry_geo_zone' ]            = 'Geo Zone';
$_[ 'entry_status' ]              = 'Status';
$_[ 'entry_sort_order' ]          = 'Sort Order';
$_[ 'entry_ipn' ] = 'Webhook page';
$_[ 'entry_completed_status' ] = 'Completed';
$_[ 'entry_new_status' ] = 'Authorized';
$_[ 'entry_title' ] = 'Title';
$_[ 'entry_country' ] = 'Country';
$_[ 'entry_iva' ] = 'IVA';
$_[ 'entry_affiliation_bbva' ] = 'BBVA affiliation number';

//help
$_[ 'help_title' ] = 'Caption, which will appear on Checkout';
$_[ 'help_installments' ] = 'Press ctrl and click to select more than one option.';
$_[ 'help_iva' ] = 'It must contain the IVA value, is only informative field, has no effect on the amount field.';
$_[ 'help_total' ] = 'Minimum total amount of an order. (No less then %s)';
$_[ 'help_charge' ] = 'With One Step Payment on checkout occurs Authorization and Charge, with Two Step Payment - Authorization only';
$_[ 'help_cvc_front' ] = 'American Express presenta este código código de tres dígitos en la parte frontal de la tarjeta.';
$_[ 'help_cvc_back' ] = 'MasterCard y VISA presentan este código código de tres dígitos en el dorso de la tarjeta';

// Error
$_[ 'error_permission' ]          = 'Warning: You do not have permission to modify payment: Openpay Cards!';
$_[ 'error_live_merchant_id' ]                 = 'Live Merchant ID Required!';
$_[ 'error_test_merchant_id' ]                 = 'Test Merchant ID Required!';
$_[ 'error_test_public_key' ]              = 'Test Public Key Required!';
$_[ 'error_test_secret_key' ]                 = 'Test Secret Key Required!';
$_[ 'error_live_public_key' ]              = 'Live Public Key Required!';
$_[ 'error_live_secret_key' ]                 = 'Live Secret Key Required!';
$_[ 'error_validate_currency_co' ]                 = 'Openpay Plugin is only available for COP currency.';
$_[ 'error_validate_currency_mx' ]                 = 'Openpay Plugin is only available for MXN and USD currencies.';
$_[ 'error_correct_data' ] = 'To proceed You must correct some data';
$_[ 'error_total' ] = 'Total can not be less then %s';
$_[ 'error_error' ] = 'Sorry, seems we got Error here.';
$_[ 'error_invalid_amount' ] = 'Amount must be positive numeric value';
$_[ 'error_min_total' ] = 'Total amount less then permissible';
$_[ 'error_missing_currency' ] = 'Currency missing';
$_[ 'error_test_merchant_account' ] = 'Test account credentials are invalid!';
$_[ 'error_live_merchant_account' ] = 'Live account credentials are invalid!';
$_[ 'error_affiliation_bbva' ] = 'The BBVA affiliation number is invalid!';
?>