<?php

namespace Kanboard\Plugin\AutomaticAction\Action;

use Kanboard\Model\TaskModel;
use Kanboard\Action\Base;

class AutoCreateSubtask extends Base
{

    public function getDescription()
    {
        return t('Create a Subtask Automatically');
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
            'title' => t('Subtask Title, leave blank to copy Task Title'),
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
		 $title_test = $this->getParam($data['task']['title'];
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
       return $this->subtaskModel->create($values);
    }

    public function hasRequiredCondition(array $data)
    {
        return $data['task']['column_id'] == $this->getParam('column_id');
    }
}
