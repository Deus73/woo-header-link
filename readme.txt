=== Woo Header Link ===
Contributors: Deus Dust
Tags: woocommerce, header, link, image, logo
Requires at least: 5.6
Tested up to: 6.7
Requires PHP: 7.2
Stable tag: 1.2.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Zet een klikbare afbeelding of link in de linkerbovenhoek van je (WooCommerce) site. Opent in een nieuw venster.

== Description ==

Woo Header Link voegt een klikbare afbeelding of link toe aan de linkerbovenhoek van je site. Ideaal voor
bijvoorbeeld een banner, logo of terugkerende actie-link.

Kenmerken:

* Kies een afbeelding uit de WordPress-mediabibliotheek of plak een directe afbeeldings-URL.
* Optionele linktekst (als je geen afbeelding gebruikt of tekst naast de afbeelding wilt).
* Opent standaard in een nieuw venster (met `rel="noopener noreferrer"`).
* Drie positioneringen: vast in het scherm, absoluut in de header, of inline.
* Keuze uit de thema-hook waar de link wordt ingevoegd (standaard `wp_body_open`).
* Beperk de weergave tot WooCommerce-pagina's.
* Verberg de link op mobiel.
* Plaats de link ook handmatig met de shortcode `[header_link]` of via de widget.
* Zwevende menuknop met vaste WooCommerce-links (Contact, Winkelwagen, Account, Links, Bobby Helpdesk).
* De zwevende knop is met muis of vinger te verslepen en blijft altijd binnen het scherm.
* WooCommerce-productcategorieën bovenaan het menu (aantal instelbaar).
* De volgorde van de menu-items is vrij te bepalen met een sleepbare lijst in het beheer.
* Extra vaste items vooraan: "GrowSet Configurator" (knippert) en "Kweeksets".

== Installation ==

1. Upload de map `woo-header-link` naar `/wp-content/plugins/`, of installeer de ZIP via Plugins > Nieuwe toevoegen > Plugin uploaden.
2. Activeer de plugin via het menu Plugins.
3. Ga naar Instellingen > Woo Header Link en vul de URL en afbeelding in.

== Frequently Asked Questions ==

= De link verschijnt niet in mijn header. =

Veel thema's gebruiken de hook `wp_body_open`. Werkt dit niet, vul dan de hook van jouw thema in
(bijvoorbeeld `storefront_before_header`, `astra_header_before`) of gebruik de shortcode `[header_link]`
in een HTML-blok of header-widget. Bekijk de `header.php` van je thema voor beschikbare hooks.

= Kan ik de link ook alleen op de shop tonen? =

Ja, vink "Alleen tonen op WooCommerce-pagina's" aan.

== Changelog ==

= 1.2.7 =
* Menu-item "Bobby" heet nu "Bobby Helpdesk".

= 1.2.6 =
* Label gecorrigeerd naar "GrowSet Configurator" (enkelvoud).

= 1.2.5 =
* Nieuw menu-item "GrowSet Configuratoren" helemaal bovenaan, met knipperende animatie.
* Nieuw menu-item "Kweeksets" onder "GrowSet Configuratoren".
* De categorie "Geen categorie" wordt niet meer in het menu getoond.

= 1.2.4 =
* Menu-item "Onze Locatie" weer verwijderd.

= 1.2.3 =
* Menu-item en link gewijzigd van "Onze Lokatie" naar "Onze Locatie", passend bij de paginatitel.

= 1.2.2 =
* "Onze Lokatie" linkt nu naar de echte pagina (op slug of titel), met terugval op /onze-lokatie/ als de pagina niet bestaat.

= 1.2.1 =
* Nieuw menu-item "Onze Lokatie", direct onder "Contact". Nieuwe items schuiven op bestaande installaties automatisch op de juiste plek in.

= 1.2.0 =
* De menu-items (categorieën én vaste links) zijn vrij te ordenen met een sleepbare lijst in het beheer.
* Categorieën volgen de sorteervolgorde uit WooCommerce > Producten > Categorieën.

= 1.1.1 =
* WooCommerce-productcategorieën bovenaan het zwevende menu (aan/uit en maximum instelbaar).
* Opgelost: het menu werkte niet doordat het script vóór de knop-HTML werd geladen; script wacht nu op de DOM.
* Menu wordt bij veel items scrollbaar en past zich aan de beschikbare schermruimte aan.

= 1.1.0 =
* Nieuw: zwevende, versleepbare menuknop met vaste WooCommerce-links (Contact, Winkelwagen, Account, Links, Bobby).
* De knop blijft altijd binnen het scherm en onthoudt de positie per browser.

= 1.0.0 =
* Eerste versie.
