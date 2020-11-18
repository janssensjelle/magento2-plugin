/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Paynl_Payment/js/view/payment/method-renderer/default',
        'mage/url', 
        'Magento_Checkout/js/model/quote'
    ],
    function ($, Component, url, quote) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Paynl_Payment/payment/ideal'
            },

            selectedBank: null,
            getBanks: function(){
                return window.checkoutConfig.payment.banks[this.item.method];
            },
            showBanks: function(){
                return window.checkoutConfig.payment.banks[this.item.method].length > 0;
            },
            isVisible:function(){
                var disallowedShippingMethods = this.getDisallowedShipping().split(',');
                if($.isArray(disallowedShippingMethods) && disallowedShippingMethods.length > 0){
                    var carrier_code = typeof quote.shippingMethod().carrier_code !== 'undefined' ? quote.shippingMethod().carrier_code + '_' : '';
                    var method_code = typeof quote.shippingMethod().method_code !== 'undefined' ? quote.shippingMethod().method_code : '';
                    var currentShippingMethod = carrier_code + method_code;                  
                    if(disallowedShippingMethods.includes(currentShippingMethod) && currentShippingMethod.length > 0){
                        return false;
                    }
                }      
                if(this.getforCompany() == 1 && this.getCompany().length != 0){
                    return false;
                }
                if(this.getforCompany() == 2 && this.getCompany().length == 0){
                    return false;
                }
                return true;
            },
            getDisallowedShipping: function () {
                return window.checkoutConfig.payment.disallowedshipping[this.item.method];
            }, 
            getCompany: function () {                
                if (typeof quote.billingAddress._latestValue.company !== 'undefined') {
                    return quote.billingAddress._latestValue.company;
                }
                return '';                
            },   
            getforCompany   : function () {
                return window.checkoutConfig.payment.showforcompany[this.item.method];
            }, 
            /**
             * Get payment method data
             */
            getData: function () {
                return {
                    'method': this.item.method,
                    'po_number': null,
                    'additional_data': {
                        "bank_id": this.selectedBank
                    }
                };
            }
        });
    }
);