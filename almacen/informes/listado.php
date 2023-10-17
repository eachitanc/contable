<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../../index.php");</script>';
    exit();
}
function pesos($valor)
{
    return '$' . number_format($valor, 2, ",", ".");
}

include '../../conexion.php';
include '../../permisos.php';
$id = 1;
$rol = $_SESSION['rol'];
?>
<div class="px-0">
    <div class="shadow">
        <div class="modal-header" style="background-color: #16a085 !important; color:aliceblue">
            <h5 class="modal-title">INFORMES</h5>
            <button type="button" class="close btn btn-outline-success" data-dismiss="modal" aria-label="Close">
                <span class="fas fa-times text-color:white"></span>
            </button>
        </div>
        <div class="p-2">
            <table id="tableListInfoAlmacen" class="table table-striped table-bordered table-sm nowrap table-hover shadow" style="width:100%">
                <thead>
                    <tr class="text-center centro-vertical">
                        <th>ID</th>
                        <th>Descripción</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="accionListInfoAlm">
                    <tr>
                        <td><?php echo $id;
                            $id++ ?></td>
                        <td class="text-left">CONTROL DE EXISTENCIAS</td>
                        <td class="text-center">
                            <button value="1" class="btn btn-outline-warning btn-sm btn-circle shadow-gb informe" title="VER INFORME"><span class="fas fa-eye fa-lg"></span></button>
                        </td>
                    </tr>
                    <?php if ($rol == 1 || $rol == 3) { ?>
                        <tr>
                            <td><?php echo $id;
                                $id++ ?></td>
                            <td class="text-left">CONTROL DE CONSUMOS PERIODO-GRUPO-CONSOLIDADOS</td>
                            <td class="text-center">
                                <button value="2" class="btn btn-outline-warning btn-sm btn-circle shadow-gb informe" title="VER INFORME"><span class="fas fa-eye fa-lg"></span></button>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $id;
                                $id++ ?></td>
                            <td class="text-left">EXISTENCIA POR ARTÍCULO</td>
                            <td class="text-center">
                                <button value="3" class="btn btn-outline-warning btn-sm btn-circle shadow-gb informe" title="VER INFORME"><span class="fas fa-eye fa-lg"></span></button>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $id;
                                $id++ ?></td>
                            <td class="text-left">CAPTURA INVENTARIO FÍSICO (KARDEX TOTALIZADO)</td>
                            <td class="text-center">
                                <button value="4" class="btn btn-outline-warning btn-sm btn-circle shadow-gb informe" title="VER INFORME"><span class="fas fa-eye fa-lg"></span></button>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $id;
                                $id++ ?></td>
                            <td class="text-left">ENTRADAS POR TERCERO (DETALLE - CONSOLIDADO)</td>
                            <td class="text-center">
                                <button value="5" class="btn btn-outline-warning btn-sm btn-circle shadow-gb informe" title="VER INFORME"><span class="fas fa-eye fa-lg"></span></button>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $id;
                                $id++ ?></td>
                            <td class="text-left">TRASLADOS (CONTROL - MULTIPLES)</td>
                            <td class="text-center">
                                <button value="6" class="btn btn-outline-warning btn-sm btn-circle shadow-gb informe" title="VER INFORME"><span class="fas fa-eye fa-lg"></span></button>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $id;
                                $id++ ?></td>
                            <td class="text-left">CONSECUTIVOS TRASLADO</td>
                            <td class="text-center">
                                <button value="7" class="btn btn-outline-warning btn-sm btn-circle shadow-gb informe" title="VER INFORME"><span class="fas fa-eye fa-lg"></span></button>
                            </td>
                        </tr>
                        <tr>
                            <td><?php echo $id;
                                $id++ ?></td>
                            <td class="text-left">VENCIMIENTOS POR AREA</td>
                            <td class="text-center">
                                <button value="8" class="btn btn-outline-warning btn-sm btn-circle shadow-gb informe" title="VER INFORME"><span class="fas fa-eye fa-lg"></span></button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>