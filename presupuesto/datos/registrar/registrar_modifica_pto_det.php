<?php
session_start();
if (isset($_POST)) {
    //Recibir variables por POST
    $ctrl = 0;
    $id_rubroCod = $_POST['id_rubroCod'];
    $valorDeb = str_replace(",", "", $_POST['valorDeb']);
    $id_pto_mod = $_POST['id_pto_mod'];
    $tipo_doc = $_POST['tipo_doc'];
    $estado = $_POST['estado'];
    if ($tipo_doc == 'APL' || $tipo_doc == 'RED') {
        $mov = 1;
    } else {
        $mov = 0;
    }
    if ($tipo_doc == 'TRA') {
        if ($estado == 'true') {
            $mov = 0;
        } else {
            $mov = 1;
        }
    }
    if ($estado == 'true') {
        $estado2 = '1';
    } else {
        $estado2 = '0';
    }

    $iduser = $_SESSION['id_user'];
    $date = new DateTime('now', new DateTimeZone('America/Bogota'));
    include '../../../conexion.php';

    try {
        $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
        $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        if (empty($_POST['id_pto_mov'])) {
            $query = $cmd->prepare("INSERT INTO seg_pto_mvto (id_pto_doc, tipo_mov,mov, rubro, valor,ctrl,estado) VALUES (?, ?, ?, ?, ?,?,?)");
            $query->bindParam(1, $id_pto_mod, PDO::PARAM_INT);
            $query->bindParam(2, $tipo_doc, PDO::PARAM_STR);
            $query->bindParam(3, $mov, PDO::PARAM_INT);
            $query->bindParam(4, $id_rubroCod, PDO::PARAM_STR);
            $query->bindParam(5, $valorDeb, PDO::PARAM_STR);
            $query->bindParam(6, $ctrl, PDO::PARAM_STR);
            $query->bindParam(7, $estado2, PDO::PARAM_INT);

            $query->execute();
            if ($cmd->lastInsertId() > 0) {
                $id = $cmd->lastInsertId();
                // Consulto la suma de la modificación
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                    $sql = "SELECT sum(valor) as valorsum FROM seg_pto_mvto WHERE id_pto_doc = $id_pto_mod AND estado =1 GROUP BY id_pto_doc";
                    $rs = $cmd->query($sql);
                    $datos2 = $rs->fetch();
                    $valor1 = number_format($datos2['valorsum'], 2, '.', ',');
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                }
                try {
                    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
                    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                    $sql = "SELECT sum(valor) as valorsum FROM seg_pto_mvto WHERE id_pto_doc = $id_pto_mod AND estado =0 GROUP BY id_pto_doc";
                    $rs = $cmd->query($sql);
                    $datos3 = $rs->fetch();
                    $valor2 = number_format($datos3['valorsum'], 2, ".", ",");
                    $cmd = null;
                } catch (PDOException $e) {
                    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
                }
                $dif = $datos2['valorsum'] - $datos3['valorsum'];
                $response[] = array("value" => 'ok', "id" => $id, "valor1" => $valor1, "valor2" => $valor2, "dif" => $dif);
            } else {
                print_r($query_rubro->errorInfo()[2]);
            }
            $cmd = null;
        } else {
            $id = $_POST['id_pto_doc'];
            $query = $cmd->prepare("UPDATE seg_pto_documento SET id_manu = :id_manu, fecha = :fecha, objeto =:objeto, id_usuer_act=:id_usuer_act,fec_act=:fec_act WHERE id_pto_doc = :id_pto_doc");
            $query->bindParam(":id_manu", $id_manu);
            $query->bindParam(":fecha", $fecha);
            $query->bindParam(":objeto", $objeto);
            $query->bindParam(":id_usuer_act", $iduser);
            $query->bindParam(":fec_act", $date);
            $query->bindParam(":id_pto_doc", $id);
            $query->execute();
            $cmd = null;
            $response[] = array("value" => 'modificado', "id" => $id);
        }
        echo json_encode($response);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}
