<?php
$data = file_get_contents("php://input");
include '../../../conexion.php';
// Inicio conexion a la base de datos
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
} catch (Exception $e) {
    die("No se pudo conectar: " . $e->getMessage());
}
// Inicio transaccion 
try {
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $cmd->beginTransaction();
    try {
        $query = $cmd->prepare("DELETE FROM seg_pto_documento WHERE id_pto_doc =?");
        $query->bindParam(1, $data);
        $query->execute();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    // Editar campo id_cdp =0 en la tabla seg_adquisiciones cuando sea igual a id_cdp
    try {
        $query = $cmd->prepare("UPDATE seg_adquisiciones SET id_cdp =1 WHERE id_cdp =?");
        $query->bindParam(1, $data);
        $query->execute();
    } catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
    $cmd->commit();
    echo "ok";
} catch (Exception $e) {
    $cmd->rollBack();
    echo "Failed: " . $e->getMessage();
}
