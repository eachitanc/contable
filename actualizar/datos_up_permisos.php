<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo '<script>window.location.replace("../index.php");</script>';
    exit();
}
include '../conexion.php';
include '../permisos.php';
if ($_SESSION['id_user'] != 1) {
    exit('Usuario no autorizado');
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT id_permiso, seg_usuarios.id_usuario, nombre1, nombre2, apellido1, apellido2, login, listar, registrar, editar, borrar
            FROM
                seg_permisos_usuario
            INNER JOIN seg_usuarios 
                ON (seg_permisos_usuario.id_usuario = seg_usuarios.id_usuario)
            WHERE estado = '1'";
    $rs = $cmd->query($sql);
    $objs = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
try {
    $cmd = new PDO("$bd_driver:host=$bd_servidor;dbname=$bd_base;$charset", $bd_usuario, $bd_clave);
    $cmd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $sql = "SELECT `id_per_mod`, `id_usuario`, `id_modulo` FROM `seg_permisos_modulos`";
    $rs = $cmd->query($sql);
    $pmodulos = $rs->fetchAll();
    $cmd = null;
} catch (PDOException $e) {
    echo $e->getCode() == 2002 ? 'Sin Conexión a Mysql (Error: 2002)' : 'Error: ' . $e->getMessage();
}
?>
<script>
    var setIdioma = {
        "decimal": "",
        "emptyTable": "No hay información",
        "info": "Mostrando _START_ - _END_ registros de _TOTAL_ ",
        "infoEmpty": "Mostrando 0 to 0 of 0 Entradas",
        "infoFiltered": "(Filtrado de _MAX_ entradas en total )",
        "infoPostFix": "",
        "thousands": ",",
        "lengthMenu": "Ver _MENU_ Filas",
        "loadingRecords": "Cargando...",
        "processing": "Procesando...",
        "search": '<i class="fas fa-search fa-flip-horizontal" style="font-size:1.5rem; color:#2ECC71;"></i>',
        "zeroRecords": "No se encontraron registros",
        "paginate": {
            "first": "&#10096&#10096",
            "last": "&#10097&#10097",
            "next": "&#10097",
            "previous": "&#10096"
        }
    };
    var setdom = "<'row'<'col-md-6'l><'col-md-6'f>>" +
        "<'row'<'col-sm-12'tr>>" +
        "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
    $('#dataTablePermiso').DataTable({
        language: setIdioma,
        "pageLength": 10,
        "order": [
            [0, "asc"]
        ]
    });
    $('#dataTableModulos').DataTable({
        language: setIdioma,
        "pageLength": 10,
        "order": [
            [0, "asc"]
        ]
    });
    $('#dataTablePermiso').wrap('<div class="overflow" />');
    $("#dataTablePermiso_length").addClass("text-left");
    $('#dataTableModulos').wrap('<div class="overflow" />');
    $("#dataTableModulos_length").addClass("text-left");
</script>
<div class="px-0">
    <div class="shadow mb-3">
        <div class="card-header" style="background-color: #16a085 !important;">
            <h5 style="color: white;"><i class="fas fa-user-lock fa-lg" style="color:#2FDA49"></i>ACTUALIZAR PERMISOS DE USUARIOS DEL SISTEMA</h5>
        </div>

        <div class="p-3">
            <nav>
                <div class="nav nav-tabs" id="nav-tab" role="tablist">
                    <a class="nav-item nav-link active small" id="crud-tab" data-toggle="tab" href="#crud" role="tab" aria-controls="crud" aria-selected="true">CRUD</a>
                    <a class="nav-item nav-link small" id="modulos-tab" data-toggle="tab" href="#modulos" role="tab" aria-controls="modulos" aria-selected="false">MÓDULOS</a>
                    <a class="nav-item nav-link small" id="opciones-tab" data-toggle="tab" href="#opciones" role="tab" aria-controls="opciones" aria-selected="false">OPCIONES</a>
                </div>
            </nav>
            <div class="tab-content pt-2" id="nav-tabContent">
                <div class="tab-pane fade show active" id="crud" role="tabpanel" aria-labelledby="crud-tab">
                    <table id="dataTablePermiso" class="table-striped table-bordered table-sm nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th rowspan="2" style="vertical-align:middle" class="text-center">Nombres</th>
                                <th rowspan="2" style="vertical-align:middle" class="text-center">Usuario</th>
                                <th colspan="4" style="vertical-align:middle" class="text-center">Permisos</th>
                            </tr>
                            <tr>
                                <th class="text-center">Listar</th>
                                <th class="text-center">Registrar</th>
                                <th class="text-center">Editar</th>
                                <th class="text-center">Borrar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($objs as $o) {
                            ?>
                                <tr>
                                    <td><?php echo mb_strtoupper($o['nombre1'] . ' ' . $o['apellido1']) ?></td>
                                    <td><?php echo mb_strtoupper($o['login']) ?></td>
                                    <td class="text-center">
                                        <?php if ($o['listar'] == '1') { ?>
                                            <button class="btn-estado">
                                                <span class="fas fa-check-circle fa-lg circle-verde listar" value="SI|<?php echo $o['id_permiso'] ?>|L"></span>
                                            </button>
                                        <?php } else { ?>
                                            <button class="btn-estado">
                                                <span class="fas fa-times-circle fa-lg circle-rojo listar" value="NO|<?php echo $o['id_permiso'] ?>|L"></span>
                                            </button>
                                        <?php } ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($o['registrar'] == '1') { ?>
                                            <button class="btn-estado">
                                                <span class="fas fa-check-circle fa-lg circle-verde registrar" value="SI|<?php echo $o['id_permiso'] ?>|R"></span>
                                            </button>
                                        <?php } else { ?>
                                            <button class="btn-estado">
                                                <span class="fas fa-times-circle fa-lg circle-rojo registrar" value="NO|<?php echo $o['id_permiso'] ?>|R"></span>
                                            </button>
                                        <?php } ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($o['editar'] == '1') { ?>
                                            <button class="btn-estado">
                                                <span class="fas fa-check-circle fa-lg circle-verde editar" value="SI|<?php echo $o['id_permiso'] ?>|E"></span>
                                            </button>
                                        <?php } else { ?>
                                            <button class="btn-estado">
                                                <span class="fas fa-times-circle fa-lg circle-rojo editar" value="NO|<?php echo $o['id_permiso'] ?>|E"></span>
                                            </button>
                                        <?php } ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($o['borrar'] == '1') { ?>
                                            <button class="btn-estado">
                                                <span class="fas fa-check-circle fa-lg circle-verde borrar" value="SI|<?php echo $o['id_permiso'] ?>|B"></span>
                                            </button>
                                        <?php } else { ?>
                                            <button class="btn-estado">
                                                <span class="fas fa-times-circle fa-lg circle-rojo borrar" value="NO|<?php echo $o['id_permiso'] ?>|B"></span>
                                            </button>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="modulos" role="tabpanel" aria-labelledby="modulos-tab">
                    <table id="dataTableModulos" class="table-striped table-bordered table-sm nowrap" style="width:100%">
                        <thead>
                            <tr>
                                <th rowspan="2" style="vertical-align:middle" class="text-center">Nombres</th>
                                <th rowspan="2" style="vertical-align:middle" class="text-center">Usuario</th>
                                <th colspan="8" style="vertical-align:middle" class="text-center">Módulos</th>
                            </tr>
                            <tr>
                                <td class="text-center">
                                    <div class="div-icono">
                                        <span class="fas fa-calculator" style="color: #2ECC71CC;" aria-hidden="true" title="NÓMINA"></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="div-icono">
                                        <span class="fas fa-people-arrows" style="color: #2874A6;" aria-hidden="true" title="TERCEROS"></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="div-icono">
                                        <span class="fas fa-file-signature" style="color: #A569BD;" aria-hidden="true" title="CONTRATACIÓN"></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="div-icono">
                                        <span class="fas fa-chart-pie" style="color: #FF5733;" aria-hidden="true" title="PRESUPUESTO"></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="div-icono">
                                        <span class="fas fa-file-invoice-dollar" style="color: #45B39D;" aria-hidden="true" title="CONTABILIDAD"></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="div-icono">
                                        <span class="fas fa-coins" style="color: #3498DB;" aria-hidden="true" title="TESORERÍA"></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="div-icono">
                                        <span class="fas fa-store" style="color: #82E0AA;" aria-hidden="true" title="ALMACÉN"></span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="div-icono">
                                        <span class="fas fa-laptop-house" style="color: #D2B4DE;" aria-hidden="true" title="ACTIVOS FIJOS"></span>
                                    </div>
                                </td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($objs as $o) {
                                $mods_user = [];
                                foreach ($pmodulos as $pm) {
                                    if ($o['id_usuario'] == $pm['id_usuario']) {
                                        $mods_user[] = $pm['id_modulo'];
                                    }
                                }
                            ?>
                                <tr>
                                    <td><?php echo mb_strtoupper($o['nombre1'] . ' ' . $o['apellido1']) ?></td>
                                    <td><?php echo mb_strtoupper($o['login']) ?></td>
                                    <?php
                                    for ($i = 1; $i <= 8; $i++) {
                                        $key = array_search($i, $mods_user);
                                        if ($key !== false) {
                                            $estado = 1;
                                            $icono = 'fa-check-circle';
                                            $color = 'circle-verde';
                                        } else {
                                            $estado = 0;
                                            $icono = 'fa-times-circle';
                                            $color = 'circle-rojo';
                                        }
                                        echo '<td class="text-center"><a class="btn-circle shadow" href="#"><span class="fas ' . $icono . ' fa-lg ' . $color . '" value="' . $o['id_usuario'] . '|' . $i . '|' . $estado . '"></span></a></td>';
                                    }
                                    ?>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div class="tab-pane fade" id="opciones" role="tabpanel" aria-labelledby="opciones-tab">
                    <div class="form-row">
                        <div class="form-group col-md-12">
                            <label for="txtBuscaUser" class="small">Usuario</label>
                            <input type="text" class="form-control form-control-sm" id="txtBuscaUser" name="txtBuscaUser" placeholder="Buscar">
                            <input type="hidden" id="numIdUser" name="numIdUser" value="0">
                        </div>
                    </div>
                    <div id="divPermisOpc">

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="text-right">
        <button type="button" class="btn btn-primary" data-dismiss="modal" style="width: 10rem;">Cerrar</button>
    </div>
</div>