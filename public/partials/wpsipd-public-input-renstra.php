<?php 
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}
global $wpdb;
$input = shortcode_atts( array(
	'id_skpd' => '',
	'tahun_anggaran' => get_option('_crb_tahun_anggaran_sipd')
), $atts );

function button_edit_monev($class=false){
	$ret = ' <span style="display: none;" data-id="'.$class.'" class="edit-monev"><i class="dashicons dashicons-edit"></i></span>';
	return $ret;
}

function get_target($target, $satuan){
	if(empty($satuan)){
		return $target;
	}else{
		$target = explode($satuan, $target);
		return $target[0];
	}
}

function parsing_nama_kode($nama_kode){
	$nama_kodes = explode('||', $nama_kode);
	$nama = $nama_kodes[0];
	unset($nama_kodes[0]);
	return $nama.'<span class="debug-kode">||'.implode('||', $nama_kodes).'</span>';
}

$api_key = get_option('_crb_api_key_extension' );
$tahun_anggaran = $input['tahun_anggaran'];

$awal_renstra = 0;
$namaJadwal = '-';
$mulaiJadwal = '-';
$selesaiJadwal = '-';
$relasi_perencanaan = '-';
$id_tipe_relasi = '-';

$jadwal_lokal = $wpdb->get_results("SELECT a.*, (SELECT id_tipe FROM data_jadwal_lokal WHERE id_jadwal_lokal=a.relasi_perencanaan) id_tipe_relasi FROM data_jadwal_lokal a WHERE a.id_jadwal_lokal = (SELECT MAX(id_jadwal_lokal) FROM data_jadwal_lokal a WHERE a.id_tipe=4)", ARRAY_A);

$add_renstra = '';
if(!empty($jadwal_lokal)){
	$awal_renstra = $jadwal_lokal[0]['tahun_anggaran'];
	$namaJadwal = $jadwal_lokal[0]['nama'];
	$mulaiJadwal = $jadwal_lokal[0]['waktu_awal'];
	$selesaiJadwal = $jadwal_lokal[0]['waktu_akhir'];
	$relasi_perencanaan = $jadwal_lokal[0]['relasi_perencanaan'] ?? '-';
	$id_tipe_relasi = $jadwal_lokal[0]['id_tipe_relasi'] ?? '-';

	$awal = new DateTime($mulaiJadwal);
	$akhir = new DateTime($selesaiJadwal);
	$now = new DateTime(date('Y-m-d H:i:s'));

	if($now >= $awal && $now <= $akhir){
		$add_renstra = '<a style="margin-left: 10px;" id="tambah-data" onclick="return false;" href="#" class="btn btn-success">Tambah Data RENSTRA</a>';
	}
}

$akhir_renstra = $awal_renstra+5;
$urut = $tahun_anggaran-$awal_renstra;

$timezone = get_option('timezone_string');

$rumus_indikator_db = $wpdb->get_results("SELECT * from data_rumus_indikator where active=1 and tahun_anggaran=".$tahun_anggaran, ARRAY_A);
$rumus_indikator = '';
foreach ($rumus_indikator_db as $k => $v){
	$rumus_indikator .= '<option value="'.$v['id'].'">'.$v['rumus'].'</option>';
}

$where_skpd = '';
if(!empty($input['id_skpd'])){
	$where_skpd = "and id_skpd =".$input['id_skpd'];
}

$sql = $wpdb->prepare("
	select 
		* 
	from data_unit 
	where tahun_anggaran=%d
		".$where_skpd."
		and active=1
	order by id_skpd ASC
", $tahun_anggaran);

$unit = $wpdb->get_results($sql, ARRAY_A);

$judul_skpd = '';
if(!empty($input['id_skpd'])){
	$judul_skpd = $unit[0]['kode_skpd'].'&nbsp;'.$unit[0]['nama_skpd'].'<br>';
}
$nama_pemda = get_option('_crb_daerah');

$current_user = wp_get_current_user();

$body = '';
$bulan = date('m');
$data_all = array(
	'data' => array()
);

$tujuan_ids = array();
$sasaran_ids = array();
$program_ids = array();
$kegiatan_ids = array();
$skpd_filter = array();
$nama_pemda = get_option('_crb_daerah');

ksort($skpd_filter);
$skpd_filter_html = '<option value="">Pilih SKPD</option>';
foreach ($skpd_filter as $kode_skpd => $nama_skpd) {
	$skpd_filter_html .= '<option value="'.$kode_skpd.'">'.$kode_skpd.' '.$nama_skpd.'</option>';
}

$tujuan_all = $wpdb->get_results($wpdb->prepare("
			SELECT 
				* 
			FROM data_renstra_tujuan_lokal 
			WHERE 
				id_unit=%d AND 
				active=1 ORDER BY urut_tujuan",
				$input['id_skpd']), ARRAY_A);

foreach ($tujuan_all as $keyTujuan => $tujuan_value) {
	if(empty($data_all['data'][$tujuan_value['id_unik']])){
			$data_all['data'][$tujuan_value['id_unik']] = [
					'id' => $tujuan_value['id'],
					'id_bidang_urusan' => $tujuan_value['id_bidang_urusan'],
					'id_unik' => $tujuan_value['id_unik'],
					'tujuan_teks' => $tujuan_value['tujuan_teks'],
					'nama_bidang_urusan' => $tujuan_value['nama_bidang_urusan'],
					'indikator' => array(),
					'data' => array()
			];
	}

	if(!empty($tujuan_value['id_unik_indikator'])){
			if(empty($data_all['data'][$tujuan_value['id_unik']]['indikator'][$tujuan_value['id_unik_indikator']])){
					$data_all['data'][$tujuan_value['id_unik']]['indikator'][$tujuan_value['id_unik_indikator']] = [
							'id_unik_indikator' => $tujuan_value['id_unik_indikator'],
							'indikator_teks' => $tujuan_value['indikator_teks'],
							'satuan' => $tujuan_value['satuan'],
							'target_1' => $tujuan_value['target_1'],
							'target_2' => $tujuan_value['target_2'],
							'target_3' => $tujuan_value['target_3'],
							'target_4' => $tujuan_value['target_4'],
							'target_5' => $tujuan_value['target_5'],
							'target_awal' => $tujuan_value['target_awal'],
							'target_akhir' => $tujuan_value['target_akhir'],
					];
			}
	}

	if(empty($tujuan_value['id_unik_indikator'])){
		$sasaran_all = $wpdb->get_results($wpdb->prepare("
			SELECT 
				* 
			FROM data_renstra_sasaran_lokal 
			WHERE 
				kode_tujuan=%s AND 
				active=1 ORDER BY urut_sasaran",
				$tujuan_value['id_unik']), ARRAY_A);

		foreach ($sasaran_all as $keySasaran => $sasaran_value) {
			if(empty($data_all['data'][$tujuan_value['id_unik']]['data'][$sasaran_value['id_unik']])){
					$data_all['data'][$tujuan_value['id_unik']]['data'][$sasaran_value['id_unik']] = [
							'id' => $sasaran_value['id'],
							'id_unik' => $sasaran_value['id_unik'],
							'sasaran_teks' => $sasaran_value['sasaran_teks'],
							'indikator' => array(),
							'data' => array()
					];
			}

			if(!empty($sasaran_value['id_unik_indikator'])){
				if(empty($data_all['data'][$tujuan_value['id_unik']]['data'][$sasaran_value['id_unik']]['indikator'][$sasaran_value['id_unik_indikator']])){
					$data_all['data'][$tujuan_value['id_unik']]['data'][$sasaran_value['id_unik']]['indikator'][$sasaran_value['id_unik_indikator']] = [
									'id_unik_indikator' => $sasaran_value['id_unik_indikator'],
									'indikator_teks' => $sasaran_value['indikator_teks'],
									'satuan' => $sasaran_value['satuan'],
									'target_1' => $sasaran_value['target_1'],
									'target_2' => $sasaran_value['target_2'],
									'target_3' => $sasaran_value['target_3'],
									'target_4' => $sasaran_value['target_4'],
									'target_5' => $sasaran_value['target_5'],
									'target_awal' => $sasaran_value['target_awal'],
									'target_akhir' => $sasaran_value['target_akhir'],
							];
				}
			}

			if(empty($sasaran_value['id_unik_indikator'])){

					$program_all = $wpdb->get_results($wpdb->prepare("
						SELECT 
							* 
						FROM data_renstra_program_lokal 
						WHERE 
							kode_sasaran=%s AND 
							kode_tujuan=%s AND 
							active=1 ORDER BY id",
							$sasaran_value['id_unik'], $tujuan_value['id_unik']), ARRAY_A);

					foreach ($program_all as $keyProgram => $program_value) {
							if(empty($data_all['data'][$tujuan_value['id_unik']]['data'][$sasaran_value['id_unik']]['data'][$program_value['id_unik']])){
									$data_all['data'][$tujuan_value['id_unik']]['data'][$sasaran_value['id_unik']]['data'][$program_value['id_unik']] = [
											'id' => $program_value['id'],
											'id_unik' => $program_value['id_unik'],
											'program_teks' => $program_value['nama_program'],
											'indikator' => array(),
											'data' => array()
									];
							}

							if(!empty($program_value['id_unik_indikator'])){
								if(empty($data_all['data'][$tujuan_value['id_unik']]['data'][$sasaran_value['id_unik']]['data'][$program_value['id_unik']]['indikator'][$program_value['id_unik_indikator']])){
									$data_all['data'][$tujuan_value['id_unik']]['data'][$sasaran_value['id_unik']]['data'][$program_value['id_unik']]['indikator'][$program_value['id_unik_indikator']] = [
											'id_unik_indikator' => $program_value['id_unik_indikator'],
											'indikator_teks' => $program_value['indikator'],
											'satuan' => $program_value['satuan'],
											'target_1' => $program_value['target_1'],
											'pagu_1' => $program_value['pagu_1'],
											'target_2' => $program_value['target_2'],
											'pagu_2' => $program_value['pagu_2'],
											'target_3' => $program_value['target_3'],
											'pagu_3' => $program_value['pagu_3'],
											'target_4' => $program_value['target_4'],
											'pagu_4' => $program_value['pagu_4'],
											'target_5' => $program_value['target_5'],
											'pagu_5' => $program_value['pagu_5'],
											'target_awal' => $program_value['target_awal'],
											'target_akhir' => $program_value['target_akhir'],
									];
								}
							}

							if(empty($program_value['id_unik_indikator'])){
									$kegiatan_all = $wpdb->get_results($wpdb->prepare("
											SELECT 
												* 
											FROM data_renstra_kegiatan_lokal 
											WHERE 
												kode_program=%s AND 
												kode_sasaran=%s AND 
												kode_tujuan=%s AND 
												active=1 ORDER BY id",
												$program_value['id_unik'],
												$sasaran_value['id_unik'],
												$tujuan_value['id_unik'],
											), ARRAY_A);

									foreach ($kegiatan_all as $keyKegiatan => $kegiatan_value) {
										
											if(empty($data_all['data'][$tujuan_value['id_unik']]['data'][$sasaran_value['id_unik']]['data'][$program_value['id_unik']]['data'][$kegiatan_value['id_unik']])){

												$data_all['data'][$tujuan_value['id_unik']]['data'][$sasaran_value['id_unik']]['data'][$program_value['id_unik']]['data'][$kegiatan_value['id_unik']] = [
														'id' => $kegiatan_value['id'],
														'id_unik' => $kegiatan_value['id_unik'],
														'kegiatan_teks' => $kegiatan_value['nama_giat'],
														'indikator' => array()
												];
											}

											if(!empty($kegiatan_value['id_unik_indikator'])){
												if(empty($data_all['data'][$tujuan_value['id_unik']]['data'][$sasaran_value['id_unik']]['data'][$program_value['id_unik']]['data'][$kegiatan_value['id_unik']]['indikator'][$kegiatan_value['id_unik_indikator']])){
														$data_all['data'][$tujuan_value['id_unik']]['data'][$sasaran_value['id_unik']]['data'][$program_value['id_unik']]['data'][$kegiatan_value['id_unik']]['indikator'][$kegiatan_value['id_unik_indikator']] = [
																'id_unik_indikator' => $kegiatan_value['id_unik_indikator'],
																'indikator_teks' => $kegiatan_value['indikator'],
																'satuan' => $kegiatan_value['satuan'],
																'target_1' => $kegiatan_value['target_1'],
																'pagu_1' => $kegiatan_value['pagu_1'],
																'target_2' => $kegiatan_value['target_2'],
																'pagu_2' => $kegiatan_value['pagu_2'],
																'target_3' => $kegiatan_value['target_3'],
																'pagu_3' => $kegiatan_value['pagu_3'],
																'target_4' => $kegiatan_value['target_4'],
																'pagu_4' => $kegiatan_value['pagu_4'],
																'target_5' => $kegiatan_value['target_5'],
																'pagu_5' => $kegiatan_value['pagu_5'],
																'target_awal' => $kegiatan_value['target_awal'],
																'target_akhir' => $kegiatan_value['target_akhir'],
														];
												}
											}
									}
							}
					}
			}
		}
	}

}

// echo '<pre>';print_r($data_all);echo '</pre>';die();

?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/slim-select/1.27.1/slimselect.min.css" rel="stylesheet">
<style type="text/css">
	.debug-tujuan, .debug-sasaran, .debug-program, .debug-kegiatan, .debug-kode { display: none; }
	.indikator_program { min-height: 40px; }
	.indikator_kegiatan { min-height: 40px; }
	.modal {overflow-y:auto;}
</style>
<h4 style="text-align: center; margin: 0; font-weight: bold;">Monitoring dan Evaluasi RENSTRA (Rencana Strategis) <br><?php echo $judul_skpd.'Tahun '.$awal_renstra.' - '.$akhir_renstra.' '.$nama_pemda; ?></h4>
<div id="cetak" title="Laporan MONEV RENSTRA" style="padding: 5px; overflow: auto; height: 80vh;">
	<table cellpadding="2" cellspacing="0" style="font-family:\'Open Sans\',-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif; border-collapse: collapse; font-size: 70%; border: 0; table-layout: fixed;" contenteditable="false">
		<thead>
			<tr>
				<th style="width: 85px;" class="atas kiri kanan bawah text_tengah text_blok">No</th>
				<th style="width: 200px;" class="atas kanan bawah text_tengah text_blok">Bidang Urusan</th>
				<th style="width: 200px;" class="atas kanan bawah text_tengah text_blok">Tujuan</th>
				<th style="width: 200px;" class="atas kanan bawah text_tengah text_blok">Sasaran</th>
				<th style="width: 200px;" class="atas kanan bawah text_tengah text_blok">Program</th>
				<th style="width: 200px;" class="atas kanan bawah text_tengah text_blok">Kegiatan</th>
				<th style="width: 400px;" class="atas kanan bawah text_tengah text_blok">Indikator</th>
				<th style="width: 100px;" class="atas kanan bawah text_tengah text_blok">Target Awal</th>
				<th style="width: 100px;" class="atas kanan bawah text_tengah text_blok">Target Tahun 1</th>
				<th style="width: 100px;" class="atas kanan bawah text_tengah text_blok">Target Tahun 2</th>
				<th style="width: 100px;" class="atas kanan bawah text_tengah text_blok">Target Tahun 3</th>
				<th style="width: 100px;" class="atas kanan bawah text_tengah text_blok">Target Tahun 4</th>
				<th style="width: 100px;" class="atas kanan bawah text_tengah text_blok">Target Tahun 5</th>
				<th style="width: 100px;" class="atas kanan bawah text_tengah text_blok">Target Akhir</th>
				<th style="width: 100px;" class="atas kanan bawah text_tengah text_blok">Satuan</th>
				<th style="width: 150px;" class="atas kanan bawah text_tengah text_blok">Keterangan</th>
			</tr>
			<tr>
				<th class='atas kiri kanan bawah text_tengah text_blok'>0</th>
				<th class='atas kanan bawah text_tengah text_blok'>1</th>
				<th class='atas kanan bawah text_tengah text_blok'>2</th>
				<th class='atas kanan bawah text_tengah text_blok'>3</th>
				<th class='atas kanan bawah text_tengah text_blok'>4</th>
				<th class='atas kanan bawah text_tengah text_blok'>5</th>
				<th class='atas kanan bawah text_tengah text_blok'>6</th>
				<th class='atas kanan bawah text_tengah text_blok'>7</th>
				<th class='atas kanan bawah text_tengah text_blok'>8</th>
				<th class='atas kanan bawah text_tengah text_blok'>9</th>
				<th class='atas kanan bawah text_tengah text_blok'>10</th>
				<th class='atas kanan bawah text_tengah text_blok'>11</th>
				<th class='atas kanan bawah text_tengah text_blok'>12</th>
				<th class='atas kanan bawah text_tengah text_blok'>13</th>
				<th class='atas kanan bawah text_tengah text_blok'>14</th>
				<th class='atas kanan bawah text_tengah text_blok'>15</th>
			</tr>
		</thead>
		<tbody>
			<?php echo $body; ?>
		</tbody>
	</table>
</div>

<div class="modal fade" id="modal-monev" tabindex="-1" role="dialog" data-backdrop="static" aria-hidden="true">'
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bgpanel-theme">
                <h4 style="margin: 0;" class="modal-title" id="">Data Renstra</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span><i class="dashicons dashicons-dismiss"></i></span></button>
            </div>
            <div class="modal-body">
            	<nav>
				  	<div class="nav nav-tabs" id="nav-tab" role="tablist">
					    <a class="nav-item nav-link" id="nav-tujuan-tab" data-toggle="tab" href="#nav-tujuan" role="tab" aria-controls="nav-tujuan" aria-selected="false">Tujuan</a>
					    <a class="nav-item nav-link" id="nav-sasaran-tab" data-toggle="tab" href="#nav-sasaran" role="tab" aria-controls="nav-sasaran" aria-selected="false">Sasaran</a>
					    <a class="nav-item nav-link" id="nav-program-tab" data-toggle="tab" href="#nav-program" role="tab" aria-controls="nav-program" aria-selected="false">Program</a>
					    <a class="nav-item nav-link" id="nav-kegiatan-tab" data-toggle="tab" href="#nav-kegiatan" role="tab" aria-controls="nav-kegiatan" aria-selected="false">Kegiatan</a>
				  	</div>
				</nav>
				<div class="tab-content" id="nav-tabContent">
				  	<div class="tab-pane fade show active" id="nav-tujuan" role="tabpanel" aria-labelledby="nav-tujuan-tab"></div>
				  	<div class="tab-pane fade" id="nav-sasaran" role="tabpanel" aria-labelledby="nav-sasaran-tab"></div>
				  	<div class="tab-pane fade" id="nav-program" role="tabpanel" aria-labelledby="nav-program-tab"></div>
				  	<div class="tab-pane fade" id="nav-kegiatan" role="tabpanel" aria-labelledby="nav-kegiatan-tab"></div>
				</div>
            </div>
            <div class="modal-footer">
            </div>
        </div>
    </div>
</div>

<!-- Modal indikator renstra -->
<div class="modal fade" id="modal-indikator-renstra" tabindex="-1" role="dialog" aria-labelledby="modal-indikator-renstra-label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer"></div>
    </div>
  </div>
</div>

<!-- Modal crud renstra -->
<div class="modal fade" id="modal-crud-renstra" tabindex="-2" role="dialog" aria-labelledby="modal-crud-renstra-label" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Modal title</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
      </div>
      <div class="modal-footer"></div>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/slim-select/1.27.1/slimselect.min.js"></script>
<script type="text/javascript">
	run_download_excel();
	let data_all = <?php echo json_encode($data_all); ?>;

	var mySpace = '<div style="padding:3rem;"></div>';
	
	jQuery('body').prepend(mySpace);

	var dataHitungMundur = {
		'namaJadwal' : '<?php echo ucwords($namaJadwal)  ?>',
		'mulaiJadwal' : '<?php echo $mulaiJadwal  ?>',
		'selesaiJadwal' : '<?php echo $selesaiJadwal  ?>',
		'thisTimeZone' : '<?php echo $timezone ?>'
	}

	penjadwalanHitungMundur(dataHitungMundur);

	var aksi = ''
		+'<a style="margin-left: 10px;" id="singkron-sipd" onclick="return false;" href="#" class="btn btn-danger">Ambil data dari SIPD lokal</a>'
		+'<?php echo $add_renstra; ?>'
		+'<h3 style="margin-top: 20px;">SETTING</h3>'
		+'<label><input type="checkbox" onclick="tampilkan_edit(this);"> Edit Data RENSTRA</label>'
		+'<label style="margin-left: 20px;"><input type="checkbox" onclick="show_debug(this);"> Debug Cascading RPJM</label>'
		+'<label style="margin-left: 20px;">'
			+'Sembunyikan Baris '
			+'<select id="sembunyikan-baris" onchange="sembunyikan_baris(this);" style="padding: 5px 10px; min-width: 200px;">'
				+'<option value="">Pilih Baris</option>'
				+'<option value="tr-sasaran">Sasaran</option>'
				+'<option value="tr-program">Program</option>'
				+'<option value="tr-kegiatan">Kegiatan</option>'
			+'</select>'
		+'</label>'
		+'<label style="margin-left: 20px;">'
			+'Filter SKPD '
			+'<select onchange="filter_skpd(this);" style="padding: 5px 10px; min-width: 200px; max-width: 400px;">'
				+'<?php echo $skpd_filter_html; ?>'
			+'</select>'
		+'</label>';
	jQuery('#action-sipd').append(aksi);

	jQuery('#tambah-data').on('click', function(){
        tujuanRenstra();
	});

	jQuery(document).on('click', '.btn-tambah-tujuan', function(){

		jQuery('#wrap-loading').show();

		jQuery.ajax({
				method:'POST',
				url:ajax.url,
				dataType:'json',
				data:{
					'action': 'add_tujuan_renstra',
		          	'api_key': '<?php echo $api_key; ?>',
		          	'id_unit': '<?php echo $input['id_skpd']; ?>',
		          	'relasi_perencanaan': '<?php echo $relasi_perencanaan; ?>',
		          	'id_tipe_relasi': '<?php echo $id_tipe_relasi; ?>',
				},
				success:function(response){
						jQuery('#wrap-loading').hide();
						if(response.status){
							let tujuanModal = jQuery("#modal-crud-renstra");
							let html = '<form id="form-renstra">'
											+'<input type="hidden" name="id_unit" value="'+<?php echo $input['id_skpd']; ?>+'">'
											+'<div class="form-group">'
												+'<label for="tujuan_teks">Sasaran Rpjm/Rpd</label>'
												+'<select class="form-control" id="sasaran-parent" name="sasaran_parent" onchange="pilihSasaranParent(this)">';
													html+='<option value="">Pilih Sasaran</option>';
													response.data.map(function(value, index){
														html +='<option value="'+value.id_unik+'|'+value.id_program+'">'+value.sasaran_teks+'</option>';
													})
												html+='</select>'
											+'</div>'
											+'<div class="form-group">'
												+'<label for="tujuan_teks">Tujuan Renstra</label>'
								  				+'<textarea class="form-control" id="tujuan_teks" name="tujuan_teks"></textarea>'
											+'</div>'
											+'<div class="form-group">'
												+'<label for="tujuan_teks">Urut Tujuan</label>'
								  				+'<input type="number" class="form-control" name="urut_tujuan" />'
											+'</div>'
										+'</form>';

							tujuanModal.find('.modal-title').html('Tambah Tujuan');
							tujuanModal.find('.modal-body').html(html);
							tujuanModal.find('.modal-footer').html(''
								+'<button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">'
									+'<i class="dashicons dashicons-no" style="margin-top: 3px;"></i> Tutup'
								+'</button>'
								+'<button type="button" class="btn btn-sm btn-success" id="btn-simpan-data-renstra-lokal" '
									+'data-action="submit_tujuan_renstra" '
									+'data-view="tujuanRenstra"'
								+'>'
									+'<i class="dashicons dashicons-yes" style="margin-top: 3px;"></i> Simpan'
								+'</button>');
							tujuanModal.modal('show');
						}else{
							alert(response.message);
						}
				}
			});
	});

	jQuery(document).on('click', '.btn-edit-tujuan', function(){
		jQuery('#wrap-loading').show();

		let tujuanModal = jQuery("#modal-crud-renstra");
		let idtujuan = jQuery(this).data('id');
		jQuery.ajax({
			url: ajax.url,
          	type: "post",
          	data: {
          		"action": "edit_tujuan_renstra",
          		"api_key": "<?php echo $api_key; ?>",
							'id_tujuan': idtujuan, 
			        'id_unit': '<?php echo $input['id_skpd'] ?>',
			        'relasi_perencanaan': '<?php echo $relasi_perencanaan; ?>',
			        'id_tipe_relasi': '<?php echo $id_tipe_relasi; ?>',
          	},
          	dataType: "json",
          	success: function(response){
				jQuery('#wrap-loading').hide();
				if(response.status){
					let html = '<form id="form-renstra">'
											+'<input type="hidden" name="id" value="'+response.tujuan.id+'">'
											+'<input type="hidden" name="id_unik" value="'+response.tujuan.id_unik+'">'
											+'<input type="hidden" name="id_unit" value="'+<?php echo $input['id_skpd']; ?>+'">'
											+'<div class="form-group">'
												+'<label for="tujuan_teks">Sasaran Rpjm/Rpd</label>'
												+'<select class="form-control" id="sasaran-parent" name="sasaran_parent" onchange="pilihSasaranParent(this)">';
													html+='<option value="" selected>Pilih Sasaran</option>';
													response.sasaran_parent.map(function(value, index){
														html +='<option value="'+value.id_unik+'|'+value.id_program+'">'+value.sasaran_teks+'</option>';
													})
												html+='</select>'
											+'</div>'
											+'<div class="form-group">'
												+'<label for="tujuan_teks">Tujuan Renstra</label>'
								  				+'<textarea class="form-control" id="tujuan_teks" name="tujuan_teks">'+response.tujuan.tujuan_teks+'</textarea>'
											+'</div>'
											+'<div class="form-group">'
												+'<label for="tujuan_teks">Urut Tujuan</label>'
								  				+'<input type="number" class="form-control" name="urut_tujuan" value="'+response.tujuan.urut_tujuan+'" />'
											+'</div>'
										+'</form>';

			        tujuanModal.find('.modal-title').html('Edit Tujuan');
					tujuanModal.find('.modal-body').html(html);
					tujuanModal.find('.modal-footer').html(''
						+'<button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">'
							+'<i class="dashicons dashicons-no" style="margin-top: 3px;"></i> Tutup'
						+'</button>'
						+'<button type="button" class="btn btn-sm btn-success" id="btn-simpan-data-renstra-lokal" '
							+'data-action="update_tujuan_renstra" '
							+'data-view="tujuanRenstra"'
						+'>'
							+'<i class="dashicons dashicons-yes" style="margin-top: 3px;"></i> Simpan'
						+'</button>');
					tujuanModal.modal('show');
				}else{
					alert(response.message);
				}
          	}
        });
	});

	jQuery(document).on('click', '.btn-hapus-tujuan', function(){
		
		if(confirm('Data akan dihapus, lanjut?')){

	        jQuery('#wrap-loading').show();

			let id_tujuan = jQuery(this).data('id');
			let id_unik = jQuery(this).data('idunik');

			jQuery.ajax({
				method:'POST',
				url:ajax.url,
				dataType:'json',
				data:{
					'action':'delete_tujuan_renstra',
					'api_key':'<?php echo $api_key; ?>',
					'id_tujuan':id_tujuan,
					'id_unik':id_unik,
				},
				success:function(response){
					alert(response.message);
					if(response.status){
						tujuanRenstra();
					}
					jQuery('#wrap-loading').hide();
				}
			})
		}
	});

	jQuery(document).on('click', '.btn-kelola-indikator-tujuan', function(){
        jQuery("#modal-indikator-renstra").find('.modal-body').html('');
				indikatorTujuanRenstra({'id_unik':jQuery(this).data('idunik')});
	});

	jQuery(document).on('click', '.btn-add-indikator-tujuan', function(){

		let indikatorTujuanModal = jQuery("#modal-crud-renstra");
		let id_unik = jQuery(this).data('kodetujuan');
		let html = '<form id="form-renstra">'
					+'<input type="hidden" name="id_unik" value="'+id_unik+'">'
					+'<div class="form-group">'
						+'<label for="indikator_teks">Indikator</label>'
		  				+'<textarea class="form-control" name="indikator_teks"></textarea>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="satuan">Satuan</label>'
		  				+'<input type="text" class="form-control" name="satuan"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_1">Target tahun ke-1</label>'
		  				+'<input type="text" class="form-control" name="target_1"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_2">Target tahun ke-2</label>'
		  				+'<input type="text" class="form-control" name="target_2"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_3">Target tahun ke-3</label>'
		  				+'<input type="text" class="form-control" name="target_3"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_4">Target tahun ke-4</label>'
		  				+'<input type="text" class="form-control" name="target_4"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_5">Target tahun ke-5</label>'
		  				+'<input type="text" class="form-control" name="target_5"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_awal">Target awal</label>'
		  				+'<input type="text" class="form-control" name="target_awal"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_akhir">Target akhir</label>'
		  				+'<input type="text" class="form-control" name="target_akhir"/>'
					+'</div>'
					+'</form>';

			indikatorTujuanModal.find('.modal-title').html('Tambah Indikator');
			indikatorTujuanModal.find('.modal-body').html(html);
			indikatorTujuanModal.find('.modal-footer').html(''
				+'<button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">'
					+'<i class="dashicons dashicons-no" style="margin-top: 3px;"></i> Tutup'
				+'</button>'
				+'<button type="button" class="btn btn-sm btn-success" id="btn-simpan-data-renstra-lokal" '
					+'data-action="submit_indikator_tujuan_renstra" '
					+'data-view="indikatorTujuanRenstra"'
				+'>'
					+'<i class="dashicons dashicons-yes" style="margin-top: 3px;"></i> Simpan'
				+'</button>');
			indikatorTujuanModal.modal('show');
	});

	jQuery(document).on('click', '.btn-edit-indikator-tujuan', function(){

		jQuery('#wrap-loading').show();

		let indikatorTujuanModal = jQuery("#modal-crud-renstra");

		let id = jQuery(this).data('id');

		let id_unik = jQuery(this).data('idunik');

		jQuery.ajax({
			url: ajax.url,
          	type: "post",
          	data: {
          		"action": "edit_indikator_tujuan_renstra",
          		"api_key": "<?php echo $api_key; ?>",
				'id': id
          	},
          	dataType: "json",
          	success: function(response){
          		jQuery('#wrap-loading').hide();

          		let html = '<form id="form-renstra">'
					+'<input type="hidden" name="id" value="'+id+'">'
					+'<input type="hidden" name="id_unik" value="'+id_unik+'">'
					+'<div class="form-group">'
						+'<label for="indikator_teks">Indikator</label>'
	  					+'<textarea class="form-control" name="indikator_teks">'+response.data.indikator_teks+'</textarea>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="satuan">Satuan</label>'
	  					+'<input type="text" class="form-control" name="satuan" value="'+response.data.satuan+'"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_1">Target tahun ke-1</label>'
	  					+'<input type="text" class="form-control" name="target_1" value="'+response.data.target_1+'"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_2">Target tahun ke-2</label>'
	  					+'<input type="text" class="form-control" name="target_2" value="'+response.data.target_2+'"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_3">Target tahun ke-3</label>'
	  					+'<input type="text" class="form-control" name="target_3" value="'+response.data.target_3+'"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_4">Target tahun ke-4</label>'
	  					+'<input type="text" class="form-control" name="target_4" value="'+response.data.target_4+'"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_5">Target tahun ke-5</label>'
	  					+'<input type="text" class="form-control" name="target_5" value="'+response.data.target_5+'"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_awal">Target awal</label>'
	  					+'<input type="text" class="form-control" name="target_awal" value="'+response.data.target_awal+'"/>'
					+'</div>'
					+'<div class="form-group">'
					+'<label for="target_akhir">Target akhir</label>'
	  					+'<input type="text" class="form-control" name="target_akhir" value="'+response.data.target_akhir+'"/>'
					+'</div>'
				  +'</form>';

				indikatorTujuanModal.find('.modal-title').html('Edit Indikator Tujuan');
				indikatorTujuanModal.find('.modal-body').html(html);
				indikatorTujuanModal.find('.modal-footer').html(''
					+'<button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">'
						+'<i class="dashicons dashicons-no" style="margin-top: 3px;"></i> Tutup'
					+'</button>'
					+'<button type="button" class="btn btn-sm btn-success" id="btn-simpan-data-renstra-lokal" '
						+'data-action="update_indikator_tujuan_renstra" '
						+'data-view="indikatorTujuanRenstra"'
					+'>'
						+'<i class="dashicons dashicons-yes" style="margin-top: 3px;"></i> Simpan'
					+'</button>');
				indikatorTujuanModal.modal('show');
          	}
		})			
	});

	jQuery(document).on('click', '.btn-delete-indikator-tujuan', function(){

		if(confirm('Data akan dihapus, lanjut?')){
			jQuery('#wrap-loading').show();
			
			let id = jQuery(this).data('id');
			
			let id_unik = jQuery(this).data('idunik');

			jQuery.ajax({
				method:'POST',
				url:ajax.url,
				dataType:'json',
				data:{
					'action': 'delete_indikator_tujuan_renstra',
		          	'api_key': '<?php echo $api_key; ?>',
					'id': id
				},
				success:function(response){

					alert(response.message);
					if(response.status){
						indikatorTujuanRenstra({
							'id_unik': id_unik
						});
					}
					jQuery('#wrap-loading').hide();

				}
			})
		}
	});

	jQuery(document).on('click', '.btn-detail-tujuan', function(){
		sasaranRenstra({
			'kode_tujuan':jQuery(this).data('kodetujuan')
		});
	});

	jQuery(document).on('click', '.btn-tambah-sasaran', function(){
		let relasi_perencanaan = '<?php echo $relasi_perencanaan; ?>';
		let id_tipe_relasi = '<?php echo $id_tipe_relasi; ?>';
		let id_unit = '<?php echo $input['id_skpd']; ?>';

		let sasaranModal = jQuery("#modal-crud-renstra");
		let kode_tujuan = jQuery(this).data('kodetujuan');
		let html = '<form id="form-renstra">'
						+'<input type="hidden" name="kode_tujuan" value="'+kode_tujuan+'">'
						+'<input type="hidden" name="relasi_perencanaan" value="'+relasi_perencanaan+'">'
						+'<input type="hidden" name="id_tipe_relasi" value="'+id_tipe_relasi+'">'
						+'<input type="hidden" name="id_unit" value="'+id_unit+'">'
						+'<div class="form-group">'
							+'<label for="sasaran">Sasaran</label>'
	  						+'<textarea class="form-control" name="sasaran_teks"></textarea>'
						+'</div>'
						+'<div class="form-group">'
							+'<label for="urut_sasaran">Urut Sasaran</label>'
	  						+'<input type="number" class="form-control" name="urut_sasaran"/>'
						+'</div>'
					+'</form>';

		sasaranModal.find('.modal-title').html('Tambah Sasaran');
		sasaranModal.find('.modal-body').html(html);
		sasaranModal.find('.modal-footer').html(''
			+'<button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">'
				+'<i class="dashicons dashicons-no" style="margin-top: 3px;"></i> Tutup'
			+'</button>'
			+'<button type="button" class="btn btn-sm btn-success" id="btn-simpan-data-renstra-lokal" '
				+'data-action="submit_sasaran_renstra" '
				+'data-view="sasaranRenstra"'
			+'>'
				+'<i class="dashicons dashicons-yes" style="margin-top: 3px;"></i> Simpan'
			+'</button>');
		sasaranModal.modal('show');
	});

	jQuery(document).on('click', '.btn-edit-sasaran', function(){

		jQuery('#wrap-loading').show();

		let relasi_perencanaan = '<?php echo $relasi_perencanaan; ?>';
		let id_tipe_relasi = '<?php echo $id_tipe_relasi; ?>';
		let id_unit = '<?php echo $input['id_skpd']; ?>';
		let id_sasaran = jQuery(this).data('idsasaran');
		let sasaranModal = jQuery("#modal-crud-renstra");

		jQuery.ajax({
			url: ajax.url,
          	type: "post",
          	data: {
          		"action": "edit_sasaran_renstra",
          		"api_key": "<?php echo $api_key; ?>",
							'id_sasaran': id_sasaran
          	},
          	dataType: "json",
          	success: function(response){

          		jQuery('#wrap-loading').hide();
							let html = '<form id="form-renstra">'
											+'<input type="hidden" name="relasi_perencanaan" value="'+relasi_perencanaan+'">'
											+'<input type="hidden" name="id_tipe_relasi" value="'+id_tipe_relasi+'">'
											+'<input type="hidden" name="id_unit" value="'+id_unit+'">'
											+'<input type="hidden" name="kode_tujuan" value="'+response.data.kode_tujuan+'" />'
											+'<input type="hidden" name="kode_sasaran" value="'+response.data.id_unik+'" />'
											+'<div class="form-group">'
												+'<label for="sasaran">Sasaran</label>'
				  								+'<textarea class="form-control" name="sasaran_teks">'+response.data.sasaran_teks+'</textarea>'
											+'</div>'
											+'<div class="form-group">'
												+'<label for="urut_sasaran">Urut Sasaran</label>'
				  								+'<input type="number" class="form-control" name="urut_sasaran" value="'+response.data.urut_sasaran+'"/>'
											+'</div>'
										+'</form>';

					        sasaranModal.find('.modal-title').html('Edit Sasaran');
									sasaranModal.find('.modal-body').html(html);
									sasaranModal.find('.modal-footer').html(''
										+'<button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">'
											+'<i class="dashicons dashicons-no" style="margin-top: 3px;"></i> Tutup'
										+'</button>'
										+'<button type="button" class="btn btn-sm btn-success" id="btn-simpan-data-renstra-lokal" '
											+'data-action="update_sasaran_renstra" '
											+'data-view="sasaranRenstra"'
										+'>'
											+'<i class="dashicons dashicons-yes" style="margin-top: 3px;"></i> Simpan'
										+'</button>');
									sasaranModal.modal('show');
          	}
        });
	});

	jQuery(document).on('click', '.btn-hapus-sasaran', function(){

		if(confirm('Data akan dihapus, lanjut?')){
			
			jQuery('#wrap-loading').show();
			let id_sasaran = jQuery(this).data('idsasaran');
			let kode_sasaran = jQuery(this).data('kodesasaran');
			let kode_tujuan = jQuery(this).data('kodetujuan');

			jQuery.ajax({
				method:'POST',
				url:ajax.url,
				dataType:'json',
				data:{
					'action': 'delete_sasaran_renstra',
		      'api_key': '<?php echo $api_key; ?>',
					'id_sasaran': id_sasaran,
					'kode_sasaran': kode_sasaran,
				},
				success:function(response){

					alert(response.message);
					if(response.status){
						sasaranRenstra({
							'kode_tujuan': kode_tujuan
						});
					}
					jQuery('#wrap-loading').hide();

				}
			})
		}
	});

	jQuery(document).on('click', '.btn-kelola-indikator-sasaran', function(){
		jQuery("#modal-indikator-renstra").find('.modal-body').html('');
		indikatorSasaranRenstra({'id_unik':jQuery(this).data('kodesasaran')});
	});

	jQuery(document).on('click', '.btn-add-indikator-sasaran', function(){

		let indikatorSasaranModal = jQuery("#modal-crud-renstra");
		let id_unik = jQuery(this).data('kodesasaran');
		let html = '<form id="form-renstra">'
					+'<input type="hidden" name="id_unik" value="'+id_unik+'">'
					+'<div class="form-group">'
						+'<label for="indikator_teks">Indikator</label>'
		  				+'<textarea class="form-control" name="indikator_teks"></textarea>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="satuan">Satuan</label>'
		  				+'<input type="text" class="form-control" name="satuan"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_1">Target tahun ke-1</label>'
		  				+'<input type="text" class="form-control" name="target_1"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_2">Target tahun ke-2</label>'
		  				+'<input type="text" class="form-control" name="target_2"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_3">Target tahun ke-3</label>'
		  				+'<input type="text" class="form-control" name="target_3"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_4">Target tahun ke-4</label>'
		  				+'<input type="text" class="form-control" name="target_4"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_5">Target tahun ke-5</label>'
		  				+'<input type="text" class="form-control" name="target_5"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_awal">Target awal</label>'
		  				+'<input type="text" class="form-control" name="target_awal"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_akhir">Target akhir</label>'
		  				+'<input type="text" class="form-control" name="target_akhir"/>'
					+'</div>'
					+'</form>';

			indikatorSasaranModal.find('.modal-title').html('Tambah Indikator');
			indikatorSasaranModal.find('.modal-body').html(html);
			indikatorSasaranModal.find('.modal-footer').html(''
				+'<button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">'
					+'<i class="dashicons dashicons-no" style="margin-top: 3px;"></i> Tutup'
				+'</button>'
				+'<button type="button" class="btn btn-sm btn-success" id="btn-simpan-data-renstra-lokal" '
					+'data-action="submit_indikator_sasaran_renstra" '
					+'data-view="indikatorSasaranRenstra"'
				+'>'
					+'<i class="dashicons dashicons-yes" style="margin-top: 3px;"></i> Simpan'
				+'</button>');
			indikatorSasaranModal.modal('show');
	});

	jQuery(document).on('click', '.btn-edit-indikator-sasaran', function(){

		jQuery('#wrap-loading').show();

		let id = jQuery(this).data('id');
		let id_unik = jQuery(this).data('idunik');
		let indikatorSasaranModal = jQuery("#modal-crud-renstra");

		jQuery.ajax({
			url: ajax.url,
          	type: "post",
          	data: {
          		"action": "edit_indikator_sasaran_renstra",
          		"api_key": "<?php echo $api_key; ?>",
							'id': id
          	},
          	dataType: "json",
          	success: function(response){
          		jQuery('#wrap-loading').hide();

          		let html = '<form id="form-renstra">'
								+'<input type="hidden" name="id" value="'+id+'">'
								+'<input type="hidden" name="id_unik" value="'+id_unik+'">'
								+'<div class="form-group">'
									+'<label for="indikator_teks">Indikator</label>'
				  					+'<textarea class="form-control" name="indikator_teks">'+response.data.indikator_teks+'</textarea>'
								+'</div>'
								+'<div class="form-group">'
									+'<label for="satuan">Satuan</label>'
				  					+'<input type="text" class="form-control" name="satuan" value="'+response.data.satuan+'"/>'
								+'</div>'
								+'<div class="form-group">'
									+'<label for="target_1">Target tahun ke-1</label>'
				  					+'<input type="text" class="form-control" name="target_1" value="'+response.data.target_1+'"/>'
								+'</div>'
								+'<div class="form-group">'
									+'<label for="target_2">Target tahun ke-2</label>'
				  					+'<input type="text" class="form-control" name="target_2" value="'+response.data.target_2+'"/>'
								+'</div>'
								+'<div class="form-group">'
									+'<label for="target_3">Target tahun ke-3</label>'
				  					+'<input type="text" class="form-control" name="target_3" value="'+response.data.target_3+'"/>'
								+'</div>'
								+'<div class="form-group">'
									+'<label for="target_4">Target tahun ke-4</label>'
				  					+'<input type="text" class="form-control" name="target_4" value="'+response.data.target_4+'"/>'
								+'</div>'
								+'<div class="form-group">'
									+'<label for="target_5">Target tahun ke-5</label>'
				  					+'<input type="text" class="form-control" name="target_5" value="'+response.data.target_5+'"/>'
								+'</div>'
								+'<div class="form-group">'
									+'<label for="target_awal">Target awal</label>'
				  					+'<input type="text" class="form-control" name="target_awal" value="'+response.data.target_awal+'"/>'
								+'</div>'
								+'<div class="form-group">'
								+'<label for="target_akhir">Target akhir</label>'
				  					+'<input type="text" class="form-control" name="target_akhir" value="'+response.data.target_akhir+'"/>'
								+'</div>'
							  +'</form>';

							indikatorSasaranModal.find('.modal-title').html('Edit Indikator Sasaran');
							indikatorSasaranModal.find('.modal-body').html(html);
							indikatorSasaranModal.find('.modal-footer').html(''
								+'<button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">'
									+'<i class="dashicons dashicons-no" style="margin-top: 3px;"></i> Tutup'
								+'</button>'
								+'<button type="button" class="btn btn-sm btn-success" id="btn-simpan-data-renstra-lokal" '
									+'data-action="update_indikator_sasaran_renstra" '
									+'data-view="indikatorSasaranRenstra"'
								+'>'
									+'<i class="dashicons dashicons-yes" style="margin-top: 3px;"></i> Simpan'
								+'</button>');
							indikatorSasaranModal.modal('show');
          	}
		})			
	});

	jQuery(document).on('click', '.btn-delete-indikator-sasaran', function(){

		if(confirm('Data akan dihapus, lanjut?')){
	
			jQuery('#wrap-loading').show();

			let id = jQuery(this).data('id');
			let id_unik = jQuery(this).data('idunik');

			jQuery.ajax({
				method:'POST',
				url:ajax.url,
				dataType:'json',
				data:{
					'action': 'delete_indikator_sasaran_renstra',
		      'api_key': '<?php echo $api_key; ?>',
					'id': id
				},
				success:function(response){

					alert(response.message);
					if(response.status){
						indikatorSasaranRenstra({
							'id_unik': id_unik
						});
					}
					jQuery('#wrap-loading').hide();

				}
			})
		}
	});

	jQuery(document).on('click', '.btn-detail-sasaran', function(){
		programRenstra({
			'kode_sasaran':jQuery(this).data('kodesasaran')
		});
	});

	jQuery(document).on('click', '.btn-tambah-program', function(){

		jQuery('#wrap-loading').show();

		let programModal = jQuery("#modal-crud-renstra");
		let kode_sasaran = jQuery(this).data('kodesasaran');

  				get_bidang_urusan().then(function(){

		  			jQuery('#wrap-loading').hide();
		  				
						let html = '<form id="form-renstra">'
										+'<input type="hidden" name="kode_sasaran" value="'+kode_sasaran+'"/>'
										+'<div class="form-group">'
									    	+'<label>Pilih Urusan</label>'
									    	+'<select class="form-control" name="id_urusan" id="urusan-teks"></select>'
									  	+'</div>'
									  	+'<div class="form-group">'
									    	+'<label>Pilih Bidang</label>'
									    	+'<select class="form-control" name="id_bidang" id="bidang-teks"></select>'
									  	+'</div>'
									  	+'<div class="form-group">'
									    	+'<label>Pilih Program</label>'
									    	+'<select class="form-control" name="id_program" id="program-teks"></select>'
									  	+'</div>'
									+'</form>';

				    programModal.find('.modal-title').html('Tambah Program');
						programModal.find('.modal-body').html(html);
						programModal.find('.modal-footer').html(''
							+'<button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">'
								+'<i class="dashicons dashicons-no" style="margin-top: 3px;"></i> Tutup'
							+'</button>'
							+'<button type="button" class="btn btn-sm btn-success" id="btn-simpan-data-renstra-lokal" '
								+'data-action="submit_program_renstra" '
								+'data-view="programRenstra"'
							+'>'
								+'<i class="dashicons dashicons-yes" style="margin-top: 3px;"></i> Simpan'
							+'</button>');

						get_urusan();
						get_bidang();
						get_program();

						programModal.modal('show');
  		});	
	});

	jQuery(document).on('click', '.btn-edit-program', function(){
		
		jQuery('#wrap-loading').show();

		let programModal = jQuery("#modal-crud-renstra");
		
		jQuery.ajax({
			url: ajax.url,
          	type: "post",
          	data: {
          		"action": "edit_program_renstra",
          		"api_key": "<?php echo $api_key; ?>",
							'id_unik': jQuery(this).data('kodeprogram')
          	},
          	dataType: "json",
          	success: function(res){

          		let id_program = res.data.id_program;

          		get_bidang_urusan().then(function(){

			          			jQuery('#wrap-loading').hide();
							
											let html = '<form id="form-renstra">'
															+'<input type="hidden" name="id_unik" value="'+res.data.id_unik+'"/>'
															+'<input type="hidden" name="kode_sasaran" value="'+res.data.kode_sasaran+'"/>'
															+'<div class="form-group">'
														    	+'<label>Pilih Urusan</label>'
														    	+'<select class="form-control" name="id_urusan" id="urusan-teks"></select>'
														  	+'</div>'
														  	+'<div class="form-group">'
														    	+'<label>Pilih Bidang</label>'
														    	+'<select class="form-control" name="id_bidang" id="bidang-teks"></select>'
														  	+'</div>'
														  	+'<div class="form-group">'
														    	+'<label>Pilih Program</label>'
														    	+'<select class="form-control" name="id_program" id="program-teks"></select>'
														  	+'</div>'
														+'</form>';

									    programModal.find('.modal-title').html('Edit Program');
											programModal.find('.modal-body').html(html);
											programModal.find('.modal-footer').html(''
												+'<button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">'
													+'<i class="dashicons dashicons-no" style="margin-top: 3px;"></i> Tutup'
												+'</button>'
												+'<button type="button" class="btn btn-sm btn-success" id="btn-simpan-data-renstra-lokal" '
													+'data-action="update_program_renstra" '
													+'data-view="programRenstra"'
												+'>'
													+'<i class="dashicons dashicons-yes" style="margin-top: 3px;"></i> Simpan'
												+'</button>');

											get_urusan();
											get_bidang();
											get_program(false, id_program);

											programModal.modal('show');

			          		});

          	}
        });
	});

	jQuery(document).on('click', '.btn-hapus-program', function(){

		if(confirm('Data akan dihapus, lanjut?')){
			
			jQuery('#wrap-loading').show();
			
			let kode_program = jQuery(this).data('kodeprogram');
			let kode_sasaran = jQuery(this).data('kodesasaran');

			jQuery.ajax({
				method:'POST',
				url:ajax.url,
				dataType:'json',
				data:{
					'action': 'delete_program_renstra',
		      'api_key': '<?php echo $api_key; ?>',
					'kode_program': kode_program,
				},
				success:function(response){

					alert(response.message);
					if(response.status){
						programRenstra({
							'kode_sasaran': kode_sasaran
						});
					}
					jQuery('#wrap-loading').hide();

				}
			})
		}
	});

	jQuery(document).on('click', '.btn-kelola-indikator-program', function(){
		jQuery("#modal-indikator-renstra").find('.modal-body').html('');
		indikatorProgramRenstra({'kode_program':jQuery(this).data('kodeprogram')});
	});

	jQuery(document).on('click', '.btn-add-indikator-program', function(){
		
		jQuery('#wrap-loading').show();
		
		let kode_program = jQuery(this).data('kodeprogram');
		
		let html = '';

		get_bidang_urusan(true).then(function(){
			
			jQuery('#wrap-loading').hide();

			html += '<form id="form-renstra">'
						+'<input type="hidden" name="kode_program" value='+kode_program+'>'
						+'<div class="form-group">'
							+'<label for="indikator_teks">Indikator</label>'
			  			+'<textarea class="form-control" name="indikator_teks"></textarea>'
						+'</div>'
						+'<div class="form-group">'
							+'<label for="satuan">Satuan</label>'
			  			+'<input type="text" class="form-control" name="satuan"/>'
						+'</div>'
						+'<div class="form-group">'
							+'<label for="target_1">Target tahun ke-1</label>'
			  			+'<input type="text" class="form-control" name="target_1"/>'
						+'</div>'
						+'<div class="form-group">'
							+'<label for="target_2">Target tahun ke-2</label>'
			  			+'<input type="text" class="form-control" name="target_2"/>'
						+'</div>'
						+'<div class="form-group">'
							+'<label for="target_3">Target tahun ke-3</label>'
					  	+'<input type="text" class="form-control" name="target_3"/>'
						+'</div>'
						+'<div class="form-group">'
							+'<label for="target_4">Target tahun ke-4</label>'
					  	+'<input type="text" class="form-control" name="target_4"/>'
						+'</div>'
						+'<div class="form-group">'
							+'<label for="target_5">Target tahun ke-5</label>'
					  	+'<input type="text" class="form-control" name="target_5"/>'	
						+'</div>'
						+'<div class="form-group">'
							+'<label for="target_awal">Target awal</label>'
			  				+'<input type="text" class="form-control" name="target_awal"/>'
						+'</div>'
						+'<div class="form-group">'
							+'<label for="target_akhir">Target akhir</label>'
			  				+'<input type="text" class="form-control" name="target_akhir"/>'
						+'</div>'
					+'</form>';
				
				jQuery("#modal-crud-renstra").find('.modal-title').html('Tambah Indikator');
				jQuery("#modal-crud-renstra").find('.modal-body').html(html);
				jQuery("#modal-crud-renstra").find('.modal-footer').html(''
					+'<button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">'
						+'<i class="dashicons dashicons-no" style="margin-top: 3px;"></i> Tutup'
					+'</button>'
					+'<button type="button" class="btn btn-sm btn-success" id="btn-simpan-data-renstra-lokal" '
						+'data-action="submit_indikator_program_renstra" '
						+'data-view="indikatorProgramRenstra"'
					+'>'
						+'<i class="dashicons dashicons-yes" style="margin-top: 3px;"></i> Simpan'
					+'</button>');

				jQuery("#modal-crud-renstra").modal('show');
			}); 
	});

	jQuery(document).on('click', '.btn-edit-indikator-program', function(){

		jQuery('#wrap-loading').show();

		let id = jQuery(this).data('id');
		let kode_program = jQuery(this).data('kodeprogram');

		jQuery.ajax({
			url: ajax.url,
          	type: "post",
          	data: {
          		"action": "edit_indikator_program_renstra",
          		"api_key": "<?php echo $api_key; ?>",
							'id': id,
							'kode_program': kode_program
          	},
          	dataType: "json",
          	success: function(response){

	          		jQuery('#wrap-loading').hide();

	          		let html = '<form id="form-renstra">'
									+'<input type="hidden" name="id" value="'+id+'">'
									+'<input type="hidden" name="kode_program" value="'+kode_program+'">'
									+'<div class="form-group">'
										+'<label for="indikator_teks">Indikator</label>'
					  					+'<textarea class="form-control" name="indikator_teks">'+response.data.indikator+'</textarea>'
									+'</div>'
									+'<div class="form-group">'
										+'<label for="satuan">Satuan</label>'
					  					+'<input type="text" class="form-control" name="satuan" value="'+response.data.satuan+'"/>'
									+'</div>'
									+'<div class="form-group">'
										+'<label for="target_1">Target tahun ke-1</label>'
							  		+'<input type="text" class="form-control" name="target_1" value="'+response.data.target_1+'"/>'
									+'</div>'
									+'<div class="form-group">'
										+'<label for="target_2">Target tahun ke-2</label>'
								  	+'<input type="text" class="form-control" name="target_2" value="'+response.data.target_2+'"/>'
									+'</div>'
									+'<div class="form-group">'
										+'<label for="target_3">Target tahun ke-3</label>'
							  		+'<input type="text" class="form-control" name="target_3" value="'+response.data.target_3+'"/>'
									+'</div>'
									+'<div class="form-group">'
										+'<label for="target_4">Target tahun ke-4</label>'
							  		+'<input type="text" class="form-control" name="target_4" value="'+response.data.target_4+'"/>'
									+'</div>'
									+'<div class="form-group">'
										+'<label for="target_5">Target tahun ke-5</label>'
							  		+'<input type="text" class="form-control" name="target_5" value="'+response.data.target_5+'"/>'
									+'</div>'
									+'<div class="form-group">'
										+'<label for="target_awal">Target awal</label>'
					  					+'<input type="text" class="form-control" name="target_awal" value="'+response.data.target_awal+'"/>'
									+'</div>'
									+'<div class="form-group">'
									+'<label for="target_akhir">Target akhir</label>'
					  					+'<input type="text" class="form-control" name="target_akhir" value="'+response.data.target_akhir+'"/>'
									+'</div>'
								  +'</form>';

								jQuery("#modal-crud-renstra").find('.modal-title').html('Edit Indikator Program');
								jQuery("#modal-crud-renstra").find('.modal-body').html(html);
								jQuery("#modal-crud-renstra").find('.modal-footer').html(''
									+'<button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">'
										+'<i class="dashicons dashicons-no" style="margin-top: 3px;"></i> Tutup'
									+'</button><button type="button" class="btn btn-sm btn-success" id="btn-simpan-data-renstra-lokal" '
										+'data-action="update_indikator_program_renstra" '
										+'data-view="indikatorProgramRenstra"'
									+'>'
										+'<i class="dashicons dashicons-yes" style="margin-top: 3px;"></i> Simpan'
									+'</button>');	

								jQuery("#modal-crud-renstra").modal('show');
          	}
		})	
	});

	jQuery(document).on('click', '.btn-delete-indikator-program', function(){

		if(confirm('Data akan dihapus, lanjut?')){

			jQuery('#wrap-loading').show();
			
			let id = jQuery(this).data('id');	
			let kode_program = jQuery(this).data('kodeprogram');

			jQuery.ajax({
				method:'POST',
				url:ajax.url,
				dataType:'json',
				data:{
					'action': 'delete_indikator_program_renstra',
		      'api_key': '<?php echo $api_key; ?>',
					'id': id,
					'kode_program': kode_program,
				},
				success:function(response){

					alert(response.message);
					if(response.status){
						indikatorProgramRenstra({
							'kode_program': kode_program
						});
					}
					jQuery('#wrap-loading').hide();

				}
			})
		}
	});

	jQuery(document).on('click', '.btn-detail-program', function(){
		kegiatanRenstra({
			'kode_program':jQuery(this).data('kodeprogram'),
			'id_program':jQuery(this).data('idprogram'),
		});
	});

	jQuery(document).on('click', '.btn-tambah-kegiatan', function(){

		jQuery('#wrap-loading').show();

		let kegiatanModal = jQuery("#modal-crud-renstra");
		let kode_program = jQuery(this).data('kodeprogram');
		let id_program = jQuery(this).data('idprogram');

		jQuery.ajax({
				method:'POST',
				url:ajax.url,
				dataType:'json',
				data:{
					'action': 'add_kegiatan_renstra',
		      'api_key': '<?php echo $api_key; ?>',
					'id_program': id_program,
				},
				success:function(response){

					jQuery('#wrap-loading').hide();
		  				
						let html = '<form id="form-renstra">'
										+'<input type="hidden" name="kode_program" value="'+kode_program+'"/>'
										+'<input type="hidden" name="kegiatan_teks" id="kegiatan_teks"/>'
									  +'<div class="form-group">'
												+'<label for="kegiatan_teks">Kegiatan</label>'
												+'<select class="form-control" id="id_kegiatan" name="id_kegiatan" onchange="pilihKegiatan(this)">';
													html+='<option value="">Pilih Kegiatan</option>';
													response.data.map(function(value, index){
														html +='<option value="'+value.id+'">'+value.kegiatan_teks+'</option>';
													})
												html+='</select>'
											+'</div>'
									+'</form>';

				    kegiatanModal.find('.modal-title').html('Tambah Kegiatan');
						kegiatanModal.find('.modal-body').html(html);
						kegiatanModal.find('.modal-footer').html(''
							+'<button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">'
								+'<i class="dashicons dashicons-no" style="margin-top: 3px;"></i> Tutup'
							+'</button>'
							+'<button type="button" class="btn btn-sm btn-success" id="btn-simpan-data-renstra-lokal" '
								+'data-action="submit_kegiatan_renstra" '
								+'data-view="kegiatanRenstra"'
							+'>'
								+'<i class="dashicons dashicons-yes" style="margin-top: 3px;"></i> Simpan'
							+'</button>');

						kegiatanModal.modal('show');

				}
		});	
	});

	jQuery(document).on('click', '.btn-edit-kegiatan', function(){
		jQuery('#wrap-loading').show();

		let kegiatanModal = jQuery("#modal-crud-renstra");
		let id_program = jQuery(this).data('idprogram');
		let id_kegiatan = jQuery(this).data('id');

		jQuery.ajax({
				method:'POST',
				url:ajax.url,
				dataType:'json',
				data:{
					'action': 'edit_kegiatan_renstra',
		      'api_key': '<?php echo $api_key; ?>',
					'id_program': id_program,
					'id_kegiatan': id_kegiatan,
				},
				success:function(response){

					jQuery('#wrap-loading').hide();
		  				
						let html = '<form id="form-renstra">'
										+'<input type="hidden" name="id" id="id" value="'+response.kegiatan.id+'"/>'
										+'<input type="hidden" name="id_unik" id="id_unik" value="'+response.kegiatan.id_unik+'"/>'
										+'<input type="hidden" name="id_program" id="id_program" value="'+response.kegiatan.id_program+'"/>'
										+'<input type="hidden" name="kode_program" id="kode_program" value="'+response.kegiatan.kode_program+'"/>'
										+'<input type="hidden" name="kegiatan_teks" id="kegiatan_teks"/>'
									  +'<div class="form-group">'
												+'<label for="kegiatan_teks">Kegiatan</label>'
												+'<select class="form-control" id="id_kegiatan" name="id_kegiatan" onchange="pilihKegiatan(this)">';
													html+='<option value="">Pilih Kegiatan</option>';
													response.data.map(function(value, index){
														html +='<option value="'+value.id+'">'+value.kegiatan_teks+'</option>';
													})
												html+='</select>'
											+'</div>'
									+'</form>';

				    kegiatanModal.find('.modal-title').html('Tambah Kegiatan');
						kegiatanModal.find('.modal-body').html(html);
						kegiatanModal.find('.modal-footer').html(''
							+'<button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">'
								+'<i class="dashicons dashicons-no" style="margin-top: 3px;"></i> Tutup'
							+'</button>'
							+'<button type="button" class="btn btn-sm btn-success" id="btn-simpan-data-renstra-lokal" '
								+'data-action="update_kegiatan_renstra" '
								+'data-view="kegiatanRenstra"'
							+'>'
								+'<i class="dashicons dashicons-yes" style="margin-top: 3px;"></i> Simpan'
							+'</button>');

						kegiatanModal.modal('show');

						jQuery("#id_kegiatan").val(response.kegiatan.id_giat);

				}
		});	
	});

	jQuery(document).on('click', '.btn-hapus-kegiatan', function(){

		if(confirm('Data akan dihapus, lanjut?')){

			jQuery('#wrap-loading').show();
			
			let id = jQuery(this).data('id');	
			let id_unik = jQuery(this).data('kodekegiatan');
			let id_program = jQuery(this).data('idprogram');
			let kode_program = jQuery(this).data('kodeprogram');

			jQuery.ajax({
				method:'POST',
				url:ajax.url,
				dataType:'json',
				data:{
					'action': 'delete_kegiatan_renstra',
		      'api_key': '<?php echo $api_key; ?>',
					'id': id,
					'id_unik': id_unik,
				},
				success:function(response){

					alert(response.message);
					if(response.status){
						kegiatanRenstra({
							'id_program': id_program,
							'kode_program': kode_program
						});
					}
					jQuery('#wrap-loading').hide();

				}
			})
		}
	});

	jQuery(document).on('click', '.btn-kelola-indikator-kegiatan', function(){
        jQuery("#modal-indikator-renstra").find('.modal-body').html('');
				indikatorKegiatanRenstra({'id_unik':jQuery(this).data('kodekegiatan')});
	});

	jQuery(document).on('click', '.btn-add-indikator-kegiatan', function(){

		let indikatorKegiatanModal = jQuery("#modal-crud-renstra");
		let id_unik = jQuery(this).data('kodekegiatan');
		let html = '<form id="form-renstra">'
					+'<input type="hidden" name="id_unik" value="'+id_unik+'">'
					+'<div class="form-group">'
						+'<label for="indikator_teks">Indikator</label>'
		  				+'<textarea class="form-control" name="indikator_teks"></textarea>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="satuan">Satuan</label>'
		  				+'<input type="text" class="form-control" name="satuan"/>'
					+'</div>'
					+'<div class="form-group">'
							+'<div class="row">'
								+'<div class="col-md-6">'
									+'<label for="target_1">Target tahun ke-1</label>'
			  						+'<input type="text" class="form-control" name="target_1"/>'
								+'</div>'
								+'<div class="col-md-6">'
									+'<label for="pagu_1">Pagu</label>'
			  						+'<input type="number" class="form-control" name="pagu_1"/>'
								+'</div>'
							+'</div>'
						+'</div>'
						+'<div class="form-group">'
							+'<div class="row">'
								+'<div class="col-md-6">'
									+'<label for="target_2">Target tahun ke-2</label>'
			  						+'<input type="text" class="form-control" name="target_2"/>'
								+'</div>'
								+'<div class="col-md-6">'
									+'<label for="pagu_2">Pagu</label>'
			  						+'<input type="number" class="form-control" name="pagu_2"/>'
								+'</div>'
							+'</div>'
						+'</div>'
						+'<div class="form-group">'
							+'<div class="row">'
								+'<div class="col-md-6">'
									+'<label for="target_3">Target tahun ke-3</label>'
					  				+'<input type="text" class="form-control" name="target_3"/>'
								+'</div>'
								+'<div class="col-md-6">'
			  						+'<label for="pagu_3">Pagu</label>'
			  						+'<input type="number" class="form-control" name="pagu_3"/>'
								+'</div>'
							+'</div>'
						+'</div>'
						+'<div class="form-group">'
							+'<div class="row">'
								+'<div class="col-md-6">'
									+'<label for="target_4">Target tahun ke-4</label>'
					  				+'<input type="text" class="form-control" name="target_4"/>'
								+'</div>'
								+'<div class="col-md-6">'
			  						+'<label for="pagu_4">Pagu</label>'
			  						+'<input type="number" class="form-control" name="pagu_4"/>'
								+'</div>'
							+'</div>'
						+'</div>'
						+'<div class="form-group">'
							+'<div class="row">'
								+'<div class="col-md-6">'
									+'<label for="target_5">Target tahun ke-5</label>'
					  				+'<input type="text" class="form-control" name="target_5"/>'
								+'</div>'
								+'<div class="col-md-6">'
			  						+'<label for="pagu_5">Pagu</label>'
			  						+'<input type="number" class="form-control" name="pagu_5"/>'
								+'</div>'
							+'</div>'	
						+'</div>'
					+'<div class="form-group">'
						+'<label for="target_awal">Target awal</label>'
		  				+'<input type="text" class="form-control" name="target_awal"/>'
					+'</div>'
					+'<div class="form-group">'
						+'<label for="target_akhir">Target akhir</label>'
		  				+'<input type="text" class="form-control" name="target_akhir"/>'
					+'</div>'
					+'</form>';

			indikatorKegiatanModal.find('.modal-title').html('Tambah Indikator');
			indikatorKegiatanModal.find('.modal-body').html(html);
			indikatorKegiatanModal.find('.modal-footer').html(''
				+'<button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">'
					+'<i class="dashicons dashicons-no" style="margin-top: 3px;"></i> Tutup'
				+'</button>'
				+'<button type="button" class="btn btn-sm btn-success" id="btn-simpan-data-renstra-lokal" '
					+'data-action="submit_indikator_kegiatan_renstra" '
					+'data-view="indikatorKegiatanRenstra"'
				+'>'
					+'<i class="dashicons dashicons-yes" style="margin-top: 3px;"></i> Simpan'
				+'</button>');
			indikatorKegiatanModal.modal('show');
	});

	jQuery(document).on('click', '.btn-edit-indikator-kegiatan', function(){

		jQuery('#wrap-loading').show();

		let id = jQuery(this).data('id');
		let kode_kegiatan = jQuery(this).data('kodekegiatan');

		jQuery.ajax({
			url: ajax.url,
          	type: "post",
          	data: {
          		"action": "edit_indikator_kegiatan_renstra",
          		"api_key": "<?php echo $api_key; ?>",
							'id': id,
							'kode_kegiatan': kode_kegiatan
          	},
          	dataType: "json",
          	success: function(response){

	          		jQuery('#wrap-loading').hide();

	          		let html = '<form id="form-renstra">'
									+'<input type="hidden" name="id" value="'+id+'">'
									+'<input type="hidden" name="id_unik" value="'+kode_kegiatan+'">'
									+'<div class="form-group">'
										+'<label for="indikator_teks">Indikator</label>'
					  					+'<textarea class="form-control" name="indikator_teks">'+response.data.indikator+'</textarea>'
									+'</div>'
									+'<div class="form-group">'
										+'<label for="satuan">Satuan</label>'
					  					+'<input type="text" class="form-control" name="satuan" value="'+response.data.satuan+'"/>'
									+'</div>'
									+'<div class="form-group">'
									+'<div class="row">'
										+'<div class="col-md-6">'
											+'<label for="target_1">Target tahun ke-1</label>'
						  					+'<input type="text" class="form-control" name="target_1" value="'+response.data.target_1+'"/>'
						  				+'</div>'
						  				+'<div class="col-md-6">'
						  					+'<label for="pagu_1">Pagu</label>'
						  					+'<input type="number" class="form-control" name="pagu_1" value="'+response.data.pagu_1+'"/>'
						  				+'</div>'
						  			+'</div>'
								+'</div>'
								+'<div class="form-group">'
									+'<div class="row">'
										+'<div class="col-md-6">'
											+'<label for="target_2">Target tahun ke-2</label>'
							  				+'<input type="text" class="form-control" name="target_2" value="'+response.data.target_2+'"/>'
							  			+'</div>'
							  			+'<div class="col-md-6">'
						  					+'<label for="pagu_2">Pagu</label>'
						  					+'<input type="number" class="form-control" name="pagu_2" value="'+response.data.pagu_2+'"/>'
						  				+'</div>'
								+'</div>'
								+'<div class="form-group">'
									+'<div class="row">'
										+'<div class="col-md-6">'
											+'<label for="target_3">Target tahun ke-3</label>'
						  					+'<input type="text" class="form-control" name="target_3" value="'+response.data.target_3+'"/>'
						  				+'</div>'
							  			+'<div class="col-md-6">'
							  				+'<label for="pagu_3">Pagu</label>'
							  				+'<input type="number" class="form-control" name="pagu_3" value="'+response.data.pagu_3+'"/>'
							  			+'</div>'
						  			+'</div>'
								+'</div>'
								+'<div class="form-group">'
									+'<div class="row">'
										+'<div class="col-md-6">'
											+'<label for="target_4">Target tahun ke-4</label>'
						  					+'<input type="text" class="form-control" name="target_4" value="'+response.data.target_4+'"/>'
										+'</div>'
										+'<div class="col-md-6">'
						  					+'<label for="pagu_4">Pagu</label>'
						  					+'<input type="number" class="form-control" name="pagu_4" value="'+response.data.pagu_4+'"/>'
						  				+'</div>'
									+'</div>'
								+'</div>'
								+'<div class="form-group">'
									+'<div class="row">'
										+'<div class="col-md-6">'
											+'<label for="target_5">Target tahun ke-5</label>'
						  					+'<input type="text" class="form-control" name="target_5" value="'+response.data.target_5+'"/>'
										+'</div>'
										+'<div class="col-md-6">'
						  					+'<label for="pagu_5">Pagu</label>'
						  					+'<input type="number" class="form-control" name="pagu_5" value="'+response.data.pagu_5+'"/>'
						  				+'</div>'
									+'</div>'
								+'</div>'
									+'<div class="form-group">'
										+'<label for="target_awal">Target awal</label>'
					  					+'<input type="text" class="form-control" name="target_awal" value="'+response.data.target_awal+'"/>'
									+'</div>'
									+'<div class="form-group">'
									+'<label for="target_akhir">Target akhir</label>'
					  					+'<input type="text" class="form-control" name="target_akhir" value="'+response.data.target_akhir+'"/>'
									+'</div>'
								  +'</form>';

								jQuery("#modal-crud-renstra").find('.modal-title').html('Edit Indikator Kegiatan');
								jQuery("#modal-crud-renstra").find('.modal-body').html(html);
								jQuery("#modal-crud-renstra").find('.modal-footer').html(''
									+'<button type="button" class="btn btn-sm btn-warning" data-dismiss="modal">'
										+'<i class="dashicons dashicons-no" style="margin-top: 3px;"></i> Tutup'
									+'</button><button type="button" class="btn btn-sm btn-success" id="btn-simpan-data-renstra-lokal" '
										+'data-action="update_indikator_kegiatan_renstra" '
										+'data-view="indikatorKegiatanRenstra"'
									+'>'
										+'<i class="dashicons dashicons-yes" style="margin-top: 3px;"></i> Simpan'
									+'</button>');	

								jQuery("#modal-crud-renstra").modal('show');
          	}
		})	
	});

	jQuery(document).on('click', '.btn-delete-indikator-kegiatan', function(){

		if(confirm('Data akan dihapus, lanjut?')){

			jQuery('#wrap-loading').show();
			
			let id = jQuery(this).data('id');	
			let kode_kegiatan = jQuery(this).data('kodekegiatan');

			jQuery.ajax({
				method:'POST',
				url:ajax.url,
				dataType:'json',
				data:{
					'action': 'delete_indikator_kegiatan_renstra',
		      'api_key': '<?php echo $api_key; ?>',
					'id': id,
				},
				success:function(response){

					alert(response.message);
					if(response.status){
						indikatorKegiatanRenstra({
							'id_unik': kode_kegiatan
						});
					}
					jQuery('#wrap-loading').hide();

				}
			})
		}
	});

	jQuery(document).on('click', '#btn-simpan-data-renstra-lokal', function(){
		
		jQuery('#wrap-loading').show();
		let renstraModal = jQuery("#modal-crud-renstra");
		let action = jQuery(this).data('action');
		let view = jQuery(this).data('view');
		let form = getFormData(jQuery("#form-renstra"));
		
		jQuery.ajax({
			method:'POST',
			url:ajax.url,
			dataType:'json',
			data:{
				'action': action,
	      'api_key': '<?php echo $api_key; ?>',
				'data': JSON.stringify(form),
			},
			success:function(response){
				jQuery('#wrap-loading').hide();
				alert(response.message);
				if(response.status){
					runFunction(view, [form])
					renstraModal.modal('hide');
				}
			}
		})
	});

	jQuery(document).on('change', '#urusan-teks', function(){
		get_bidang(jQuery(this).val());
		get_program();
	});

	jQuery(document).on('change', '#bidang-teks', function(){
		get_program(jQuery(this).val());
	});

	function pilihJadwal(that){
		jQuery("#wrap-loading").show();
		if(that.value != ''){
			jQuery.ajax({
				method:'POST',
				url:ajax.url,
				dataType:'json',
				data:{
					'action': 'get_sasaran_rpjm_history',
		      'api_key': '<?php echo $api_key; ?>',
		      'id_jadwal': that.value,
		      'id_unit': '<?php echo $input['id_skpd'] ?>',
				},
				success:function(response){
					jQuery("#wrap-loading").hide();
					let html = '<option value="">Pilih Sasaran Rpjm</option>';
					response.data.map(function(value, index){
						html +='<option value="'+value.id_unik+'|'+value.id_program+'">'+value.sasaran_teks+'</option>'
					});
					jQuery("#sasaran-parent").html(html);
				}
			});
		}
	}

	function pilihSasaranParent(that){
		if(that.value !=""){
			jQuery("#tujuan_teks").val(jQuery("#sasaran-parent").find(':selected').text());
		}
	}

	function pilihKegiatan(that){
		if(that.value !=""){
			jQuery("#kegiatan_teks").val(jQuery("#id_kegiatan").find(':selected').text());
		}
	}

	function tujuanRenstra(){
		
		jQuery('#wrap-loading').show();
		jQuery('#nav-tujuan').html('');
		jQuery('#nav-sasaran').html('');
		jQuery('#nav-program').html('');
		jQuery('#nav-kegiatan').html('');

		jQuery.ajax({
			url: ajax.url,
          	type: "post",
          	data: {
          		"action": "get_tujuan_renstra", // wpsipd-public-base-2
          		"api_key": "<?php echo $api_key; ?>",
          		"type": 1
          	},
          	dataType: "json",
          	success: function(res){
          		jQuery('#wrap-loading').hide();

          		let tujuan = ''
	          		+'<div style="margin-top:10px"><button type="button" class="btn btn-sm btn-primary mb-2 btn-tambah-tujuan"><i class="dashicons dashicons-plus" style="margin-top: 3px;"></i> Tambah Tujuan</button>'
	          		+'</div>'
	          		+'<table class="table">'
	          			+'<thead>'
	          				+'<tr>'
	          					+'<th style="width:5%">No.</th>'
	          					+'<th style="width:75%">Tujuan</th>'
	          					+'<th style="width:25%">Aksi</th>'
	          				+'<tr>'
	          			+'</thead>'
	          			+'<tbody>';
			          		res.data.map(function(value, index){
			          			tujuan +='<tr kodetujuan="'+value.id_unik+'">'
						          			+'<td>'+(index+1)+'.</td>'
						          			+'<td>'+value.tujuan_teks+'</td>'
						          			+'<td>'
						          					+'<a href="javascript:void(0)" data-idtujuan="'+value.id+'" data-idunik="'+value.id_unik+'" class="btn btn-sm btn-warning btn-kelola-indikator-tujuan"><i class="dashicons dashicons-menu-alt" style="margin-top: 3px;"></i></a>&nbsp;'
						          					+'<a href="javascript:void(0)" data-kodetujuan="'+value.id_unik+'" class="btn btn-sm btn-primary btn-detail-tujuan"><i class="dashicons dashicons-search"></i></a>&nbsp;'
						          					+'<a href="javascript:void(0)" data-id="'+value.id+'" class="btn btn-sm btn-success btn-edit-tujuan"><i class="dashicons dashicons-edit"></i></a>&nbsp;'
						          					+'<a href="javascript:void(0)" data-id="'+value.id+'" data-idunik="'+value.id_unik+'" class="btn btn-sm btn-danger btn-hapus-tujuan"><i class="dashicons dashicons-trash"></i></a>'
						          			+'</td>'
						          		+'</tr>';
			          		})
          			tujuan+='<tbody>'
          			+'</table>';

          		jQuery("#nav-tujuan").html(tujuan);
				jQuery('.nav-tabs a[href="#nav-tujuan"]').tab('show');
				jQuery('#modal-monev').modal('show');
        	}
		})
	}

	function indikatorTujuanRenstra(params){
		
		jQuery('#wrap-loading').show();

		jQuery.ajax({
			url: ajax.url,
          	type: "post",
          	data: {
          		"action": "get_indikator_tujuan_renstra",
          		"api_key": "<?php echo $api_key; ?>",
							'id_unik': params.id_unik,
							'type':1
          	},
          	dataType: "json",
          	success: function(response){
          		jQuery('#wrap-loading').hide();

          		let html=""
					+'<div style="margin-top:10px">'
						+"<button type=\"button\" class=\"btn btn-sm btn-primary mb-2 btn-add-indikator-tujuan\" data-kodetujuan=\""+params.id_unik+"\">"
								+"<i class=\"dashicons dashicons-plus\" style=\"margin-top: 3px;\"></i> Tambah Indikator"
						+"</button>"
					+'</div>'
          			+'<table class="table">'
	          			+'<thead>'
	          				+'<tr>'
	          					+'<th class="text-center" style="width: 160px;">Tujuan</th>'
	          					+'<th>'+jQuery('#nav-tujuan tr[kodetujuan="'+params.id_unik+'"]').find('td').eq(1).text()+'</th>'
	          				+'</tr>'
	          			+'</thead>'
          			+'</table>'
					+"<table class='table'>"
						+"<thead>"
							+"<tr>"
								+"<th>No.</th>"
								+"<th>Indikator</th>"
								+"<th>Satuan</th>"
								+"<th>Target 1</th>"
								+"<th>Target 2</th>"
								+"<th>Target 3</th>"
								+"<th>Target 4</th>"
								+"<th>Target 5</th>"
								+"<th>Target Awal</th>"
								+"<th>Target Akhir</th>"
								+"<th>Aksi</th>"
							+"</tr>"
						+"</thead>"
						+"<tbody id='indikator_tujuan'>";
						response.data.map(function(value, index){
			          			html +="<tr>"
						          		+"<td>"+(index+1)+".</td>"
						          		+"<td>"+value.indikator_teks+"</td>"
						          		+"<td>"+value.satuan+"</td>"
						          		+"<td>"+value.target_1+"</td>"
						          		+"<td>"+value.target_2+"</td>"
						          		+"<td>"+value.target_3+"</td>"
						          		+"<td>"+value.target_4+"</td>"
						          		+"<td>"+value.target_5+"</td>"
						          		+"<td>"+value.target_awal+"</td>"
						          		+"<td>"+value.target_akhir+"</td>"
						          		+"<td>"
						          			+"<a href='#' class='btn btn-sm btn-success btn-edit-indikator-tujuan' data-id='"+value.id+"' data-idunik='"+value.id_unik+"'><i class='dashicons dashicons-edit' style='margin-top: 3px;'></i></a>&nbsp"
											+"<a href='#' class='btn btn-sm btn-danger btn-delete-indikator-tujuan' data-id='"+value.id+"' data-idunik='"+value.id_unik+"'><i class='dashicons dashicons-trash' style='margin-top: 3px;'></i></a>&nbsp;"
						          		+"</td>"
						          	+"</tr>";
			          		});
		          	html+='</tbody></table>';

		          	jQuery("#modal-indikator-renstra").find('.modal-title').html('Indikator Tujuan');
		          	jQuery("#modal-indikator-renstra").find('.modal-body').html(html)
					jQuery("#modal-indikator-renstra").find('.modal-dialog').css('maxWidth','1250px');
					jQuery("#modal-indikator-renstra").find('.modal-dialog').css('width','100%');
					jQuery("#modal-indikator-renstra").modal('show');
          	}  	
		})
	}

	function sasaranRenstra(params){

		jQuery('#wrap-loading').show();
		
		jQuery.ajax({
			method:'POST',
			url:ajax.url,
			dataType:'json',
			data:{
				'action': 'get_sasaran_renstra',
	      'api_key': '<?php echo $api_key; ?>',
				'kode_tujuan': params.kode_tujuan,
				'type':1
			},
			success:function(response){

          		jQuery('#wrap-loading').hide();
          		
          		let sasaran = ''
          				+'<div style="margin-top:10px"><button type="button" class="btn btn-sm btn-primary mb-2 btn-tambah-sasaran" data-kodetujuan="'+params.kode_tujuan+'"><i class="dashicons dashicons-plus" style="margin-top: 3px;"></i> Tambah Sasaran</button></div>'
          				+'<table class="table">'
          					+'<thead>'
	          					+'<tr>'
	          						+'<th class="text-center" style="width: 160px;">Tujuan</th>'
	          						+'<th>'+jQuery('#nav-tujuan tr[kodetujuan="'+params.kode_tujuan+'"]').find('td').eq(1).text()+'</th>'
	          					+'</tr>'
          					+'</thead>'
          				+'</table>'
          				
          				+'<table class="table">'
          					+'<thead>'
          						+'<tr>'
          							+'<th style="width:5%">No.</th>'
          							+'<th style="width:75%">Sasaran</th>'
          							+'<th style="width:25%">Aksi</th>'
          						+'<tr>'
          					+'</thead>'
          					+'<tbody>';

          						response.data.map(function(value, index){
          							sasaran +='<tr kodesasaran="'+value.id_unik+'">'
			          							+'<td>'+(index+1)+'.</td>'
			          							+'<td>'+value.sasaran_teks+'</td>'
			          							+'<td>'
			          								+'<a href="javascript:void(0)" data-idsasaran="'+value.id+'" data-kodesasaran="'+value.id_unik+'" class="btn btn-sm btn-warning btn-kelola-indikator-sasaran"><i class="dashicons dashicons-menu-alt" style="margin-top: 3px;"></i></a>&nbsp;'
			          								+'<a href="javascript:void(0)" data-kodesasaran="'+value.id_unik+'" class="btn btn-sm btn-primary btn-detail-sasaran"><i class="dashicons dashicons-search" style="margin-top: 3px;"></i></a>&nbsp;'
			          								+'<a href="javascript:void(0)" data-idsasaran="'+value.id+'" class="btn btn-sm btn-success btn-edit-sasaran"><i class="dashicons dashicons-edit" style="margin-top: 3px;"></i></a>&nbsp;'
			          								+'<a href="javascript:void(0)" data-idsasaran="'+value.id+'" data-kodesasaran="'+value.id_unik+'" data-kodetujuan="'+value.kode_tujuan+'" class="btn btn-sm btn-danger btn-hapus-sasaran"><i class="dashicons dashicons-trash" style="margin-top: 3px;"></i></a>'
			          							+'</td>'
			          						+'</tr>';
          						})
          					sasaran +='<tbody>'
          				+'</table>';

			    jQuery("#nav-sasaran").html(sasaran);
			 	jQuery('.nav-tabs a[href="#nav-sasaran"]').tab('show');
			}
		})
	}

	function indikatorSasaranRenstra(params){

		jQuery('#wrap-loading').show();

		jQuery.ajax({
			url: ajax.url,
          	type: "post",
          	data: {
          		"action": "get_indikator_sasaran_renstra",
          		"api_key": "<?php echo $api_key; ?>",
							'id_unik': params.id_unik,
							"type": 1
          	},
          	dataType: "json",
          	success: function(response){
          		jQuery('#wrap-loading').hide();
          		
          		let html=""
									+'<div style="margin-top:10px">'
										+"<button type=\"button\" class=\"btn btn-sm btn-primary mb-2 btn-add-indikator-sasaran\" data-kodesasaran=\""+params.id_unik+"\">"
												+"<i class=\"dashicons dashicons-plus\" style=\"margin-top: 3px;\"></i> Tambah Indikator"
											+"</button>"
									+'</div>'
				          			+'<table class="table">'
					          			+'<thead>'
					          				+'<tr>'
					          					+'<th class="text-center" style="width: 160px;">Sasaran</th>'
					          					+'<th>'+jQuery('#nav-sasaran tr[kodesasaran="'+params.id_unik+'"]').find('td').eq(1).text()+'</th>'
					          				+'</tr>'
					          			+'</thead>'
				          			+'</table>'
									+"<table class='table'>"
										+"<thead>"
											+"<tr>"
												+"<th>No.</th>"
												+"<th>Indikator</th>"
												+"<th>Satuan</th>"
												+"<th>Target 1</th>"
												+"<th>Target 2</th>"
												+"<th>Target 3</th>"
												+"<th>Target 4</th>"
												+"<th>Target 5</th>"
												+"<th>Target Awal</th>"
												+"<th>Target Akhir</th>"
												+"<th>Aksi</th>"
											+"</tr>"
										+"</thead>"
										+"<tbody id='indikator_sasaran'>";
										response.data.map(function(value, index){
							          			html +="<tr>"
										          		+"<td>"+(index+1)+".</td>"
										          		+"<td>"+value.indikator_teks+"</td>"
										          		+"<td>"+value.satuan+"</td>"
										          		+"<td>"+value.target_1+"</td>"
										          		+"<td>"+value.target_2+"</td>"
										          		+"<td>"+value.target_3+"</td>"
										          		+"<td>"+value.target_4+"</td>"
										          		+"<td>"+value.target_5+"</td>"
										          		+"<td>"+value.target_awal+"</td>"
										          		+"<td>"+value.target_akhir+"</td>"
										          		+"<td>"
										          			+"<a href='#' class='btn btn-sm btn-success btn-edit-indikator-sasaran' data-id='"+value.id+"' data-idunik='"+value.id_unik+"' ><i class='dashicons dashicons-edit' style='margin-top: 3px;'></i></a>&nbsp"
																		+"<a href='#' class='btn btn-sm btn-danger btn-delete-indikator-sasaran' data-id='"+value.id+"' data-idunik='"+value.id_unik+"' ><i class='dashicons dashicons-trash' style='margin-top: 3px;'></i></a>&nbsp;"
										          		+"</td>"
										          	+"</tr>";
							          		});
						          	html+='</tbody></table>';

								jQuery("#modal-indikator-renstra").find('.modal-title').html('Indikator Sasaran');
						    jQuery("#modal-indikator-renstra").find('.modal-body').html(html);
								jQuery("#modal-indikator-renstra").find('.modal-dialog').css('maxWidth','1250px');
								jQuery("#modal-indikator-renstra").find('.modal-dialog').css('width','100%');
								jQuery("#modal-indikator-renstra").modal('show');
          	}
		})
	}

	function programRenstra(params){

		jQuery('#wrap-loading').show();

		jQuery.ajax({
			method:'POST',
			url:ajax.url,
			dataType:'json',
			data:{
				'action': 'get_program_renstra',
	      'api_key': '<?php echo $api_key; ?>',
				'kode_sasaran': params.kode_sasaran,
				'type':1
			},
			success:function(response){

          		jQuery('#wrap-loading').hide();
          		
          		let program = ''
          				+'<div style="margin-top:10px"><button type="button" class="btn btn-sm btn-primary mb-2 btn-tambah-program" data-kodesasaran="'+params.kode_sasaran+'"><i class="dashicons dashicons-plus" style="margin-top: 3px;"></i> Tambah Program</button></div>'
          				+'<table class="table">'
          					+'<thead>'
	          					+'<tr>'
	          						+'<th class="text-center" style="width: 160px;">Tujuan</th>'
	          						+'<th>'+jQuery('#nav-tujuan tr[kodetujuan="'+jQuery("#nav-sasaran .btn-tambah-sasaran").data("kodetujuan")+'"]').find('td').eq(1).text()+'</th>'
	          					+'</tr>'
	          					+'<tr>'
	          						+'<th class="text-center" style="width: 160px;">Sasaran</th>'
	          						+'<th>'+jQuery('#nav-sasaran tr[kodesasaran="'+params.kode_sasaran+'"]').find('td').eq(1).text()+'</th>'
	          					+'</tr>'
          					+'</thead>'
          				+'</table>'
          				
          				+'<table class="table">'
          					+'<thead>'
          						+'<tr>'
          							+'<th style="width:5%">No.</th>'
          							+'<th style="width:75%">Program</th>'
          							+'<th style="width:25%">Aksi</th>'
          						+'<tr>'
          					+'</thead>'
          					+'<tbody>';

          						response.data.map(function(value, index){
          							program +='<tr kodeprogram="'+value.id_unik+'">'
			          							+'<td>'+(index+1)+'.</td>'
			          							+'<td>'+value.nama_program+'</td>'
			          							+'<td>'
			          								+'<a href="javascript:void(0)" data-kodeprogram="'+value.id_unik+'" class="btn btn-sm btn-warning btn-kelola-indikator-program"><i class="dashicons dashicons-menu-alt" style="margin-top: 3px;"></i></a>&nbsp;'	
			          								+'<a href="javascript:void(0)" data-kodeprogram="'+value.id_unik+'" data-idprogram="'+value.id_program+'" class="btn btn-sm btn-primary btn-detail-program"><i class="dashicons dashicons-search" style="margin-top: 3px;"></i></a>&nbsp;'
			          								+'<a href="javascript:void(0)" data-id="'+value.id+'" data-kodeprogram="'+value.id_unik+'" class="btn btn-sm btn-success btn-edit-program"><i class="dashicons dashicons-edit" style="margin-top: 3px;"></i></a>&nbsp;'
			          								+'<a href="javascript:void(0)" data-id="'+value.id+'" data-kodeprogram="'+value.id_unik+'" data-kodesasaran="'+value.kode_sasaran+'" class="btn btn-sm btn-danger btn-hapus-program"><i class="dashicons dashicons-trash" style="margin-top: 3px;"></i></a></td>'
			          						+'</tr>';
          						})

          					program +='<tbody>'
          				+'</table>';

			    jQuery("#nav-program").html(program);
			 	jQuery('.nav-tabs a[href="#nav-program"]').tab('show');
			}
		})
	}

	function indikatorProgramRenstra(params){

		jQuery('#wrap-loading').show();

		jQuery.ajax({
			url: ajax.url,
          	type: "post",
          	data: {
          		"action": "get_indikator_program_renstra",
          		"api_key": "<?php echo $api_key; ?>",
							'kode_program': params.kode_program,
							"type": 1
          	},
          	dataType: "json",
          	success: function(response){

          		jQuery('#wrap-loading').hide();
          		
          		let html=""
							+'<div style="margin-top:10px">'
								+"<button type=\"button\" class=\"btn btn-sm btn-primary mb-2 btn-add-indikator-program\" data-kodeprogram=\""+params.kode_program+"\">"
										+"<i class=\"dashicons dashicons-plus\" style=\"margin-top: 3px;\"></i> Tambah Indikator"
								+"</button>"
							+'</div>'
		          			+'<table class="table">'
			          			+'<thead>'
			          				+'<tr>'
			          					+'<th class="text-center" style="width: 160px;">Program</th>'
			          					+'<th>'+jQuery('#nav-program tr[kodeprogram="'+params.kode_program+'"]').find('td').eq(1).text()+'</th>'
			          				+'</tr>'
			          			+'</thead>'
		          			+'</table>'

										+"<table class='table'>"
											+"<thead>"
												+"<tr>"
													+"<th>No.</th>"
													+"<th>Indikator</th>"
													+"<th>Satuan</th>"
													+"<th>Target 1</th>"
													+"<th>Target 2</th>"
													+"<th>Target 3</th>"
													+"<th>Target 4</th>"
													+"<th>Target 5</th>"
													+"<th>Target Awal</th>"
													+"<th>Target Akhir</th>"
													+"<th>Aksi</th>"
												+"</tr>"
											+"</thead>"
											+"<tbody id='indikator_program'>";
											response.data.map(function(value, index){
								          			html +="<tr>"
											          		+"<td>"+(index+1)+".</td>"
											          		+"<td>"+value.indikator+"</td>"
											          		+"<td>"+value.satuan+"</td>"
											          		+"<td>"+value.target_1+"</td>"
											          		+"<td>"+value.target_2+"</td>"
											          		+"<td>"+value.target_3+"</td>"
											          		+"<td>"+value.target_4+"</td>"
											          		+"<td>"+value.target_5+"</td>"
											          		+"<td>"+value.target_awal+"</td>"
											          		+"<td>"+value.target_akhir+"</td>"
											          		+"<td>"
											          			+"<a href='#' class='btn btn-sm btn-success btn-edit-indikator-program' data-kodeprogram='"+value.id_unik+"' data-id='"+value.id+"'><i class='dashicons dashicons-edit' style='margin-top: 3px;'></i></a>&nbsp"
																			+"<a href='#' class='btn btn-sm btn-danger btn-delete-indikator-program' data-kodeprogram='"+value.id_unik+"' data-id='"+value.id+"'><i class='dashicons dashicons-trash' style='margin-top: 3px;'></i></a>&nbsp;"
											          		+"</td>"
											          	+"</tr>";
								          		});
							          	html+='</tbody></table>';

								jQuery("#modal-indikator-renstra").find('.modal-title').html('Indikator Program');
						        jQuery("#modal-indikator-renstra").find('.modal-body').html(html);
								jQuery("#modal-indikator-renstra").find('.modal-dialog').css('maxWidth','1250px');
								jQuery("#modal-indikator-renstra").find('.modal-dialog').css('width','100%');
								jQuery("#modal-indikator-renstra").modal('show');
			}
		});
	}

	function kegiatanRenstra(params){

		jQuery('#wrap-loading').show();

		jQuery.ajax({
			method:'POST',
			url:ajax.url,
			dataType:'json',
			data:{
				'action': 'get_kegiatan_renstra',
	      'api_key': '<?php echo $api_key; ?>',
				'kode_program': params.kode_program,
				'type':1
			},
			success:function(response){

          		jQuery('#wrap-loading').hide();
          		
          		let kegiatan = ''
          				+'<div style="margin-top:10px"><button type="button" class="btn btn-sm btn-primary mb-2 btn-tambah-kegiatan" data-kodeprogram="'+params.kode_program+'" data-idprogram="'+params.id_program+'"><i class="dashicons dashicons-plus" style="margin-top: 3px;"></i> Tambah Kegiatan</button></div>'
          				+'<table class="table">'
          					+'<thead>'
	          					+'<tr>'
	          						+'<th class="text-center" style="width: 160px;">Tujuan</th>'
	          						+'<th>'+jQuery('#nav-tujuan tr[kodetujuan="'+jQuery("#nav-sasaran .btn-tambah-sasaran").data("kodetujuan")+'"]').find('td').eq(1).text()+'</th>'
	          					+'</tr>'

	          					+'<tr>'
	          						+'<th class="text-center" style="width: 160px;">Tujuan</th>'
	          						+'<th>'+jQuery('#nav-sasaran tr[kodesasaran="'+jQuery("#nav-program .btn-tambah-program").data("kodesasaran")+'"]').find('td').eq(1).text()+'</th>'
	          					+'</tr>'
	          					+'<tr>'
	          						+'<th class="text-center" style="width: 160px;">Program</th>'
	          						+'<th>'+jQuery('#nav-program tr[kodeprogram="'+params.kode_program+'"]').find('td').eq(1).text()+'</th>'
	          					+'</tr>'
          					+'</thead>'
          				+'</table>'
          				
          				+'<table class="table">'
          					+'<thead>'
          						+'<tr>'
          							+'<th style="width:5%">No.</th>'
          							+'<th style="width:75%">Kegiatan</th>'
          							+'<th style="width:25%">Aksi</th>'
          						+'<tr>'
          					+'</thead>'
          					+'<tbody>';

          						response.data.map(function(value, index){
          							kegiatan +='<tr kodekegiatan="'+value.id_unik+'">'
			          							+'<td>'+(index+1)+'.</td>'
			          							+'<td>'+value.nama_giat+'</td>'
			          							+'<td>'
			          								+'<a href="javascript:void(0)" data-kodekegiatan="'+value.id_unik+'" class="btn btn-sm btn-warning btn-kelola-indikator-kegiatan"><i class="dashicons dashicons-menu-alt" style="margin-top: 3px;"></i></a>&nbsp;'	
			          								+'<a href="javascript:void(0)" data-id="'+value.id+'" data-kodekegiatan="'+value.id_unik+'" data-idprogram="'+value.id_program+'" class="btn btn-sm btn-success btn-edit-kegiatan"><i class="dashicons dashicons-edit" style="margin-top: 3px;"></i></a>&nbsp;'
			          								+'<a href="javascript:void(0)" data-id="'+value.id+'" data-kodekegiatan="'+value.id_unik+'" data-kodeprogram="'+value.kode_program+'" data-idprogram="'+value.id_program+'" class="btn btn-sm btn-danger btn-hapus-kegiatan"><i class="dashicons dashicons-trash" style="margin-top: 3px;"></i></a></td>'
			          						+'</tr>';
          						})

          					kegiatan +='<tbody>'
          				+'</table>';

			    jQuery("#nav-kegiatan").html(kegiatan);
			 	jQuery('.nav-tabs a[href="#nav-kegiatan"]').tab('show');
			}
		})
	}

	function indikatorKegiatanRenstra(params){

		jQuery('#wrap-loading').show();

		jQuery.ajax({
			url: ajax.url,
          	type: "post",
          	data: {
          		"action": "get_indikator_kegiatan_renstra",
          		"api_key": "<?php echo $api_key; ?>",
							'id_unik': params.id_unik,
							"type": 1
          	},
          	dataType: "json",
          	success: function(response){

          		jQuery('#wrap-loading').hide();
          		
          		let html=""
									+'<div style="margin-top:10px">'
										+"<button type=\"button\" class=\"btn btn-sm btn-primary mb-2 btn-add-indikator-kegiatan\" data-kodekegiatan=\""+params.id_unik+"\">"
												+"<i class=\"dashicons dashicons-plus\" style=\"margin-top: 3px;\"></i> Tambah Indikator"
										+"</button>"
									+'</div>'
				          			+'<table class="table">'
					          			+'<thead>'
					          				+'<tr>'
					          					+'<th class="text-center" style="width: 160px;">Kegiatan</th>'
					          					+'<th>'+jQuery('#nav-kegiatan tr[kodekegiatan="'+params.id_unik+'"]').find('td').eq(1).text()+'</th>'
					          				+'</tr>'
					          			+'</thead>'
				          			+'</table>'

									+"<table class='table'>"
										+"<thead>"
											+"<tr>"
												+"<th>No.</th>"
												+"<th>Indikator</th>"
												+"<th>Satuan</th>"
												+"<th>Target 1</th>"
												+"<th>Target 2</th>"
												+"<th>Target 3</th>"
												+"<th>Target 4</th>"
												+"<th>Target 5</th>"
												+"<th>Target Awal</th>"
												+"<th>Target Akhir</th>"
												+"<th>Aksi</th>"
											+"</tr>"
										+"</thead>"
										+"<tbody id='indikator_kegiatan'>";
										response.data.map(function(value, index){
							          html +="<tr>"
										          		+"<td>"+(index+1)+".</td>"
										          		+"<td>"+value.indikator+"</td>"
										          		+"<td>"+value.satuan+"</td>"
										          		+"<td>"+value.target_1+"</td>"
										          		+"<td>"+value.target_2+"</td>"
										          		+"<td>"+value.target_3+"</td>"
										          		+"<td>"+value.target_4+"</td>"
										          		+"<td>"+value.target_5+"</td>"
										          		+"<td>"+value.target_awal+"</td>"
										          		+"<td>"+value.target_akhir+"</td>"
										          		+"<td>"
										          			+"<a href='#' class='btn btn-sm btn-success btn-edit-indikator-kegiatan' data-kodekegiatan='"+value.id_unik+"' data-id='"+value.id+"'><i class='dashicons dashicons-edit' style='margin-top: 3px;'></i></a>&nbsp"
																		+"<a href='#' class='btn btn-sm btn-danger btn-delete-indikator-kegiatan' data-kodekegiatan='"+value.id_unik+"' data-id='"+value.id+"'><i class='dashicons dashicons-trash' style='margin-top: 3px;'></i></a>&nbsp;"
										          		+"</td>"
										          	+"</tr>";
							      });
						          html+='</tbody></table>';

										jQuery("#modal-indikator-renstra").find('.modal-title').html('Indikator kegiatan');
								    jQuery("#modal-indikator-renstra").find('.modal-body').html(html);
										jQuery("#modal-indikator-renstra").find('.modal-dialog').css('maxWidth','1250px');
										jQuery("#modal-indikator-renstra").find('.modal-dialog').css('width','100%');
										jQuery("#modal-indikator-renstra").modal('show');
									}
		});
	}

	function get_urusan() {
		var html = '<option value="">Pilih Urusan</option>';
		for(var nm_urusan in all_program){
			html += '<option>'+nm_urusan+'</option>';
		}
		jQuery('#urusan-teks').html(html);
	}

	function get_bidang(nm_urusan) {
		var html = '<option value="">Pilih Bidang</option>';
		if(nm_urusan){
			for(var nm_bidang in all_program[nm_urusan]){
				html += '<option>'+nm_bidang+'</option>';
			}
		}else{
			for(var nm_urusan in all_program){
				for(var nm_bidang in all_program[nm_urusan]){
					html += '<option>'+nm_bidang+'</option>';
				}
			}
		}
		jQuery('#bidang-teks').html(html);
	}

	function get_program(nm_bidang, val) {
		var html = '<option value="">Pilih Program</option>';
		var current_nm_urusan = jQuery('#urusan-teks').val();
		if(current_nm_urusan){
			if(nm_bidang){
				for(var nm_program in all_program[current_nm_urusan][nm_bidang]){
					var selected = '';
					if(val && val == all_program[current_nm_urusan][nm_bidang][nm_program].id_program){
						selected = 'selected';
					}
					html += '<option '+selected+' value="'+all_program[current_nm_urusan][nm_bidang][nm_program].id_program+'">'+nm_program+'</option>';
				}
			}else{
				for(var nm_bidang in all_program[current_nm_urusan]){
					for(var nm_program in all_program[current_nm_urusan][nm_bidang]){
						var selected = '';
						if(val && val == all_program[current_nm_urusan][nm_bidang][nm_program].id_program){
							selected = 'selected';
						}
						html += '<option '+selected+' value="'+all_program[current_nm_urusan][nm_bidang][nm_program].id_program+'">'+nm_program+'</option>';
					}
				}
			}
		}else{
			if(nm_bidang){
				for(var nm_urusan in all_program){
					if(all_program[nm_urusan][nm_bidang]){
						for(var nm_program in all_program[nm_urusan][nm_bidang]){
							var selected = '';
							if(val && val == all_program[nm_urusan][nm_bidang][nm_program].id_program){
								selected = 'selected';
							}
							html += '<option '+selected+' value="'+all_program[nm_urusan][nm_bidang][nm_program].id_program+'">'+nm_program+'</option>';
						}
					}
				}
			}else{
				for(var nm_urusan in all_program){
					for(var nm_bidang in all_program[nm_urusan]){
						for(var nm_program in all_program[nm_urusan][nm_bidang]){
							var selected = '';
							if(val && val == all_program[nm_urusan][nm_bidang][nm_program].id_program){
								selected = 'selected';
							}
							html += '<option '+selected+' value="'+all_program[nm_urusan][nm_bidang][nm_program].id_program+'">'+nm_program+'</option>';
						}
					}
				}
			}
		}
		jQuery('#program-teks').html(html);
	}

	function get_bidang_urusan(skpd){
		return new Promise(function(resolve, reject){
			if(!skpd){
				if(typeof all_program == 'undefined'){
					jQuery.ajax({
						url: ajax.url,
			          	type: "post",
			          	data: {
			          		"action": "get_bidang_urusan_renstra",
			          		"api_key": "<?php echo $api_key; ?>",
			          		"id_unit": "<?php echo $input['id_skpd']; ?>",
			        			'relasi_perencanaan': '<?php echo $relasi_perencanaan; ?>',
			        			'id_tipe_relasi': '<?php echo $id_tipe_relasi; ?>',
			          		"type": 1
			          	},
			          	dataType: "json",
			          	success: function(res){
							window.all_program = {};
							res.data.map(function(b, i){
								if(!all_program[b.nama_urusan]){
									all_program[b.nama_urusan] = {};
								}
								if(!all_program[b.nama_urusan][b.nama_bidang_urusan]){
									all_program[b.nama_urusan][b.nama_bidang_urusan] = {};
								}
								if(!all_program[b.nama_urusan][b.nama_bidang_urusan][b.nama_program]){
									all_program[b.nama_urusan][b.nama_bidang_urusan][b.nama_program] = b;
								}
							});
							resolve();
			          	}
		          });
				}else{
					resolve();
				}
			}else{
				if(typeof all_program == 'undefined'){
					jQuery.ajax({
						url: ajax.url,
			          	type: "post",
			          	data: {
			          		"action": "get_bidang_urusan",
			          		"api_key": "<?php echo $api_key; ?>",
			          		"id_unit": "<?php echo $input['id_skpd']; ?>",
			        			'relasi_perencanaan': '<?php echo $relasi_perencanaan; ?>',
			        			'id_tipe_relasi': '<?php echo $id_tipe_relasi; ?>',
			          		"type": 0
			          	},
			          	dataType: "json",
			          	success: function(res){
							window.all_skpd = {};
							res.data.map(function(b, i){
								if(!all_skpd[b.nama_urusan]){
									all_skpd[b.nama_urusan] = {};
								}
								if(!all_skpd[b.nama_urusan][b.nama_bidang_urusan]){
									all_skpd[b.nama_urusan][b.nama_bidang_urusan] = {};
								}
								if(!all_skpd[b.nama_urusan][b.nama_bidang_urusan][b.nama_skpd]){
									all_skpd[b.nama_urusan][b.nama_bidang_urusan][b.nama_skpd] = b;
								}
							});
							resolve();
			          	}
		          });
				}else{
					resolve();
				}
			}
		});
	}

	function set_data_jadwal_lokal() {
		var html = '<option value="">Pilih Jadwal</option>';
		for(var jadwal_lokal in all_jadwal_lokal){
			html += '<option value="'+jadwal_lokal.id_jadwal_lokal+'">'+jadwal_lokal.nama+'</option>';
		}
		jQuery('#jadwal_lokal').html(html);
	}

	function getFormData($form) {
	    let unindexed_array = $form.serializeArray();
	    let indexed_array = {};

	    jQuery.map(unindexed_array, function (n, i) {
	    	indexed_array[n['name']] = n['value'];
	    });

	    return indexed_array;
	}

	function runFunction(name, arguments){
	    var fn = window[name];
	    if(typeof fn !== 'function')
	        return;

	    fn.apply(window, arguments);
	}
</script>