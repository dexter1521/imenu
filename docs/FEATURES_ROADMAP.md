# 🍽️ iMenu - Features Roadmap & Analysis

**Fecha de análisis**: 16 de octubre de 2025  
**Versión del proyecto**: MVP v1.0  
**Arquitectura**: CodeIgniter 3 + Multi-tenant SaaS  

## 📋 Resumen del Proyecto

**iMenu** es una plataforma SaaS para gestión de menús digitales con arquitectura multi-tenant. El proyecto permite a restaurantes crear menús digitales accesibles vía QR con gestión completa desde panel administrativo.

### 🏗️ Arquitectura Actual
- **Framework**: CodeIgniter 3
- **Base de datos**: MySQL con estructura multi-tenant
- **Frontend**: Bootstrap (SB Admin 2) + JavaScript vanilla
- **Autenticación**: JWT + Sistema de roles granular
- **Estructura**: 
  - Dominio principal: `imenu.com.mx`
  - Subdominios por cliente: `{cliente}.imenu.com.mx`
  - URLs públicas: `imenu.com.mx/r/{slug}`

### 📊 Estado Actual del Desarrollo

#### ✅ **Completado**
- [x] Base de datos multi-tenant con 9 tablas principales
- [x] Sistema de autenticación JWT
- [x] Modelos: Tenant, User, Categoria, Producto, Pedido, Permission, Plan, Suscripcion, Pago
- [x] Controladores: App.php, Auth.php, PublicUser.php, AdminPanel.php
- [x] CRUD completo para categorías y productos
- [x] Sistema de permisos granular por usuario
- [x] API para gestión de pedidos con filtros avanzados
- [x] Generación automática de códigos QR
- [x] Helpers: auth_helper.php, tenant_helper.php
- [x] Librerías: JWT.php, QrGenerator.php, Notification_lib.php
- [x] Instalador automático (install.php)
- [x] Vista pública del menú con integración WhatsApp
- [x] Panel administrativo con dashboard básico

#### 🔄 **En Desarrollo** 
- [ ] Vistas HTML del panel administrativo (parcialmente)
- [ ] Sistema de notificaciones completo
- [ ] Landing page de registro público

#### ❌ **Pendiente**
- [ ] Sistema de subida de imágenes
- [ ] Personalización de temas
- [ ] Analytics y reportes
- [ ] Optimizaciones de rendimiento

---

## 🚀 Features Propuestos

### 🔥 **Nivel 1: Críticos para MVP**

#### 1. **Sistema de Subida de Imágenes**
**Prioridad**: 🔴 Crítica  
**Estimación**: 1-2 semanas  
**Descripción**: Implementar carga de imágenes para productos con optimización automática.

**Funcionalidades**:
- Upload endpoint con validación MIME (JPG, PNG, WebP)
- Redimensionamiento automático (thumbnail 150x150, completo 800x600)
- Almacenamiento organizado: `/uploads/tenants/{tenant_id}/productos/`
- Compresión automática para optimizar peso
- Validación de límites por plan (tamaño y cantidad)
- Integración con formulario de productos existente

**Archivos a modificar**:
- `application/controllers/App.php` (nuevo endpoint upload)
- `application/views/app/productos.php` (agregar input file)
- `assets/js/` (JavaScript para preview y upload)
- `application/config/upload.php` (configuración)

---

#### 2. **Landing Page y Onboarding Público**
**Prioridad**: 🔴 Crítica  
**Estimación**: 1 semana  
**Descripción**: Página de registro público para nuevos restaurantes con proceso automatizado.

**Funcionalidades**:
- Landing page atractiva con precios y features
- Formulario de registro con validación en tiempo real
- Generación automática de tenant + usuario owner
- Creación automática de QR tras registro
- Email de bienvenida con credenciales de acceso
- Período de prueba automático (14 días)

**Archivos a crear**:
- `application/controllers/Public.php` (landing + registro)
- `application/views/public/landing.php`
- `application/views/public/registro.php`
- `application/views/emails/welcome.php`

---

#### 3. **Sistema de Notificaciones Completo**
**Prioridad**: 🟡 Alta  
**Estimación**: 1 semana  
**Descripción**: Notificaciones automáticas por email/webhook cuando llegan nuevos pedidos.

**Funcionalidades**:
- Configuración de email/webhook en panel de ajustes
- Envío automático al crear pedido público
- Templates de email personalizables por tenant
- Logs de notificaciones enviadas/fallidas
- Webhooks con payload estructurado
- Reintento automático en caso de fallo

**Mejoras en**:
- `application/libraries/Notification_lib.php` (completar)
- `application/controllers/App.php` (endpoint config)
- `application/views/app/ajustes.php` (formulario notif)

---

### 🎯 **Nivel 2: Importantes para Crecimiento**

#### 4. **Panel de Análisis y Estadísticas**
**Prioridad**: 🟡 Alta  
**Estimación**: 2 semanas  
**Descripción**: Dashboard con métricas de ventas y análisis de rendimiento.

**Funcionalidades**:
- Gráficos de pedidos por día/semana/mes/año
- Productos más vendidos y menos populares
- Ingresos totales y ticket promedio
- Comparativas período anterior
- Filtros por fechas y categorías
- Exportación de reportes (CSV, PDF)
- Métricas en tiempo real

**Archivos a crear**:
- `application/controllers/Analytics.php`
- `application/models/Analytics_model.php`
- `application/views/app/analytics.php`
- `assets/js/charts.js` (Chart.js integration)

---

#### 5. **Sistema de Temas Personalizables**
**Prioridad**: 🟡 Alta  
**Estimación**: 1.5 semanas  
**Descripción**: Editor visual para personalizar la apariencia del menú público.

**Funcionalidades**:
- Editor de colores primario/secundario/acento
- Subida y gestión de logo personalizado
- Selección de fuentes (Google Fonts)
- Preview en tiempo real del menú
- Plantillas prediseñadas por tipo de negocio
- CSS dinámico generado por tenant

**Mejoras en**:
- `application/views/public/menu.php` (CSS dinámico)
- `application/controllers/App.php` (endpoints tema)
- `application/views/app/temas.php` (nuevo)

---

#### 6. **Gestión de Promociones y Descuentos**
**Prioridad**: 🟠 Media  
**Estimación**: 2 semanas  
**Descripción**: Sistema para crear ofertas especiales y promociones.

**Funcionalidades**:
- Descuentos por porcentaje o monto fijo
- Promociones por tiempo limitado
- Productos destacados en menú público
- Combos y paquetes especiales
- Códigos de descuento únicos
- Límites de uso por promoción
- Analytics de efectividad de promociones

**Nuevas tablas**:
- `promociones` (id, tenant_id, nombre, tipo, valor, activo, inicio, fin)
- `promocion_productos` (id, promocion_id, producto_id)
- `descuentos_aplicados` (id, pedido_id, promocion_id, descuento)

---

### 💎 **Nivel 3: Premium y Diferenciación**

#### 7. **Integración con Plataformas de Delivery**
**Prioridad**: 🟠 Media  
**Estimación**: 3-4 semanas  
**Descripción**: Conexión con servicios de delivery externos y sistema propio.

**Funcionalidades**:
- API para sincronizar con Uber Eats, Rappi, DiDi Food
- Sistema de delivery propio básico
- Cálculo de costos de envío por zona geográfica
- Tracking de pedidos con estados detallados
- Gestión de repartidores (sistema propio)
- Comisiones y reportes por plataforma

---

#### 8. **Sistema de Inventario Inteligente**
**Prioridad**: 🟠 Media  
**Estimación**: 2 semanas  
**Descripción**: Control de stock básico para evitar vender productos agotados.

**Funcionalidades**:
- Campo de stock actual por producto
- Alertas de stock bajo (configurable)
- Desactivación automática cuando stock = 0
- Historial de movimientos de inventario
- Predicción de demanda básica
- Integración con sistema de pedidos

**Nuevas tablas**:
- `inventario` (id, producto_id, stock_actual, stock_minimo, actualizado_en)
- `movimientos_stock` (id, producto_id, tipo, cantidad, motivo, fecha)

---

#### 9. **Progressive Web App (PWA)**
**Prioridad**: 🟢 Baja  
**Estimación**: 2 semanas  
**Descripción**: Convertir menú público en aplicación web progresiva.

**Funcionalidades**:
- Service Worker para funcionamiento offline
- Manifest para instalación en móvil
- Cache inteligente de menús
- Push notifications para nuevas promociones
- Experiencia nativa en móvil
- Sincronización cuando vuelve conexión

**Archivos a crear**:
- `sw.js` (Service Worker)
- `manifest.json` (PWA Manifest)
- `assets/js/pwa.js` (PWA logic)

---

### 🔧 **Nivel 4: Optimizaciones Técnicas**

#### 10. **Sistema de Cache Avanzado**
**Prioridad**: 🟢 Baja  
**Estimación**: 1 semana  
**Descripción**: Implementar cache multicapa para optimizar rendimiento.

**Funcionalidades**:
- Cache de menús públicos (Redis/File cache)
- Cache de consultas de base de datos frecuentes
- Compresión automática de respuestas API
- CDN para assets estáticos (CloudFlare)
- Lazy loading de imágenes
- Minificación automática CSS/JS

---

#### 11. **API Pública para Desarrolladores**
**Prioridad**: 🟢 Baja  
**Estimación**: 2 semanas  
**Descripción**: API REST completa para integraciones externas.

**Funcionalidades**:
- Documentación interactiva (Swagger/OpenAPI)
- Rate limiting por API key
- Webhooks para eventos importantes
- SDKs en JavaScript y PHP
- Sandbox para testing
- Versionado de API (v1, v2...)

---

## 📅 Roadmap Propuesto

### **Fase 1: Completar MVP (4 semanas)**
```
Semana 1: Feature #1 - Sistema de Subida de Imágenes
Semana 2: Feature #2 - Landing Page y Onboarding  
Semana 3: Feature #3 - Sistema de Notificaciones
Semana 4: Testing, bugs y pulimiento general
```

### **Fase 2: Crecimiento (6 semanas)**
```
Semanas 5-6: Feature #4 - Analytics y Estadísticas
Semanas 7-8: Feature #5 - Temas Personalizables  
Semanas 9-10: Feature #6 - Promociones y Descuentos
```

### **Fase 3: Premium (8 semanas)**
```
Semanas 11-14: Feature #7 - Integración Delivery
Semanas 15-16: Feature #8 - Sistema de Inventario
Semanas 17-18: Feature #9 - PWA Implementation
```

### **Fase 4: Optimización (4 semanas)**
```
Semanas 19-20: Feature #10 - Cache Avanzado
Semanas 21-22: Feature #11 - API Pública
```

---

## 💰 Estimación de Inversión

### **Recursos Necesarios**
- **Desarrollador Full-Stack**: 1 persona
- **Designer UX/UI**: 0.5 personas (para temas y landing)
- **QA Tester**: 0.25 personas
- **DevOps**: 0.25 personas (para cache y optimizaciones)

### **Costo Estimado por Fase**
- **Fase 1 (MVP)**: ~80 horas de desarrollo
- **Fase 2 (Crecimiento)**: ~120 horas de desarrollo  
- **Fase 3 (Premium)**: ~160 horas de desarrollo
- **Fase 4 (Optimización)**: ~80 horas de desarrollo

**Total estimado**: 440 horas de desarrollo

---

## 🎯 Métricas de Éxito

### **KPIs Técnicos**
- Tiempo de carga del menú público < 2 segundos
- Uptime del sistema > 99.5%
- Tiempo de respuesta API < 500ms
- Cobertura de tests > 80%

### **KPIs de Negocio**
- Conversión landing → registro > 15%
- Retención de clientes mes 1 > 80%
- Retención de clientes mes 6 > 60%
- Ticket promedio de upgrade a plan Pro > $199 MXN

### **KPIs de Usuario**
- Tiempo promedio de carga de menú < 30 segundos
- Tasa de abandono en proceso de pedido < 25%
- Satisfacción del cliente (NPS) > 8/10

---

## 📝 Notas Técnicas

### **Consideraciones de Escalabilidad**
- Implementar sharding de base de datos cuando > 1000 tenants
- Migrar a microservicios cuando el equipo > 5 desarrolladores
- Considerar migración a Laravel para Fase 3 si el proyecto crece exponencialmente

### **Seguridad**
- Implementar rate limiting en todas las APIs públicas
- Auditoría de seguridad antes de Fase 2
- Backup automático diario de base de datos
- Encriptación de datos sensibles (PII)

### **Monitoreo**
- Implementar logging centralizado (ELK Stack)
- Monitoreo de performance (New Relic/DataDog)
- Alertas automáticas para errores críticos
- Dashboard de métricas de negocio en tiempo real

---

## 🔗 Enlaces Útiles

- **Repositorio**: [GitHub - dexter1521/imenu](https://github.com/dexter1521/imenu)
- **Documentación API**: Ver `API_DOCUMENTATION.md`
- **TODO List**: Ver `TODO.md`
- **Template Admin**: Ver carpeta `startbootstrap-sb-admin-2-gh-pages/`

---

*Última actualización: 16 de octubre de 2025*  
*Preparado por: GitHub Copilot*  
*Versión del documento: 1.0*
