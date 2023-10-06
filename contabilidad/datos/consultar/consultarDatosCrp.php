<?php
    $data = file_get_contents("php://input");
    include '../../../conexion.php';
    try {
        $pdo = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $query = $pdo->prepare("SELECT
        `seg_pto_documento`.`id_pto_doc`
        , `seg_pto_documento`.`id_tercero`
        , `seg_pto_documento`.`fecha`
        , `seg_pto_documento`.`objeto`
        , `z_terceros`.`nombre`
    FROM
        `seg_pto_documento`
        INNER JOIN `z_terceros` 
            ON (`seg_pto_documento`.`id_tercero` = `z_terceros`.`num_id`)
    WHERE (`seg_pto_documento`.`id_pto_doc` =:id);");
        $query->bindParam(":id", $data);
        $query->execute();
        $resultado = $query->fetch(PDO::FETCH_ASSOC);
        echo json_encode($resultado);
    }
    catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
?>