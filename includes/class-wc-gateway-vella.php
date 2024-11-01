<?php

if (!defined('ABSPATH')) {
	exit;
}

class WC_Gateway_Vella extends WC_Payment_Gateway_CC
{

	/**
	 * Is test mode active?
	 *
	 * @var bool
	 */
	public $testmode;

	/**
	 * merchant id?
	 *
	 * @var bool
	 */
	public $merchant_id;

	/**
	 * Should orders be marked as complete after payment?
	 * 
	 * @var bool
	 */
	public $autocomplete_order;

	/**
	 * Vella payment page type.
	 *
	 * @var string
	 */
	public $payment_page;
	/**
	 * Vella test key.
	 *
	 * @var string
	 */
	public $test_key;
	/**
	 * Vella live key.
	 *
	 * @var string
	 */
	public $live_key;
	/**
	 * Vella test public key.
	 *
	 * @var string
	 */
	public $test_public_key;

	/**
	 * vella test secret key.
	 *
	 * @var string
	 */
	public $test_secret_key;

	/**
	 * Should Vella split payment be enabled.
	 *
	 * @var bool
	 */
	public $split_payment;

	/**
	 * Should the cancel & remove order button be removed on the pay for order page.
	 *
	 * @var bool
	 */
	public $remove_cancel_order_button;

	/**
	 * Vella sub account code.
	 *
	 * @var string
	 */
	public $subaccount_code;

	/**
	 * Who bears Vella charges?
	 *
	 * @var string
	 */
	public $charges_account;

	/**
	 * A flat fee to charge the sub account for each transaction.
	 *
	 * @var string
	 */
	public $transaction_charges;

	/**
	 * Should custom metadata be enabled?
	 *
	 * @var bool
	 */
	public $custom_metadata;

	/**
	 * Should the order id be sent as a custom metadata to Vella?
	 *
	 * @var bool
	 */
	public $meta_order_id;

	/**
	 * Should the customer name be sent as a custom metadata to Vella?
	 *
	 * @var bool
	 */
	public $meta_name;

	/**
	 * Should the billing email be sent as a custom metadata to Vella?
	 *
	 * @var bool
	 */
	public $meta_email;

	/**
	 * Should the billing phone be sent as a custom metadata to Vella?
	 *
	 * @var bool
	 */
	public $meta_phone;

	/**
	 * Should the billing address be sent as a custom metadata to Vella?
	 *
	 * @var bool
	 */
	public $meta_billing_address;

	/**
	 * Should the shipping address be sent as a custom metadata to Vella?
	 *
	 * @var bool
	 */
	public $meta_shipping_address;

	/**
	 * Should the order items be sent as a custom metadata to Vella?
	 *
	 * @var bool
	 */
	public $meta_products;

	/**
	 * API public key
	 *
	 * @var string
	 */
	public $public_key;

	/**
	 * API secret key
	 *
	 * @var string
	 */
	public $secret_key;

	/**
	 * Gateway disabled message
	 *
	 * @var string
	 */
	public $msg;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->id                 = 'vella';
		$this->method_title       = __('VellaPay', 'woo-vella-pay');
		$this->method_description = sprintf(__('Vella provide merchants with the tools and services needed to accept online payments from local and international customers using Vella Pay, Crypto and Card. <a href="%1$s" target="_blank">Sign up</a> for a Vella account, and <a href="%2$s" target="_blank">get your API keys</a> under settings page.', 'woo-vella-pay'), 'https://app.vella.finance', 'https://app.vella.finance');
		$this->has_fields         = true;

		$this->payment_page = $this->get_option('payment_page');

		$this->supports = array(
			'products',
			'refunds',
			'tokenization',
			'subscriptions',
			'multiple_subscriptions',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'subscription_payment_method_change',
			'subscription_payment_method_change_customer',
		);

		// Load the form fields
		$this->init_form_fields();

		// Load the settings
		$this->init_settings();

		// Get setting values

		$this->title              = $this->get_option('title');
		$this->description        = $this->get_option('description');
		$this->enabled            = $this->get_option('enabled');
		$this->testmode           = $this->get_option('testmode') === 'yes' ? true : false;
		$this->autocomplete_order = $this->get_option('autocomplete_order') === 'yes' ? true : false;
		$this->merchant_id =  $this->get_option('merchant_id');
		$this->test_key = $this->get_option('test_key');
		$this->live_key = $this->get_option('live_key');

		$this->saved_cards = $this->get_option('saved_cards') === 'yes' ? true : false;

		//$this->split_payment              = $this->get_option('split_payment') === 'yes' ? true : false;
		$this->remove_cancel_order_button = $this->get_option('remove_cancel_order_button') === 'yes' ? true : false;
		$this->subaccount_code            = $this->get_option('subaccount_code');
		$this->charges_account            = $this->get_option('split_payment_charge_account');
		$this->transaction_charges        = $this->get_option('split_payment_transaction_charge');

		$this->custom_metadata = $this->get_option('custom_metadata') === 'yes' ? true : false;

		$this->meta_order_id         = $this->get_option('meta_order_id') === 'yes' ? true : false;
		$this->meta_name             = $this->get_option('meta_name') === 'yes' ? true : false;
		$this->meta_email            = $this->get_option('meta_email') === 'yes' ? true : false;
		$this->meta_phone            = $this->get_option('meta_phone') === 'yes' ? true : false;
		$this->meta_billing_address  = $this->get_option('meta_billing_address') === 'yes' ? true : false;
		$this->meta_shipping_address = $this->get_option('meta_shipping_address') === 'yes' ? true : false;
		$this->meta_products         = $this->get_option('meta_products') === 'yes' ? true : false;

		$this->public_key = $this->testmode ? $this->test_key : $this->live_key;
		$this->notify_url         = WC()->api_request_url('WC_Gateway_Vella');
		// Hooks
		add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
		add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
		add_action('admin_notices', array($this, 'admin_notices'));
		add_action(
			'woocommerce_update_options_payment_gateways_' . $this->id,
			array(
				$this,
				'process_admin_options',
			)
		);

		add_action('woocommerce_receipt_' . $this->id, array($this, 'receipt_page'));

		// Payment listener/API hook.
		add_action('woocommerce_api_wc_gateway_vella', array($this, 'verify_vella_transaction'));

		// Webhook listener/API hook.
		add_action('woocommerce_api_tbz_wc_vella_webhook', array($this, 'process_webhooks'));

		// Check if the gateway can be used.
		if (!$this->is_valid_for_use()) {
			$this->enabled = false;
		}
	}

	/**
	 * Check if this gateway is enabled and available in the user's country.
	 */
	public function is_valid_for_use()
	{

		if (!in_array(get_woocommerce_currency(), apply_filters('woocommerce_vella_supported_currencies', array('NGN', 'USD')))) {

			$this->msg = sprintf(__('Vella does not support your store currency. Kindly set it to either NGN (&#8358),  USD (&#36;) <a href="%s">here</a>', 'woo-vella-pay'), admin_url('admin.php?page=wc-settings&tab=general'));

			return false;
		}

		return true;
	}

	/**
	 * Display Vella payment icon.
	 */
	public function get_icon()
	{

		$icon = '<img src="' . WC_HTTPS::force_https_url(plugins_url('images/vella-pay-icon.png', WC_VELLA_PAY_MAIN_FILE)) . '" alt="Vella Payment Options" />';

		return apply_filters('woocommerce_gateway_icon', $icon, $this->id);
	}

	/**
	 * Check if Vella merchant details is filled.
	 */
	public function admin_notices()
	{

		if ($this->enabled == 'no') {
			return;
		}

		// Check required fields.
		if (!($this->public_key)) {
			echo '<div class="error"><p>' . sprintf(__('Please enter your Vella merchant details <a href="%s">here</a> to be able to use the VellaPay plugin.', 'woo-vella-pay'), admin_url('admin.php?page=wc-settings&tab=checkout&section=vella')) . '</p></div>';
			return;
		}
	}

	/**
	 * Check if Vella gateway is enabled.
	 *
	 * @return bool
	 */
	public function is_available()
	{

		if ('yes' == $this->enabled) {

			if (!($this->public_key)) {

				return false;
			}

			return true;
		}

		return false;
	}

	/**
	 * Admin Panel Options.
	 */
	public function admin_options()
	{

?>

		<h2><?php _e('VellaPay', 'woo-vella-pay'); ?>
			<?php
			if (function_exists('wc_back_link')) {
				wc_back_link(__('Return to payments', 'woo-vella-pay'), admin_url('admin.php?page=wc-settings&tab=checkout'));
			}
			?>
		</h2>

		<h4>
			<strong><?php printf(__('Optional: To avoid situations where bad network makes it impossible to verify transactions, set your webhook URL <a href="%1$s" target="_blank" rel="noopener noreferrer">here</a> to the URL below<span style="color: red"><pre><code>%2$s</code></pre></span>', 'woo-vella-pay'), 'https://app.vella.finance', WC()->api_request_url('Tbz_WC_Vella_Webhook')); ?></strong>
		</h4>

		<?php

		if ($this->is_valid_for_use()) {

			echo '<table class="form-table">';
			$this->generate_settings_html();
			echo '</table>';
		} else {
		?>
			<div class="inline error">
				<p><strong><?php _e('Vella Payment Gateway Disabled', 'woo-vella-pay'); ?></strong>: <?php echo $this->msg; ?></p>
			</div>

<?php
		}
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields()
	{

		$form_fields = array(
			'enabled'                          => array(
				'title'       => __('Enable/Disable', 'woo-vella-pay'),
				'label'       => __('Enable Vella', 'woo-vella-pay'),
				'type'        => 'checkbox',
				'description' => __('Enable Vella as a payment option on the checkout page.', 'woo-vella-pay'),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'title'                            => array(
				'title'       => __('Title', 'woo-vella-pay'),
				'type'        => 'text',
				'description' => __('This controls the payment method title which the user sees during checkout.', 'woo-vella-pay'),
				'default'     => __('Pay With Vella', 'woo-vella-pay'),
				'desc_tip'    => true,
			),
			'description'                      => array(
				'title'       => __('Description', 'woo-vella-pay'),
				'type'        => 'textarea',
				'description' => __('This controls the payment method description which the user sees during checkout.', 'woo-vella-pay'),
				'default'     => __('Make payment using your Vella Wallet or Crypto', 'woo-vella-pay'),
				'desc_tip'    => true,
			),
			'testmode'                         => array(
				'title'       => __('Test mode', 'woo-vella-pay'),
				'label'       => __('Enable Test Mode', 'woo-vella-pay'),
				'type'        => 'checkbox',
				'description' => __('Test mode enables you to test payments before going live. <br />Once the LIVE MODE is enabled on your Vella account uncheck this.', 'woo-vella-pay'),
				'default'     => 'yes',
				'desc_tip'    => true,
			),

			'currency'                     => array(
				'title'       => __('Currency', 'woo-vella-pay'),
				'type'        => 'select',
				'description' => __('Your Settlement Currency.', 'woo-vella-pay'),
				'default'     => '',
				'desc_tip'    => false,
				'options'     => array(
					''          => __('Select One', 'woo-vella-pay'),
					'NGN'    => __('NGN', 'woo-vella-pay'),
					'USDC'  => __('USD', 'woo-vella-pay'),
					'USDC'  => __('USDC', 'woo-vella-pay'),
					'USDT'  => __('USDT', 'woo-vella-pay'),
				),
			),
			'test_key'                  => array(
				'title'       => __('Test Key', 'woo-vella-pay'),
				'type'        => 'text',
				'description' => __('Enter your Test Key here', 'woo-vella-pay'),
				'default'     => '',
			),
			'live_key'                  => array(
				'title'       => __('Live Key', 'woo-vella-pay'),
				'type'        => 'text',
				'description' => __('Enter your Live Key here.', 'woo-vella-pay'),
				'default'     => '',
			),
			'merchant_id'                  => array(
				'title'       => __('Vella Tag', 'woo-vella-pay'),
				'type'        => 'text',
				'description' => __('Enter your Vella Tag here.', 'woo-vella-pay'),
				'default'     => '',
			),

			'autocomplete_order'               => array(
				'title'       => __('Autocomplete Order After Payment', 'woo-vella-pay'),
				'label'       => __('Autocomplete Order', 'woo-vella-pay'),
				'type'        => 'checkbox',
				'class'       => 'wc-vella-autocomplete-order',
				'description' => __('If enabled, the order will be marked as complete after successful payment', 'woo-vella-pay'),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'remove_cancel_order_button'       => array(
				'title'       => __('Remove Cancel Order & Restore Cart Button', 'woo-vella-pay'),
				'label'       => __('Remove the cancel order & restore cart button on the pay for order page', 'woo-vella-pay'),
				'type'        => 'checkbox',
				'description' => '',
				'default'     => 'no',
			),
			/*
			
			
			
			'custom_metadata'                  => array(
				'title'       => __('Custom Metadata', 'woo-vella-pay'),
				'label'       => __('Enable Custom Metadata', 'woo-vella-pay'),
				'type'        => 'checkbox',
				'class'       => 'wc-vella-metadata',
				'description' => __('If enabled, you will be able to send more information about the order to vella.', 'woo-vella-pay'),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_order_id'                    => array(
				'title'       => __('Order ID', 'woo-vella-pay'),
				'label'       => __('Send Order ID', 'woo-vella-pay'),
				'type'        => 'checkbox',
				'class'       => 'wc-vella-meta-order-id',
				'description' => __('If checked, the Order ID will be sent to vella', 'woo-vella-pay'),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_name'                        => array(
				'title'       => __('Customer Name', 'woo-vella-pay'),
				'label'       => __('Send Customer Name', 'woo-vella-pay'),
				'type'        => 'checkbox',
				'class'       => 'wc-vella-meta-name',
				'description' => __('If checked, the customer full name will be sent to Vella', 'woo-vella-pay'),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_email'                       => array(
				'title'       => __('Customer Email', 'woo-vella-pay'),
				'label'       => __('Send Customer Email', 'woo-vella-pay'),
				'type'        => 'checkbox',
				'class'       => 'wc-vella-meta-email',
				'description' => __('If checked, the customer email address will be sent to Vella', 'woo-vella-pay'),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_phone'                       => array(
				'title'       => __('Customer Phone', 'woo-vella-pay'),
				'label'       => __('Send Customer Phone', 'woo-vella-pay'),
				'type'        => 'checkbox',
				'class'       => 'wc-vella-meta-phone',
				'description' => __('If checked, the customer phone will be sent to vella', 'woo-vella-pay'),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_billing_address'             => array(
				'title'       => __('Order Billing Address', 'woo-vella-pay'),
				'label'       => __('Send Order Billing Address', 'woo-vella-pay'),
				'type'        => 'checkbox',
				'class'       => 'wc-vella-meta-billing-address',
				'description' => __('If checked, the order billing address will be sent to vella', 'woo-vella-pay'),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_shipping_address'            => array(
				'title'       => __('Order Shipping Address', 'woo-vella-pay'),
				'label'       => __('Send Order Shipping Address', 'woo-vella-pay'),
				'type'        => 'checkbox',
				'class'       => 'wc-vella-meta-shipping-address',
				'description' => __('If checked, the order shipping address will be sent to vella', 'woo-vella-pay'),
				'default'     => 'no',
				'desc_tip'    => true,
			),
			'meta_products'                    => array(
				'title'       => __('Product(s) Purchased', 'woo-vella-pay'),
				'label'       => __('Send Product(s) Purchased', 'woo-vella-pay'),
				'type'        => 'checkbox',
				'class'       => 'wc-vella-meta-products',
				'description' => __('If checked, the product(s) purchased will be sent to vella', 'woo-vella-pay'),
				'default'     => 'no',
				'desc_tip'    => true,
			),*/
		);

		if ('NGN' !== get_woocommerce_currency()) {
			unset($form_fields['custom_gateways']);
		}

		$this->form_fields = $form_fields;
	}


	/**
	 * Payment form on checkout page
	 */
	public function payment_fields()
	{

		if ($this->description) {
			echo esc_textarea($this->description);
		}

		if (!is_ssl()) {
			return;
		}

		if ($this->supports('tokenization') && is_checkout() && $this->saved_cards && is_user_logged_in()) {
			$this->tokenization_script();
			//$this->saved_payment_methods();
			$this->save_payment_method_checkbox();
		}
	}

	/**
	 * Outputs scripts used for vella payment.
	 */
	public function payment_scripts()
	{

		if (!is_checkout_pay_page()) {
			return;
		}

		if ($this->enabled === 'no') {
			return;
		}

		$order_key = sanitize_text_field($_GET['key']);
		$order_id  = absint(get_query_var('order-pay'));

		$order = wc_get_order($order_id);

		$payment_method = method_exists($order, 'get_payment_method') ? $order->get_payment_method() : $order->payment_method;

		if ($this->id !== $payment_method) {
			return;
		}

		//wp_register_script('vella-module');


		$suffix = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) ? '' : '.min';


		wp_enqueue_style('vella-css');
		wp_register_style('vella-css', plugins_url('css/vella.css', WC_VELLA_PAY_MAIN_FILE));

		wp_enqueue_script('jquery');
		wp_enqueue_script('wc_vella', plugins_url('js/vella.js', WC_VELLA_PAY_MAIN_FILE), array('jquery'), WC_VELLA_PAY_VERSION, false);

		wp_enqueue_script('vella-checkout-handler', 'https://checkout.vella.finance/widget/sdk.js', WC_VELLA_PAY_VERSION, false);

		$vella_params = array(
			'key' => $this->public_key,
		);

		if (is_checkout_pay_page() && get_query_var('order-pay')) {
			$first_name = method_exists($order, 'get_billing_first_name') ? $order->get_billing_first_name() : $order->billing_first_name;
			$last_name  = method_exists($order, 'get_billing_last_name') ? $order->get_billing_last_name() : $order->billing_last_name;
			$email         = method_exists($order, 'get_billing_email') ? $order->get_billing_email() : $order->billing_email;
			$amount        = $order->get_total();
			$txnref        = $order_id . '_' . time();
			$the_order_id  = method_exists($order, 'get_id') ? $order->get_id() : $order->id;
			$the_order_key = method_exists($order, 'get_order_key') ? $order->get_order_key() : $order->order_key;
			$currency      = method_exists($order, 'get_currency') ? $order->get_currency() : $order->order_currency;

			if ($the_order_id == $order_id && $the_order_key == $order_key) {

				$vella_params['email']    = $email;
				$vella_params['amount']   = $amount;
				$vella_params['txnref']   = $txnref;
				$vella_params['currency'] = $currency;
				$vella_params['order_id'] = $order_id;
				$vella_params['merchant_id'] = $this->merchant_id;
				$vella_params['name']    = $first_name . ' ' . $last_name;
				$vella_params['cb_url'] = WC()->api_request_url('Vella_Payment_Gateway');
			}

			if ($this->custom_metadata) {

				if ($this->meta_order_id) {

					$vella_params['meta_order_id'] = $order_id;
				}

				if ($this->meta_name) {

					$first_name = method_exists($order, 'get_billing_first_name') ? $order->get_billing_first_name() : $order->billing_first_name;
					$last_name  = method_exists($order, 'get_billing_last_name') ? $order->get_billing_last_name() : $order->billing_last_name;

					$vella_params['meta_name'] = $first_name . ' ' . $last_name;
				}

				if ($this->meta_email) {

					$vella_params['meta_email'] = $email;
				}

				if ($this->meta_phone) {

					$billing_phone = method_exists($order, 'get_billing_phone') ? $order->get_billing_phone() : $order->billing_phone;

					$vella_params['meta_phone'] = $billing_phone;
				}

				if ($this->meta_products) {

					$line_items = $order->get_items();

					$products = '';

					foreach ($line_items as $item_id => $item) {
						$name      = $item['name'];
						$quantity  = $item['qty'];
						$products .= $name . ' (Qty: ' . $quantity . ')';
						$products .= ' | ';
					}

					$products = rtrim($products, ' | ');

					$vella_params['meta_products'] = $products;
				}

				if ($this->meta_billing_address) {

					$billing_address = $order->get_formatted_billing_address();
					$billing_address = esc_html(preg_replace('#<br\s*/?>#i', ', ', $billing_address));

					$vella_params['meta_billing_address'] = $billing_address;
				}

				if ($this->meta_shipping_address) {

					$shipping_address = $order->get_formatted_shipping_address();
					$shipping_address = esc_html(preg_replace('#<br\s*/?>#i', ', ', $shipping_address));

					if (empty($shipping_address)) {

						$billing_address = $order->get_formatted_billing_address();
						$billing_address = esc_html(preg_replace('#<br\s*/?>#i', ', ', $billing_address));

						$shipping_address = $billing_address;
					}

					$vella_params['meta_shipping_address'] = $shipping_address;
				}
			}

			update_post_meta($order_id, '_vella_txn_ref', $txnref);
		}

		wp_localize_script('wc_vella', 'wc_vella_params', $vella_params);
	}


	public function add_type_attribute($tag, $handle, $src)
	{
		// if not your script, do nothing and return original $tag
		if ('vella-checkout-handler' != $handle) {
			return $tag;
		}
		// change the script tag by adding type="module" and return it.
		$tag = '<script type="module" src="' . esc_url($src) . '"></script>';
		return $tag;
	}

	/**
	 * Load admin scripts.
	 */
	public function admin_scripts()
	{

		if ('woocommerce_page_wc-settings' !== get_current_screen()->id) {
			return;
		}
	}

	/**
	 * Process the payment.
	 *
	 * @param int $order_id
	 *
	 * @return array|void
	 */
	public function process_payment($order_id)
	{
		global $woocommerce;
		$order = new WC_Order($order_id);


		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url(true)
		);
	}


	/**
	 * Show new card can only be added when placing an order notice.
	 */
	public function add_payment_method()
	{

		wc_add_notice(__('You can only add a new card when placing an order.', 'woo-vella-pay'), 'error');

		return;
	}

	/**
	 * Displays the payment page.
	 *
	 * @param $order_id
	 */
	public function receipt_page($order_id)
	{

		$order = wc_get_order($order_id);

		echo '<div id="wc-vella-form">';
		echo '<p>' . __('Thank you for your order, please click the button below to pay with Vella.', 'woo-vella-pay') . '</p>';
		echo '<div id="vella_form"><form method="post" action="' . WC()->api_request_url('WC_Gateway_Vella') . '" id="vella_order_review" ></form>
				<button type="button" style="background: none;" id="vella-payment-button">
				<img src="' . WC_HTTPS::force_https_url(plugins_url('images/vella-button-01.svg', WC_VELLA_PAY_MAIN_FILE)) . '" width="200" alt="Vella Payment Button" />
				</button>
				<span style="background: none; display:none;" id="vella-loading-button">processing...</span>
		';

		if (!$this->remove_cancel_order_button) {
			echo '  <a class="button cancel" id="vella-cancel-payment-button" href="' . esc_url($order->get_cancel_order_url()) . '">' . __('Cancel order &amp; restore cart', 'woo-vella-pay') . '</a></div>';
		}

		echo '</div>';
	}

	/**
	 * Verify vella payment.
	 */
	public function verify_vella_transaction()
	{

		if ($this->testmode) {
			$base_url =  'https://sandbox.vella.finance/api/v1/checkout/transaction/';
		} else {
			$base_url =  'https://api.vella.finance/api/v1/checkout/transaction/';
		}

		if (isset($_REQUEST['vella_txnref'])) {
			/** clean txnref */
			$vella_txn_ref = sanitize_text_field($_REQUEST['vella_txnref']);
		} else {
			$vella_txn_ref = false;
		}

		@ob_clean();

		if ($vella_txn_ref) {

			/** explode cleaned txn_ref */
			$order_details = explode('_', $vella_txn_ref);

			$order_id = (int) $order_details[0];

			/** Get order */
			$order = wc_get_order($order_id);

			$vella_url = $base_url . $vella_txn_ref . '/verify';

			$headers = array(
				'Authorization' => 'Bearer ' . $this->public_key,
			);

			$args = array(
				'headers' => $headers,
				'timeout' => 60,
			);

			$request = wp_remote_get($vella_url, $args);

			if (!is_wp_error($request) && 200 === wp_remote_retrieve_response_code($request)) {

				$vella_response = json_decode(wp_remote_retrieve_body($request));

				$statusArray = [
					"successful",
					"completed",
					"success",
				];

				//if (strtolower($vella_response->data->status) == "successful" || $vella_response->data->status == "Success" || $vella_response->data->status == "Completed") {

				if (in_array($vella_response->data->status, $statusArray)) {

					if (in_array($order->get_status(), array('processing', 'completed', 'on-hold'))) {

						wp_redirect($this->get_return_url($order));

						exit;
					}

					$order_total      = $order->get_total();
					$order_currency   = method_exists($order, 'get_currency') ? $order->get_currency() : $order->get_order_currency();
					$currency_symbol  = get_woocommerce_currency_symbol($order_currency);
					$amount_paid      =  floatval($vella_response->data->amount);
					$vella_ref     =  $vella_response->data->reference;
					$payment_currency = strtoupper($vella_params["currency"]);
					$gateway_symbol   = get_woocommerce_currency_symbol($payment_currency);

					// check if the amount paid is equal to the order amount.
					if ($amount_paid < $order_total) {

						$order->update_status('on-hold', '');

						add_post_meta($order_id, '_transaction_id', $vella_ref, true);

						$notice      = sprintf(__('Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-vella-pay'), '<br />', '<br />', '<br />');
						$notice_type = 'notice';

						// Add Customer Order Note
						$order->add_order_note($notice, 1);

						// Add Admin Order Note
						$admin_order_note = sprintf(__('<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>Vella Transaction Reference:</strong> %9$s', 'woo-vella-pay'), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $vella_ref);
						$order->add_order_note($admin_order_note);

						function_exists('wc_reduce_stock_levels') ? wc_reduce_stock_levels($order_id) : $order->reduce_order_stock();

						wc_add_notice($notice, $notice_type);
					} else {

						$order->payment_complete($vella_ref);
						$order->add_order_note(sprintf(__('Payment via Vella was successful (Transaction Reference: %s)', 'woo-vella-pay'), $vella_ref));

						if ($this->is_autocomplete_order_enabled($order)) {
							$order->update_status('completed');
						}
						WC()->cart->empty_cart();
					}
				} else if (strtolower($vella_response->data->status) == "pending") {



					$order->update_status('on-hold', '');

					update_post_meta($order_id, '_transaction_id', $vella_ref);

					$notice      = sprintf(__('Thank you for shopping with us.%1$sYour payment is Pending, Your order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-vella-pay'), '<br />', '<br />', '<br />');
					$notice_type = 'notice';

					// Add Customer Order Note
					$order->add_order_note($notice, 1);

					// Add Admin Order Note
					$admin_order_note = sprintf(__('<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Pending Payment Confirmation.<strong>Vella Transaction Reference:</strong> %9$s', 'woo-vella-pay'), '<br />', '<br />', '<br />', $order_currency, $currency_symbol, $payment_currency, $gateway_symbol, '<br />', $vella_ref);
					$order->add_order_note($admin_order_note);

					function_exists('wc_reduce_stock_levels') ? wc_reduce_stock_levels($order_id) : $order->reduce_order_stock();

					wc_add_notice($notice, $notice_type);
				} else {

					$order->update_status('failed', __('Payment was declined by Vella.', 'woo-vella-pay'));
				}
			}

			wp_redirect($this->get_return_url($order));

			exit;
		}

		wp_redirect(wc_get_page_permalink('cart'));

		exit;
	}

	/**
	 * Process Webhook.
	 */
	public function process_webhooks()
	{
		if ((strtoupper($_SERVER['REQUEST_METHOD']) != 'POST')) {
			exit;
		}

		$json = file_get_contents('php://input');

		$event = json_decode($json);

		if ('transaction.completed' == $event->type) {

			sleep(5);

			$order_details = explode('_', $event->data->reference);

			$order_id = (int) $order_details[0];

			$order = wc_get_order($order_id);

			$vella_txn_ref = get_post_meta($order_id, '_vella_txn_ref', true);

			if ($event->data->reference != $vella_txn_ref) {
				exit;
			}

			http_response_code(200);

			if (in_array($order->get_status(), array('processing', 'completed', 'on-hold'))) {
				exit;
			}

			$order_currency = method_exists($order, 'get_currency') ? $order->get_currency() : $order->get_order_currency();

			$currency_symbol = get_woocommerce_currency_symbol($order_currency);

			$order_total = $order->get_total();

			$amount_paid = floatval($event->data->amount);

			$vella_ref = $event->data->reference;

			$payment_currency = strtoupper($event->data->currency);

			$gateway_symbol = get_woocommerce_currency_symbol($payment_currency);

			// check if the amount paid is equal to the order amount.
			if ($amount_paid < $order_total) {

				$order->update_status('on-hold', '');

				add_post_meta($order_id, '_transaction_id', $vella_ref, true);

				$notice      = sprintf(__('Thank you for shopping with us.%1$sYour payment transaction was successful, but the amount paid is not the same as the total order amount.%2$sYour order is currently on hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-vella-pay'), '<br />', '<br />', '<br />');
				$notice_type = 'notice';

				// Add Customer Order Note.
				$order->add_order_note($notice, 1);

				// Add Admin Order Note.
				$admin_order_note = sprintf(__('<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Amount paid is less than the total order amount.%3$sAmount Paid was <strong>%4$s (%5$s)</strong> while the total order amount is <strong>%6$s (%7$s)</strong>%8$s<strong>Vella Transaction Reference:</strong> %9$s', 'woo-vella-pay'), '<br />', '<br />', '<br />', $currency_symbol, $amount_paid, $currency_symbol, $order_total, '<br />', $vella_ref);
				$order->add_order_note($admin_order_note);

				function_exists('wc_reduce_stock_levels') ? wc_reduce_stock_levels($order_id) : $order->reduce_order_stock();

				wc_add_notice($notice, $notice_type);

				WC()->cart->empty_cart();
			} else {

				/*if ( $payment_currency !== $order_currency ) {

					$order->update_status( 'on-hold', '' );

					update_post_meta( $order_id, '_transaction_id', $vella_ref );

					$notice      = sprintf( __( 'Thank you for shopping with us.%1$sYour payment was successful, but the payment currency is different from the order currency.%2$sYour order is currently on-hold.%3$sKindly contact us for more information regarding your order and payment status.', 'woo-vella-pay' ), '<br />', '<br />', '<br />' );
					$notice_type = 'notice';

					// Add Customer Order Note.
					$order->add_order_note( $notice, 1 );

					// Add Admin Order Note.
					$admin_order_note = sprintf( __( '<strong>Look into this order</strong>%1$sThis order is currently on hold.%2$sReason: Order currency is different from the payment currency.%3$sOrder Currency is <strong>%4$s (%5$s)</strong> while the payment currency is <strong>%6$s (%7$s)</strong>%8$s<strong>Vella Transaction Reference:</strong> %9$s', 'woo-vella-pay' ), '<br />', '<br />', '<br />', $order_currency, $currency_symbol, $payment_currency, $gateway_symbol, '<br />', $vella_ref );
					$order->add_order_note( $admin_order_note );

					function_exists( 'wc_reduce_stock_levels' ) ? wc_reduce_stock_levels( $order_id ) : $order->reduce_order_stock();

					wc_add_notice( $notice, $notice_type );

				} else {*/

				$order->payment_complete($vella_ref);

				$order->add_order_note(sprintf(__('Payment via Vella successful (Transaction Reference: %s)', 'woo-vella-pay'), $vella_ref));

				WC()->cart->empty_cart();

				if ($this->is_autocomplete_order_enabled($order)) {
					$order->update_status('completed');
				}
				//}
			}

			//$this->save_card_details( $event, $order->get_user_id(), $order_id );

			exit;
		}

		exit;
	}

	/**
	 * Get custom fields to pass to Vella.
	 *
	 * @param int $order_id WC Order ID
	 *
	 * @return array
	 */
	public function get_custom_fields($order_id)
	{

		$order = wc_get_order($order_id);

		$custom_fields = array();

		$custom_fields[] = array(
			'display_name'  => 'Plugin',
			'variable_name' => 'plugin',
			'value'         => 'woo-vella-pay',
		);

		if ($this->custom_metadata) {

			if ($this->meta_order_id) {

				$custom_fields[] = array(
					'display_name'  => 'Order ID',
					'variable_name' => 'order_id',
					'value'         => $order_id,
				);
			}

			if ($this->meta_name) {

				$first_name = method_exists($order, 'get_billing_first_name') ? $order->get_billing_first_name() : $order->billing_first_name;
				$last_name  = method_exists($order, 'get_billing_last_name') ? $order->get_billing_last_name() : $order->billing_last_name;

				$custom_fields[] = array(
					'display_name'  => 'Customer Name',
					'variable_name' => 'customer_name',
					'value'         => $first_name . ' ' . $last_name,
				);
			}

			if ($this->meta_email) {

				$email = method_exists($order, 'get_billing_email') ? $order->get_billing_email() : $order->billing_email;

				$custom_fields[] = array(
					'display_name'  => 'Customer Email',
					'variable_name' => 'customer_email',
					'value'         => $email,
				);
			}

			if ($this->meta_phone) {

				$billing_phone = method_exists($order, 'get_billing_phone') ? $order->get_billing_phone() : $order->billing_phone;

				$custom_fields[] = array(
					'display_name'  => 'Customer Phone',
					'variable_name' => 'customer_phone',
					'value'         => $billing_phone,
				);
			}

			if ($this->meta_products) {

				$line_items = $order->get_items();

				$products = '';

				foreach ($line_items as $item_id => $item) {
					$name     = $item['name'];
					$quantity = $item['qty'];
					$products .= $name . ' (Qty: ' . $quantity . ')';
					$products .= ' | ';
				}

				$products = rtrim($products, ' | ');

				$custom_fields[] = array(
					'display_name'  => 'Products',
					'variable_name' => 'products',
					'value'         => $products,
				);
			}

			if ($this->meta_billing_address) {

				$billing_address = $order->get_formatted_billing_address();
				$billing_address = esc_html(preg_replace('#<br\s*/?>#i', ', ', $billing_address));

				$vella_params['meta_billing_address'] = $billing_address;

				$custom_fields[] = array(
					'display_name'  => 'Billing Address',
					'variable_name' => 'billing_address',
					'value'         => $billing_address,
				);
			}

			if ($this->meta_shipping_address) {

				$shipping_address = $order->get_formatted_shipping_address();
				$shipping_address = esc_html(preg_replace('#<br\s*/?>#i', ', ', $shipping_address));

				if (empty($shipping_address)) {

					$billing_address = $order->get_formatted_billing_address();
					$billing_address = esc_html(preg_replace('#<br\s*/?>#i', ', ', $billing_address));

					$shipping_address = $billing_address;
				}
				$custom_fields[] = array(
					'display_name'  => 'Shipping Address',
					'variable_name' => 'shipping_address',
					'value'         => $shipping_address,
				);
			}
		}

		return $custom_fields;
	}

	/**
	 * Process a refund request from the Order details screen.
	 *
	 * @param int    $order_id WC Order ID.
	 * @param null   $amount   WC Order Amount.
	 * @param string $reason   Refund Reason
	 *
	 * @return bool|WP_Error
	 */
	public function process_refund($order_id, $amount = null, $reason = '')
	{
	}

	/**
	 * Checks if WC version is less than passed in version.
	 *
	 * @param string $version Version to check against.
	 *
	 * @return bool
	 */
	public function is_wc_lt($version)
	{
		return version_compare(WC_VERSION, $version, '<');
	}


	public function validate_fields()
	{

		return true;
	}

	/**
	 * Checks if autocomplete order is enabled for the payment method.
	 *
	 * @since 5.7
	 * @param WC_Order $order Order object.
	 * @return bool
	 */
	protected function is_autocomplete_order_enabled($order)
	{
		$autocomplete_order = false;

		$payment_method = $order->get_payment_method();

		$vella_settings = get_option('woocommerce_' . $payment_method . '_settings');

		if (isset($vella_settings['autocomplete_order']) && 'yes' === $vella_settings['autocomplete_order']) {
			$autocomplete_order = true;
		}

		return $autocomplete_order;
	}

	/**
	 * Retrieve the payment channels configured for the gateway
	 *
	 * @since 5.7
	 * @param WC_Order $order Order object.
	 * @return array
	 */
	protected function get_gateway_payment_channels($order)
	{

		$payment_method = $order->get_payment_method();

		if ('vella' === $payment_method) {
			return array();
		}

		$payment_channels = $this->payment_channels;

		if (empty($payment_channels)) {
			$payment_channels = array('card');
		}

		return $payment_channels;
	}
}
