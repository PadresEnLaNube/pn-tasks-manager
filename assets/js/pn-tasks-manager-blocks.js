( function( blocks, i18n, element ) {
  var el = element.createElement;

  function ctaPreview( icon, title, description ) {
    return el( 'div', { className: 'pn-tasks-manager-call-to-action pn-tasks-manager-text-align-center pn-tasks-manager-pt-30 pn-tasks-manager-pb-50' },
      el( 'div', { className: 'pn-tasks-manager-call-to-action-icon' },
        el( 'i', { className: 'material-icons-outlined pn-tasks-manager-font-size-75 pn-tasks-manager-color-main-0' }, icon || 'info' )
      ),
      el( 'h4', { className: 'pn-tasks-manager-call-to-action-title pn-tasks-manager-text-align-center pn-tasks-manager-mt-10 pn-tasks-manager-mb-20' }, title ),
      description ? el( 'p', { className: 'pn-tasks-manager-text-align-center' }, description ) : null
    );
  }

  var defs = [
    { name: 'pn-tasks-manager/joinable-tasks', icon: 'group_add', title: i18n.__( 'Joinable Tasks (PN Tasks Manager)', 'pn-tasks-manager' ), desc: i18n.__( 'Displays a list of public tasks users can join.', 'pn-tasks-manager' ) },
    { name: 'pn-tasks-manager/users-ranking', icon: 'leaderboard', title: i18n.__( 'Users Ranking (PN Tasks Manager)', 'pn-tasks-manager' ), desc: i18n.__( 'Admin-only ranking by completed task hours.', 'pn-tasks-manager' ) },
    { name: 'pn-tasks-manager/calendar', icon: 'calendar_month', title: i18n.__( 'Calendar (PN Tasks Manager)', 'pn-tasks-manager' ), desc: i18n.__( 'Renders the PN Tasks Manager calendar.', 'pn-tasks-manager' ) },
    { name: 'pn-tasks-manager/task', icon: 'assignment', title: i18n.__( 'Task (PN Tasks Manager)', 'pn-tasks-manager' ), desc: i18n.__( 'Displays a single task contextually.', 'pn-tasks-manager' ) },
    { name: 'pn-tasks-manager/task-list', icon: 'checklist', title: i18n.__( 'Task List (PN Tasks Manager)', 'pn-tasks-manager' ), desc: i18n.__( 'Displays the task list.', 'pn-tasks-manager' ) },
  ];

  defs.forEach( function( def ) {
    if ( ! blocks.getBlockType( def.name ) ) {
      blocks.registerBlockType( def.name, {
        apiVersion: 2,
        title: def.title,
        category: 'pn-tasks-manager',
        icon: 'excerpt-view',
        edit: function(){ return ctaPreview( def.icon, def.title, def.desc ); },
        save: function(){ return null; },
      } );
    }
  } );

} )( window.wp.blocks, window.wp.i18n, window.wp.element );


