<link rel="stylesheet" type="text/css" href="<?php echo $this->registry->get( 'config' )->get('config_ssl'); ?>catalog/view/theme/default/stylesheet/openpay_stores.css">

<div id="msgBox" role="alert"><i></i><span style="margin-left:10px;"></span></div>
<div class="content" id="payment">
    
    <h2>Pago en Tiendas de Conveniencia</h2>
    <div class="mb20">
        <img src="<?php echo $this->registry->get( 'config' )->get('config_ssl'); ?>catalog/view/theme/default/image/openpay_stores.png" alt="Tiendas" class="tiendas">
    </div>
    <div class="well">
        Una vez que des clic en el botón <strong>Confirmar Orden</strong>, tu pedido será puesto en <strong>Espera de pago</strong> y podrás imprimir tu recibo pago el cual podrás liquidar en cualquiera de las tiendas participantes. <br><a href="http://www.openpay.mx/tiendas-de-conveniencia.html" target="_blank">Ver todas las tiendas</a>.
    </div>
    
    <div class="pull-right">
            <button type="button" class="btn btn-primary" id="button-confirm" data-loading-text="Processing"><?php echo $button_confirm; ?></button>
    </div>
    
</div>

<script type="text/javascript"><!--
  $(document).ready(function(){
    $('#button-confirm').click(function(){
      location = '<?php echo $continue ?>';
    });
  });
</script>