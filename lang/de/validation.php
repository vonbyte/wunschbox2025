<?php

// German lines for the rules the app uses; any other rule falls back to English.
// The wish form has its own messages under "wishes", not under "custom", which would apply
// to every validator with these field names: StoreWishRequest::messages() reads them.
return [

    'confirmed' => ':attribute und Bestätigung stimmen nicht überein.',
    'current_password' => 'Das Passwort ist falsch.',
    'email' => ':attribute muss eine gültige E-Mail-Adresse sein.',
    'in' => 'Der gewählte Wert für :attribute ist ungültig.',
    'integer' => ':attribute muss eine ganze Zahl sein.',
    'lowercase' => ':attribute darf nur Kleinbuchstaben enthalten.',
    'max' => [
        'numeric' => ':attribute darf höchstens :max sein.',
        'string' => ':attribute darf höchstens :max Zeichen lang sein.',
    ],
    'min' => [
        'numeric' => ':attribute muss mindestens :min sein.',
        'string' => ':attribute muss mindestens :min Zeichen lang sein.',
    ],
    'required' => 'Bitte :attribute ausfüllen.',
    'string' => ':attribute muss ein Text sein.',
    'unique' => 'Diese :attribute ist schon vergeben.',

    'attributes' => [
        'current_password' => 'aktuelles Passwort',
        'email' => 'E-Mail',
        'name' => 'Name',
        'package_nr' => 'Päckchen-Nummer',
        'password' => 'Passwort',
        'sortnr' => 'Sortierung',
        'status' => 'Status',
    ],

    'wishes' => [
        'title' => [
            'required' => 'Bitte einen Wunsch eintragen.',
            'string' => 'Bitte einen Wunsch eintragen.',
            'max' => 'Der Wunsch darf höchstens :max Zeichen lang sein.',
        ],
        'receiver' => [
            'required' => 'Bitte auswählen, von wem der Wunsch ist.',
            'in' => 'Bitte auswählen, von wem der Wunsch ist.',
        ],
        'description' => [
            'string' => 'Bitte die Beschreibung als Text eingeben.',
            'max' => 'Die Beschreibung darf höchstens :max Zeichen lang sein.',
        ],
        'image' => [
            'uploaded' => 'Das Bild konnte nicht hochgeladen werden. Vielleicht ist es zu gross?',
            'image' => 'Das Bild muss ein JPG, PNG oder GIF sein.',
            'mimes' => 'Das Bild muss ein JPG, PNG oder GIF sein.',
            'max' => 'Das Bild darf höchstens :megabytes MB gross sein.',
            'pixels' => 'Das Bild hat zu viele Pixel, höchstens :megapixels Megapixel. Bitte nimm ein kleineres Foto.',
            'unreadable' => 'Das Bild konnte nicht gelesen werden. Bitte nimm ein anderes Foto.',
        ],
        'example_links' => [
            'array' => 'Bitte die Links als Text eingeben, einen pro Zeile.',
            'max' => 'Höchstens :max Links pro Wunsch.',
        ],
        // Quick edits on the dashboard, AdminController
        'sortnr' => 'Die Sortierung muss eine ganze Zahl ab 0 sein.',
        'package_nr' => 'Die Päckchen-Nummer muss eine ganze Zahl ab 0 sein.',
        'example_links.*' => [
            'string' => 'Bitte die Links als Text eingeben, einen pro Zeile.',
            'max' => 'Ein Link darf höchstens :max Zeichen lang sein.',
            'url' => 'Links müssen mit http:// oder https:// beginnen: :input',
        ],
    ],

];
