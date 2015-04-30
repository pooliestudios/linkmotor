Installation Aktualisieren
==========================

Um ein Update einer vorhandenen Installation der Linkmotor Community Edition auf
die aktuellste Version durchzuführen, sind folgende Schritte erforderlich:

    git fetch
    git checkout v1.6.0
    php composer.phar install
    app/console doctrine:migrations:migrate
    app/console assetic:dump --env=prod
    app/console cache:clear --env=prod
    
Ggf. ist es noch notwendig die beiden folgenden Verzeichnisse wieder mit
Schreibrechten für den Webserver auszustatten:

    app/cache
    app/logs

Wird der Linkmotor auf einem öffentlich zugänglichen Server installiert, sollte auf jeden Fall ein VHost
eingerichtet werden, der direkt auf `/pfad/zum/linkmotor/web/` zeigt. Alternativ sollte das Verzeichnis
`/pfad/zum/linkmotor/web/` per Symlink eingebunden werden, falls der Linkmotor in ein Unterverzeichnis
installiert werden soll.
Mit dieser Absicherung wird verhindert, dass auf Dateien oberhalb von `/web/` zugegriffen werden kann. Dort
liegen z.B. in `app/config/parameters.yml` ggf. sicherheitskritische Daten.


Cronjobs
--------

Der Linkmotor muss im Hintergrund in regelmäßigen Abständen bestimmte Arbeiten ausführen um z.B. die Aktualität
der Backlinks zu gewährleisten. Aber auch um zusätzliche Informationen zu Domains, Kandidaten, etc. abzuholen.
Dafür sind folgende Kommandos auszuführen - am Besten per cronjob:

    app/console seo:crawl:subdomains
    app/console seo:crawl:pages
    app/console seo:crawl:domains
    app/console seo:crawl:backlinks
    app/console seo:notifications:daily
    app/console seo:imports:process

Wie häufig Subdomains, Kandidaten (Pages), Domains und Backlinks tatsächlich aktualisiert werden, wird in der Datei
`app/config/parameters.yml` festgelegt. Die cronjobs können ruhig stündlich laufen. Sobald es etwas zu tun gibt,
beginnt der Crawler dann seine Arbeit. Das Kommando `app/console seo:notifications:daily` sollte nur einmal pro Tag
ausgeführt werden. Damit werden die Benachrichtigung-Mails verschickt. Das Kommando `app/console seo:imports:process`
wird benötigt, um beim CSV-Import die verschiedenen Schritte auszuführen. Er sollte also häufig laufen (minütlich),
kann aber auch gänzlich abgeschaltet werden, wenn keine Datenimporte gemacht werden.


Fehlersuche
-----------

Sollten im laufenden Betrieb Fehler auftauchen, sollte der erste Blick der Datei `app/logs/prod.log` gelten.
Hier werden alle Applikationsfehler protokolliert. Taucht hier kein Fehler auf, sollte im Error-Log des
Apache nachgesehen werden. Das sind dann meistens Fehler, die grundsätzlich mit der Installation des Webservers
oder PHP auf dem Server zu tun haben.

Dieses Dokument ist noch ein ziemlich früher Entwurf. Aufgrund der Vielzahl der möglichen Konfigurationen
von Webservern und der unterschiedlichen Kenntnisstände bei der Benutzung von Git, Composer und Symfony
sind wir auf euer Feedback angewiesen um dieses Dokument zu verbessern.


Viel Spaß mit dem Linkmotor!
