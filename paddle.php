<?php
/**
 * WHMCS Sample Paddle Payment Gateway Module
 * You will need to add this file to the modules/gateways/ folder of your WHMCS installation
 * It will send the seller to the Paddle checkout for the amount they have ordered
 * 
 * WHMCS only provide invoice amounts, so in Paddle you just need to setup a single product or
 * subscription plan and provide that in the config.
 *
 * Payment Gateway modules allow you to integrate payment solutions with the
 * WHMCS platform.
 *
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/payment-gateways/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define module related meta data.
 *
 * Values returned here are used to determine module related capabilities and
 * settings.
 *
 * @see https://developers.whmcs.com/payment-gateways/meta-data-params/
 *
 * @return array
 */
function paddle_MetaData()
{
    return array(
        'DisplayName' => 'Paddle',
        'APIVersion' => '1.1', // Use API Version 1.1
        'DisableLocalCredtCardInput' => true,
        'TokenisedStorage' => false
    );
}

/**
 * Define gateway configuration options.
 *
 * The fields you define here determine the configuration options that are
 * presented to administrator users when activating and configuring your
 * payment gateway module for use.
 *
 * Supported field types include:
 * * text
 * * password
 * * yesno
 * * dropdown
 * * radio
 * * textarea
 *
 * Examples of each field type and their possible configuration parameters are
 * provided in the sample function below.
 *
 * @return array
 */
function paddle_config()
{
    return array(
        // the friendly display name for a payment gateway should be
        // defined here for backwards compatibility
        'FriendlyName' => array(
            'Type' => 'System',
            'Value' => 'Paddle',
        ),
        // a text field type allows for single line text input
        'accountID' => array(
            'FriendlyName' => 'Paddle Vendor ID',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your Vendor ID here (Vendor Settings -> Integrations)',
        ),
        // a password field type allows for masked text input
        'secretKey' => array(
            'FriendlyName' => 'Paddle Auth Code',
            'Type' => 'text',
            'Size' => '50',
            'Default' => '',
            'Description' => 'Enter your Paddle Auth Code here (Vendor Settings -> Integrations)',
        ),
        // a text field type allows for single line text input
        'prodId' => array(
            'FriendlyName' => 'Paddle Plan ID',
            'Type' => 'text',
            'Size' => '25',
            'Default' => '',
            'Description' => 'Enter your Plan or Product ID here',
        ),
        'logoUrl' => array(
            'FriendlyName' => 'Your Logo URL',
            'Type' => 'text',
            'Size' => '255',
            'Default' => '',
            'Description' => 'Enter a link to your logo for the Paddle checkout',
        )
    );
}

// Function to generate Paddle URL
function paddleCheckoutAPI($params)
{
    // This takes your WHMCS Paddle Gateway settings and sends them to the Paddle API
    $data = array();
    $data['vendor_id'] = $params['accountID'];;
    $data['vendor_auth_code'] = $params['secretKey'];
    $data['product_id'] = $params['prodId'];
    $data['customer_email'] = $params['clientdetails']['email'];
    $data['product_id'] = $params['prodId'];
    $data['title'] = $params["description"];
    $data['image_url'] = $params["logoUrl"];
    $data['marketing_consent'] = $params["marketing_emails_opt_in"];
    $data['customer_country'] = $params['clientdetails']['country'];
    $data['customer_postcode'] = $params['clientdetails']['postcode'];
    $data['passthrough'] = $params['invoiceid'];

    $data['expires'] = date('Y-m-d', strtotime("+1 days"));

	$data['prices'] = [
		$params['currency'] . ":" . $params['amount']
    ];
    
    // Comment out below if you are only selling one time products, leave as is for subscriptions
    $data['recurring_prices'] = [
		$params['currency'] . ":" . $params['amount']
	];
	
	// Here we make the request to the Paddle API
	$url = 'https://vendors.paddle.com/api/2.0/product/generate_pay_link';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	$response = curl_exec($ch);
	
	// And handle the response...
    $data = json_decode($response);

	if ($data->success) {
		return $data->response->url;
	} else {
        throw new Exception("Your request failed with error: ".$data->error->message);
	}
} 

/**
 * Payment link.
 *
 * Required by third party payment gateway modules only.
 *
 * Defines the HTML output displayed on an invoice. Typically consists of an
 * HTML form that will take the user to the payment gateway endpoint.
 *
 * @param array $params Payment Gateway Module Parameters
 *
 * @see https://developers.whmcs.com/payment-gateways/third-party-gateway/
 *
 * @return string
 */
function paddle_link($params)
{
    // Gateway Configuration Parameters
    $accountId = $params['accountID'];
    $secretKey = $params['secretKey'];
    $testMode = $params['testMode'];
    $dropdownField = $params['dropdownField'];
    $radioField = $params['radioField'];
    $textareaField = $params['textareaField'];

    // Invoice Parameters
    $invoiceId = $params['invoiceid'];
    $description = $params["description"];
    $amount = $params['amount'];
    $currencyCode = $params['currency'];

    // Client Parameters
    $firstname = $params['clientdetails']['firstname'];
    $lastname = $params['clientdetails']['lastname'];
    $email = $params['clientdetails']['email'];
    $address1 = $params['clientdetails']['address1'];
    $address2 = $params['clientdetails']['address2'];
    $city = $params['clientdetails']['city'];
    $state = $params['clientdetails']['state'];
    $postcode = $params['clientdetails']['postcode'];
    $country = $params['clientdetails']['country'];
    $phone = $params['clientdetails']['phonenumber'];

    // System Parameters
    $companyName = $params['companyname'];
    $systemUrl = $params['systemurl'];
    $returnUrl = $params['returnurl'];
    $langPayNow = $params['langpaynow'];
    $moduleDisplayName = $params['name'];
    $moduleName = $params['paymentmethod'];
    $whmcsVersion = $params['whmcsVersion'];

    $postfields = array();
    $postfields['username'] = $username;
    $postfields['invoice_id'] = $invoiceId;
    $postfields['description'] = $description;
    $postfields['amount'] = $amount;
    $postfields['currency'] = $currencyCode;
    $postfields['first_name'] = $firstname;
    $postfields['last_name'] = $lastname;
    $postfields['email'] = $email;
    $postfields['address1'] = $address1;
    $postfields['address2'] = $address2;
    $postfields['city'] = $city;
    $postfields['state'] = $state;
    $postfields['postcode'] = $postcode;
    $postfields['country'] = $country;
    $postfields['phone'] = $phone;
    $postfields['callback_url'] = $systemUrl . '/modules/gateways/callback/' . $moduleName . '.php';
    $postfields['return_url'] = $returnUrl;

    $htmlOutput = '<form method="get" action="' . paddleCheckoutAPI($params) . '">';
    $htmlOutput .= '<input type="submit" value="' . $langPayNow . '" />';
    $htmlOutput .= '</form>';

    return $htmlOutput;
}