(function (window, document) {
  'use strict';

  var ajaxDefaults = {
    url: (window.taskspn_ajax && window.taskspn_ajax.ajax_url) || window.ajaxurl || '/wp-admin/admin-ajax.php',
    nonce: (window.taskspn_ajax && window.taskspn_ajax.taskspn_ajax_nonce) || '',
  };

  function getErrorMessage() {
    return (window.taskspn_i18n && window.taskspn_i18n.an_error_has_occurred) || 'Error';
  }

  function alertError() {
    window.alert(getErrorMessage());
  }

  function joinTask(taskId, trigger) {
    var data = new window.FormData();
    data.append('action', 'taskspn_ajax');
    data.append('taskspn_ajax_type', 'taskspn_task_join');
    data.append('taskspn_task_id', taskId);
    data.append('taskspn_ajax_nonce', ajaxDefaults.nonce);

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

        var successText = (window.taskspn_i18n && window.taskspn_i18n.saved_successfully) || 'Joined';
        var span = document.createElement('span');
        span.className = 'taskspn-color-green';
        span.textContent = successText;

        trigger.replaceWith(span);
      })
      .catch(alertError)
      .finally(function () {
        trigger.dataset.loading = '0';
      });
  }

  function handleJoinableTasksClick(event) {
    var button = event.target.closest('.taskspn-join-task-btn');
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
    var item = event.target.closest('.taskspn-users-ranking-item');
    if (!item) {
      return;
    }

    var userId = item.getAttribute('data-user-id');
    if (!userId) {
      return;
    }

    var data = new window.FormData();
    data.append('action', 'taskspn_ajax');
    data.append('taskspn_ajax_type', 'taskspn_users_ranking_user_tasks');
    data.append('user_id', userId);
    data.append('taskspn_ajax_nonce', ajaxDefaults.nonce);

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

        var popupId = 'taskspn-users-ranking-popup';
        var popup = document.getElementById(popupId);

        if (!popup) {
          return;
        }

        var content = popup.querySelector('#taskspn-users-ranking-popup-content');
        if (content && typeof payload.html === 'string') {
          content.innerHTML = payload.html;
        }

        if (typeof window.TASKSPN_Popups !== 'undefined' && typeof window.TASKSPN_Popups.open === 'function') {
          window.TASKSPN_Popups.open(popupId);
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

