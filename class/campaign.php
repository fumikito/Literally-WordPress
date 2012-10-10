<?php
class LWP_Campaign extends Literally_WordPress_Common {
	
	/**
	 * Register hooks
	 */
	public function on_construct() {
		add_action("admin_init", array($this, "update_campaign"));
	}
	
	/**
	 * Get campaign data of specified post
	 * 
	 * @param int $post_id
	 * @param string $time 
	 * @param boolean $multi
	 * @return object|array 
	 */
	public function get_campaign($post_id, $time = false, $multi = false){
		global $wpdb, $lwp;
		$sql = "SELECT * FROM {$lwp->campaign} WHERE book_id = %d";
		if($time)
			$sql .= " AND start <= %s AND end >= %s";
		$sql .= " ORDER BY `end` DESC";
		if($time)
			$sql = $wpdb->prepare($sql, $post_id, $time, $time);
		else
			$sql = $wpdb->prepare($sql, $post_id);
		if($multi)
			return $wpdb->get_results($sql);
		else
			return $wpdb->get_row($sql);
	}
	
	/**
	 * CRUD interface for Campaign
	 * @global wpdb $wpdb 
	 * @global Literally_WordPress $lwp
	 * @return void
	 */
	public function update_campaign(){
		global $wpdb, $lwp;
		if(isset($_REQUEST["_wpnonce"], $_REQUEST['page']) && $_REQUEST['page'] == 'lwp-campaign'){
			//Add campaing
			if(wp_verify_nonce($_REQUEST["_wpnonce"], "lwp_add_campaign")){
				//投稿の確認
				if(!is_numeric($_REQUEST["book_id"])){
					$lwp->error = true;
					$lwp->message[] = $this->_("Please select item.");
				}
				//価格の確認
				if(!is_numeric(mb_convert_kana($_REQUEST["price"], "n"))){
					$lwp->error = true;
					$lwp->message[] = $this->_("Price must be numeric.");
				}
				//価格の確認
				elseif($_REQUEST["price"] > get_post_meta($_REQUEST["book_id"], "lwp_price", true)){
					$lwp->error = true;
					$lwp->message[] = $this->_("Price is higher than original price.");
				}
				//形式の確認
				if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_REQUEST["start"]) || !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_REQUEST["end"])){
					$lwp->error = true;
					$lwp->message[] = $this->_("Date format is invalid.");
				}
				//開始日と終了日の確認
				elseif(strtotime($_REQUEST["end"]) < time() || strtotime($_REQUEST["end"]) < strtotime($_REQUEST["start"])){
					$lwp->error = true;
					$lwp->message[] = $this->_("End date was past.");
				}
				//エラーがなければ登録
				if(!$lwp->error){
					global $wpdb;
					$wpdb->insert(
						$lwp->campaign,
						array(
							"book_id" => $_REQUEST["book_id"],
							"price" => mb_convert_kana($_REQUEST["price"], "n"),
							"start" => $_REQUEST["start"],
							"end" => $_REQUEST["end"]
						),
						array("%d", "%f", "%s", "%s")
					);
					if($wpdb->insert_id)
						$lwp->message[] = $this->_("Campaign added.");
					else{
						$lwp->error = true;
						$lwp->message[] = $this->_("Failed to add campaign.");
					}
				}
			}elseif(wp_verify_nonce($_REQUEST["_wpnonce"], "lwp_update_campaign")){
				//Update Campaign
				//キャンペーンIDの存在を確認
				if(!$wpdb->get_row($wpdb->prepare("SELECT ID FROM {$lwp->campaign} WHERE ID = %d", $_REQUEST["campaign"]))){
					$lwp->error = true;
					$lwp->message[] = $this->_("Specified campaing doesn't exist");
				}
				//価格の確認
				if(!is_numeric(mb_convert_kana($_REQUEST["price"], "n"))){
					$lwp->error = true;
					$lwp->message[] = $this->_("Price should be numeric.");
				}elseif($_REQUEST["price"] > get_post_meta($_REQUEST["book_id"], "lwp_price", true)){
					$lwp->error = true;
					$lwp->message[] = $this->_("Campgin price is higher than original price.");
				}
				//形式の確認
				if(!preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_REQUEST["start"]) || !preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $_REQUEST["end"])){
					$lwp->error = true;
					$lwp->message[] = $this->_("Date format is invalid.");
				}
				//開始日と終了日の確認
				elseif(strtotime($_REQUEST["end"]) < time() || strtotime($_REQUEST["end"]) < strtotime($_REQUEST["start"])){
					$lwp->error = true;
					$lwp->message[] = $this->_("End date is earlier than start date.");
				}
				//エラーがなければ更新
				if(!$lwp->error){
					$req = $wpdb->update(
						$lwp->campaign,
						array(
							"price" => mb_convert_kana($_REQUEST["price"], "n"),
							"start" => $_REQUEST["start"],
							"end" => $_REQUEST["end"]
						),
						array("ID" => $_REQUEST["campaign"]),
						array("%d", "%s", "%s"),
						array("%d")
					);
					if($req)
						$lwp->message[] = $this->_("Successfully Updated.");
					else{
						$lwp->error = true;
						$lwp->message[] = $this->_('Update Failed.');
					}
				}
			}elseif(wp_verify_nonce($_REQUEST["_wpnonce"], "bulk-campaigns") && is_array($_REQUEST["campaigns"])){
				$sql = "DELETE FROM {$lwp->campaign} WHERE ID IN (".implode(",", $_REQUEST["campaigns"]).")";
				if($wpdb->query($sql))
					$lwp->message[] = $this->_("Campaign was deleted.");
				else{
					$lwp->error = true;
					$lwp->message[] = $this->_("Failed to delete campaign.");
				}
			}
		}
	}
}