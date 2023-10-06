<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../../index.php");</script>';
    exit();
}
include '../../../../conexion.php';
$id = isset($_POST['id']) ? $_POST['id'] : exit('Acción no permitida');
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT *
            FROM
                seg_libranzas
            INNER JOIN seg_bancos 
                ON (seg_libranzas.id_banco = seg_bancos.id_banco) 
            WHERE id_empleado = '$id'";
    $rs = $cmd->query($sql);
    $libranzas = $rs->fetchAll();
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $sql = "SELECT seg_liq_libranza.id_libranza, id_empleado, SUM(val_mes_lib) AS pagado, COUNT(seg_liq_libranza.id_libranza) AS cuotas
            FROM
                seg_liq_libranza
            INNER JOIN seg_libranzas 
                ON (seg_liq_libranza.id_libranza = seg_libranzas.id_libranza)
            GROUP BY id_libranza";
    $rs = $cmd->query($sql);
    $pagosLib = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
include '../../../../permisos.php';
if (!empty($libranzas)) {
    foreach ($libranzas as $li) {
        if ((intval($permisos['editar'])) === 1) {
            $editar = '<button value="' . $li['id_libranza'] . '" class="btn btn-outline-primary btn-sm btn-circle editar" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></button>';
        } else {
            $editar = null;
        }
        if ((intval($permisos['borrar'])) === 1) {
            $borrar = '<button value="' . $li['id_libranza'] . '" class="btn btn-outline-danger btn-sm btn-circle borrar" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></button>';
        } else {
            $borrar = null;
        }
        if ($li['estado'] == 0) {
            $borrar = $editar = null;
        }
        $idlib = $li['id_libranza'];
        $key = array_search($idlib, array_column($pagosLib, 'id_libranza'));
        if (false !== $key) {
            $pago = $pagosLib[$key]['pagado'];
            $cuotas = $pagosLib[$key]['cuotas'];
        } else {
            $pago = '0';
            $cuotas = '0';
        }
        $estado = $li['estado'] == 1 ? '<span class="badge badge-success">Activo</span><button value="' . $li['id_libranza'] . '" class="btn btn-outline-success btn-sm btn-circle estado" title="Cambiar Estado" estado="' . $li['estado'] . '"><span class="fas fa-exchange-alt"></span></button>' : '<span class="badge badge-secondary">Inactivo</span><button value="' . $li['id_libranza'] . '" class="btn btn-outline-secondary btn-sm btn-circle estado" title="Cambiar Estado"  estado="' . $li['estado'] . '"><span class="fas fa-exchange-alt"></span></button>';
        $data[] = [
            'id_libranza' => $li['id_libranza'],
            'nom_banco' => $li['nom_banco'],
            'valor_total' => pesos($li['valor_total']),
            'cuotas' => $li['cuotas'],
            'val_mes' => pesos($li['val_mes']),
            'val_pagado' => pesos($pago),
            'cuotas_pag' => $cuotas,
            'fecha_inicio' => $li['fecha_inicio'],
            'fecha_fin' => $li['fecha_fin'],
            'estado' => $estado,
            'botones' => '<div class="center-block">' . $editar . $borrar . '<button class="btn btn-outline-warning btn-sm btn-circle detalles" value="' . $idlib . '" title="Detalles Libranza"><span class="far fa-eye fa-lg"></span></button></div>'
        ];
    }
} else {
    $data = [];
}

$datos = ['data' => $data];

echo json_encode($datos);
