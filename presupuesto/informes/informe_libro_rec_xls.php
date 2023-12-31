<?php
session_start();
set_time_limit(5600);
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../../index.php");</script>';
    exit();
}
$vigencia = $_SESSION['vigencia'];
$fecha_corte = file_get_contents("php://input");
function pesos($valor)
{
    return '$' . number_format($valor, 2);
}
include '../../conexion.php';
include '../../financiero/consultas.php';
$cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
$cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
//
try {
    $sql = "SELECT
    fecha
    ,factura
    ,nit
    ,cuenta
    , objeto
    ,valor
FROM (
	SELECT 
		fecha
		,CONCAT(id_factura,'_',num_factura) AS factura
		, nit
		,detalle AS objeto
		,cuenta
		,valordeb  AS valor
	FROM vista_ctb_libaux 
	LEFT JOIN seg_terceros ON(vista_ctb_libaux.nit = seg_terceros.no_doc)
	WHERE tipo ='REC' AND fecha BETWEEN '2023-01-01' AND '$fecha_corte'
	UNION ALL
	SELECT
        seg_pto_documento.fecha   
        ,seg_pto_documento.id_manu AS factura	    
	    , seg_terceros.no_doc AS nit
	    , seg_pto_documento.objeto
	    , seg_pto_mvto.rubro AS cuenta
	    , SUM(seg_pto_mvto.valor) AS valor
	FROM
	    seg_pto_mvto
	    INNER JOIN seg_pto_documento ON (seg_pto_mvto.id_pto_doc = seg_pto_documento.id_pto_doc)
	    LEFT JOIN seg_terceros ON(seg_pto_documento.id_tercero = seg_terceros.id_tercero_api)
	WHERE (seg_pto_documento.tipo_doc ='REC' AND seg_pto_documento.fecha BETWEEN '2023-01-01' AND '$fecha_corte')
	GROUP BY seg_pto_mvto.rubro
    ) AS reconocimientos ORDER BY fecha ASC;";
    $res = $cmd->query($sql);
    $causaciones = $res->fetchAll();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
// consulto el nombre de la empresa de la tabla seg_empresas
try {
    $sql = "SELECT
    `nombre`
    , `nit`
    , `dig_ver`
FROM
    `seg_empresas`;";
    $res = $cmd->query($sql);
    $empresa = $res->fetch();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getCode();
}
?>
<table style="width:100% !important; border-collapse: collapse;">
    <thead>
        <tr>
            <td rowspan="4" style="text-align:center"><label class="small"><img src="<?php echo $_SESSION['urlin'] ?>/images/logos/logo.png" width="100"></label></td>
            <td colspan="6" style="text-align:center"><?php echo $empresa['nombre']; ?></td>
        </tr>
        <tr>
            <td colspan="6" style="text-align:center"><?php echo $empresa['nit'] . '-' . $empresa['dig_ver']; ?></td>
        </tr>
        <tr>
            <td colspan="6" style="text-align:center"><?php echo 'RELACION DE RECAUDOS PRESUPUESTALES'; ?></td>
        </tr>
        <tr>
            <td colspan="6" style="text-align:center"><?php echo 'Fecha de corte: ' . $fecha_corte; ?></td>
        </tr>
        <tr style="background-color: #CED3D3; text-align:center;font-size:9px;">
            <th>Tipo</th>
            <th>No reconocimiento</th>
            <th>Fecha</th>
            <th>Tercero</th>
            <th>Objeto</th>
            <th>Rubro</th>
            <th>Valor</th>
        </tr>
    </thead>
    <tbody style="font-size:9px;">
        <?php
        foreach ($causaciones as $rp) {
            // consulto el valor registrado de cada cdp y rubro
            /*$sql = "SELECT
                        SUM(`seg_pto_mvto`.`valor`) AS `valor_rp`
                    FROM
                        `seg_pto_mvto`
                        INNER JOIN `seg_pto_documento` 
                            ON (`seg_pto_mvto`.`id_pto_doc` = `seg_pto_documento`.`id_pto_doc`)
                    WHERE `seg_pto_mvto`.`rubro` ='{$rp['rubro']}'
                        AND `seg_pto_documento`.`fecha` <='$fecha_corte'
                        AND `seg_pto_mvto`.`id_auto_dep` ={$rp['id_pto_doc']}
                        AND `seg_pto_mvto`.`tipo_mov`='CRP' 
                    GROUP BY `seg_pto_mvto`.`rubro`;";
            $res = $cmd->query($sql);
            $reg2 = $res->fetch();

*/
            if ($rp['objeto'] == '') {
                $rp['objeto'] = 'RECAUDO POR VENTA DE SERVICIOS ';
            }
            $fecha = date('Y-m-d', strtotime($rp['fecha']));
            if ($saldo >= 0) {
                echo "<tr>
            <td style='text-aling:left'>" . 'REC' .  "</td>
            <td style='text-aling:left'>" . $rp['factura'] . "</td>
            <td style='text-aling:left'>" .   $fecha   . "</td>
            <td style='text-aling:left'>" . $rp['nit'] . "</td>
            <td style='text-aling:left'>" . $rp['objeto'] . "</td>
            <td style='text-aling:left'>" .  $rp['cuenta'] . "</td>
            <td style='text-aling:right'>" . number_format($rp['valor'], 2, ".", ",")  . "</td>
            </tr>";
            }
        }
        ?>
    </tbody>
</table>