Verwendet wird PHP Version 7.1 TS 64 bit (die für das Projekt angepasst php.ini kann über https://fwuen.de/sprechstundenverwaltung/php.ini bezogen werden)
Folgendes Plugin herunterladen und in den Ordner {php-directory}\ext\ einfügen: https://xdebug.org/files/php_xdebug-2.5.3-7.1-vc14-x86_64.dll --> Der Pfad in Zeile 1892 der php.ini muss eventuell angepasst werden!
Zum Debuggen wird das Chrome-Plugin "XDebug Helper" benötigt (https://chrome.google.com/webstore/detail/xdebug-helper/eadndfjplgieldjbigjakmdgkmoaaaoc)

Nach erstem Pull beachten:

1. Projekt in PHPStorm öffnen
2. Terminal in PHPStorm öffnen (Alt + F12)
3. composer install ausführen (Composer muss vorher natürlich am PC installiert und zu den Umgebungsvariablen hinzugefügt sein)
4. Datei .env.example kopieren, einfügen und die entstehende Datei in .env umbenennen
5. php artisan key:generate ausführen
6. Zum Starten des Servers und Testen der Website php artisan serve ausführen

Datenbankverbindung:

Jeder entwickelt mit Hilfe eines lokalen (!) MySQL-Servers. Hierfür eignet sich beispielsweise XAMPP.