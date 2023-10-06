<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../../../conexion.php';
include '../../../financiero/consultas.php';
$conexion = new mysqli($bd_servidor, $bd_usuario, $bd_clave, $bd_base);
$_post = json_decode(file_get_contents('php://input'), true);
$fecha_fin = date('Y-m-d', strtotime($_post['vigencia'] . '/12/31'));
try {
    $sql = "SELECT cod_pptal,nom_rubro,ppto_aprob,id_pto_presupuestos FROM seg_pto_cargue WHERE vigencia = '$_post[vigencia]' AND cod_pptal='$_post[rubro]' ";
    $res = $conexion->query($sql);
    $row = $res->fetch_assoc();
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
$adicion = saldoRubroGastos($_post['vigencia'], $_post['rubro'], $fecha_fin, 'ADI', 0, $conexion);
$reduccion = saldoRubroGastos($_post['vigencia'], $_post['rubro'], $fecha_fin, 'RED', 0, $conexion);
$credito = saldoRubroGastos($_post['vigencia'], $_post['rubro'], $fecha_fin, 'TRA', 0, $conexion);
$contracredito = saldoRubroGastos($_post['vigencia'], $_post['rubro'], $fecha_fin, 'TRA', 1, $conexion);
$aplazamiento = saldoRubroGastos($_post['vigencia'], $_post['rubro'], $fecha_fin, 'APL', 0, $conexion);
$desaplazamiento = saldoRubroGastos($_post['vigencia'], $_post['rubro'], $fecha_fin, 'DES', 0, $conexion);
$comprometido = saldoRubroGastos($_post['vigencia'], $_post['rubro'], $fecha_fin, 'CDP', 0, $conexion);
$liquidado = saldoRubroGastos($_post['vigencia'], $_post['rubro'], $fecha_fin, 'LCD', 0, $conexion);
$comprometido = $comprometido + $liquidado;
$definitivo =  $row['ppto_aprob'] + $adicion - $reduccion + $credito - $contracredito - $aplazamiento + $desaplazamiento;
$saldo = $definitivo - $comprometido;
$conexion->close();
?>
<div class="px-0">
    <form id="formFechaSesion">
        <div class="shadow mb-3">
            <div class="card-header" style="background-color: #16a085 !important;">
                <h6 style="color: white;"><i class="fas fa-lock fa-lg" style="color: #FCF3CF"></i>&nbsp;HISTORIAL DE EJECUCIÓN DEL RUBRO</h5>
            </div>
            <div class="pt-3 px-3">
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-10 text-left">
                        <label for="passAnt" class="small"><strong>Rubro : </strong><?php echo ' ' . $row['cod_pptal'] . ' - ' . $row['nom_rubro']; ?></label>
                    </div>
                    <div class="col-md-1"></div>
                </div>
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-6 text-left">
                        <label for="passAnt" class="small">Presupuesto inicial:</label>
                    </div>
                    <div class="col-md-4 text-right">
                        <label for="passAnt" class="small"><?php echo number_format($row['ppto_aprob'], 2, ',', '.'); ?></label>
                    </div>
                    <div class="col-md-1"></div>
                </div>
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-6 text-left">
                        <label for="passAnt" class="small">Adiciones:</label>
                    </div>
                    <div class="col-md-4 text-right">
                        <label for="passAnt" class="small"><?php echo number_format($adicion, 2, ',', '.'); ?></label>
                    </div>
                    <div class="col-md-1"></div>
                </div>
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-6 text-left">
                        <label for="passAnt" class="small">Reducciones:</label>
                    </div>
                    <div class="col-md-4 text-right">
                        <label for="passAnt" class="small"><?php echo number_format($reduccion, 2, ',', '.'); ?></label>
                    </div>
                    <div class="col-md-1"></div>
                </div>
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-6 text-left">
                        <label for="passAnt" class="small">Créditos:</label>
                    </div>
                    <div class="col-md-4 text-right">
                        <label for="passAnt" class="small"><?php echo number_format($credito, 2, ',', '.'); ?></label>
                    </div>
                    <div class="col-md-1"></div>
                </div>
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-6 text-left">
                        <label for="passAnt" class="small">Contracreditos:</label>
                    </div>
                    <div class="col-md-4 text-right">
                        <label for="passAnt" class="small"><?php echo number_format($contracredito, 2, ',', '.'); ?></label>
                    </div>
                    <div class="col-md-1"></div>
                </div>
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-6 text-left">
                        <label for="passAnt" class="small">Aplazamiento:</label>
                    </div>
                    <div class="col-md-4 text-right">
                        <label for="passAnt" class="small"><?php echo number_format($aplazamiento, 2, ',', '.'); ?></label>
                    </div>
                    <div class="col-md-1"></div>
                </div>
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-6 text-left">
                        <label for="passAnt" class="small">Desaplazamiento:</label>
                    </div>
                    <div class="col-md-4 text-right">
                        <label for="passAnt" class="small"><?php echo number_format($desaplazamiento, 2, ',', '.'); ?></label>
                    </div>
                    <div class="col-md-1"></div>
                </div>
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-6 text-left">
                        <label for="passAnt" class="small"><strong>Definitivo:</strong></label>
                    </div>
                    <div class="col-md-4 text-right">
                        <label for="passAnt" class="small"><strong><?php echo number_format($definitivo, 2, ',', '.'); ?></strong></label>
                    </div>
                    <div class="col-md-1"></div>
                </div>
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-6 text-left">
                        <label for="passAnt" class="small">Compromisos:</label>
                    </div>
                    <div class="col-md-4 text-right">
                        <label for="passAnt" class="small"><?php echo number_format($comprometido, 2, ',', '.'); ?></label>
                    </div>
                    <div class="col-md-1"></div>
                </div>
                <div class="row">
                    <div class="col-md-1"></div>
                    <div class="col-md-6 text-left">
                        <label for="passAnt" class="small"><strong>Saldo:</strong></label>
                    </div>
                    <div class="col-md-4 text-right">
                        <label for="passAnt" class="small"><strong><?php echo number_format($saldo, 2, ',', '.');  ?></strong></label>
                    </div>
                    <div class="col-md-1"></div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                    </div>
                </div>

            </div>
        </div>
        <div class="text-right">
            <a class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</a>
        </div>
    </form>
</div>