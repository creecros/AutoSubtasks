<?php

namespace Kanboard\Plugin\AutoSubtasks\Action;

use Kanboard\Model\TaskModel;
use Kanboard\Action\Base;

class AutoCreateSubtask extends Base
{

  public function getDescription()
  {
    return t('Create one or more Subtasks Automatically based on column');
  }

  public function getCompatibleEvents()
  {

    return array(
      TaskModel::EVENT_CREATE,
      TaskModel::EVENT_MOVE_COLUMN,
    );
  }

  public function getActionRequiredParameters()
  {
    //changed 'titles' to 'multitasktitles' to have a clean way to render the title-textfield as a textarea
    return array(
      'column_id' => t('Column'),
      'user_id' => t('Assignee'),
      'multitasktitles' => t('Subtask Title(s)'),
      'time_estimated' => t('Estimated Time in Hours'),
      'duration' => t('Duration in days'),
      'check_box_all_columns' => t('Apply to all Columns'),
      'check_box_no_duplicates' => t('Do not duplicate subtasks'),
    );
  }

  public function getEventRequiredParameters()
  {
    return array(
      'task_id',
      'task' => array(
        'project_id',
        'column_id',
        'title',
      ),
    );
  }

  public function doAction(array $data)
  {
    //get the value of 'multitasktitles' in stead of the original 'titles'
    $title_test = $this->getParam('multitasktitles');
    $title_test = preg_replace("/^\s+/m", $data['task']['title'] . "\r\n", $title_test);

    $values = array(
      'title' => $title_test,
      'task_id' => $data['task_id'],
      'user_id' => $this->getParam('user_id'),
      'time_estimated' => $this->getParam('time_estimated'),
      'time_spent' => 0,
      'status' => 0,
      'due_date' => strtotime('+'.$this->getParam('duration').'days'),
    );

    $subtasks = array_map('trim', explode("\r\n", isset($values['title']) ? $values['title'] : ''));
    $subtasksAdded = 0;

    if ($this->getParam('check_box_no_duplicates') == true ){
      $current_subtasks = $this->subtaskModel->getAll($data['task_id']);
      foreach ($current_subtasks as $current_subtask) {
        if (in_array($current_subtask['title'], $subtasks)) {
          $title = array_search($current_subtask['title'], $subtasks);
          unset($subtasks[$title]);
        }
      }
    }

    foreach ($subtasks as $_raw_subtask) {

      if (! empty($raw_subtask)) {
        $subtaskValues = $values;

        $subtask = $this->helper->magicalParamsHelper->getCleanSubtaskTitle($raw_subtask);

        // Extracting optional assignee for this subtask ELSE assignee from form will be used
        $magic_user_id_exists = preg_match('/{u:(.*?)}/', $subtask, $magic_user_id);
        $subtaskValues['user_id'] = ($magic_user_id_exists) ? $magic_user_id[1] : $subtaskValues['user_id'];

        // Extracting optional estimated hours for this subtask ELSE estimated hours from form will be used
        $magic_time_exists = preg_match('/{h:(.*?)}/', $subtask, $magic_time);
        $subtaskValues['time_estimated'] = ($magic_time_exists) ? $magic_time[1] : $subtaskValues['time_estimated'];

        // Extracting optional due date for this subtask ELSE due date from form will be used
        $magic_days_exist = preg_match('/{d:(.*?)}/', $subtask, $magic_days);
        $subtaskValues['due_date'] = ($magic_days_exist) ? strtotime('+'.$magic_days[1].'days') : $subtaskValues['due_date'];

        list($valid, $errors) = $this->subtaskValidator->validateCreation($subtaskValues);

        if (! $valid) {
          $this->create($values, $errors);
          return false;
        }

        if (! $this->subtaskModel->create($subtaskValues)) {
          $this->flash->failure(t('Unable to create your sub-task.'));
          $this->response->redirect($this->helper->url->to('TaskViewController', 'show', array('project_id' => $task['project_id'], 'task_id' => $task['id']), 'subtasks'), true);
          return false;
        }

        $subtasksAdded++;
      }
    }
    //restore the messaging with a flash but this message doesn't seem to appear in the flash area. Only the create message from (kanboard/app/Controller/ActionCreationController.php).
    if ($subtasksAdded > 0) {
      if ($subtasksAdded === 1) {
        $this->flash->success(t('Subtask added successfully.'));
      } else {
        $this->flash->success(t('%d subtasks added successfully.', $subtasksAdded));
      }
    }
  }

  public function hasRequiredCondition(array $data)
  {

    if ($this->getParam('check_box_all_columns')) {
    return $data['task']['column_id'] == $data['task']['column_id'];
    } else {
    return $data['task']['column_id'] == $this->getParam('column_id');
    }
  }
}
