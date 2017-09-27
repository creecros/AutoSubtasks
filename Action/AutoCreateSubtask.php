
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
	    TaskModel::EVENT_CREATE_UPDATE,
            TaskModel::EVENT_MOVE_COLUMN,
        );
    }

    public function getActionRequiredParameters()
    {
        return array(
            'column_id' => t('Column'),
            'user_id' => t('Assignee'),
            'title' => t('SubTitle'),
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
            ),
        );
    }

    public function doAction(array $data)
    {
	 $values = array(
            'title' => $this->getParam('title'),
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
