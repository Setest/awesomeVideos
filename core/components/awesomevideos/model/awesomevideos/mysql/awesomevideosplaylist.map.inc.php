<?php
$xpdo_meta_map['awesomeVideosPlaylist']= array (
  'package' => 'awesomevideos',
  'version' => '1.1',
  'table' => 'awesomevideos_playlists',
  'extends' => 'xPDOSimpleObject',
  'fields' => 
  array (
    'active' => 0,
    'channel' => '',
    'channelId' => '',
    'user' => '',
    'playlist' => '',
    'playlistId' => '',
  ),
  'fieldMeta' => 
  array (
    'active' => 
    array (
      'dbtype' => 'tinyint',
      'precision' => '1',
      'phptype' => 'boolean',
      'null' => true,
      'default' => 0,
    ),
    'channel' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'channelId' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'user' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'playlist' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
    'playlistId' => 
    array (
      'dbtype' => 'varchar',
      'precision' => '255',
      'phptype' => 'string',
      'null' => false,
      'default' => '',
    ),
  ),
);
