<?php	
/**
 * Provide a common footer area view for the plugin
 *
 * This file is used to markup the common footer facing aspects of the plugin.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 *
 * @package    PN_TASKS_MANAGER
 * @subpackage pn-tasks-manager/common/templates
 */

	if (!defined('ABSPATH')) exit; // Exit if accessed directly

	if(wp_is_block_theme()) {
  	wp_head();
		block_template_part('header');
	} else {
  		get_header();
	}

  $post_id = get_the_ID();

	$pn_tasks_manager_ingredients = get_post_meta($post_id, 'pn_tasks_manager_ingredients_name', true);
	$pn_tasks_manager_steps = get_post_meta($post_id, 'pn_tasks_manager_steps_name', true);
	$pn_tasks_manager_steps_description = get_post_meta($post_id, 'pn_tasks_manager_steps_description', true);
	$pn_tasks_manager_steps_time = get_post_meta($post_id, 'pn_tasks_manager_steps_time', true);
	$pn_tasks_manager_steps_total_time = get_post_meta($post_id, 'pn_tasks_manager_time', true);
	$pn_tasks_manager_images = explode(',', get_post_meta($post_id, 'pn_tasks_manager_images', true));
	$pn_tasks_manager_suggestions = get_post_meta($post_id, 'pn_tasks_manager_suggestions', true);
	$pn_tasks_manager_steps_count = (!empty($pn_tasks_manager_steps) && !empty($pn_tasks_manager_steps[0]) && is_array($pn_tasks_manager_steps) && count($pn_tasks_manager_steps) > 0) ? count($pn_tasks_manager_steps) : 0;
	$pn_tasks_manager_ingredients_count = (!empty($pn_tasks_manager_ingredients) && !empty($pn_tasks_manager_ingredients[0]) && is_array($pn_tasks_manager_ingredients) && count($pn_tasks_manager_ingredients) > 0) ? count($pn_tasks_manager_ingredients) : 0;

	function pn_tasks_manager_minutes($time){
		if ($time) {
			$time = explode(':', $time);
			return ($time[0] * 60) + ($time[1]);
		} else {
			return 0;
		}
	}
?>
	<body <?php body_class(); ?>>
		<div id="pn-tasks-manager-task-wrapper" class="pn-tasks-manager-wrapper pn-tasks-manager-task-wrapper" data-pn-tasks-manager-ingredients-count="<?php echo intval($pn_tasks_manager_ingredients_count); ?>" data-pn-tasks-manager-steps-count="<?php echo intval($pn_tasks_manager_steps_count); ?>">
		  <div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
		  	<div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-50-percent pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent">
		  		<a href="<?php echo esc_url(get_post_type_archive_link('pn_tasks_manager_task')); ?>"><i class="material-icons-outlined pn-tasks-manager-font-size-30 pn-tasks-manager-vertical-align-middle pn-tasks-manager-mr-10 pn-tasks-manager-color-main-0">keyboard_arrow_left</i> <?php esc_html_e('More tasks', 'pn-tasks-manager'); ?></a>
		  	</div>
		  	<div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-50-percent pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent pn-tasks-manager-text-align-right">
		  		<?php if (current_user_can('administrator') || current_user_can('pn_tasks_manager_role_manager')): ?>
		  			<a href="<?php echo esc_url(admin_url('post.php?post=' . $post_id . '&action=edit')); ?>"><i class="material-icons-outlined pn-tasks-manager-font-size-30 pn-tasks-manager-vertical-align-middle pn-tasks-manager-mr-10 pn-tasks-manager-color-main-0">edit</i> <?php esc_html_e('Edit task', 'pn-tasks-manager'); ?></a>
		  		<?php endif ?>
		  	</div>
		  </div>
			
			<h1 class="pn-tasks-manager-text-align-center pn-tasks-manager-mb-50"><?php echo esc_html(get_the_title($post_id)); ?></h1>

			<div class="pn-tasks-manager-display-block pn-tasks-manager-width-100-percent pn-tasks-manager-mb-30">
				<div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-50-percent pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent pn-tasks-manager-mb-30 pn-tasks-manager-vertical-align-top">
					<div class="pn-tasks-manager-image pn-tasks-manager-p-20 pn-tasks-manager-mb-30">
						<?php if (has_post_thumbnail($post_id)): ?>
					    <?php echo get_the_post_thumbnail($post_id, 'full', ['class' => 'pn-tasks-manager-border-radius-20']); ?>
					  <?php else: ?>
							<img src="<?php echo esc_url(PN_TASKS_MANAGER_URL . 'assets/media/pn-tasks-manager-image.jpg'); ?>" class="pn-tasks-manager-border-radius-20 pn-tasks-manager-width-100-percent">
					  <?php endif ?>
					</div>

					<?php if (!empty($pn_tasks_manager_images)): ?>
						<div class="pn-tasks-manager-carousel pn-tasks-manager-carousel-main-images">
			        <div class="owl-carousel owl-theme">
			          <?php if (!empty($pn_tasks_manager_images)): ?>
			          	<?php if (has_post_thumbnail($post_id)): ?>
				          	<div class="pn-tasks-manager-image pn-tasks-manager-cursor-grab">
			                <a href="#" data-fancybox="gallery" data-src="<?php echo esc_url(get_the_post_thumbnail_url($post_id, 'full', ['class' => 'pn-tasks-manager-border-radius-10'])); ?>"><?php echo esc_html(get_the_post_thumbnail($post_id, 'thumbnail', ['class' => 'pn-tasks-manager-border-radius-10'])); ?></a>  
			              </div>
								  <?php endif ?>

			            <?php foreach ($pn_tasks_manager_images as $pn_tasks_manager_image_id): ?>
		              	<?php if (!empty($pn_tasks_manager_image_id)): ?>
			              	<div class="pn-tasks-manager-image pn-tasks-manager-cursor-grab">
			                	<a href="#" data-fancybox="gallery" data-src="<?php echo esc_url(wp_get_attachment_image_src($pn_tasks_manager_image_id, 'full')[0]); ?>"><?php echo esc_html(wp_get_attachment_image($pn_tasks_manager_image_id, 'thumbnail', false, ['class' => 'pn-tasks-manager-border-radius-10'])); ?></a>  
			              	</div>
		              	<?php endif ?>
			            <?php endforeach ?>
			          <?php endif ?>
			        </div>
			      </div>
					<?php endif ?>
				</div>

				<div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-50-percent pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent pn-tasks-manager-mb-30 pn-tasks-manager-vertical-align-top pn-tasks-manager-mb-30">
					<div class="pn-tasks-manager-task-content pn-tasks-manager-p-20">
						<?php 
						$pn_tasks_manager_the_content_hook = 'the_content';
						echo wp_kses_post(str_replace(']]>', ']]&gt;', apply_filters($pn_tasks_manager_the_content_hook, get_post($post_id)->post_content))); 
						?>
					</div>
				</div>
			</div>

			<div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent pn-tasks-manager-mb-50">
				<div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-50-percent pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent pn-tasks-manager-mb-30 pn-tasks-manager-vertical-align-top">
					<div class="pn-tasks-manager-ingredients pn-tasks-manager-p-20">
						<?php if ($pn_tasks_manager_ingredients_count): ?>
							<h2 class="pn-tasks-manager-mb-30"><?php esc_html_e('Ingredients', 'pn-tasks-manager'); ?></h2>
							<ul>
								<?php foreach ($pn_tasks_manager_ingredients as $pn_tasks_manager_ingredient): ?>
									<li class="pn-tasks-manager-mb-20 pn-tasks-manager-font-size-20 pn-tasks-manager-list-style-none">
										<div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
											<div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-90-percent">
												<?php echo esc_html($pn_tasks_manager_ingredient); ?>
											</div>
											<div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-10-percent">
												<i class="material-icons-outlined pn-tasks-manager-ingredient-checkbox pn-tasks-manager-cursor-pointer pn-tasks-manager-vertical-align-middle pn-tasks-manager-font-size-30">radio_button_unchecked</i>
											</div>
										</div>
									</li>
								<?php endforeach ?>
							</ul>
						<?php endif ?>
					</div>
				</div>

				<div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-50-percent pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent pn-tasks-manager-mb-30 pn-tasks-manager-vertical-align-top">
					<div class="pn-tasks-manager-steps pn-tasks-manager-p-20 pn-tasks-manager-mb-50">
						<?php if ($pn_tasks_manager_steps_count): ?>
							<div class="pn-tasks-manager-mb-30">
								<div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
									<div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-80-percent">
										<h2><?php esc_html_e('Elaboration steps', 'pn-tasks-manager'); ?></h2>
									</div>
									<div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-20-percent">
										<a href="#" class="pn-tasks-manager-popup-player-btn" data-fancybox data-src="#pn-tasks-manager-popup-player"><i class="material-icons-outlined pn-tasks-manager-mr-10 pn-tasks-manager-font-size-50 pn-tasks-manager-float-right pn-tasks-manager-vertical-align-middle pn-tasks-manager-tooltip" title="<?php esc_html_e('Play task', 'pn-tasks-manager'); ?>">play_circle_outline</i></a>
									</div>
								</div>
										
								<?php if (!empty($pn_tasks_manager_steps_total_time)): ?>
									<div class="pn-tasks-manager-text-align-right">
										<i class="material-icons-outlined pn-tasks-manager-mr-10 pn-tasks-manager-font-size-10 pn-tasks-manager-vertical-align-middle">timer</i> <small><strong><?php esc_html_e('Total time', 'pn-tasks-manager'); ?></strong> <?php echo esc_html($pn_tasks_manager_steps_total_time); ?> (<?php esc_html_e('hours', 'pn-tasks-manager'); ?>:<?php esc_html_e('minutes', 'pn-tasks-manager'); ?>)</small>
									</div>
								<?php endif ?>
							</div>

							<ol>
								<?php foreach ($pn_tasks_manager_steps as $pn_tasks_manager_index => $pn_tasks_manager_step): ?>
									<li class="pn-tasks-manager-mb-50">
										<div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
											<div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-80-percent">
												<?php if (!empty($pn_tasks_manager_step)): ?>
													<h4 class="pn-tasks-manager-mb-10"><?php echo esc_html($pn_tasks_manager_step); ?></h4>
												<?php endif ?>
											</div>

											<div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-20-percent">
												<h5 class="pn-tasks-manager-mb-10"><i class="material-icons-outlined pn-tasks-manager-mr-10 pn-tasks-manager-font-size-10 pn-tasks-manager-vertical-align-middle">timer</i><?php echo !empty($pn_tasks_manager_steps_time[$pn_tasks_manager_index]) ? esc_html($pn_tasks_manager_steps_time[$pn_tasks_manager_index]) : '00:00'; ?></h5>
											</div>
										</div>

										<?php if (!empty($pn_tasks_manager_steps_description[$pn_tasks_manager_index])): ?>
											<p><?php echo esc_html($pn_tasks_manager_steps_description[$pn_tasks_manager_index]); ?></p>
										<?php endif ?>
									</li>
								<?php endforeach ?>
							</ol>

							<div id="pn-tasks-manager-popup-player" class="pn-tasks-manager-display-none-soft">
								<div id="pn-tasks-manager-popup-steps" class="pn-tasks-manager-mb-30" data-pn-tasks-manager-current-step="1">
									<?php foreach ($pn_tasks_manager_steps as $pn_tasks_manager_index => $pn_tasks_manager_step): ?>
										<div class="pn-tasks-manager-player-step <?php echo $pn_tasks_manager_index != 0 ? 'pn-tasks-manager-display-none-soft' : ''; ?>" data-pn-tasks-manager-step="<?php echo number_format($pn_tasks_manager_index + 1); ?>">
											<div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
												<div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-80-percent pn-tasks-manager-vertical-align-top">
													<?php if (!empty($pn_tasks_manager_step)): ?>
														<h3 class="pn-tasks-manager-mb-10"><?php echo esc_html($pn_tasks_manager_step); ?></h3>
													<?php endif ?>
												</div>
												<div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-20-percent pn-tasks-manager-vertical-align-top  pn-tasks-manager-text-align-right">
													<h3>
														<i class="material-icons-outlined pn-tasks-manager-display-inline pn-tasks-manager-player-timer-icon pn-tasks-manager-mr-10 pn-tasks-manager-font-size-30 pn-tasks-manager-vertical-align-middle">timer</i> 
														<span class="pn-tasks-manager-player-timer pn-tasks-manager-display-inline"><?php echo number_format(pn_tasks_manager_minutes($pn_tasks_manager_steps_time[$pn_tasks_manager_index])); ?></span>'
													</h3>
												</div>
											</div>

											<?php if (!empty($pn_tasks_manager_steps_description[$pn_tasks_manager_index])): ?>
												<div class="pn-tasks-manager-step-description"><?php echo esc_html($pn_tasks_manager_steps_description[$pn_tasks_manager_index]); ?></div>
											<?php endif ?>
										</div>
									<?php endforeach ?>
								</div>

								<div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent">
									<div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-50-percent pn-tasks-manager-text-align-center pn-tasks-manager-mb-20">
										<a href="#" class="pn-tasks-manager-steps-prev pn-tasks-manager-display-none"><?php esc_html_e('Previous', 'pn-tasks-manager'); ?></a>
									</div>
									<div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-50-percent pn-tasks-manager-text-align-center pn-tasks-manager-mb-20">
										<a href="#" class="pn-tasks-manager-btn pn-tasks-manager-btn-mini pn-tasks-manager-steps-next"><?php esc_html_e('Next', 'pn-tasks-manager'); ?></a>
									</div>
								</div>
							</div>
						<?php endif ?>
					</div>

					<?php if (!empty($pn_tasks_manager_suggestions)): ?>
						<div class="pn-tasks-manager-suggestions pn-tasks-manager-mb-50">
							<div class="pn-tasks-manager-text-align-center pn-tasks-manager-mb-10"><i class="material-icons-outlined pn-tasks-manager-font-size-50 pn-tasks-manager-tooltip" title="<?php esc_html_e('Suggestions', 'pn-tasks-manager'); ?>">lightbulb</i></div>

							<?php echo wp_kses_post(wp_specialchars_decode($pn_tasks_manager_suggestions)); ?>
						</div>
					<?php endif ?>
				</div>
			</div>
		</div>
	</body>
<?php 
	if(wp_is_block_theme()) {
  	wp_footer();
		block_template_part('footer');
	} else {
  	get_footer();
	}
?>