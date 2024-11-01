<?php
/*
Plugin Name: WEDOS News
Plugin URI: http://wp-blog.cz
Description: Plugin pro zobrazení nejnovějších novinek a informací z neomezeného hostingu WEDOS, přehledně na nástěnce v administraci.
Version: 0.2
Author: Lukenzi
Author URI: http://wp-blog.cz
License: GPLv2 or later
*/
if(!defined('ABSPATH')) die('You do not have sufficient permissions to access this file.');

if(is_admin()):

	class WEDOS_News{

		public $wedos_rss       = array('http://datacentrum.wedos.com/art-rss.php');// URL pro RSS kanál (nemusí být jen jedna)
		public $cache           = 43200;                                        // Jako dlouho držet položky v cache (vteřiny, 12 hodin)
		public $max_gplus_items = 5;                                            // Kolik položek zobrazit ze streamu Google+
		public $max_rss_items   = 5;                                            // Kolik položek zobrazit z RSS kanálu
		public $timeout         = 10;                                           // Max. doba načítání RSS kanálu
		private $cache_file     = '.wedos-news-cache';                          // Název cache souboru
		private $wedos_ico       = 'assets/wedos-mini.png';                     // relativni cesta ze složky pluginu k ikonce widgetu
		private $locale          = '';                                          // Kód jazyka použitý v systému
		private $textdomain      = 'wedosnews';                                 // Textdomain pro překlad nebo jedinečné ID pluginu
		private $version         = '0.2';                                       // Verze pluginu
		private $path            = '';                                          // Absolutní adresa do složky pluginu (/var/www/wp-content/wedos-news/)
		private $url             = '';                                          // URL do složky pluginu (http://example.com/wp-content/wedos-news/)
		private $google_id       = '103221313212683920526';                     // ID Google+ WEDOS streamu
		private $google_api_key  = 'AIzaSyC5EQDOwtd2AqskUGm2yi0EzrC4BcrLzUE';   // Google API key



		/** Inicializace nastavení pluginu **/
		public function InitSettings(){
			$this->url    = trailingslashit(plugin_dir_url(__FILE__));
			$this->path   = trailingslashit(plugin_dir_path(__FILE__));
			$this->locale = get_locale();
			$this->wedos_ico = $this->url.$this->wedos_ico;

			add_filter('plugin_action_links', array(&$this, 'CreateDonateLink'),10,2);
		}// END Init



		/** Překlad pluginu **/
		public function Translate(){
			if(function_exists('load_textdomain')){
				if(!empty($this->locale)){
					$moFile = '';
					$moFile = $this->path.'language/'.$this->locale.'.mo';
					if(@file_exists($moFile) && is_readable($moFile)){
						@load_textdomain($this->textdomain, $moFile);
					}
					unset($moFile);
				}
			}
		}// END Translate



		/** Vložení odkazu na CSS soubor s definicí vzhledu widgetu **/
		public function InsertCss(){
			if($this->IsAdmin()){ // pouze pokud je uživatel admin
				add_action( 'admin_print_styles', array(&$this, 'CreateCssLink') );
			}
		}// END InsertCss



		/** Vložení odkazu na JS soubor do hlavičky administrace **/
		public function InsertJs(){
			if($this->IsAdmin()){
				add_action('admin_init', array(&$this, 'CreateJSLink') );
			}
		}// END InsertJs



		/** Vytvoření odkazu na CSS soubor s definicí vzhledu widgetu **/
		public function CreateCssLink(){
			wp_enqueue_style($this->textdomain, $this->url."assets/wedos-news.css", false, $this->version.'.3', "all");
		}// END CreateCssLink



		/** vytvoření odkazu na JS soubor **/
		public function CreateJsLink(){
			wp_enqueue_script($this->textdomain.'-js', $this->url."assets/wedos-news.js", array(), $this->version.'.3', TRUE);
		}// END CreateJsLink



		/** Vložení Widgetu do administrace **/
		public function InsertWidget(){
			if($this->IsAdmin() && is_network_admin()){
				add_action('wp_network_dashboard_setup', array(&$this, 'CreateWidget'));
			}elseif($this->IsAdmin()){
				add_action('wp_dashboard_setup', array(&$this, 'CreateWidget'));
			}else return;
		}// END InsertWidget



		/** Vytvoření widgetu **/
		public function CreateWidget(){
			wp_add_dashboard_widget($this->textdomain, '<img src="'.$this->wedos_ico.'" class="wedos-icon" title="WEDOS"> '.__('WEDOS News', $this->textdomain), array(&$this, 'ViewWidget'));
		}// END CreateWidget



		/** Zobrazení widgetu **/
		public function ViewWidget(){
			?>
			<noscript><p style="color:red;"><?php _e('To use this plugin you must have JavaScript enabled in your browser!', $this->textdomain);?></p></noscript>
			<div id="wedos-widget">
			<a id="wedos-datacentrum-link" class="wedos-button active" title="<?php _e('RSS feed',$this->textdomain); ?>"><?php _e('RSS',$this->textdomain); ?></a>
			<a id="wedos-google-plus-link" class="wedos-button inactive" title="<?php _e('Google plus stream',$this->textdomain); ?>"><?php _e('Google+',$this->textdomain); ?></a>
			<a id="wedos-traffic-link" class="wedos-button inactive" title="<?php _e('Daily traffic',$this->textdomain); ?>"><?php _e('Traffic',$this->textdomain); ?></a>
			<a id="wedos-admin-link" class="wedos-button inactive right" title="<?php _e('Customer administration',$this->textdomain); ?>"><?php _e('Administration',$this->textdomain); ?></a>
			<a id="wedos-help-link" class="wedos-button inactive right" title="<?php _e('Help & FAQ',$this->textdomain); ?>"><?php _e('Help',$this->textdomain); ?></a>
			<a id="wedos-forum-link" class="wedos-button inactive right" title="<?php _e('Forum',$this->textdomain); ?>"><?php _e('Forum',$this->textdomain); ?></a>
			<a onclick="windowopen('https://client.wedos.com/chat/entry.html?deps=hosting', 800, 710);" id="wedos-chat-link" class="wedos-button inactive right" title="<?php _e('Online chat',$this->textdomain); ?>"><?php _e('Chat',$this->textdomain); ?></a>

			<div id="wedos-rss-datacentrum">
			<?php
			$this->ViewRssFeed();// Zobrazení RSS kanálu datacentra
			?>
			</div>
			<div id="wedos-google-plus">
			<?php
			$this->ViewGooglePlus();// Zobrazení streamu z Google+
			?>
			</div>
			<div id="wedos-traffic">
			<?php
			$this->ViewTraffic();// Zobrazení grafu přenosu
			?>
			</div>
			<div class="wedos-weblinks">
				<a id="wedos-domena" title="<?php _e('Register domain', $this->textdomain);?>"><?php _e('Register domain', $this->textdomain);?></a>
				<a id="wedos-hosting" title="<?php _e('Order Web Hosting', $this->textdomain);?>"><?php _e('Order Web Hosting', $this->textdomain);?></a>
				<a id="wedos-disk" title="<?php _e('Order FTP Disc', $this->textdomain);?>"><?php _e('Order FTP Disc', $this->textdomain);?></a>
			</div>
			</div>
			<?php
		}// END ViewWidget



		/** Zobrazení RSS kanálu Datacentra **/
		public function ViewRssFeed(){
			// Načteme RSS kanál
			$rss = fetch_feed($this->wedos_rss);
			// pokud dojde k problému zobrazíme chybu
			if(is_wp_error($rss)){
				echo '<p class="wedos-error">'.__('An error occurred while retrieving data', $this->textdomain).'</p>';
			// Jinak vytaháme data (viz. SimplePie knihovna pro parsování RSS kanálu)
			}else{
				$maxitems = $rss->get_item_quantity($this->max_rss_items);
				$rss->set_timeout($this->timeout);
				$rss->set_cache_duration($this->cache);
				$rss->init();
				$rss->handle_content_type();
				$items = $rss->get_items(0, $maxitems);
				unset($rss);

				// když máme 0 položek zobrazíme chybu
				if($maxitems == 0){
					echo '<p class="wedos-error">'.__('Did not find any news or notifications.', $this->textdomain).'</p>';
				// Jinak projdem jednotlivé položky a zobrazíme
				}else{
					echo '<ul class="wedos-list">';

					foreach($items as $item){
						echo '<li class="wedos-item">';
						$title = trim(strip_tags($item->get_title()));
						echo '<a href="'.$item->get_permalink().'" title="'.$item->get_date('d.m Y v G:i').'" target="_blank" class="wedos-link">'.$title.'</a>';
						echo '</li>';
						unset($title);
						unset($item);
					}
					unset($items);
					echo '</ul>';
				}
				// O cache se nestaráme, cache si zařizuje sám WP v databázi
				unset($maxitems);
			}
		}// END ViewRssFeed



		/** Zobrazení postů na Google+ **/
		public function ViewGooglePlus($cache = TRUE){
			// pokud se má načíst cache
			if($cache){
				// pokud cache existuje
				if(file_exists(dirname(dirname($this->path)).'/'.$this->cache_file)){
					// pokud je cache starší než 12 hodin
					if((filemtime(dirname(dirname($this->path)).'/'.$this->cache_file) + $this->cache) < time()){
						// nenačítat z cache
						$this->ViewGooglePlus(FALSE);
					// pokud je cache aktuální
					}else{
						// načteme cache
						$cache_stream = @file_get_contents(dirname(dirname($this->path)).'/'.$this->cache_file);
						// když se nepovedlo znovu bez cache
						if($cache_stream === FALSE || empty($cache_stream)){
							$this->ViewGooglePlus(FALSE);
						// když se povedlo zobrazíme cache
						}else{
							echo '<ul class="wedos-list">';
							echo $cache_stream;
							echo '</ul>';
						}
					}
				// pokud cache neexistuje znovu bez cache
				}else{
					$this->ViewGooglePlus(FALSE);
				}
			// pokud se nemá použít cache
			}else{
				// načteme G+
				$stream = @file_get_contents('https://www.googleapis.com/plus/v1/people/'.$this->google_id.'/activities/public?fields=items/title,items/url&key='.$this->google_api_key);
				$stream_obj = '';
				// pokud se nepovede zobrazíme chybu
				if($stream === FALSE){
					echo '<p class="wedos-error">'.__('An error occurred while retrieving data', $this->textdomain).'</p>';
				// pokud se povede
				}else{
					echo '<ul class="wedos-list">';
					ob_start();
					$stream_array = json_decode($stream, TRUE);
					unset($stream);
					// vytaháme potřebné položky z JSONu
					foreach($stream_array as $items => $item){
						if(is_array($item)){
							$count = 1;
							foreach($item as $i){
								if($count > $this->max_gplus_items) continue;
								// Fix kvůli novým řádkům v textu
								$title = trim(strip_tags(str_replace('\n','',str_replace('......','...',$i['title']))));
								echo '<li class="wedos-item"><a class="wedos-link" href="'.trim($i['url']).'" title="'.$title.'">'.$title.'</a></li>';
								$count++;
							}
						}else{ continue; }
					}
					// hodíme do bufferu
					$stream_cache = ob_get_clean();
					// uložíme do nové cache
					@file_put_contents(dirname(dirname($this->path)).'/'.$this->cache_file, $stream_cache);
					// a zobrazíme
					echo $stream_cache;
					echo '</ul>';
				}
			}
		}// END ViewGooglePlus



		/** Zobrazení grafu přenosu IPV4 + IPV6 **/
		public function ViewTraffic(){
			echo '<small>'.__('Daily Graph (5 Minute Average)', $this->textdomain).'. <a id="wedos-traffic-link-info" class="wedos-traffic-link-info">'.__('More graphs...', $this->textdomain).'</a></small>';
			echo '<img src="http://datacentrum.wedos.com/mrtg/sum/day.png" title="'.__('Daily Graph (5 Minute Average)', $this->textdomain).'" class="wedos-traffic-image">';
		}// END ViewTraffic



		/** Kontrola zda je aktuálně přihlášený uživatel administrátor
		 *  (trochu šílená funkce, ale za všech okolností funkční)
		 *
		 * @return bool
		 */
		public function IsAdmin(){
			if(function_exists('wp_get_current_user')){
				if(function_exists('current_user_can')){
					if(current_user_can('activate_plugins')){ return TRUE; }else{ return FALSE; }
				}else{ return FALSE; }
			}else{
				if(file_exists(ABSPATH.'/wp-includes/pluggable.php')){
					include_once ABSPATH.'/wp-includes/pluggable.php';
				}elseif(file_exists(dirname(dirname(dirname($this->path))).'/wp-includes/pluggable.php')){
					include_once dirname(dirname(dirname($this->path))).'/wp-includes/pluggable.php';
				}else{ return FALSE; }

				if(function_exists('current_user_can')){
					if(current_user_can('activate_plugins')){ return TRUE; }else{ return FALSE; }
				}else{ return FALSE; }
			}
		}// END IsAdmin



		/** Vložení odkazu na PayPal v seznamu pluginů **/
		public function CreateDonateLink($links, $file){
			if(plugin_basename(__FILE__) == $file) {
				$settings = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=K9FYKDPJGREHC" title="'.__('Donate', $this->textdomain).'" class="wedos-news-donate-link">'.__('Donate', $this->textdomain).'</a>';
				array_unshift($links, $settings);
			}
			return $links;
		}// END CreateDonateLink



	}// END Class WEDOS_News



	/** Inicializace a spuštění pluginu **/
	$WEDOS_News = new WEDOS_News;
	$WEDOS_News->InitSettings();
	//$WEDOS_News->max_gplus_items = 10;
	//$WEDOS_News->max_rss_items   = 10;
	$WEDOS_News->Translate();
	$WEDOS_News->InsertCss();
	$WEDOS_News->InsertJs();
	$WEDOS_News->InsertWidget();
	unset($WEDOS_News);

endif;
