<?php
$servername = "localhost";
$username = "coffeewa_herramienta_db";
$password = "Irios.,._1A";
$dbname = "coffeewa_herramienta_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Manejo del formulario de compras
if (isset($_POST['agregar_compra'])) {
    $herramienta_id = $_POST['herramienta_id'] ?? null;
    $cantidad = $_POST['cantidad'] ?? null;
    $estado = $_POST['estado'] ?? 'no comprado';

    if ($herramienta_id && $cantidad) {
        $sql = "INSERT INTO compras (herramienta_id, cantidad, estado) VALUES ('$herramienta_id', '$cantidad', '$estado')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Compra agregada exitosamente.');</script>";
            if ($estado === 'comprado') {
                // Actualizar cantidad en la tabla herramientas
                $sql_update = "UPDATE herramientas SET cantidad = cantidad + $cantidad WHERE id = $herramienta_id";
                $conn->query($sql_update);
            }
        } else {
            echo "<script>alert('Error: " . $sql . "<br>" . $conn->error . "');</script>";
        }
    } else {
        echo "<script>alert('Por favor, complete todos los campos.');</script>";
    }
}

// Obtener herramientas con bajo stock
$sql = "SELECT h.id, h.nombre, h.codigo, h.ubicacion, h.cantidad, h.imagen 
        FROM herramientas h 
        WHERE h.cantidad < 10"; // Cambia este número según el mínimo de stock deseado
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compras de Herramientas</title>
    <style>
        /* Tu CSS aquí */
    </style>
</head>
<body>
    <header>
        <h1>Compras de Herramientas</h1>
    </header>
    <div>
        <h2>Herramientas con bajo stock</h2>
        <form method="post" action="compras.php">
            <table border="1">
                <tr>
                    <th>Nombre</th>
                    <th>Código</th>
                    <th>Ubicación</th>
                    <th>Cantidad Actual</th>
                    <th>Cantidad a Comprar</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
                <?php
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $row['nombre'] . "</td>";
                        echo "<td>" . $row['codigo'] . "</td>";
                        echo "<td>" . $row['ubicacion'] . "</td>";
                        echo "<td>" . $row['cantidad'] . "</td>";
                        echo '<td><input type="number" name="cantidad"></td>';
                        echo '<td>
                                <select name="estado">
                                    <option value="no comprado">No Comprado</option>
                                    <option value="comprado">Comprado</option>
                                </select>
                              </td>';
                        echo '<td>
                                <input type="hidden" name="herramienta_id" value="' . $row['id'] . '">
                                <input type="submit" name="agregar_compra" value="Registrar Compra">
                              </td>';
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='7'>No hay herramientas con bajo stock.</td></tr>";
                }
                ?>
            </table>
        </form>
    </div>
    
    <div>
        <h2>Agregar Nueva Compra</h2>
        <form method="post" action="compras.php">
            <label for="nombre">Nombre de la Herramienta:</label>
            <input type="text" name="nombre"><br>

            <label for="codigo">Código de la Herramienta:</label>
            <input type="text" name="codigo"><br>

            <label for="ubicacion">Ubicación:</label>
            <input type="text" name="ubicacion"><br>

            <label for="cantidad">Cantidad:</label>
            <input type="number" name="cantidad"><br>

            <label for="estado">Estado:</label>
            <select name="estado">
                <option value="no comprado">No Comprado</option>
                <option value="comprado">Comprado</option>
            </select><br>

            <input type="submit" name="agregar_nueva" value="Agregar Nueva Compra">
        </form>
    </div>

    <?php
    if (isset($_POST['agregar_nueva'])) {
        $nombre = $_POST['nombre'] ?? '';
        $codigo = $_POST['codigo'] ?? '';
        $ubicacion = $_POST['ubicacion'] ?? '';
        $cantidad = $_POST['cantidad'] ?? 0;
        $estado = $_POST['estado'] ?? 'no comprado';

        if ($nombre && $codigo && $ubicacion && $cantidad) {
            // Insertar la nueva herramienta en la tabla herramientas
            $sql_herramienta = "INSERT INTO herramientas (nombre, codigo, ubicacion, cantidad) VALUES ('$nombre', '$codigo', '$ubicacion', '$cantidad')";

            if ($conn->query($sql_herramienta) === TRUE) {
                $herramienta_id = $conn->insert_id;

                // Insertar la nueva compra en la tabla compras
                $sql_compra = "INSERT INTO compras (herramienta_id, cantidad, estado) VALUES ('$herramienta_id', '$cantidad', '$estado')";

                if ($conn->query($sql_compra) === TRUE) {
                    echo "<script>alert('Nueva herramienta y compra registradas exitosamente.');</script>";
                } else {
                    echo "<script>alert('Error al registrar la compra: " . $conn->error . "');</script>";
                }
            } else {
                echo "<script>alert('Error al registrar la herramienta: " . $conn->error . "');</script>";
            }
        } else {
            echo "<script>alert('Por favor, complete todos los campos.');</script>";
        }
    }

    $conn->close();
    ?>
</body>
</html>
