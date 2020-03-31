<?php

namespace Kanboard\Plugin\AutoSubtasks\Helper;

use Kanboard\Core\Base;

/**
 * Magical parameters Helper
 *
 * @package Kanboard\Plugin\AutoSubtasks\Helper
 * @author  Manfred Hoffmann
 */
class MagicalParamsHelper extends Base
{
    /**
     * Strip off all magical params from subtask-title
     *
     * @param  string $raw_subtask
     * @return string
     */
    public function getCleanSubtaskTitle($raw_subtask)
    {
        // Extract subtask-title by stripping off all "magical" params
        $clean_subtask = preg_replace('~.*?}~', '', $subtask);

        return $clean_subtask;
    }

    /**
     * Extract Magical Params from
     *
     * @param  array
     * @param  array
     * @return
     */
    public function extractMagicalParams()
    {
        // *** Parsing for "magical" parameters ... enabling separate values for each subtask ***
        // Extract subtask-title by ignoring all "magical" parameters
        $subtaskValues['title'] = preg_replace('~.*?}~', '', $subtask);

        // Extracting optional assignee for this subtask ELSE assignee from form will be used
        $magic_user_id_exists = preg_match('/{u:(.*?)}/', $subtask, $magic_user_id);
        $subtaskValues['user_id'] = ($magic_user_id_exists) ? $magic_user_id[1] : $subtaskValues['user_id'];

        // Extracting optional estimated hours for this subtask ELSE estimated hours from form will be used
        $magic_time_exists = preg_match('/{h:(.*?)}/', $subtask, $magic_time);
        $subtaskValues['time_estimated'] = ($magic_time_exists) ? $magic_time[1] : $subtaskValues['time_estimated'];

        // Extracting optional due date for this subtask ELSE due date from form will be used
        $magic_days_exist = preg_match('/{d:(.*?)}/', $subtask, $magic_days);
        $subtaskValues['due_date'] = ($magic_days_exist) ? strtotime('+'.$magic_days[1].'days') : $subtaskValues['due_date'];

        return $magical_subtask;
    }
}
