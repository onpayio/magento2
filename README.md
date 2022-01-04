# OnPay - Magento 2.4+ Payment Gateway

Payment Gateway Extension for Magento 2, based on the official OnPay PHP SDK. The plugin adds the following functionality to Magento:

-   Usage of OnPay as a payment method.
-   Validation of orders with callbacks directly from OnPay, outside the context of the cardholders browser.
-   Management of transaction on order pages in Magento admin.

The plugin is tested and confirmed working on

-   Magento 2.4

## Usage

### Installation via Composer (Recommended)

 - Install the module composer by running `composer require onpay/magento2`
 - Enable the module by running `php bin/magento module:enable OnPay_OnPay`
 - Apply database updates by running `php bin/magento setup:upgrade`
 - Compile Magento code base by running `php bin/magento setup:di:compile`
 - Deploy static content by running `php bin/magento setup:static-content:deploy`
 - Clean the cache by running `php bin/magento cache:clean`
 - You are ready to go




