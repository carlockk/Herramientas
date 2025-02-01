<?php
if (isset($_GET['file'])) {
    $file = 'uploads/' . basename($_GET['file']);
    if (file_exists($file)) {
        header('Content-Description: File Transfer');
        header('Content-Type: image/jpeg');
        header('Content-Disposition: attachment; filename="' . basename($file) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        readfile($file);
        exit;
    } else {
        echo "Archivo no encontrado.";
    }
} else {
    echo "Archivo no especificado.";
}
