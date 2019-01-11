<?php



namespace Kanboard\Plugin\AutoSubtasks;

use Kanboard\Core\Plugin\Base;
use Kanboard\Plugin\AutoSubtasks\Action\AutoCreateSubtask;
use Kanboard\Plugin\AutoSubtasks\Action\AutoCreateSubtaskVanilla;

class Plugin extends Base

{
  public function initialize()

  {
    $this->template->setTemplateOverride('action_creation/params', 'autoSubtasks:action_creation/params');
    
    if (file_exists('plugins/Subtaskdate')) {
      $this->actionManager->register(new AutoCreateSubtask($this->container));
    } else {
      $this->actionManager->register(new AutoCreateSubtaskVanilla($this->container));
    }
    
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
    return '0.0.3';
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
