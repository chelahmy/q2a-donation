<?php

function qa_donation_point_rates_opt($data = FALSE)
{
	$optname = 'plugin_donation_point_rates';
	
	if ($data === FALSE)
		return json_decode(qa_opt($optname), true);
	
	if (is_array($data))
		qa_opt($optname, json_encode($data));
}

function qa_donation_point_rate($name, $rate = FALSE)
{
	$rates = qa_donation_point_rates_opt();
	
	if ($rate === FALSE){
		if (is_array($rates) && isset($rates[$name])){
			if (function_exists('qa_waves_asset_dp')) // Query a Waves Pays plugin function
				$dp = qa_waves_asset_dp($name);
			else
				$dp = 8;
		
			return number_format(floatval($rates[$name]), $dp);
		}	
		
		return 0.0;
	}
	else if (strlen($name) > 0) {
		if (!is_array($rates))
			$rates = array();
				
		if (strlen($rate) > 0)
			$rates[$name] = floatval($rate);
		else
			unset($rates[$name]);
		
		qa_donation_point_rates_opt($rates);
	}
}

class qa_donation_point_rates_page {

	function get_request($request)
	{
		$parts = explode('/', $request);
		
		if (count($parts) < 2 || $parts[0] != 'admin' || $parts[1] != 'donation-point-rates')
			return FALSE;
			
		array_splice($parts, 0, 2);
		
		return $parts;
	}
	
	function match_request($request)
	{
		if ($this->get_request($request) === FALSE)
			return FALSE;
		
		return TRUE;
	}

	function page_point_rates($content)
	{
		$content['title'] = qa_lang_html('plugin_donation_desc/donation_point_rates');

		if (qa_clicked('add')) {
			qa_redirect('admin/donation-point-rates/add');
		}
		
		$fields = array();
		
		$rates = qa_donation_point_rates_opt();
		
		if (is_array($rates)) {
			
			ksort($rates);
			
			foreach ($rates as $name => $rate)
			{
				if (function_exists('qa_waves_asset_dp')) // Query a Waves Pays plugin function
					$dp = qa_waves_asset_dp($name);
				else
					$dp = 8;

				$fields['asset_' . $name] = array(
					'type' => 'static',
					'label' => '<a href="' . qa_path('admin/donation-point-rates/edit/' . $name) . '">' . $name . '</a> &ndash; ' . 
						number_format($rate, $dp),
				);
			} 
		}
		
		$content['form'] = array(
			'tags' => 'METHOD="POST" ACTION="' . qa_self_html() . '"',

			'style' => 'tall', // could be 'wide'

			'buttons' => array(
				array(
					'tags' => 'NAME="add"',
					'label' => qa_lang_html('plugin_donation_desc/add_rate'),
				),
			),
		);
		
		if (count($fields) > 0)
			$content['form']['fields'] = $fields;
		
		return $content;
	}

	function page_point_rates_add($content)
	{
		$content['title'] = qa_lang_html('plugin_donation_desc/add_donation_point_rate');

		if (qa_clicked('add')) {
			
			$name = trim(qa_post_text('asset_name'));
			$rate = trim(qa_post_text('rate'));
			qa_donation_point_rate($name, $rate);
			
			qa_redirect('admin/donation-point-rates');
		}
				
		$asset_options = array();
		
		if (function_exists('qa_waves_assets_opt')) { // Query a Waves Pays plugin function
			$assets = qa_waves_assets_opt();
		
			if (is_array($assets)) {
				ksort($assets);
				foreach ($assets as $name => $id) {
					$asset_options[$name] = $name;
				}
			}
		}

		$content['form'] = array(
			'tags' => 'METHOD="POST" ACTION="' . qa_self_html() . '"',

			'style' => 'wide', // could be 'tall'

			'fields' => array(
				'name' => array(
					'label' => qa_lang_html('plugin_donation_desc/asset_name'),
					'type' => 'select',
					'tags' => 'NAME="asset_name" ID="asset_name"',
					'options' => $asset_options, 
					'value' => '',
				),
				'rate' => array(
					'label' => qa_lang_html('plugin_donation_desc/rate_per_point'),
					'type' => 'text',
					'tags' => 'NAME="rate" ID="rates"',
					'value' => '',
				),
			),

			'buttons' => array(
				array(
					'tags' => 'NAME="add"',
					'label' => qa_lang_html('plugin_donation_desc/add'),
				),
			),
		);

		return $content;
	}

	function page_point_rates_edit($content, $asset)
	{
		$content['title'] = qa_lang_html('plugin_donation_desc/edit_donation_point_rate');

		if (qa_clicked('save')) {
			
			$name = trim(qa_post_text('asset_name'));
			$rate = trim(qa_post_text('rate'));
			qa_donation_point_rate($name, $rate);
			
			qa_redirect('admin/donation-point-rates');
		}

		if (qa_clicked('delete')) {
			
			$name = trim(qa_post_text('asset_name'));
			
			if (strlen($name) > 0)
				qa_redirect('admin/donation-point-rates/delete/' . $name);
		}
				
		$asset_options = array();
		
		if (function_exists('qa_waves_assets_opt')) { // Query a Waves Pays plugin function
			$assets = qa_waves_assets_opt();
		
			if (is_array($assets)) {
				ksort($assets);
				foreach ($assets as $name => $id) {
					$asset_options[$name] = $name;
				}
			}
		}

		$content['form'] = array(
			'tags' => 'METHOD="POST" ACTION="' . qa_self_html() . '"',

			'style' => 'wide', // could be 'tall'

			'fields' => array(
				'name' => array(
					'label' => qa_lang_html('plugin_donation_desc/asset_name'),
					'type' => 'select',
					'tags' => 'NAME="asset_name" ID="asset_name"',
					'options' => $asset_options, 
					'value' => $asset,
				),
				'rate' => array(
					'label' => qa_lang_html('plugin_donation_desc/rate_per_point'),
					'type' => 'text',
					'tags' => 'NAME="rate" ID="rates"',
					'value' => qa_donation_point_rate($asset),
				),
			),

			'buttons' => array(
				array(
					'tags' => 'NAME="save"',
					'label' => qa_lang_html('plugin_donation_desc/save'),
				),
				array(
					'tags' => 'NAME="delete"',
					'label' => qa_lang_html('plugin_donation_desc/delete'),
				),
			),
		);

		return $content;
	}
	
	function page_point_rates_delete($content, $asset)
	{
		if (strlen($asset) <= 0)
			qa_redirect('admin/donation-point-rates');
			
		$content['title'] = qa_lang_html('plugin_donation_desc/delete_donation_point_rate');
		
		if (qa_clicked('yes')) {
			
			$name = trim(qa_post_text('asset_name'));
			qa_donation_point_rate($name, '');			
			
			qa_redirect('admin/donation-point-rates');
		}
		
		if (qa_clicked('no')) {
			$name = trim(qa_post_text('asset_name'));
			
			if (strlen($name) > 0)
				qa_redirect('admin/donation-point-rates/edit/' . $name);
			
			qa_redirect('admin/donation-point-rates');				
		}
		
		$content['form'] = array(
			'tags' => 'METHOD="POST" ACTION="' . qa_self_html() . '"',

			'style' => 'tall', // could be 'wide'

			'fields' => array(
				'message' => array(
					'label' => qa_lang_html_sub('plugin_donation_desc/are_you_sure_to_delete_point_rate', '<em>' . $asset . '</em>'),
					'type' => 'static',
				),
				'asset_name' => array(
					'type' => 'hidden',
					'tags' => 'NAME="asset_name" ID="asset_name"',
					'value' => $asset,
				),
			),

			'buttons' => array(
				array(
					'tags' => 'NAME="yes"',
					'label' => qa_lang_html('plugin_donation_desc/yes'),
				),
				array(
					'tags' => 'NAME="no"',
					'label' => qa_lang_html('plugin_donation_desc/no'),
				),
			),
		);

		return $content;
	}

	function process_request($request)
	{
		$content = qa_content_prepare();

		if (!qa_admin_check_privileges($content)) // this page is for admin only
			return $content;
		
		$req = $this->get_request($request);
		
		if ($req === FALSE || !is_array($req))
			return $content;
			
		$content['navigation']['sub'] = qa_admin_sub_navigation();
		$cnt = count($req);
		 
		if ($cnt <= 0)
			return $this->page_point_rates($content);		
		
		if ($req[0] == 'add')
			return $this->page_point_rates_add($content);
		
		if ($req[0] == 'edit')
			return $this->page_point_rates_edit($content, $req[1]);
		
		if ($req[0] == 'delete')
			return $this->page_point_rates_delete($content, $req[1]);

		return $qa_content;
	}	
}
