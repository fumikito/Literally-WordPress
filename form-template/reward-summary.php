<?php
/* @var $this Literally_WordPress */
if(isset($_GET['page'])){
	if($_GET['page'] == 'lwp-reward'){
		$script = 'admin.php';
		$user_id = 0;
	}elseif($_GET['page'] == 'lwp-personal-reward'){
		$script = 'profile.php';
		$user_id = get_current_user_id();
	}else{
		wp_die($this->e('You do not have permission to access this page.'));
	}
}else{
	wp_die($this->e('You do not have permission to access this page.'));
}
$from = isset($_GET['from']) ? esc_attr($_GET['from']) : date('Y-m-d', current_time('timestamp') - (60 * 60 * 24 * 30));
$to = isset($_GET['to']) ? esc_attr($_GET['to']) : date('Y-m-d');
//Get Data
$reward_fixed = $this->reward->get_estimated_reward($from, $to, $user_id,  LWP_Payment_Status::SUCCESS);
$reward_start = $this->reward->get_estimated_reward($from, $to, $user_id, LWP_Payment_Status::START);
$reward_total = $this->reward->get_estimated_reward($from, $to, $user_id);
$reward_lost = $reward_total - $reward_fixed - $reward_start;
$promotion = $this->reward->get_top_promotion($from, $to, $user_id, null);
$sold = $this->reward->get_top_promotion($from, $to, $user_id);
$referrer = $this->reward->get_top_referrer($from, $to, $user_id);
?>
<form id="date-changer" method="get" action="<?php echo admin_url($script);  ?>">
	<input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>" />
	<p class="search-box">
		<input type="text" class="date-picker" name="from" value="<?php echo $from; ?>" />
		~
		<input type="text" class="date-picker" name="to" value="<?php echo $to; ?>" />
		<input type="submit" value="<?php $this->e('Refresh'); ?>" class="button" />
	</p>
	<div style="clear:both"></div>
</form>

<div id="lwp-dashboard-amount">
	<h3><?php $this->e('Amount: '); ?><?php echo number_format($reward_total).' '.  lwp_currency_code(); ?></h3>
	<input type="hidden" name="reward_fixed" value="<?php echo $reward_fixed;?>" />
	<input type="hidden" name="reward_start" value="<?php echo $reward_start;?>" />
	<input type="hidden" name="reward_lost" value="<?php echo $reward_lost;?>" />
	<div class="pie-chart"></div>
</div>

<div id="lwp-dashboard-ranking">
	<ul>
		<li><a href="#tabs-promotion"><?php $this->e('All Promotion'); ?></a></li>
		<li><a href="#tabs-sold"><?php $this->e('Fixed'); ?></a></li>
		<li><a href="#tabs-referrer"><?php $this->e('Referrer'); ?></a></li>
		<?php if(!$user_id): ?>
			<li><a href="#tabs-reward"><?php $this->e('Promoter'); ?></a></li>
		<?php endif; ?>
	</ul>
	<div id="tabs-promotion">
		<?php if(empty($promotion)):?>
			<p class="error"><?php $this->e('No Data'); ?></p>
		<?php else: ?>
		<table class="widefat">
			<thead>
				<tr>
					<th scope="col">&nbsp;</th>
					<th scope="col"><?php $this->e('Title'); ?></th>
					<th scope="col"><?php $this->e('Reward'); ?></th>
					<th scope="col"><?php $this->e('Count'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php $counter = 0; foreach($promotion as $pro): $counter++; ?>
					<tr<?php if($counter % 2 == 0) echo ' class="alternate"';?>>
						<th scope="row"><?php echo $counter;?></th>
						<td>
							<a href="<?php echo get_permalink($pro->post_id); ?>">
								<?php echo apply_filters('the_title', $pro->post_title); ?>
							</a>
						</td>
						<td><?php echo number_format($pro->total)." (".lwp_currency_code().')';  ?></td>
						<td><?php echo number_format($pro->num); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>
	<div id="tabs-sold">
		<?php if(empty($sold)):?>
			<p class="error"><?php $this->e('No Data'); ?></p>
		<?php else: ?>
		<table class="widefat">
			<thead>
				<tr>
					<th scope="col">&nbsp;</th>
					<th scope="col"><?php $this->e('Title'); ?></th>
					<th scope="col"><?php $this->e('Reward'); ?></th>
					<th scope="col"><?php $this->e('Count'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php $counter = 0; foreach($sold as $pro): $counter++; ?>
					<tr<?php if($counter % 2 == 0) echo ' class="alternate"';?>>
						<th scope="row"><?php echo $counter;?></th>
						<td>
							<a href="<?php echo get_permalink($pro->post_id); ?>">
								<?php echo apply_filters('the_title', $pro->post_title); ?>
							</a>
						</td>
						<td><?php echo number_format($pro->total)." (".lwp_currency_code().')';  ?></td>
						<td><?php echo number_format($pro->num); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>
	<div id="tabs-referrer">
		<?php if(empty($referrer)): ?>
			<p class="error"><?php $this->e('No Data'); ?></p>
		<?php else: ?>
		<table class="widefat">
			<thead>
				<tr>
					<th scope="col">&nbsp;</th>
					<th scope="col"><?php $this->e('Domain'); ?></th>
					<th scope="col"><?php $this->e('Price'); ?></th>
					<th scope="col"><?php $this->e('Count'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php $counter = 0; foreach($referrer as $ref): $counter++; ?>
					<tr<?php if($counter % 2 == 0) echo ' class="alternate"';?>>
						<th scope="row"><?php echo $counter;?></th>
						<td><?php echo esc_html($ref->domain); ?></td>
						<td><?php echo number_format($ref->total)." (".lwp_currency_code().')';  ?></td>
						<td><?php echo number_format($ref->num); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>
	<?php if(!$user_id): ?>
		<div id="tabs-reward">
			<?php $promoters = $this->reward->get_top_promoter($from, $to); ?>
			<?php if(empty($promoters)): ?>
				<p class="error"><?php $this->e('No Data'); ?></p>
			<?php else: ?>
				<table class="widefat">
					<thead>
						<tr>
							<th scope="col">&nbsp;</th>
							<th scope="col"><?php $this->e('Name'); ?></th>
							<th scope="col"><?php $this->e('Sold'); ?></th>
							<th scope="col"><?php $this->e('Promoted'); ?></th>
							<th scope="col"><?php $this->e('Reward'); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php $counter = 0; foreach($promoters as $ref): $counter++; ?>
							<tr<?php if($counter % 2 == 0) echo ' class="alternate"';?>>
								<th scope="row"><?php echo $counter;?></th>
								<td>
									<?php if($ref->display_name): ?>
										<a href="<?php echo admin_url('user-edit.php?user_id='.$ref->user_id); ?>"><?php echo esc_html($ref->display_name); ?></a>
									<?php else: ?>
										<?php $this->e('Deleted User'); ?>
									<?php endif; ?>
								</td>
								<td><?php echo number_format($ref->sold)." (".lwp_currency_code().')';  ?></td>
								<td><?php echo number_format($ref->promoted)." (".lwp_currency_code().')';  ?></td>
								<td><?php echo number_format($ref->total)." (".lwp_currency_code().')';  ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	<?php endif; ?>
</div>

<div id="lwp-dashboard-daily">
	<form method="get" action="<?php echo admin_url('admin-ajax.php'); ?>">
		<?php wp_nonce_field('lwp_area_chart'); ?>
		<input type="hidden" name="action" value="lwp_area_chart" />
		<input type="hidden" name="from" value="<?php echo $from;?>" />
		<input type="hidden" name="to" value="<?php echo $to;?>" />
		<input type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
	</form>
	<div class="area-chart"></div>
</div>