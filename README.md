# Sistema Web de Restaurante - Pachuco

Proyecto base en PHP 8 + MySQL para un restaurante. Incluye login, registro, formularios, menú, mesas, reservaciones, pedidos y panel de administración.

## 1. Requisitos

- PHP 8.0 o superior
- MySQL o MariaDB
- Servidor local como XAMPP, Laragon, WAMP o el servidor integrado de PHP

## 2. Crear la base de datos

1. Abre phpMyAdmin o tu cliente MySQL favorito.
2. Crea una base de datos llamada `pachuco_restaurante`.
3. Importa el archivo:

```sql
database/schema.sql
```

Ese archivo crea 7 tablas y carga 15 registros por tabla:

- `users`
- `categories`
- `menu_items`
- `restaurant_tables`
- `reservations`
- `orders`
- `order_items`

## 3. Configurar conexión

Edita `app/config.php` si tus datos de MySQL son diferentes:

```php
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'pachuco_restaurante');
define('DB_USER', 'root');
define('DB_PASS', '');
```

## 4. Ejecutar el proyecto

Desde la carpeta del proyecto:

```bash
php -S localhost:8000 -t public
```

Luego abre:

```text
http://localhost:8000
```

Si usas XAMPP, copia el proyecto dentro de `htdocs` y entra desde el navegador con la ruta correspondiente.

## 5. Usuarios de prueba

Todos los usuarios sembrados usan la contraseña:

```text
password
```

Administrador:

```text
admin@pachuco.test
```

Cliente:

```text
ana@pachuco.test
```

## 6. Estructura del proyecto

```text
app/
  auth.php
  config.php
  db.php
  helpers.php
  layout/
database/
  schema.sql
public/
  admin.php
  index.php
  login.php
  logout.php
  menu.php
  orders.php
  register.php
  reservations.php
  tables.php
  assets/css/styles.css
```

## 7. Flujo recomendado de desarrollo

1. Crear la base de datos y tablas.
2. Crear conexión segura con PDO.
3. Crear layout compartido: encabezado, navegación y pie.
4. Crear login y registro con sesiones.
5. Crear páginas públicas: inicio, menú y mesas.
6. Crear formularios protegidos: reservaciones y pedidos.
7. Crear administración para consultar datos y agregar platillos.
8. Validar entradas del usuario.
9. Probar con usuarios cliente y administrador.

