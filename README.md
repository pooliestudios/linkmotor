Linkmotor Community Edition
===========================

Herzlich Willkommen bei der Linkmotor Community Edition. Hierbei handelt
es sich um die selbe Software, die auch auf https://linkmotor.de als
gehostete Version zur Verfügung steht.

In diesem Dokument wird die Installation der selbstgehosteten Community
Edition des Linkmotors beschrieben.

1) Installation der Community Edition vorbereiten
-------------------------------------------------

Für die Installation sollten Grundkenntnisse in Git, Composer, PHP und der Kommandozeile
vorhanden sein. Die Installation sollte auf einem Linux-, oder MacOS-System efolgen.

Folgende Voraussetzungen müssen erfüllt sein:
 Apache
   `mod_rewrite` muss installiert und aktiviert sein
 PHP
   Ab Version 5.3.3, aber *nicht* 5.3.16
   Das Modul `mcrypt` muss installiert und aktiviert sein.

Der Linkmotor basiert auf dem PHP-Framework [Symfony 2.3][1], welches nicht Teil
des Linkmotors ist, aber sehr einfach im Zuge der Installation mit installiert wird.

Damit dies möglich ist, muss zuerst die aktuelle Version des Linkmotors von GitHub
heruntergeladen werden. Die neuste lauffähige Version wird sich immer im Master-Branch
des Projektes befinden. Auf folgendem Weg erhält man also immer die neuste Version:

    git clone https://github.com/pooliestudios/linkmotor.git linkmotor
    git checkout v1.5.1

Anschließend in das neu erstellte Verzeichnis `linkmotor` wechseln und per composer
die benötigten Pakete (in erster Linie Symfony) installieren:

    cd linkmotor
    php composer.phar install

Wenn Composer noch nicht installiert ist, bitte unter http://getcomposer.org/ nachlesen,
wie dies bewerkstelligt wird.

Die Installation der Komponenten kann je nach Netzwerkverbindung etwas dauern. Anschließend
werden einige Einstellungen abgefragt. Die meisten dieser Werte können so übernommen werden.
Die folgenden Werte sollten jedoch an die Gegebenheiten auf dem System, auf das der Linkmotor
installiert werden soll, angepasst werden:

    database_host:     127.0.0.1
    database_port:     ~
    database_name:     linkmotor
    database_user:     root
    database_password: ~

Hierbei handelt es sich um die Zugangsdaten der Datenbank. Die unter `database_name` angegebene
Datenbank muss nicht angelegt werden. Dies passiert gleich in den nächsten Schritten der Installation.
Der User unter `database_user` sollte jedoch über das Recht verfügen, eine Datenbank anlegen zu können.

    mailer_transport:  smtp
    mailer_host:       127.0.0.1
    mailer_user:       ~
    mailer_password:   ~

Dies sind die Zugangsdaten für den Mailserver, über den der Linkmotor Mails verschicken soll.

    router.request_context.host: localhost
    router.request_context.scheme: http
    router.request_context.base_url: ~

Diese Angaben werden benötigt, damit in Mails, die der Linkmotor über cronjobs verschickt,
die korrekte URL zur Installation erzeugen kann. Sollte der Linkmotor unter https://pooliestudios.com/test/linkmotor
installiert sein, müsste in der ersten Zeile `pooliestudios.com` eingetragen werden, `https` in der zweiten und
`/test/linkmotor` in der dritten.

    linkmotor.noreplyAddress: noreply@example.com

Dies ist die Absendeadresse der Mails, die über den Linkmotor verschickt werden.

Alle anderen Angaben können beibehalten werden. Damit die Anbindung an die SEO-Services funktioniert, *muss* die
`seoservices.url` unverändert bleiben.


2) Systemkonfiguration überprüfen
---------------------------------

Bevor wir in der Installation fortfahren, sollte sichergestellt werden, dass
zumindest schon einmal alle Voraussetzungen für den Betrieb von Symfony gegeben sind.

Dazu kann auf der Konsole der folgende Befehl ausgeführt werden:

    php app/check.php


3) Datenbank und Assets installieren
------------------------------------

Zuerst wird die Datenbank erstellt, anschließend die Struktur der Datenbank angelegt und Defaultdaten
aufgespielt:

    app/console doctrine:database:create
    app/console doctrine:migrations:migrate
    app/console doctrine:fixtures:load

Unter den "Assets" versteht man alle CSS- und JS-Dateien, sowie zugehörige Grafiken. Diese werden
über den folgenden Befehl installiert:

    app/console assetic:dump --env=prod


4) Adminuser anlegen
--------------------

Aus Sicherheitsgründen kommt die Installation ohne einen User daher.
Der erste Adminuser muss über die Kommandozeile angelegt werden:

    app/console seo:admin:create

Es werden nun E-Mail-Adresse, Benutzername und Passwort abgefragt. Auf der Konsole
sollte auf die Zeichen !, $, ", <, > und & im Passwort verzichtet werden. Über das Webinterface
können anschließend beliebige Passwörter verwendet werden.


5) Abschluss der Installation
-----------------------------

Die Installation ist nun abgeschlossen, der Linkmotor kann unter

    http://servername/pfad/zum/linkmotor/web/

aufgerufen werden. Ggf. ist es noch notwendig die beiden folgenden Verzeichnisse mit
Schreibrechten für den Webserver auszustatten:
    app/cache
    app/logs

Wird der Linkmotor auf einem öffentlich zugänglichen Server installiert, sollte auf jeden Fall ein VHost
eingerichtet werden, der direkt auf `/pfad/zum/linkmotor/web/` zeigt. Alternativ sollte das Verzeichnis
`/pfad/zum/linkmotor/web/` per Symlink eingebunden werden, falls der Linkmotor in ein Unterverzeichnis
installiert werden soll.
Mit dieser Absicherung wird verhindert, dass auf Dateien oberhalb von `/web/` zugegriffen werden kann. Dort
liegen z.B. in `app/config/parameters.yml` ggf. sicherheitskritische Daten.


6) Cronjobs
-----------

Der Linkmotor muss im Hintergrund in regelmäßigen Abständen bestimmte Arbeiten ausführen um z.B. die Aktualität
der Backlinks zu gewährleisten. Aber auch um zusätzliche Informationen zu Domains, Kandidaten, etc. abzuholen.
Dafür sind folgende Kommandos auszuführen - am Besten per cronjob:
    `app/console seo:crawl:subdomains`
    `app/console seo:crawl:pages`
    `app/console seo:crawl:domains`
    `app/console seo:crawl:backlinks`
    `app/console seo:notifications:daily`
    `app/console seo:imports:process`

Wie häufig Subdomains, Kandidaten (Pages), Domains und Backlinks tatsächlich aktualisiert werden, wird in der Datei
`app/config/parameters.yml` festgelegt. Die cronjobs können ruhig stündlich laufen. Sobald es etwas zu tun gibt,
beginnt der Crawler dann seine Arbeit. Das Kommando `app/console seo:notifications:daily` sollte nur einmal pro Tag
ausgeführt werden. Damit werden die Benachrichtigung-Mails verschickt. Das Kommando `app/console seo:imports:process`
wird benötigt, um beim CSV-Import die verschiedenen Schritte auszuführen. Er sollte also häufig laufen (minütlich),
kann aber auch gänzlich abgeschaltet werden, wenn keine Datenimporte gemacht werden.


7) Fehlersuche
--------------

Sollten im laufenden Betrieb Fehler auftauchen, sollte der erste Blick der Datei `app/logs/prod.log` gelten.
Hier werden alle Applikationsfehler protokolliert. Taucht hier kein Fehler auf, sollte im Error-Log des
Apache nachgesehen werden. Das sind dann meistens Fehler, die grundsätzlich mit der Installation des Webservers
oder PHP auf dem Server zu tun haben.


Dieses Dokument ist noch ein ziemlich früher Entwurf. Aufgrund der Vielzahl der möglichen Konfigurationen
von Webservern und der unterschiedlichen Kenntnisstände bei der Benutzung von Git, Composer und Symfony
sind wir auf euer Feedback angewiesen um dieses Dokument zu verbessern.


Viel Spaß mit dem Linkmotor!

[1]:  http://symfony.com/
