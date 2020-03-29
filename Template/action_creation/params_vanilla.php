<div class="page-header">
    <h2><?= t('Define action parameters') ?></h2>
</div>

<form method="post" action="<?= $this->url->href('ActionCreationController', 'save', array('project_id' => $project['id'])) ?>" autocomplete="off">
    <?= $this->form->csrf() ?>

    <?= $this->form->hidden('event_name', $values) ?>
    <?= $this->form->hidden('action_name', $values) ?>

    <?= $this->form->label(t('Action'), 'action_name') ?>
    <?= $this->form->select('action_name', $available_actions, $values, array(), array('disabled')) ?>

    <?= $this->form->label(t('Event'), 'event_name') ?>
    <?= $this->form->select('event_name', $events, $values, array(), array('disabled')) ?>

    <?php foreach ($action_params as $param_name => $param_desc): ?>
        <?php if ($this->text->contains($param_name, 'column_id')): ?>
            <?= $this->form->label($param_desc, $param_name) ?>
            <?= $this->form->select('params['.$param_name.']', $columns_list, $values) ?>
        <?php elseif ($this->text->contains($param_name, 'user_id')): ?>
            <?= $this->form->label($param_desc, $param_name) ?>
            <?= $this->form->select('params['.$param_name.']', $users_list, $values) ?>
        <?php elseif ($this->text->contains($param_name, 'group_id')): ?>
           <?php $groups = $this->model->projectGroupRoleModel->getGroups($values['project_id']); ?>
           <?php $groupnames = array_column($groups, 'name'); ?>
           <?php $groupids = array_column($groups, 'id'); ?>
           <?php array_unshift($groupnames, t('Unassigned')); ?>
           <?php array_unshift($groupids, 0); ?>
           <?php $groupvalues = array_combine($groupids, $groupnames); ?>
            <?= $this->form->label($param_desc, $param_name) ?>
            <?= $this->form->select('params['.$param_name.']', $groupvalues, $values) ?>
        <?php elseif ($this->text->contains($param_name, 'check_box')): ?>
            <?= $this->form->label(t('Options'), $param_name) ?>
            <?= $this->form->checkbox('params['.$param_name.']', $param_desc, 1) ?>
        <?php elseif ($this->text->contains($param_name, 'project_id')): ?>
            <?= $this->form->label($param_desc, $param_name) ?>
            <?= $this->form->select('params['.$param_name.']', $projects_list, $values) ?>
        <?php elseif ($this->text->contains($param_name, 'color_id')): ?>
            <?= $this->form->label($param_desc, $param_name) ?>
            <?= $this->form->select('params['.$param_name.']', $colors_list, $values) ?>
        <?php elseif ($this->text->contains($param_name, 'category_id')): ?>
            <?= $this->form->label($param_desc, $param_name) ?>
            <?= $this->form->select('params['.$param_name.']', $categories_list, $values) ?>
        <?php elseif ($this->text->contains($param_name, 'link_id')): ?>
            <?= $this->form->label($param_desc, $param_name) ?>
            <?= $this->form->select('params['.$param_name.']', $links_list, $values) ?>
        <?php elseif ($param_name === 'priority'): ?>
            <?= $this->form->label($param_desc, $param_name) ?>
            <?= $this->form->select('params['.$param_name.']', $priorities_list, $values) ?>
        <?php elseif ($this->text->contains($param_name, 'duration')): ?>
            <?= $this->form->label($param_desc, $param_name) ?>
            <?= $this->form->number('params['.$param_name.']', $values) ?>
        <?php elseif ($this->text->contains($param_name, 'swimlane_id')): ?>
            <?= $this->form->label($param_desc, $param_name) ?>
            <?= $this->form->select('params['.$param_name.']', $swimlane_list, $values) ?>
        <?php elseif (is_array($param_desc)): ?>
            <?= $this->form->label(ucfirst($param_name), $param_name) ?>
            <?= $this->form->select('params['.$param_name.']', $param_desc, $values) ?>
        <?php elseif ($this->text->contains($param_name, 'multitasktitles')): ?>
            <?= $this->form->label($param_desc, $param_name) ?>
            <?= $this->form->textarea('params['.$param_name.']', $values) ?>
            <div class="form-help">
                <?= t('Enter one line per task, or leave blank to copy Task Title and create only one subtask.') ?><br />
                <?= t('You can use "magical" parameters on each line to set individual values for Assignee and Estimated Hours for any subtask.') ?><br />
                <?= t('--> Hover mouse over INFO-icon for help ...') ?><br />
                <?php
                    $help_tooltip = t('HELP for useage of "magical" parameters:') . '&#10';
                    $help_tooltip .= t('- Prepending a line with {u:19} will assign that subtask to the user with user-id 19') . '&#10';
                    $help_tooltip .= t('- Prepending a line with {h:1.5} will set the estimatedhours for that subtask 1.5 hours') . '&#10';
                    $help_tooltip .= t('-- You can use all combinations of "magical" parameters:') . '&#10';
                    $help_tooltip .= t('--- None, only the {u:-parameter}, only the {h:-parameter} or both!');
                ?>
                <i class="fa fa-fw fa-info-circle fa-2x aria-hidden="true" title="<?= $help_tooltip; ?>"></i>
            </div>
        <?php else: ?>
            <?= $this->form->label($param_desc, $param_name) ?>
            <?= $this->form->text('params['.$param_name.']', $values) ?>
        <?php endif ?>
    <?php endforeach ?>

    <?= $this->modal->submitButtons() ?>
</form>
