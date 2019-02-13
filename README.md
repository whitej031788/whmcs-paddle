# Paddle and WHMCS Sample Module
This repo is a sample module for connecting the WHMCS cart solution with the Paddle checkout, and subsequently catching the Paddle webhooks to mark WHMCS invoices as "paid" on successful payment.
 
You will need to add the file in the root directory to the `modules/gateways/` folder of your WHMCS installation. It should then show up in your list of Payment Gateways, and you then will need to add your Paddle Vendor ID, Auth Code and Product / Plan ID in the WHMCS configuration.

You will need to add the file in the "callback" folder to your `modules/gateways/callback` folder in your WHMCS installation. The only thing you must edit in this file should be replacing your Paddle Public RSA Key from Vendor Settings in the paddle.php WHMCS file.

It will send the seller to the Paddle checkout for the amount they have ordered.
 
WHMCS only provide invoice amounts, so in Paddle you just need to setup a single product or
subscription plan and provide that in the config.

For more information, please refer to the online documentation for WHMCS and Paddle:
 
https://developers.whmcs.com/payment-gateways

https://paddle.com/docs/api-custom-checkout/

# Usage and support
This code is provided as an example plugin to implement the Paddle checkout on a WHMCS instance. End users may use or modify the code as required as they see fit, as each WHMCS and Paddle implementation can be quite different.

This plugin is not provided by nor supported by Paddle does not provide technical support or maintenance for this plugin or any derivations of it.