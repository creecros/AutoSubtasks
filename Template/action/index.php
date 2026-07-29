<div class="page-header">
    <h2><?= t('Automatic actions for this project') ?></h2>
    <ul>
        <li>
            <?= $this->url->icon('plus', t('Add a new action'), 'ActionCreationController', 'create', array('project_id' => $project['id']), false, 'js-modal-medium') ?>
        </li>
    </ul>
</div>

<?php if (empty($actions)): ?>
    <p class="alert"><?= t('There is no action for this project.') ?></p>
<?php else: ?>
    <table class="table-striped table-scrolling">
        <tr>
            <th><?= t('Action') ?></th>
            <th><?= t('Event name') ?></th>
            <th><?= t('Parameters') ?></th>
            <th><?= t('Action') ?></th>
        </tr>
        <?php foreach ($actions as $action): ?>
            <tr>
                <td>
                    <strong><?= $this->text->e(isset($available_actions[$action['action_name']]) ? $available_actions[$action['action_name']] : $action['action_name']) ?></strong>
                </td>
                <td>
                    <?= $this->text->e(isset($events[$action['event_name']]) ? $events[$action['event_name']] : $action['event_name']) ?>
                </td>
                <td>
                    <ul>
                        <?php foreach ($action['params'] as $param_name => $param_value): ?>
                            <li>
                                <strong><?= $this->text->e(isset($available_params[$action['action_name']][$param_name]) ? $available_params[$action['action_name']][$param_name] : $param_name) ?>:</strong>
                                <span>
                                    <?php if ($this->text->contains($param_name, 'multitasktitles')): ?>
                                        <br><?= nl2br($this->text->e($param_value)) ?>
                                    <?php else: ?>
                                        <?= $this->text->e($param_value) ?>
                                    <?php endif ?>
                                </span>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </td>
                <td>
                    <div class="dropdown">
                        <a href="#" class="dropdown-menu dropdown-menu-link-icon"><i class="fa fa-cog"></i><i class="fa fa-caret-down"></i></a>
                        <ul>
                            <li>
                                <?= $this->url->icon('edit', t('Edit'), 'SubtaskActionController', 'edit', array('plugin' => 'AutoSubtasks', 'project_id' => $project['id'], 'action_id' => $action['id']), false, 'js-modal-medium') ?>
                            </li>
                            <li>
                                <?= $this->url->icon('trash-o', t('Remove'), 'ActionController', 'confirm', array('project_id' => $project['id'], 'action_id' => $action['id']), false, 'js-modal-medium') ?>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        <?php endforeach ?>
    </table>
<?php endif ?>
