<?php
/**
 * WHMCS Sample Payment Callback File
 *
 * This sample file demonstrates how a payment gateway callback should be
 * handled within WHMCS.
 *
 * It demonstrates verifying that the payment gateway module is active,
 * validating an Invoice ID, checking for the existence of a Transaction ID,
 * Logging the Transaction for debugging and Adding Payment to an Invoice.
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/payment-gateways/callbacks/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

// Require libraries needed for gateway module functions.
require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/../../../includes/gatewayfunctions.php';
require_once __DIR__ . '/../../../includes/invoicefunctions.php';

// Detect module name from filename.
$gatewayModuleName = basename(__FILE__, '.php');

// Fetch gateway configuration parameters.
$gatewayParams = getGatewayVariables($gatewayModuleName);

// Die if module is not active.
if (!$gatewayParams['type']) {
    die("Module Not Activated");
}

// Here we begin verifying the webhook from Paddle, making sure it is the correct webhook type, and then applying
// the payment to the WHMCS invoice

// Your Paddle 'Public Key'
// You get this from your Vendor Dashboard, Vendor Settings -> Public Key
$public_key = '-----BEGIN PUBLIC KEY-----
MIICIjANBg.....
-----END PUBLIC KEY-----';
  
// Get the p_signature parameter & base64 decode it.
$signature = base64_decode($_POST['p_signature']);

// Get the fields sent in the request, and remove the p_signature parameter
$fields = $_POST;
unset($fields['p_signature']);

// ksort() and serialize the fields
ksort($fields);
foreach($fields as $k => $v) {
    if(!in_array(gettype($v), array('object', 'array'))) {
        $fields[$k] = "$v";
    }
}
$data = serialize($fields);

// Verify the signature
$verification = openssl_verify($data, $signature, $public_key, OPENSSL_ALGO_SHA1);

if ($verification != 1) {
    // We could not verify the Paddle webhook
    header('HTTP/1.0 200 Status');
    die('We could not verify the Paddle webhook');
}

// We only take action on a payment webhook, others are not relevant to WHMCS
if ($fields['alert_name'] == 'subscription_payment_succeeded' || $fields['alert_name'] == 'payment_succeeded') {

    $transactionId = $fields['order_id'];
    // If your Paddle Vendor Account VAT settings are to 'Add To Price", use the fee + earnings. If Paddle is set to Include In Price, use the balance_gross
    // $paymentAmount = $fields['balance_gross'];
    $paymentAmount = $fields['balance_fee'] + $fields['balance_earnings'];
    $paymentFee = 0;

    // Invoice ID is in the passthrough field
    $invoiceId = $fields['passthrough'];

    /**
     * Check Callback Transaction ID.
     *
     * Performs a check for any existing transactions with the same given
     * transaction number.
     *
     * Performs a die upon encountering a duplicate.
     *
     * @param string $transactionId Unique Transaction ID
     */
    checkCbTransID($transactionId);

    logTransaction($gatewayParams['name'], $fields, "Success");

    /**
     * Validate Callback Invoice ID.
     *
     * Checks invoice ID is a valid invoice number. Note it will count an
     * invoice in any status as valid.
     *
     * Performs a die upon encountering an invalid Invoice ID.
     *
     * Returns a normalised invoice ID.
     *
     * @param int $invoiceId Invoice ID
     * @param string $gatewayName Gateway Name
     */
    $invoiceId = checkCbInvoiceID($invoiceId, $gatewayParams['name']);

    /**
     * Add Invoice Payment.
     *
     * Applies a payment transaction entry to the given invoice ID.
     *
     * @param int $invoiceId         Invoice ID
     * @param string $transactionId  Transaction ID
     * @param float $paymentAmount   Amount paid (defaults to full balance)
     * @param float $paymentFee      Payment fee (optional)
     * @param string $gatewayModule  Gateway module name
     */
    addInvoicePayment(
        $invoiceId,
        $transactionId,
        $paymentAmount,
        $paymentFee,
        $gatewayModuleName
    );

    echo json_encode(array());
}
echo json_encode(array());
