<?php

declare(strict_types=1);

return [

    'navigation' => [
        'label' => 'Openingstijden',
    ],

    'page' => [
        'title' => 'Openingstijden',
        'heading' => 'Openingstijden',
        'subheading' => 'Beheer de wekelijkse openingstijden, uitzonderlijke sluitingen en vakantieperiodes.',
    ],

    'actions' => [
        'save' => 'Opslaan',
    ],

    'days' => [
        'monday' => 'Maandag',
        'tuesday' => 'Dinsdag',
        'wednesday' => 'Woensdag',
        'thursday' => 'Donderdag',
        'friday' => 'Vrijdag',
        'saturday' => 'Zaterdag',
        'sunday' => 'Zondag',
    ],

    'grid' => [
        'heading' => 'Wekelijkse uren',
        'description' => 'Tijdzone: :timezone. Het rooster rondt af op :step minuten.',
        'hint' => 'Sleep over een dag om een tijdsblok te maken, sleep een blok om het te verplaatsen, sleep de randen om het te vergroten of verkleinen. Klik op een blok (of klik met de rechtermuisknop, of druk op Enter) om precieze tijden in te typen of het te verwijderen.',
        'closed' => 'Gesloten',
        'overnight' => '(+1)',
        'total_hours' => ':hours u',
        'total_hours_minutes' => ':hours u :min',
        'add_range' => 'Tijdsblok toevoegen',
        'copy_day' => 'Deze dag kopiëren naar…',
        'copy_day_heading' => 'Uren van ":day" kopiëren naar…',
        'copy' => 'Kopiëren',
        'clear_day' => 'Deze dag wissen',
        'editor' => [
            'edit' => 'Tijdsblok bewerken',
            'from' => 'Van',
            'until' => 'Tot',
            'delete' => 'Verwijderen',
            'done' => 'Klaar',
            'invalid' => 'Het einde moet na het begin liggen.',
        ],
    ],

    'settings' => [
        'label' => 'Instellingen',
        'heading' => 'Instellingen openingstijden',
        'description' => 'Deze instellingen worden toegepast bij het opslaan.',
        'apply' => 'Toepassen',
        'timezone' => 'Tijdzone',
        'timezone_placeholder' => 'Tijdzone van de applicatie (:timezone)',
        'timezone_helper' => 'De tijdzone waarin de openingstijden uitgedrukt worden.',
        'overflow' => 'Tijdsblokken na middernacht',
        'overflow_helper' => 'Sta tijdsblokken toe die na middernacht eindigen, zoals 20:00 - 02:00. Deze kunnen enkel via de tijdsblok-editor ingevoerd worden.',
    ],

    'exceptions' => [
        'heading' => 'Uitzonderlijke data',
        'description' => 'Dagen waarop de zaak uitzonderlijk gesloten is, of open met bijzondere uren.',
        'empty' => 'Nog geen uitzonderlijke data.',
        'add' => 'Datum toevoegen',
        'add_heading' => 'Uitzonderlijke datum toevoegen',
        'edit' => 'Bewerken',
        'edit_heading' => 'Uitzonderlijke datum bewerken',
        'remove' => 'Verwijderen',
        'remove_heading' => 'Deze uitzonderlijke datum verwijderen?',
        'recurring_badge' => 'Elk jaar',
        'closed_badge' => 'Gesloten',
        'form' => [
            'date' => 'Datum',
            'recurring' => 'Herhaalt zich elk jaar',
            'recurring_helper' => 'Het jaar van de gekozen datum wordt genegeerd.',
            'closed' => 'Hele dag gesloten',
            'hours' => 'Openingstijden op die dag',
            'add_range' => 'Tijdsblok toevoegen',
            'from' => 'Van',
            'until' => 'Tot',
            'label' => 'Label',
            'label_placeholder' => 'Kerstmis, inventaris, …',
        ],
    ],

    'vacations' => [
        'heading' => 'Vakantieperiodes',
        'description' => 'Datums waarbinnen de zaak gesloten is.',
        'empty' => 'Nog geen vakantieperiode.',
        'add' => 'Periode toevoegen',
        'add_heading' => 'Vakantieperiode toevoegen',
        'edit' => 'Bewerken',
        'edit_heading' => 'Vakantieperiode bewerken',
        'remove' => 'Verwijderen',
        'remove_heading' => 'Deze vakantieperiode verwijderen?',
        'range' => ':from → :until',
        'days_count' => '{1} :count dag|[2,*] :count dagen',
        'fallback_label' => 'Vakantie',
        'form' => [
            'from' => 'Eerste gesloten dag',
            'until' => 'Laatste gesloten dag',
            'label' => 'Label',
            'label_placeholder' => 'Zomervakantie, …',
        ],
    ],

    'save' => [
        'success_title' => 'Openingstijden opgeslagen',
        'invalid_title' => 'De uren konden niet opgeslagen worden',
        'conflicting_dates' => ':date komt twee keer voor (door ":first" en ":second"). Verwijder er één.',
        'invalid_period' => 'Een vakantieperiode heeft een ongeldige datum.',
        'period_too_long' => 'Een vakantieperiode mag niet langer dan :max dagen zijn.',
        'too_many_entries' => 'Te veel uitzonderlijke data en vakantieperiodes (maximaal :max in totaal).',
        'too_many_ranges' => 'Te veel tijdsblokken op :day (maximaal :max).',
        'invalid_timezone' => '":timezone" is geen geldige tijdzone.',
    ],

];
