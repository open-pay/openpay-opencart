<table class="form">
    <style scoped>
        .tabel caption{
            font-weight: bold;
            text-align: center;
            font-size: 1.2em;
        }
    </style>
    <div class="form-horizontal">
        <div class="form-group">
            <div class="col-sm-2">
                <?php echo $text_charge_id; ?>
            </div>
            <div class="cal-sm-4">
                <?php echo $charge->id; ?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-2">
                <?php echo $text_amount; ?>
            </div>
            <div class="cal-sm-4">
                <?php echo $amount; ?>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-2" for="sp-charge-id">
                <?php echo $text_amount_refunded; ?>
            </div>
            <div class="cal-sm-4">
                <?php echo $amount_refunded; ?>
            </div>
        </div>
        <?php if( ! $charge->captured ) : ?>
        <div class="form-group">
            <div class="col-sm-2">
                <button type="button" class="btn btn-primary" data-loading-text=<?php echo $text_processing; ?> onclick="capture(this)"><?php echo $text_capture; ?></button>
            </div>
            <div class="col-sm-4">
                <input id='capture-amount' class="form-control" type="number" min="0" max="<?php echo $non_formatted_amount; ?>" value="<?php echo $non_formatted_amount; ?>" >
            </div>
        </div>
        <?php endif; ?>
        <?php if( ! $charge->refunded ) : ?>
        <div class="form-group">
            <div class="col-sm-2">
                <button type="button" class="btn btn-primary" data-loading-text=<?php echo $text_processing; ?> onclick="refund(this)"><?php echo $text_refund; ?></button>
            </div>
            <div class="col-sm-4">
                <input id='refund-amount' class="form-control" type="number" min="0" max="<?php echo ( $non_formatted_amount - $non_formatted_amount_refunded ); ?>" value="0" >
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if( ! empty( $txn ) ) : ?>
    <table class="table table-bordered table-striped">
        <caption><?php echo $text_transaction; ?></caption>
        <thead>
            <tr>
                <td><?php echo $text_date; ?></td>
                <td><?php echo $text_type; ?></td>
                <td><?php echo $text_amount; ?></td>
                <td><?php echo $text_description; ?></td>
                <td><?php echo $text_status; ?></td>
                <td><?php echo $text_initiator; ?></td>
            </tr>
        </thead>
        <tbody>
            <?php foreach( $txn as $line ) : ?>
            <tr>
                <td class="left"><?php echo $line[ 'date_added' ]; ?></td>
                <td class="left"><?php echo $line[ 'type' ]; ?></td>
                <td class="left"><?php echo $line[ 'amount' ]; ?></td>
                <td class="left"><?php echo $line[ 'description' ]; ?></td>
                <td class="left"><?php echo $line[ 'status' ]; ?></td>
                <td class="left"><?php echo $line[ 'initiator' ]; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    <script>
        if (typeof console == 'undefined')
        {
                console = {}    ;
                console.log = function(){    }
                    console.dir = function(){}
            }
            function capture(button) {

                var $button = $(button);

                $button.button('loading');
                $.ajax({
                    'url': '<?php echo $url_capture; ?>&amount=' + $('#capture-amount').val()
                })
                        .done(function(resp) {
                            console.dir(resp);
                            try {
                                var json = JSON.parse(resp)
                            }
                            catch (err)
                            {
                                console.dir(err);
                                alert('<?php echo $error_error; ?>');
                                return;
                            }
                            if (json.success)
                            {
                                $button.attr('disabled', 'disabled');
                                alert('<?php echo $text_captured; ?>');
                                document.location.reload();
                            }
                            else if (json.error)
                            {
                                alert(resp.error);
                            }
                            else
                            {
                                alert('<?php echo $error_error; ?>');
                            }
                        })
                        .fail(function(err) {
                            console.dir(err);
                            alert('<?php echo $error_error; ?>');
                        })
                        .always(function() {
                            $button.button('reset');
                        });
            }

            function refund(button) {

                var $button = $(button);

                $button.button('loading');
                $.ajax({
                    'url': '<?php echo $url_refund; ?>&amount=' + $('#refund-amount').val()
                })
                        .done(function(resp) {
                            console.dir(resp);
                            try {
                                var json = JSON.parse(resp)
                            }
                            catch (err)
                            {
                                console.dir(err);
                                alert('<?php echo $error_error; ?>');
                                return;
                            }
                            if (json.success)
                            {
                                alert('<?php echo $text_charge_refunded; ?>');
                                document.location.reload();
                            }
                            else if (json.error)
                            {
                                alert(json.error);
                            }
                            else
                            {
                                alert('<?php echo $error_error; ?>');
                            }
                        })
                        .fail(function(err) {
                            console.dir(err);
                            alert('<?php echo $error_error; ?>');
                        })
                        .always(function() {
                            $button.button('reset');
                        });
            }

            $('#refund-amount').on('change', function() {
                var $input = $(this),
                        min = parseFloat($input.attr('min')),
                        max = parseFloat($input.attr('max'));

                if ($input.val() < min)
                    $input.val(min);
                else if ($input.val() > max)
                    $input.val(max);
            });
    </script>