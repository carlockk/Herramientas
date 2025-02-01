<?php
require_once 'phpqrcode/qrlib.php';  // Incluimos la biblioteca QR

$servername = "localhost";
$username = "coffeewa_herramienta_db";
$password = "Irios.,._1A";
$dbname = "coffeewa_herramienta_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Manejo del formulario de subida de herramientas
if (isset($_POST['agregar'])) {
    $nombre = $_POST['nombre'];
    $codigo = $_POST['codigo'];
    $ubicacion = $_POST['ubicacion'];
    $cantidad = $_POST['cantidad'];
    $imagen = $_FILES['imagen']['name'];
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($imagen);
    $uploadOk = 1;

    // Verificar si la carpeta de destino existe y tiene permisos de escritura
    if (!is_dir($target_dir) || !is_writable($target_dir)) {
        echo "<script>alert('La carpeta de destino no existe o no tiene permisos de escritura.');</script>";
        $uploadOk = 0;
    }

    // Verificar si hay errores en el archivo de subida
    if ($_FILES['imagen']['error'] != 0) {
        echo "<script>alert('Hubo un error al subir la imagen: " . $_FILES['imagen']['error'] . "');</script>";
        $uploadOk = 0;
    }

    // Verificar el tamaño del archivo
    if ($_FILES['imagen']['size'] > 500000) {
        echo "<script>alert('La imagen es demasiado grande.');</script>";
        $uploadOk = 0;
    }

    // Verificar si el archivo es una imagen real
    $check = getimagesize($_FILES['imagen']['tmp_name']);
    if($check === false) {
        echo "<script>alert('El archivo no es una imagen.');</script>";
        $uploadOk = 0;
    }

    // Subir la imagen si todo está bien
    if ($uploadOk && move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file)) {
        $qrContent = "Nombre: $nombre\nCódigo: $codigo\nUbicación: $ubicacion";
        $qrFile = $target_dir . 'qr_' . $codigo . '.png';
        QRcode::png($qrContent, $qrFile, QR_ECLEVEL_L, 10);

        $sql = "INSERT INTO herramientas (nombre, codigo, ubicacion, cantidad, imagen) VALUES ('$nombre', '$codigo', '$ubicacion', '$cantidad', '$target_file')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Nueva herramienta agregada exitosamente.');</script>";
        } else {
            echo "<script>alert('Error: " . $sql . "<br>" . $conn->error . "');</script>";
        }
    } else {
        if ($uploadOk) {
            echo "<script>alert('Hubo un error al subir la imagen.');</script>";
        }
    }
}

// Manejo de edición de herramientas
if (isset($_POST['editar'])) {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $ubicacion = $_POST['ubicacion'];
    $cantidad = $_POST['cantidad'];

    $sql = "UPDATE herramientas SET nombre='$nombre', ubicacion='$ubicacion', cantidad='$cantidad' WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Herramienta actualizada exitosamente.');</script>";
    } else {
        echo "<script>alert('Error: " . $sql . "<br>" . $conn->error . "');</script>";
    }
}

// Manejo de eliminación de herramientas
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];

    $sql = "DELETE FROM herramientas WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Herramienta eliminada exitosamente.');</script>";
    } else {
        echo "<script>alert('Error: " . $sql . "<br>" . $conn->error . "');</script>";
    }
}

// Obtener todas las herramientas
$sql = "SELECT * FROM herramientas";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Herramientas</title>
    
    <!--hoja de estilos-->
    <link rel="stylesheet" href="estilos/styles.css">
</head>
<body>
    <header>
        <div class="container">
            <h1>Gestión de Herramientas</h1>
        </div>
    </header>
    <div class="menu-bar">
        <div class="container">
            <nav>
                <a href="#" id="mostrar_lista">Lista de herramientas</a>
                <a href="#" id="mostrar_formulario">Agregar elementos</a>
            </nav>
        </div>
    </div>
    <div id="buscar-form" class="container">
        <form method="get" action="index.php">
            
            <input type="text" name="buscar" id="buscar" placeholder="Buscar herramientas" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Buscar herramientas'">
            <input type="button" id="escanear_buscar" value="Escanear">
            <div id="qr-reader-buscar" style="width:300px; display: none;"></div>
            <input type="submit" value="Buscar">
        </form>
    </div>
    <div id="main" class="container">
        <form id="formulario" class="tool-form" action="index.php" method="post" enctype="multipart/form-data">
            <label for="nombre">Agregar nombre de la herramienta:</label>
            <input type="text" name="nombre" id="nombre" required>
            
            <label for="codigo">Agregar código de la herramienta:</label>
            <input type="text" name="codigo" id="codigo" required>
            <div id="qr-reader-codigo" style="width:300px; display: none;"></div>

            <label for="ubicacion">Agregar ubicación de la herramienta:</label>
            <input type="text" name="ubicacion" id="ubicacion" required>
            
            <label for="cantidad">Cantidad:</label>
            <input type="text" name="cantidad" required>
            
            <label for="imagen">Agregar imagen de la herramienta:</label>
            <input type="file" name="imagen" id="imagen" required>
            
            <input type="submit" name="agregar" value="Agregar Herramienta">
        </form>

        <div class="tools">
            <h2>Lista de Herramientas</h2>
            <div class="tools-grid">
                <?php
                $buscar = isset($_GET['buscar']) ? $_GET['buscar'] : '';
                $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
                $limite = 10;
                $offset = ($pagina - 1) * $limite;
        
                $sql = "SELECT * FROM herramientas WHERE nombre LIKE '%$buscar%' OR codigo LIKE '%$buscar%' LIMIT $limite OFFSET $offset";
                $result = $conn->query($sql);
        
                if ($result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        echo '<div class="tool">';
                        echo '<div class="tool-content">';
                        echo '<img src="' . $row["imagen"] . '" alt="Imagen de ' . $row["nombre"] . '">';
                        echo '<div>';
                        echo '<h3>' . $row["nombre"] . '</h3>';
                        echo '<p>Código: ' . $row["codigo"] . '</p>';
                        echo '<p>Ubicación: ' . $row["ubicacion"] . '</p>';
                        echo '<p>Cantidad: ' . $row["cantidad"] . '</p>';
                        echo '<p><a style="color: #e14b2b; font-weight: bold; text-decoration: none; font-size: 13px;" href="' . 'uploads/qr_' . $row["codigo"] . '.png' . '" target="_blank">Ver código QR</a></p>'; // Enlace para ver el QR

                        // Botones de editar y eliminar
echo '<div class="tool-actions">';
echo '<form method="post" action="index.php" style="display:inline-block;">';
echo '<input type="hidden" name="id" value="' . $row["id"] . '">';
echo '<button type="button" class="btn-pagination" onclick="toggleEditForm(' . $row["id"] . ')">Editar</button>';
echo '</form>';
echo '<form method="get" action="index.php" style="display:inline-block;">';
echo '<input type="hidden" name="eliminar" value="' . $row["id"] . '">';
echo '<button type="submit" class="btn-pagination">Eliminar</button>';
echo '</form>';
echo '</div>';

                        
                        // Formulario de edición (oculto por defecto)
                        echo '<div class="edit-form" id="edit-form-' . $row["id"] . '" style="display:none;">';
                        echo '<form method="post" action="index.php">';
                        echo '<input type="hidden" name="id" value="' . $row["id"] . '">';
                        echo '<label for="nombre">Nombre:</label>';
                        echo '<input type="text" name="nombre" value="' . $row["nombre"] . '">';
                        echo '<label for="ubicacion">Ubicación:</label>';
                        echo '<input type="text" name="ubicacion" value="' . $row["ubicacion"] . '">';
                        echo '<label for="cantidad">Cantidad:</label>';
                        echo '<input type="text" name="cantidad" value="' . $row["cantidad"] . '">';
                        echo '<button type="submit" name="editar">Guardar cambios</button>';
                        echo '</form>';
                        echo '</div>';

                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo "No se encontraron herramientas.";
                }

                $conn->close();
                ?>

                
            </div>
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="js/html5-qrcode.min.js"></script>
    <script src="js/scripts.js"></script>
    <script>
        function toggleEditForm(id) {
            var form = document.getElementById('edit-form-' + id);
            if (form.style.display === 'none') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }
    </script>
</body>
</html>
