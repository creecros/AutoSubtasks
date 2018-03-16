<?php

namespace Kanboard\Plugin\AutoSubtasks\Action;

use Kanboard\Model\TaskModel;
use Kanboard\Action\Base;

class AutoCreateSubtask extends Base
{

    public function getDescription()
    {
        return t('Create Subtasks Automatically');
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
        return array(
            'column_id' => t('Column'),
            'user_id' => t('Assignee'),
            'title' => t('Subtask Title(s), leave blank to copy Task Title'),
	    'time_estimated' => t('Estimated Time in Hours'),                                                                  
            'duration' => t('Duration in days'), 
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
	 $title_test = $this->getParam('title');
	 
	 if (empty ($title_test)) {
		 $title_test = $data['task']['title'];
	  }
	    
	 $values = array(
            'title' => $title_test,
            'task_id' => $data['task_id'],
            'user_id' => $this->getParam('user_id'),
            'time_estimated' => $this->getParam('time_estimated'),
            'time_spent' => 0,
            'status' => 0,
            'due_date' => strtotime('+'.$this->getParam('duration').'days'),                                          
        );
	    
// START NEW CODE COPIED FROM /app/Controller/SubtaskControlle.php line 70 - 95
        $subtasks = explode("\r\n", isset($values['title']) ? $values['title'] : '');
        $subtasksAdded = 0;

        foreach ($subtasks as $subtask) {
            $subtask = trim($subtask);

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
//END NEW CODE 
//COMMENT OUT YOUR RETURN

     //  return $this->subtaskModel->create($values);
    }

    public function hasRequiredCondition(array $data)
    {
        return $data['task']['column_id'] == $this->getParam('column_id');
    }
}
