<?php

namespace Kanboard\Plugin\AutoSubtasks\Helper;

use Kanboard\Core\Base;

class CheckCoworkerPluginsHelper extends Base
{
    // check exitence of Subtaskdate-plugin

    public function checkSubtaskdate()
    {
        return (file_exists('plugins/Subtaskdate')) ? true : false;
    }
}
