<?php

namespace Kanboard\Plugin\AutoSubtasks\Helper;

use Kanboard\Core\Base;

// some helpers for AutoSubtasks

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
        if ( $this->helper->checkCoworkerPlugins->checkSubtaskdate() ) {
            $magic_days_exist = preg_match('/{d:(.*?)}/', $raw_subtask, $magic_days);
            $magical_subtaskValues['due_date'] = ($magic_days_exist) ? strtotime('+'.$magic_days[1].'days') : $raw_subtaskValues['due_date'];
        }

        return $magical_subtaskValues;
    }

    // For comparison we need to temporarily remove magical params to find and remove duplicates
    public function removeDuplicateSubtasks($raw_subtasks, $current_subtasks)
    {
        $clean_subtask_titles = array();
        foreach ($raw_subtasks as $raw_subtask) {
            $clean_subtask_titles[] = preg_replace('/{(.*?)}/', '', $raw_subtask);
        }

        foreach ($current_subtasks as $current_subtask) {
            if (in_array($current_subtask['title'], $clean_subtask_titles)) {
                $remove_id = array_search($current_subtask['title'], $clean_subtask_titles);
                unset($raw_subtasks[$remove_id]);
            }
        }

        return $raw_subtasks;
    }

    // render helptext for multitasktitles-textarea
    public function renderHelpMultitasktitles()
    {
        // feed array for lines of helptext
        $help_lines = array(
            t('Enter one line per task, or leave blank to copy Task Title and create only one subtask.'),
            t('You can use "magical" parameters on each line to set individual values for Assignee, Estimated Hours and Duration in days for any subtask.'),#
            t('--> Hover mouse over INFO-icon for help ...')
        );

        // feed array for lines of helptext in mouseover-tooltip
        $tooltip_lines = array(
            t('HELP for useage of "magical" parameters:'),
            t('- Appending {u:19} to a line will assign that subtask to the user with user-id 19'),
            t('- Appending {h:1.5} to a line will set the estimatedhours for that subtask to 1.5 hours')
        );
        // last helpline in tooltip depends on coexistence of Subtaskdate-plugin or vanilla!
        if ( $this->helper->checkCoworkerPlugins->checkSubtaskdate() ) {
            $tooltip_lines [] = t('- Appending {d:7} to a line will set the duration in days for that subtask to 7 days');
            $tooltip_lines [] = t('-- You can use all combinations of "magical" parameters:');
            $tooltip_lines [] = t('--- None, only the {u:-parameter}, only the {h:-parameter}, only the {d:-parameter}, any 2 of them or all 3!');
        } else {
            $tooltip_lines [] = t('-- You can use all combinations of "magical" parameters:');
            $tooltip_lines [] = t('--- None, only the {u:-parameter}, only the {h:-parameter} or both!');
        }

        // let's compose it ...
        $help_multitasktitles = '';
        foreach($help_lines as $help_line){
            $help_multitasktitles .= $help_line . '<br />';
        }
        // ... now adding the tooltip ...
        $tooltip_linebreak = '&#10'; // tested with current versions of chrome and firefox
        $help_multitasktitles .= '<i class="fa fa-fw fa-info-circle fa-2x aria-hidden="true" title="';
        foreach($tooltip_lines as $tooltip_line){
            $help_multitasktitles .= $tooltip_line . $tooltip_linebreak;
        }
        $help_multitasktitles .= '"></i>';

        return $help_multitasktitles;
    }
}
