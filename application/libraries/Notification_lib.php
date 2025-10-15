<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Notification_lib
{
    private $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
    }

    /**
     * Enviar notificación de nuevo pedido
     */
    public function notify_new_order($pedido_id, $tenant_id)
    {
        try {
            // Obtener configuración de notificaciones del tenant
            $this->CI->db->select('notif_email, notif_webhook, notif_whatsapp, nombre');
            $tenant = $this->CI->db->get_where('tenants', ['id' => $tenant_id], 1)->row();

            if (!$tenant) {
                return false;
            }

            // Obtener datos del pedido
            $this->CI->load->model('Pedido_model');
            $pedido = $this->CI->Pedido_model->get_with_items($tenant_id, $pedido_id);

            if (!$pedido) {
                return false;
            }

            $notifications_sent = [];

            // Notificación por email
            if (!empty($tenant->notif_email)) {
                $sent = $this->_send_email_notification($tenant, $pedido);
                $notifications_sent['email'] = $sent;
            }

            // Notificación por webhook
            if (!empty($tenant->notif_webhook)) {
                $sent = $this->_send_webhook_notification($tenant, $pedido);
                $notifications_sent['webhook'] = $sent;
            }

            // Notificación por WhatsApp (si configurado)
            if (!empty($tenant->notif_whatsapp)) {
                $sent = $this->_send_whatsapp_notification($tenant, $pedido);
                $notifications_sent['whatsapp'] = $sent;
            }

            // Guardar log de notificación
            $this->_log_notification($pedido_id, $tenant_id, $notifications_sent);

            return $notifications_sent;

        } catch (Exception $e) {
            log_message('error', 'Error enviando notificación: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar notificación por email
     */
    private function _send_email_notification($tenant, $pedido)
    {
        try {
            $this->CI->load->library('email');

            $config = [
                'protocol' => 'smtp',
                'smtp_host' => 'localhost',
                'smtp_port' => 587,
                'smtp_user' => '',
                'smtp_pass' => '',
                'charset' => 'utf-8',
                'newline' => "\r\n",
                'mailtype' => 'html'
            ];

            $this->CI->email->initialize($config);

            $subject = "🔔 Nuevo Pedido #{$pedido->id} - {$tenant->nombre}";
            $message = $this->_build_email_template($tenant, $pedido);

            $this->CI->email->from('noreply@imenu.app', 'iMenu Notifications');
            $this->CI->email->to($tenant->notif_email);
            $this->CI->email->subject($subject);
            $this->CI->email->message($message);

            return $this->CI->email->send();

        } catch (Exception $e) {
            log_message('error', 'Error email notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar notificación por webhook
     */
    private function _send_webhook_notification($tenant, $pedido)
    {
        try {
            $payload = [
                'event' => 'new_order',
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->nombre,
                'order' => [
                    'id' => $pedido->id,
                    'cliente' => $pedido->nombre_cliente,
                    'telefono' => $pedido->telefono_cliente,
                    'total' => $pedido->total,
                    'metodo_pago' => $pedido->metodo_pago,
                    'estado' => $pedido->estado,
                    'items' => $pedido->items,
                    'created_at' => $pedido->creado_en
                ],
                'timestamp' => date('c')
            ];

            $ch = curl_init($tenant->notif_webhook);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'User-Agent: iMenu-Notifications/1.0'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);

            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            return $http_code >= 200 && $http_code < 300;

        } catch (Exception $e) {
            log_message('error', 'Error webhook notification: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Enviar notificación por WhatsApp (placeholder)
     */
    private function _send_whatsapp_notification($tenant, $pedido)
    {
        // TODO: Implementar integración con WhatsApp API
        // Por ahora retornamos false ya que no está implementado
        log_message('info', 'WhatsApp notification not implemented yet');
        return false;
    }

    /**
     * Construir template de email
     */
    private function _build_email_template($tenant, $pedido)
    {
        $items_html = '';
        foreach ($pedido->items as $item) {
            $items_html .= "<tr>
                <td>{$item->nombre}</td>
                <td>{$item->cantidad}</td>
                <td>\${$item->precio_unit}</td>
                <td>\${$item->subtotal}</td>
            </tr>";
        }

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { background: #28a745; color: white; padding: 20px; text-align: center; }
                .content { padding: 20px; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
                th { background-color: #f2f2f2; }
                .total { font-weight: bold; font-size: 18px; color: #28a745; }
            </style>
        </head>
        <body>
            <div class='header'>
                <h1>🔔 Nuevo Pedido Recibido</h1>
                <p>{$tenant->nombre}</p>
            </div>
            <div class='content'>
                <h2>Pedido #{$pedido->id}</h2>
                <p><strong>Cliente:</strong> {$pedido->nombre_cliente}</p>
                <p><strong>Teléfono:</strong> {$pedido->telefono_cliente}</p>
                <p><strong>Método de Pago:</strong> {$pedido->metodo_pago}</p>
                <p><strong>Fecha:</strong> {$pedido->creado_en}</p>
                
                <h3>Items del Pedido:</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unit.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        {$items_html}
                    </tbody>
                </table>
                
                <p class='total'>Total: \${$pedido->total}</p>
                
                <p>Por favor, confirma la recepción de este pedido y actualiza su estado en el panel de administración.</p>
            </div>
        </body>
        </html>";
    }

    /**
     * Guardar log de notificación
     */
    private function _log_notification($pedido_id, $tenant_id, $notifications_sent)
    {
        $log_data = [
            'pedido_id' => $pedido_id,
            'tenant_id' => $tenant_id,
            'email_sent' => isset($notifications_sent['email']) ? (int)$notifications_sent['email'] : 0,
            'webhook_sent' => isset($notifications_sent['webhook']) ? (int)$notifications_sent['webhook'] : 0,
            'whatsapp_sent' => isset($notifications_sent['whatsapp']) ? (int)$notifications_sent['whatsapp'] : 0,
            'sent_at' => date('Y-m-d H:i:s')
        ];

        $this->CI->db->insert('notification_logs', $log_data);
    }
}
