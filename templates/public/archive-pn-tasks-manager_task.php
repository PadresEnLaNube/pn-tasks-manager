<?php 
/**
 * Provide an archive page for Tasks
 *
 * This file is used to provide an archive page for Task
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

	if (class_exists('Polylang')) {
		$pn_tasks_manager_tasks = get_posts(['numberposts' => -1, 'fields' => 'ids', 'post_type' => 'pn_tasks_manager_task', 'lang' => pll_current_language(), 'post_status' => ['publish'], 'order' => 'DESC', ]);
	} else {
		$pn_tasks_manager_tasks = get_posts(['numberposts' => -1, 'fields' => 'ids', 'post_type' => 'pn_tasks_manager_task', 'post_status' => ['publish'], 'order' => 'DESC', ]);
	}
?>
	<body <?php body_class(); ?>>
		<div class="pn-tasks-manager-wrapper pn-tasks-manager-task-wrapper">
		  <h1 class="pn-tasks-manager-p-20"><?php esc_html_e('Base CPT', 'pn-tasks-manager'); ?></h1>
			
			<div class="pn-tasks-manager-display-table pn-tasks-manager-width-100-percent pn-tasks-manager-mt-50 pn-tasks-manager-mb-50">
				<?php if (!empty($pn_tasks_manager_tasks)): ?>
			  	<?php foreach ($pn_tasks_manager_tasks as $pn_tasks_manager_task_id): ?>
						<div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-33-percent pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent pn-tasks-manager-p-20 pn-tasks-manager-text-align-center pn-tasks-manager-vertical-align-top">
							<div class="pn-tasks-manager-mb-30">
								<a href="<?php echo esc_url(get_permalink($pn_tasks_manager_task_id)); ?>">
									<?php if (has_post_thumbnail($pn_tasks_manager_task_id)): ?>
								    <?php echo get_the_post_thumbnail($pn_tasks_manager_task_id, 'full', ['class' => 'pn-tasks-manager-border-radius-20 pn-tasks-manager-width-100-percent']); ?>
								  <?php else: ?>
								  	<img src="<?php echo esc_url(PN_TASKS_MANAGER_URL . 'assets/media/pn-tasks-manager-image.jpg'); ?>" class="pn-tasks-manager-border-radius-20 pn-tasks-manager-width-100-percent">
								  <?php endif ?>
								</a>
							</div>

							<a href="<?php echo esc_url(get_permalink($pn_tasks_manager_task_id)); ?>"><h4 class="pn-tasks-manager-color-main-hover pn-tasks-manager-mb-20"><?php echo esc_html(get_the_title($pn_tasks_manager_task_id)); ?></h4></a>

							<?php if (current_user_can('administrator') || current_user_can('pn_tasks_manager_role_manager')): ?>
				  			<a href="<?php echo esc_url(admin_url('post.php?post=' . $pn_tasks_manager_task_id . '&action=edit')); ?>"><i class="material-icons-outlined pn-tasks-manager-font-size-30 pn-tasks-manager-vertical-align-middle pn-tasks-manager-mr-10 pn-tasks-manager-color-main-0">edit</i> <?php esc_html_e('Edit task', 'pn-tasks-manager'); ?></a>
				  		<?php endif ?>
						</div>
			  	<?php endforeach ?>
				<?php endif ?>

				<?php if (current_user_can('administrator') || current_user_can('pn_tasks_manager_role_manager')): ?>
					<div class="pn-tasks-manager-display-inline-table pn-tasks-manager-width-33-percent pn-tasks-manager-tablet-display-block pn-tasks-manager-tablet-width-100-percent pn-tasks-manager-p-20 pn-tasks-manager-text-align-center pn-tasks-manager-vertical-align-top">
						<div class="pn-tasks-manager-mb-30">
							<a href="<?php echo esc_url(admin_url('post-new.php?post_type=pn_tasks_manager_task')); ?>">
								<img src="<?php echo esc_url(PN_TASKS_MANAGER_URL . 'assets/media/pn-tasks-manager-image.jpg'); ?>" class="pn-tasks-manager-border-radius-20 pn-tasks-manager-width-100-percent pn-tasks-manager-filter-grayscale">
							</a>
						</div>

						<a href="<?php echo esc_url(admin_url('post-new.php?post_type=pn_tasks_manager_task')); ?>"><h4 class="pn-tasks-manager-color-main-hover pn-tasks-manager-mb-20"><i class="material-icons-outlined pn-tasks-manager-vertical-align-middle pn-tasks-manager-mr-10">add</i> <?php esc_html_e('Add task', 'pn-tasks-manager'); ?></h4></a>
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