CREATE TABLE `data_rpd_tujuan` (
  `id` int(11) NOT NULL,
  `head_teks` text DEFAULT NULL,
  `id_misi_old` int(11) DEFAULT NULL,
  `id_tujuan` int(11) DEFAULT NULL,
  `id_unik` text DEFAULT NULL,
  `id_unik_indikator` text DEFAULT NULL,
  `indikator_teks` text DEFAULT NULL,
  `is_locked` tinyint(4) DEFAULT NULL,
  `is_locked_indikator` tinyint(4) DEFAULT NULL,
  `isu_teks` text DEFAULT NULL,
  `kebijakan_teks` text DEFAULT NULL,
  `misi_lock` tinyint(4) DEFAULT NULL,
  `misi_teks` text DEFAULT NULL,
  `saspok_teks` text DEFAULT NULL,
  `satuan` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `target_1` text DEFAULT NULL,
  `target_2` text DEFAULT NULL,
  `target_3` text DEFAULT NULL,
  `target_4` text DEFAULT NULL,
  `target_5` text DEFAULT NULL,
  `target_akhir` text DEFAULT NULL,
  `target_awal` text DEFAULT NULL,
  `tujuan_teks` text DEFAULT NULL,
  `urut_misi` tinyint(4) DEFAULT NULL,
  `urut_saspok` tinyint(4) DEFAULT NULL,
  `urut_tujuan` tinyint(4) DEFAULT NULL,
  `visi_teks` text DEFAULT NULL,
  `active` tinyint(4) NOT NULL,
  `update_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `data_rpd_tujuan`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `data_rpd_tujuan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `data_rpd_sasaran` (
  `id` int(11) NOT NULL,
  `head_teks` text DEFAULT NULL,
  `id_misi_old` int(11) DEFAULT NULL,
  `id_sasaran` int(11) DEFAULT NULL,
  `id_unik` text DEFAULT NULL,
  `id_unik_indikator` text DEFAULT NULL,
  `indikator_teks` text DEFAULT NULL,
  `is_locked` tinyint(4) DEFAULT NULL,
  `is_locked_indikator` tinyint(4) DEFAULT NULL,
  `isu_teks` text DEFAULT NULL,
  `kebijakan_teks` text DEFAULT NULL,
  `kode_tujuan` text DEFAULT NULL,
  `misi_lock` tinyint(4) DEFAULT NULL,
  `misi_teks` text DEFAULT NULL,
  `sasaran_teks` text DEFAULT NULL,
  `saspok_teks` text DEFAULT NULL,
  `satuan` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `target_1` text DEFAULT NULL,
  `target_2` text DEFAULT NULL,
  `target_3` text DEFAULT NULL,
  `target_4` text DEFAULT NULL,
  `target_5` text DEFAULT NULL,
  `target_akhir` text DEFAULT NULL,
  `target_awal` text DEFAULT NULL,
  `tujuan_lock` tinyint(4) DEFAULT NULL,
  `tujuan_teks` text DEFAULT NULL,
  `urut_misi` text DEFAULT NULL,
  `urut_sasaran` text DEFAULT NULL,
  `urut_saspok` text DEFAULT NULL,
  `urut_tujuan` text DEFAULT NULL,
  `visi_teks` text DEFAULT NULL,
  `active` tinyint(4) NOT NULL,
  `update_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `data_rpd_sasaran`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `data_rpd_sasaran`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `data_rpd_program` (
  `id` int(11) NOT NULL,
  `head_teks` text DEFAULT NULL,
  `id_bidur_mth` int(11) DEFAULT NULL,
  `id_misi_old` int(11) DEFAULT NULL,
  `id_program` int(11) DEFAULT NULL,
  `id_program_mth` int(11) DEFAULT NULL,
  `id_unik` text DEFAULT NULL,
  `id_unik_indikator` text DEFAULT NULL,
  `id_unit` int(11) DEFAULT NULL,
  `indikator` text DEFAULT NULL,
  `is_locked` tinyint(4) DEFAULT NULL,
  `is_locked_indikator` tinyint(4) DEFAULT NULL,
  `isu_teks` text DEFAULT NULL,
  `kebijakan_teks` text DEFAULT NULL,
  `kode_sasaran` text DEFAULT NULL,
  `kode_skpd` text DEFAULT NULL,
  `kode_tujuan` text DEFAULT NULL,
  `misi_lock` text DEFAULT NULL,
  `misi_teks` tinyint(4) DEFAULT NULL,
  `nama_program` text DEFAULT NULL,
  `nama_skpd` text DEFAULT NULL,
  `pagu_1` double(20, 0) DEFAULT NULL,
  `pagu_2` double(20, 0) DEFAULT NULL,
  `pagu_3` double(20, 0) DEFAULT NULL,
  `pagu_4` double(20, 0) DEFAULT NULL,
  `pagu_5` double(20, 0) DEFAULT NULL,
  `program_lock` tinyint(4) DEFAULT NULL,
  `sasaran_lock` tinyint(4) DEFAULT NULL,
  `sasaran_teks` text DEFAULT NULL,
  `saspok_teks` text DEFAULT NULL,
  `satuan` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `target_1` text DEFAULT NULL,
  `target_2` text DEFAULT NULL,
  `target_3` text DEFAULT NULL,
  `target_4` text DEFAULT NULL,
  `target_5` text DEFAULT NULL,
  `target_akhir` text DEFAULT NULL,
  `target_awal` text DEFAULT NULL,
  `tujuan_lock` tinyint(4) DEFAULT NULL,
  `tujuan_teks` text DEFAULT NULL,
  `urut_misi` tinyint(4) DEFAULT NULL,
  `urut_sasaran` tinyint(4) DEFAULT NULL,
  `urut_saspok` tinyint(4) DEFAULT NULL,
  `urut_tujuan` tinyint(4) DEFAULT NULL,
  `visi_teks` text DEFAULT NULL,
  `active` tinyint(4) NOT NULL,
  `update_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `data_rpd_program`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `data_rpd_program`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `data_rpd_tujuan_lokal` (
  `id` int(11) NOT NULL,
  `head_teks` text DEFAULT NULL,
  `id_misi_old` int(11) DEFAULT NULL,
  `id_tujuan` int(11) DEFAULT NULL,
  `id_unik` text DEFAULT NULL,
  `id_unik_indikator` text DEFAULT NULL,
  `indikator_teks` text DEFAULT NULL,
  `is_locked` tinyint(4) DEFAULT NULL,
  `is_locked_indikator` tinyint(4) DEFAULT NULL,
  `isu_teks` text DEFAULT NULL,
  `kebijakan_teks` text DEFAULT NULL,
  `misi_lock` tinyint(4) DEFAULT NULL,
  `misi_teks` text DEFAULT NULL,
  `saspok_teks` text DEFAULT NULL,
  `satuan` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `target_1` text DEFAULT NULL,
  `target_2` text DEFAULT NULL,
  `target_3` text DEFAULT NULL,
  `target_4` text DEFAULT NULL,
  `target_5` text DEFAULT NULL,
  `target_akhir` text DEFAULT NULL,
  `target_awal` text DEFAULT NULL,
  `tujuan_teks` text DEFAULT NULL,
  `urut_misi` tinyint(4) DEFAULT NULL,
  `urut_saspok` tinyint(4) DEFAULT NULL,
  `urut_tujuan` tinyint(4) DEFAULT NULL,
  `visi_teks` text DEFAULT NULL,
  `update_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `data_rpd_tujuan_lokal`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `data_rpd_tujuan_lokal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `data_rpd_sasaran_lokal` (
  `id` int(11) NOT NULL,
  `head_teks` text DEFAULT NULL,
  `id_misi_old` int(11) DEFAULT NULL,
  `id_sasaran` int(11) DEFAULT NULL,
  `id_unik` text DEFAULT NULL,
  `id_unik_indikator` text DEFAULT NULL,
  `indikator_teks` text DEFAULT NULL,
  `is_locked` tinyint(4) DEFAULT NULL,
  `is_locked_indikator` tinyint(4) DEFAULT NULL,
  `isu_teks` text DEFAULT NULL,
  `kebijakan_teks` text DEFAULT NULL,
  `kode_tujuan` text DEFAULT NULL,
  `misi_lock` tinyint(4) DEFAULT NULL,
  `misi_teks` text DEFAULT NULL,
  `sasaran_teks` text DEFAULT NULL,
  `saspok_teks` text DEFAULT NULL,
  `satuan` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `target_1` text DEFAULT NULL,
  `target_2` text DEFAULT NULL,
  `target_3` text DEFAULT NULL,
  `target_4` text DEFAULT NULL,
  `target_5` text DEFAULT NULL,
  `target_akhir` text DEFAULT NULL,
  `target_awal` text DEFAULT NULL,
  `tujuan_lock` tinyint(4) DEFAULT NULL,
  `tujuan_teks` text DEFAULT NULL,
  `urut_misi` text DEFAULT NULL,
  `urut_sasaran` text DEFAULT NULL,
  `urut_saspok` text DEFAULT NULL,
  `urut_tujuan` text DEFAULT NULL,
  `visi_teks` text DEFAULT NULL,
  `update_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `data_rpd_sasaran_lokal`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `data_rpd_sasaran_lokal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `data_rpd_program_lokal` (
  `id` int(11) NOT NULL,
  `head_teks` text DEFAULT NULL,
  `id_bidur_mth` int(11) DEFAULT NULL,
  `id_misi_old` int(11) DEFAULT NULL,
  `id_program` int(11) DEFAULT NULL,
  `id_program_mth` int(11) DEFAULT NULL,
  `id_unik` text DEFAULT NULL,
  `id_unik_indikator` text DEFAULT NULL,
  `id_unit` int(11) DEFAULT NULL,
  `indikator` text DEFAULT NULL,
  `is_locked` tinyint(4) DEFAULT NULL,
  `is_locked_indikator` tinyint(4) DEFAULT NULL,
  `isu_teks` text DEFAULT NULL,
  `kebijakan_teks` text DEFAULT NULL,
  `kode_sasaran` text DEFAULT NULL,
  `kode_skpd` text DEFAULT NULL,
  `kode_tujuan` text DEFAULT NULL,
  `misi_lock` text DEFAULT NULL,
  `misi_teks` tinyint(4) DEFAULT NULL,
  `nama_program` text DEFAULT NULL,
  `nama_skpd` text DEFAULT NULL,
  `pagu_1` double(20, 0) DEFAULT NULL,
  `pagu_2` double(20, 0) DEFAULT NULL,
  `pagu_3` double(20, 0) DEFAULT NULL,
  `pagu_4` double(20, 0) DEFAULT NULL,
  `pagu_5` double(20, 0) DEFAULT NULL,
  `program_lock` tinyint(4) DEFAULT NULL,
  `sasaran_lock` tinyint(4) DEFAULT NULL,
  `sasaran_teks` text DEFAULT NULL,
  `saspok_teks` text DEFAULT NULL,
  `satuan` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `target_1` text DEFAULT NULL,
  `target_2` text DEFAULT NULL,
  `target_3` text DEFAULT NULL,
  `target_4` text DEFAULT NULL,
  `target_5` text DEFAULT NULL,
  `target_akhir` text DEFAULT NULL,
  `target_awal` text DEFAULT NULL,
  `tujuan_lock` tinyint(4) DEFAULT NULL,
  `tujuan_teks` text DEFAULT NULL,
  `urut_misi` tinyint(4) DEFAULT NULL,
  `urut_sasaran` tinyint(4) DEFAULT NULL,
  `urut_saspok` tinyint(4) DEFAULT NULL,
  `urut_tujuan` tinyint(4) DEFAULT NULL,
  `visi_teks` text DEFAULT NULL,
  `update_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `data_rpd_program_lokal`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `data_rpd_program_lokal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `data_rpd_tujuan_history` (
  `id` int(11) NOT NULL,
  `head_teks` text DEFAULT NULL,
  `id_misi_old` int(11) DEFAULT NULL,
  `id_tujuan` int(11) DEFAULT NULL,
  `id_unik` text DEFAULT NULL,
  `id_unik_indikator` text DEFAULT NULL,
  `indikator_teks` text DEFAULT NULL,
  `is_locked` tinyint(4) DEFAULT NULL,
  `is_locked_indikator` tinyint(4) DEFAULT NULL,
  `isu_teks` text DEFAULT NULL,
  `kebijakan_teks` text DEFAULT NULL,
  `misi_lock` tinyint(4) DEFAULT NULL,
  `misi_teks` text DEFAULT NULL,
  `saspok_teks` text DEFAULT NULL,
  `satuan` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `target_1` text DEFAULT NULL,
  `target_2` text DEFAULT NULL,
  `target_3` text DEFAULT NULL,
  `target_4` text DEFAULT NULL,
  `target_5` text DEFAULT NULL,
  `target_akhir` text DEFAULT NULL,
  `target_awal` text DEFAULT NULL,
  `tujuan_teks` text DEFAULT NULL,
  `urut_misi` tinyint(4) DEFAULT NULL,
  `urut_saspok` tinyint(4) DEFAULT NULL,
  `urut_tujuan` tinyint(4) DEFAULT NULL,
  `visi_teks` text DEFAULT NULL,
  `active` tinyint(4) NOT NULL,
  `update_at` datetime NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `id_asli` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `data_rpd_tujuan_history`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `data_rpd_tujuan_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `data_rpd_sasaran_history` (
  `id` int(11) NOT NULL,
  `head_teks` text DEFAULT NULL,
  `id_misi_old` int(11) DEFAULT NULL,
  `id_sasaran` int(11) DEFAULT NULL,
  `id_unik` text DEFAULT NULL,
  `id_unik_indikator` text DEFAULT NULL,
  `indikator_teks` text DEFAULT NULL,
  `is_locked` tinyint(4) DEFAULT NULL,
  `is_locked_indikator` tinyint(4) DEFAULT NULL,
  `isu_teks` text DEFAULT NULL,
  `kebijakan_teks` text DEFAULT NULL,
  `kode_tujuan` text DEFAULT NULL,
  `misi_lock` tinyint(4) DEFAULT NULL,
  `misi_teks` text DEFAULT NULL,
  `sasaran_teks` text DEFAULT NULL,
  `saspok_teks` text DEFAULT NULL,
  `satuan` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `target_1` text DEFAULT NULL,
  `target_2` text DEFAULT NULL,
  `target_3` text DEFAULT NULL,
  `target_4` text DEFAULT NULL,
  `target_5` text DEFAULT NULL,
  `target_akhir` text DEFAULT NULL,
  `target_awal` text DEFAULT NULL,
  `tujuan_lock` tinyint(4) DEFAULT NULL,
  `tujuan_teks` text DEFAULT NULL,
  `urut_misi` text DEFAULT NULL,
  `urut_sasaran` text DEFAULT NULL,
  `urut_saspok` text DEFAULT NULL,
  `urut_tujuan` text DEFAULT NULL,
  `visi_teks` text DEFAULT NULL,
  `active` tinyint(4) NOT NULL,
  `update_at` datetime NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `id_asli` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `data_rpd_sasaran_history`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `data_rpd_sasaran_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `data_rpd_program_history` (
  `id` int(11) NOT NULL,
  `head_teks` text DEFAULT NULL,
  `id_bidur_mth` int(11) DEFAULT NULL,
  `id_misi_old` int(11) DEFAULT NULL,
  `id_program` int(11) DEFAULT NULL,
  `id_program_mth` int(11) DEFAULT NULL,
  `id_unik` text DEFAULT NULL,
  `id_unik_indikator` text DEFAULT NULL,
  `id_unit` int(11) DEFAULT NULL,
  `indikator` text DEFAULT NULL,
  `is_locked` tinyint(4) DEFAULT NULL,
  `is_locked_indikator` tinyint(4) DEFAULT NULL,
  `isu_teks` text DEFAULT NULL,
  `kebijakan_teks` text DEFAULT NULL,
  `kode_sasaran` text DEFAULT NULL,
  `kode_skpd` text DEFAULT NULL,
  `kode_tujuan` text DEFAULT NULL,
  `misi_lock` text DEFAULT NULL,
  `misi_teks` tinyint(4) DEFAULT NULL,
  `nama_program` text DEFAULT NULL,
  `nama_skpd` text DEFAULT NULL,
  `pagu_1` double(20, 0) DEFAULT NULL,
  `pagu_2` double(20, 0) DEFAULT NULL,
  `pagu_3` double(20, 0) DEFAULT NULL,
  `pagu_4` double(20, 0) DEFAULT NULL,
  `pagu_5` double(20, 0) DEFAULT NULL,
  `program_lock` tinyint(4) DEFAULT NULL,
  `sasaran_lock` tinyint(4) DEFAULT NULL,
  `sasaran_teks` text DEFAULT NULL,
  `saspok_teks` text DEFAULT NULL,
  `satuan` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `target_1` text DEFAULT NULL,
  `target_2` text DEFAULT NULL,
  `target_3` text DEFAULT NULL,
  `target_4` text DEFAULT NULL,
  `target_5` text DEFAULT NULL,
  `target_akhir` text DEFAULT NULL,
  `target_awal` text DEFAULT NULL,
  `tujuan_lock` tinyint(4) DEFAULT NULL,
  `tujuan_teks` text DEFAULT NULL,
  `urut_misi` tinyint(4) DEFAULT NULL,
  `urut_sasaran` tinyint(4) DEFAULT NULL,
  `urut_saspok` tinyint(4) DEFAULT NULL,
  `urut_tujuan` tinyint(4) DEFAULT NULL,
  `visi_teks` text DEFAULT NULL,
  `active` tinyint(4) NOT NULL,
  `update_at` datetime NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `id_asli` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `data_rpd_program_history`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `data_rpd_program_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `data_rpd_tujuan_lokal_history` (
  `id` int(11) NOT NULL,
  `head_teks` text DEFAULT NULL,
  `id_misi_old` int(11) DEFAULT NULL,
  `id_tujuan` int(11) DEFAULT NULL,
  `id_unik` text DEFAULT NULL,
  `id_unik_indikator` text DEFAULT NULL,
  `indikator_teks` text DEFAULT NULL,
  `is_locked` tinyint(4) DEFAULT NULL,
  `is_locked_indikator` tinyint(4) DEFAULT NULL,
  `isu_teks` text DEFAULT NULL,
  `kebijakan_teks` text DEFAULT NULL,
  `misi_lock` tinyint(4) DEFAULT NULL,
  `misi_teks` text DEFAULT NULL,
  `saspok_teks` text DEFAULT NULL,
  `satuan` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `target_1` text DEFAULT NULL,
  `target_2` text DEFAULT NULL,
  `target_3` text DEFAULT NULL,
  `target_4` text DEFAULT NULL,
  `target_5` text DEFAULT NULL,
  `target_akhir` text DEFAULT NULL,
  `target_awal` text DEFAULT NULL,
  `tujuan_teks` text DEFAULT NULL,
  `urut_misi` tinyint(4) DEFAULT NULL,
  `urut_saspok` tinyint(4) DEFAULT NULL,
  `urut_tujuan` tinyint(4) DEFAULT NULL,
  `visi_teks` text DEFAULT NULL,
  `update_at` datetime NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `id_asli` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `data_rpd_tujuan_lokal_history`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `data_rpd_tujuan_lokal_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `data_rpd_sasaran_lokal_history` (
  `id` int(11) NOT NULL,
  `head_teks` text DEFAULT NULL,
  `id_misi_old` int(11) DEFAULT NULL,
  `id_sasaran` int(11) DEFAULT NULL,
  `id_unik` text DEFAULT NULL,
  `id_unik_indikator` text DEFAULT NULL,
  `indikator_teks` text DEFAULT NULL,
  `is_locked` tinyint(4) DEFAULT NULL,
  `is_locked_indikator` tinyint(4) DEFAULT NULL,
  `isu_teks` text DEFAULT NULL,
  `kebijakan_teks` text DEFAULT NULL,
  `kode_tujuan` text DEFAULT NULL,
  `misi_lock` tinyint(4) DEFAULT NULL,
  `misi_teks` text DEFAULT NULL,
  `sasaran_teks` text DEFAULT NULL,
  `saspok_teks` text DEFAULT NULL,
  `satuan` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `target_1` text DEFAULT NULL,
  `target_2` text DEFAULT NULL,
  `target_3` text DEFAULT NULL,
  `target_4` text DEFAULT NULL,
  `target_5` text DEFAULT NULL,
  `target_akhir` text DEFAULT NULL,
  `target_awal` text DEFAULT NULL,
  `tujuan_lock` tinyint(4) DEFAULT NULL,
  `tujuan_teks` text DEFAULT NULL,
  `urut_misi` text DEFAULT NULL,
  `urut_sasaran` text DEFAULT NULL,
  `urut_saspok` text DEFAULT NULL,
  `urut_tujuan` text DEFAULT NULL,
  `visi_teks` text DEFAULT NULL,
  `update_at` datetime NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `id_asli` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `data_rpd_sasaran_lokal_history`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `data_rpd_sasaran_lokal_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

CREATE TABLE `data_rpd_program_lokal_history` (
  `id` int(11) NOT NULL,
  `head_teks` text DEFAULT NULL,
  `id_bidur_mth` int(11) DEFAULT NULL,
  `id_misi_old` int(11) DEFAULT NULL,
  `id_program` int(11) DEFAULT NULL,
  `id_program_mth` int(11) DEFAULT NULL,
  `id_unik` text DEFAULT NULL,
  `id_unik_indikator` text DEFAULT NULL,
  `id_unit` int(11) DEFAULT NULL,
  `indikator` text DEFAULT NULL,
  `is_locked` tinyint(4) DEFAULT NULL,
  `is_locked_indikator` tinyint(4) DEFAULT NULL,
  `isu_teks` text DEFAULT NULL,
  `kebijakan_teks` text DEFAULT NULL,
  `kode_sasaran` text DEFAULT NULL,
  `kode_skpd` text DEFAULT NULL,
  `kode_tujuan` text DEFAULT NULL,
  `misi_lock` text DEFAULT NULL,
  `misi_teks` tinyint(4) DEFAULT NULL,
  `nama_program` text DEFAULT NULL,
  `nama_skpd` text DEFAULT NULL,
  `pagu_1` double(20, 0) DEFAULT NULL,
  `pagu_2` double(20, 0) DEFAULT NULL,
  `pagu_3` double(20, 0) DEFAULT NULL,
  `pagu_4` double(20, 0) DEFAULT NULL,
  `pagu_5` double(20, 0) DEFAULT NULL,
  `program_lock` tinyint(4) DEFAULT NULL,
  `sasaran_lock` tinyint(4) DEFAULT NULL,
  `sasaran_teks` text DEFAULT NULL,
  `saspok_teks` text DEFAULT NULL,
  `satuan` text DEFAULT NULL,
  `status` text DEFAULT NULL,
  `target_1` text DEFAULT NULL,
  `target_2` text DEFAULT NULL,
  `target_3` text DEFAULT NULL,
  `target_4` text DEFAULT NULL,
  `target_5` text DEFAULT NULL,
  `target_akhir` text DEFAULT NULL,
  `target_awal` text DEFAULT NULL,
  `tujuan_lock` tinyint(4) DEFAULT NULL,
  `tujuan_teks` text DEFAULT NULL,
  `urut_misi` tinyint(4) DEFAULT NULL,
  `urut_sasaran` tinyint(4) DEFAULT NULL,
  `urut_saspok` tinyint(4) DEFAULT NULL,
  `urut_tujuan` tinyint(4) DEFAULT NULL,
  `visi_teks` text DEFAULT NULL,
  `update_at` datetime NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `id_asli` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
ALTER TABLE `data_rpd_program_lokal_history`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `data_rpd_program_lokal_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;