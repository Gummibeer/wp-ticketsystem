Wordpress Ticketsystem
===============

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
**2.0 scheduled**
+ geschlossene Tickets im Frontend anzeigen
+ Ticketverlinkung in Blog-Kommentaren
+ E-Mail-Benachrichtigung bei neuen Tickets / Kommentaren inkl. Einstellung
+ Bearbeiter (Wordpress-User) einstellen inkl. E-Mail-Benachrichtigung
+ Fortschritt für Ticket angeben
+ Shortcode für Tickettypen mit Beschreibung
+ E-Mail-Input für angemeldete Nutzer ausblenden
+ Einstellung Kommentare nur für angemeldete Nutzer sichtbar
+ [BUG] geschriebener Kommentar erst nach reload sichtbar
-> Comment-Object vor db-Input erzeugt

**1.0 stable**
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

**0.5 alpha**
+ Shortcodes für Ticketformular, Ticketliste, Ticketeinzelansicht
+ Einstellungsseite & Ticketübersichtsseiten
+ Dashboard-Widgets
+ Ticketverlinkung in Seiten, Beiträgen, Tickets, Ticketkommentaren & bbPress-Foren-Beiträgen
