#  Literally WordPress 

Contributors: Takahashi_Fumiki  
Tags: payment, paypal, ebook, ticket, subscription, monetize  
Requires at least: 3.1  
Tested up to: 3.5.2  
Stable tag: 0.9.3.0  

> **THIS PLUGIN IS ABONDONED**
> We stop maintaining Litearlly WordPress because of few development resource. Please use alteranatives.
> If you need special support for importing or exporting, please give us a feed back from the support forum.

This plugin make your WordPress post payable. Registered users can buy your post via PayPal and so on.

##  Description 

Literally WordPress make your post payable. Registered users can buy your post via PayPal and other payment methods.

###  What you can sell 

You can provide your customers serveral services.

* Digital contents **e.g. ePub, PDF, MP3**
* Event ticket **e.g. Live ticket**
* Subscription **e.g. User only magazine**

###  To know more 

There also is plugin support site [LWPer.info](http://lwper.info). Create thread, read TIPs or get sample theme there.

###  Acknowledgements 

Icon sets are provided by [Functions](http://wefunction.com/2008/07/function-free-icon-set/).

##  Installation 

Installation is easy, but you need activation after plugin installation.

###  Installation 

1. Donwload plugin file and unzip it.
2. Upload `literally-wordpress` folder to the `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

###  Setup 

To use Literally WordPress, you have to set up options to activate payment methods.

1. In WordPress admin panel, go to `Literally WP > General Setting` then You will see setting dashboard.
2. On payment options tab, you have to set up payment options. If you would like to use PayPal, PayPal credential of buisiness account is required.
3. After payment options set up, set up what you would like to sell. 

**Note: ** Most of payment methods needs contract with payment agencies. To use this plugin on productional environment, you have to contract with them. It depends on **your marchandise and Web site**, not on this plugin. For example, most of payment agency doesn't accept selling porno videos.

###  Frequently Asked Questions 

> Does it have diffrent payment method from PayPal?

Yes. There is 4 payment options.

1. PayPal
2. Bank Transfer
3. GMO Payment Gateway *(Japanese Payment agency)*
4. Softbank Payment Service *(Japanese Payment agency)*

Except Bank Transfer, contract with payment agency is required.You can visit plugin support site [LWPer.info](http://lwper.info) and feel free to ask questions.

> Can I use this plugin without customize theme?
> 
> Can I customize plugin's appearance?

Yes to both. LWP has some proper UIs like forms, button and so on, and they are designed suit to WordPress Admin UI. If you would like to customize them, you can stop assets auto-loading and use your own CSS. Or else, if you are well-informed theme developer, you can create your own theme with LWP template tags(functions). See detail at `Literally WP > General settings > Misc` on WP admin panel.

##  Screenshots 

1. Transaction form is automatically generated.
2. *Buy now* widget is ready.
3. You can assign purchase history page on front page.
4. Event ticket has QR code and event specific code. Event promotor can manage participants.
5. Transaction history dashboard.

##  Changelog 

###  0.9.3.0 

* GMO Payment Gateway is now supported.
* Softbank Payment Service is suppoted.
* Redund price will be saved.
* Many bug fix

###  0.9.2.5 

* Many bug fix
* Support for Android XML-RPC API(exprimental)
* Enhanced campaign list.
* Now you can wait for cancellation on event.

###  0.9.2.4 

* Small bug fix
* Enhanced event list

###  0.9.2.3 

* File uploader's label is wrong.

###  0.9.2.2 

* Miner enhancement on XML-RPC API.

###  0.9.2.1 

* Some bug fix
* XML-RPC API for iOS is updated. Now you can save UUID.

###  0.9.2.0 

* Add iOS non-consumable product support.
* Many bug fixes.

###  0.9.1.2 

* Bugfix. Sorry for that.

###  0.9.1.1 

* Bugfix.

###  0.9.1 

* Added event ticket functions. Now you can sell your event ticket.
* Added transaction summary on dash board.
* Some bugs maybe fixed.

###  0.9.0 

* Added promotion functions

###  0.8.8 

* Add subscription plan feature.
* Add smart phone support on PayPal Landing page.
* Add Payment selection page.
* Bug fix.

###  0.8.6 

* Enable user to pay via bank account.
* You can refund to your customer.
* Delete related data on uninstallation.
* Database structure has been changed. Don't forget to backup your data on upgrading!
* Bug fixes.

###  0.8.2 

* Bug fix. Count Down timer uses console.log().

###  0.8.1 

* Bug fix.

###  0.8 

* 1st release.

##  Upgrade Notice 

###  0.9.3.0 

* Refund price will be saved. Old partial refund(only event ticket) is detected information. You can fix them from `Literally WP > Refund History`.
* Form template was added. If you have custom design, please check your own style.

###  0.8 

Nothing.