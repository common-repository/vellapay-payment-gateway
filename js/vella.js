jQuery(function ($) {


    function wcVellaCustomFields() {

        let custom_fields = [
            {
                "display_name": "Plugin",
                "variable_name": "plugin",
                "value": "woo-vella-pay"
            }
        ];

        if (wc_vella_params.meta_order_id) {

            custom_fields.push({
                display_name: "Order ID",
                variable_name: "order_id",
                value: wc_vella_params.order_id
            });

        }

        if (wc_vella_params.name) {

            custom_fields.push({
                display_name: "Customer Name",
                variable_name: "customer_name",
                value: wc_vella_params.name
            });
        }

        if (wc_vella_params.meta_email) {

            custom_fields.push({
                display_name: "Customer Email",
                variable_name: "customer_email",
                value: wc_vella_params.email
            });
        }

        if (wc_vella_params.meta_phone) {

            custom_fields.push({
                display_name: "Customer Phone",
                variable_name: "customer_phone",
                value: wc_vella_params.phone
            });
        }

        if (wc_vella_params.meta_billing_address) {

            custom_fields.push({
                display_name: "Billing Address",
                variable_name: "billing_address",
                value: wc_vella_params.meta_billing_address
            });
        }

        if (wc_vella_params.meta_shipping_address) {

            custom_fields.push({
                display_name: "Shipping Address",
                variable_name: "shipping_address",
                value: wc_vella_params.meta_shipping_address
            });
        }

        if (wc_vella_params.meta_products) {

            custom_fields.push({
                display_name: "Products",
                variable_name: "products",
                value: wc_vella_params.meta_products
            });
        }
        custom_fields.push({
            display_name: "Host",
            variable_name: "host",
            value:  window.location.hostname
        });
        return custom_fields;
    }

    document.getElementById('vella-payment-button').addEventListener('click', function (e) {
        //e.preventDefault();
        let $form = $('form#vella_form, form#vella_order_review'),
            vella_txnref = $form.find('input.vella_txnref');

        let currency;
        $("#vella-loading-button").fadeIn(300);
        $("#vella-payment-button").fadeOut(500);
        var public_key = wc_vella_params.key; 
        var merchant_id = wc_vella_params.merchant_id;
        var order_amount = wc_vella_params.amount; 
        var customer_email = wc_vella_params.email;
        var customer_name = wc_vella_params.name; 
        var order_currency = wc_vella_params.currency; 
        var cbUrl = wc_vella_params.cb_url;
       
        var redirect_url;
       /*  if (order_currency === "NGN") {
            currency = "NGN";
        } else if (order_currency === "USD") {
            currency = "USDC";
        } else {
            $("#vella-payment-button").fadeOut(500).text("Currency Not Supported");
            return;
        } */
        let vellaSDK = VellaCheckoutSDK.init(public_key, {
            email: customer_email,
            name: customer_name,
            amount: parseFloat(order_amount),
            currency: order_currency,
            merchant_id: merchant_id,
            custom_meta: JSON.stringify(wcVellaCustomFields()),
            source:"woocommerce",
            reference: wc_vella_params.txnref
        });

        vellaSDK.onSuccess(response => {
            $("#vella-payment-button").fadeIn(500);
            $("#vella-loading-button").fadeOut(500);
            $form.append('<input type="hidden" class="vella_txnref" name="vella_txnref" value="' + wc_vella_params.txnref + '"/>');
            $form.submit();
            console.log("data", response);
        })
        vellaSDK.onError(error => {
            $("#vella-payment-button").fadeIn(500);
            $("#vella-loading-button").fadeOut(500);
            console.log(error)
            alert(error)
        });
        vellaSDK.onClose(() => {
            $("#vella-payment-button").fadeIn(500);
            $("#vella-loading-button").fadeOut(500);
            console.log("closed");
            if (redirect_url) {
                redirectTo(redirect_url);
            }
        });
    })
    var redirectTo = function (url) {
        location.href = url;
    };
})