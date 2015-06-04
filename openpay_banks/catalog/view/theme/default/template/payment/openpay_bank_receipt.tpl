<script type="text/javascript" src="<?php echo $this->registry->get( 'config' )->get('config_ssl'); ?>catalog/view/theme/default/javascript/jquery-2.1.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $this->registry->get( 'config' )->get('config_ssl'); ?>catalog/view/theme/default/stylesheet/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="<?php echo $this->registry->get( 'config' )->get('config_ssl'); ?>catalog/view/theme/default/stylesheet/openpay_banks.css">
<div class="container">
    <div class="row">
        <div id="content" class="col-md-12">

            <div class="container container-receipt">
                <div class="row header" style="padding: 10px 0 0 0;">
                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                        <img class="tiendas" src="<?php echo $logo; ?>" alt="">
                    </div>	
                    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">	
                        <p class="Yellow2">Esperamos tu pago</p>
                    </div>	
                </div>

                <div class="row">
                    <div class="col-xs-9 col-sm-8 col-md-8 col-lg-8">
                        <h1><strong>Total a pagar</strong></h1>
                        <h2 class="amount">$<?php echo $amount; ?><small> <?php echo $currency; ?></small></h2>
                        <h1><strong>Fecha límite de pago:</strong></h1>
                        <h1><?php echo $due_date; ?></h1>
                    </div>
                    <div class="col-xs-3 col-sm-4 col-md-4 col-lg-4">
                        <a href="http://www.openpay.mx/bancos.html" target="_blank"><img class="img-responsive spei" src="<?php echo $this->registry->get( 'config' )->get('config_ssl'); ?>catalog/view/theme/default/image/spei.gif"  alt="SPEI"></a>
                    </div>
                </div>

                <div class="row marketing">
                    <h1 style="padding-bottom:10px;"><strong>Datos para transferencia electrónica</strong></h1>
                    <div class="col-lg-12 datos_pago">
                        <table>
                            <tr>
                                <td style="width: 50%;">Banco:</td>
                                <td>STP</td>
                            </tr>    
                            <tr>
                                <td>CLABE:</td>
                                <td><?php echo $clabe; ?></td>
                            </tr>    
                            <tr>
                                <td>Referencia:</td>
                                <td><?php echo $name; ?></td>
                            </tr>    
                            <tr>
                                <td>Beneficiario:</td>
                                <td><?php echo $store_name; ?></td>
                            </tr>    
                        </table>
                    </div>	        



                    <div class="col-lg-12" style="text-align: center; margin-top: 20px;">
                        <p>¿Tienes alguna dudas o problema? Escríbenos a</p>
                        <h4><?php echo $store_email; ?></h4>
                    </div>
                        
                    <div class="col-lg-6 mb30" style="text-align:center; margin-top:5px;">
                        <a type="button" class="btn btn-info btn-lg" onclick="window.print();"><i class="fa fa-print"></i> Imprimir</a>
                    </div>	  
                    <div class="col-lg-6 mb30" style="text-align:center; margin-top:5px;">
                        <a type="button" class="btn btn-success btn-lg" id="button-confirm"><i class="fa fa-check"></i> Regresar a la tienda</a>
                    </div>
                    
                    <div class="footer mb30">
                        <img src="<?php echo $this->registry->get( 'config' )->get('config_ssl'); ?>catalog/view/theme/default/image/powered_openpay.png" alt="Powered by Openpay">
                    </div>  
                        
                </div>

            </div>        
            
        </div>
        
    </div>
</div>

<script type="text/javascript"><!--
  $(document).ready(function() {
      
        window.print();
      
        $('#button-confirm').click(function() {
            location = '<?php echo $continue ?>';
        });
    });
</script>