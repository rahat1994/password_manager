<?php
// password manger routes
// folder routes
$app->get('folder', 'FolderController@index');
$app->post('folder', 'FolderController@store');

// item routes
$app->get('item', 'ItemController@index');
$app->post('item', 'ItemController@store');
$app->post('item/update', 'ItemController@update');
$app->post('item/delete', 'ItemController@delete');
$app->post('item/bulk-move', 'ItemController@bulkMove');

// user routes
$app->post('validate-master-password', 'ItemController@validateMasterPassword');
