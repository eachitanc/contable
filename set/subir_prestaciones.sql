INSERT INTO `seg_nominas` (`id_nomina`, `descripcion`,`mes`,`vigencia`,`tipo`,`estado`,`planilla`,`fec_reg`) VALUES
(1,"LIQUIDACIÓN PRESTACIONES SOCIALES", "01", 2023, "PS",5,5,"2023-01-31 11:02:00");
INSERT INTO `seg_liq_bsp`(`id_empleado`,`val_bsp`,`id_user_reg`,`mes`,`anio`,`fec_reg`,`id_nomina`)VALUES
("5","500000","1","01","2023","2023-01-31 11:02:00","1"),
("6","500000","1","01","2023","2023-01-31 11:02:00","1"),
("16","460000","1","01","2023","2023-01-31 11:02:00","1"),
("19","500000","1","01","2023","2023-01-31 11:02:00","1"),
("20","979999","1","01","2023","2023-01-31 11:02:00","1"),
("27","1000000","1","01","2023","2023-01-31 11:02:00","1");
INSERT INTO `seg_liq_prima`(`id_empleado`,`cant_dias`,`val_liq_ps`,`periodo`,`fec_reg`,`id_nomina`, `corte`)VALUES
(5,360,282714,1,"2023-01-31 11:02:00",1,"2023-01-31"),
(6,360,307878,1,"2023-01-31 11:02:00",1,"2023-01-31"),
(16,360,0,1,"2023-01-31 11:02:00",1,"2023-01-31"),
(19,360,118956,1,"2023-01-31 11:02:00",1,"2022-10-10"),
(20,360,969969,1,"2023-01-31 11:02:00",1,"2023-03-14"),
(27,360,108142,1,"2023-01-31 11:02:00",1,"2022-10-10");
INSERT INTO `seg_liq_prima_nav`(`id_empleado`,`cant_dias`,`val_liq_pv`,`periodo`,`anio`,`fec_reg`,`id_nomina`,`corte`)VALUES
(2,360,1073436,2,2022,"2022-12-31 14:57:01",NULL,"2022-12-31"),
(3,360,1013801,2,2022,"2022-12-31 14:57:01",NULL,"2022-12-31"),
(7,360,2766288,2,2022,"2022-12-06 11:52:37",NULL,"2022-12-06"),
(8,360,360794,2,2022,"2022-12-31 11:52:37",NULL,"2022-12-31"),
(9,360,4820655,2,2022,"2022-12-31 11:52:37",NULL,"2022-12-31"),
(11,360,4798122,2,2022,"2022-12-31 11:52:37",NULL,"2022-12-31"),
(12,360,1086847,2,2022,"2022-12-31 11:52:37",NULL,"2022-12-31"),
(13,360,3834385,2,2022,"2022-12-31 11:52:37",NULL,"2022-12-31"),
(14,360,5698321,2,2022,"2022-12-31 11:52:37",NULL,"2022-12-31"),
(15,360,2875462,2,2022,"2022-12-31 11:52:37",NULL,"2022-12-31"),
(17,360,1910317,2,2022,"2022-12-31 11:52:37",NULL,"2022-12-31"),
(18,360,4746236,2,2022,"2022-12-31 11:52:37",NULL,"2022-12-31"),
(22,360,2766119,2,2022,"2022-12-31 11:52:37",NULL,"2022-12-31"),
(23,360,454248,2,2023,"2023-03-15 00:00:00",NULL,"2023-03-15"),
(24,360,1057932,2,2022,"2022-12-31 11:52:37",NULL,"2022-12-31"),
(25,360,2483061,2,2022,"2022-12-31 11:52:37",NULL,"2022-12-31"),
(26,360,1046045,2,2022,"2022-12-31 11:52:37",NULL,"2022-12-31"),
(28,360,1456162,2,2022,"2022-12-31 14:57:01",NULL,"2022-12-31"),
(31,360,48896,2,2023,"2023-02-13 08:56:56",NULL,"2023-02-13"),
(32,360,14493,2,2023,"2023-01-31 10:04:36",NULL,"2023-01-31");

INSERT INTO `seg_vacaciones`(`id_vac`,`id_empleado`,`anticipo`,`fec_inicial`,`fec_inicio`,`fec_fin`,`dias_inactivo`,`dias_habiles`,`dias_liquidar`,`estado`,`fec_reg`)VALUES
(17,29,2,"2022-01-27","2022-01-27","2022-02-18,22",15,360,2,"2022-01-27"),
(18,9,2,"2022-08-02","2022-08-02","2022-08-24,22",15,360,2,"2022-08-02"),
(19,14,2,"2023-01-17","2023-01-17","2023-02-08,22",15,360,2,"2023-01-17"),
(20,25,2,"2023-01-31","2023-01-31","2023-02-22,22",15,360,2,"2023-01-31"),
(21,32,2,"2023-01-31","2023-01-31","2023-02-22,22",15,360,2,"2023-01-31"),
(22,31,2,"2023-02-13","2023-02-13","2023-03-07,22",15,360,2,"2023-02-13"),
(23,15,2,"2023-02-16","2023-02-16","2023-03-10,22",15,360,2,"2023-02-16"),
(24,22,2,"2023-02-16","2023-02-16","2023-03-10,22",15,360,2,"2023-02-16"),
(25,11,2,"2023-02-23","2023-02-23","2023-03-17,22",15,360,2,"2023-02-23"),
(26,7,2,"2023-03-15","2023-03-15","2023-04-06,22",15,360,2,"2023-03-15"),
(27,23,2,"2023-03-15","2023-03-15","2023-04-06,22",15,360,2,"2023-03-15"),
(28,13,2,"2023-05-10","2023-05-10","2023-06-01,22",15,360,2,"2023-05-10");
INSERT INTO `seg_liq_vac`(`id_vac`,`dias_liqs`,`val_liq`,`val_prima_vac`,`val_bon_recrea`,`anio_vac`,`fec_reg`, `mes_vac`)VALUES
(17,503321,503321,65740,2022,"2022-01-27","01"),
(18,4570197,4570197,285285,2022,"2022-08-02","08"),
(19,3443241,2718348,341204,2023,"2023-01-17","01"),
(20,1572605,1241531,154761,2023,"2023-01-31","01"),
(21,555664,555664,71188,2023,"2023-01-31","01"),
(22,590485,590485,71979,2023,"2023-02-13","02"),
(23,2031747,1385282,172668,2023,"2023-02-16","02"),
(24,1751876,1383060,172668,2023,"2023-02-16","02"),
(25,3204029,2288592,285285,2023,"2023-02-23","02"),
(26,2401155,1385282,172668,2023,"2023-03-15","03"),
(27,1777492,1777492,229920,2023,"2023-03-15","03"),
(28,2588932,1849237,229920,2023,"2023-05-10","05");

INSERT INTO `seg_liq_cesantias`(`id_empleado`,`cant_dias`,`val_cesantias`,`val_icesantias`,`porcentaje_interes`,`anio`,`fec_reg`,`id_nomina`, `corte`)VALUES
(5,360,1394058,167287,12,2023,"2023-01-31 11:02:00",1,"2023-01-31"),
(6,360,1345342,161441,12,2023,"2023-01-31 11:02:00",1,"2023-01-31"),
(16,360,0,0,12,2023,"2023-01-31 11:02:00",1,"2023-01-31"),
(19,360,1044122,125295,12,2023,"2023-01-31 11:02:00",1,"2022-10-10"),
(20,360,609401,73128,12,2023,"2023-01-31 11:02:00",1,"2023-03-14"),
(27,360,1022342,122681,12,2023,"2023-01-31 11:02:00",1,"2022-10-10");
INSERT INTO `seg_liq_salario` (`id_empleado`,`forma_pago`,`metodo_pago`,`val_liq`,`anio`,`fec_reg`,`id_nomina`)VALUES
(5,1,47,3527561,2023,"2023-01-31 11:02:00",1),
(6,1,47,3543348,2023,"2023-01-31 11:02:00",1),
(16,1,47,460000,2023,"2023-01-31 11:02:00",1),
(19,1,47,3759768,2023,"2023-01-31 11:02:00",1),
(20,1,47,6172142,2023,"2023-01-31 11:02:00",1),
(27,1,47,5386439,2023,"2023-01-31 11:02:00",1);
INSERT INTO `seg_liq_dlab_auxt`(`id_empleado`, `val_liq_dias`, `val_liq_auxt`, `aux_alim`, `g_representa`, `horas_ext`, `id_nomina`)VALUES
(5,0,0,0,0,0,1),
(6,0,0,0,0,0,1),
(16,0,0,0,0,0,1),
(19,0,0,0,0,0,1),
(20,0,0,0,0,0,1),
(27,0,0,0,0,0,1);
