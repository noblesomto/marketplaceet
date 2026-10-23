<?php return array (
  'App\\Providers\\EventServiceProvider' => 
  array (
    'Illuminate\\Auth\\Events\\Registered' => 
    array (
      0 => 'Illuminate\\Auth\\Listeners\\SendEmailVerificationNotification',
    ),
    'Spatie\\MediaLibrary\\MediaCollections\\Events\\MediaHasBeenConverted' => 
    array (
      0 => 'App\\Listeners\\DeleteOriginalAfterConversions',
    ),
  ),
);