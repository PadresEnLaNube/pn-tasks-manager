<?php	
/**
 * Provide a common footer area view for the plugin
 *
 * This file is used to markup the common footer facing aspects of the plugin.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 *
 * @package    TASKSPN
 * @subpackage TASKSPN/common/templates
 */

	if (!defined('ABSPATH')) exit; // Exit if accessed directly

	if(wp_is_block_theme()) {
  	wp_head();
		block_template_part('header');
	} else {
  		get_header();
	}

  $post_id = get_the_ID();

	$ingredients = get_post_meta($post_id, 'taskspn_ingredients_name', true);
	$steps = get_post_meta($post_id, 'taskspn_steps_name', true);
	$steps_description = get_post_meta($post_id, 'taskspn_steps_description', true);
	$steps_time = get_post_meta($post_id, 'taskspn_steps_time', true);
	$steps_total_time = get_post_meta($post_id, 'taskspn_time', true);
	$taskspn_images = explode(',', get_post_meta($post_id, 'taskspn_images', true));
	$suggestions = get_post_meta($post_id, 'taskspn_suggestions', true);
	$steps_count = (!empty($steps) && !empty($steps[0]) && is_array($steps) && count($steps) > 0) ? count($steps) : 0;
	$ingredients_count = (!empty($ingredients) && !empty($ingredients[0]) && is_array($ingredients) && count($ingredients) > 0) ? count($ingredients) : 0;

	function taskspn_minutes($time){
		if ($time) {
			$time = explode(':', $time);
			return ($time[0] * 60) + ($time[1]);
		} else {
			return 0;
		}
	}
?>
	<body <?php body_class(); ?>>
		<div id="taskspn-task-wrapper" class="taskspn-wrapper taskspn-task-wrapper" data-taskspn-ingredients-count="<?php echo intval($ingredients_count); ?>" data-taskspn-steps-count="<?php echo intval($steps_count); ?>">
		  <div class="taskspn-display-table taskspn-width-100-percent">
		  	<div class="taskspn-display-inline-table taskspn-width-50-percent taskspn-tablet-display-block taskspn-tablet-width-100-percent">
		  		<a href="<?php echo esc_url(get_post_type_archive_link('taskspn_task')); ?>"><i class="material-icons-outlined taskspn-font-size-30 taskspn-vertical-align-middle taskspn-mr-10 taskspn-color-main-0">keyboard_arrow_left</i> <?php esc_html_e('More tasks', 'taskspn'); ?></a>
		  	</div>
		  	<div class="taskspn-display-inline-table taskspn-width-50-percent taskspn-tablet-display-block taskspn-tablet-width-100-percent taskspn-text-align-right">
		  		<?php if (current_user_can('administrator') || current_user_can('taskspn_role_manager')): ?>
		  			<a href="<?php echo esc_url(admin_url('post.php?post=' . $post_id . '&action=edit')); ?>"><i class="material-icons-outlined taskspn-font-size-30 taskspn-vertical-align-middle taskspn-mr-10 taskspn-color-main-0">edit</i> <?php esc_html_e('Edit task', 'taskspn'); ?></a>
		  		<?php endif ?>
		  	</div>
		  </div>
			
			<h1 class="taskspn-text-align-center taskspn-mb-50"><?php echo esc_html(get_the_title($post_id)); ?></h1>

			<div class="taskspn-display-block taskspn-width-100-percent taskspn-mb-30">
				<div class="taskspn-display-inline-table taskspn-width-50-percent taskspn-tablet-display-block taskspn-tablet-width-100-percent taskspn-mb-30 taskspn-vertical-align-top">
					<div class="taskspn-image taskspn-p-20 taskspn-mb-30">
						<?php if (has_post_thumbnail($post_id)): ?>
					    <?php echo get_the_post_thumbnail($post_id, 'full', ['class' => 'taskspn-border-radius-20']); ?>
					  <?php else: ?>
							<img src="<?php echo esc_url(TASKSPN_URL . 'assets/media/taskspn-image.jpg'); ?>" class="taskspn-border-radius-20 taskspn-width-100-percent">
					  <?php endif ?>
					</div>

					<?php if (!empty($taskspn_images)): ?>
						<div class="taskspn-carousel taskspn-carousel-main-images">
			        <div class="owl-carousel owl-theme">
			          <?php if (!empty($taskspn_images)): ?>
			          	<?php if (has_post_thumbnail($post_id)): ?>
				          	<div class="taskspn-image taskspn-cursor-grab">
			                <a href="#" data-fancybox="gallery" data-src="<?php echo esc_url(get_the_post_thumbnail_url($post_id, 'full', ['class' => 'taskspn-border-radius-10'])); ?>"><?php echo esc_html(get_the_post_thumbnail($post_id, 'thumbnail', ['class' => 'taskspn-border-radius-10'])); ?></a>  
			              </div>
								  <?php endif ?>

			            <?php foreach ($taskspn_images as $image_id): ?>
		              	<?php if (!empty($image_id)): ?>
			              	<div class="taskspn-image taskspn-cursor-grab">
			                	<a href="#" data-fancybox="gallery" data-src="<?php echo esc_url(wp_get_attachment_image_src($image_id, 'full')[0]); ?>"><?php echo esc_html(wp_get_attachment_image($image_id, 'thumbnail', false, ['class' => 'taskspn-border-radius-10'])); ?></a>  
			              	</div>
		              	<?php endif ?>
			            <?php endforeach ?>
			          <?php endif ?>
			        </div>
			      </div>
					<?php endif ?>
				</div>

				<div class="taskspn-display-inline-table taskspn-width-50-percent taskspn-tablet-display-block taskspn-tablet-width-100-percent taskspn-mb-30 taskspn-vertical-align-top taskspn-mb-30">
					<div class="taskspn-task-content taskspn-p-20">
						<?php echo wp_kses_post(str_replace(']]>', ']]&gt;', apply_filters('the_content', get_post($post_id)->post_content))); ?>
					</div>
				</div>
			</div>

			<div class="taskspn-display-table taskspn-width-100-percent taskspn-mb-50">
				<div class="taskspn-display-inline-table taskspn-width-50-percent taskspn-tablet-display-block taskspn-tablet-width-100-percent taskspn-mb-30 taskspn-vertical-align-top">
					<div class="taskspn-ingredients taskspn-p-20">
						<?php if ($ingredients_count): ?>
							<h2 class="taskspn-mb-30"><?php esc_html_e('Ingredients', 'taskspn'); ?></h2>
							<ul>
								<?php foreach ($ingredients as $ingredient): ?>
									<li class="taskspn-mb-20 taskspn-font-size-20 taskspn-list-style-none">
										<div class="taskspn-display-table taskspn-width-100-percent">
											<div class="taskspn-display-inline-table taskspn-width-90-percent">
												<?php echo esc_html($ingredient); ?>
											</div>
											<div class="taskspn-display-inline-table taskspn-width-10-percent">
												<i class="material-icons-outlined taskspn-ingredient-checkbox taskspn-cursor-pointer taskspn-vertical-align-middle taskspn-font-size-30">radio_button_unchecked</i>
											</div>
										</div>
									</li>
								<?php endforeach ?>
							</ul>
						<?php endif ?>
					</div>
				</div>

				<div class="taskspn-display-inline-table taskspn-width-50-percent taskspn-tablet-display-block taskspn-tablet-width-100-percent taskspn-mb-30 taskspn-vertical-align-top">
					<div class="taskspn-steps taskspn-p-20 taskspn-mb-50">
						<?php if ($steps_count): ?>
							<div class="taskspn-mb-30">
								<div class="taskspn-display-table taskspn-width-100-percent">
									<div class="taskspn-display-inline-table taskspn-width-80-percent">
										<h2><?php esc_html_e('Elaboration steps', 'taskspn'); ?></h2>
									</div>
									<div class="taskspn-display-inline-table taskspn-width-20-percent">
										<a href="#" class="taskspn-popup-player-btn" data-fancybox data-src="#taskspn-popup-player"><i class="material-icons-outlined taskspn-mr-10 taskspn-font-size-50 taskspn-float-right taskspn-vertical-align-middle taskspn-tooltip" title="<?php esc_html_e('Play task', 'taskspn'); ?>">play_circle_outline</i></a>
									</div>
								</div>
										
								<?php if (!empty($steps_total_time)): ?>
									<div class="taskspn-text-align-right">
										<i class="material-icons-outlined taskspn-mr-10 taskspn-font-size-10 taskspn-vertical-align-middle">timer</i> <small><strong><?php esc_html_e('Total time', 'taskspn'); ?></strong> <?php echo esc_html($steps_total_time); ?> (<?php esc_html_e('hours', 'taskspn'); ?>:<?php esc_html_e('minutes', 'taskspn'); ?>)</small>
									</div>
								<?php endif ?>
							</div>

							<ol>
								<?php foreach ($steps as $index => $step): ?>
									<li class="taskspn-mb-50">
										<div class="taskspn-display-table taskspn-width-100-percent">
											<div class="taskspn-display-inline-table taskspn-width-80-percent">
												<?php if (!empty($step)): ?>
													<h4 class="taskspn-mb-10"><?php echo esc_html($step); ?></h4>
												<?php endif ?>
											</div>

											<div class="taskspn-display-inline-table taskspn-width-20-percent">
												<h5 class="taskspn-mb-10"><i class="material-icons-outlined taskspn-mr-10 taskspn-font-size-10 taskspn-vertical-align-middle">timer</i><?php echo !empty($steps_time[$index]) ? esc_html($steps_time[$index]) : '00:00'; ?></h5>
											</div>
										</div>

										<?php if (!empty($steps_description[$index])): ?>
											<p><?php echo esc_html($steps_description[$index]); ?></p>
										<?php endif ?>
									</li>
								<?php endforeach ?>
							</ol>

							<div id="taskspn-popup-player" class="taskspn-display-none-soft">
								<div id="taskspn-popup-steps" class="taskspn-mb-30" data-taskspn-current-step="1">
									<?php foreach ($steps as $index => $step): ?>
										<div class="taskspn-player-step <?php echo $index != 0 ? 'taskspn-display-none-soft' : ''; ?>" data-taskspn-step="<?php echo number_format($index + 1); ?>">
											<div class="taskspn-display-table taskspn-width-100-percent">
												<div class="taskspn-display-inline-table taskspn-width-80-percent taskspn-vertical-align-top">
													<?php if (!empty($step)): ?>
														<h3 class="taskspn-mb-10"><?php echo esc_html($step); ?></h3>
													<?php endif ?>
												</div>
												<div class="taskspn-display-inline-table taskspn-width-20-percent taskspn-vertical-align-top  taskspn-text-align-right">
													<h3>
														<i class="material-icons-outlined taskspn-display-inline taskspn-player-timer-icon taskspn-mr-10 taskspn-font-size-30 taskspn-vertical-align-middle">timer</i> 
														<span class="taskspn-player-timer taskspn-display-inline"><?php echo number_format(taskspn_minutes($steps_time[$index])); ?></span>'
													</h3>
												</div>
											</div>

											<?php if (!empty($steps_description[$index])): ?>
												<div class="taskspn-step-description"><?php echo esc_html($steps_description[$index]); ?></div>
											<?php endif ?>
										</div>
									<?php endforeach ?>
								</div>

								<div class="taskspn-display-table taskspn-width-100-percent">
									<div class="taskspn-display-inline-table taskspn-width-50-percent taskspn-text-align-center taskspn-mb-20">
										<a href="#" class="taskspn-steps-prev taskspn-display-none"><?php esc_html_e('Previous', 'taskspn'); ?></a>
									</div>
									<div class="taskspn-display-inline-table taskspn-width-50-percent taskspn-text-align-center taskspn-mb-20">
										<a href="#" class="taskspn-btn taskspn-btn-mini taskspn-steps-next"><?php esc_html_e('Next', 'taskspn'); ?></a>
									</div>
								</div>
							</div>
						<?php endif ?>
					</div>

					<?php if (!empty($suggestions)): ?>
						<div class="taskspn-suggestions taskspn-mb-50">
							<div class="taskspn-text-align-center taskspn-mb-10"><i class="material-icons-outlined taskspn-font-size-50 taskspn-tooltip" title="<?php esc_html_e('Suggestions', 'taskspn'); ?>">lightbulb</i></div>

							<?php echo wp_kses_post(wp_specialchars_decode($suggestions)); ?>
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