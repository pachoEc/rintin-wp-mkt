<?php
/**
 * AJAX handlers */ 
// Returns the saved user billing state
add_action( 'wp_ajax_nopriv_billing_state_ajax', 'billing_state_ajax' );
add_action( 'wp_ajax_billing_state_ajax', 'billing_state_ajax' );
function billing_state_ajax(){
	if ( wp_verify_nonce( $_POST['_wpnonce'], 'wp_rest' ) ){
		$user_id = get_current_user_id();
		echo get_user_meta($user_id, 'billing_state', true);
		exit;
	} else {
		echo 'nonce check failed';
		exit;
	}
}

// Sends the address components from the geocode api
add_action( 'wp_ajax_nopriv_geocode_ajax', 'geocode_ajax' );
add_action( 'wp_ajax_geocode_ajax', 'geocode_ajax' );
function geocode_ajax(){
	if ( wp_verify_nonce( $_POST['_wpnonce'], 'wp_rest' ) ){
		$content = $_POST['billing_address'];
		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($content) . '&components=country:mx&types=establishment|route&key=AIzaSyC9GnYe0meFUxQBaMcUFgqaBQgYogsFkyM';
		$response = wp_remote_get($url);
		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);
		$json_data = json_encode($data['results'][0], JSON_UNESCAPED_UNICODE);
		echo $json_data;
		exit;
	} else {
		echo 'nonce check failed';
		exit;
	}
}

// Sends the possible predictions of autocomplete api
add_action( 'wp_ajax_nopriv_autocomplete_ajax', 'autocomplete_ajax' );
add_action( 'wp_ajax_autocomplete_ajax', 'autocomplete_ajax' );
function autocomplete_ajax(){
	if ( wp_verify_nonce( $_POST['_wpnonce'], 'wp_rest' ) ){
		$content = $_POST['billing_address'];
		$url = 'https://maps.googleapis.com/maps/api/place/autocomplete/json?input=' . urlencode($content) . '&types=geocode&country:mx&key=AIzaSyC9GnYe0meFUxQBaMcUFgqaBQgYogsFkyM';
		$response = wp_remote_get($url);
		$body = wp_remote_retrieve_body($response);
		$data = json_decode($body, true);
		$address_array = array();
		foreach ($data['predictions'] as $component) {
			$long_name = $component['description'];
			array_push($address_array, $long_name);

		}
		$json_data = json_encode($address_array, JSON_UNESCAPED_UNICODE);
		echo $json_data;
		exit;

	} else {
		echo 'nonce check failed';
		exit;
	}
}

// Checks postal code for fees and payment options
// Sets transients with this information
add_action( 'wp_ajax_nopriv_postal_code_ajax', 'postal_code_ajax' );
add_action( 'wp_ajax_postal_code_ajax', 'postal_code_ajax' );
function postal_code_ajax(){
	if ( wp_verify_nonce( $_POST['_wpnonce'], 'wp_rest' ) ){
		$content = strval($_POST['postal_code']);
		$oax_cp_array = ['68070', '68200', '71330', '71294', '68288', '71588', '71355', '70484', '71256', '71550', '70442', '71516', '68026', '70450', '71586', '71313', '68290', '71334', '68235', '71580', '70426', '71235', '71408', '68207', '68125', '71220', '68237', '68254', '68204', '71268', '68043', '71207', '68067', '68140', '70400', '71249', '71210', '71508', '70496', '70411', '71526', '71569', '68133', '71202', '70423', '71333', '71224', '71205', '68230', '70445', '68293', '71283', '68090', '68218', '68287', '68259', '70480', '70497', '68120', '71574', '71242', '71400', '71233', '68149', '71363', '71505', '71520', '71203', '71575', '70461', '70437', '68146', '71354', '68213', '70474', '68250', '70434', '68214', '68269', '71232', '71529', '71552', '71578', '68277', '68210', '71316', '71343', '71545', '68013', '68216', '68143', '68157', '71352', '68130', '71266', '71267', '70410', '71507', '71240', '68273', '68276', '68016', '71246', '68266', '71248', '68028', '68233', '70456', '70412', '71273', '71323', '71548', '71568', '71292', '71340', '68068', '71502', '70436', '68126', '71317', '70435', '71218', '71404', '68268', '71228', '71406', '68075', '71215', '71409', '68208', '68261', '71253', '68060', '71295', '68025', '70430', '71260', '68267', '68284', '70440', '68024', '68220', '71204', '68023', '71290', '68104', '70404', '70406', '71525', '68222', '71284', '71557', '68217', '71227', '71270', '70453', '70495', '68264', '68278', '68205', '68224', '71315', '68103', '70420', '71226', '71244', '71320', '71338', '71403', '68257', '68280', '71350', '70498', '68020', '68045', '71359', '68248', '68000', '68144', '70477', '68156', '68010', '68299', '70482', '71597', '71214', '71300', '68227', '68034', '68134', '71500', '71514', '68240', '71405', '68110', '71528', '68256', '71576', '70439', '71577', '68228', '70403', '71314', '71543', '70460', '68258', '68236', '68033', '71517', '71200', '70467', '68027', '71515', '68260', '68285', '68275', '68128', '70478', '71247', '71230', '68100', '71223', '71567', '71324', '71523', '71243', '71310', '71554', '71255', '68080', '71590', '71318', '68018', '70428', '71506', '70464', '71518', '71297', '68155', '68234', '68040', '68030', '71534', '68232', '71280', '71250', '68219', '70408', '68274', '71336', '68148', '68159', '68083', '70417', '68247', '71254', '71560', '70405', '68115', '71265', '68127', '71565', '68050', '71213', '68039', '68153', '71512', '68150', '71357', '68154', '71360', '71245', '71337', '70458', '71530', '71504', '71553', '68044', '68270', '68226', '68262', '70479', '71217', '71573', '71231', '71286', '71595', '71364', '70407', '68263', '68283', '68244', '71510', '68297', '71537', '71274', '71562', '71566', '71570', '68238', '70424', '71222'];
		$min_cp_99 = ['3300', '53310', '55768', '14387', '7230', '2200', '9706', '14140', '53220', '42040', '54605', '56410', '6140', '15450', '7850', '7707', '2320', '15850', '55425', '1275', '5780', '14735', '55294', '13310', '52775', '16440', '55290', '1139', '8300', '56599', '3700', '56529', '54467', '10926', '53580', '12700', '54879', '2630', '56609', '9680', '55036', '56585', '15830', '56440', '2830', '1789', '7100', '53120', '56224', '57210', '54050', '56395', '55085', '5000', '54134', '2100', '55369', '42101', '55125', '55728', '10500', '54128', '3640', '9479', '55744', '56390', '15430', '54015', '2490', '54945', '2950', '53688', '56577', '15010', '53680', '14150', '53224', '55500', '14049', '11619', '54834', '53237', '53830', '55068', '56110', '3590', '14020', '54764', '16070', '1759', '11950', '15410', '15900', '55039', '56226', '55420', '55027', '5269', '9856', '54068', '54118', '53809', '1130', '55349', '53216', '54040', '55138', '11340', '52949', '15250', '42033', '54929', '4320', '54469', '53110', '8020', '55410', '1010', '7456', '6030', '56500', '54883', '16533', '13220', '14720', '5220', '55416', '56330', '55330', '55238', '55084', '52778', '16300', '55520', '6000', '7370', '55640', '14266', '6700', '4489', '3510', '55749', '1219', '11360', '57520', '15530', '52779', '42030', '5700', '54196', '10620', '13070', '12100', '56337', '15960', '53227', '14357', '10630', '16428', '9209', '55508', '1900', '56625', '14420', '9638', '13040', '54023', '53070', '54434', '57171', '57400', '9858', '54448', '53124', '53280', '54877', '55135', '56512', '5200', '54769', '54893', '55628', '3420', '8030', '10810', '56366', '9290', '9670', '42020', '56225', '56254', '9820', '54959', '1540', '55418', '42055', '55548', '54955', '55709', '6470', '54720', '55600', '42037', '10130', '13050', '54094', '14449', '2530', '55340', '11920', '55414', '9020', '15460', '52975', '53690', '54730', '14480', '2860', '6080', '2800', '56207', '1376', '7530', '57170', '4939', '42004', '53426', '7149', '55074', '1400', '9870', '57138', '14070', '54197', '55237', '15310', '52764', '57719', '9140', '6300', '1560', '55064', '10600', '52790', '55267', '55605', '3410', '1830', '2650', '2930', '55717', '3540', '2730', '9099', '7900', '4840', '53670', '14490', '55707', '3023', '9850', '14300', '11600', '4370', '53930', '6860', '56100', '54440', '1549', '2120', '11260', '54700', '6200', '56245', '4610', '13400', '56607', '57800', '57620', '5710', '6400', '1230', '53339', '54927', '52920', '14040', '52924', '54915', '56605', '53390', '7630', '16640', '3940', '15500', '54656', '56555', '15520', '14250', '55246', '53798', '3020', '54800', '7240', '3920', '54413', '54539', '54683', '16530', '52787', '55516', '2600', '2040', '4040', '6040', '9608', '53718', '1650', '56233', '15840', '53247', '52998', '57120', '7187', '13219', '52997', '7670', '57460', '55517', '1140', '56430', '54145', '13430', '1259', '57185', '11370', '1470', '56428', '56223', '2300', '10368', '13440', '16609', '57180', '16210', '1320', '1857', '54658', '4940', '7620', '56569', '54604', '7860', '14657', '53569', '9760', '53960', '1569', '55158', '9260', '53490', '1220', '3650', '1290', '53370', '54146', '9100', '55519', '14710', '57150', '53177', '54894', '11220', '16443', '8800', '56509', '56623', '53708', '55146', '9420', '1299', '1080', '2330', '2460', '54615', '42083', '4330', '53050', '16520', '15510', '9239', '53179', '56343', '15990', '54168', '2900', '13550', '8100', '56263', '2470', '11530', '13180', '7214', '9880', '9930', '54180', '3600', '54408', '42064', '5348', '11300', '55295', '16040', '16050', '7940', '55634', '12070', '54076', '11100', '7858', '53660', '57420', '7650', '55188', '16610', '54645', '10370', '56400', '52784', '54890', '1150', '8760', '13520', '53900', '55404', '56550', '16038', '9640', '52797', '1170', '15600', '56215', '56537', '4400', '11290', '42078', '1610', '53115', '55629', '3710', '4800', '1240', '7119', '55635', '7160', '53309', '3740', '54463', '4960', '56255', '2000', '52916', '1125', '8910', '2640', '16200', '56535', '53298', '57810', '11350', '42102', '54093', '16020', '54030', '14406', '54460', '11430', '7720', '55758', '56340', '7979', '14000', '14160', '9920', '54680', '11910', '14219', '55230', '11529', '7220', '57510', '8930', '13410', '53128', '16720', '54878', '9220', '56257', '13010', '13549', '56525', '4369', '52793', '54857', '52799', '55765', '55619', '13700', '14647', '16340', '14320', '54098', '56608', '9440', '52788', '54614', '54466', '57809', '52994', '54785', '55604', '14376', '6900', '8770', '1090', '14325', '54054', '55130', '55739', '7330', '1530', '55040', '3104', '56240', '10840', '11510', '7680', '54836', '54407', '54694', '3910', '2920', '14210', '55236', '14240', '9868', '14900', '9130', '7069', '14620', '4600', '54745', '54840', '14430', '7859', '5050', '10400', '6090', '3330', '3010', '54400', '1070', '7899', '1250', '10580', '53450', '2910', '53130', '7224', '10830', '54152', '55025', '55094', '42060', '13360', '15020', '54426', '55755', '16059', '14388', '55726', '53940', '55506', '52789', '52927', '42119', '16606', '9000', '14360', '55458', '54187', '57950', '5020', '2760', '14100', '9897', '15610', '6350', '11610', '1279', '2990', '9270', '9630', '1870', '15400', '15470', '7130', '14640', '6720', '14476', '55308', '7889', '52934', '55767', '14110', '42018', '53650', '8920', '55419', '16880', '53459', '55738', '42097', '56616', '14643', '54980', '55095', '7420', '56619', '52930', '2960', '55515', '4010', '16240', '55390', '56513', '9250', '13600', '42187', '13545', '14310', '55736', '5360', '55610', '14739', '14409', '57920', '55245', '55747', '5600', '55028', '54938', '56384', '11480', '13099', '52953', '56150', '54193', '4918', '42036', '13210', '54119', '56220', '55637', '12910', '15320', '3400', '56530', '10380', '55023', '7739', '55745', '10820', '2519', '10300', '55509', '14609', '15730', '56334', '56266', '4200', '53760', '57718', '56429', '56527', '5610', '1708', '13093', '55299', '3100', '7144', '56510', '3230', '52978', '9969', '55137', '9520', '14400', '54457', '2710', '1260', '56375', '13150', '55510', '57849', '53150', '55063', '7510', '56618', '54714', '52769', '53279', '7520', '14730', '56520', '3000', '42075', '54880', '53700', '55288', '7580', '1419', '57410', '53178', '55296', '42182', '15220', '2670', '16000', '54109', '55249', '15300', '4250', '4519', '7470', '14658', '54884', '12300', '8420', '54943', '42026', '16430', '9960', '9849', '42110', '5280', '7188', '55769', '5030', '54805', '14608', '8710', '16450', '55234', '2090', '15540', '2080', '56558', '1408', '2128', '57710', '10320', '1410', '9790', '14308', '52760', '54449', '13000', '14230', '56106', '6170', '57189', '1100', '55417', '8610', '1490', '3630', '1820', '14386', '9280', '55700', '15810', '1280', '52940', '1460', '13450', '6020', '3340', '7010', '54640', '7250', '57300', '54424', '7380', '55115', '16850', '55630', '2129', '55298', '53278', '56536', '52959', '54425', '54063', '2020', '53248', '53714', '55050', '53296', '55459', '14737', '56205', '55280', '16740', '16604', '55189', '56344', '15440', '7540', '53100', '6890', '55247', '42116', '55320', '9578', '52783', '14108', '16605', '53500', '55269', '11489', '11850', '53400', '16860', '53116', '14700', '54767', '1520', '6850', '11560', '53696', '56237', '55719', '14208', '53687', '11210', '4815', '16400', '14080', '55778', '9230', '8600', '56553', '15700', '54021', '54870', '54414', '52937', '5410', '55770', '52777', '55266', '7110', '55300', '1619', '7207', '52763', '6880', '52957', '56606', '11410', '7369', '53000', '7880', '15290', '54026', '56203', '9070', '16457', '55260', '14268', '14200', '1285', '52767', '10710', '54913', '3810', '53348', '54190', '55729', '15980', '1566', '13090', '54473', '7770', '42035', '56253', '4910', '9800', '52960', '54713', '2010', '14340', '14239', '55287', '56227', '57310', '13270', '55268', '53970', '15630', '11500', '55764', '6920', '3560', '52794', '55530', '54655', '55613', '54464', '53839', '54189', '7950', '56579', '14653', '54476', '9900', '7708', '2440', '9460', '52785', '1340', '8900', '16080', '55270', '4210', '54916', '53689', '2140', '1548', '9709', '7700', '13120', '53160', '14630', '53330', '12930', '13080', '6820', '11280', '9829', '55540', '16739', '54073', '1588', '54650', '55220', '57450', '7918', '7259', '9700', '7455', '4630', '56647', '56346', '8400', '1620', '1480', '1720', '15339', '2500', '9200', '54172', '14439', '15260', '55609', '16514', '1618', '7560', '54696', '16797', '4810', '16034', '10340', '52929', '7509', '2719', '4310', '42094', '5500', '9708', '54195', '54057', '11830', '11200', '42000', '1089', '55507', '15970', '52948', '56586', '53950', '11810', '54954', '5379', '14748', '7640', '52980', '2310', '42070', '11320', '55127', '15860', '3620', '54944', '54744', '54037', '3800', '16620', '55105', '4730', '1780', '55075', '11250', '3720', '2750', '10369', '14090', '55737', '55087', '9040', '7930', '56363', '14460', '13100', '14646', '14410', '1030', '53430', '4980', '3240', '4620', '6250', '56243', '56160', '1750', '11580', '15390', '52796', '2240', '2790', '15340', '4970', '12600', '9320', '53290', '54028', '3520', '4650', '11440', '10910', '55714', '11470', '13625', '7190', '54420', '55065', '56516', '54765', '10920', '54858', '10360', '2150', '6220', '53428', '7870', '54719', '1550', '6050', '7359', '56386', '10330', '15660', '8210', '13509', '15000', '12200', '1645', '16030', '55348', '12920', '16600', '5010', '52987', '53570', '2980', '6270', '11400', '14500', '52918', '55347', '14275', '52766', '4360', '4710', '11000', '8810', '14740', '4530', '54830', '6870', '53458', '52900', '56540', '56210', '55720', '11490', '7209', '13540', '6760', '55415', '1109', '55017', '53260', '53800', '15330', '54038', '5760', '12500', '13250', '54158', '55107', '55766', '7480', '9359', '9570', '1296', '42090', '4660', '52774', '2480', '7550', '12250', '55405', '7460', '53217', '56567', '56380', '1849', '52919', '55100', '9620', '52970', '52910', '52996', '57709', '7268', '53030', '55200', '55264', '53664', '16410', '56560', '9837', '9705', '54831', '55743', '42057', '55750', '56247', '54957', '7300', '2160', '9530', '12080', '42180', '55128', '54602', '14060', '8230', '54693', '56508', '4460', '54610', '52950', '11650', '52915', '52928', '4390', '4899', '11870', '53410', '54740', '56350', '7279', '55139', '11550', '9750', '7210', '3500', '14380', '56213', '56614', '9470', '53060', '55076', '1200', '56383', '52945', '2360', '5270', '54939', '4500', '10700', '14734', '7109', '9910', '4930', '53730', '9400', '9840', '54075', '54942', '55317', '56267', '56588', '53229', '55069', '7740', '55760', '4410', '5310', '54882', '55029', '15380', '57739', '2130', '53117', '10000', '15309', '54725', '56505', '16776', '3580', '5100', '7910', '9720', '57760', '9689', '16749', '54416', '9430', '7839', '2720', '9208', '54760', '57440', '14039', '1770', '42084', '15740', '4890', '13300', '42080', '5129', '42027', '54616', '3550', '7800', '54855', '55310', '54130', '54410', '55030', '4938', '1860', '54435', '56615', '57100', '54759', '54807', '16629', '56568', '56264', '4230', '13280', '7919', '16799', '12950', '3660', '56256', '4700', '7340', '54033', '42050', '54025', '4870', '11520', '53698', '54710', '14479', '55718', '9890', '9450', '14248', '56373', '54459', '7050', '16617', '14377', '7939', '14440', '56246', '54948', '7090', '4920', '57205', '55614', '8240', '14610', '55000', '1278', '14267', '52990', '9830', '55243', '2250', '57129', '7239', '11860', '5240', '54950', '9660', '56235', '2230', '11700', '7040', '52979', '53530', '14650', '14390', '52795', '53228', '55117', '56589', '54067', '57000', '6840', '8010', '16616', '3610', '54743', '1210', '1800', '10379', '16550', '56105', '55289', '53799', '13419', '7990', '56563', '56514', '52923', '54684', '14408', '16459', '7400', '7080', '6280', '55187', '4950', '53719', '55240', '54932', '9060', '13094', '15280', '9480', '42014', '57720', '55242', '9360', '9859', '53320', '16630', '9310', '9090', '1730', '54070', '8650', '3200', '52938', '53425', '7430', '55190', '4100', '1640', '42032', '54173', '55716', '55244', '13530', '54766', '56613', '9510', '56617', '1040', '53560', '16429', '9319', '16010', '7350', '4120', '56230', '12410', '42099', '42079', '56641', '5730', '14600', '56260', '8730', '9819', '15350', '7969', '16607', '5219', '10200', '54958', '2680', '56396', '8700', '57910', '53790', '55776', '10900', '55090', '16100', '15200', '9030', '54925', '7820', '6070', '42039', '15240', '56200', '42111', '57530', '15640', '52988', '12800', '12400', '4318', '9429', '57630', '7450', '53717', '7410', '6430', '7440', '16035', '54110', '2050', '55066', '4739', '56507', '4919', '14429', '54090', '54143', '9600', '1239', '55730', '54900', '2340', '52776', '52768', '55450', '55067', '53440', '15650', '56250', '13630', '12110', '14050', '54439', '1269', '56360', '7970', '9740', '6060', '1450', '56515', '54715', '16810', '3303', '54009', '4490', '9550', '53529', '4420', '54910', '3530', '15270', '55126', '8310', '9210', '55148', '7270', '13230', '53270', '55752', '14350', '7750', '54608', '9438', '15800', '42181', '53297', '53470', '13640', '7760', '14270', '56539', '53010', '53694', '55014', '55715', '55757', '8040', '55016', '1810', '13508', '7360', '7600', '42029', '53770', '14260', '7140', '9810', '1407', '4510', '54803', '6450', '3570', '9690', '53200', '54010', '56170', '11230', '5110', '56214', '16813', '13315', '52976', '52798', '1330', '54935', '55284', '55740', '8830', '54860', '54150', '9500', '55235', '52925', '57140', '42086', '8620', '7730', '15230', '4260', '55010', '55218', '53550', '14760', '4470', '56208', '7000', '56265', '2410', '14659', '9089', '54049', '54716', '52967', '54136', '57830', '55024', '5119', '57820', '57188', '53695', '1298', '57840', '1420', '54124', '14438', '8200', '42056', '42183', '53659', '53640', '4030', '12940', '56236', '15820', '53788', '54055', '55176', '55018', '11460', '14269', '1000', '5260', '53427', '10800', '7838', '7960', '56420', '42015', '14629', '1630', '57750', '10010', '6780', '52926', '57740', '53658', '53713', '5330', '5230', '11240', '55430', '54729', '6240', '11330', '53780', '11840', '56204', '54430', '42082', '3440', '15670', '54135', '54477', '56216', '57500', '54060', '42113', '54875', '54712', '42034', '13610', '56353', '54920', '53127', '53119', '4440', '7180', '9637', '53129', '55037', '7754', '7755', '3930', '53378', '55020', '1863', '1270', '16029', '52786', '9828', '56356', '5370', '55170', '1807', '11590', '14120', '8510', '1276', '56217', '54946', '55140', '55712', '55118', '52770', '54198', '1180', '6600', '14209', '9838', '53126', '8320', '2660', '52936', '55248', '53240', '1110', '42058', '2770', '1859', '11800', '55339', '9730', '54022', '2870', '42088', '2520', '9710', '56495', '55710', '13510', '55038', '55056', '42092', '53219', '9240', '14326', '56564', '1289', '7170', '3103', '13319', '54914', '9080', '53138', '54080', '1760', '16513', '54020', '1050', '2459', '13030', '10378', '15420', '56268', '7780', '4830', '7280', '1600', '53420', '2780', '8720', '54687', '53340', '57700', '3310', '57103', '54147', '57930', '54835', '1700', '56604', '53787', '53820', '2940', '57200', '3900', '53716', '53810', '53398', '55498', '15870', '2840', '55620', '57179', '16780', '53218', '9704', '1510', '1904', '54108', '55407', '55547', '57730', '56120', '4640', '5120', '55059', '13460', '16310', '53697', '7840', '52985', '54000', '54600', '4480', '52989', '1710', '8220', '55147', '9970', '3320', '42098', '54149', '7469', '7310', '55708', '42100', '6100', '11289', '53909', '7164', '52773', '14389', '15710', '9609', '53140', '53840', '54934', '54100', '52986', '7200', '5530', '7150', '53329', '56643', '53300', '6500', '2810', '56130', '16750', '14427', '57900', '55713', '16500', '55607', '54924', '13200', '11420', '57940', '9300', '11540', '53520', '15100', '15210', '1120', '9769', '16320', '10640', '16840', '13060', '57130', '54140', '53170', '4240', '42095', '54474', '7089', '1310', '6010', '54142', '54402', '55754', '55636', '55114', '9780', '9839', '53230', '56526', '1509', '56580', '9010', '10610', '54409', '55360', '42117', '14749', '56528', '7320', '7790', '1840', '16800', '2700', '54162', '53598', '55518', '5750', '1389', '1539', '16900', '55104', '56490', '11040', '55748', '11270', '15750', '16060', '55606', '1060', '54160', '54933', '14330', '14370', '52966', '54126', '56570', '55129', '9770', '56576', '53533', '3430', '2060', '7890', '57610', '56590', '2970', '3840', '9696', '10660', '3730', '56556', '11930', '4450', '52968', '57430', '53125', '13020', '52995', '7810', '9410', '54753', '54170', '4929', '14738', '56646', '56640', '54763', '16420', '1160', '7570', '55400', '1020', '7189', '54850', '52765', '55429', '54405', '15620', '56624', '53338', '54930', '54685', '54092', '7058', '57158', '4020', '56566', '55060', '54603', '56620', '56610', '42115', '8000', '7830', '14010', '8500', '5118', '54016', '56642', '14470', '1430', '11310', '54470', '57139', '54475', '4000', '55316', '1729', '1590', '7060', '5520', '8840', '9648', '57178', '11450', '1790', '56140', '53710', '55490', '5320', '6800', '56338', '52946', '14220', '9350', '9860', '54918', '57600', '2400', '16036', '55338', '55080', '53215', '2420', '56524', '10020', '4300', '54120', '56244', '55055', '55180', '14655', '2070', '56600', '13420', '54127', '9180', '52947', '53489', '3820', '54069', '53283', '55210', '55297', '53350', '42010', '16770', '53910', '54804', '56377', '4340', '56538', '54417', '2099', '9560', '9698', '54940', '9940', '42184', '14426', '15680', '16090', '57819', '13273', '7290', '55763', '56644', '55773', '57708', '7980', '55119', '7183', '11570', '7869', '54455', '53239', '15370', '1740', '4380', '10350', '56565', '7070', '54949', '16614', '53519', '7020', '11820', '55746', '4909', '1500', '54607', '55705', '12000', '52780', '53040', '7199', '56335', '1377', '54750', '53460', '1538', '56370', '53819', '1049', '55120', '54926', '53250', '56376', '52977', '7920', '14030', '54059', '55149', '13278', '54017', '55070', '7500', '16615', '55549', '5400', '52965', '16710'];
		
	$cp_array = ['71245', '70407', '70437', '68045', '68023', '68200', '68288', '71334', '68068', '70460', '71202', '70456', '70805', '70498', '68157', '68150', '71534', '71253', '68110', '68236', '71300', '70436', '71295', '71310', '68277', '68238', '71557', '71222', '71529', '70405', '71250', '71204', '68299', '71359', '71568', '71578', '70417', '71313', '71405', '70461', '71220', '71360', '71510', '68030', '71577', '71406', '68028', '68125', '70439', '68224', '70478', '71576', '71233', '71274', '71565', '71338', '68254', '68039', '71330', '71554', '68218', '71290', '71512', '68127', '70404', '71580', '70467', '68290', '68227', '71283', '71516', '70435', '70477', '71586', '68213', '71205', '68159', '71408', '71354', '70434', '71520', '71337', '71246', '70497', '68293', '71231', '71352', '71355', '68143', '68270', '71570', '71284', '71517', '70464', '68287', '71292', '71550', '71560', '71403', '68018', '71294', '71515', '68156', '70430', '71314', '68146', '68285', '71242', '68233', '68016', '71240', '68154', '70479', '71316', '68080', '68235', '71545', '71266', '68216', '71525', '68140', '71507', '68149', '68070', '68100', '68044', '71213', '70800', '68268', '70458', '70480', '71254', '71553', '68060', '71320', '71363', '71350', '68115', '70423', '68283', '68219', '71273', '68033', '70408', '68134', '68104', '68222', '71502', '68075', '71595', '68067', '68144', '71228', '71400', '68214', '68240', '68234', '68205', '68128', '68261', '71232', '70445', '68210', '68013', '68130', '68284', '70406', '70440', '68260', '68276', '68263', '71235', '71286', '68244', '71537', '68230', '70410', '71318', '70450', '71243', '71260', '71574', '68010', '71255', '71552', '71357', '68258', '71526', '70803', '68273', '68153', '68126', '71404', '71409', '70453', '68207', '68040', '71218', '71317', '68256', '71265', '68050', '68237', '71215', '70400', '68226', '70495', '71504', '70484', '68083', '70496', '70442', '71562', '70802', '71505', '68026', '68034', '71244', '71280', '71236', '68043', '68025', '71249', '68155', '68208', '71566', '68120', '71297', '68024', '71523', '71530', '71548', '71597', '71267', '71200', '71224', '71214', '71323', '70420', '68103', '71315', '71340', '71508', '71514', '70428', '71343', '68266', '71588', '68262', '68228', '68027', '68133', '71590', '68248', '70426', '71256', '71247', '70411', '68259', '71210', '70412', '68264', '71518', '71364', '68148', '71573', '71203', '71543', '71324', '68269', '68000', '70403', '71528', '68217', '71207', '71336', '68278', '68232', '71223', '68267', '68090', '68257', '68247', '71567', '71333', '71226', '68220', '71230', '68280', '71500', '68204', '71227', '71217', '70482', '71569', '71575', '70424', '68274', '71248', '71268', '71506', '68250', '71270', '68020', '68275', '70474', '68297', '68145'];
		if(in_array($content, $min_cp_99) || in_array($content, $cp_array)){
			set_transient( 'is_in_cp_array', true, 15 * MINUTE_IN_SECONDS );
		} else{
			set_transient( 'is_in_cp_array', false, 15 * MINUTE_IN_SECONDS );
		}
		if(in_array($content, $min_cp_99)){
			set_transient( 'is_in_99', true, 15 * MINUTE_IN_SECONDS );
		} else {
			set_transient( 'is_in_99', false, 15 * MINUTE_IN_SECONDS );
		}
		if(in_array($content, $oax_cp_array)){
			set_transient( 'is_in_oax', true, 15 * MINUTE_IN_SECONDS );
		} else {
			set_transient( 'is_in_oax', false, 15 * MINUTE_IN_SECONDS );
		}
		
		echo $content;
		exit;
	} else {
		echo 'nonce check failed';
		exit;
	}
}
?>