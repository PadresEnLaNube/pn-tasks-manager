<?php 
/**
 * Provide an archive page for Tasks
 *
 * This file is used to provide an archive page for Task
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

	if (class_exists('Polylang')) {
		$tasks = get_posts(['numberposts' => -1, 'fields' => 'ids', 'post_type' => 'taskspn_task', 'lang' => pll_current_language(), 'post_status' => ['publish'], 'order' => 'DESC', ]);
	} else {
		$tasks = get_posts(['numberposts' => -1, 'fields' => 'ids', 'post_type' => 'taskspn_task', 'post_status' => ['publish'], 'order' => 'DESC', ]);
	}
?>
	<body <?php body_class(); ?>>
		<div class="taskspn-wrapper taskspn-task-wrapper">
		  <h1 class="taskspn-p-20"><?php esc_html_e('Base CPT', 'taskspn'); ?></h1>
			
			<div class="taskspn-display-table taskspn-width-100-percent taskspn-mt-50 taskspn-mb-50">
				<?php if (!empty($tasks)): ?>
			  	<?php foreach ($tasks as $task_id): ?>
						<div class="taskspn-display-inline-table taskspn-width-33-percent taskspn-tablet-display-block taskspn-tablet-width-100-percent taskspn-p-20 taskspn-text-align-center taskspn-vertical-align-top">
							<div class="taskspn-mb-30">
								<a href="<?php echo esc_url(get_permalink($task_id)); ?>">
									<?php if (has_post_thumbnail($task_id)): ?>
								    <?php echo get_the_post_thumbnail($task_id, 'full', ['class' => 'taskspn-border-radius-20 taskspn-width-100-percent']); ?>
								  <?php else: ?>
								  	<img src="<?php echo esc_url(TASKSPN_URL . 'assets/media/taskspn-image.jpg'); ?>" class="taskspn-border-radius-20 taskspn-width-100-percent">
								  <?php endif ?>
								</a>
							</div>

							<a href="<?php echo esc_url(get_permalink($task_id)); ?>"><h4 class="taskspn-color-main-hover taskspn-mb-20"><?php echo esc_html(get_the_title($task_id)); ?></h4></a>

							<?php if (current_user_can('administrator') || current_user_can('taskspn_role_manager')): ?>
				  			<a href="<?php echo esc_url(admin_url('post.php?post=' . $task_id . '&action=edit')); ?>"><i class="material-icons-outlined taskspn-font-size-30 taskspn-vertical-align-middle taskspn-mr-10 taskspn-color-main-0">edit</i> <?php esc_html_e('Edit task', 'taskspn'); ?></a>
				  		<?php endif ?>
						</div>
			  	<?php endforeach ?>
				<?php endif ?>

				<?php if (current_user_can('administrator') || current_user_can('taskspn_role_manager')): ?>
					<div class="taskspn-display-inline-table taskspn-width-33-percent taskspn-tablet-display-block taskspn-tablet-width-100-percent taskspn-p-20 taskspn-text-align-center taskspn-vertical-align-top">
						<div class="taskspn-mb-30">
							<a href="<?php echo esc_url(admin_url('post-new.php?post_type=taskspn_task')); ?>">
								<img src="<?php echo esc_url(TASKSPN_URL . 'assets/media/taskspn-image.jpg'); ?>" class="taskspn-border-radius-20 taskspn-width-100-percent taskspn-filter-grayscale">
							</a>
						</div>

						<a href="<?php echo esc_url(admin_url('post-new.php?post_type=taskspn_task')); ?>"><h4 class="taskspn-color-main-hover taskspn-mb-20"><i class="material-icons-outlined taskspn-vertical-align-middle taskspn-mr-10">add</i> <?php esc_html_e('Add task', 'taskspn'); ?></h4></a>
					</div>
				<?php endif ?>
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