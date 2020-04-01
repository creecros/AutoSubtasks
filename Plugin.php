<?php

namespace Kanboard\Plugin\AutoSubtasks;

use Kanboard\Core\Plugin\Base;
use Kanboard\Plugin\AutoSubtasks\Action\AutoCreateSubtask;
use Kanboard\Plugin\AutoSubtasks\Action\CategoryAutoSubtask;

class Plugin extends Base

{
  public function initialize()

  {
    // Helpers
    $this->helper->register('checkCoworkerPlugins', '\Kanboard\Plugin\AutoSubtasks\Helper\CheckCoworkerPluginsHelper');
    $this->helper->register('magicalParams', '\Kanboard\Plugin\AutoSubtasks\Helper\MagicalParamsHelper');

    // Templates
    $this->template->setTemplateOverride('action_creation/params', 'autoSubtasks:action_creation/params');

    // register actions
    $this->actionManager->register(new AutoCreateSubtask($this->container));
    $this->actionManager->register(new CategoryAutoSubtask($this->container));

  }

  public function getPluginName()
  {
    return 'Auto Subtask Creation';
  }

  public function getPluginAuthor()
  {
    return 'Craig Crosby';
  }

  public function getPluginVersion()
  {
    return '2.1.0';
  }

  public function getPluginDescription()
  {
    return 'Adding automatic actions for subtasks';
  }

  public function getPluginHomepage()
  {
    return 'https://github.com/creecros/AutoSubtasks';
  }

  public function getCompatibleVersion()
  {
    return '>=1.0.48';
  }
}
