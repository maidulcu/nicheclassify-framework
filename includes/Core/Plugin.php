<?php

namespace NicheClassify\Core;

use NicheClassify\Fields\FieldManager;
use NicheClassify\Forms\FormHandler;
use NicheClassify\Directory\Directory;
use NicheClassify\Admin\Settings;
use NicheClassify\PostTypes\RegisterTypes;
use NicheClassify\Taxonomies\RegisterTaxonomies;
use NicheClassify\User\Dashboard;

class Plugin {
    public static function init() {
        FieldManager::get_instance();
        FormHandler::get_instance();
        Directory::get_instance();
        Settings::get_instance();
        RegisterTypes::init();
        RegisterTaxonomies::init();
        Dashboard::get_instance();
    }
}
