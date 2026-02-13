<?php

return [
    // System Settings
    'systemSettings'       => 'System Settings',
    'general'              => 'General',
    'allowRegistrations'   => 'Allow new user registrations',
    'registrationHelp'     => 'When disabled, only existing users can log in. New accounts can still be created via the CLI.',
    'require2fa'           => 'Require two-factor authentication for all users',
    'require2faHelp'       => 'When enabled, users without 2FA will be redirected to set it up before they can access the application.',
    'saveSettings'         => 'Save Settings',
    'settingsSaved'        => 'Settings saved.',

    // Default Lane Types
    'defaultLaneTypes'     => 'Default Lane Types for New Users',
    'defaultLaneTypesHelp' => 'These lane types will be automatically added when a new user registers.',
    'noDefaultLaneTypes'   => 'No defaults configured. The built-in defaults (25m, 50m, 100m) will be used.',
    'laneTypePlaceholder'  => 'e.g. 25m',

    // Default Sightings
    'defaultSightings'     => 'Default Sighting Options for New Users',
    'defaultSightingsHelp' => 'These sighting options will be automatically added when a new user registers.',
    'noDefaultSightings'   => 'No defaults configured. The built-in defaults (Front Sight, Aperture Sight, Scope) will be used.',
    'sightingPlaceholder'  => 'e.g. Scope',

    // Default management
    'defaultAdded'         => 'Default added.',
    'defaultRemoved'       => 'Default removed.',
    'removeDefaultConfirm' => 'Remove this default?',
    'invalidInput'         => 'Invalid input.',
    'invalidType'          => 'Invalid type.',

    // Users
    'usersTitle'           => 'Users',
    'totalUsers'           => 'Total Users',
    'totalWeapons'         => 'Total Weapons',
    'totalSessions'        => 'Total Sessions',
    'totalLocations'       => 'Total Locations',
    'searchPlaceholder'    => 'Search by username or email...',
    'username'             => 'Username',
    'email'                => 'Email',
    'role'                 => 'Role',
    'twoFa'                => '2FA',
    'joined'               => 'Joined',
    'adminBadge'           => 'Admin',
    'userBadge'            => 'User',
    'twoFaEnabled'         => '2FA enabled',
    'twoFaNotEnabled'      => '2FA not enabled',
    'promoteToAdmin'       => 'Promote to admin',
    'demoteFromAdmin'      => 'Demote from admin',
    'promoteConfirm'       => 'Promote {0} to admin?',
    'demoteConfirm'        => 'Demote {0} from admin?',
    'cannotChangeOwnRole'  => 'You cannot change your own admin status.',
    'userNotFound'         => 'User not found.',
    'userPromoted'         => '{0} has been promoted to admin.',
    'userDemoted'          => '{0} has been demoted from admin.',
    'noUsersFound'         => 'No users found',
    'noUsersMatchSearch'   => 'No users found matching "{0}".',

    // Approval
    'pendingBadge'         => 'Pending',
    'pendingUsers'         => 'Pending Approval',
    'approve'              => 'Approve',
    'reject'               => 'Reject',
    'rejectConfirm'        => 'Reject and delete user {0}? This cannot be undone.',
    'userApproved'         => '{0} has been approved.',
    'userRejected'         => '{0} has been rejected and removed.',
    'cannotRejectSelf'     => 'You cannot reject your own account.',

    // Disable / Enable
    'disabledBadge'        => 'Disabled',
    'enableUser'           => 'Enable user',
    'disableUser'          => 'Disable user',
    'disableConfirm'       => 'Disable user {0}? They will not be able to log in.',
    'userEnabled'          => '{0} has been enabled.',
    'userDisabled'         => '{0} has been disabled.',
    'cannotDisableSelf'    => 'You cannot disable your own account.',
];
