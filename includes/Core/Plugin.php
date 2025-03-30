<?php

namespace NicheClassify\Core;

use NicheClassify\Fields\FieldManager;
use NicheClassify\Forms\FormHandler;
use NicheClassify\Directory\DirectoryRenderer;
use NicheClassify\Admin\Settings;
use NicheClassify\PostTypes\RegisterTypes;
use NicheClassify\Taxonomies\RegisterTaxonomies;
use NicheClassify\User\UserDashboard;

class Plugin {
    public static function init() {
        FieldManager::get_instance();
        FormHandler::get_instance();
        DirectoryRenderer::get_instance();
        Settings::get_instance();
        RegisterTypes::init();
        RegisterTaxonomies::init();
        UserDashboard::get_instance();
    }
}
