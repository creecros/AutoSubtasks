<?php

namespace Kanboard\Plugin\AutoSubtasks\Helper;

use Kanboard\Core\Base;


class MagicalParamsHelper extends Base
{
    // Parse for "magical" parameters and if present use them instead of the values from the form (thus enabling individual params per subtask)

    public function injectMagicalParams($raw_subtaskValues, $raw_subtask, $project_id)
    {
        $magical_subtaskValues = $raw_subtaskValues;

        // Extract subtask-title by ignoring all "magical" parameters
        $magical_subtaskValues['title'] = preg_replace('/{(.*?)}/', '', $raw_subtask);

        // Extract optional assignee for this subtask ELSE assignee from form will be used
        $magic_user_id_exists = preg_match('/{u:(.*?)}/', $raw_subtask, $magic_user_id);
        $magical_user_id = ($magic_user_id_exists) ? $magic_user_id[1] : $raw_subtaskValues['user_id'];
        // Now let's check if that user isAssignable ELSE switch back to default assignee
        $magical_user_id = ( $this->projectPermissionModel->isAssignable($project_id, $magical_user_id) ) ? $magical_user_id : $raw_subtaskValues['user_id'];
        $magical_subtaskValues['user_id'] = $magical_user_id;

        // Extract optional estimated hours for this subtask ELSE estimated hours from form will be used
        $magic_time_exists = preg_match('/{h:(.*?)}/', $raw_subtask, $magic_time);
        $magical_subtaskValues['time_estimated'] = ($magic_time_exists) ? $magic_time[1] : $raw_subtaskValues['time_estimated'];

        // Extract optional due date for this subtask ELSE due date from form will be used
        $magic_days_exist = preg_match('/{d:(.*?)}/', $raw_subtask, $magic_days);
        $magical_subtaskValues['due_date'] = ($magic_days_exist) ? strtotime('+'.$magic_days[1].'days') : $raw_subtaskValues['due_date'];

        return $magical_subtaskValues;
    }
}
