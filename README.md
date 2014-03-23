wp-ticketsystem
===============

Installation
------------
Plugin zip-Datei in den Wordpress-Plugin-Ordner entpacken und im Wordpress-Backend aktivieren.

Eine extra Seite anlegen auf welcher die einzelnen Tickets angezeigt werden sollen, in den Inhaltsbereich folgenden Shortcode eintragen:
```html
[ticket_single/]
```
Diese Seite auf der Plugin-Einstellungsseite auswählen und speichern.

**zur Anzeige des Ticket-Formulars:**
```html
[ticket_form/]
```

**Liste aller nicht abgeschlossenen Tickets:**
```html
[ticket_form/]
```
lässt sich um den Parameter type="" erweitern: bug, task, feature
