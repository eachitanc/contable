<?php
    $data = file_get_contents("php://input");
    include '../../../conexion.php';
    try {
        $pdo = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $query = $pdo->prepare("SELECT
                `seg_pto_mvto`.`id_pto_mvto`
                , `seg_pto_mvto`.`id_pto_doc`
                , `seg_pto_mvto`.`tipo_mov`
                , `seg_pto_mvto`.`rubro`
                , `seg_pto_mvto`.`valor`
                , `seg_pto_cargue`.`nom_rubro`
                , `seg_pto_cargue`.`tipo_dato`
            FROM
                `seg_pto_mvto`
                INNER JOIN `seg_pto_cargue` 
                    ON (`seg_pto_cargue`.`cod_pptal` = `seg_pto_mvto`.`rubro`)
            WHERE (`seg_pto_mvto`.`id_pto_mvto` =:id);");
        $query->bindParam(":id", $data);
        $query->execute();
        $resultado = $query->fetch(PDO::FETCH_ASSOC);
        echo json_encode($resultado);
    }
    catch (PDOException $e) {
        echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
    }
?>