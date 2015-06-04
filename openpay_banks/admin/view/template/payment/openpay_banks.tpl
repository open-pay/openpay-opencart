<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-manufacturer" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>
            </div>
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>
    <div class="container-fluid">
        <?php if( ! empty( $error_warning ) ) : ?>
        <div class="alert alert-danger">
            <i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
        <?php endif; ?>
        <?php if( ! empty( $error_attention ) ) : ?>
        <div class="alert alert-warning alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Warning!</strong> <?php echo $error_attention; ?>
        </div>
        <?php endif; ?>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_form; ?></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-product" class="form-horizontal">
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#tab-api" data-toggle="tab"><?php echo $tab_api; ?></a></li>
                        <li><a href="#tab-general" data-toggle="tab"><?php echo $tab_general; ?></a></li>
                        <li><a href="#tab-status" data-toggle="tab"><?php echo $tab_status; ?></a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active " id="tab-api">
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="radio-test-mode"><?php echo $text_test_mode; ?></label>
                                <div class="col-sm-10">
                                    <label class="radio-inline">
                                        <input id="radio-test-mode" type="radio" name="openpay_bank_test_mode" value="0" <?php if( ! $openpay_bank_test_mode )echo' checked'; ?> ><?php echo $text_no; ?>
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="openpay_bank_test_mode" value="1" <?php if( $openpay_bank_test_mode )echo' checked'; ?> ><?php echo $text_yes; ?>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-test-merchant-id">
                                    <?php echo $entry_test_merchant_id; ?>
                                </label>
                                <div class="col-sm-10">
                                    <input type="text" name="openpay_bank_test_merchant_id" value="<?php echo $openpay_bank_test_merchant_id; ?>" class="form-control" id="input-test-merchant" placeholder="<?php echo $entry_test_merchant_id; ?>">
                                    <?php if( ! empty( $error_test_merchant_id ) ) : ?>
                                        <div class="text-danger"><?php echo $error_test_merchant_id; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-test-secret">
                                    <?php echo $entry_test_secret_key; ?>
                                </label>
                                <div class="col-sm-10">
                                    <input type="text" name="openpay_bank_test_secret_key" value="<?php echo $openpay_bank_test_secret_key; ?>" class="form-control" id="input-test-secret" placeholder="<?php echo $entry_test_secret_key; ?>">
                                    <?php if( ! empty( $error_test_secret_key ) ) : ?>
                                    <div class="text-danger"><?php echo $error_test_secret_key; ?></div>
                                    <?php endif; ?>
                                    <?php if( ! empty( $error_test_merchant_account ) ) : ?>
                                        <div class="text-danger"><?php echo $error_test_merchant_account; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-test-public">
                                    <?php echo $entry_test_public_key; ?>
                                </label>
                                <div class="col-sm-10">
                                    <input type="text" id="input-test-public" class="form-control" name="openpay_bank_test_public_key" placeholder="<?php echo $entry_test_public_key; ?>" value="<?php echo $openpay_bank_test_public_key; ?>" >
                                    <?php if( ! empty( $error_test_public_key ) ) : ?>
                                    <div class="text-danger"><?php echo $error_test_public_key; ?></div>
                                    <?php endif; ?>
                                    <?php if( ! empty( $error_test_merchant_account ) ) : ?>
                                        <div class="text-danger"><?php echo $error_test_merchant_account; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-live-merchant-id">
                                    <?php echo $entry_live_merchant_id; ?>
                                </label>
                                <div class="col-sm-10">
                                    <input type="text" name="openpay_bank_live_merchant_id" value="<?php echo $openpay_bank_live_merchant_id; ?>" class="form-control" id="input-live-merchant" placeholder="<?php echo $entry_live_merchant_id; ?>">
                                    <?php if( ! empty( $error_live_merchant_id ) ) : ?>
                                        <div class="text-danger"><?php echo $error_live_merchant_id; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-live-secret">
                                    <?php echo $entry_live_secret_key; ?>
                                </label>
                                <div class="col-sm-10">
                                    <input type="text" name="openpay_bank_live_secret_key" value="<?php echo $openpay_bank_live_secret_key; ?>" class="form-control" id="input-live-secret" placeholder="<?php echo $entry_live_secret_key; ?>">
                                    <?php if( ! empty( $error_live_secret_key ) ) : ?>
                                    <div class="text-danger"><?php echo $error_live_secret_key; ?></div>
                                    <?php endif; ?>
                                    <?php if( ! empty( $error_live_merchant_account ) ) : ?>
                                        <div class="text-danger"><?php echo $error_live_merchant_account; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-live-public">
                                    <?php echo $entry_live_public_key; ?>
                                </label>
                                <div class="col-sm-10">
                                    <input type="text" id="input-live-public" class="form-control" name="openpay_bank_live_public_key" placeholder="<?php echo $entry_live_public_key; ?>" value="<?php echo $openpay_bank_live_public_key; ?>" >
                                    <?php if( ! empty( $error_live_public_key ) ) : ?>
                                    <div class="text-danger"><?php echo $error_live_public_key; ?></div>
                                    <?php endif; ?>
                                    <?php if( ! empty( $error_live_merchant_account ) ) : ?>
                                        <div class="text-danger"><?php echo $error_live_merchant_account; ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab-general">
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-title" >
                                    <?php echo $entry_title; ?>
                                    <span class="glyphicon" data-toggle="popover" data-content="<?php echo $help_title; ?>" style="cursor:pointer;" >
                                    </span>
                                </label>
                                <div class="col-sm-10">
                                    <input type="text" name="openpay_bank_title" value="<?php echo $openpay_bank_title; ?>" class="form-control" id="input-title" placeholder="<?php echo $entry_title; ?>" >
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="select-geo-zone">
                                    <?php echo $entry_geo_zone; ?>
                                </label>
                                <div class="col-sm-10">
                                    <select name="openpay_banks_geo_zone_id" id="select-geo-zone" class="form-control">
                                        <option value="0"><?php echo $text_all_zones; ?></option>
                                        <?php foreach ($geo_zones as $geo_zone) : ?>
                                        <option value="<?php echo $geo_zone['geo_zone_id']; ?>" <?php if( $geo_zone['geo_zone_id'] == $openpay_banks_geo_zone_id ) echo ' selected'; ?> ><?php echo $geo_zone['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="select-module-status">
                                    <?php echo $entry_status; ?>
                                </label>
                                <div class="col-sm-10">
                                    <select name="openpay_banks_status" id="select-module-status" class="form-control">
                                        <option value="0" ><?php echo $text_disabled; ?></option>
                                        <option value="1" <?php if( $openpay_banks_status )echo' selected'; ?> ><?php echo $text_enabled; ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group required">
                                <label class="col-sm-2 control-label" for="input-total">
                                    <?php echo $entry_total; ?>
                                    <span class="glyphicon" data-toggle="popover" data-content="<?php echo $help_total; ?>" style="cursor:pointer;" ></span>
                                </label>
                                <div class="col-sm-10">
                                    <div class="input-group">
                                        <?php if( $currency_symbol_left ) : ?>
                                        <span class="input-group-addon"><?php echo $currency_symbol_left; ?></span>
                                        <?php endif; ?>
                                        <input type="text" id="input-total" class="form-control" name="openpay_bank_total" value="<?php echo $openpay_bank_total; ?>" >
                                        <?php if( $currency_symbol_right ) : ?>
                                        <span class="input-group-addon"><?php echo $currency_symbol_right; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ( ! empty( $error_total ) ) : ?>
                                    <div class="text-danger"><?php echo $error_total; ?></div>
                                    <?php endif; ?> 
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-sort-order">
                                    <?php echo $entry_sort_order; ?>
                                </label>
                                <div class="col-sm-10">
                                    <input type="text" id="input-sort-order" class="form-control" name="openpay_banks_sort_order" value="<?php echo $openpay_banks_sort_order; ?>" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="input-deadline">
                                    <?php echo $entry_deadline; ?>
                                </label>
                                <div class="col-sm-10">
                                    <input type="text" id="input-deadline" class="form-control" name="openpay_bank_deadline" value="<?php echo $openpay_bank_deadline; ?>" >
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="tab-status">
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="select-new">
                                    <?php echo $entry_new_status; ?>
                                </label>
                                <div class="col-sm-10">
                                    <select name="openpay_bank_new_status_id" id="select-new" class="form-control">
                                        <?php foreach( $order_statuses as $order_status ) : ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>" <?php if( $order_status['order_status_id'] == $openpay_bank_new_status_id ) echo ' selected'; ?> ><?php echo $order_status['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label" for="select-captured">
                                    <?php echo $entry_captured_status; ?>
                                </label>
                                <div class="col-sm-10">
                                    <select name="openpay_bank_captured_status_id" id="select-captured" class="form-control">
                                        <?php foreach( $order_statuses as $order_status ) : ?>
                                        <option value="<?php echo $order_status['order_status_id']; ?>" <?php if( $order_status['order_status_id'] == $openpay_bank_captured_status_id ) echo ' selected'; ?> ><?php echo $order_status['name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div><!-- .tab-content -->
                </form><!-- #form -->
            </div><!-- .panel-body -->
        </div><!--.panel .panel-default -->
    </div><!-- .container-fluid -->
</div><!-- #content -->
<script>
    'use strict';
    $('[data-toggle=\'popover\']').popover({
        title: '',
        html: true,
        template: '<div class="popover" role="tooltip"><div class="arrow"></div><h3 class="popover-title"></h3><div class="popover-content" style="min-width:150px;"></div></div>'
    });
</script>