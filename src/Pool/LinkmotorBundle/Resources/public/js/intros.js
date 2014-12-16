var poolIntros = {
    pool_linkmotor_project_dashboard: {
        autostart: true,
        steps: [{
                intro: "<img src='//linkmotor.de/img/team/felix.jpg' alt'Felix' style='height: 80px; float: right; border-radius: 50px;'> Hi <strong>"+poolUserDisplayName+"</strong>,<br>mein Name ist Felix.<br>Ich erkläre dir <strong>ganz kurz</strong> das Wichtigste, danach kannst du sofort loslegen."
            }, {
                element: '.uk-navbar',
                intro: "Hier in der Leiste kannst du innerhalb eines Projektes navigieren.",
                position: 'bottom'
            }, {
                element: '#navi-dashboard',
                intro: "Das Dashboard zeigt dir einen Überblick über das aktuelle Projekt und deine anstehenden Aufgaben."
            }, {
                element: '#navi-pages',
                intro: "Unter Kandidaten findest du eine Auflistung aller gespeicherten und vorgeschlagenen Seiten."
            }, {
                element: '#navi-backlinks',
                intro: "Hier findest du die angelegten und überprüften Backlinks eines Projektes."
            }, {
                element: '#navi-domains',
                intro: "Eine einfache Übersicht aller unter Kandidaten und Backlinks verwendeten Domains."
            }, {
                element: '#navi-vendors',
                intro: "Jedem Kandidat/Backlink kann ein Kontakt zugeordnet werden. Hier findet man sie alle."
            }, {
                onlyForAdmin: true,
                element: '#navi-explorer',
                intro: "Mit dem Exporer kann man neue Kandidaten zu bestimmten Keywords oder Wettbewerbern automatisch recherchieren."
            }, {
                element: '#step5',
                intro: '<strong>Das war\'s auch schon.</strong><br><br>Falls du noch Fragen haben solltest, schick uns eine E-Mail an <a href="mailto:support@linkmotor.de">support@linkmotor.de</a> oder klingel einfach durch: <strong>0221 6306 111 36</strong><br><br>Viel Erfolg beim Linkaufbau!'
            }
        ]
    },
    dummy: {
        steps: []
    }
};
