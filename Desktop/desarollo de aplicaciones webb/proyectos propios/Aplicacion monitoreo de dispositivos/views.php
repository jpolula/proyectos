<?php
require_once 'functions.php';
$config = parse_ini_file('config.ini', true);

// Procesar las conexiones y servicios del archivo config.ini
$connections = [];
$devices = [];

// Primero, procesar los servicios y tipos de dispositivos
if (isset($config['ips']) && isset($config['icons'])) {
    foreach ($config['ips'] as $ip => $service) {
        if ($service === 'Array') continue;
        
        $devices[$ip] = [
            'service' => $service,
            'icon' => isset($config['icons'][$ip]) ? $config['icons'][$ip] : 'router'
        ];
    }
}

// Luego, procesar las conexiones
if (isset($config['connections'])) {
    foreach ($config['connections'] as $sourceIp => $targetIps) {
        if (!empty($targetIps)) {
            $targets = explode(',', $targetIps);
            $connections[$sourceIp] = $targets;
            
            // Añadir las conexiones a los datos del dispositivo
            if (isset($devices[$sourceIp])) {
                $devices[$sourceIp]['connections'] = $targets;
            }
        }
    }
}

// Debug: Imprimir las conexiones para verificar
error_log("Conexiones cargadas: " . print_r($connections, true));
error_log("Dispositivos procesados: " . print_r($devices, true));

$ping_data = [];
if (file_exists('ping_results.json')) {
    $ping_data = json_decode(file_get_contents('ping_results.json'), true) ?? [];
}

// Definir los iconos disponibles
$deviceIcons = [
    'router' => ['label' => 'Router', 'image' => 'icons/router.png'],
    'parabolica' => ['label' => 'Parabólica', 'image' => 'icons/parabolica.png'],
    'radio' => ['label' => 'Panel Radio', 'image' => 'icons/radio.png'],
    'pc' => ['label' => 'PC', 'image' => 'icons/pc.png'],
    'casa' => ['label' => 'Casa', 'image' => 'icons/casa.png'],
    'switch' => ['label' => 'Switch', 'image' => 'icons/switch.png'],
    'ap' => ['label' => 'Punto de Acceso', 'image' => 'icons/ap.png']
];

// Asegurarse de que los iconos existan
require_once('create_icons.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="<?php echo isset($_GET['interval']) ? $_GET['interval'] : 60; ?>">
    <title>Monitor de Red</title>
    <link href="/app2/public/css/styles.css" rel="stylesheet">
    <script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
    <style>
        #network-map {
            height: 400px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            background-color: #f8f9fa;
        }
        .icon-option {
            cursor: pointer;
            padding: 8px;
            border: 2px solid transparent;
            border-radius: 4px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .icon-option:hover {
            background-color: #f0f0f0;
        }
        .icon-option img {
            width: 32px;
            height: 32px;
        }
        .icon-checkbox {
            width: 18px;
            height: 18px;
            margin-right: 8px;
        }
        
        .icon-option {
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .icon-option:hover {
            border-color: #4a5568;
        }
        
        .icon-option.selected {
            border-color: #4299e1;
            background-color: #ebf8ff;
        }
        
        .icon-checkbox {
            margin-right: 0.5rem;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4">
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h1 class="text-2xl font-bold text-gray-800">Monitor de Red</h1>
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-600">Intervalo de actualización:</span>
                        <div class="flex gap-2">
                            <button onclick="changeInterval(5)" data-seconds="5" 
                                class="interval-btn px-3 py-1 rounded <?php echo (isset($_GET['interval']) && $_GET['interval'] == 5) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'; ?> hover:bg-blue-700 hover:text-white">5s</button>
                            <button onclick="changeInterval(10)" data-seconds="10" 
                                class="interval-btn px-3 py-1 rounded <?php echo (isset($_GET['interval']) && $_GET['interval'] == 10) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'; ?> hover:bg-blue-700 hover:text-white">10s</button>
                            <button onclick="changeInterval(20)" data-seconds="20" 
                                class="interval-btn px-3 py-1 rounded <?php echo (isset($_GET['interval']) && $_GET['interval'] == 20) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'; ?> hover:bg-blue-700 hover:text-white">20s</button>
                            <button onclick="changeInterval(30)" data-seconds="30" 
                                class="interval-btn px-3 py-1 rounded <?php echo (isset($_GET['interval']) && $_GET['interval'] == 30) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'; ?> hover:bg-blue-700 hover:text-white">30s</button>
                            <button onclick="changeInterval(60)" data-seconds="60" 
                                class="interval-btn px-3 py-1 rounded <?php echo (!isset($_GET['interval']) || $_GET['interval'] == 60) ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'; ?> hover:bg-blue-700 hover:text-white">1m</button>
                        </div>
                    </div>
                    <button onclick="checkNow()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Ping Ahora
                    </button>
                    <button onclick="showAddDeviceModal()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                        Añadir Dispositivo
                    </button>
                </div>
            </div>

            <div class="text-center my-4">
                <p class="text-lg bg-gray-800 text-white bg-opacity-50 p-2 rounded-lg inline-block shadow-lg">
                    Próximo ping en <span id="countdown" class="font-bold"><?php echo isset($_GET['interval']) ? $_GET['interval'] : 60; ?></span> segundos
                </p>
            </div>

            <!-- Mapa de Red -->
            <div id="network-map" class="mb-6"></div>

            <!-- Status Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white rounded-lg overflow-hidden">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2">IP</th>
                            <th class="px-4 py-2">Servicio</th>
                            <th class="px-4 py-2">Tipo</th>
                            <th class="px-4 py-2">Estado</th>
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <th class="px-2 py-2 text-xs">Ping <?php echo $i; ?></th>
                            <?php endfor; ?>
                            <th class="px-4 py-2">Disponibilidad</th>
                            <th class="px-4 py-2">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $history = [];
                        if (file_exists('ping_history.json')) {
                            $history = json_decode(file_get_contents('ping_history.json'), true) ?? [];
                        }
                        
                        foreach ($devices as $ip => $device): 
                            $status = analyze_ip($ip);
                            $statusClass = $status['status'] == 'UP' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                            $serviceDisplay = is_array($device['service']) ? implode(', ', $device['service']) : $device['service'];
                            $deviceType = isset($device['icon']) && isset($deviceIcons[$device['icon']]) ? $device['icon'] : 'router';
                            $icon = $deviceIcons[$deviceType];
                        ?>
                        <tr data-ip="<?php echo $ip; ?>">
                            <td class="border px-4 py-2"><?php echo $ip; ?></td>
                            <td class="border px-4 py-2"><?php echo $serviceDisplay; ?></td>
                            <td class="border px-4 py-2">
                                <img src="<?php echo $icon['image']; ?>" 
                                     alt="<?php echo $icon['label']; ?>" 
                                     class="w-6 h-6 inline-block mr-1">
                                <?php echo $icon['label']; ?>
                            </td>
                            <td class="border px-4 py-2"><span class="px-2 py-1 rounded <?php echo $statusClass; ?>"><?php echo $status['status']; ?></span></td>
                            <?php 
                            $deviceHistory = $history[$ip] ?? [];
                            for ($i = 0; $i < 10; $i++): 
                                $ping = $deviceHistory[$i] ?? null;
                            ?>
                                <td class="border px-2 py-2">
                                    <?php if ($ping): ?>
                                        <div class="text-center">
                                            <div class="text-xs text-gray-500 mb-1"><?php echo date('H:i:s', strtotime($ping['timestamp'])); ?></div>
                                            <span class="px-2 py-0.5 rounded text-xs font-medium <?php echo $ping['status'] === 'UP' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                <?php echo $ping['status']; ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            <?php endfor; ?>
                            <td class="border px-4 py-2"><?php echo number_format($status['availability'], 2); ?>%</td>
                            <td class="border px-4 py-2">
                                <button onclick="editDevice('<?php echo $ip; ?>', '<?php echo $serviceDisplay; ?>', '<?php echo $deviceType; ?>')" class="text-blue-600 hover:text-blue-800 mr-2">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button onclick="deleteDevice('<?php echo $ip; ?>')" class="text-red-600 hover:text-red-800">
                                    <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal de Añadir Dispositivo -->
    <div id="addDeviceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-xl w-[600px] max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Añadir Nuevo Dispositivo</h2>
                <button onclick="hideAddDeviceModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="addDeviceForm">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="newIp">
                        Dirección IP
                    </label>
                    <input type="text" id="newIp" name="ip" pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$" 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="newService">
                        Servicio
                    </label>
                    <input type="text" id="newService" name="service"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Tipo de Dispositivo
                    </label>
                    <div class="grid grid-cols-2 gap-4" id="addDeviceIcons">
                        <?php foreach ($deviceIcons as $type => $icon): ?>
                        <label class="icon-option" for="deviceType_<?php echo $type; ?>">
                            <input type="radio" name="deviceType" id="deviceType_<?php echo $type; ?>" value="<?php echo $type; ?>" class="icon-checkbox">
                            <img src="<?php echo $icon['image']; ?>" alt="<?php echo $icon['label']; ?>">
                            <span><?php echo $icon['label']; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Conectar con dispositivos
                    </label>
                    <div class="grid grid-cols-1 gap-4">
                        <div class="max-h-60 overflow-y-auto p-4 border rounded-md">
                            <?php foreach ($devices as $connIp => $connDevice): ?>
                            <div class="flex items-center mb-2">
                                <input type="checkbox" name="connections[]" value="<?php echo $connIp; ?>" 
                                       id="add_conn_<?php echo $connIp; ?>"
                                       class="w-4 h-4 mr-2 rounded border-gray-300 focus:ring-2 focus:ring-blue-500">
                                <label class="text-sm text-gray-700" for="add_conn_<?php echo $connIp; ?>">
                                    <?php echo $connIp; ?> (<?php echo $connDevice['service']; ?>)
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Editar Dispositivo -->
    <div id="editDeviceModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-xl w-[800px] max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-2xl font-bold">Editar Dispositivo</h2>
                <button onclick="hideEditDeviceModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form id="editDeviceForm">
                <input type="hidden" id="originalIp" name="originalIp">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="editService">
                        Servicio
                    </label>
                    <input type="text" id="editService" name="service"
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Tipo de Dispositivo
                    </label>
                    <div class="grid grid-cols-2 gap-4">
                        <?php foreach ($deviceIcons as $type => $icon): ?>
                        <label class="icon-option edit-icon-option flex items-center space-x-2 p-2 border rounded cursor-pointer hover:bg-gray-100" for="editDeviceType_<?php echo $type; ?>" data-type="<?php echo $type; ?>">
                            <input type="radio" name="editDeviceType" id="editDeviceType_<?php echo $type; ?>" value="<?php echo $type; ?>" class="icon-checkbox">
                            <img src="<?php echo $icon['image']; ?>" alt="<?php echo $icon['label']; ?>" class="w-6 h-6">
                            <span><?php echo $icon['label']; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="editDeviceIp">
                        Dirección IP
                    </label>
                    <input type="text" id="editDeviceIp" name="ip" pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$" 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Conectar con dispositivos
                    </label>
                    <div class="grid grid-cols-1 gap-4">
                        <div class="max-h-60 overflow-y-auto p-4 border rounded-md">
                            <?php foreach ($devices as $connIp => $connDevice): ?>
                            <div class="flex items-center mb-2">
                                <input type="checkbox" name="editConnections[]" value="<?php echo $connIp; ?>" 
                                       id="edit_conn_<?php echo $connIp; ?>"
                                       class="w-4 h-4 mr-2 rounded border-gray-300 focus:ring-2 focus:ring-blue-500">
                                <label class="text-sm text-gray-700" for="edit_conn_<?php echo $connIp; ?>">
                                    <?php echo $connIp; ?> (<?php echo $connDevice['service']; ?>)
                                </label>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="flex justify-between items-center">
                            <p class="text-sm text-gray-600">Selecciona los dispositivos a los que este dispositivo debe hacer ping.</p>
                            <button type="button" onclick="clearConnections()" 
                                    class="px-3 py-1 text-sm text-red-600 hover:text-red-800 focus:outline-none">
                                Quitar todas las conexiones
                            </button>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let pingInterval = <?php echo isset($_GET['interval']) ? $_GET['interval'] : 60; ?>;
        let countdown = pingInterval;
        let isUpdating = false;
        let network = null;
        let nodes = new vis.DataSet();
        let edges = new vis.DataSet();
        let deviceIcons = <?php echo json_encode($deviceIcons); ?>;

        function updateCountdown() {
            if (countdown > 0) {
                countdown--;
                document.getElementById('countdown').textContent = countdown;
            }
        }

        function changeInterval(seconds) {
            window.location.href = `${window.location.pathname}?interval=${seconds}`;
        }

        function checkNow() {
            countdown = pingInterval;
            performPing();
        }

        async function performPing() {
            if (isUpdating) return false;
            isUpdating = true;
            
            try {
                const response = await fetch('ping_all.php');
                const data = await response.json();
                isUpdating = false;
                countdown = pingInterval;
                updateStatusTable(data);
                updateNetworkStatus(data);
            } catch (error) {
                console.error('Error:', error);
                isUpdating = false;
            }
        }

        function showAddDeviceModal() {
            document.getElementById('addDeviceModal').style.display = 'flex';
        }

        function hideAddDeviceModal() {
            document.getElementById('addDeviceModal').style.display = 'none';
            document.getElementById('addDeviceForm').reset();
        }

        function saveNodePositions(positions) {
            try {
                localStorage.setItem('networkPositions', JSON.stringify(positions));
            } catch (e) {
                console.error('Error al guardar posiciones:', e);
            }
        }

        function loadNodePositions() {
            try {
                const savedPositionsStr = localStorage.getItem('networkPositions');
                if (savedPositionsStr) {
                    return JSON.parse(savedPositionsStr);
                }
            } catch (e) {
                console.error('Error al cargar posiciones:', e);
                localStorage.removeItem('networkPositions');
            }
            return null;
        }

        function resetNodePositions() {
            localStorage.removeItem('networkPositions');
            location.reload();
        }

        function initializeNetwork() {
            const container = document.getElementById('network-map');
            const devices = <?php echo json_encode($devices); ?>;
            const connections = <?php echo json_encode($connections); ?>;
            const pingData = <?php echo json_encode($ping_data); ?>;

            // Cargar posiciones guardadas
            const savedPositions = loadNodePositions();

            // Preparar los nodos
            for (const [ip, device] of Object.entries(devices)) {
                const nodeData = {
                    id: ip,
                    label: ip,
                    title: `${ip}\n${device.service}`,
                    image: deviceIcons[device.icon]?.image || deviceIcons['router'].image,
                    shape: 'image',
                    size: 30,
                    color: {
                        border: '#f44336',
                        highlight: { border: '#e53935' }
                    }
                };

                // Añadir posición guardada si existe
                if (savedPositions && savedPositions[ip]) {
                    nodeData.x = savedPositions[ip].x;
                    nodeData.y = savedPositions[ip].y;
                }

                nodes.add(nodeData);
            }

            // Añadir las conexiones
            for (const [sourceIp, targets] of Object.entries(connections)) {
                if (Array.isArray(targets)) {
                    targets.forEach(targetIp => {
                        if (devices[targetIp]) {
                            edges.add({
                                from: sourceIp,
                                to: targetIp,
                                arrows: 'to',
                                color: { 
                                    color: '#f44336',
                                    highlight: '#e53935',
                                    opacity: 1.0
                                },
                                width: 2
                            });
                        }
                    });
                }
            }

            const data = { nodes: nodes, edges: edges };
            const options = {
                nodes: {
                    font: { size: 12, color: '#000000' },
                    borderWidth: 2,
                    shadow: true
                },
                edges: {
                    smooth: { type: 'continuous' },
                    shadow: true
                },
                physics: {
                    enabled: !savedPositions,
                    solver: 'forceAtlas2Based',
                    forceAtlas2Based: {
                        gravitationalConstant: -26,
                        centralGravity: 0.005,
                        springLength: 230,
                        springConstant: 0.18
                    },
                    stabilization: {
                        enabled: true,
                        iterations: 200
                    }
                },
                interaction: {
                    hover: true,
                    tooltipDelay: 200
                }
            };

            network = new vis.Network(container, data, options);
            
            // Guardar posiciones cuando se mueven los nodos
            network.on('dragEnd', function(params) {
                if (params.nodes.length > 0) {
                    const positions = {};
                    params.nodes.forEach(nodeId => {
                        positions[nodeId] = network.getPosition(nodeId);
                    });
                    saveNodePositions(positions);
                }
            });

            // Añadir botón para resetear posiciones
            const resetButton = document.createElement('button');
            resetButton.innerHTML = 'Resetear Posiciones';
            resetButton.className = 'bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded mt-2';
            resetButton.onclick = resetNodePositions;
            container.parentNode.insertBefore(resetButton, container.nextSibling);
            
            // Hacer un ping inicial
            performPing();
        }

        function updateNetworkStatus(pingData) {
            if (!network) return;

            Object.entries(pingData.current || {}).forEach(([ip, data]) => {
                const status = data.status === 'UP';
                
                // Actualizar nodo
                nodes.update({
                    id: ip,
                    color: {
                        border: status ? '#4CAF50' : '#f44336',
                        highlight: { border: status ? '#45a049' : '#e53935' }
                    }
                });

                // Actualizar conexiones
                edges.get().forEach(edge => {
                    if (edge.from === ip || edge.to === ip) {
                        const targetStatus = pingData.current[edge.to]?.status === 'UP';
                        const sourceStatus = pingData.current[edge.from]?.status === 'UP';
                        const isConnected = sourceStatus && targetStatus;

                        edges.update({
                            id: edge.id,
                            color: {
                                color: isConnected ? '#4CAF50' : '#f44336',
                                highlight: isConnected ? '#45a049' : '#e53935'
                            }
                        });
                    }
                });
            });
        }

        function updateStatusTable(pingData) {
            if (!pingData || !pingData.current) return;

            Object.entries(pingData.current).forEach(([ip, data]) => {
                const row = document.querySelector(`tr[data-ip="${ip}"]`);
                if (!row) return;

                // Actualizar estado
                const statusCell = row.querySelector('td:nth-child(4)');
                if (statusCell) {
                    const status = data.status === 'UP';
                    const statusClass = status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                    statusCell.innerHTML = `<span class="px-2 py-1 rounded ${statusClass}">${data.status}</span>`;
                }

                // Actualizar historial de ping
                if (pingData.history && pingData.history[ip]) {
                    const history = pingData.history[ip];
                    for (let i = 0; i < 10; i++) {
                        const historyCell = row.querySelector(`td[data-history="${i}"]`);
                        if (historyCell) {
                            const pingResult = history[i] || {};
                            const status = pingResult.status === 'UP';
                            historyCell.innerHTML = `<span class="inline-block w-3 h-3 rounded-full ${status ? 'bg-green-500' : 'bg-red-500'}"></span>`;
                        }
                    }
                }
            });
        }

        // Inicializar cuando el documento esté listo
        document.addEventListener('DOMContentLoaded', function() {
            // Iniciar el contador
            setInterval(updateCountdown, 1000);
            
            // Inicializar el mapa de red
            initializeNetwork();

            // Iniciar el intervalo de ping
            setInterval(performPing, pingInterval * 1000);
        });

        function hideEditDeviceModal() {
            document.getElementById('editDeviceModal').style.display = 'none';
        }

        function editDevice(ip, service, deviceType) {
            // Mostrar el modal de edición
            document.getElementById('editDeviceModal').style.display = 'flex';
            
            // Establecer los valores actuales
            document.getElementById('editDeviceIp').value = ip;
            document.getElementById('editService').value = service;
            document.getElementById('originalIp').value = ip;

            // Seleccionar el tipo de dispositivo correcto
            const radioInput = document.querySelector(`input[name="editDeviceType"][value="${deviceType}"]`);
            if (radioInput) {
                radioInput.checked = true;
                // Actualizar clases visuales
                document.querySelectorAll('#editDeviceIcons .icon-option').forEach(label => {
                    label.classList.remove('selected');
                });
                radioInput.closest('.icon-option').classList.add('selected');
                
                // Deshabilitar IP si es switch
                if (deviceType === 'switch') {
                    document.getElementById('editDeviceIp').value = '';
                    document.getElementById('editDeviceIp').disabled = true;
                } else {
                    document.getElementById('editDeviceIp').disabled = false;
                }
            }

            // Obtener y marcar las conexiones existentes
            fetch(`get_connections.php?ip=${encodeURIComponent(ip)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Desmarcar todas las conexiones primero
                        document.querySelectorAll('input[name="editConnections[]"]').forEach(checkbox => {
                            checkbox.checked = false;
                        });

                        // Marcar las conexiones existentes
                        if (data.connections) {
                            data.connections.forEach(connectedIp => {
                                const checkbox = document.querySelector(`input[name="editConnections[]"][value="${connectedIp}"]`);
                                if (checkbox) {
                                    checkbox.checked = true;
                                }
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }

        // Manejar la selección de tipo de dispositivo en el modal de edición
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('#editDeviceIcons input[type="radio"]').forEach(input => {
                input.addEventListener('change', function() {
                    // Remover la clase selected de todos los labels
                    document.querySelectorAll('#editDeviceIcons .icon-option').forEach(label => {
                        label.classList.remove('selected');
                    });
                    
                    // Añadir la clase selected al label seleccionado
                    const selectedLabel = this.closest('.icon-option');
                    if (selectedLabel) {
                        selectedLabel.classList.add('selected');
                    }

                    // Manejar el campo IP según el tipo de dispositivo
                    const deviceType = this.value;
                    const ipField = document.getElementById('editDeviceIp');
                    if (deviceType === 'switch') {
                        ipField.value = '';
                        ipField.disabled = true;
                    } else {
                        ipField.disabled = false;
                        // Si estamos cambiando de switch a otro tipo, restaurar la IP original
                        if (ipField.value === '') {
                            ipField.value = document.getElementById('originalIp').value;
                        }
                    }
                });
            });

            // Manejar el formulario de edición
            document.getElementById('editDeviceForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData();
                const deviceType = document.querySelector('input[name="editDeviceType"]:checked')?.value;
                const originalIp = document.getElementById('originalIp').value;
                
                // Solo añadir IP si no es un switch
                if (deviceType !== 'switch') {
                    const ip = document.getElementById('editDeviceIp').value;
                    formData.append('new_ip', ip);
                }
                
                formData.append('original_ip', originalIp);
                formData.append('service', document.getElementById('editService').value);
                formData.append('device_type', deviceType);
                
                // Recoger las conexiones seleccionadas
                const connections = [];
                document.querySelectorAll('input[name="editConnections[]"]:checked').forEach(checkbox => {
                    connections.push(checkbox.value);
                });
                formData.append('connections', JSON.stringify(connections));

                // Enviar la actualización
                fetch('update_ip.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error || 'Error al actualizar el dispositivo');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al actualizar el dispositivo');
                });
            });
        });

        // Manejar el formulario de añadir dispositivo
        document.getElementById('addDeviceForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            const deviceType = document.querySelector('input[name="deviceType"]:checked')?.value;
            
            // Solo añadir IP si no es un switch
            if (deviceType !== 'switch') {
                const ip = document.getElementById('newIp').value;
                formData.append('ip', ip);
            }
            
            formData.append('service', document.getElementById('newService').value);
            formData.append('device_type', deviceType);
            
            // Recoger las conexiones seleccionadas
            const connections = [];
            document.querySelectorAll('input[name="connections[]"]:checked').forEach(checkbox => {
                connections.push(checkbox.value);
            });
            formData.append('connections', JSON.stringify(connections));

            // Enviar la solicitud
            fetch('add_ip.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.error || 'Error al añadir el dispositivo');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al añadir el dispositivo');
            });
        });

        // Manejar la selección de tipo de dispositivo en el modal de añadir
        document.querySelectorAll('#addDeviceIcons input[type="radio"]').forEach(input => {
            input.addEventListener('change', function() {
                // Remover la clase selected de todos los labels
                document.querySelectorAll('#addDeviceIcons .icon-option').forEach(label => {
                    label.classList.remove('selected');
                });
                
                // Añadir la clase selected al label seleccionado
                const selectedLabel = this.closest('.icon-option');
                if (selectedLabel) {
                    selectedLabel.classList.add('selected');
                }

                // Manejar el campo IP según el tipo de dispositivo
                const deviceType = this.value;
                const ipField = document.getElementById('newIp');
                if (deviceType === 'switch') {
                    ipField.value = '';
                    ipField.disabled = true;
                } else {
                    ipField.disabled = false;
                }
            });
        });

        function deleteDevice(ip) {
            if (confirm('¿Estás seguro de que deseas eliminar este dispositivo?')) {
                fetch('delete_ip.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `ip=${encodeURIComponent(ip)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert(data.error || 'Error al eliminar el dispositivo');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el dispositivo');
                });
            }
        }

        function clearConnections() {
            document.querySelectorAll('input[name="editConnections[]"]').forEach(checkbox => {
                checkbox.checked = false;
            });
        }
    </script>
</body>
</html>