<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pago_model extends CI_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->load->database();
	}

	/**
	 * Obtener todos los pagos con filtros opcionales
	 */
	public function get_all()
	{
		return $this->db->get('pagos')->result();
	}

	/**
	 * Obtener pagos por tenant_id
	 */
	public function where($field, $value)
	{
		$this->db->where($field, $value);
		return $this;
	}

	/**
	 * Ordenar resultados
	 */
	public function order_by($field, $direction = 'ASC')
	{
		$this->db->order_by($field, $direction);
		return $this;
	}

	/**
	 * Limitar resultados
	 */
	public function limit($limit, $offset = 0)
	{
		$this->db->limit($limit, $offset);
		return $this;
	}

	/**
	 * Ejecutar query chainable
	 */
	public function get_results()
	{
		return $this->db->get('pagos')->result();
	}

	/**
	 * Obtener un solo pago
	 */
	public function get_one()
	{
		return $this->db->get('pagos')->row();
	}

	/**
	 * Obtener pago por ID
	 */
	public function get($id)
	{
		return $this->db->where('id', $id)->get('pagos')->row();
	}

	/**
	 * Obtener pago por ID con información relacionada (tenant, suscripción)
	 */
	public function get_with_relations($id)
	{
		$this->db->select('
			pagos.*,
			tenants.nombre as tenant_nombre,
			tenants.email as tenant_email,
			tenants.slug as tenant_slug,
			tenants.activo as tenant_activo,
			suscripciones.id as suscripcion_id,
			suscripciones.inicio as suscripcion_inicio,
			suscripciones.fin as suscripcion_fin,
			suscripciones.estatus as suscripcion_estatus,
			planes.nombre as plan_nombre,
			planes.precio_mensual as plan_precio
		');
		$this->db->from('pagos');
		$this->db->join('tenants', 'tenants.id = pagos.tenant_id', 'left');
		$this->db->join('suscripciones', 'suscripciones.id = pagos.suscripcion_id', 'left');
		$this->db->join('planes', 'planes.id = suscripciones.plan_id', 'left');
		$this->db->where('pagos.id', $id);
		return $this->db->get()->row();
	}

	/**
	 * Obtener pagos con filtros avanzados y JOIN
	 */
	public function get_with_filters($filters = [])
	{
		$this->db->select('
			pagos.*,
			tenants.nombre as tenant_nombre,
			tenants.slug as tenant_slug
		');
		$this->db->from('pagos');
		$this->db->join('tenants', 'tenants.id = pagos.tenant_id', 'left');

		// Aplicar filtros
		if (!empty($filters['tenant_id'])) {
			$this->db->where('pagos.tenant_id', $filters['tenant_id']);
		}

		if (!empty($filters['status'])) {
			$this->db->where('pagos.status', $filters['status']);
		}

		if (!empty($filters['metodo'])) {
			$this->db->where('pagos.metodo', $filters['metodo']);
		}

		if (!empty($filters['fecha_inicio'])) {
			$this->db->where('pagos.fecha >=', $filters['fecha_inicio']);
		}

		if (!empty($filters['fecha_fin'])) {
			$this->db->where('pagos.fecha <=', $filters['fecha_fin']);
		}

		if (!empty($filters['concepto'])) {
			$this->db->like('pagos.concepto', $filters['concepto']);
		}

		// Ordenar por fecha descendente por defecto
		$this->db->order_by('pagos.fecha', 'DESC');
		$this->db->order_by('pagos.id', 'DESC');

		return $this->db->get()->result();
	}

	/**
	 * Obtener estadísticas de pagos
	 */
	public function get_stats($filters = [])
	{
		// Total de pagos
		$this->db->select('
			COUNT(*) as total_pagos,
			SUM(CASE WHEN status = "pagado" THEN monto ELSE 0 END) as total_ingresos,
			SUM(CASE WHEN status = "pagado" THEN 1 ELSE 0 END) as pagos_exitosos,
			SUM(CASE WHEN status = "pendiente" THEN 1 ELSE 0 END) as pagos_pendientes,
			SUM(CASE WHEN status = "fallido" THEN 1 ELSE 0 END) as pagos_fallidos
		');
		$this->db->from('pagos');

		// Aplicar filtros de fecha si existen
		if (!empty($filters['fecha_inicio'])) {
			$this->db->where('fecha >=', $filters['fecha_inicio']);
		}

		if (!empty($filters['fecha_fin'])) {
			$this->db->where('fecha <=', $filters['fecha_fin']);
		}

		if (!empty($filters['tenant_id'])) {
			$this->db->where('tenant_id', $filters['tenant_id']);
		}

		$result = $this->db->get()->row();

		// Ingresos del mes actual
		$this->db->select('SUM(monto) as ingresos_mes');
		$this->db->from('pagos');
		$this->db->where('status', 'pagado');
		$this->db->where('MONTH(fecha)', date('m'));
		$this->db->where('YEAR(fecha)', date('Y'));
		$ingresos_mes = $this->db->get()->row();

		return [
			'total_pagos' => (int)$result->total_pagos,
			'total_ingresos' => (float)$result->total_ingresos,
			'pagos_exitosos' => (int)$result->pagos_exitosos,
			'pagos_pendientes' => (int)$result->pagos_pendientes,
			'pagos_fallidos' => (int)$result->pagos_fallidos,
			'ingresos_mes' => (float)($ingresos_mes->ingresos_mes ?? 0)
		];
	}

	/**
	 * Insertar nuevo pago
	 */
	public function insert($data)
	{
		$this->db->insert('pagos', $data);
		return $this->db->insert_id();
	}

	/**
	 * Actualizar pago
	 */
	public function update($id, $data)
	{
		$this->db->where('id', $id);
		return $this->db->update('pagos', $data);
	}

	/**
	 * Eliminar pago
	 */
	public function delete($id)
	{
		return $this->db->delete('pagos', ['id' => $id]);
	}

	/**
	 * Obtener ingresos mensuales de los últimos N meses
	 * @param int $months Número de meses (por defecto 12)
	 * @return array
	 */
	public function get_monthly_revenue($months = 12)
	{
		$this->db->select("
			DATE_FORMAT(fecha, '%Y-%m') as mes,
			SUM(CASE WHEN status = 'pagado' THEN monto ELSE 0 END) as ingresos,
			COUNT(CASE WHEN status = 'pagado' THEN 1 END) as pagos_exitosos
		");
		$this->db->where('fecha >=', date('Y-m-d', strtotime("-$months months")));
		$this->db->group_by("DATE_FORMAT(fecha, '%Y-%m')");
		$this->db->order_by('mes', 'ASC');
		return $this->db->get('pagos')->result();
	}

	/**
	 * Obtener ingresos totales del sistema
	 * @return float
	 */
	public function get_total_revenue()
	{
		$this->db->select('SUM(monto) as total');
		$this->db->where('status', 'pagado');
		$result = $this->db->get('pagos')->row();
		return (float)($result->total ?? 0);
	}

	/**
	 * Obtener estadísticas de ingresos para dashboard
	 * @return array
	 */
	public function get_revenue_stats()
	{
		// Ingresos totales
		$total = $this->get_total_revenue();

		// Ingresos del mes actual
		$this->db->select('SUM(monto) as total');
		$this->db->where('status', 'pagado');
		$this->db->where('MONTH(fecha)', date('m'));
		$this->db->where('YEAR(fecha)', date('Y'));
		$mes_actual = $this->db->get('pagos')->row();

		// Ingresos del mes anterior
		$mes_anterior_num = date('m', strtotime('-1 month'));
		$year_anterior = date('Y', strtotime('-1 month'));
		$this->db->select('SUM(monto) as total');
		$this->db->where('status', 'pagado');
		$this->db->where('MONTH(fecha)', $mes_anterior_num);
		$this->db->where('YEAR(fecha)', $year_anterior);
		$mes_anterior = $this->db->get('pagos')->row();

		// Calcular crecimiento
		$ingresos_mes = (float)($mes_actual->total ?? 0);
		$ingresos_mes_ant = (float)($mes_anterior->total ?? 0);
		$crecimiento = 0;
		if ($ingresos_mes_ant > 0) {
			$crecimiento = (($ingresos_mes - $ingresos_mes_ant) / $ingresos_mes_ant) * 100;
		}

		// Promedio diario del mes
		$dia_actual = (int)date('d');
		$promedio_diario = $dia_actual > 0 ? $ingresos_mes / $dia_actual : 0;

		return [
			'total' => $total,
			'mes_actual' => $ingresos_mes,
			'mes_anterior' => $ingresos_mes_ant,
			'crecimiento_porcentaje' => round($crecimiento, 2),
			'promedio_diario' => round($promedio_diario, 2)
		];
	}
}
