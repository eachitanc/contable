<?php
session_start();
$data = file_get_contents("php://input");
include '../../../conexion.php';
try {
    $pdo = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    $query = $pdo->prepare("SELECT
    `seg_pto_mvto`.`rubro`
    , `seg_pto_cargue`.`nom_rubro`
    , `seg_pto_mvto`.`valor`
    , `seg_pto_cargue`.`vigencia`
    , `seg_pto_mvto`.`id_pto_doc`
    FROM
    `seg_pto_mvto`
    INNER JOIN `seg_pto_cargue` 
        ON (`seg_pto_mvto`.`rubro` = `seg_pto_cargue`.`cod_pptal`)
    WHERE (`seg_pto_cargue`.`vigencia` =:anno
    AND `seg_pto_mvto`.`id_pto_mvto` =:id);
    ");
    $query->bindParam(':anno', $_SESSION['vigencia']);
    $query->bindParam(":id", $data);
    $query->execute();
    $resultado = $query->fetch(PDO::FETCH_ASSOC);
    // Consultar el valor total aplazado del rubro por documento
    $query = $pdo->prepare("SELECT SUM(`valor`) as suma FROM `seg_pto_mvto` WHERE id_auto_dep = $resultado[id_pto_doc] AND tipo_mov ='DES' AND rubro ='$resultado[rubro]';");
    $query->execute();
    $resultado2 = $query->fetch(PDO::FETCH_ASSOC);
    $valor = $resultado['valor'] - $resultado2['suma'];
    $datos = array('rubro' =>  $resultado['rubro'], 'nom_rubro' => $resultado['nom_rubro'], 'valor' => $valor);
    echo json_encode($datos);
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
