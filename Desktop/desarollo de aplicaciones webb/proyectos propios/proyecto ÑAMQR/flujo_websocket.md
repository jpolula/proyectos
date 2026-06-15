# Flujo del Sistema de Gestión de Pedidos con WebSockets

## 1. Componentes Principales

### 1.1 Frontend
- **`carrito.js`**: Maneja el carrito de compras y el envío de pedidos.
- **`cocina.js`**: Controla la interfaz de cocina y las actualizaciones en tiempo real.
- **`camarero.js`**: Gestiona la interfaz del camarero y el estado de los pedidos.

### 1.2 Backend
- **`confirmarPedido.php`**: Procesa los pedidos y notifica a la cocina.
- **`cocina.php`**: Endpoint para gestionar las peticiones AJAX de la cocina.
- **`servidorWebsocket.php`**: Servidor WebSocket que maneja las conexiones en tiempo real.

## 2. Flujo de un Nuevo Pedido

### 2.1 Cliente Realiza el Pedido
**Archivo: `carrito.js`**
1. El cliente agrega productos al carrito.
2. Al confirmar, se envía el formulario a `confirmarPedido.php`.

### 2.2 Procesamiento del Pedido
**Archivo: `confirmarPedido.php`**
1. Valida y guarda el pedido en la base de datos.
2. Notifica a la cocina mediante WebSocket:
   ```php
   $clienteWebSocket = new \WebSocket\Client("ws://localhost:8080");
   $mensaje = json_encode([
       "tipo" => "nuevoPedido",
       "cod" => $codPedido,
       "numMesa" => $numMesa,
       "timestamp" => time()
   ]);
   $clienteWebSocket->send($mensaje);
   $clienteWebSocket->close();
   ```

### 2.3 Distribución de la Notificación
**Archivo: `servidorWebsocket.php`**
1. Recibe el mensaje `nuevoPedido`.
2. Identifica los clientes de tipo "cocina".
3. Reenvía el mensaje a las cocinas conectadas.

## 3. Actualización en Tiempo Real

### 3.1 En la Cocina
**Archivo: `cocina.js`**
1. Recibe la notificación del nuevo pedido:
   ```javascript
   socket.onmessage = function(event) {
       const mensaje = JSON.parse(event.data);
       if (mensaje.tipo === 'nuevoPedido') {
           cargarPedidosCocina();
       }
   };
   ```
2. Actualiza la interfaz mostrando el nuevo pedido como "pendiente".

### 3.2 Cambios de Estado
**Archivo: `cocina.js`**
1. Al cambiar el estado de un producto (ej: a "preparando"):
   - Actualización optimista de la UI.
   - Petición AJAX para guardar el cambio.
   - Notificación WebSocket a otros clientes.
   ```javascript
   if (window.ws && window.ws.readyState === WebSocket.OPEN) {
       window.ws.send(JSON.stringify({
           tipo: 'producto_preparando',
           codPedido: codPedido,
           codProducto: codProducto,
           numMesa: numMesa
       }));
   }
   ```

## 4. Comunicación Bidireccional
   ```javascript
   // Envío de notificación WebSocket
   if (window.ws && window.ws.readyState === WebSocket.OPEN) {
       const notificacion = {
           tipo: 'productoListo',
           codPedido: codPedido,
           codProducto: codProducto,
           numMesa: numMesa
       };
       window.ws.send(JSON.stringify(notificacion));
   }
   ```

## 5. Servidor WebSocket (Retransmisión)

**Archivo: `servidorWebsocket.php`**

1. El servidor recibe el mensaje `productoListo` en `onMessage()`
2. Identifica a todos los clientes de tipo "camarero"
3. Reenvía el mensaje a estos clientes:
   ```php
   // Difusión del mensaje productoListo a todos los camareros
   foreach ($this->clientesTipo['camarero'] as $cliente) {
       $cliente->send($msg);
   }
   ```

## 6. Recepción por el Camarero

**Archivo: `camarero.js`**

1. El WebSocket del camarero recibe el mensaje en la función `onmessage`:
   ```javascript
   window.ws.onmessage = function(event) {
       try {
           const data = JSON.parse(event.data);
           
           if (data.tipo === 'productoListo') {
               // Un producto ha sido marcado como listo por la cocina
               mostrarNotificacion('info', `¡Producto listo para servir! Mesa: ${data.numMesa || 'N/A'}`);
               // Actualizar la lista de pedidos listos para servir
               cargarPedidosListos();
           }
       } catch (error) {
           // Manejo de errores
       }
   };
   ```

2. La función `cargarPedidosListos()` hace una petición AJAX a `camarero.php` para obtener los productos listos para servir
3. La interfaz del camarero se actualiza con los productos listos para servir mediante `renderizarPedidos()`

## 7. Servicio por el Camarero

**Archivo: `camarero.js`**

1. El camarero localiza el producto listo para servir en la interfaz
2. Hace clic en el botón "SERVIR" del producto
3. Se ejecuta la función `marcarComoServido()`:
   ```javascript
   function marcarComoServido(e) {
       // Obtener datos del producto y pedido
       const boton = e.currentTarget;
       const codPedido = boton.getAttribute('data-pedido');
       const codProducto = boton.getAttribute('data-producto');
       const codDetalle = boton.getAttribute('data-detalle');
       
       // Elementos DOM relevantes
       const productoElement = boton.closest('.producto');
       const pedidoCard = productoElement ? productoElement.closest('.pedido-card') : null;
       const mesaContainer = pedidoCard ? pedidoCard.closest('.mesa-container') : null;
       
       // Mostrar notificación y actualizar UI optimistamente
       mostrarNotificacion('success', '¡Producto marcado como servido!');
       
       // 1. Deshabilitar el botón para evitar múltiples clics
       boton.disabled = true;
       boton.classList.add('opacity-50');
       
       // 2. Enviar la actualización al servidor
       fetch('camarero.php', {
           method: 'POST',
           headers: {
               'Content-Type': 'application/json',
           },
           body: JSON.stringify({
               action: 'marcarComoServido',
               codPedido: codPedido,
               codProducto: codProducto,
               codDetalle: codDetalle
           })
       })
       .then(response => response.json())
       .then(data => {
           if (!data.success) {
               // Manejo de errores
           }
       })
       .catch(error => {
           // Manejo de errores
       });
       
       // 3. Eliminamos el producto inmediatamente
       productoElement.remove();
       
       // 4. Verificar si el pedido quedó sin productos
       if (pedidoCard) {
           const productosRestantes = pedidoCard.querySelectorAll('.producto');
           if (productosRestantes.length === 0) {
               // Si el pedido quedó sin productos, lo eliminamos
               pedidoCard.remove();
           }
       }
       
       // 5. Verificar si la mesa quedó sin productos
       if (mesaContainer) {
           verificarYocultarMesaSiVacia(mesaContainer);
       }
       
       // 6. Enviar notificación WebSocket
       if (window.ws && window.ws.readyState === WebSocket.OPEN) {
           const notificacion = {
               tipo: 'productoServido',
               codPedido: codPedido,
               codProducto: codProducto,
               numMesa: mesaContainer ? mesaContainer.dataset.mesaId : 'N/A'
           };
           window.ws.send(JSON.stringify(notificacion));
       }
   }
   ```

4. El producto se elimina inmediatamente de la interfaz del camarero
5. La función envía una petición AJAX a `camarero.php` para actualizar el estado en la base de datos
6. Se envía una notificación WebSocket de tipo `productoServido`

## 8. Servidor WebSocket (Final)

**Archivo: `servidorWebsocket.php`**

1. El servidor recibe el mensaje `productoServido` 
2. Puede notificar a los clientes interesados (clientes con pedidos, administradores, etc.)
3. El ciclo del pedido queda completado

## Resumen del Flujo

1. **Cliente** → Realiza pedido → `confirmarPedido.js` → Envía mensaje WebSocket `nuevoPedido`
2. **Servidor WebSocket** → Recibe `nuevoPedido` → Reenvía a todos los clientes de tipo "cocina"
3. **Cocina** → Recibe `nuevoPedido` → Muestra el pedido → Procesa productos → Envía mensajes `productoPreparando` y `productoListo`
4. **Servidor WebSocket** → Recibe `productoListo` → Reenvía a todos los clientes de tipo "camarero"
5. **Camarero** → Recibe `productoListo` → Muestra el producto listo → Sirve el producto → Envía mensaje `productoServido`
6. **Servidor WebSocket** → Recibe `productoServido` → Cierra el ciclo del pedido

Este flujo demuestra la arquitectura de comunicación en tiempo real entre todos los actores del sistema, permitiendo actualizaciones instantáneas en toda la plataforma sin necesidad de recargar páginas.
