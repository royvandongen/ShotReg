<?php

return [
    // System Settings
    'systemSettings'       => 'Systeeminstellingen',
    'general'              => 'Algemeen',
    'allowRegistrations'   => 'Nieuwe gebruikersregistraties toestaan',
    'registrationHelp'     => 'Wanneer uitgeschakeld, kunnen alleen bestaande gebruikers inloggen. Nieuwe accounts kunnen nog steeds via de CLI worden aangemaakt.',
    'require2fa'           => 'Tweefactorauthenticatie vereisen voor alle gebruikers',
    'require2faHelp'       => 'Wanneer ingeschakeld, worden gebruikers zonder 2FA doorgestuurd om het in te stellen voordat ze de applicatie kunnen gebruiken.',
    'saveSettings'         => 'Instellingen opslaan',
    'settingsSaved'        => 'Instellingen opgeslagen.',

    // Default Lane Types
    'defaultLaneTypes'     => 'Standaard baantypes voor nieuwe gebruikers',
    'defaultLaneTypesHelp' => 'Deze baantypes worden automatisch toegevoegd wanneer een nieuwe gebruiker zich registreert.',
    'noDefaultLaneTypes'   => 'Geen standaarden geconfigureerd. De ingebouwde standaarden (25m, 50m, 100m) worden gebruikt.',
    'laneTypePlaceholder'  => 'bijv. 25m',

    // Default Sightings
    'defaultSightings'     => 'Standaard vizieropties voor nieuwe gebruikers',
    'defaultSightingsHelp' => 'Deze vizieropties worden automatisch toegevoegd wanneer een nieuwe gebruiker zich registreert.',
    'noDefaultSightings'   => 'Geen standaarden geconfigureerd. De ingebouwde standaarden (Korrel, Dioptervisier, Richtkijker) worden gebruikt.',
    'sightingPlaceholder'  => 'bijv. Richtkijker',

    // Default management
    'defaultAdded'         => 'Standaard toegevoegd.',
    'defaultRemoved'       => 'Standaard verwijderd.',
    'removeDefaultConfirm' => 'Deze standaard verwijderen?',
    'invalidInput'         => 'Ongeldige invoer.',
    'invalidType'          => 'Ongeldig type.',

    // Users
    'usersTitle'           => 'Gebruikers',
    'totalUsers'           => 'Totaal gebruikers',
    'totalWeapons'         => 'Totaal wapens',
    'totalSessions'        => 'Totaal sessies',
    'totalLocations'       => 'Totaal locaties',
    'searchPlaceholder'    => 'Zoeken op gebruikersnaam of e-mail...',
    'username'             => 'Gebruikersnaam',
    'email'                => 'E-mail',
    'role'                 => 'Rol',
    'twoFa'                => '2FA',
    'joined'               => 'Geregistreerd',
    'adminBadge'           => 'Beheerder',
    'userBadge'            => 'Gebruiker',
    'twoFaEnabled'         => '2FA ingeschakeld',
    'twoFaNotEnabled'      => '2FA niet ingeschakeld',
    'promoteToAdmin'       => 'Promoveren tot beheerder',
    'demoteFromAdmin'      => 'Degraderen van beheerder',
    'promoteConfirm'       => '{0} promoveren tot beheerder?',
    'demoteConfirm'        => '{0} degraderen van beheerder?',
    'cannotChangeOwnRole'  => 'Je kunt je eigen beheerderstatus niet wijzigen.',
    'userNotFound'         => 'Gebruiker niet gevonden.',
    'userPromoted'         => '{0} is gepromoveerd tot beheerder.',
    'userDemoted'          => '{0} is gedegradeerd van beheerder.',
    'noUsersFound'         => 'Geen gebruikers gevonden',
    'noUsersMatchSearch'   => 'Geen gebruikers gevonden die overeenkomen met "{0}".',

    // Approval
    'pendingBadge'         => 'In afwachting',
    'pendingUsers'         => 'Wachtend op goedkeuring',
    'approve'              => 'Goedkeuren',
    'reject'               => 'Afwijzen',
    'rejectConfirm'        => 'Gebruiker {0} afwijzen en verwijderen? Dit kan niet ongedaan worden gemaakt.',
    'userApproved'         => '{0} is goedgekeurd.',
    'userRejected'         => '{0} is afgewezen en verwijderd.',
    'cannotRejectSelf'     => 'Je kunt je eigen account niet afwijzen.',

    // Disable / Enable
    'disabledBadge'        => 'Uitgeschakeld',
    'enableUser'           => 'Gebruiker inschakelen',
    'disableUser'          => 'Gebruiker uitschakelen',
    'disableConfirm'       => 'Gebruiker {0} uitschakelen? Deze kan dan niet meer inloggen.',
    'userEnabled'          => '{0} is ingeschakeld.',
    'userDisabled'         => '{0} is uitgeschakeld.',
    'cannotDisableSelf'    => 'Je kunt je eigen account niet uitschakelen.',
];
