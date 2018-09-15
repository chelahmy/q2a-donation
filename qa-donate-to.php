<?php

function donate_points_to_user($handle, $points)
{
	$userid = qa_handle_to_userid($handle);
	
	if ($userid <= 0 || $points <= 0)
		return;
		
	require_once QA_INCLUDE_DIR . 'qa-db-metas.php';
	$opt = 'donation_points';
	$dpoints = qa_db_usermeta_get($userid, $opt);
	
	if ($dpoints == null)
		$dpoints = 0;
		
	qa_db_usermeta_set($userid, $opt, $dpoints + $points);
} 

class qa_donate_to_page {

	function get_request($request)
	{
		$parts = explode('/', $request);
		
		if (count($parts) < 1 || $parts[0] != 'donate-to')
			return FALSE;
			
		array_splice($parts, 0, 1);
		
		return $parts;
	}

	function match_request($request)
	{
		if ($this->get_request($request) === FALSE)
			return FALSE;
		
		return TRUE;
	}

	function page_donate_to($content, $handle)
	{
		if (strlen($handle) <= 0)
			qa_redirect('/');

		if (qa_clicked('donate')) {
			$points = intval(trim(qa_post_text('points')));
			qa_redirect('donate-to/' . $handle . '/' . $points);
		}
			
		$user = '<a href="' . qa_path('user/' . $handle) . '">' . $handle . '</a>';
		$content['title'] = qa_lang_html_sub('plugin_donation_desc/donate_points_to', $user);

		if (isset($_REQUEST['points']))
			$points = intval($_REQUEST['points']);
		else
			$points = 100;		
				
		$content['form']=array(
			'tags' => 'METHOD="POST" ACTION="' . qa_self_html(). '"',

			'style' => 'tall', // could be 'wide'

			'fields' => array(
				'points' => array(
					'label' => qa_lang_html('plugin_donation_desc/number_of_points_to_donate'),
					'type' => 'number',
					'tags' => 'NAME="points" ID="points"',
					'value' => $points,
				),
			),

			'buttons' => array(
				array(
					'tags' => 'NAME="donate"',
					'label' => qa_lang_html('plugin_donation_desc/donate'),
				),
			),
		);

		$content['focusid'] = 'points';

		return $content;
	}

	function page_donate_to_for($content, $handle, $points)
	{
		if (strlen($handle) <= 0 || $points <= 0)
			qa_redirect('/');

		if (qa_clicked('back')) {
			qa_redirect('donate-to/' . $handle, array('points' => $points));
		}
		
		if (qa_clicked('pay')) {
			$asset = trim(qa_post_text('pay_with'));
			
			if (strlen($asset) > 0) {
				
				if (function_exists('wp_get_waves_pay_req_url')) {
					$rate = qa_donation_point_rate($asset);
					$amount = intval($points) * $rate;
					$cb_url = qa_path_absolute('donate-to/' . $handle . '/' . $points . '/' . $asset . '/cb');
					$w_url = wp_get_waves_pay_req_url($asset, $amount, $cb_url);
					qa_redirect_raw($w_url);
					//qa_redirect_raw($cb_url . '?txid=9aoC4tNPNNCpz9N3V5ea38mqtBYDeQjs2wY281HPEph4'); // Testing the callback function
					//$content['custom'] = '<H1>' . qa_lang_html('plugin_donation_desc/thank_you') . '</H1>';
				}
			}
			else
				$content['custom'] = qa_lang_html('plugin_donation_desc/please_select_payment');
		}
			
		$userid = qa_handle_to_userid($handle);
		$user = '<a href="' . qa_path('user/' . $handle) . '">' . $handle . '</a>';
		$content['title'] = qa_lang_html_sub('plugin_donation_desc/donate_points_to', $user);
		
		$pay_options = array();
		
		$rates = qa_donation_point_rates_opt();
		
		if (is_array($rates)) {
			
			ksort($rates);
			
			foreach ($rates as $name => $rate)
			{
				if (function_exists('qa_waves_asset_dp')) // Query a Waves Pays plugin function
					$dp = qa_waves_asset_dp($name);
				else
					$dp = 8;

				$pay_options[$name] = $name . ' ' . number_format($rate * $points, $dp);
			} 
		}

		$content['form']=array(
			'tags' => 'METHOD="POST" ACTION="' . qa_self_html(). '"',

			'style' => 'tall', // could be 'wide'

			'fields' => array(
				'pay_with' => array(
					'label' => qa_lang_html_sub('plugin_donation_desc/pay_points_with', $points),
					'type' => 'select-radio',
					'tags' => 'NAME="pay_with" ID="pay_with"',
					'value' => '',
					'options' => $pay_options,
				),
			),

			'buttons' => array(
				array(
					'tags' => 'NAME="pay"',
					'label' => qa_lang_html('plugin_donation_desc/pay'),
				),
				array(
					'tags' => 'NAME="back"',
					'label' => qa_lang_html('plugin_donation_desc/back'),
				),
			),
		);

		return $content;
	}

	// The call back function for the Waves client after payment has been made.
	// The Waves client will attach the txid as a return parameter.
	function page_donate_to_cb($content, $handle, $points, $asset)
	{
		$userid = qa_handle_to_userid($handle);
		$user = '<a href="' . qa_path('user/' . $handle) . '">' . $handle . '</a>';
		$content['title'] = qa_lang_html_sub('plugin_donation_desc/donate_points_to', $user);

		$req =  array_change_key_case($_REQUEST, CASE_LOWER);
		
		if (!isset($req['txid']) || strlen($req['txid']) <= 0) {
			$content['custom'] = qa_lang_html('plugin_donation_desc/payment_error_no_txid');
			return $content; 
		}
		
		$txid = $req['txid'];				
		$rate = qa_donation_point_rate($asset);
		$amount = intval($points) * $rate;

		if (function_exists('wp_is_valid_payment')) { // Query a Waves Pays plugin function
			$stt = wp_is_valid_payment($txid, $asset, $amount);
			if ($stt != 0) {
				$content['custom'] = qa_lang_html_sub('plugin_donation_desc/payment_error_invalid_txid', $stt);
				return $content; 
			}
		}
				
		// Create payment record.
		// Note: Failure to create the record will not stop the other processes
		// because payment had already been verified.
		if (function_exists('wp_create_payment_rec')) { // Query a Waves Pays plugin function
			$purpose = qa_lang_html_sub('plugin_donation_desc/donated_points_to_user', $points);
			$purpose = str_replace('%user', $handle, $purpose);
			wp_create_payment_rec($txid, $asset, $amount, $purpose);
		}
		
		// Add the user's donation points
		donate_points_to_user($handle, $points);
		
		$content['custom'] = '<H1>' . qa_lang_html('plugin_donation_desc/thank_you') . '</H1>';
		
		return $content;
	}
		
	function process_request($request)
	{
		$content = qa_content_prepare();
		
		$req = $this->get_request($request);
		
		if ($req === FALSE || !is_array($req))
			return $content;

		$cnt = count($req);
		 
		if ($cnt <= 0)
			return $content;

		if ($cnt == 1)
			return $this->page_donate_to($content, $req[0]);

		if ($cnt == 2)
			return $this->page_donate_to_for($content, $req[0], $req[1]);

		if ($cnt == 4) {
			if ($req[3] == 'cb')
				return $this->page_donate_to_cb($content, $req[0], $req[1], $req[2]);
		}
		
		return $content;
	}

}

