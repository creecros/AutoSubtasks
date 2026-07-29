<?php

namespace Kanboard\Plugin\AutoSubtasks\Controller;

if (! class_exists('Kanboard\Plugin\AutoSubtasks\Controller\BaseController')) {
    if (class_exists('Kanboard\Core\Controller\BaseController')) {
        class_alias('Kanboard\Core\Controller\BaseController', 'Kanboard\Plugin\AutoSubtasks\Controller\BaseController');
    } elseif (class_exists('Kanboard\Controller\BaseController')) {
        class_alias('Kanboard\Controller\BaseController', 'Kanboard\Plugin\AutoSubtasks\Controller\BaseController');
    }
}

class SubtaskActionController extends BaseController
{
    public function edit()
    {
        try {
        $project = $this->getProject();
        $action_id = $this->request->getIntegerParam('action_id');
        $action = $this->actionModel->getById($action_id);

        if (empty($action) || $action['project_id'] != $project['id']) {
            $this->flash->failure(t('Action not found.'));
            return $this->response->redirect($this->helper->url->to('ActionController', 'index', array('project_id' => $project['id'])));
        }

        $paramsList = $this->db->table('action_has_params')->eq('action_id', $action_id)->findAll();
        $params = array();
        foreach ($paramsList as $p) {
            $params[$p['name']] = $p['value'];
        }

        $values = array(
            'action_id' => $action['id'],
            'project_id' => $project['id'],
            'event_name' => $action['event_name'],
            'action_name' => $action['action_name'],
            'params' => $params,
        );

        foreach ($params as $key => $val) {
            $values[$key] = $val;
            $values['params[' . $key . ']'] = $val;
        }

        $available_actions = $this->actionManager->getAvailableActions();

        $action_name = $action['action_name'];
        $action_object = null;

        if (isset($available_actions[$action_name])) {
            $action_object = $this->actionManager->getAction($action_name);
        } elseif (isset($available_actions['\\' . ltrim($action_name, '\\')])) {
            $action_object = $this->actionManager->getAction('\\' . ltrim($action_name, '\\'));
        } elseif (isset($available_actions[ltrim($action_name, '\\')])) {
            $action_object = $this->actionManager->getAction(ltrim($action_name, '\\'));
        }

        $action_params = $action_object ? $action_object->getActionRequiredParameters() : array();

        $columns_list = $this->columnModel->getList($project['id']);
        
        $users_list = $this->projectUserRoleModel->getAssignableUsersList($project['id']);

        $projects_list = $this->projectUserRoleModel->getActiveProjectsByUser($this->userSession->getId());

        $colors_list = $this->colorModel->getList();
        $categories_list = $this->categoryModel->getList($project['id']);
        $links_list = $this->linkModel->getList();
        $swimlane_list = $this->swimlaneModel->getList($project['id']);

        $priorities_list = array();
        $priority_start = isset($project['priority_start']) ? (int) $project['priority_start'] : 0;
        $priority_end = isset($project['priority_end']) ? (int) $project['priority_end'] : 3;
        for ($i = $priority_start; $i <= $priority_end; $i++) {
            $priorities_list[$i] = $i;
        }

        $groupvalues = array();
        if (isset($this->container['projectGroupRoleModel'])) {
            $groups = $this->projectGroupRoleModel->getGroups($project['id']);
            if (!empty($groups)) {
                $groupnames = array_column($groups, 'name');
                $groupids = array_column($groups, 'id');
                array_unshift($groupnames, t('Unassigned'));
                array_unshift($groupids, 0);
                $groupvalues = array_combine($groupids, $groupnames);
            }
        }

        $events = $this->actionManager->getCompatibleEvents($action_name);

        $this->response->html($this->template->render('autoSubtasks:action_creation/edit', array(
            'project' => $project,
            'action' => $action,
            'action_params' => $action_params,
            'values' => $values,
            'available_actions' => $available_actions,
            'events' => $events,
            'columns_list' => $columns_list,
            'users_list' => $users_list,
            'projects_list' => $projects_list,
            'colors_list' => $colors_list,
            'categories_list' => $categories_list,
            'links_list' => $links_list,
            'priorities_list' => $priorities_list,
            'swimlane_list' => $swimlane_list,
            'groupvalues' => $groupvalues,
        )));
        } catch (\Throwable $e) {
            die('DEBUG: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\n" . $e->getTraceAsString());
        }
    }

    public function update()
    {
        $project = $this->getProject();
        $action_id = $this->request->getIntegerParam('action_id');
        $action = $this->actionModel->getById($action_id);

        if (empty($action) || $action['project_id'] != $project['id']) {
            $this->flash->failure(t('Action not found.'));
            return $this->response->redirect($this->helper->url->to('ActionController', 'index', array('project_id' => $project['id'])));
        }

        $values = $this->request->getValues();

        if (isset($values['event_name']) && !empty($values['event_name'])) {
            $this->db->table('actions')
                ->eq('id', $action_id)
                ->update(array('event_name' => $values['event_name']));
        }

        $available_actions = $this->actionManager->getAvailableActions();

        $action_name = $action['action_name'];
        $action_object = null;

        if (isset($available_actions[$action_name])) {
            $action_object = $this->actionManager->getAction($action_name);
        } elseif (isset($available_actions['\\' . ltrim($action_name, '\\')])) {
            $action_object = $this->actionManager->getAction('\\' . ltrim($action_name, '\\'));
        } elseif (isset($available_actions[ltrim($action_name, '\\')])) {
            $action_object = $this->actionManager->getAction(ltrim($action_name, '\\'));
        }

        $action_params = $action_object ? $action_object->getActionRequiredParameters() : array();
        $submitted_params = isset($values['params']) && is_array($values['params']) ? $values['params'] : array();

        foreach ($action_params as $param_name => $param_desc) {
            if (strpos($param_name, 'check_box') !== false && !isset($submitted_params[$param_name])) {
                $submitted_params[$param_name] = 0;
            }
        }

        $this->db->table('action_has_params')
            ->eq('action_id', $action_id)
            ->remove();

        foreach ($submitted_params as $param_name => $param_value) {
            $this->db->table('action_has_params')->insert(array(
                'action_id' => $action_id,
                'name' => $param_name,
                'value' => $param_value,
            ));
        }

        $this->flash->success(t('Action updated successfully.'));
        return $this->response->redirect($this->helper->url->to('ActionController', 'index', array('project_id' => $project['id'])));
    }
}
