# PayZen for CS-CART

PayZen for CS-CART is an open source plugin that links e-commerce websites based on CS-CART to PayZen secure payment gateway developed by [Lyra Network](https://www.lyra.com/).

## Installation & Upgrade

If the plugin is already installed, uninstall it in `Add-ons > Manage add-ons` menu. Do not forget to note the parameters of your plugin and in particular to note the production key which is no longer visible in the PayZen Back Office.

To install the module, in the CS-CART admin panel:

- Go to `Add-ons > Manage add-ons` menu.
- Click the **Setting icon** ((+) button in CS-CART older versions).
- Click on `Manual installation`.
- Click on the `Local` button and upload the payment module zip file.
- Click on the `Upload & install` button.

To add the PayZen payment method:

- Go to `Administration > Payment methods` menu.
- Click the (+) button.
- Set the parameters as following:
    - Name: PayZen
    - Processor: PayZen
    - Payment category: Credit card
    - Configure the rest of the parameters according to your needs.
- Once all parameters are filled, click the `Create` button.

## Configuration

In the CS-CART admin panel:

- Go to `Administration > Payment methods`.
- Click on `PayZen` then click on the `Configure` tab.
- Set the parameters then click the `Save` button.

## License

Each PayZen payment module source file included in this distribution is licensed under the The MIT License (MIT).

Please see LICENSE.txt for the full text of the MIT license. It is also available through the world-wide-web at this URL: https://opensource.org/licenses/mit-license.html.