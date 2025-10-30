( function( blocks, i18n, element ) {
  var el = element.createElement;

  function ctaPreview( icon, title, description ) {
    return el( 'div', { className: 'taskspn-call-to-action taskspn-text-align-center taskspn-pt-30 taskspn-pb-50' },
      el( 'div', { className: 'taskspn-call-to-action-icon' },
        el( 'i', { className: 'material-icons-outlined taskspn-font-size-75 taskspn-color-main-0' }, icon || 'info' )
      ),
      el( 'h4', { className: 'taskspn-call-to-action-title taskspn-text-align-center taskspn-mt-10 taskspn-mb-20' }, title ),
      description ? el( 'p', { className: 'taskspn-text-align-center' }, description ) : null
    );
  }

  var defs = [
    { name: 'taskspn/joinable-tasks', icon: 'group_add', title: i18n.__( 'Joinable Tasks (Taskspn)', 'taskspn' ), desc: i18n.__( 'Displays a list of public tasks users can join.', 'taskspn' ) },
    { name: 'taskspn/users-ranking', icon: 'leaderboard', title: i18n.__( 'Users Ranking (Taskspn)', 'taskspn' ), desc: i18n.__( 'Admin-only ranking by completed task hours.', 'taskspn' ) },
    { name: 'taskspn/calendar', icon: 'calendar_month', title: i18n.__( 'Calendar (Taskspn)', 'taskspn' ), desc: i18n.__( 'Renders the Taskspn calendar.', 'taskspn' ) },
    { name: 'taskspn/task', icon: 'assignment', title: i18n.__( 'Task (Taskspn)', 'taskspn' ), desc: i18n.__( 'Displays a single task contextually.', 'taskspn' ) },
    { name: 'taskspn/task-list', icon: 'checklist', title: i18n.__( 'Task List (Taskspn)', 'taskspn' ), desc: i18n.__( 'Displays the task list.', 'taskspn' ) },
  ];

  defs.forEach( function( def ) {
    if ( ! blocks.getBlockType( def.name ) ) {
      blocks.registerBlockType( def.name, {
        apiVersion: 2,
        title: def.title,
        category: 'taskspn',
        icon: 'excerpt-view',
        edit: function(){ return ctaPreview( def.icon, def.title, def.desc ); },
        save: function(){ return null; },
      } );
    }
  } );

} )( window.wp.blocks, window.wp.i18n, window.wp.element );


