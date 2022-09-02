<?php
class Wpsipd_Simda
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	
	private $opsi_nilai_rincian;

	private $status_koneksi_simda;
	
	public $custom_mapping;

	public function __construct($plugin_name, $version){

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->opsi_nilai_rincian = get_option( '_crb_simda_pagu' );
		$simda = get_option( '_crb_singkron_simda' );
		if($simda == 1){
			$this->status_koneksi_simda = true;
		}else{
			$this->status_koneksi_simda = false;
		}
		$this->custom_mapping = $this->get_custom_mapping_sub_keg();
	}

	function get_custom_mapping_sub_keg(){
		$data = get_option('_crb_custom_mapping_sub_keg_simda');
		$data = explode(',', $data);
		$data_all = array();
		foreach ($data as $k => $v) {
			$baris = explode('-', $v);
			if(count($baris) == 2){
				$sipd = explode('_', $baris[0]);
				$simda = explode('_', $baris[1]);
				$data_all[$v] = array(
					'sipd' => array(
						'kode_skpd' => $sipd[0],
						'kode_sub_keg' => $sipd[1]
					), 
					'simda' => array(
						'kode_skpd' => $simda[0],
						'kode_sub_keg' => $simda[1]
					)
				);
			}
		}
		return $data_all;
	}

	function singkronSimdaPembiayaan($opsi=array()){
		global $wpdb;
		$ret = array(
			'status'	=> 'success',
			'message'	=> 'Berhasil export SIMDA!'
		);
		if (!empty($_POST)) {
			if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option('_crb_api_key_extension' )) {
				if(!empty($_POST['tahun_anggaran'])){
					$tahun_anggaran = $_POST['tahun_anggaran'];
					$pembiayaan_all = array();
					$pembiayaan = $wpdb->get_results($wpdb->prepare("
						SELECT 
							*
						from data_pembiayaan
						where tahun_anggaran=%d
							AND id_skpd=%d
							AND active=1", $tahun_anggaran, $_POST['id_skpd'])
					, ARRAY_A);
					foreach ($pembiayaan as $k => $v) {
						$pembiayaan_all[$v['kode_akun']][] = $v;
					}

					$kd_unit_simda = explode('.', get_option('_crb_unit_'.$_POST['id_skpd']));
					if(empty($kd_unit_simda) || empty($kd_unit_simda[3])){
						$ret['status'] = 'error';
						$ret['message'] = 'Mapping SKPD tidak ditemukan! id_skpd='.$_POST['id_skpd'];
					}else{
						$_kd_urusan = $kd_unit_simda[0];
						$_kd_bidang = $kd_unit_simda[1];
						$kd_unit = $kd_unit_simda[2];
						$kd_sub_unit = $kd_unit_simda[3];
	                	$options = array(
	                        'query' => "
	                        DELETE from ta_pembiayaan_rinc
	                        where 
	                            tahun=".$tahun_anggaran
	                            .' and kd_urusan='.$_kd_urusan
	                            .' and kd_bidang='.$_kd_bidang
	                            .' and kd_unit='.$kd_unit
	                            .' and kd_sub='.$kd_sub_unit
	                            .' and kd_prog=0'
	                            .' and id_prog=0'
	                            .' and kd_keg=0'
	                    );
	                    // print_r($options); die();
	                    $this->CurlSimda($options);

	                	$options = array(
	                        'query' => "
	                        DELETE from ta_pembiayaan
	                        where 
	                            tahun=".$tahun_anggaran
	                            .' and kd_urusan='.$_kd_urusan
	                            .' and kd_bidang='.$_kd_bidang
	                            .' and kd_unit='.$kd_unit
	                            .' and kd_sub='.$kd_sub_unit
	                            .' and kd_prog=0'
	                            .' and id_prog=0'
	                            .' and kd_keg=0'
	                    );
	                    // print_r($options); die();
	                    $this->CurlSimda($options);
	                    
						foreach ($pembiayaan_all as $kode_akun => $v) {
							$akun = explode('.', $kode_akun);
			                $mapping_rek = $this->cekRekMapping(array(
								'tahun_anggaran' => $tahun_anggaran,
								'kode_akun' => $kode_akun,
								'kd_rek_0' => $akun[0],
								'kd_rek_1' => $akun[1],
								'kd_rek_2' => $akun[2],
								'kd_rek_3' => $akun[3],
								'kd_rek_4' => $akun[4],
								'kd_rek_5' => $akun[5],
			                ));

							$kd = explode('.', $kode_sub_giat[0]['kode_sub_giat']);
							$kd_urusan90 = (int) $kd[0];
							$kd_bidang90 = (int) $kd[1];
							$kd_program90 = (int) $kd[2];
							$kd_kegiatan90 = ((int) $kd[3]).'.'.$kd[4];
							$kd_sub_kegiatan = (int) $kd[5];

			                if(!empty($mapping_rek)){
		                        $options = array(
		                            'query' => "
		                            INSERT INTO ta_pembiayaan (
		                                tahun,
		                                kd_urusan,
		                                kd_bidang,
		                                kd_unit,
		                                kd_sub,
		                                kd_prog,
		                                id_prog,
		                                kd_keg,
		                                kd_rek_1,
			                            kd_rek_2,
			                            kd_rek_3,
			                            kd_rek_4,
			                            kd_rek_5,
		                                kd_sumber
		                            )
		                            VALUES (
		                                ".$tahun_anggaran.",
		                                ".$_kd_urusan.",
		                                ".$_kd_bidang.",
		                                ".$kd_unit.",
		                                ".$kd_sub_unit.",
		                                0,
		                                0,
		                                0,
		                                ".$mapping_rek[0]->kd_rek_1.",
			                            ".$mapping_rek[0]->kd_rek_2.",
			                            ".$mapping_rek[0]->kd_rek_3.",
			                            ".$mapping_rek[0]->kd_rek_4.",
			                            ".$mapping_rek[0]->kd_rek_5.",
		                                null
		                            )"
		                        );
								// print_r($v); die($kode_akun);
								$this->CurlSimda($options);
								$no_rinc = 0;
								foreach ($v as $kk => $vv) {
									$no_rinc++;
									$total_rinci = $vv['total'];
									if($this->opsi_nilai_rincian == 2){
										$total_rinci = $vv['nilaimurni'];
									}
									$options = array(
			                            'query' => "
			                            INSERT INTO ta_pembiayaan_rinc (
			                                tahun,
			                                kd_urusan,
			                                kd_bidang,
			                                kd_unit,
			                                kd_sub,
			                                kd_prog,
			                                id_prog,
			                                kd_keg,
			                                kd_rek_1,
				                            kd_rek_2,
				                            kd_rek_3,
				                            kd_rek_4,
				                            kd_rek_5,
			                                no_id,
			                                sat_1,
			                                nilai_1,
			                                sat_2,
			                                nilai_2,
			                                sat_3,
			                                nilai_3,
			                                satuan123,
			                                jml_satuan,
			                                nilai_rp,
			                                total,
			                                keterangan
			                            )
			                            VALUES (
			                                ".$tahun_anggaran.",
			                                ".$_kd_urusan.",
			                                ".$_kd_bidang.",
			                                ".$kd_unit.",
			                                ".$kd_sub_unit.",
			                                0,
			                                0,
			                                0,
			                                ".$mapping_rek[0]->kd_rek_1.",
				                            ".$mapping_rek[0]->kd_rek_2.",
				                            ".$mapping_rek[0]->kd_rek_3.",
				                            ".$mapping_rek[0]->kd_rek_4.",
				                            ".$mapping_rek[0]->kd_rek_5.",
			                                ".$no_rinc.",
			                                null,
			                                1,
			                                null,
			                                0,
			                                null,
			                                0,
			                                'Tahun',
			                                1,
			                                ".str_replace(',', '.', $total_rinci).",
			                                ".str_replace(',', '.', $total_rinci).",
			                                '".str_replace("'", '`', $vv['uraian'])."'
			                            )"
			                        );
									// print_r($options); die($kode_akun);
									$this->CurlSimda($options);
								}
			                }
						}
					}
				} else {
					$ret['status'] = 'error';
					$ret['message'] = 'Format singkron simda salah!';
				}
			} else {
				$ret['status'] = 'error';
				$ret['message'] = 'APIKEY tidak sesuai!';
			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format Salah!';
		}
		if(!empty($opsi['return'])){
			die(json_encode($ret));
		}
	}

	function singkronSimdaPendapatan($opsi=array()){
		global $wpdb;
		$ret = array(
			'status'	=> 'success',
			'message'	=> 'Berhasil export SIMDA!'
		);
		if (!empty($_POST)) {
			if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option('_crb_api_key_extension' )) {
				if(!empty($_POST['tahun_anggaran'])){
					$tahun_anggaran = $_POST['tahun_anggaran'];
					$pendapatan_all = array();
					foreach ($_POST['data'] as $k => $v) {
						$pendapatan = $wpdb->get_results($wpdb->prepare("
							SELECT 
								*
							from data_pendapatan
							where tahun_anggaran=%d
								AND id_pendapatan=%d
								AND active=1", $tahun_anggaran, $v['id_pendapatan'])
						, ARRAY_A);
						if(empty($pendapatan_all[$pendapatan[0]['kode_akun']])){
							$pendapatan_all[$pendapatan[0]['kode_akun']] = array();
						}
						foreach ($pendapatan as $key => $value) {
							$pendapatan_all[$pendapatan[0]['kode_akun']][] = $value;
						}
					}
					$kd_unit_simda = explode('.', get_option('_crb_unit_'.$_POST['id_skpd']));
					if(empty($kd_unit_simda) || empty($kd_unit_simda[3])){
						$ret['status'] = 'error';
						$ret['message'] = 'Mapping SKPD tidak ditemukan! id_skpd='.$_POST['id_skpd'];
					}else{
						$_kd_urusan = $kd_unit_simda[0];
						$_kd_bidang = $kd_unit_simda[1];
						$kd_unit = $kd_unit_simda[2];
						$kd_sub_unit = $kd_unit_simda[3];
	                	$options = array(
	                        'query' => "
	                        DELETE from ta_pendapatan_rinc
	                        where 
	                            tahun=".$tahun_anggaran
	                            .' and kd_urusan='.$_kd_urusan
	                            .' and kd_bidang='.$_kd_bidang
	                            .' and kd_unit='.$kd_unit
	                            .' and kd_sub='.$kd_sub_unit
	                            .' and kd_prog=0'
	                            .' and id_prog=0'
	                            .' and kd_keg=0'
	                    );
	                    // print_r($options); die();
	                    $this->CurlSimda($options);

	                	$options = array(
	                        'query' => "
	                        DELETE from ta_pendapatan
	                        where 
	                            tahun=".$tahun_anggaran
	                            .' and kd_urusan='.$_kd_urusan
	                            .' and kd_bidang='.$_kd_bidang
	                            .' and kd_unit='.$kd_unit
	                            .' and kd_sub='.$kd_sub_unit
	                            .' and kd_prog=0'
	                            .' and id_prog=0'
	                            .' and kd_keg=0'
	                    );
	                    // print_r($options); die();
	                    $this->CurlSimda($options);

						$no_pendapatan = 0;
						foreach ($pendapatan_all as $kode_akun => $v) {
							$no_pendapatan++;
							$akun = explode('.', $kode_akun);
			                $mapping_rek = $this->cekRekMapping(array(
								'tahun_anggaran' => $tahun_anggaran,
								'kode_akun' => $kode_akun,
								'kd_rek_0' => $akun[0],
								'kd_rek_1' => $akun[1],
								'kd_rek_2' => $akun[2],
								'kd_rek_3' => $akun[3],
								'kd_rek_4' => $akun[4],
								'kd_rek_5' => $akun[5],
			                ));

							$kd = explode('.', $kode_sub_giat[0]['kode_sub_giat']);
							$kd_urusan90 = (int) $kd[0];
							$kd_bidang90 = (int) $kd[1];
							$kd_program90 = (int) $kd[2];
							$kd_kegiatan90 = ((int) $kd[3]).'.'.$kd[4];
							$kd_sub_kegiatan = (int) $kd[5];

			                if(!empty($mapping_rek)){
		                        $options = array(
		                            'query' => "
		                            INSERT INTO ta_pendapatan (
		                                tahun,
		                                kd_urusan,
		                                kd_bidang,
		                                kd_unit,
		                                kd_sub,
		                                kd_prog,
		                                id_prog,
		                                kd_keg,
		                                kd_rek_1,
			                            kd_rek_2,
			                            kd_rek_3,
			                            kd_rek_4,
			                            kd_rek_5,
			                            kd_pendapatan,
		                                kd_sumber
		                            )
		                            VALUES (
		                                ".$tahun_anggaran.",
		                                ".$_kd_urusan.",
		                                ".$_kd_bidang.",
		                                ".$kd_unit.",
		                                ".$kd_sub_unit.",
		                                0,
		                                0,
		                                0,
		                                ".$mapping_rek[0]->kd_rek_1.",
			                            ".$mapping_rek[0]->kd_rek_2.",
			                            ".$mapping_rek[0]->kd_rek_3.",
			                            ".$mapping_rek[0]->kd_rek_4.",
			                            ".$mapping_rek[0]->kd_rek_5.",
			                            ".$mapping_rek[0]->kd_rek_2.",
		                                null
		                            )"
		                        );
								// print_r($options); die($kode_akun);
								$this->CurlSimda($options);
								$no_rinc = 0;
								foreach ($v as $kk => $vv) {
									$no_rinc++;
									$total_rinci = $vv['total'];
									if($this->opsi_nilai_rincian == 2){
										$total_rinci = $vv['nilaimurni'];
									}
									$options = array(
			                            'query' => "
			                            INSERT INTO ta_pendapatan_rinc (
			                                tahun,
			                                kd_urusan,
			                                kd_bidang,
			                                kd_unit,
			                                kd_sub,
			                                kd_prog,
			                                id_prog,
			                                kd_keg,
			                                kd_rek_1,
				                            kd_rek_2,
				                            kd_rek_3,
				                            kd_rek_4,
				                            kd_rek_5,
			                                no_id,
			                                sat_1,
			                                nilai_1,
			                                sat_2,
			                                nilai_2,
			                                sat_3,
			                                nilai_3,
			                                satuan123,
			                                jml_satuan,
			                                nilai_rp,
			                                total,
			                                keterangan
			                            )
			                            VALUES (
			                                ".$tahun_anggaran.",
			                                ".$_kd_urusan.",
			                                ".$_kd_bidang.",
			                                ".$kd_unit.",
			                                ".$kd_sub_unit.",
			                                0,
			                                0,
			                                0,
			                                ".$mapping_rek[0]->kd_rek_1.",
				                            ".$mapping_rek[0]->kd_rek_2.",
				                            ".$mapping_rek[0]->kd_rek_3.",
				                            ".$mapping_rek[0]->kd_rek_4.",
				                            ".$mapping_rek[0]->kd_rek_5.",
			                                ".$no_rinc.",
			                                null,
			                                1,
			                                null,
			                                0,
			                                null,
			                                0,
			                                'Tahun',
			                                1,
			                                ".str_replace(',', '.', $total_rinci).",
			                                ".str_replace(',', '.', $total_rinci).",
			                                '".str_replace("'", '`', $vv['uraian'])."'
			                            )"
			                        );
									// print_r($options); die($kode_akun);
									$this->CurlSimda($options);
								}
			                }
						}
					}
				} else {
					$ret['status'] = 'error';
					$ret['message'] = 'Format singkron simda salah!';
				}
			} else {
				$ret['status'] = 'error';
				$ret['message'] = 'APIKEY tidak sesuai!';
			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format Salah!';
		}
		if(!empty($opsi['return'])){
			die(json_encode($ret));
		}
	}

	function singkronRefUnit($opsi){
		global $wpdb;
		$kd = explode('.', $opsi['kode_skpd']);
		$bidang_mapping = $this->CurlSimda(array(
			'query' => 'select * from ref_bidang_mapping where kd_urusan90='.$kd[0].' and kd_bidang90='.((int)$kd[1])
		));
		$kode_unit90 = $kd[0].'-'.$kd[1].'.'.$kd[2].'-'.$kd[3].'.'.$kd[4].'-'.$kd[5].'.'.$kd[6];
		if($opsi['is_skpd'] == 0 && empty($opsi['only_unit'])){
			$unit = $wpdb->get_results($wpdb->prepare("
				SELECT 
					nama_skpd 
				from data_unit 
				where tahun_anggaran=%d
					AND id_skpd=%d
					AND active=1", $opsi['tahun_anggaran'], $opsi['idinduk'])
			, ARRAY_A);
			$nama_unit = $unit[0]['nama_skpd'];
		}else{
			$nama_unit = $opsi['nama_skpd'];
		}

		$kd_urusan = $bidang_mapping[0]->kd_urusan;
		$kd_bidang = $bidang_mapping[0]->kd_bidang;
		$x_mapping_skpd = array();
		$mapping_skpd = get_option( '_crb_unit_'.$opsi['id_skpd'] );
		if(!empty($mapping_skpd)){
			$x_mapping_skpd = explode('.', $mapping_skpd);
			$kd_urusan = $x_mapping_skpd[0];
			if(!empty($x_mapping_skpd[1])){
				$kd_bidang = $x_mapping_skpd[1];
			}
		}
		if(!empty($x_mapping_skpd[2])){
			$cek_unit = $this->CurlSimda(array(
				'query' => 'select * from ref_unit where kd_urusan='.$kd_urusan.' and kd_bidang='.$kd_bidang.' and kd_unit='.$x_mapping_skpd[2]
			));
		}else{
			$cek_unit = $this->CurlSimda(array(
				'query' => 'select * from ref_unit where kd_urusan='.$kd_urusan.' and kd_bidang='.$kd_bidang.' and nm_unit=\''.$nama_unit.'\''
			));
		}
		if(empty($cek_unit)){
			if(!empty($x_mapping_skpd[2])){
				$no_unit = $x_mapping_skpd[2];
			}else{
				$no_unit = $this->CurlSimda(array(
					'query' => 'select max(kd_unit) as max_unit from ref_unit where kd_urusan='.$kd_urusan.' and kd_bidang='.$kd_bidang
				));
				if(empty($no_unit) || $no_unit[0]->max_unit == 0){
					$no_unit = 1;
				}else{
					$no_unit = $no_unit[0]->max_unit+1;
				}
			}
			if($opsi['is_skpd'] == 1){
				$this->CurlSimda(array(
					'query' => '
					INSERT INTO ref_unit (
						kd_urusan, 
						kd_bidang, 
						kd_unit, 
						nm_unit, 
						kd_unit90
					) VALUES (
						'.$kd_urusan.', 
						'.$kd_bidang.', 
						'.$no_unit.',
						\''.$opsi['nama_skpd'].'\',
						\''.$kode_unit90.'\'
					)'
				));
			}
		}else{
			$no_unit = $cek_unit[0]->kd_unit;
		}
		$no_sub_unit = 1;

		if(empty($opsi['only_unit'])){
			if(!empty($x_mapping_skpd[3])){
				$cek_sub_unit = $this->CurlSimda(array(
				'query' => 'select * from ref_sub_unit where kd_urusan='.$kd_urusan.' and kd_bidang='.$kd_bidang.' and kd_unit='.$no_unit.' and kd_sub='.$x_mapping_skpd[3]
				));
			}else{
				$cek_sub_unit = $this->CurlSimda(array(
				'query' => 'select * from ref_sub_unit where kd_urusan='.$kd_urusan.' and kd_bidang='.$kd_bidang.' and kd_unit='.$no_unit.' and nm_sub_unit=\''.$opsi['nama_skpd'].'\''
				));
			}
			if(empty($cek_sub_unit)){
				if(!empty($x_mapping_skpd[3])){
					$no_sub_unit = $x_mapping_skpd[3];
				}else{
					$no_sub_unit = $this->CurlSimda(array(
						'query' => 'select max(kd_sub) as max_unit from ref_sub_unit where kd_urusan='.$kd_urusan.' and kd_bidang='.$kd_bidang.' and kd_unit='.$no_unit
					));
					if(empty($no_sub_unit) || $no_sub_unit[0]->max_unit == 0){
						$no_sub_unit = 1;
					}else{
						$no_sub_unit = $no_sub_unit[0]->max_unit+1;
					}
				}
				$this->CurlSimda(array(
					'query' => '
					INSERT INTO ref_sub_unit (
						kd_urusan, 
						kd_bidang, 
						kd_unit, 
						kd_sub, 
						nm_sub_unit
					) VALUES (
						'.$kd_urusan.', 
						'.$kd_bidang.', 
						'.$no_unit.',
						'.$no_sub_unit.',
						\''.$opsi['nama_skpd'].'\'
					)'
				));
			}else{
				$no_sub_unit = $cek_sub_unit[0]->kd_sub;
			}
		}
		$kd_sub_unit_simda = $kd_urusan.'.'.$kd_bidang.'.'.$no_unit.'.'.$no_sub_unit;
		if(empty($x_mapping_skpd[3])){
			update_option( '_crb_unit_'.$opsi['id_skpd'], $kd_sub_unit_simda );
		}
		return $kd_sub_unit_simda;
	}

	function singkronSimdaUnit($opsi=array()){
		global $wpdb;
		$ret = array(
			'status'	=> 'success',
			'message'	=> 'Berhasil export SIMDA!'
		);
		if(!empty($opsi['res'])){
			$ret = $opsi['res'];
		}
		if (!empty($_POST)) {
			if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option('_crb_api_key_extension' )) {
				if(!empty($_POST['data_unit']) && !empty($_POST['tahun_anggaran'])){
					$ref_unit_all = array();
					if(get_option('_crb_singkron_simda_unit') == 1){
						// singkron unit dulu
						foreach ($_POST['data_unit'] as $k => $v) {
							$v['only_unit'] = true;
							$this->singkronRefUnit($v);
						}
						// singkron sub unit
						foreach ($_POST['data_unit'] as $k => $v) {
							$v['tahun_anggaran'] = $_POST['tahun_anggaran'];
							$this->singkronRefUnit($v);
						}
					}
					foreach ($_POST['data_unit'] as $k => $v) {
						$kd_unit_simda = explode('.', get_option('_crb_unit_'.$v['id_skpd']));
						if(empty($kd_unit_simda) || empty($kd_unit_simda[3])){
							continue;
						}
						$tahun_anggaran = $_POST['tahun_anggaran'];
						$unit = $wpdb->get_results($wpdb->prepare("
							SELECT 
								* 
							from data_unit 
							where tahun_anggaran=%d
								AND id_skpd=%d
								AND active=1", $tahun_anggaran, $v['id_skpd'])
						, ARRAY_A);
						$_kd_urusan = $kd_unit_simda[0];
						$_kd_bidang = $kd_unit_simda[1];
						$kd_unit = $kd_unit_simda[2];
						$kd_sub_unit = $kd_unit_simda[3];

						$cek_ta_sub_unit = $this->CurlSimda(array(
							'query' => "
								SELECT 
									* 
								from ta_sub_unit 
								where tahun=".$tahun_anggaran
		                            .' and kd_urusan='.$_kd_urusan
		                            .' and kd_bidang='.$_kd_bidang
		                            .' and kd_unit='.$kd_unit
		                            .' and kd_sub='.$kd_sub_unit
						));
						if(!empty($cek_ta_sub_unit)){
							$options = array(
	                            'query' => "
	                            UPDATE ta_sub_unit set
	                                nm_pimpinan = '".$unit[0]['namakepala']."',
	                                nip_pimpinan = '".$unit[0]['nipkepala']."'
	                            where 
		                            tahun=".$tahun_anggaran
		                            .' and kd_urusan='.$_kd_urusan
		                            .' and kd_bidang='.$_kd_bidang
		                            .' and kd_unit='.$kd_unit
		                            .' and kd_sub='.$kd_sub_unit
	                        );
							$this->CurlSimda($options);
							/*
							*/
	                    }else{
	                        $options = array(
	                            'query' => "
	                            INSERT INTO ta_sub_unit (
	                                tahun,
	                                kd_urusan,
	                                kd_bidang,
	                                kd_unit,
	                                kd_sub,
	                                nm_pimpinan,
	                                nip_pimpinan
	                            )
	                            VALUES (
	                                ".$tahun_anggaran.",
	                                ".$_kd_urusan.",
	                                ".$_kd_bidang.",
	                                ".$kd_unit.",
	                                ".$kd_sub_unit.",
	                                '".str_replace("'", '`', $unit[0]['namakepala'])."',
	                                '".$unit[0]['nipkepala']."'
	                            )"
	                        );
							// print_r($options); die($v['id_skpd']);
							$this->CurlSimda($options);
						}
						// print_r($options); die($v['id_skpd']);
						// $this->CurlSimda($options);
					}
				} else {
					$ret['status'] = 'error';
					$ret['message'] = 'Format singkron simda salah!';
				}
			} else {
				$ret['status'] = 'error';
				$ret['message'] = 'APIKEY tidak sesuai!';
			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format Salah!';
		}
		if(!empty($opsi['return'])){
			die(json_encode($ret));
		}
	}

	function singkronSimdaKas($opsi=array()){
		global $wpdb;
		$ret = array(
			'status'	=> 'success',
			'message'	=> 'Berhasil export SIMDA!'
		);
		if (!empty($_POST)) {
			if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option('_crb_api_key_extension' )) {
				if(!empty($_POST['data']) && !empty($_POST['tahun_anggaran'])){
					$tahun_anggaran = $_POST['tahun_anggaran'];
					$type = $_POST['type'];
					if($type == 'belanja'){
						$kode_sbl = explode('.', $_POST['kode_sbl']);
						unset($kode_sbl[0]);
						unset($kode_sbl[3]);
						$sub_giat = $wpdb->get_results($wpdb->prepare("
							SELECT 
								k.kode_sub_giat,
								k.nama_giat, 
								k.nama_sub_giat 
							from data_sub_keg_bl k
							where k.tahun_anggaran=%d
								AND k.kode_sbl=%s
								AND k.active=1", $tahun_anggaran, implode('.', $kode_sbl))
						, ARRAY_A);
						if(empty($sub_giat)){
							$ret['status'] = 'error';
							$ret['message'] = 'Data Sub Kegaitan di table data_sub_keg_bl dengan kode_sbl=\''.implode('.', $kode_sbl).'\' tidak ditemukan. Lakukan singkronisasi dulu di SIPD Merah!';
						}
						// print_r($sub_giat); die($wpdb->last_query);
					}

					if($ret['status']!='error'){
						foreach ($_POST['data'] as $k => $v) {
							if(!empty($v['id_sub_skpd'])){
								$kd_unit_simda = explode('.', get_option('_crb_unit_'.$v['id_sub_skpd']));
								$id_unit_sipd = $v['id_sub_skpd'];
							}else if(!empty($v['id_unit'])){
								$kd_unit_simda = explode('.', get_option('_crb_unit_'.$v['id_unit']));
								$id_unit_sipd = $v['id_unit'];
							}else{
								$kd_unit_simda = explode('.', get_option('_crb_unit_'.$v['id_skpd']));
								$id_unit_sipd = $v['id_skpd'];
							}
							$unit_sipd = $wpdb->get_results($wpdb->prepare("
								SELECT 
									u.kode_skpd 
								from data_unit u
								where u.tahun_anggaran=%d
									AND u.id_skpd=%d
									AND u.active=1", $tahun_anggaran, $id_unit_sipd)
							, ARRAY_A);
							if($type == 'belanja'){
								$kd_unit_simda = explode('.', get_option('_crb_unit_'.$kode_sbl[2]));
							}
							if($type == 'pendapatan'){
								$kd_unit_simda = explode('.', get_option('_crb_unit_'.$v['id_unit']));
							}

							if(empty($kd_unit_simda) || empty($kd_unit_simda[3])){
								continue;
							}
							$_kd_urusan = $kd_unit_simda[0];
							$_kd_bidang = $kd_unit_simda[1];
							$kd_unit = $kd_unit_simda[2];
							$kd_sub_unit = $kd_unit_simda[3];

							$rak = $wpdb->get_results($wpdb->prepare("
								SELECT 
									k.*
								from data_anggaran_kas k
								where k.tahun_anggaran=%d
									AND k.kode_sbl=%s
									AND k.id_akun=%d
									AND k.active=1", $tahun_anggaran, $_POST['kode_sbl'], $v['id_akun'])
							, ARRAY_A);
							
							$akun = explode('.', $rak[0]['kode_akun']);
			                $mapping_rek = $this->cekRekMapping(array(
								'tahun_anggaran' => $tahun_anggaran,
								'kode_akun' => $rak[0]['kode_akun'],
								'kd_rek_0' => $akun[0],
								'kd_rek_1' => $akun[1],
								'kd_rek_2' => $akun[2],
								'kd_rek_3' => $akun[3],
								'kd_rek_4' => $akun[4],
								'kd_rek_5' => $akun[5],
			                ));

							if($type == 'belanja'){
								$kd = explode('.', $sub_giat[0]['kode_sub_giat']);
								$kd_urusan90 = (int) $kd[0];
								$kd_bidang90 = (int) $kd[1];
								$kd_program90 = (int) $kd[2];
								$kd_kegiatan90 = ((int) $kd[3]).'.'.$kd[4];
								$kd_sub_kegiatan = (int) $kd[5];
								$nama_keg = explode(' ', $sub_giat[0]['nama_sub_giat']);
			                    unset($nama_keg[0]);
			                    $nama_keg = implode(' ', $nama_keg);
								$mapping = $this->cekKegiatanMapping(array(
									'kd_urusan90' => $kd_urusan90,
									'kd_bidang90' => $kd_bidang90,
									'kd_program90' => $kd_program90,
									'kd_kegiatan90' => $kd_kegiatan90,
									'kd_sub_kegiatan' => $kd_sub_kegiatan,
									'nama_program' => $sub_giat[0]['nama_giat'],
									'nama_kegiatan' => $nama_keg,
								));
				            }else{
				            	$mapping = true;
				            }
			                
			                if(!empty($mapping) && !empty($mapping_rek)){
			                	if($type == 'belanja'){
									$kd_urusan = $mapping[0]->kd_urusan;
									$kd_bidang = $mapping[0]->kd_bidang;
									$kd_prog = $mapping[0]->kd_prog;
									$kd_keg = $mapping[0]->kd_keg;
									foreach ($this->custom_mapping as $c_map_k => $c_map_v) {
										if(
											$unit_sipd[0]['kode_skpd'] == $c_map_v['sipd']['kode_skpd']
											&& $sub_giat[0]['kode_sub_giat'] == $c_map_v['sipd']['kode_sub_keg']
										){
											$kd_unit_simda_map = explode('.', $c_map_v['simda']['kode_skpd']);
											$_kd_urusan = $kd_unit_simda_map[0];
											$_kd_bidang = $kd_unit_simda_map[1];
											$kd_unit = $kd_unit_simda_map[2];
											$kd_sub_unit = $kd_unit_simda_map[3];
											$kd_keg_simda = explode('.', $c_map_v['simda']['kode_sub_keg']);
											$kd_urusan = $kd_keg_simda[0];
											$kd_bidang = $kd_keg_simda[1];
											$kd_prog = $kd_keg_simda[2];
											$kd_keg = $kd_keg_simda[3];
										}
									}
									$id_prog = $kd_urusan.$this->CekNull($kd_bidang);
								}else{
									$kd_urusan = 0;
									$kd_bidang = 0;
									$kd_prog = 0;
									$kd_keg = 0;
									$id_prog = 0;
								}
								$cek_ta_rencana = $this->CurlSimda(array(
									'query' => "
										SELECT 
											* 
										from ta_rencana 
										where tahun=".$tahun_anggaran
				                            .' and kd_urusan='.$_kd_urusan
				                            .' and kd_bidang='.$_kd_bidang
				                            .' and kd_unit='.$kd_unit
				                            .' and kd_sub='.$kd_sub_unit
				                            .' and kd_prog='.$kd_prog
				                            .' and id_prog='.$id_prog
				                            .' and kd_keg='.$kd_keg
				                            .' and kd_rek_1='.$mapping_rek[0]->kd_rek_1
				                            .' and kd_rek_2='.$mapping_rek[0]->kd_rek_2
				                            .' and kd_rek_3='.$mapping_rek[0]->kd_rek_3
				                            .' and kd_rek_4='.$mapping_rek[0]->kd_rek_4
				                            .' and kd_rek_5='.$mapping_rek[0]->kd_rek_5
								));
								if(!empty($cek_ta_rencana)){
									$options = array(
			                            'query' => "
			                            UPDATE ta_rencana set
			                                jan = ".$rak[0]['bulan_1'].",
			                                feb = ".$rak[0]['bulan_2'].",
			                                mar = ".$rak[0]['bulan_3'].",
			                                apr = ".$rak[0]['bulan_4'].",
			                                mei = ".$rak[0]['bulan_5'].",
			                                jun = ".$rak[0]['bulan_6'].",
			                                jul = ".$rak[0]['bulan_7'].",
			                                agt = ".$rak[0]['bulan_8'].",
			                                sep = ".$rak[0]['bulan_9'].",
			                                okt = ".$rak[0]['bulan_10'].",
			                                nop = ".$rak[0]['bulan_11'].",
			                                des = ".$rak[0]['bulan_12']."
			                            where 
				                            tahun=".$tahun_anggaran
				                            .' and kd_urusan='.$_kd_urusan
				                            .' and kd_bidang='.$_kd_bidang
				                            .' and kd_unit='.$kd_unit
				                            .' and kd_sub='.$kd_sub_unit
				                            .' and kd_prog='.$kd_prog
				                            .' and id_prog='.$id_prog
				                            .' and kd_keg='.$kd_keg
				                            .' and kd_rek_1='.$mapping_rek[0]->kd_rek_1
				                            .' and kd_rek_2='.$mapping_rek[0]->kd_rek_2
				                            .' and kd_rek_3='.$mapping_rek[0]->kd_rek_3
				                            .' and kd_rek_4='.$mapping_rek[0]->kd_rek_4
				                            .' and kd_rek_5='.$mapping_rek[0]->kd_rek_5
			                        );
			                    }else{
			                        $options = array(
			                            'query' => "
			                            INSERT INTO ta_rencana (
			                                tahun,
			                                kd_urusan,
			                                kd_bidang,
			                                kd_unit,
			                                kd_sub,
			                                kd_prog,
			                                id_prog,
			                                kd_keg,
			                                kd_rek_1,
				                            kd_rek_2,
				                            kd_rek_3,
				                            kd_rek_4,
				                            kd_rek_5,
				                            jan,
			                                feb,
			                                mar,
			                                apr,
			                                mei,
			                                jun,
			                                jul,
			                                agt,
			                                sep,
			                                okt,
			                                nop,
			                                des
			                            )
			                            VALUES (
			                                ".$tahun_anggaran.",
			                                ".$_kd_urusan.",
			                                ".$_kd_bidang.",
			                                ".$kd_unit.",
			                                ".$kd_sub_unit.",
			                                ".$kd_prog.",
			                                ".$id_prog.",
			                                ".$kd_keg.",
			                                ".$mapping_rek[0]->kd_rek_1.",
				                            ".$mapping_rek[0]->kd_rek_2.",
				                            ".$mapping_rek[0]->kd_rek_3.",
				                            ".$mapping_rek[0]->kd_rek_4.",
				                            ".$mapping_rek[0]->kd_rek_5.",
			                                ".str_replace(',', '.', $rak[0]['bulan_1']).",
			                                ".str_replace(',', '.', $rak[0]['bulan_2']).",
			                                ".str_replace(',', '.', $rak[0]['bulan_3']).",
			                                ".str_replace(',', '.', $rak[0]['bulan_4']).",
			                                ".str_replace(',', '.', $rak[0]['bulan_5']).",
			                                ".str_replace(',', '.', $rak[0]['bulan_6']).",
			                                ".str_replace(',', '.', $rak[0]['bulan_7']).",
			                                ".str_replace(',', '.', $rak[0]['bulan_8']).",
			                                ".str_replace(',', '.', $rak[0]['bulan_9']).",
			                                ".str_replace(',', '.', $rak[0]['bulan_10']).",
			                                ".str_replace(',', '.', $rak[0]['bulan_11']).",
			                                ".str_replace(',', '.', $rak[0]['bulan_12'])."
			                            )"
			                        );
								}
								// print_r($options); die($v['id_akun']);
								$this->CurlSimda($options);
			                }else{
			                	$ret['status'] = 'error';
								$ret['message'] = 'ref_kegiatan_mapping atau ref_rek_mapping tidak ditemukan!';
			                }
						}
					}
				} else {
					$ret['status'] = 'error';
					$ret['message'] = 'Format singkron simda salah!';
				}
			} else {
				$ret['status'] = 'error';
				$ret['message'] = 'APIKEY tidak sesuai!';
			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format Salah!';
		}
		if(!empty($opsi['return'])){
			die(json_encode($ret));
		}
	}

	function singkronSimda($opsi=array()){
		global $wpdb;
		$ret = array(
			'action'	=> $_POST['action'],
			'kode_sbl'	=> $_POST['kode_sbl'],
			'status'	=> 'success',
			'message'	=> 'Berhasil export SIMDA!'
		);
		if (!empty($_POST)) {
			if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option('_crb_api_key_extension' )) {
				$kodeunit = '';
				$opsi_return = false;
				if(!empty($opsi['return'])){
					$opsi_return = $opsi['return'];
				}
				if(!empty($_POST['kode_sbl']) && !empty($_POST['tahun_anggaran'])){
					$sbl = $wpdb->get_results($wpdb->prepare("
						SELECT 
							* 
						from data_sub_keg_bl 
						where kode_sbl=%s
							AND tahun_anggaran=%d
							AND active=1", $_POST['kode_sbl'], $_POST['tahun_anggaran'])
					, ARRAY_A);
					if(!empty($sbl)){
						foreach ($sbl as $k => $v) {
							$sql = "
								SELECT 
									* 
								from data_lokasi_sub_keg 
								where kode_sbl='".$v['kode_sbl']."'
									AND tahun_anggaran=".$v['tahun_anggaran']."
									AND active=1";
							$lokasi_sub_keg = $wpdb->get_results($sql, ARRAY_A);
							$lokasi_sub = array();
							foreach ($lokasi_sub_keg as $key => $lok) {
								if(!empty($lok['idkabkota'])){
									$lokasi_sub[] = $lok['daerahteks'];
								}
								if(!empty($lok['idcamat'])){
									$lokasi_sub[] = $lok['camatteks'];
								}
								if(!empty($lok['idlurah'])){
									$lokasi_sub[] = $lok['lurahteks'];
								}
							}

							if($ret['status'] != 'error'){

								$bulan = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
								$waktu_pelaksanaan = $bulan[$v['waktu_awal']-1].' s.d. '.$bulan[$v['waktu_akhir']-1];

								$kd_unit_simda = explode('.', get_option('_crb_unit_'.$v['id_sub_skpd']));
								$tahun_anggaran = $v['tahun_anggaran'];
								if(!empty($kd_unit_simda) && !empty($kd_unit_simda[3])){
									$kd = explode('.', $v['kode_sub_giat']);
									$kd_urusan90 = (int) $kd[0];
									$kd_bidang90 = (int) $kd[1];
									$kd_program90 = (int) $kd[2];
									$kd_kegiatan90 = ((int) $kd[3]).'.'.$kd[4];
									$kd_sub_kegiatan = (int) $kd[5];
									$nama_keg = explode(' ', $v['nama_sub_giat']);
				                    unset($nama_keg[0]);
				                    $nama_keg = implode(' ', $nama_keg);
									$mapping = $this->cekKegiatanMapping(array(
										'kd_urusan90' => $kd_urusan90,
										'kd_bidang90' => $kd_bidang90,
										'kd_program90' => $kd_program90,
										'kd_kegiatan90' => $kd_kegiatan90,
										'kd_sub_kegiatan' => $kd_sub_kegiatan,
										'nama_program' => $v['nama_giat'],
										'nama_kegiatan' => $nama_keg,
									));
									if(!empty($mapping)){
										$_kd_urusan = $kd_unit_simda[0];
										$_kd_bidang = $kd_unit_simda[1];
										$kd_unit = $kd_unit_simda[2];
										$kd_sub_unit = $kd_unit_simda[3];
										
										$kd_urusan = $mapping[0]->kd_urusan;
										$kd_bidang = $mapping[0]->kd_bidang;
										$kd_prog = $mapping[0]->kd_prog;
										$kd_keg = $mapping[0]->kd_keg;
										foreach ($this->custom_mapping as $c_map_k => $c_map_v) {
											if(
												$_POST['data_unit']['kodeunit'] == $c_map_v['sipd']['kode_skpd']
												&& $v['kode_sub_giat'] == $c_map_v['sipd']['kode_sub_keg']
											){
												$kd_unit_simda_map = explode('.', $c_map_v['simda']['kode_skpd']);
												$_kd_urusan = $kd_unit_simda_map[0];
												$_kd_bidang = $kd_unit_simda_map[1];
												$kd_unit = $kd_unit_simda_map[2];
												$kd_sub_unit = $kd_unit_simda_map[3];
												$kd_keg_simda = explode('.', $c_map_v['simda']['kode_sub_keg']);
												$kd_urusan = $kd_keg_simda[0];
												$kd_bidang = $kd_keg_simda[1];
												$kd_prog = $kd_keg_simda[2];
												$kd_keg = $kd_keg_simda[3];
											}
										}
					                    $id_prog = $kd_urusan.$this->CekNull($kd_bidang);
					                    $nama_prog = $v['nama_giat'];

					                    $nama_keg = explode(' ', $v['nama_sub_giat']);
					                    unset($nama_keg[0]);
					                    $nama_keg = implode(' ', $nama_keg);

										$program_simda = $this->CurlSimda(array(
											'query' => "
												SELECT 
													* 
												from ta_program
												where tahun=".$tahun_anggaran
						                            .' and kd_urusan='.$_kd_urusan
						                            .' and kd_bidang='.$_kd_bidang
						                            .' and kd_unit='.$kd_unit
						                            .' and kd_sub='.$kd_sub_unit
						                            .' and kd_prog='.$kd_prog
						                            .' and id_prog='.$id_prog
										));
										if(empty($program_simda)){
					                        $options = array(
												'query' => "
													INSERT INTO ta_program (
					                                    tahun,
					                                    kd_urusan,
					                                    kd_bidang,
					                                    kd_unit,
					                                    kd_sub,
					                                    kd_prog,
					                                    id_prog,
					                                    ket_program,
					                                    kd_urusan1,
					                                    kd_bidang1
					                                )
					                                VALUES (
					                                    ".$tahun_anggaran.",
					                                    ".$_kd_urusan.",
					                                    ".$_kd_bidang.",
					                                    ".$kd_unit.",
					                                    ".$kd_sub_unit.",
					                                    ".$kd_prog.",
					                                    ".$id_prog.",
					                                    '".str_replace("'", '`', substr($nama_prog, 0, 255))."',
					                                    ".$kd_urusan.",
					                                    ".$kd_bidang."
					                                )"
											);
											// print_r($options); die();
											$this->CurlSimda($options);
										}

										$sql = "
											SELECT 
												id_rinci_sub_bl
											from data_rka 
											where kode_sbl='".$v['kode_sbl']."'
												AND tahun_anggaran=".$v['tahun_anggaran']."
												AND active=1
												AND kode_akun!=''
											Order by kode_akun ASC, subs_bl_teks ASC, ket_bl_teks ASC, id_rinci_sub_bl ASC
											LIMIT 1";
										$id_rinci_sub_bl = $wpdb->get_var($sql);
										$id_sumber_dana_kegiatan = $this->get_id_sumber_dana_simda(array(
											'tahun_anggaran' => $v['tahun_anggaran'],
											'id_rinci_sub_bl' => $id_rinci_sub_bl,
											'return' => $opsi_return
										));

										$options = array(
					                        'query' => "
					                        select
					                            tahun 
					                        from 
					                            ta_kegiatan 
					                        where 
					                            tahun=".$tahun_anggaran."
					                            and kd_urusan=".$_kd_urusan."
					                            and kd_bidang=".$_kd_bidang."
					                            and kd_unit=".$kd_unit."
					                            and kd_sub=".$kd_sub_unit."
					                            and kd_prog=".$kd_prog."
					                            and id_prog=".$id_prog."
					                            and kd_keg=".$kd_keg
					                    );
					                    $cek_kegiatan = $this->CurlSimda($options);
					                    if(!empty($cek_kegiatan)){
					                        $options = array(
					                            'query' => "
					                            UPDATE ta_kegiatan set
					                                ket_kegiatan = '".str_replace("'", '`', substr($nama_keg, 0, 255))."',
					                                lokasi = '".str_replace("'", '`', substr(implode(', ', $lokasi_sub), 0, 800))."',
					                                status_kegiatan = 1,
					                                pagu_anggaran = ".str_replace(',', '.', $v['pagu']).",
					                                kd_sumber = ".$id_sumber_dana_kegiatan.",
					                                waktu_pelaksanaan = '".str_replace("'", '`', substr($waktu_pelaksanaan, 0, 100))."',
					                                kelompok_sasaran = '".str_replace("'", '`', substr($v['sasaran'], 0, 255))."'
					                            where 
						                            tahun=".$tahun_anggaran."
						                            and kd_urusan=".$_kd_urusan."
						                            and kd_bidang=".$_kd_bidang."
						                            and kd_unit=".$kd_unit."
						                            and kd_sub=".$kd_sub_unit."
						                            and kd_prog=".$kd_prog."
						                            and id_prog=".$id_prog."
						                            and kd_keg=".$kd_keg
					                        );
					                    }else{
					                        $options = array(
					                            'query' => "
					                            INSERT INTO ta_kegiatan (
					                                tahun,
					                                kd_urusan,
					                                kd_bidang,
					                                kd_unit,
					                                kd_sub,
					                                kd_prog,
					                                id_prog,
					                                kd_keg,
					                                ket_kegiatan,
					                                lokasi,
					                                kelompok_sasaran,
					                                status_kegiatan,
					                                pagu_anggaran,
					                                waktu_pelaksanaan,
					                                kd_sumber
					                            )
					                            VALUES (
					                                ".$tahun_anggaran.",
					                                ".$_kd_urusan.",
					                                ".$_kd_bidang.",
					                                ".$kd_unit.",
					                                ".$kd_sub_unit.",
					                                ".$kd_prog.",
					                                ".$id_prog.",
					                                ".$kd_keg.",
					                                '".str_replace("'", '`', substr($nama_keg, 0, 255))."',
					                                '".str_replace("'", '`', substr(implode(', ', $lokasi_sub), 0, 800))."',
					                                '".str_replace("'", '`', substr($v['sasaran'], 0, 255))."',
					                                1,
					                                ".str_replace(',', '.', $v['pagu']).",
					                                '".str_replace("'", '`', substr($waktu_pelaksanaan, 0, 100))."',
					                                ".$id_sumber_dana_kegiatan."
					                            )"
					                        );
					                    }
										// print_r($options); die($_POST['kode_sbl']);
										$this->CurlSimda($options, false, false);

										$options = array(
					                        'query' => "
					                        DELETE from ta_indikator
					                        where 
					                            tahun=".$tahun_anggaran."
					                            and kd_urusan=".$_kd_urusan."
					                            and kd_bidang=".$_kd_bidang."
					                            and kd_unit=".$kd_unit."
					                            and kd_sub=".$kd_sub_unit."
					                            and kd_prog=".$kd_prog."
					                            and id_prog=".$id_prog."
					                            and kd_keg=".$kd_keg
					                    );
					                    // print_r($options); die();
					                    $this->CurlSimda($options);

										$options = array(
					                        'query' => "
					                        DELETE from ta_belanja_rinc_sub
					                        where 
					                            tahun=".$tahun_anggaran."
					                            and kd_urusan=".$_kd_urusan."
					                            and kd_bidang=".$_kd_bidang."
					                            and kd_unit=".$kd_unit."
					                            and kd_sub=".$kd_sub_unit."
					                            and kd_prog=".$kd_prog."
					                            and id_prog=".$id_prog."
					                            and kd_keg=".$kd_keg
					                    );
					                    // print_r($options); die();
					                    $this->CurlSimda($options);

										$options = array(
					                        'query' => "
					                        DELETE from ta_belanja_rinc
					                        where 
					                            tahun=".$tahun_anggaran."
					                            and kd_urusan=".$_kd_urusan."
					                            and kd_bidang=".$_kd_bidang."
					                            and kd_unit=".$kd_unit."
					                            and kd_sub=".$kd_sub_unit."
					                            and kd_prog=".$kd_prog."
					                            and id_prog=".$id_prog."
					                            and kd_keg=".$kd_keg
					                    );
					                    // print_r($options); die();
					                    $this->CurlSimda($options);

										$options = array(
					                        'query' => "
					                        DELETE from ta_belanja
					                        where 
					                            tahun=".$tahun_anggaran."
					                            and kd_urusan=".$_kd_urusan."
					                            and kd_bidang=".$_kd_bidang."
					                            and kd_unit=".$kd_unit."
					                            and kd_sub=".$kd_sub_unit."
					                            and kd_prog=".$kd_prog."
					                            and id_prog=".$id_prog."
					                            and kd_keg=".$kd_keg
					                    );
					                    // print_r($options); die();
					                    $this->CurlSimda($options);

					                    $sql = "
											SELECT 
												* 
											from data_sub_keg_indikator 
											where kode_sbl='".$v['kode_sbl']."'
												AND tahun_anggaran=".$v['tahun_anggaran']."
												AND active=1";
										$ind_keg = $wpdb->get_results($sql, ARRAY_A);
										$no = 0;
										foreach ($ind_keg as $kk => $ind) {
											$no++;
											$options = array(
					                            'query' => "
					                            INSERT INTO ta_indikator (
					                                tahun,
					                                kd_urusan,
					                                kd_bidang,
					                                kd_unit,
					                                kd_sub,
					                                kd_prog,
					                                id_prog,
					                                kd_keg,
					                                kd_indikator,
					                                no_id,
					                                tolak_ukur,
					                                target_angka,
					                                target_uraian
					                            )
					                            VALUES (
									            	".$tahun_anggaran.",
					                                ".$_kd_urusan.",
					                                ".$_kd_bidang.",
					                                ".$kd_unit.",
					                                ".$kd_sub_unit.",
					                                ".$kd_prog.",
					                                ".$id_prog.",
					                                ".$kd_keg.",
					                                3,
					                                ".$no.",
					                                '".str_replace("'", '`', substr($ind['outputteks'], 0, 255))."',
					                                ".str_replace(',', '.', str_replace(',', '.', (!empty($ind['targetoutput'])? $ind['targetoutput']:0))).",
					                                '".str_replace("'", '`', $ind['satuanoutput'])."'
					                            )"
					                        );
					                        // print_r($options); die();
					                        $this->CurlSimda($options);
										}

					                    $sql = "
											SELECT 
												* 
											from data_keg_indikator_hasil 
											where kode_sbl='".$v['kode_sbl']."'
												AND tahun_anggaran=".$v['tahun_anggaran']."
												AND active=1";
										$ind_keg = $wpdb->get_results($sql, ARRAY_A);
										$no = 0;
										foreach ($ind_keg as $kk => $ind) {
											$no++;
											$options = array(
					                            'query' => "
					                            INSERT INTO ta_indikator (
					                                tahun,
					                                kd_urusan,
					                                kd_bidang,
					                                kd_unit,
					                                kd_sub,
					                                kd_prog,
					                                id_prog,
					                                kd_keg,
					                                kd_indikator,
					                                no_id,
					                                tolak_ukur,
					                                target_angka,
					                                target_uraian
					                            )
					                            VALUES (
									            	".$tahun_anggaran.",
					                                ".$_kd_urusan.",
					                                ".$_kd_bidang.",
					                                ".$kd_unit.",
					                                ".$kd_sub_unit.",
					                                ".$kd_prog.",
					                                ".$id_prog.",
					                                ".$kd_keg.",
					                                4,
					                                ".$no.",
					                                '".str_replace("'", '`', substr($ind['hasilteks'], 0, 255))."',
					                                ".str_replace(',', '.', (!empty($ind['targethasil'])? $ind['targethasil']:0)).",
					                                '".str_replace("'", '`', $ind['satuanhasil'])."'
					                            )"
					                        );
					                        // print_r($options); die();
					                        $this->CurlSimda($options);
										}

					                    $sql = "
											SELECT 
												* 
											from data_rka 
											where kode_sbl='".$v['kode_sbl']."'
												AND tahun_anggaran=".$v['tahun_anggaran']."
												AND active=1
												AND kode_akun!=''
											Order by kode_akun ASC, subs_bl_teks ASC, ket_bl_teks ASC, id_rinci_sub_bl ASC";
										$rka = $wpdb->get_results($sql, ARRAY_A);
										$akun_all = array();
										$rinc_all = array();
										foreach ($rka as $kk => $rk) {
											$aktivitas = $rk['subs_bl_teks'].' | '.$rk['ket_bl_teks'];
											if(empty($akun_all[$rk['kode_akun']])){
												$id_sumber_dana_akun = $this->get_id_sumber_dana_simda(array(
													'tahun_anggaran' => $v['tahun_anggaran'],
													'id_rinci_sub_bl' => $rk['id_rinci_sub_bl'],
													'return' => $opsi_return
												));
												$akun_all[$rk['kode_akun']] = array(
													'id_sumber_dana' => $id_sumber_dana_akun,
													'data' => array()
												);	
											}
											if(empty($akun_all[$rk['kode_akun']]['data'][$aktivitas])){
												$id_sumber_dana_aktivitas = $this->get_id_sumber_dana_simda(array(
													'tahun_anggaran' => $v['tahun_anggaran'],
													'id_rinci_sub_bl' => $rk['id_rinci_sub_bl'],
													'return' => $opsi_return
												));
												$akun_all[$rk['kode_akun']]['data'][$aktivitas] = array(
													'id_sumber_dana' => $id_sumber_dana_aktivitas,
													'data' => array()
												);	
											}
											$akun_all[$rk['kode_akun']]['data'][$aktivitas]['data'][] = $rk;
										}
										
										foreach ($akun_all as $kk => $rk) {
											$akun = explode('.', $kk);

							                $mapping_rek = $this->cekRekMapping(array(
												'tahun_anggaran' => $tahun_anggaran,
												'kode_akun' => $kk,
												'kd_rek_0' => $akun[0],
												'kd_rek_1' => $akun[1],
												'kd_rek_2' => $akun[2],
												'kd_rek_3' => $akun[3],
												'kd_rek_4' => $akun[4],
												'kd_rek_5' => $akun[5],
							                ));
											if(!empty($mapping_rek)){
									            $options = array(
									                'query' => "
											            INSERT INTO ta_belanja (
											                tahun,
											                kd_urusan,
											                kd_bidang,
											                kd_unit,
											                kd_sub,
											                kd_prog,
											                id_prog,
											                kd_keg,
											                kd_rek_1,
											                kd_rek_2,
											                kd_rek_3,
											                kd_rek_4,
											                kd_rek_5,
											                kd_sumber
											            ) VALUES (
											            	".$tahun_anggaran.",
							                                ".$_kd_urusan.",
							                                ".$_kd_bidang.",
							                                ".$kd_unit.",
							                                ".$kd_sub_unit.",
							                                ".$kd_prog.",
							                                ".$id_prog.",
							                                ".$kd_keg.",
											                ".$mapping_rek[0]->kd_rek_1.",
											                ".$mapping_rek[0]->kd_rek_2.",
											                ".$mapping_rek[0]->kd_rek_3.",
											                ".$mapping_rek[0]->kd_rek_4.",
											                ".$mapping_rek[0]->kd_rek_5.",
											                ".$rk['id_sumber_dana']."
											            )"
									            );
							                    // print_r($options); die();
							                    $this->CurlSimda($options);
												
						                		$no_rinc = 0;
												foreach ($rk['data'] as $kkk => $rkk) {
													$no_rinc++;
													$options = array(
										                'query' => "
												            INSERT INTO ta_belanja_rinc (
												                tahun,
												                kd_urusan,
												                kd_bidang,
												                kd_unit,
												                kd_sub,
												                kd_prog,
												                id_prog,
												                kd_keg,
												                kd_rek_1,
												                kd_rek_2,
												                kd_rek_3,
												                kd_rek_4,
												                kd_rek_5,
												                no_rinc,
												                keterangan,
												                kd_sumber
												            ) VALUES (
												            	".$tahun_anggaran.",
								                                ".$_kd_urusan.",
								                                ".$_kd_bidang.",
								                                ".$kd_unit.",
								                                ".$kd_sub_unit.",
								                                ".$kd_prog.",
								                                ".$id_prog.",
								                                ".$kd_keg.",
												                ".$mapping_rek[0]->kd_rek_1.",
												                ".$mapping_rek[0]->kd_rek_2.",
												                ".$mapping_rek[0]->kd_rek_3.",
												                ".$mapping_rek[0]->kd_rek_4.",
												                ".$mapping_rek[0]->kd_rek_5.",
												                ".$no_rinc.",
												                '".str_replace("'", '`', substr($kkk, 0, 255))."',
												                ".$rkk['id_sumber_dana']."
												            )"
										            );
								                    // print_r($options); die();
								                    $this->CurlSimda($options);

							                		$no_rinc_sub = 0;
													foreach ($rkk['data'] as $kkkk => $rkkk) {
														$no_rinc_sub++;
														$komponen = array($rkkk['nama_komponen'], $rkkk['spek_komponen']);
														$nilai1 = 0;
														$nilai1_t = 1;
														if(!empty($rkkk['volum1'])){
															$nilai1 = $rkkk['volum1'];
															$nilai1_t = $rkkk['volum1'];
														}else{
															$jml_satuan_db = explode(' ', $rkkk['koefisien']);
															if(!empty($jml_satuan_db) && $jml_satuan_db[0] >= 1){
																$nilai1 = $jml_satuan_db[0];
															}
														}
														$sat1 = $rkkk['satuan'];
														if(!empty($rkkk['sat1'])){
															$sat1 = $rkkk['sat1'];
														}
														$nilai2 = 0;
														$nilai2_t = 1;
														if(!empty($rkkk['volum2'])){
															$nilai2 = $rkkk['volum2'];
															$nilai2_t = $rkkk['volum2'];
														}
														$nilai3 = 0;
														$nilai3_t = 1;
														if(!empty($rkkk['volum3'])){
															$nilai3 = $rkkk['volum3'];
															$nilai3_t = $rkkk['volum3'];
														}
														$nilai4_t = 1;
														if(!empty($rkkk['volum4'])){
															$nilai4_t = $rkkk['volum4'];
														}
														$jml_satuan = $nilai1_t*$nilai2_t*$nilai3_t*$nilai4_t;

														$harga_satuan = $rkkk['harga_satuan'];
														$total_rinci = $rkkk['total_harga'];
														if($this->opsi_nilai_rincian == 2){
															$harga_satuan = $rkkk['harga_satuan_murni'];
															$total_rinci = $rkkk['rincian_murni'];
															$nilai1 = $rkkk['volume_murni'];
															$nilai2 = 0;
															$nilai3 = 0;
															$jml_satuan = $rkkk['volume_murni'];
														}
														if(empty($nilai1)){
															$nilai1 = 0;
														}
														if(empty($nilai2)){
															$nilai2 = 0;
														}
														if(empty($nilai3)){
															$nilai3 = 0;
														}
														if(empty($jml_satuan)){
															$jml_satuan = 0;
														}
														if(empty($harga_satuan)){
															$harga_satuan = 0;
														}
														$options = array(
											                'query' => "
													            INSERT INTO ta_belanja_rinc_sub (
													                tahun,
													                kd_urusan,
													                kd_bidang,
													                kd_unit,
													                kd_sub,
													                kd_prog,
													                id_prog,
													                kd_keg,
													                kd_rek_1,
													                kd_rek_2,
													                kd_rek_3,
													                kd_rek_4,
													                kd_rek_5,
													                no_rinc,
													                no_id,
													                sat_1,
											                        nilai_1,
											                        sat_2,
											                        nilai_2,
											                        sat_3,
											                        nilai_3,
											                        satuan123,
											                        jml_satuan,
											                        nilai_rp,
											                        total,
											                        keterangan
													            ) VALUES (
													            	".$tahun_anggaran.",
									                                ".$_kd_urusan.",
									                                ".$_kd_bidang.",
									                                ".$kd_unit.",
									                                ".$kd_sub_unit.",
									                                ".$kd_prog.",
									                                ".$id_prog.",
									                                ".$kd_keg.",
													                ".$mapping_rek[0]->kd_rek_1.",
													                ".$mapping_rek[0]->kd_rek_2.",
													                ".$mapping_rek[0]->kd_rek_3.",
													                ".$mapping_rek[0]->kd_rek_4.",
													                ".$mapping_rek[0]->kd_rek_5.",
													                ".$no_rinc.",
													                ".$no_rinc_sub.",
													                '".str_replace("'", '`', substr($sat1, 0, 10))."',
													                ". str_replace(',', '.', $nilai1) .",
													                '".str_replace("'", '`', substr($rkkk['sat2'], 0, 10))."',
													                ". str_replace(',', '.', $nilai2) .",
													                '".str_replace("'", '`', substr($rkkk['sat3'], 0, 10))."',
													                ". str_replace(',', '.', $nilai3) .",
													                '".str_replace("'", '`', substr($rkkk['satuan'], 0, 50))."',
													                ". str_replace(',', '.', $jml_satuan) .",
													                ". str_replace(',', '.', $harga_satuan) .",
													                ". str_replace(',', '.', $total_rinci) .",
													                '".str_replace("'", '`', substr(implode(' | ', $komponen), 0, 255))."'
													            )"
											            );
									                    // print_r($options); die();
									                    $this->CurlSimda($options);
													}
												}

											}else{
												$ret['status'] = 'error';
												$ret['simda_status'] = 'error';
												$ret['simda_msg'] = 'Kode akun '.$kk.' tidak ditemukan di ref_rek_mapping SIMDA';
											}
						                }
									}else{
										$ret['status'] = 'error';
										$ret['simda_status'] = 'error';
										$ret['simda_msg'] = 'Kode kegiatan '.$v['kode_sub_giat'].' tidak ditemukan di ref_kegiatan_mapping SIMDA';
									}
								}else{
									$ret['status'] = 'error';
									$ret['simda_status'] = 'error';
									$ret['simda_msg'] = 'Kode Unit belum dimapping di wp-sipd untuk OPD '.$v['nama_skpd'];
								}
							}
						}
					}else{
						$ret['status'] = 'error';
						$ret['message'] = 'kode_sbl '.$_POST['kode_sbl'].' di tahun anggaran '.$_POST['tahun_anggaran'].' tidak ditemukan!';
					}
				} else {
					$ret['status'] = 'error';
					$ret['message'] = 'Format singkron simda salah!';
				}
			} else {
				$ret['status'] = 'error';
				$ret['message'] = 'APIKEY tidak sesuai!';
			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format Salah!';
		}
		if(!empty($opsi['return'])){
			die(json_encode($ret));
		}else{
			return $ret;
		}
	}

	public function get_id_sumber_dana_simda($options=array()){
		global $wpdb;
		$ret = array();
		$tahun_anggaran = $options['tahun_anggaran'];
		$id_rinci_sub_bl = $options['id_rinci_sub_bl'];
		$id_sumber_dana = $wpdb->get_var($wpdb->prepare('
			select 
				id_sumber_dana 
			from data_mapping_sumberdana 
			where tahun_anggaran=%d
				and id_rinci_sub_bl=%d
				and active=1', 
			$tahun_anggaran, $id_rinci_sub_bl
		));

		if(empty($id_sumber_dana)){
			$id_sumber_dana = get_option('_crb_default_sumber_dana' );
		}

		$sql = "
			SELECT 
				nama_dana
			from data_sumber_dana 
			where id_dana=".$id_sumber_dana."
				AND tahun_anggaran=".$tahun_anggaran."";
		$namadana = $wpdb->get_var($sql);
		$new_sd = explode(' - ', $namadana);
		$nama_sd = trim($new_sd[1]);
		$nama_sd = substr($nama_sd, 0, 100);
		$cek_sd = $this->CurlSimda(array(
			'query' => "select * from ref_sumber_dana where kd_sumber=".$id_sumber_dana.""
		));
		if(empty($cek_sd)){
			/* 
			- Cek jika iddana lebih dari 255 karena type kd_sumber adalah tinyint, idmaksimal 255.
			- Type kolom tabel ref_sumber_dana tidak bisa dirubah karena berelasi ke beberapa tabel lainnya.
			- Admin simda perlu menginput sumber dana secara manual di tabel ref_sumber_dana dengan nama sumber dana yang sesuai di SIPD.
			*/
			if($id_sumber_dana > 255){
				$cek_sd = $this->CurlSimda(array(
					'query' => "select * from ref_sumber_dana where nm_sumber='".$nama_sd."'"
				));
				if(!empty($cek_sd)){
					$id_sumber_dana = $cek_sd[0]->kd_sumber;
				}else{
					$ret['status'] = 'error';
					$ret['simda_status'] = 'error';
					$ret['simda_msg'] = 'Sumber dana dengan kd_sumber='.$id_sumber_dana.' dan nm_sumber="'.$nama_sd.'" tidak dapat disimpan di tabel ref_sumber_dana. Harap tambahkan secara manual! nm_sumber harus sama dan untuk kd_sumber tidak harus sama. Sesuaikan dengan kondisi.';
				}
			}else{
				$options = array('query' => "
					INSERT INTO ref_sumber_dana (
                        kd_sumber,
                        nm_sumber
                    )
                    VALUES (
						".$id_sumber_dana.",
						'".$nama_sd."'
					)"
				);
				$this->CurlSimda($options);
			}
		}else{
			if($cek_sd[0]->nm_sumber != $nama_sd){
				$options = array('query' => "
					UPDATE ref_sumber_dana 
					set nm_sumber='".$nama_sd."'
					where kd_sumber=".$id_sumber_dana.""
				);
				$this->CurlSimda($options);
			}
		}

		if(
			!empty($options['return'])
			&& $ret['status'] == 'error'
		){
			die(json_encode($ret));
		}else{
			return $id_sumber_dana;
		}
	}

	public function CurlSimda($options, $debug=false, $debug_req=false){
		if(
			false == $this->status_koneksi_simda
			|| (
				!empty($_GET) 
				&& !empty($_GET['no_simda'])
			)
		){
			return;
		}
        $query = $options['query'];
        $curl = curl_init();
        $req = array(
            'api_key' => get_option( '_crb_apikey_simda' ),
            'query' => $query,
            'db' => get_option('_crb_db_simda')
        );
        set_time_limit(0);
        $url = get_option( '_crb_url_api_simda' );
    	if($debug_req){
        	print_r($req); die($url);
    	}
        $req = http_build_query($req);
        $timeout = (int) get_option('_crb_timeout_simda');
        if(empty($timeout)){
        	$timeout = 10;
        }
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $req,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_NOSIGNAL => 1,
            CURLOPT_CONNECTTIMEOUT => 100,
            CURLOPT_TIMEOUT => $timeout
        ));

        $response = curl_exec($curl);
        // die($response);
        $err = curl_error($curl);

        curl_close($curl);

        $debug_option = get_option('_crb_singkron_simda_debug');
        if ($err) {
        	$this->status_koneksi_simda = false;
        	$msg = "cURL Error #:".$err." (".$url.")";
        	if($debug_option == 1){
            	die($msg);
        	}else{
        		return $msg;
        	}
        } else {
        	if($debug){
            	print_r($response); die();
        	}
            $ret = json_decode($response);
            if(!empty($ret->error)){
            	if(empty($options['no_debug']) && $debug_option==1){
                	echo "<pre>".print_r($ret, 1)."</pre>"; die();
                }
            }else{
            	if(isset($ret->msg)){
                	return $ret->msg;
            	}else{
        			$this->status_koneksi_simda = false;
            		$msg = $response.' (terkoneksi tapi gagal parsing data!)';
        			if($debug_option == 1){
            			// die($msg);
            		}else{
            			// return $msg;
            		}
            	}
            }
        }
    }

    function CekNull($number, $length=2){
        $l = strlen($number);
        $ret = '';
        for($i=0; $i<$length; $i++){
            if($i+1 > $l){
                $ret .= '0';
            }
        }
        $ret .= $number;
        return $ret;
    }

    function cekRekMapping($options){
    	global $wpdb;
    	$mapping_rek = $this->CurlSimda(array(
			'query' => "
				SELECT 
					* 
				from ref_rek_mapping
				where kd_rek90_1=".((int)$options['kd_rek_0'])
                    .' and kd_rek90_2='.((int)$options['kd_rek_1'])
                    .' and kd_rek90_3='.((int)$options['kd_rek_2'])
                    .' and kd_rek90_4='.((int)$options['kd_rek_3'])
                    .' and kd_rek90_5='.((int)$options['kd_rek_4'])
                    .' and kd_rek90_6='.((int)$options['kd_rek_5'])
		));
		$rek90_1 = $options['kd_rek_0'];
		$rek90_2 = $rek90_1.'.'.$options['kd_rek_1'];
		$rek90_3 = $rek90_2.'.'.$options['kd_rek_2'];
		$rek90_4 = $rek90_3.'.'.$options['kd_rek_3'];
		$rek90_5 = $rek90_4.'.'.$options['kd_rek_4'];
		$rek90_6 = $rek90_5.'.'.$options['kd_rek_5'];

		$no_tinny = 100;
		$kd_rek_3 = ((int)$options['kd_rek_2'])+$no_tinny;
		$kd_rek_4 = ((int)$options['kd_rek_4'])+$no_tinny;
		$kd_rek_5 = ((int)$options['kd_rek_5'])+$no_tinny;

		if(!empty($options['delete'])){
			$this->CurlSimda(array(
				'query' => "DELETE from ref_rek_mapping
					where kd_rek90_1=".((int)$options['kd_rek_0'])."
						AND kd_rek90_2=".((int)$options['kd_rek_1'])."
						AND kd_rek90_3=".((int)$options['kd_rek_2'])."
						AND kd_rek90_4=".((int)$options['kd_rek_3'])."
						AND kd_rek90_5=".((int)$options['kd_rek_4'])."
						AND kd_rek90_6=".((int)$options['kd_rek_5'])
			));
			$this->CurlSimda(array(
				'query' => "DELETE from ref_rek_5 
					where kd_rek_1=".((int)$options['kd_rek_0'])."
						AND kd_rek_2=".((int)$options['kd_rek_1'])."
						AND kd_rek_3=".$kd_rek_3."
						AND kd_rek_4=".$kd_rek_4."
						AND kd_rek_5=".$kd_rek_5
			));
			$this->CurlSimda(array(
				'query' => "DELETE from ref_rek_4 
					where kd_rek_1=".((int)$options['kd_rek_0'])."
						AND kd_rek_2=".((int)$options['kd_rek_1'])."
						AND kd_rek_3=".$kd_rek_3."
						AND kd_rek_4=".$kd_rek_4
			));
			$this->CurlSimda(array(
				'query' => "DELETE from ref_rek_3 
					where kd_rek_1=".((int)$options['kd_rek_0'])."
						AND kd_rek_2=".((int)$options['kd_rek_1'])."
						AND kd_rek_3=".$kd_rek_3
			));
			$this->CurlSimda(array(
				'query' => "DELETE from ref_rek_2 
					where kd_rek_1=".((int)$options['kd_rek_0'])."
						AND kd_rek_2=".((int)$options['kd_rek_1'])
			));
			$this->CurlSimda(array(
				'query' => "DELETE from ref_rek_1 where kd_rek_1=".((int)$options['kd_rek_0']).""
			));
			$this->CurlSimda(array(
				'query' => "DELETE from ref_rek90_6 
					where kd_rek90_1=".((int)$options['kd_rek_0'])."
						AND kd_rek90_2=".((int)$options['kd_rek_1'])."
						AND kd_rek90_3=".((int)$options['kd_rek_2'])."
						AND kd_rek90_4=".((int)$options['kd_rek_3'])."
						AND kd_rek90_5=".((int)$options['kd_rek_4'])."
						AND kd_rek90_6=".((int)$options['kd_rek_5'])
			));
			$this->CurlSimda(array(
				'query' => "DELETE from ref_rek90_5 
					where kd_rek90_1=".((int)$options['kd_rek_0'])."
						AND kd_rek90_2=".((int)$options['kd_rek_1'])."
						AND kd_rek90_3=".((int)$options['kd_rek_2'])."
						AND kd_rek90_4=".((int)$options['kd_rek_3'])."
						AND kd_rek90_5=".((int)$options['kd_rek_4'])
			));
			$this->CurlSimda(array(
				'query' => "DELETE from ref_rek90_4 
					where kd_rek90_1=".((int)$options['kd_rek_0'])."
						AND kd_rek90_2=".((int)$options['kd_rek_1'])."
						AND kd_rek90_3=".((int)$options['kd_rek_2'])."
						AND kd_rek90_4=".((int)$options['kd_rek_3'])
			));
			$this->CurlSimda(array(
				'query' => "DELETE from ref_rek90_3 
					where kd_rek90_1=".((int)$options['kd_rek_0'])."
						AND kd_rek90_2=".((int)$options['kd_rek_1'])."
						AND kd_rek90_3=".((int)$options['kd_rek_2'])
			));
			$this->CurlSimda(array(
				'query' => "DELETE from ref_rek90_2 
					where kd_rek90_1=".((int)$options['kd_rek_0'])."
						AND kd_rek90_2=".((int)$options['kd_rek_1'])
			));
			$this->CurlSimda(array(
				'query' => "DELETE from ref_rek90_1 
					where kd_rek90_1=".((int)$options['kd_rek_0'])
			));
		}

		if(
			empty($mapping_rek)
			&& get_option('_crb_auto_ref_rek_mapping') == 1
		){

			$cek_rek_1 = $this->CurlSimda(array(
				'query' => "
					SELECT 
						* 
					from ref_rek_1
					where kd_rek_1=".((int)$options['kd_rek_0'])
			));
			if(empty($cek_rek_1)){
				$rek_name = $wpdb->get_results($wpdb->prepare("
					SELECT 
						nama_akun
					from data_akun
					where tahun_anggaran=%d
						AND kode_akun=%s", $options['tahun_anggaran'], $rek90_1)
				, ARRAY_A);
				$this->CurlSimda(array(
					'query' => "
						INSERT INTO ref_rek_1 (
							kd_rek_1,
							nm_rek_1
						) VALUES (
							".((int)$options['kd_rek_0']).",
							'".str_replace("'", '`', substr($rek_name[0]['nama_akun'], 0, 100))."'
						)"
				));
			}

			$cek_rek_2 = $this->CurlSimda(array(
				'query' => "
					SELECT 
						* 
					from ref_rek_2
					where kd_rek_1=".((int)$options['kd_rek_0'])."
						and kd_rek_2=".((int)$options['kd_rek_1'])
			));
			if(empty($cek_rek_2)){
				$rek_name = $wpdb->get_results($wpdb->prepare("
					SELECT 
						nama_akun
					from data_akun
					where tahun_anggaran=%d
						AND kode_akun=%s", $options['tahun_anggaran'], $rek90_2)
				, ARRAY_A);
				$this->CurlSimda(array(
					'query' => "
						INSERT INTO ref_rek_2 (
							kd_rek_1,
							kd_rek_2,
							nm_rek_2
						) VALUES (
							".((int)$options['kd_rek_0']).",
							".((int)$options['kd_rek_1']).",
							'".str_replace("'", '`', substr($rek_name[0]['nama_akun'], 0, 100))."'
						)"
				));
			}

			$cek_rek_3 = $this->CurlSimda(array(
				'query' => "
					SELECT 
						* 
					from ref_rek_3
					where kd_rek_1=".((int)$options['kd_rek_0'])."
						and kd_rek_2=".((int)$options['kd_rek_1'])."
						and kd_rek_3=".$kd_rek_3
			));
			if(empty($cek_rek_3)){
				$rek_name = $wpdb->get_results($wpdb->prepare("
					SELECT 
						nama_akun
					from data_akun
					where tahun_anggaran=%d
						AND kode_akun=%s", $options['tahun_anggaran'], $rek90_4)
				, ARRAY_A);
				$this->CurlSimda(array(
					'query' => "
						INSERT INTO ref_rek_3 (
							kd_rek_1,
							kd_rek_2,
							kd_rek_3,
							nm_rek_3
						) VALUES (
							".((int)$options['kd_rek_0']).",
							".((int)$options['kd_rek_1']).",
							".$kd_rek_3.",
							'".str_replace("'", '`', substr($rek_name[0]['nama_akun'], 0, 100))."'
						)"
				));
			}

			$cek_rek_4 = $this->CurlSimda(array(
				'query' => "
					SELECT 
						* 
					from ref_rek_4
					where kd_rek_1=".((int)$options['kd_rek_0'])."
						and kd_rek_2=".((int)$options['kd_rek_1'])."
						and kd_rek_3=".$kd_rek_3."
						and kd_rek_4=".$kd_rek_4
			));
			if(empty($cek_rek_4)){
				$rek_name = $wpdb->get_results($wpdb->prepare("
					SELECT 
						nama_akun
					from data_akun
					where tahun_anggaran=%d
						AND kode_akun=%s", $options['tahun_anggaran'], $rek90_5)
				, ARRAY_A);
				$this->CurlSimda(array(
					'query' => "
						INSERT INTO ref_rek_4 (
							kd_rek_1,
							kd_rek_2,
							kd_rek_3,
							kd_rek_4,
							nm_rek_4
						) VALUES (
							".((int)$options['kd_rek_0']).",
							".((int)$options['kd_rek_1']).",
							".$kd_rek_3.",
							".$kd_rek_4.",
							'".str_replace("'", '`', substr($rek_name[0]['nama_akun'], 0, 255))."'
						)"
				));
			}

			$cek_rek_5 = $this->CurlSimda(array(
				'query' => "
					SELECT 
						* 
					from ref_rek_5
					where kd_rek_1=".((int)$options['kd_rek_0'])."
						and kd_rek_2=".((int)$options['kd_rek_1'])."
						and kd_rek_3=".$kd_rek_3."
						and kd_rek_4=".$kd_rek_4."
						and kd_rek_5=".$kd_rek_5
			));
			if(empty($cek_rek_5)){
				$rek_name = $wpdb->get_results($wpdb->prepare("
					SELECT 
						nama_akun
					from data_akun
					where tahun_anggaran=%d
						AND kode_akun=%s", $options['tahun_anggaran'], $rek90_6)
				, ARRAY_A);
				$this->CurlSimda(array(
					'query' => "
						INSERT INTO ref_rek_5 (
							kd_rek_1,
							kd_rek_2,
							kd_rek_3,
							kd_rek_4,
							kd_rek_5,
							nm_rek_5
						) VALUES (
							".((int)$options['kd_rek_0']).",
							".((int)$options['kd_rek_1']).",
							".$kd_rek_3.",
							".$kd_rek_4.",
							".$kd_rek_5.",
							'".str_replace("'", '`', substr($rek_name[0]['nama_akun'], 0, 255))."'
						)"
				));
			}

			$cek_rek90_1 = $this->CurlSimda(array(
				'query' => "
					SELECT 
						* 
					from ref_rek90_1
					where kd_rek90_1=".((int)$options['kd_rek_0'])
			));
			if(empty($cek_rek90_1)){
				$rek_name = $wpdb->get_results($wpdb->prepare("
					SELECT 
						nama_akun
					from data_akun
					where tahun_anggaran=%d
						AND kode_akun=%s", $options['tahun_anggaran'], $rek90_1)
				, ARRAY_A);
				$this->CurlSimda(array(
					'query' => "
						INSERT INTO ref_rek90_1 (
							kd_rek90_1,
							nm_rek90_1
						) VALUES (
							".((int)$options['kd_rek_0']).",
							'".str_replace("'", '`', substr($rek_name[0]['nama_akun'], 0, 255))."'
						)"
				));
			}

			$cek_rek90_2 = $this->CurlSimda(array(
				'query' => "
					SELECT 
						* 
					from ref_rek90_2
					where kd_rek90_1=".((int)$options['kd_rek_0'])."
						and kd_rek90_2=".((int)$options['kd_rek_1'])
			));
			if(empty($cek_rek90_2)){
				$rek_name = $wpdb->get_results($wpdb->prepare("
					SELECT 
						nama_akun
					from data_akun
					where tahun_anggaran=%d
						AND kode_akun=%s", $options['tahun_anggaran'], $rek90_2)
				, ARRAY_A);
				$this->CurlSimda(array(
					'query' => "
						INSERT INTO ref_rek90_2 (
							kd_rek90_1,
							kd_rek90_2,
							nm_rek90_2
						) VALUES (
							".((int)$options['kd_rek_0']).",
							".((int)$options['kd_rek_1']).",
							'".str_replace("'", '`', substr($rek_name[0]['nama_akun'], 0, 100))."'
						)"
				));
			}

			$cek_rek90_3 = $this->CurlSimda(array(
				'query' => "
					SELECT 
						* 
					from ref_rek90_3
					where kd_rek90_1=".((int)$options['kd_rek_0'])."
						and kd_rek90_2=".((int)$options['kd_rek_1'])."
						and kd_rek90_3=".((int)$options['kd_rek_2'])
			));
			if(empty($cek_rek90_3)){
				$rek_name = $wpdb->get_results($wpdb->prepare("
					SELECT 
						nama_akun
					from data_akun
					where tahun_anggaran=%d
						AND kode_akun=%s", $options['tahun_anggaran'], $rek90_3)
				, ARRAY_A);
				$this->CurlSimda(array(
					'query' => "
						INSERT INTO ref_rek90_3 (
							kd_rek90_1,
							kd_rek90_2,
							kd_rek90_3,
							nm_rek90_3
						) VALUES (
							".((int)$options['kd_rek_0']).",
							".((int)$options['kd_rek_1']).",
							".((int)$options['kd_rek_2']).",
							'".str_replace("'", '`', substr($rek_name[0]['nama_akun'], 0, 100))."'
						)"
				));
			}

			$cek_rek90_4 = $this->CurlSimda(array(
				'query' => "
					SELECT 
						* 
					from ref_rek90_4
					where kd_rek90_1=".((int)$options['kd_rek_0'])."
						and kd_rek90_2=".((int)$options['kd_rek_1'])."
						and kd_rek90_3=".((int)$options['kd_rek_2'])."
						and kd_rek90_4=".((int)$options['kd_rek_3'])
			));
			if(empty($cek_rek90_4)){
				$rek_name = $wpdb->get_results($wpdb->prepare("
					SELECT 
						nama_akun
					from data_akun
					where tahun_anggaran=%d
						AND kode_akun=%s", $options['tahun_anggaran'], $rek90_4)
				, ARRAY_A);
				$this->CurlSimda(array(
					'query' => "
						INSERT INTO ref_rek90_4 (
							kd_rek90_1,
							kd_rek90_2,
							kd_rek90_3,
							kd_rek90_4,
							nm_rek90_4
						) VALUES (
							".((int)$options['kd_rek_0']).",
							".((int)$options['kd_rek_1']).",
							".((int)$options['kd_rek_2']).",
							".((int)$options['kd_rek_3']).",
							'".str_replace("'", '`', substr($rek_name[0]['nama_akun'], 0, 255))."'
						)"
				));
			}

			$cek_rek90_5 = $this->CurlSimda(array(
				'query' => "
					SELECT 
						* 
					from ref_rek90_5
					where kd_rek90_1=".((int)$options['kd_rek_0'])."
						and kd_rek90_2=".((int)$options['kd_rek_1'])."
						and kd_rek90_3=".((int)$options['kd_rek_2'])."
						and kd_rek90_4=".((int)$options['kd_rek_3'])."
						and kd_rek90_5=".((int)$options['kd_rek_4'])
			));
			if(empty($cek_rek90_5)){
				$rek_name = $wpdb->get_results($wpdb->prepare("
					SELECT 
						nama_akun
					from data_akun
					where tahun_anggaran=%d
						AND kode_akun=%s", $options['tahun_anggaran'], $rek90_5)
				, ARRAY_A);
				$this->CurlSimda(array(
					'query' => "
						INSERT INTO ref_rek90_5 (
							kd_rek90_1,
							kd_rek90_2,
							kd_rek90_3,
							kd_rek90_4,
							kd_rek90_5,
							nm_rek90_5
						) VALUES (
							".((int)$options['kd_rek_0']).",
							".((int)$options['kd_rek_1']).",
							".((int)$options['kd_rek_2']).",
							".((int)$options['kd_rek_3']).",
							".((int)$options['kd_rek_4']).",
							'".str_replace("'", '`', substr($rek_name[0]['nama_akun'], 0, 255))."'
						)"
				));
			}

			$cek_rek90_6 = $this->CurlSimda(array(
				'query' => "
					SELECT 
						* 
					from ref_rek90_6
					where kd_rek90_1=".((int)$options['kd_rek_0'])."
						and kd_rek90_2=".((int)$options['kd_rek_1'])."
						and kd_rek90_3=".((int)$options['kd_rek_2'])."
						and kd_rek90_4=".((int)$options['kd_rek_3'])."
						and kd_rek90_5=".((int)$options['kd_rek_4'])."
						and kd_rek90_6=".((int)$options['kd_rek_5'])
			));
			if(empty($cek_rek90_6)){
				$rek_name = $wpdb->get_results($wpdb->prepare("
					SELECT 
						nama_akun
					from data_akun
					where tahun_anggaran=%d
						AND kode_akun=%s", $options['tahun_anggaran'], $rek90_6)
				, ARRAY_A);
				$this->CurlSimda(array(
					'query' => "
						INSERT INTO ref_rek90_6 (
							kd_rek90_1,
							kd_rek90_2,
							kd_rek90_3,
							kd_rek90_4,
							kd_rek90_5,
							kd_rek90_6,
							nm_rek90_6
						) VALUES (
							".((int)$options['kd_rek_0']).",
							".((int)$options['kd_rek_1']).",
							".((int)$options['kd_rek_2']).",
							".((int)$options['kd_rek_3']).",
							".((int)$options['kd_rek_4']).",
							".((int)$options['kd_rek_5']).",
							'".str_replace("'", '`', substr($rek_name[0]['nama_akun'], 0, 255))."'
						)"
				));
			}

			$this->CurlSimda(array(
				'query' => "
					INSERT INTO ref_rek_mapping (
						kd_rek_1,
						kd_rek_2,
						kd_rek_3,
						kd_rek_4,
						kd_rek_5,
						kd_rek90_1,
						kd_rek90_2,
						kd_rek90_3,
						kd_rek90_4,
						kd_rek90_5,
						kd_rek90_6
					) VALUES (
						".((int)$options['kd_rek_0']).",
						".((int)$options['kd_rek_1']).",
						".$kd_rek_3.",
						".$kd_rek_4.",
						".$kd_rek_5.",
						".((int)$options['kd_rek_0']).",
						".((int)$options['kd_rek_1']).",
						".((int)$options['kd_rek_2']).",
						".((int)$options['kd_rek_3']).",
						".((int)$options['kd_rek_4']).",
						".((int)$options['kd_rek_5'])."
					)"
			));
			return $this->cekRekMapping($options);
		}else{
			return $mapping_rek;
		}
    }

    function cekKegiatanMapping($options){
    	// print_r($options); die();
		$sql =  "
				SELECT 
					* 
				from ref_kegiatan_mapping
				where kd_urusan90=".$options['kd_urusan90']
                    .' and kd_bidang90='.$options['kd_bidang90']
                    .' and kd_program90='.$options['kd_program90']
                    .' and kd_kegiatan90='.$options['kd_kegiatan90']
                    .' and kd_sub_kegiatan='.$options['kd_sub_kegiatan'];
		// echo $sql;
		$mapping = $this->CurlSimda(array(
			'query' => $sql
		), false, false);
		if(
			empty($mapping)
			&& get_option('_crb_auto_ref_kegiatan_mapping') == 1
		){
			$ref_bidang_mapping = $this->CurlSimda(array(
				'query' => "
					SELECT 
						* 
					from ref_bidang_mapping
					where kd_urusan90=".$options['kd_urusan90']
	                    .' and kd_bidang90='.$options['kd_bidang90']
			));
			$mapping_prog = $this->CurlSimda(array(
				'query' => "
					SELECT 
						* 
					from ref_kegiatan_mapping
					where kd_urusan90=".$options['kd_urusan90']
	                    .' and kd_bidang90='.$options['kd_bidang90']
	                    .' and kd_program90='.$options['kd_program90']
	                    .' and kd_kegiatan90='.$options['kd_kegiatan90']
			));
			if(empty($mapping_prog)){
				$max_prog = $this->CurlSimda(array(
					'query' => "
						SELECT 
							max(kd_prog) as max
						from ref_kegiatan_mapping
						where kd_urusan=".$ref_bidang_mapping[0]->kd_urusan
		                    .' and kd_bidang='.$ref_bidang_mapping[0]->kd_bidang
				));
				$kd_prog = 150;
				if(
					!empty($max_prog) 
					&& !empty($max_prog[0]->max) 
					&& $max_prog[0]->max >= $kd_prog
				){
					$kd_prog = $max_prog[0]->max+1;
				}
				$this->CurlSimda(array(
					'query' => "
						INSERT INTO ref_program (
							kd_urusan,
							kd_bidang,
							kd_prog,
							ket_program
						) VALUES (
							".$ref_bidang_mapping[0]->kd_urusan.",
							".$ref_bidang_mapping[0]->kd_bidang.",
							".$kd_prog.",
							'".str_replace("'", '`', substr($options['nama_program'], 0, 255))."'
						)"
				));
			}else{
				$kd_prog = $mapping_prog[0]->kd_prog;
			}
			$kd_keg = 150+$options['kd_sub_kegiatan'];
			$mapping_keg = $this->CurlSimda(array(
				'query' => "
					SELECT 
						* 
					from ref_kegiatan
					where kd_urusan=".$ref_bidang_mapping[0]->kd_urusan
	                    .' and kd_bidang='.$ref_bidang_mapping[0]->kd_bidang
	                    .' and kd_prog='.$kd_prog
	                    .' and kd_keg='.$kd_keg
			));
			if(empty($mapping_keg)){
				$this->CurlSimda(array(
					'query' => "
						INSERT INTO ref_kegiatan (
							kd_urusan,
							kd_bidang,
							kd_prog,
							kd_keg,
							ket_kegiatan
						) VALUES (
							".$ref_bidang_mapping[0]->kd_urusan.",
							".$ref_bidang_mapping[0]->kd_bidang.",
							".$kd_prog.",
							".$kd_keg.",
							'".str_replace("'", '`', substr($options['nama_kegiatan'], 0, 255))."'
						)"
				));
			}
			$ref_bidang = $this->CurlSimda(array(
				'query' => "
					SELECT 
						* 
					from ref_bidang
					where kd_urusan=".$ref_bidang_mapping[0]->kd_urusan
	                    .' and kd_bidang='.$ref_bidang_mapping[0]->kd_bidang
			));
			$kd_fungsi = $ref_bidang[0]->kd_fungsi;
			$ref_sub_fungsi90 = $this->CurlSimda(array(
				'query' => "
					SELECT 
						* 
					from ref_sub_fungsi90
					where kd_fungsi=".$kd_fungsi
			));
			$kd_sub_fungsi = $ref_sub_fungsi90[0]->kd_sub_fungsi;

			// perlu insert ke ref_program90 juga sepertinya
			$mapping_keg90 = $this->CurlSimda(array(
				'query' => "
					SELECT 
						* 
					from ref_kegiatan90
					where kd_urusan=".$options['kd_urusan90']
	                    .' and kd_bidang='.$options['kd_bidang90']
	                    .' and kd_program='.$options['kd_program90']
	                    .' and kd_kegiatan='.$options['kd_kegiatan90']
						.' and kd_fungsi='.$kd_fungsi
						.' and kd_sub_fungsi='.$kd_sub_fungsi
			));
			if(empty($mapping_keg90)){
				$this->CurlSimda(array(
					'query' => "
						INSERT INTO ref_kegiatan90 (
							kd_urusan,
							kd_bidang,
							kd_program,
							kd_kegiatan,
							nm_kegiatan,
							kd_fungsi,
							kd_sub_fungsi
						) VALUES (
							".$options['kd_urusan90'].",
							".$options['kd_bidang90'].",
							".$options['kd_program90'].",
							".$options['kd_kegiatan90'].",
							'".str_replace("'", '`', substr($options['nama_program'], 0, 255))."',
							".$kd_fungsi.",
							".$kd_sub_fungsi."
						)"
				));
			}
			$mapping_sub_keg90 = $this->CurlSimda(array(
				'query' => "
					SELECT 
						* 
					from ref_sub_kegiatan90
					where kd_urusan=".$options['kd_urusan90']
	                    .' and kd_bidang='.$options['kd_bidang90']
	                    .' and kd_program='.$options['kd_program90']
	                    .' and kd_kegiatan='.$options['kd_kegiatan90']
						.' and kd_sub_kegiatan='.$options['kd_sub_kegiatan']
			));
			if(empty($mapping_sub_keg90)){
				$this->CurlSimda(array(
					'query' => "
						INSERT INTO ref_sub_kegiatan90 (
							kd_urusan,
							kd_bidang,
							kd_program,
							kd_kegiatan,
							kd_sub_kegiatan,
							nm_sub_kegiatan
						) VALUES (
							".$options['kd_urusan90'].",
							".$options['kd_bidang90'].",
							".$options['kd_program90'].",
							".$options['kd_kegiatan90'].",
							".$options['kd_sub_kegiatan'].",
							'".str_replace("'", '`', substr($options['nama_kegiatan'], 0, 255))."'
						)"
				));
			}
			$this->CurlSimda(array(
				'query' => "
					INSERT INTO ref_kegiatan_mapping (
						kd_urusan,
						kd_bidang,
						kd_prog,
						kd_keg,
						kd_urusan90,
						kd_bidang90,
						kd_program90,
						kd_kegiatan90,
						kd_sub_kegiatan
					) VALUES (
						".$ref_bidang_mapping[0]->kd_urusan.",
						".$ref_bidang_mapping[0]->kd_bidang.",
						".$kd_prog.",
						".$kd_keg.",
						".$options['kd_urusan90'].",
						".$options['kd_bidang90'].",
						".$options['kd_program90'].",
						".$options['kd_kegiatan90'].",
						".$options['kd_sub_kegiatan']."
					)"
			));
			return $this->cekKegiatanMapping($options);
		}else{
			return $mapping;
		}
    }

    function get_up_simda(){
    	global $wpdb;
		$ret = array(
			'status'	=> 'success',
			'message'	=> 'Berhasil get data SIMDA!',
			'action'	=> $_POST['action']
		);
		if (!empty($_POST)) {
			if (!empty($_POST['api_key']) && $_POST['api_key']) {
				if(!empty($_POST['tahun_anggaran'])){
					$tahun_anggaran = $_POST['tahun_anggaran'];
		    		$up = $this->CurlSimda(array(
						'query' => "
							SELECT * FROM ta_spp WHERE jn_spp=1 AND tahun=$tahun_anggaran
							"
					));
					$unit = $wpdb->get_results($wpdb->prepare("
						SELECT 
							nama_skpd,
							id_skpd 
						from data_unit 
						where tahun_anggaran=%d
							AND active=1", $tahun_anggaran)
					, ARRAY_A);
					foreach ($unit as $k => $v) {
						$unit[$k]['mapping'] = get_option('_crb_unit_'.$v['id_skpd'] );
					}
					foreach ($up as $k => $v) {
						$rinc = $this->CurlSimda(array(
							'query' => "
								SELECT * FROM ta_spp_rinc WHERE no_spp='".$v->no_spp."' AND tahun=$tahun_anggaran
								"
						));
						$mapping = $v->kd_urusan.'.'.$v->kd_bidang.'.'.$v->kd_unit.'.'.$v->kd_sub;
						foreach ($unit as $key => $value) {
							if($mapping == $value['mapping']){
								$up[$k]->mapping = $value;
							}
						}
						$up[$k]->rinc = $rinc;
					}

					$ret['data'] = $up;
				} else {
					$ret['status'] = 'error';
					$ret['message'] = 'Format request salah!';
				}
			} else {
				$ret['status'] = 'error';
				$ret['message'] = 'APIKEY tidak sesuai!';
			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format Salah!';
		}
		die(json_encode($ret));
    }

    function cek_lisensi($options){
    	$return = array(
			'status' => 'success',
			'api_key' => '',
			'message' => 'Berhasil cek lisensi!'
		);
    	$res = $this->fetch_lisensi(array(
			'post' => array(
				'action' => 'cek_lisensi',
				'lisensi' => $options['api_key']
			)
		));
		$return['res'] = $res;
		if(!empty($res['err'])){
			$return['status'] = 'error';
        	$return['message'] = "cURL Error #:".$res['err']." (".$res['url'].")";
		}else{
			$ret = json_decode($res['response']);
            if(
            	!empty($ret)
            	&& $ret->status == 'success'
            ){
				$return['api_key'] = $ret->api_key;
            }else{
            	if(
            		!empty($ret)
            		&& !empty($ret->error)
            	){
            		$res['response'] = $ret->message;
            	}
				$return['status'] = 'error';
				$return['message'] = $res['response'];
            }
		}
		return $return;
	}

    function fetch_lisensi($options){
    	$url = get_option('_crb_server_wp_sipd');
    	$api_key_wp_sipd = get_option('_crb_server_wp_sipd_api_key');
    	$no_wa = get_option('_crb_no_wa');
		$nama_pemda = get_option('_crb_daerah');
		$cek = true;
		if(empty($url)){
			$cek = false;
			$pesan = 'Server WP-SIPD wajib diisi!';
		}else if(empty($api_key_wp_sipd)){
			$cek = false;
			$pesan = 'API KEY Server WP-SIPD wajib diisi!';
		}else if(empty($no_wa)){
			$cek = false;
			$pesan = 'Nomor WA wajib diisi!';
		}else if(empty($nama_pemda)){
			$cek = false;
			$pesan = 'Nama Pemda wajib diisi!';
		}
		if(true == $cek){
			$domain = $_SERVER['SERVER_NAME'];
			$api_params = array(
				'api_key' => $api_key_wp_sipd,
				'no_wa' => $no_wa,
				'nama_pemda' => $nama_pemda,
				'produk' => 'WP-SIPD',
				'domain' => $domain,
			);
			$api_params = array_merge($api_params, $options['post']);
			$req = http_build_query($api_params);
			$curl = curl_init();
			curl_setopt_array($curl, array(
	            CURLOPT_URL => $url,
	            CURLOPT_RETURNTRANSFER => true,
	            CURLOPT_ENCODING => "",
	            CURLOPT_MAXREDIRS => 10,
	            CURLOPT_TIMEOUT => 30,
	            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	            CURLOPT_CUSTOMREQUEST => "POST",
	            CURLOPT_POSTFIELDS => $req,
	            CURLOPT_SSL_VERIFYPEER => false,
	            CURLOPT_SSL_VERIFYHOST => false,
	            CURLOPT_CONNECTTIMEOUT => 0,
	            CURLOPT_TIMEOUT => 10000
	        ));
	        $response = curl_exec($curl);
	        $err = curl_error($curl);
	        curl_close($curl);
	    }else{
	    	$api_params = array();
	    	$response = '';
	    	$err = $pesan;
	    }
        return array( 
        	'url' => $url, 
        	'params' => $api_params, 
        	'response' => $response, 
        	'err' => $err 
        );
    }
}
