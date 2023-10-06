<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../permisos.php';
// Div de acciones de la lista
$id_pto_cdp = $_POST['id_cdp'];

try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql2 = "SELECT id_pto_mvto,id_pto_doc,rubro,sum(valor) as valor FROM seg_pto_mvto WHERE id_pto_doc=$id_pto_cdp group by rubro";
    $rs = $cmd->query($sql2);
    $listappto = $rs->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
if (!empty($listappto)) {
    foreach ($listappto as $lp) {
        // Consulto el nombre del rubro
        $sql = "SELECT nom_rubro FROM seg_pto_cargue WHERE cod_pptal='$lp[rubro]'";
        $rs = $cmd->query($sql);
        $nomrubro = $rs->fetch();
        $nombre = $nomrubro['nom_rubro'];
        // Consulto el valor ejecutado en registros presupuestales 
        // $sql = "SELECT sum(valor) as ejecutado FROM seg_pto_mvto WHERE rubro ='$lp[rubro]' AND tipo_mov ='CRP' AND id_auto_dep =$id_pto_cdp";
        $sql = "SELECT
                    SUM(`seg_pto_mvto`.`valor`) as ejecutado
                FROM
                    `seg_pto_mvto`
                    INNER JOIN `seg_pto_documento` 
                        ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
                WHERE (`seg_pto_mvto`.`rubro` ='$lp[rubro]'
                    AND `seg_pto_mvto`.`tipo_mov` ='CRP'
                    AND `seg_pto_mvto`.`id_auto_dep` =$id_pto_cdp
                    AND `seg_pto_documento`.`estado` =0);";
        $rs = $cmd->query($sql);
        $ejecutado = $rs->fetch();
        // Sumar el valor liberado de la tabla seg_pto_mvto asociado al CDP
        $sql = "SELECT SUM(valor) AS valor FROM seg_pto_mvto WHERE id_auto_dep=$id_pto_cdp AND tipo_mov ='LRP' AND rubro='$lp[rubro]'";
        $rs4 = $cmd->query($sql);
        $sumalib = $rs4->fetch();
        $sql2 = $sql;
        //
        $valor_ejec = $ejecutado['ejecutado'] + $sumalib['valor'];
        $valor = $lp['valor'] - $valor_ejec;
        $valor = number_format($valor, 2, '.', ',');
        $id_pto = $lp['id_pto_doc'];
        $id = $lp['id_pto_mvto'];
        $valor_input = '<input class="form-control form-control-sm" type="text" style="text-align:right;border: 0;" name="lp' . $id . '" id="lp' . $id . '" value="' . $valor . '" min="0" max="' . $valor .  '" onkeyup="valorMiles(id)">';
        // Valor con separador de mailes

        if ((intval($permisos['editar'])) === 1) {
            $editar = '<a value="' . $id_pto . '" onclick=Editar("' . $id . '") class="btn btn-outline-primary btn-sm btn-circle shadow-gb" title="Editar"><span class="fas fa-pencil-alt fa-lg"></span></a>';
        } else {
            $editar = null;
            $detalles = null;
        }
        if ((intval($permisos['borrar'])) === 1) {
            $borrar = '<a value="' . $id . '" onclick=Eliminar("' . $id . '") class="btn btn-outline-danger btn-sm btn-circle shadow-gb" title="Eliminar"><span class="fas fa-trash-alt fa-lg"></span></a>';
        } else {
            $borrar = null;
        }
        $data[] = [

            'rubro' => $lp['rubro'] . ' - ' . $nombre,
            'valor' => '<div class="text-right">' . $valor_input . '</div>',
            'botones' => '<div class="text-center" style="position:relative">' . $editar . $borrar .  '</div>',

        ];
    }
} else {
    $data = [];
}
$cmd = null;
$datos = ['data' => $data];


echo json_encode($datos);
