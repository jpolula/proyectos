<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API de Reservas de Restaurantes - Documentación</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .code-block {
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 1rem;
            margin: 1rem 0;
            font-family: monospace;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .method-get { color: #2563eb; }
        .method-post { color: #16a34a; }
        .method-put { color: #ca8a04; }
        .method-delete { color: #dc2626; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <header class="bg-blue-600 text-white rounded-lg shadow-lg p-6 mb-8">
            <h1 class="text-3xl font-bold">API de Reservas de Restaurantes</h1>
            <p class="mt-2">Documentación para integración con n8n</p>
            <p class="mt-4 text-sm bg-blue-700 p-2 rounded">Versión: <?php echo API_VERSION; ?></p>
        </header>

        <main class="mb-8">
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Introducción</h2>
                <p class="mb-4">
                    Esta API proporciona acceso a los datos del sistema de reservas de restaurantes, permitiendo la integración con n8n para automatizar flujos de trabajo.
                </p>
                <p class="mb-4">
                    n8n es una herramienta de automatización de flujos de trabajo que te permite conectar diferentes servicios y aplicaciones. Con esta API, podrás:
                </p>
                <ul class="list-disc pl-6 mb-4">
                    <li>Obtener información sobre reservas, clientes y disponibilidad</li>
                    <li>Crear nuevas reservas automáticamente</li>
                    <li>Actualizar o cancelar reservas existentes</li>
                    <li>Obtener estadísticas sobre la ocupación del restaurante</li>
                    <li>Automatizar notificaciones y recordatorios</li>
                </ul>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Autenticación</h2>
                <p class="mb-4">
                    Todas las solicitudes a la API (excepto la documentación) requieren autenticación mediante un token de API.
                    El token debe incluirse en el encabezado <code>Authorization</code> de cada solicitud.
                </p>
                <div class="code-block">
// Ejemplo de encabezado de autorización
Authorization: Bearer <?php echo API_TOKEN; ?></div>
                <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 my-4">
                    <p class="font-bold">Importante</p>
                    <p>Mantén tu token de API seguro y no lo compartas públicamente.</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Configuración en n8n</h2>
                <p class="mb-4">
                    Para utilizar esta API con n8n, sigue estos pasos:
                </p>
                <ol class="list-decimal pl-6 mb-4">
                    <li class="mb-2">
                        <strong>Añade un nodo HTTP Request</strong>: En n8n, añade un nodo "HTTP Request" a tu flujo de trabajo.
                    </li>
                    <li class="mb-2">
                        <strong>Configura la URL</strong>: Utiliza la URL base de la API seguida del endpoint deseado.
                        <div class="code-block">http://localhost/reservar%20restaurantes/api/reservas</div>
                    </li>
                    <li class="mb-2">
                        <strong>Configura el método HTTP</strong>: Selecciona el método adecuado (GET, POST, PUT, DELETE).
                    </li>
                    <li class="mb-2">
                        <strong>Añade el encabezado de autorización</strong>: En la sección "Headers", añade:
                        <div class="code-block">Authorization: Bearer <?php echo API_TOKEN; ?></div>
                    </li>
                    <li class="mb-2">
                        <strong>Configura los parámetros o el cuerpo de la solicitud</strong>: Según el endpoint y el método.
                    </li>
                </ol>
                <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 my-4">
                    <p class="font-bold">Consejo</p>
                    <p>Puedes utilizar variables dinámicas en n8n para personalizar tus solicitudes.</p>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Endpoints de la API</h2>
                
                <!-- Endpoint: Reservas -->
                <div class="mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Reservas</h3>
                    <p class="mb-4">Gestiona las reservas del restaurante.</p>
                    
                    <div class="border-l-4 border-blue-500 pl-4 mb-4">
                        <p class="method-get font-bold">GET /api/reservas</p>
                        <p class="text-sm text-gray-600">Obtiene todas las reservas con filtros opcionales.</p>
                        <p class="text-sm text-gray-600 mt-2">Parámetros de consulta:</p>
                        <ul class="list-disc pl-6 text-sm text-gray-600">
                            <li>fecha: Filtra por fecha (formato: YYYY-MM-DD)</li>
                            <li>estado: Filtra por estado (confirmada, pendiente, rechazada)</li>
                            <li>zona: Filtra por zona (dentro, fuera)</li>
                            <li>turno_id: Filtra por ID de turno</li>
                            <li>limit: Limita el número de resultados (por defecto: 100)</li>
                        </ul>
                    </div>
                    
                    <div class="border-l-4 border-blue-500 pl-4 mb-4">
                        <p class="method-get font-bold">GET /api/reservas/{id}</p>
                        <p class="text-sm text-gray-600">Obtiene una reserva específica por su ID.</p>
                    </div>
                    
                    <div class="border-l-4 border-green-500 pl-4 mb-4">
                        <p class="method-post font-bold">POST /api/reservas</p>
                        <p class="text-sm text-gray-600">Crea una nueva reserva.</p>
                        <p class="text-sm text-gray-600 mt-2">Cuerpo de la solicitud (JSON):</p>
                        <div class="code-block text-sm">
{
  "nombre": "Nombre del cliente",
  "email": "cliente@ejemplo.com",
  "telefono": "123456789",
  "fecha": "2025-05-15",
  "turno_id": 1,
  "zona": "dentro",
  "hora": "14:00",
  "cantidad_personas": 4,
  "observaciones": "Celebración de cumpleaños",
  "necesidades_especiales": "Mesa alejada de la cocina",
  "tiene_alergenos": true
}</div>
                    </div>
                    
                    <div class="border-l-4 border-yellow-500 pl-4 mb-4">
                        <p class="method-put font-bold">PUT /api/reservas/{id}</p>
                        <p class="text-sm text-gray-600">Actualiza una reserva existente.</p>
                        <p class="text-sm text-gray-600 mt-2">Cuerpo de la solicitud (JSON):</p>
                        <div class="code-block text-sm">
{
  "fecha": "2025-05-16",
  "hora": "14:30",
  "cantidad_personas": 5,
  "estado": "confirmada"
}</div>
                    </div>
                    
                    <div class="border-l-4 border-red-500 pl-4 mb-4">
                        <p class="method-delete font-bold">DELETE /api/reservas/{id}</p>
                        <p class="text-sm text-gray-600">Elimina una reserva existente.</p>
                    </div>
                </div>
                
                <!-- Endpoint: Clientes -->
                <div class="mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Clientes</h3>
                    <p class="mb-4">Gestiona los clientes del restaurante.</p>
                    
                    <div class="border-l-4 border-blue-500 pl-4 mb-4">
                        <p class="method-get font-bold">GET /api/clientes</p>
                        <p class="text-sm text-gray-600">Obtiene todos los clientes con filtros opcionales.</p>
                        <p class="text-sm text-gray-600 mt-2">Parámetros de consulta:</p>
                        <ul class="list-disc pl-6 text-sm text-gray-600">
                            <li>nombre: Filtra por nombre (búsqueda parcial)</li>
                            <li>email: Filtra por email (búsqueda parcial)</li>
                            <li>telefono: Filtra por teléfono (búsqueda parcial)</li>
                            <li>limit: Limita el número de resultados (por defecto: 100)</li>
                        </ul>
                    </div>
                    
                    <div class="border-l-4 border-blue-500 pl-4 mb-4">
                        <p class="method-get font-bold">GET /api/clientes/{id}</p>
                        <p class="text-sm text-gray-600">Obtiene un cliente específico por su ID, incluyendo sus últimas reservas.</p>
                    </div>
                    
                    <div class="border-l-4 border-green-500 pl-4 mb-4">
                        <p class="method-post font-bold">POST /api/clientes</p>
                        <p class="text-sm text-gray-600">Crea un nuevo cliente.</p>
                        <p class="text-sm text-gray-600 mt-2">Cuerpo de la solicitud (JSON):</p>
                        <div class="code-block text-sm">
{
  "nombre": "Nombre del cliente",
  "email": "cliente@ejemplo.com",
  "telefono": "123456789"
}</div>
                    </div>
                    
                    <div class="border-l-4 border-yellow-500 pl-4 mb-4">
                        <p class="method-put font-bold">PUT /api/clientes/{id}</p>
                        <p class="text-sm text-gray-600">Actualiza un cliente existente.</p>
                        <p class="text-sm text-gray-600 mt-2">Cuerpo de la solicitud (JSON):</p>
                        <div class="code-block text-sm">
{
  "nombre": "Nuevo nombre",
  "telefono": "987654321"
}</div>
                    </div>
                    
                    <div class="border-l-4 border-red-500 pl-4 mb-4">
                        <p class="method-delete font-bold">DELETE /api/clientes/{id}</p>
                        <p class="text-sm text-gray-600">Elimina un cliente existente (solo si no tiene reservas asociadas).</p>
                    </div>
                </div>
                
                <!-- Endpoint: Disponibilidad -->
                <div class="mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Disponibilidad</h3>
                    <p class="mb-4">Consulta la disponibilidad para reservas.</p>
                    
                    <div class="border-l-4 border-blue-500 pl-4 mb-4">
                        <p class="method-get font-bold">GET /api/disponibilidad</p>
                        <p class="text-sm text-gray-600">Obtiene información general de disponibilidad (rango de fechas, turnos, zonas, configuración).</p>
                    </div>
                    
                    <div class="border-l-4 border-blue-500 pl-4 mb-4">
                        <p class="method-get font-bold">GET /api/disponibilidad?fecha=2025-05-15&turno_id=1&zona=dentro&num_personas=4</p>
                        <p class="text-sm text-gray-600">Verifica la disponibilidad para una fecha, turno, zona y número de personas específicos.</p>
                        <p class="text-sm text-gray-600 mt-2">Parámetros de consulta:</p>
                        <ul class="list-disc pl-6 text-sm text-gray-600">
                            <li>fecha: Fecha a verificar (formato: YYYY-MM-DD)</li>
                            <li>turno_id: ID del turno</li>
                            <li>zona: Zona (dentro, fuera)</li>
                            <li>num_personas: Número de personas (por defecto: 1)</li>
                        </ul>
                    </div>
                    
                    <div class="border-l-4 border-blue-500 pl-4 mb-4">
                        <p class="method-get font-bold">GET /api/disponibilidad?mes=5&anio=2025</p>
                        <p class="text-sm text-gray-600">Obtiene los días disponibles para un mes y año específicos.</p>
                        <p class="text-sm text-gray-600 mt-2">Parámetros de consulta:</p>
                        <ul class="list-disc pl-6 text-sm text-gray-600">
                            <li>mes: Número de mes (1-12)</li>
                            <li>anio: Año (2023-2030)</li>
                        </ul>
                    </div>
                </div>
                
                <!-- Endpoint: Estadísticas -->
                <div class="mb-8">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Estadísticas</h3>
                    <p class="mb-4">Obtiene estadísticas sobre las reservas.</p>
                    
                    <div class="border-l-4 border-blue-500 pl-4 mb-4">
                        <p class="method-get font-bold">GET /api/estadisticas</p>
                        <p class="text-sm text-gray-600">Obtiene estadísticas generales sobre las reservas.</p>
                        <p class="text-sm text-gray-600 mt-2">Parámetros de consulta:</p>
                        <ul class="list-disc pl-6 text-sm text-gray-600">
                            <li>fecha_inicio: Fecha de inicio del periodo (formato: YYYY-MM-DD, por defecto: 30 días atrás)</li>
                            <li>fecha_fin: Fecha de fin del periodo (formato: YYYY-MM-DD, por defecto: hoy)</li>
                            <li>detalle_diario: Si se incluye el detalle diario (true/false, por defecto: false)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h2 class="text-2xl font-semibold text-gray-800 mb-4">Ejemplos de Flujos de Trabajo en n8n</h2>
                
                <div class="mb-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">1. Envío de Recordatorios de Reserva</h3>
                    <p class="mb-2">Este flujo de trabajo envía recordatorios por email a los clientes un día antes de su reserva.</p>
                    <ol class="list-decimal pl-6 mb-4 text-sm text-gray-600">
                        <li class="mb-1">Nodo <strong>Schedule Trigger</strong>: Configúralo para que se ejecute diariamente a una hora específica.</li>
                        <li class="mb-1">Nodo <strong>HTTP Request</strong>: Obtiene las reservas para el día siguiente.
                            <div class="code-block text-sm">
GET http://localhost/reservar%20restaurantes/api/reservas?fecha=2025-05-15&estado=confirmada
Headers: Authorization: Bearer <?php echo API_TOKEN; ?></div>
                        </li>
                        <li class="mb-1">Nodo <strong>Loop</strong>: Itera sobre cada reserva.</li>
                        <li class="mb-1">Nodo <strong>Send Email</strong>: Envía un email de recordatorio al cliente.</li>
                    </ol>
                </div>
                
                <div class="mb-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">2. Actualización Automática de Estado de Reservas</h3>
                    <p class="mb-2">Este flujo de trabajo actualiza automáticamente el estado de las reservas pendientes que han superado un tiempo de espera.</p>
                    <ol class="list-decimal pl-6 mb-4 text-sm text-gray-600">
                        <li class="mb-1">Nodo <strong>Schedule Trigger</strong>: Configúralo para que se ejecute cada hora.</li>
                        <li class="mb-1">Nodo <strong>HTTP Request</strong>: Obtiene las reservas pendientes.
                            <div class="code-block text-sm">
GET http://localhost/reservar%20restaurantes/api/reservas?estado=pendiente
Headers: Authorization: Bearer <?php echo API_TOKEN; ?></div>
                        </li>
                        <li class="mb-1">Nodo <strong>Function</strong>: Filtra las reservas que llevan más de 24 horas pendientes.</li>
                        <li class="mb-1">Nodo <strong>Loop</strong>: Itera sobre cada reserva filtrada.</li>
                        <li class="mb-1">Nodo <strong>HTTP Request</strong>: Actualiza el estado de la reserva a "rechazada".
                            <div class="code-block text-sm">
PUT http://localhost/reservar%20restaurantes/api/reservas/{{$node["Loop"].item.json.id}}
Headers: Authorization: Bearer <?php echo API_TOKEN; ?>
Body: {"estado": "rechazada"}</div>
                        </li>
                        <li class="mb-1">Nodo <strong>Send Email</strong>: Notifica al cliente que su reserva ha sido cancelada por falta de confirmación.</li>
                    </ol>
                </div>
                
                <div class="mb-6">
                    <h3 class="text-xl font-semibold text-gray-800 mb-2">3. Informe Diario de Ocupación</h3>
                    <p class="mb-2">Este flujo de trabajo genera y envía un informe diario de ocupación al administrador del restaurante.</p>
                    <ol class="list-decimal pl-6 mb-4 text-sm text-gray-600">
                        <li class="mb-1">Nodo <strong>Schedule Trigger</strong>: Configúralo para que se ejecute diariamente al final del día.</li>
                        <li class="mb-1">Nodo <strong>HTTP Request</strong>: Obtiene las estadísticas del día.
                            <div class="code-block text-sm">
GET http://localhost/reservar%20restaurantes/api/estadisticas?fecha_inicio={{$today}}&fecha_fin={{$today}}&detalle_diario=true
Headers: Authorization: Bearer <?php echo API_TOKEN; ?></div>
                        </li>
                        <li class="mb-1">Nodo <strong>Function</strong>: Formatea los datos para el informe.</li>
                        <li class="mb-1">Nodo <strong>Send Email</strong>: Envía el informe por email al administrador.</li>
                    </ol>
                </div>
            </div>
        </main>

        <footer class="text-center text-gray-500 text-sm">
            <p>&copy; <?php echo date('Y'); ?> Sistema de Reservas de Restaurantes</p>
            <p class="mt-2">
                <a href="/reservar%20restaurantes/" class="text-blue-500 hover:text-blue-700">
                    <i class="fas fa-home mr-1"></i> Volver al Sistema
                </a>
            </p>
        </footer>
    </div>
</body>
</html>
