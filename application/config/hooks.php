<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/userguide3/general/hooks.html
|
*/

/*
| -------------------------------------------------------------------------
| AuthHook - Validación JWT Global
| -------------------------------------------------------------------------
| Se ejecuta antes de que se cargue el controlador para validar
| autenticación JWT. Protege automáticamente todas las rutas excepto
| las públicas definidas en el hook.
*/
$hook['pre_controller'][] = [
	'class'    => 'AuthHook',
	'function' => 'check_access',
	'filename' => 'AuthHook.php',
	'filepath' => 'hooks',
	'params'   => []
];
