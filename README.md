Wordpress Ticketsystem
===============

Inhaltsverzeichnis
------------
+ [Installation](#installation)
+ [Shortcodes](#shortcodes)
+ [Changelog](#changelog)
+ [Autor](#autor)
+ [Copyright und Lizenz](#copyright-und-lizenz)



Installation
------------
Plugin zip-Datei in den Wordpress-Plugin-Ordner entpacken und im Wordpress-Backend aktivieren.

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



Changelog
------------
**scheduled**
+ **1.0.1**
+ [BUG] geschriebener Kommentar erst nach reload sichtbar -> Comment-Object vor db-Input erzeugt
+ Ticketverlinkung in Blog-Kommentaren
+ E-Mail-Input für angemeldete Nutzer ausblenden
+ geschlossene Tickets im Frontend anzeigen
+ **1.1.0**
+ Bootstrap3 in Plugin integrieren als Option
+ Spamschutz (reCaptcha) Option
+ Einstellung Kommentare nur für angemeldete Nutzer sichtbar
+ Fortschritt für Ticket angeben
+ **1.2.0**
+ Shortcode für Tickettypen mit Beschreibung
+ **1.3.0**
+ E-Mail-Benachrichtigung bei neuen Tickets / Kommentaren inkl. Einstellung (Admin)
+ E-Mail-Benachrichtigung bei neuen Kommentaren / Statusänderung (Member)
+ Bearbeiter (Wordpress-User) einstellen inkl. E-Mail-Benachrichtigung
+ Paginator für Ticketliste & Ticketkommentare

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
+ https://www.facebook.com/tkwitkowski



Copyright und Lizenz
------------
Copyright 2014 Tom Witkowski - Lizenz [GPL2](LICENSE).
