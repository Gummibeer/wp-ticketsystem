Wordpress Ticketsystem
===============

Inhaltsverzeichnis
------------
+ [Installation](#installation)
+ [Shortcodes](#shortcodes)
+ [verwendete Software](#verwendete-software)
+ [geplante Funktionen](#geplante-funktionen)
+ [Changelog](#changelog)
+ [Autor](#autor)
+ [Copyright und Lizenz](#copyright-und-lizenz)



Installation
------------
Plugin zip-Datei in den Wordpress-Plugin-Ordner entpacken, der Ordner muss `wp_ticketsystem` heißen. Danach das Plugin im Wordpress-Backend aktivieren.

Eine extra Seite anlegen auf welcher die einzelnen Tickets angezeigt werden sollen, in den Inhaltsbereich folgenden Shortcode eintragen:
```html
[wp_ticketsystem_single /]
```
Diese Seite auf der Plugin-Einstellungsseite auswählen und speichern.



Shortcodes
------------
+ **Ticketformular:** `[wp_ticketsystem_form excl="{TypeID}" /]`
+ **Ticketliste offener Tickets:** `[wp_ticketsystem_list excl="{TypeID}" /]`
+ **Einzelticket:** `[wp_ticketsystem_single /]`
+ **Ticketverlinkung:** `@#{TicketID}` (in Seiten, Blog-Beiträgen, Tickets, Ticket-Kommentaren & bbPress-Foren-Beiträgen)
+ **Ticketverlinkung** in Themes oder Plugins durch php
```php
<?php
global $wp_ticketsystem;
echo $wp_ticketsystem->filter_content( '{Text} @#{TicketID} {Text}' );
?>
```



verwendete Software
------------
+ [Bootstrap3](https://github.com/twbs/bootstrap)
+ [Google Charts](https://developers.google.com/chart)
+ [Wordpress](https://wordpress.org)
+ **geplant**
+ [reCaptcha](http://www.google.com/recaptcha)



geplante Funktionen
------------
**1.1.0**
+ E-Mail-Input für angemeldete Nutzer ausblenden
+ geschlossene Tickets im Frontend anzeigen

**1.2.0**
+ [Bootstrap3](https://github.com/twbs/bootstrap) in Plugin integrieren als Option
+ Spamschutz ([reCaptcha](http://www.google.com/recaptcha)) Option
+ Einstellung Ticket-Formular / Ticket-Kommentare nur für angemeldete Nutzer sichtbar

**1.3.0**
+ Priorität für Tickets festlegen
+ Fortschritt für Ticket angeben -> anhand von Ticketstatus

**1.4.0**
+ Shortcode für Tickettypen mit Beschreibung

**1.5.0**
+ E-Mail-Benachrichtigung bei neuen Tickets / Kommentaren inkl. Einstellung (Admin)
+ E-Mail-Benachrichtigung bei neuen Kommentaren / Statusänderung (Member)
+ Bearbeiter (Wordpress-User) einstellen inkl. E-Mail-Benachrichtigung
+ Paginator für Ticketliste & Ticketkommentare

**1.6.0**
+ custom-CSS ermöglichen -> FE-inline-styles entfernen, CSS-Klassen in CSS generieren, vorausfüllen



Changelog
------------
**1.0.1 beta**
+ [BUG] geschriebener Kommentar erst nach reload sichtbar -> Comment-Object vor db-Input erzeugt
+ Ticketverlinkung in Blog-Kommentaren
+ Ticketverlinkung in Themes & Plugins (php-Funktion)

**1.0.0 stable**
+ eigene Tickettypen erstellen
+ Tickettypen an- und abschalten
+ Ticket Sidebar-Widget
+ neue Einstellungsmöglichkeiten im Backend
+ Tickets bearbeiten
+ Duplikate zusammenführen
+ Kommentare zu extra Tickets machen
+ Dashboard-Widget vereinfacht
+ Shortcodes angepasst
+ Einstellungsmöglichkeiten für Shortcodes

**0.5.0 alpha**
+ Shortcodes für Ticketformular, Ticketliste, Ticketeinzelansicht
+ Einstellungsseite & Ticketübersichtsseiten
+ Dashboard-Widgets
+ Ticketverlinkung in Seiten, Beiträgen, Tickets, Ticketkommentaren & bbPress-Foren-Beiträgen



Autor
------------
**Tom Witkowski**
+ https://github.com/Gummibeer
+ https://facebook.com/dev.gummibeer
+ https://facebook.com/tkwitkowski
+ https://plus.google.com/101844596511345872985
+ https://plus.google.com/+TomKayWitkowski



Copyright und Lizenz
------------
Copyright 2014 Tom Witkowski - Lizenz [GPL2](https://github.com/Gummibeer/wp-ticketsystem/blob/master/LICENSE.txt).
