<script type="text/javascript" src="/catalog/view/theme/default/javascript/jquery-2.1.1.min.js"></script>
<link rel="stylesheet" type="text/css" href="/catalog/view/theme/default/stylesheet/bootstrap.min.css">

<div class="container">    
    <div class="row">              
        <div class="col-lg-8 col-lg-offset-2">
            <iframe id="pdf" src="<?php echo $pdf; ?>" style="width:100%; height:960px; visibility: visible !important; opacity: 1 !important;" frameborder="1"></iframe>    
        </div>
        
        <div class="col-lg-8 text-center col-lg-offset-2" style="margin-top: 30px; margin-bottom: 30px;">
            <a type="button" class="btn btn-success btn-lg" id="button-confirm"><i class="fa fa-check"></i> Regresar a la tienda</a>
        </div>        
    </div>        
</div>

<script type="text/javascript">
  $(document).ready(function() {            
        $('#button-confirm').click(function() {
            location = '<?php echo $continue ?>';
        });
    });
</script>

