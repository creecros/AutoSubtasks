<?php

namespace Kanboard\Plugin\AutoSubtasks\Action;

use Kanboard\Model\TaskModel;
use Kanboard\Action\Base;

class CategoryAutoSubtaskVanilla extends Base
{

  public function getDescription()
  {
    return t('Create one or more Subtasks Automatically based on a category');
  }

  public function getCompatibleEvents()
  {

    return array(
      TaskModel::EVENT_CREATE_UPDATE,
    );
  }

  public function getActionRequiredParameters()
  {
    //changed 'titles' to 'multitasktitles' to have a clean way to render the title-textfield as a textarea
    return array(
      'category_id' => t('Category'),
      'user_id' => t('Assignee'),
      'multitasktitles' => t('Subtask Title(s)'),
      'time_estimated' => t('Estimated Time in Hours'),
      'check_box_no_duplicates' => t('Do not duplicate subtasks'),
    );
  }

  public function getEventRequiredParameters()
  {
    return array(
      'task_id',
      'task' => array(
        'project_id',
        'category_id',
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

    foreach ($subtasks as $subtask) {

      if (! empty($subtask)) {
        $subtaskValues = $values;
        $subtaskValues['title'] = $subtask;

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
        return $data['task']['category_id'] == $this->getParam('category_id');
  }
}
