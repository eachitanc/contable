<?php
session_start();
if (isset($_POST)) {
    $fecha = $_POST['fecha'];
    $id_pto = $_POST['id_pto'];
    $tipo_acto = $_POST['tipo_acto'];
    $numMod = $_POST['numMod'];
    $objeto = $_POST['objeto'];
    $tipo_doc = $_POST['tipo_doc'];
    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    $fecha2 = $date->format('Y-m-d H:i:s');
    $estado = 1;
    include '../../../conexion.php';
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
    if (empty($_POST['id_pto_mod'])) {
        $query = $cmd->prepare("INSERT INTO seg_pto_documento (id_pto_presupuestos, tipo_doc, id_manu, fecha, tipo_mod, objeto, estado, id_user_reg, fec_reg) VALUES (?, ?, ?, ?, ?, ?,?,?,?)");
        $query->bindParam(1, $id_pto, PDO::PARAM_INT);
        $query->bindParam(2, $tipo_doc, PDO::PARAM_STR);
        $query->bindParam(3, $numMod, PDO::PARAM_INT);
        $query->bindParam(4, $fecha, PDO::PARAM_STR);
        $query->bindParam(5, $tipo_acto, PDO::PARAM_INT);
        $query->bindParam(6, $objeto, PDO::PARAM_STR);
        $query->bindParam(7, $estado, PDO::PARAM_INT);
        $query->bindParam(8, $iduser, PDO::PARAM_INT);
        $query->bindParam(9, $fecha2);
        $query->execute();
        if ($cmd->lastInsertId() > 0) {
            $id = $cmd->lastInsertId();
            $response[] = array("value" => 'ok', "id" => $id);
        } else {
            print_r($query->errorInfo()[2]);
        }
        $cmd = null;
    } else {
        $id = $_POST['id_pto_mvto'];
        $query = $cmd->prepare("UPDATE seg_pto_mvto SET id_pto_doc = :id_pto, tipo_mov = :tipo, rubro =:rubro, valor = :valor WHERE id_pto_mvto = :id");
        $query->bindParam(":id_pto", $id_pto_cdp);
        $query->bindParam(":tipo", $tipo_mov);
        $query->bindParam(":rubro", $rubro);
        $query->bindParam(":valor", $valorCdp);
        $query->bindParam("id", $id);
        $query->execute();
        $cmd = null;
        echo "modificado";
        $response[] = array("value" => 'no');
    }
    echo json_encode($response);
}
