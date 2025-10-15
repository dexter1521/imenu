<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('resolve_tenant_by_slug')) {
	function resolve_tenant_by_slug($slug)
	{
		$CI = &get_instance();
		$CI->load->database();

		$tenant = $CI->db->get_where('tenants', ['slug' => $slug, 'activo' => 1], 1)->row();
		if (!$tenant) {
			return null;
		}

		return $tenant;
	}
}

if (!function_exists('resolve_tenant_by_subdomain')) {
	function resolve_tenant_by_subdomain($subdomain)
	{
		$CI = &get_instance();
		$CI->load->database();

		$tenant = $CI->db->get_where('tenants', ['subdominio' => $subdomain, 'activo' => 1], 1)->row();
		if (!$tenant) {
			return null;
		}

		return $tenant;
	}
}
