=== CallRail Phone Call Tracking ===
Contributors: apowellgt
Tags: call tracking, analytics, seo, ppc, adwords, conversion tracking, optimization
Requires at least: 3.0
Tested up to: 5.8.2
Stable tag: 0.4.9

Dynamically swap CallRail tracking phone numbers based on the visitor's referring source.

== Description ==

CallRail is here to bring complete visibility to the marketers who rely on quality inbound leads to measure success. Our customers live in a results-driven world, and giving them a clear view into their digital marketing efforts is a first priority for CallRail. We see the opportunities in surfacing and connecting data from calls, forms, chat and beyond — helping our customers get to better outcomes.

Our WordPress plugin allows you to learn detailed information about the source and web session of every caller from your website using a process called [Dynamic Number Insertion](https://www.callrail.com/leads/dynamic-number-insertion-2/). It also powers our form tracking tool, which gives you the power to attribute form submissions back to their source and learn about what the user did on your site before submitting the form.

* Learn more about [CallRail](https://www.callrail.com/).
* Check out our WP plugin [support documentation.](https://support.callrail.com/hc/en-us/articles/201011537)

== Installation ==

1. Sign in to your CallRail account and click the **Settings tab**.
2. Select the company you want from the dropdown menu.
3. Find the WordPress plugin in the list of integrations and click **Instructions**.
4. Download the plugin and follow the instructions listed.

Full documentation can be found [here](https://support.callrail.com/hc/en-us/articles/201011537).

== Changelog ==

= 0.2 =

* Initial public release.

= 0.3 =

* Update to version 2 of the javascript tracking script (swap.js)
* Prompt the user to add an API key after installation.
* Don't insert the CallRail script if no API key is present.
* Trim whitespace surrounding the API key before saving.

= 0.3.1 =

* Update to version 3 of the javascript tracking script (swap.js)

= 0.3.2 =

* Update to version 4 of the javascript tracking script (swap.js) which supports more advanced number replacement.

= 0.3.3 =

* Update to version 5 of the javascript tracking script (swap.js)

= 0.3.4 =

* Update to version 7 of the javascript tracking script (swap.js) and serve the script via the MaxCDN network (cdn.callrail.com).

= 0.3.5 =

* Update to version 8 of the javascript tracking script (swap.js).

= 0.3.6 =

* Update to version 10 of the javascript tracking script (swap.js).

= 0.3.7 =

* Add a HTML comment so the CallRail support team can see when swap.js is installed via WordPress.

= 0.3.8 =

* Update to version 11 of the javascript tracking script (swap.js).

= 0.3.11 =

* Set Callrail cookies via HTTP using xhr request from swap.js script.

= 0.4.0 =

* Added an optional feature to load required scripts as first-party through WordPress.

= 0.4.1 =

* Various bug fixes.
* Default first-party swap.js to on after testing if it is supported or not.

= 0.4.2 =

* Fix bug where forms were not rendering for first-party enabled sites.

= 0.4.3 =
* update the readme.

= 0.4.4 =
* Change the default value for the "Enable As First Party Script" flag to false for initial Installations.

= 0.4.5 =
* Add the newly required parameter "permission_callback" to all custom REST endpoint definitions

= 0.4.6 =
* Updating tested up to version.

= 0.4.7 =
* Updating tested up to version. redeploy.

= 0.4.8 =
* Updating tested up to version. redeploy.

= 0.4.9 =

* Fixed End User IP Address detection
