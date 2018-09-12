<?php

class qa_html_theme_layer extends qa_html_theme_base
{
	public function header()
	{
		if (isset($this->content['navigation']) && isset($this->content['navigation']['sub']) &&
			isset($this->content['navigation']['sub']['admin/general'])) {
			
			// Adding the Donation Point Rates page to the admin sub menu
			$path = 'admin/donation-point-rates';
			$this->content['navigation']['sub'][$path] = array (
				"label" => qa_lang_html('plugin_donation_desc/donation_point_rates'),
				"url" => qa_path($path),
				"selected" => substr(qa_request(), 0, strlen($path)) === $path ? 1 : 0,
			);
		}
		
		parent::header(); // call back through to the default function
	}

	public function main()
	{
		// parent::output(print_r($this->content, true)) is for debugging page main body rendering
		//parent::output('<pre>' . print_r($this->content, true) . '</pre>');
		
		// Adding the donations row to the activity list
		if (isset($this->content['form_activity']) && isset($this->content['form_activity']['fields'])) {
			
			$dpoints = 0;
			$dpi = 0;
			$handle = '';
			
			if (isset($this->content['raw']) && isset($this->content['raw']['userid'])) {
				require_once QA_INCLUDE_DIR . 'qa-db-metas.php';
				$userid = $this->content['raw']['userid'];
				$dpoints = qa_db_usermeta_get($userid, 'donation_points');
				$dpi = intval($dpoints);
				
				if ($dpi > 0)
					$dpoints = qa_format_number($dpoints);
				else
					$dpoints = 0;
			}
					
			if (isset($this->content['raw']) && isset($this->content['raw']['account']) && 
				isset($this->content['raw']['account']['handle']))
				$handle = $this->content['raw']['account']['handle'];
			
			$donations = array(
				'type' => 'static',
				'label' => qa_lang_html('plugin_donation_desc/donations') . ':',
				'value' => '<span class="qa-uf-user-points">' . $dpoints . '</span> ' .
					($dpi > 1 ? qa_lang_html('plugin_donation_desc/points') : qa_lang_html('plugin_donation_desc/point')) .
					(strlen($handle) > 0 ? (' &ndash; ' . render_donate_button($handle)) : ''),
				'id' => 'donations'
			);
			
			array_splice($this->content['form_activity']['fields'], 2, 0, array('donations' => $donations));
		}
		
		parent::main(); // call back through to the default function
	}

	public function post_meta_who($post, $class)
	{
		// Adding the donation button to 'who'
		if (isset($post['who'])) {
			if (isset($post['who']['points'])) {
					
				if (isset($post['raw']) && isset($post['raw']['points'])) {
					$points = intval($post['raw']['points']);
					
					if (isset($post['raw']['userid'])) {
						$userid = $post['raw']['userid'];
						
						if ($userid > 0) {
							require_once QA_INCLUDE_DIR . 'qa-db-metas.php';
							$dpoints = qa_db_usermeta_get($userid, 'donation_points');
							
							if ($dpoints)
								$points += $dpoints;
						}
					}
					
					$post['who']['points']['data'] = qa_format_number($points);
				}

				if (isset($post['raw']) && isset($post['raw']['handle'])) {
					$handle = $post['raw']['handle'];
					$button = render_donate_button($handle);
					$post['who']['points']['suffix'] .= ' &ndash; ' . $button;
				}				
			}
		}
			
		parent::post_meta_who($post, $class); // call back through to the default function
	}
}

