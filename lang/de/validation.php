<?php

// Only the messages of the wish form are translated; everything else falls back to English.
// They are not under "custom", which would apply to every validator with these field names:
// StoreWishRequest::messages() reads them from "wishes".
return [

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
        ],
        'example_links' => [
            'array' => 'Bitte die Links als Text eingeben, einen pro Zeile.',
            'max' => 'Höchstens :max Links pro Wunsch.',
        ],
        'example_links.*' => [
            'string' => 'Bitte die Links als Text eingeben, einen pro Zeile.',
            'max' => 'Ein Link darf höchstens :max Zeichen lang sein.',
            'url' => 'Links müssen mit http:// oder https:// beginnen: :input',
        ],
    ],

];
