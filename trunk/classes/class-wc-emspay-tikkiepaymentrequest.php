<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Emspay_TikkiePaymentRequest extends WC_Emspay_Gateway
{
    /**
     * WC_Emspay_TikkiePaymentRequest constructor.
     */
    public function __construct()
    {
        $this->id = 'emspay_tikkie-payment-request';
        $this->icon = false;
        $this->has_fields = false;
        $this->method_title = __('Tikkie Payment Request - EMS Online', WC_Emspay_Helper::DOMAIN);
        $this->method_description = __('Tikkie Payment Request - EMS Online', WC_Emspay_Helper::DOMAIN);

        parent::__construct();
    }

    /**
     * @param int $order_id
     * @return array
     */
    public function process_payment($order_id)
    {
        $order = new WC_Order($order_id);

        $emsOrder = $this->ems->createOrder([
            'currency' => WC_Emspay_Helper::getCurrency(),
            'amount' => WC_Emspay_Helper::gerOrderTotalInCents($order),
            'transactions' => [
                [
                    'payment_method' => str_replace('emspay_', '', $this->id)
                ]
            ],
            'merchant_order_id' => $order_id,
            'description' => WC_Emspay_Helper::getOrderDescription($order_id),
            'return_url' => WC_Emspay_Helper::getReturnUrl(),
            'customer' => WC_Emspay_Helper::getCustomerInfo($order),
            'extra' => ['plugin' => EMSPAY_PLUGIN_VERSION],
            'webhook_url' => WC_Emspay_Helper::getWebhookUrl($this),
        ]);

        update_post_meta($order_id, 'ems_order_id', $emsOrder['id']);

        if ($emsOrder['status'] == 'error') {
            wc_add_notice(__('There was a problem processing your transaction.', WC_Emspay_Helper::DOMAIN), 'error');
            return [
                'result' => 'failure'
            ];
        }

        $pay_url = array_key_exists(0, $emsOrder['transactions'])
            ? $emsOrder['transactions'][0]['payment_url']
            : null;

        return [
            'result' => 'success',
            'redirect' => $pay_url
        ];
    }
}
