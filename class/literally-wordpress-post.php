<?php


class LWP_Post extends Literally_WordPress_Common{
	
	/**
	 * Payable post types
	 * @var array
	 */
	public $post_types = array();
	
	/**
	 * Post type array
	 * @var array
	 */
	private $custom_post_type = array();
	
	/**
	 * File directory
	 * @var string
	 */
	private $file_directory = '';
	
	/**
	 * Additional mime types to upload
	 * @var type 
	 */
	private $additional_mimes = array(
		"epub" => "application/epub+zip",
		"azw" => "application/octet-stream"
	);
	
	/**
	 * @see Literally_WordPress_Common
	 */
	public function set_option($option) {
		$option = shortcode_atts(array(
			'payable_post_types' => array(),
			'custom_post_type' => array(),
			'dir' => ''
		), $option);
		if(!empty($option['custom_post_type'])){
			if(empty($option['custom_post_type']['singular'])){
				$option['custom_post_type']['singular'] = $option['custom_post_type']['name'];
			}
			if(false === array_search($option['custom_post_type']['slug'], $option['payable_post_types'])){
				array_push($option['payable_post_types'], $option['custom_post_type']['slug']);
			}
		}
		$this->post_types = apply_filters('lwp_payable_post_types', $option['payable_post_types']);
		$this->custom_post_type = $option['custom_post_type'];
		$this->file_directory = $option['dir'];
		$this->enabled = !empty($this->post_types);
	}
	
	/**
	 * @see Literally_WordPress_Common
	 */
	public function on_construct() {
		if(!empty($this->custom_post_type)){
			add_action("init", array($this, "register_post_type"));
		}
		if($this->is_enabled()){
			add_action("save_post", array($this, "save_post"));
			add_action('admin_menu', array($this, 'register_metabox'));
			add_action("admin_init", array($this, "update_devices"));
			add_filter('the_content', array($this, 'the_content'));
			add_filter("media_upload_tabs", array($this, "upload_tab"));
			add_action("media_upload_ebook", array($this, "generate_tab"));
			add_filter("upload_mimes", array($this, "upload_mimes"));
		}
	}
	
	/**
	 * Register custom post type
	 */
	public function register_post_type(){
		if(!empty($this->custom_post_type)){
			$labels = array(
				'name' => $this->custom_post_type['name'],
				'singular_name' => $this->custom_post_type['singular'],
				'add_new' => $this->_('Add New'),
				'add_new_item' => sprintf($this->_('Add New %s'), $this->custom_post_type['singular']),
				'edit_item' => sprintf($this->_("Edit %s"), $this->custom_post_type['name']),
				'new_item' => sprintf($this->_('Add New %s'), $this->custom_post_type['singular']),
				'view_item' => sprintf($this->_('View %s'), $this->custom_post_type['singular']),
				'search_items' => sprintf($this->_("Search %s"), $this->custom_post_type['name']),
				'not_found' =>  sprintf($this->_('No %s was found.'), $this->custom_post_type['singular']),
				'not_found_in_trash' => sprintf($this->_('No %s was found in trash.'), $this->custom_post_type['singular']), 
				'parent_item_colon' => ''
			);
			$args = array(
				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'query_var' => true,
				'rewrite' => true,
				'capability_type' => 'post',
				'hierarchical' => true,
				'menu_position' => 9,
				'has_archive' => true,
				'supports' => array('title','editor','author','thumbnail','excerpt', 'comments', 'custom-fields'),
				'show_in_nav_menus' => true,
				'menu_icon' => $this->url."/assets/book.png"
			);
			register_post_type($this->custom_post_type['slug'], $args);
		}
	}
	
	/**
	 * Register meta box
	 */
	public function register_metabox(){
		//Add metaboxes
		foreach($this->post_types as $post){
			add_meta_box('lwp-detail', $this->_("LWP Post sell Setting"), array($this, 'post_metabox_form'), $post, 'side', 'core');
		}
	}
	
	/**
	 * Add form to post edit screen
	 * @param object $post
	 * @param array $metabox
	 * @return void
	 */
	public function post_metabox_form($post, $metabox){
		require_once $this->dir.DIRECTORY_SEPARATOR."form-template".DIRECTORY_SEPARATOR."edit-detail.php";
		do_action('lwp_payable_post_type_metabox', $post, $metabox);
	}


	/**
	 * Executed when post is saved
	 * @global Literally_WordPress $lwp
	 */
	public function save_post($post_id){
		global $lwp;
		if(isset($_REQUEST["_lwpnonce"]) && wp_verify_nonce($_REQUEST["_lwpnonce"], "lwp_price")){
			//Required. so empty, show error message
			$price = preg_replace("/[^0-9.]/", "", mb_convert_kana($_REQUEST["lwp_price"], "n"));
			if(preg_match("/^[0-9.]+$/", $price)){
				update_post_meta($post_id, $lwp->price_meta_key, $price);
			}else{
				$lwp->message[] = $this->_("Price must be numeric.");
				$lwp->error = true;
			}
		} 
	}

	/**
	 * Output automatic file tables
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param string $content
	 * @return string
	 */
	public function the_content($content){
		global $wpdb, $lwp;
		if(in_the_loop() && false !== array_search(get_post_type(), $this->post_types) && $lwp->needs_auto_layout()){
			$content .= lwp_show_form();
			//if file exists, display file list table.
			if($wpdb->get_var($wpdb->prepare("SELECT COUNT(ID) FROM {$lwp->files} WHERE book_id = %d", get_the_ID()))){
				$content .= lwp_get_device_table().lwp_get_file_list();
			}
		}
		return $content;
	}
	
	/**
	 * Return device information
	 * 
	 * @since 0.3
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param object $file (optional) 指定した場合はファイルに紐づけられた端末を返す
	 * @return array
	 */
	public function get_devices($file = null){
		global $wpdb, $lwp;
		if(is_numeric($file)){
			$file_id = $file;
		}elseif(is_object($file)){
			$file_id = $file->ID;
		}
		if(!is_null($file)){
			$sql = <<<EOS
				SELECT * FROM {$lwp->devices} as d
				LEFT JOIN {$lwp->file_relationships} as f
				ON d.ID = f.device_id
				WHERE f.file_id = %d
EOS;
			$sql = $wpdb->prepare($sql, $file_id);
		}else{
			$sql = "SELECT * FROM {$lwp->devices}";
		}
		return $wpdb->get_results($sql);
	}

	
	/**
	 * CRUD interface for device
	 * @global Literally_WordPress $lwp
	 * @global wpdb $wpdb
	 */
	public function update_devices(){
		global $wpdb, $lwp;
		//Registere form
		if(isset($_REQUEST["_wpnonce"]) && wp_verify_nonce($_REQUEST['_wpnonce'], "lwp_add_device")){
			$req = $wpdb->insert(
				$lwp->devices,
				array(
					"name" => $_REQUEST["device_name"],
					"slug" => $_REQUEST["device_slug"]
				),
				array("%s", "%s")
			);
			if($req)
				$lwp->message[] = $this->_("Device added.");
			else
				$lwp->message[] = $this->_("Failed to add device.");
		}
		//Bulk action
		if(isset($_GET['devices'], $_REQUEST["_wpnonce"]) && wp_verify_nonce($_REQUEST['_wpnonce'], "bulk-devices") && !empty($_GET['devices'])){
			switch($_GET['action']){
				case "delete":
					$ids = implode(',', array_map('intval', $_GET['devices']));
					$wpdb->query("DELETE FROM {$lwp->devices} WHERE ID IN ({$ids})");
					$wpdb->query("DELETE FROM {$lwp->file_relationships} WHERE device_id IN ({$ids})");
					$lwp->message[] = $this->_("Device deleted.");
					break;
			}
		}
		//Update
		if(isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'edit_device')){
			$wpdb->update(
				$lwp->devices,
				array(
					'name' => (string)$_POST['device_name'],
					'slug' => (string)$_POST['device_slug']
				),
				array('ID' => $_POST['device_id']),
				array('%s', '%s'),
				array('%d')
			);
			$lwp->message[] = $this->_('Device updated.');
		}
	}

	/**
	 * Add media uploader tab
	 * @param array $tabs
	 * @return array
	 */
	public function upload_tab($tabs){
		$post_id = isset($_REQUEST['post_id']) ? intval($_REQUEST['post_id']): 0;
		if($this->is_enabled() && $this->is_payable(get_post_type($post_id))){
			$tabs["ebook"] = $this->_('Downloadble Contents');
		}
		return $tabs;
	}

	/**
	 * Generage tab with hooked 
	 */
	public function generate_tab(){
		return wp_iframe(array($this, "media_iframe"));
	}
	
	/**
	 * Output uploader inside iframe.
	 */
	public function media_iframe(){
		media_upload_header();
		require_once $this->dir.DIRECTORY_SEPARATOR."admin".DIRECTORY_SEPARATOR."upload.php";
	}
	/**
	 * Returns list of files
	 * @since 0.3
	 * @global Literally_WordPress $lwp
	 * @param int $book_id (optional)
	 * @param int $file_id (optional)
	 * @return array|object
	 */
	public function get_files($book_id = null, $file_id = null){
		global $wpdb, $lwp;
		if($book_id && $file_id){
			return array();
		}
		$query = "SELECT * FROM {$lwp->files} WHERE";
		if($file_id){
			$query .= " ID = %d";
			return $wpdb->get_row($wpdb->prepare($query, $file_id));
		}else{
			$query .= " book_id = %d";
			return $wpdb->get_results($wpdb->prepare($query, $book_id));
		}
	}
	
	/**
	 * ファイルをアップロードする
	 * 
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param int $book_id
	 * @param string $name
	 * @param string $file
	 * @param string $path
	 * @param array $devices
	 * @param string $desc
	 * @param int $public
	 * @param int $free
	 * @return boolean
	 */
	public function upload_file($book_id, $name, $file, $path, $devices, $desc = "", $public = 1, $free = 0){
		global $wpdb, $lwp;
		//Find directory and create if not exists.
		$book_dir = $this->file_directory.DIRECTORY_SEPARATOR.$book_id;
		if(!is_dir($book_dir)){
			if(!@mkdir($book_dir)){
				return false;
			}
		}
		//Create new file
		$file = sanitize_file_name($file);
		//Move file
		if(!@move_uploaded_file($path, $book_dir.DIRECTORY_SEPARATOR.$file)){
			return false;
		}
		//Write to database
		$id = $wpdb->insert(
			$lwp->files,
			array(
				"book_id" => $book_id,
				"name" => $name,
				"detail" => $desc,
				"file" => $file,
				"public" => $public,
				"free" => $free,
				"registered" => gmdate("Y-m-d H:i:s"),
				"updated" => gmdate("Y-m-d H:i:s")
			),
			array("%d", "%s", "%s", "%s", "%d", "%d", "%s", "%s")
		);
		//Registr device
		$inserted_id = $wpdb->insert_id;
		if($inserted_id && !empty($devices)){
			foreach($devices as $d){
				$wpdb->insert(
					$lwp->file_relationships,
					array(
						"file_id" => $inserted_id,
						"device_id" => $d
					),
					array("%d", "%d")
				);
			}
		}
		return $wpdb->insert_id;
	}
	
	/**
	 * Upadte file table
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param int $file_id
	 * @param string $name
	 * @param array $devices
	 * @param string $desc
	 * @param int $public default 1
	 * @param int $free default 0
	 * @return boolean
	 */
	private function update_file($file_id, $name, $devices, $desc, $public = 1, $free = 0){
		global $wpdb, $lwp;
		$req = $wpdb->update(
			$lwp->files,
			array(
				"name" => $name,
				"detail" => $desc,
				"public" => $public,
				"free" => $free,
				"updated" => gmdate("Y-m-d H:i:s")
			),
			array("ID" => $file_id),
			array("%s", "%s", "%d", "%d", "%s"),
			array("%d")
		);
		if($req){
			//Clear all realtionships
			$wpdb->query($wpdb->prepare("DELETE FROM {$lwp->file_relationships} WHERE file_id = %d", $file_id));
			if(!empty($devices)){
				foreach($devices as $d){
					//Create new realtionships
					$wpdb->insert(
						$lwp->file_relationships,
						array(
							"file_id" => $file_id,
							"device_id" => $d
						),
						array("%d","%d")
					);
				}
			}
			return true;
		}else
			return false;
	}
	
	/**
	 * Delete specified file
	 *
	 * @global wpdb $wpdb
	 * @global Literally_WordPress $lwp
	 * @param int $file_id 
	 * @return boolean
	 */
	private function delete_file($file_id){
		global $wpdb, $lwp;
		$file = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$lwp->files} WHERE ID = %d", $file_id));
		if(!$file){
			return false;
		}else{
			//ファイルを削除する
			if(!unlink($this->file_directory.DIRECTORY_SEPARATOR.$file->book_id.DIRECTORY_SEPARATOR.$file->file))
				return false;
			else{
				if($wpdb->query("DELETE FROM {$lwp->files} WHERE ID = {$file->ID}")){
					$wpdb->query($wpdb->prepare("DELETE FROM {$lwp->file_relationships} WHERE file_id = %d", $file_id));
					return true;
				}else
					return false;
			}
		}
	}
	
	/**
	 * Return error message about uploaded file
	 * @param array $info
	 * @return boolean
	 */
	private function file_has_error($info){
		$message = '';
		switch($info["error"]){
			 case UPLOAD_ERR_INI_SIZE: 
                $message = $this->_("Uploaded file size exceeds the &quot;upload_max_filesize&quot; value defined in php.ini"); 
                break; 
            case UPLOAD_ERR_FORM_SIZE: 
                $message = $this->_("Uploaded file size exceeds"); 
                break; 
            case UPLOAD_ERR_PARTIAL: 
                $message = $this->_("File has been uploaded incompletely. Check your internet connection."); 
                break; 
            case UPLOAD_ERR_NO_FILE: 
                $message = $this->_("No file was uploaded."); 
                break; 
            case UPLOAD_ERR_NO_TMP_DIR: 
                $message = $this->_("No tmp directory exists. Contact to your server administrator."); 
                break; 
            case UPLOAD_ERR_CANT_WRITE: 
                $message = $this->_("Failed to save the uploaded file. Contact to your server administrator.");; 
                break; 
            case UPLOAD_ERR_EXTENSION: 
                $message = $this->_("PHP stops uploading."); 
                break;
			case UPLOAD_ERR_OK:
				$message = false;
				break;
		}
		return $message;
	}
	
	/**
	 * Detect mime types from uploaded file
	 * @param string $file
	 * @return string|false
	 */
	public function detect_mime($file){
		$mime = false;
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		if(array_key_exists($ext, $this->additional_mimes)){
			$mime = $this->additional_mimes[$ext];
		}
		if(!$mime){
			foreach(get_allowed_mime_types() as $e => $m){
				if(false !== strpos($e, $ext)){
					$mime = $m;
					break;
				}
			}
		}
		return $mime;
	}
	
	/**
	 * Add mime types to uploadable contents
	 * @param array $mimes
	 * @return array
	 */
	public function upload_mimes($mimes){
		foreach($this->additional_mimes as $ext => $mime){
			$mimes[$ext] = $mime;
		}
		return apply_filters('lwp_upload_mimes', $mimes);
	}
	
	/**
	 * Return if post type is payable
	 * @param string $post_type
	 * @return boolean
	 */
	public function is_payable($post_type){
		return false !== array_search((string)$post_type, $this->post_types);
	}
}