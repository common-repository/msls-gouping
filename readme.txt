=== MSLS Grouping ===
Contributors: Asumaru
Donate link: http://asumaru.com/business/wp-plugins/asm-msls-grouping/
Tags: multilingual, multisite, language, switcher, international, localization, i18n, grouping, flag, WPLANG
Requires at least: 4.6
Tested up to: 4.7.3
Stable tag: 0.2.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

You can multilingualize by "Multisite Language Switcher" with grouping the sites of the plural same languages.

== Description ==

"Multisite Language Switcher" is very attractive plugin.
However, It may not be available for your site plan.

* Site A (English)   example-site.com/
* Site A (Japanese)  example-site.com/jp/
* Site B (English)   example-site.com/siteB/
* Site B (Japanese)  example-site.com/siteB-jp/
* Site C (English)   example-site.com/siteC/
* Site C (Japanese)  example-site.com/siteC-jp/

"Multisite Language Switcher" cannot tell the difference between site A, site B and site C.
Therefore you cannot connect the language when there are site A, site B, site C.

"MSLS Grouping" adds "group_key" to settings of "Multisite Language Switcher".
For example, you set "group_key" with "SiteA" or "SiteB" or "SiteC".
You can limit a language site in a group by doing this plugin.
You can display only the first site when there are multiple same language sites in a group.

And, it provide shortcode "SameLangSites" and widget "SameLangSites".
You can output the list of different groups of the same language.
For example, "SameLangSites" of "site A (English)" lists " site B (English)" and "site C (English)".

* Site A (English)  => Site B (English), Site C (English)
* Site B (Japanese) => Site A (Japanese), Site C (Japanese)

And, it provide "Language" column on the Table of Sites in network.

And, it provide "Language Flag" in Admin-Bar.

== Installation ==

1. "Multisite Language Switcher" is required.
1. Upload the plugin files to the `/wp-content/plugins/asm-MSLS_Grouping` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin in your plugin administration page (by the network administrator on all the blogs or by the root blog administrator for each particular blog).
1. Use the Settings->Multisite Language Switcher Options screen to configure the plugin
1. Set a site group key to "group_key".
1. Shortcode "SameLangSites" : \[SameLangSites\](list-template)\[/SameLangSites\]

== Frequently Asked Questions ==

= What is the replacment strings in list-template? =
* %before_item% : Prefix of the list item.
* %item_class% : List-item CSS class.
* %lang_class% : Item-language CSS class.
* %group_class% : Group-key CSS class.
* %url% : Item link url.
* %link_target% : Link target.
* %name% : Item name.
* %after_item% : Suffix of the list item.

= How do you not come to display language column on the Table of Sites in network? =
You must add the following cord to functions.php in the theme.
> add_filter( 'asm_language-column.blogs.network', create_function( '', 'return false;' ) );

= How do you not come to display language flags? =
You must add the following cord to functions.php in the theme.
> add_filter( 'asm_language-flags.admin-bar', create_function( '', 'return false;' ) );

= How do you not come to force WPLANG in the setting-general? =
You must add the following cord to functions.php in the theme.
> add_filter( 'asm_force_WPLANG.options_general', create_function( '', 'return false;' ) );

== Screenshots ==

1. e.g. Sites List.

2. Multisite Language Switcher Options.

3. Set "group_key".

4. Widget "SameLangSites".

5. Shortcode "SameLangSites".

6. e.g. English Site.

7. e.g. Japanese Site.

8. Language flags in Admin-Bar.

== Changelog ==

= 0.1 =
* created 2017.01.01.

= 0.2 =
* po/mo files updated.
* Language column on the Table of Sites in network added.
* Language flags in Admin-Bar displayed.
* Malfunction of WPLANG in the setting-general supported.
* Fix. Spelling is corrected from "Gouping" to "Grouping".

= 0.2.1 =
* bug fix.

== Upgrade Notice ==

None
