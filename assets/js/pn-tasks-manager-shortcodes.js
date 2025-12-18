(function (window, document) {
  'use strict';

  var ajaxDefaults = {
    url: (window.pn_tasks_manager_ajax && window.pn_tasks_manager_ajax.ajax_url) || window.ajaxurl || '/wp-admin/admin-ajax.php',
    nonce: (window.pn_tasks_manager_ajax && window.pn_tasks_manager_ajax.pn_tasks_manager_ajax_nonce) || '',
  };

  function getErrorMessage() {
    return (window.pn_tasks_manager_i18n && window.pn_tasks_manager_i18n.an_error_has_occurred) || 'Error';
  }

  function alertError() {
    window.alert(getErrorMessage());
  }

  function joinTask(taskId, trigger) {
    var data = new window.FormData();
    data.append('action', 'pn_tasks_manager_ajax');
    data.append('pn_tasks_manager_ajax_type', 'pn_tasks_manager_task_join');
    data.append('pn_tasks_manager_task_id', taskId);
    data.append('pn_tasks_manager_ajax_nonce', ajaxDefaults.nonce);

    trigger.dataset.loading = '1';

    window
      .fetch(ajaxDefaults.url, {
        method: 'POST',
        credentials: 'same-origin',
        body: data,
      })
      .then(function (response) {
        return response.json();
      })
      .then(function (payload) {
        if (!payload || payload.error_key) {
          alertError();
          return;
        }

        var successText = (window.pn_tasks_manager_i18n && window.pn_tasks_manager_i18n.saved_successfully) || 'Joined';
        var span = document.createElement('span');
        span.className = 'pn-tasks-manager-color-green';
        span.textContent = successText;

        trigger.replaceWith(span);
      })
      .catch(alertError)
      .finally(function () {
        trigger.dataset.loading = '0';
      });
  }

  function handleJoinableTasksClick(event) {
    var button = event.target.closest('.pn-tasks-manager-join-task-btn');
    if (!button || button.dataset.loading === '1') {
      return;
    }

    event.preventDefault();
    var taskId = button.getAttribute('data-task-id');
    if (!taskId) {
      return;
    }

    joinTask(taskId, button);
  }

  function initJoinableTasks() {
    document.addEventListener('click', handleJoinableTasksClick);
  }

  function handleRankingItemClick(event) {
    var item = event.target.closest('.pn-tasks-manager-users-ranking-item');
    if (!item) {
      return;
    }

    var userId = item.getAttribute('data-user-id');
    if (!userId) {
      return;
    }

    var data = new window.FormData();
    data.append('action', 'pn_tasks_manager_ajax');
    data.append('pn_tasks_manager_ajax_type', 'pn_tasks_manager_users_ranking_user_tasks');
    data.append('user_id', userId);
    data.append('pn_tasks_manager_ajax_nonce', ajaxDefaults.nonce);

    window
      .fetch(ajaxDefaults.url, {
        method: 'POST',
        credentials: 'same-origin',
        body: data,
      })
      .then(function (response) {
        return response.json();
      })
      .then(function (payload) {
        if (!payload || payload.error_key) {
          alertError();
          return;
        }

        var popupId = 'pn-tasks-manager-users-ranking-popup';
        var popup = document.getElementById(popupId);

        if (!popup) {
          return;
        }

        var content = popup.querySelector('#pn-tasks-manager-users-ranking-popup-content');
        if (content && typeof payload.html === 'string') {
          content.innerHTML = payload.html;
        }

        if (typeof window.PN_TASKS_MANAGER_Popups !== 'undefined' && typeof window.PN_TASKS_MANAGER_Popups.open === 'function') {
          window.PN_TASKS_MANAGER_Popups.open(popupId);
        }
      })
      .catch(alertError);
  }

  function initUsersRanking() {
    document.addEventListener('click', handleRankingItemClick);
  }

  function init() {
    initJoinableTasks();
    initUsersRanking();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})(window, document);

