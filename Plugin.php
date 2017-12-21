<?php



namespace Kanboard\Plugin\AutoSubtasks;



use Kanboard\Core\Plugin\Base;

use Kanboard\Plugin\AutoSubtasks\Action\AutoCreateSubtask;



class Plugin extends Base

{
    
	public function initialize()
    
	{
        
		$this->actionManager->register(new AutoCreateSubtask($this->container));
    
	}

	
	public function getPluginName()	
	{ 		 
		return 'Auto Subtasks'; 
	}

	public function getPluginAuthor() 
	{ 	 
		return 'Craig Crosby'; 
	}

	public function getPluginVersion() 
	{ 	 
		return '0.0.1'; 
	}

	public function getPluginDescription() 
	{ 
		return 'Adding automatic actions for subtasks'; 
	}

	public function getPluginHomepage() 
	{ 	 
		return 'https://github.com/creecros/AutoSubtasks'; 
	}
}
