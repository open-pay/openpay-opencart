<script type="text/javascript" src="<?php echo $this->registry->get( 'config' )->get('config_ssl'); ?>catalog/view/theme/default/javascript/jquery-2.1.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="<?php echo $this->registry->get( 'config' )->get('config_ssl'); ?>catalog/view/theme/default/stylesheet/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="<?php echo $this->registry->get( 'config' )->get('config_ssl'); ?>catalog/view/theme/default/stylesheet/openpay_stores.css">

<div class="container">
    <div class="row">
        <div id="content" class="col-md-12  mb30">
            <div class="container container-receipt">
                <div class="row" style="padding: 10px 0 0 0;">
                    <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                        <img class="tiendas" src="<?php echo $logo; ?>" alt="">
                    </div>	
                    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">	
                        <p class="Logo_paynet">Servicio a pagar</p>
                        <img class="img-responsive center-block Logo_paynet" src="<?php echo $this->registry->get( 'config' )->get('config_ssl'); ?>catalog/view/theme/default/image/paynet_logo.png" alt="Paynet">
                    </div>	
                </div>

                <div class="row data">

                    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                        <div class="Big_Bullet">
                            <span></span>
                        </div>
                        <h1><strong>Fecha límite de pago</strong></h1> 
                        <strong><?php echo $due_date; ?></strong>
                        <div class="col-lg-12 datos_pago">
                            <!--<h4>30 de Noviembre 2014, a las 2:30 AM</h4>-->
                            <img style="left: -10px;" width="300" src="<?php echo $barcode_url; ?>" alt="Código de Barras">
                            <span style="font-size: 14px"><?php echo $reference; ?></span>
                            <br/>
                            <p>En caso de que el escáner no sea capaz de leer el código de barras, escribir la referencia tal como se muestra.</p>
                        </div>

                    </div>

                    <div class="col-xs-12 col-sm-1 col-md-1 col-lg-1"></div>

                    <div class="col-xs-12 col-sm-5 col-md-5 col-lg-5">
                        <div class="data_amount"> 
                            <h2>Total a pagar</h2>
                            <h2 class="amount">$<?php echo $amount; ?><small> <?php echo $currency; ?></small></h2>
                            <h2 class="S-margin">+8 pesos por comisión</h2>
                        </div>
                    </div>


                </div>

                <div class="row data">

                    <div class="col-xs-12 col-sm-11 col-md-11 col-lg-11">
                        <div class="Big_Bullet">
                            <span></span>
                        </div>
                        <h1><strong>Detalles de la compra</strong></h1> 
                        <div class="col-lg-12 datos_tiendas">
                            <table>
                                <tr>
                                    <td width="40%">Orden:</td>
                                    <td width="60%"><?php echo $order_id; ?></td>
                                </tr>    
                                <tr>
                                    <td>Fecha y hora:</td>
                                    <td><?php echo $creation_date; ?></td>
                                </tr>    
                                <tr>
                                    <td>Correo electrónico:</td>
                                    <td><?php echo $email; ?></td>
                                </tr>    
                            </table>
                        </div>			
                    </div>
                </div>

                <div class="row data">

                    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                        <div class="Big_Bullet">
                            <span></span>
                        </div>
                        <h1><strong>Como realizar el pago</strong></h1> 
                        <ol style="margin-left: 30px;">
                            <li>Acude a cualquier tienda afiliada</li>
                            <li>Entrega al cajero el código de barras y menciona que realizarás un pago de servicio Paynet</li>
                            <li>Realizar el pago en efectivo por $<?php echo $amount; ?> <?php echo $currency; ?> (más $8 pesos por comisión)</li>
                            <li>Conserva el ticket para cualquier aclaración</li>
                        </ol>	
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
                        <h1><strong>Instrucciones para el cajero</strong></h1> 
                        <ol>
                            <li>Ingresar al menú de Pago de Servicios</li>
                            <li>Seleccionar Paynet</li>
                            <li>Escanear el código de barras o ingresar el núm. de referencia</li>
                            <li>Ingresa la cantidad total a pagar</li>
                            <li>Cobrar al cliente el monto total más la comisión de $8 pesos</li>
                            <li>Confirmar la transacción y entregar el ticket al cliente</li>
                        </ol>
                    </div>
                </div>


                <div class="col-lg-12" style="text-align:center;">
                    Para cualquier duda sobre como cobrar, por favor llamar al teléfono 01 800 300 08 08 en un horario de 8am a 9pm de lunes a domingo.
                </div>



                <div class="row marketing">

                    <div class="col-lg-12" style="text-align:center;">
                        <img src="<?php echo $this->registry->get( 'config' )->get('config_ssl'); ?>catalog/view/theme/default/image/openpay_stores.png" alt="Tiendas" class="tiendas">
                    </div>
                    <div class="col-lg-12 mb20" style="text-align:center;">
                        <div class="link_tiendas">¿Quieres pagar en otras tiendas? visita: <a target="_blank" href="http://www.openpay.mx/tiendas-de-conveniencia.html">www.openpay.mx/tiendas</a></div>
                    </div>

                    <div class="col-lg-6 mb30" style="text-align:center; margin-top:5px;">
                        <a type="button" class="btn btn-info btn-lg" onclick="window.print();"><i class="fa fa-print"></i> Imprimir</a>
                    </div>	  
                    <div class="col-lg-6 mb30" style="text-align:center; margin-top:5px;">
                        <a type="button" class="btn btn-success btn-lg" id="button-confirm"><i class="fa fa-check"></i> Regresar a la tienda</a>
                    </div>	  
                    
                    <div class="footer">
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

